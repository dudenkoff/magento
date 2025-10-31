<?php
/**
 * Copyright © Dudenkoff. All rights reserved.
 * Service for updating product statistics
 * 
 * EXAMPLE: This demonstrates how to use a custom indexer with realtime/schedule mode handling
 */

namespace Dudenkoff\IndexerLearn\Service;

use Magento\Framework\App\ResourceConnection;
use Dudenkoff\IndexerLearn\Model\Indexer\ProductStatsProcessor;
use Psr\Log\LoggerInterface;

class ProductStatsService
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var ProductStatsProcessor
     */
    private $productStatsProcessor;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ResourceConnection $resourceConnection
     * @param ProductStatsProcessor $productStatsProcessor
     * @param LoggerInterface $logger
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        ProductStatsProcessor $productStatsProcessor,
        LoggerInterface $logger
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->productStatsProcessor = $productStatsProcessor;
        $this->logger = $logger;
    }

    /**
     * Increment view count for a product
     * 
     * EXAMPLE: Shows how to update data and trigger indexer based on mode
     * 
     * @param int $productId
     * @param int $incrementBy
     * @return bool
     */
    public function incrementViewCount(int $productId, int $incrementBy = 1): bool
    {
        try {
            // Get entity_id for this product
            $entityId = $this->getEntityIdByProductId($productId);
            
            if (!$entityId) {
                $this->logger->warning("Product {$productId} not found in stats table");
                return false;
            }

            // Update the database
            $connection = $this->resourceConnection->getConnection();
            $table = $this->resourceConnection->getTableName('dudenkoff_product_stats');
            
            $affected = $connection->update(
                $table,
                ['view_count' => new \Zend_Db_Expr("view_count + {$incrementBy}")],
                ['product_id = ?' => $productId]
            );

            if ($affected > 0) {
                // IMPORTANT: This automatically checks if indexer is in realtime or schedule mode
                // - Realtime mode: triggers immediate reindex
                // - Schedule mode: does nothing (mview handles it via database triggers)
                $this->productStatsProcessor->reindexRow($entityId);
                
                $this->logger->info(
                    "Product {$productId} view count incremented by {$incrementBy}. " .
                    "Indexer mode: " . ($this->productStatsProcessor->isIndexerScheduled() ? 'Schedule' : 'Realtime')
                );
                
                return true;
            }

            return false;

        } catch (\Exception $e) {
            $this->logger->error("Failed to increment view count for product {$productId}: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Record a purchase for a product
     * 
     * EXAMPLE: Another example of data update with automatic indexer handling
     * 
     * @param int $productId
     * @param float $revenue
     * @return bool
     */
    public function recordPurchase(int $productId, float $revenue): bool
    {
        try {
            $entityId = $this->getEntityIdByProductId($productId);
            
            if (!$entityId) {
                $this->logger->warning("Product {$productId} not found in stats table");
                return false;
            }

            // Update the database
            $connection = $this->resourceConnection->getConnection();
            $table = $this->resourceConnection->getTableName('dudenkoff_product_stats');
            
            $affected = $connection->update(
                $table,
                [
                    'purchase_count' => new \Zend_Db_Expr('purchase_count + 1'),
                    'revenue' => new \Zend_Db_Expr("revenue + {$revenue}")
                ],
                ['product_id = ?' => $productId]
            );

            if ($affected > 0) {
                // Automatically handles realtime vs schedule mode
                $this->productStatsProcessor->reindexRow($entityId);
                
                $this->logger->info("Purchase recorded for product {$productId}. Revenue: \${$revenue}");
                
                return true;
            }

            return false;

        } catch (\Exception $e) {
            $this->logger->error("Failed to record purchase for product {$productId}: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Batch update multiple products with force reindex option
     * 
     * EXAMPLE: Shows how to force reindex even in schedule mode
     * 
     * @param array $updates Array of ['product_id' => int, 'views' => int]
     * @param bool $forceReindex Force reindex even if in schedule mode
     * @return int Number of products updated
     */
    public function batchUpdateViews(array $updates, bool $forceReindex = false): int
    {
        $count = 0;
        $entityIds = [];

        try {
            $connection = $this->resourceConnection->getConnection();
            $table = $this->resourceConnection->getTableName('dudenkoff_product_stats');

            foreach ($updates as $update) {
                $productId = $update['product_id'];
                $views = $update['views'] ?? 0;

                if ($views <= 0) {
                    continue;
                }

                $entityId = $this->getEntityIdByProductId($productId);
                if (!$entityId) {
                    continue;
                }

                $affected = $connection->update(
                    $table,
                    ['view_count' => new \Zend_Db_Expr("view_count + {$views}")],
                    ['product_id = ?' => $productId]
                );

                if ($affected > 0) {
                    $entityIds[] = $entityId;
                    $count++;
                }
            }

            if (!empty($entityIds)) {
                // Batch reindex with optional force flag
                // $forceReindex = true will reindex even in schedule mode
                $this->productStatsProcessor->reindexList($entityIds, $forceReindex);
                
                $this->logger->info(
                    "Batch updated {$count} products. " .
                    "Mode: " . ($forceReindex ? 'Forced' : ($this->productStatsProcessor->isIndexerScheduled() ? 'Schedule' : 'Realtime'))
                );
            }

            return $count;

        } catch (\Exception $e) {
            $this->logger->error("Batch update failed: {$e->getMessage()}");
            return $count;
        }
    }

    /**
     * Get entity_id by product_id
     * 
     * @param int $productId
     * @return int|null
     */
    private function getEntityIdByProductId(int $productId): ?int
    {
        $connection = $this->resourceConnection->getConnection();
        $table = $this->resourceConnection->getTableName('dudenkoff_product_stats');

        $select = $connection->select()
            ->from($table, ['entity_id'])
            ->where('product_id = ?', $productId);

        $entityId = $connection->fetchOne($select);
        return $entityId ? (int)$entityId : null;
    }
}

