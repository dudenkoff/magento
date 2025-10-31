<?php
/**
 * Copyright © Dudenkoff. All rights reserved.
 * Plugin to automatically trigger indexer after model save
 * 
 * MAGENTO PATTERN: Plugins (Interceptors) are used to extend behavior without modifying core classes
 */

namespace Dudenkoff\IndexerLearn\Plugin;

use Magento\Framework\Model\AbstractModel;
use Dudenkoff\IndexerLearn\Model\Indexer\ProductStatsProcessor;
use Psr\Log\LoggerInterface;

class ReindexAfterSavePlugin
{
    /**
     * @var ProductStatsProcessor
     */
    private $productStatsProcessor;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ProductStatsProcessor $productStatsProcessor
     * @param LoggerInterface $logger
     */
    public function __construct(
        ProductStatsProcessor $productStatsProcessor,
        LoggerInterface $logger
    ) {
        $this->productStatsProcessor = $productStatsProcessor;
        $this->logger = $logger;
    }

    /**
     * After save plugin - triggers reindex based on indexer mode
     * 
     * PLUGIN TYPE: afterSave
     * - Executes AFTER the original save() method completes
     * - Gets the result from original method
     * - Can modify or just observe the result
     * 
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $subject
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $result
     * @param AbstractModel $object
     * @return \Magento\Framework\Model\ResourceModel\AbstractResource
     */
    public function afterSave(
        $subject,
        $result,
        AbstractModel $object
    ) {
        // Only reindex if data actually changed
        if (!$object->hasDataChanges() && !$object->isObjectNew()) {
            return $result;
        }

        try {
            $entityId = $object->getId();
            
            if ($entityId) {
                // Get the entity_id from the object (assumes it has an entity_id field)
                // This automatically checks the indexer mode:
                // - Realtime: triggers immediate reindex
                // - Schedule: does nothing (mview handles it)
                $this->productStatsProcessor->reindexRow($entityId);
                
                $mode = $this->productStatsProcessor->isIndexerScheduled() ? 'Schedule' : 'Realtime';
                $action = $object->isObjectNew() ? 'created' : 'updated';
                
                $this->logger->info(
                    "[IndexerLearn] Product stats {$action} (ID: {$entityId}). " .
                    "Indexer mode: {$mode}"
                );
            }
        } catch (\Exception $e) {
            // Don't break the save operation if indexing fails
            $this->logger->error(
                "[IndexerLearn] Failed to reindex after save: {$e->getMessage()}"
            );
        }

        return $result;
    }
}

