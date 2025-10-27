<?php
/**
 * Copyright © Dudenkoff. All rights reserved.
 * Observer that demonstrates manual index invalidation
 * 
 * KEY CONCEPT: Index Invalidation
 * 
 * When data changes, indexes become "invalid" (out of sync).
 * Magento has two modes for handling this:
 * 
 * 1. UPDATE ON SAVE: Reindex immediately when data changes (slower saves, always up-to-date)
 * 2. UPDATE ON SCHEDULE: Mark as invalid, reindex later via cron (faster saves, may be stale)
 * 
 * This observer shows how to manually invalidate an index.
 */

namespace Dudenkoff\IndexerLearn\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Psr\Log\LoggerInterface;

class InvalidateStatsIndexObserver implements ObserverInterface
{
    /**
     * Indexer ID from indexer.xml
     */
    private const INDEXER_ID = 'dudenkoff_product_stats';

    /**
     * @var IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param IndexerRegistry $indexerRegistry
     * @param LoggerInterface $logger
     */
    public function __construct(
        IndexerRegistry $indexerRegistry,
        LoggerInterface $logger
    ) {
        $this->indexerRegistry = $indexerRegistry;
        $this->logger = $logger;
    }

    /**
     * Execute observer
     * 
     * This is triggered when the event 'dudenkoff_stats_updated' is dispatched.
     * It marks the indexer as invalid, so it will be rebuilt on next cron run
     * (if in "Update on Schedule" mode).
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        try {
            $indexer = $this->indexerRegistry->get(self::INDEXER_ID);
            
            // Check if indexer is in "Update on Schedule" mode
            if (!$indexer->isScheduled()) {
                $this->logger->info(
                    '[IndexerLearn] Indexer is in "Update on Save" mode - will reindex immediately'
                );
                // In "Update on Save" mode, the mview system handles it automatically
                return;
            }

            // Mark indexer as invalid
            $indexer->invalidate();
            
            $this->logger->info(
                '[IndexerLearn] Marked indexer as INVALID. ' .
                'It will be rebuilt on next cron run (indexer_update_all_views).'
            );

            // Optional: Get product IDs that were changed (if passed in event)
            $productIds = $observer->getData('product_ids');
            if ($productIds) {
                $this->logger->info(
                    '[IndexerLearn] Affected product IDs: ' . implode(',', $productIds)
                );
            }

        } catch (\Exception $e) {
            $this->logger->error(
                '[IndexerLearn] Failed to invalidate indexer: ' . $e->getMessage()
            );
        }
    }
}

