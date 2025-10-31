<?php
/**
 * Copyright © Dudenkoff. All rights reserved.
 * Main Indexer Action Class
 * 
 * This class implements Magento's indexer interfaces to perform indexing operations.
 * It demonstrates the core concepts of Magento indexation:
 * 
 * 1. FULL REINDEX (executeFull): Rebuilds entire index from scratch
 * 2. PARTIAL REINDEX (executeList): Reindexes specific IDs
 * 3. SINGLE ROW REINDEX (executeRow): Reindexes one ID
 * 
 * The indexer reads from dudenkoff_product_stats (source table)
 * and writes to dudenkoff_product_stats_idx (index table)
 */

namespace Dudenkoff\IndexerLearn\Model\Indexer;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Indexer\ActionInterface as IndexerActionInterface;
use Magento\Framework\Mview\ActionInterface as MviewActionInterface;
use Psr\Log\LoggerInterface;

class ProductStats implements IndexerActionInterface, MviewActionInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var LoggerInterface Logger for cron.log (Magento core style)
     */
    private $logger;

    /**
     * Constructor
     * 
     * MAGENTO CORE PATTERN:
     * - Inject a logger configured to write to cron.log via di.xml
     * - The logger is a virtual type using Monolog with custom handler
     * - This is exactly how core modules (Indexer, Cron) handle logging
     * 
     * @param ResourceConnection $resourceConnection
     * @param LoggerInterface $logger Logger configured for cron.log in di.xml
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        LoggerInterface $logger
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->logger = $logger;
    }

    /**
     * Execute full indexation
     * 
     * This is called when you run: bin/magento indexer:reindex dudenkoff_product_stats
     * It rebuilds the ENTIRE index from scratch.
     * 
     * Process:
     * 1. Truncate the index table (clean slate)
     * 2. Read ALL rows from source table
     * 3. Calculate derived metrics
     * 4. Insert into index table
     *
     * @return void
     */
    public function executeFull()
    {
        $this->logger->info('[IndexerLearn] Starting FULL reindex');
        
        $connection = $this->resourceConnection->getConnection();
        $sourceTable = $this->resourceConnection->getTableName('dudenkoff_product_stats');
        $indexTable = $this->resourceConnection->getTableName('dudenkoff_product_stats_idx');

        try {
            // Step 1: Clear the index table
            $connection->truncateTable($indexTable);
            $this->logger->info('[IndexerLearn] Truncated index table');

            // Step 2: Fetch all data from source table
            $select = $connection->select()
                ->from($sourceTable, [
                    'product_id',
                    'view_count',
                    'purchase_count',
                    'revenue'
                ]);

            $sourceData = $connection->fetchAll($select);
            $this->logger->info('[IndexerLearn] Fetched ' . count($sourceData) . ' rows from source table');

            // Step 3: Process and insert into index table
            if (!empty($sourceData)) {
                $indexData = [];
                foreach ($sourceData as $row) {
                    $indexData[] = $this->calculateIndexData($row);
                }

                // Batch insert into index table
                $connection->insertMultiple($indexTable, $indexData);
                $this->logger->info('[IndexerLearn] Inserted ' . count($indexData) . ' rows into index table');
            }

            $this->logger->info('[IndexerLearn] FULL reindex completed successfully');
        } catch (\Exception $e) {
            $this->logger->error('[IndexerLearn] FULL reindex failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Execute partial indexation by ID list
     * 
     * This is called when the mview system detects changes.
     * It only reindexes the CHANGED rows, making it much faster than full reindex.
     * 
     * When "Update on Schedule" mode 9
     * @return void
     */
    public function executeList(array $ids)
    {
        $this->logger->info('[IndexerLearn] Starting PARTIAL reindex for IDs: ' . implode(',', $ids));

        if (empty($ids)) {
            return;
        }

        $connection = $this->resourceConnection->getConnection();
        $sourceTable = $this->resourceConnection->getTableName('dudenkoff_product_stats');
        $indexTable = $this->resourceConnection->getTableName('dudenkoff_product_stats_idx');

        try {
            // Fetch only the changed rows
            $select = $connection->select()
                ->from($sourceTable, [
                    'product_id',
                    'view_count',
                    'purchase_count',
                    'revenue'
                ])
                ->where('entity_id IN (?)', $ids);

            $sourceData = $connection->fetchAll($select);
            $this->logger->info('[IndexerLearn] Fetched ' . count($sourceData) . ' changed rows');

            if (!empty($sourceData)) {
                foreach ($sourceData as $row) {
                    $indexData = $this->calculateIndexData($row);
                    
                    // Use INSERT ... ON DUPLICATE KEY UPDATE to handle both new and existing rows
                    $connection->insertOnDuplicate($indexTable, $indexData);
                }
                
                $this->logger->info('[IndexerLearn] Updated ' . count($sourceData) . ' rows in index table');
            }

            $this->logger->info('[IndexerLearn] PARTIAL reindex completed successfully');
        } catch (\Exception $e) {
            $this->logger->error('[IndexerLearn] PARTIAL reindex failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Execute single row indexation
     * 
     * This is called for immediate single-row updates.
     * Used when "Update on Save" mode is enabled.
     *
     * @param int $id
     * @return void
     */
    public function executeRow($id)
    {
        $this->logger->info('[IndexerLearn] Starting SINGLE ROW reindex for ID: ' . $id);
        $this->executeList([$id]);
    }

    /**
     * Execute materialized view indexation (required by MviewActionInterface)
     * 
     * This is the method called by the mview system during scheduled updates.
     * It receives the list of changed entity IDs from the changelog table.
     *
     * @param int[] $ids
     * @return void
     */
    public function execute($ids)
    {
        $this->logger->info('[IndexerLearn] Mview triggered for IDs: ' . implode(',', $ids));
        $this->executeList($ids);
    }

    /**
     * Calculate indexed data with derived metrics
     * 
     * This is where the "magic" happens - we calculate derived values
     * that would be expensive to compute on every query.
     * 
     * Instead of calculating conversion rate every time someone queries,
     * we pre-calculate it during indexing and store it.
     *
     * @param array $sourceRow
     * @return array
     */
    private function calculateIndexData(array $sourceRow): array
    {
        $viewCount = (int)$sourceRow['view_count'];
        $purchaseCount = (int)$sourceRow['purchase_count'];
        $revenue = (float)$sourceRow['revenue'];

        // Calculate conversion rate (what % of views result in purchase)
        $conversionRate = 0;
        if ($viewCount > 0) {
            $conversionRate = round(($purchaseCount / $viewCount) * 100, 2);
        }

        // Calculate average order value
        $averageOrderValue = 0;
        if ($purchaseCount > 0) {
            $averageOrderValue = round($revenue / $purchaseCount, 4);
        }

        // Classify popularity tier based on view count
        $popularityTier = 'low';
        if ($viewCount >= 1000) {
            $popularityTier = 'high';
        } elseif ($viewCount >= 100) {
            $popularityTier = 'medium';
        }

        return [
            'product_id' => $sourceRow['product_id'],
            'view_count' => $viewCount,
            'purchase_count' => $purchaseCount,
            'revenue' => $revenue,
            'conversion_rate' => $conversionRate,
            'average_order_value' => $averageOrderValue,
            'popularity_tier' => $popularityTier,
            'indexed_at' => date('Y-m-d H:i:s')
        ];
    }
}

