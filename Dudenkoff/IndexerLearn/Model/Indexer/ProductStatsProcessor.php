<?php
/**
 * Copyright © Dudenkoff. All rights reserved.
 * Processor for Product Stats Indexer
 * 
 * This follows Magento's AbstractProcessor pattern for handling realtime/schedule modes
 */

namespace Dudenkoff\IndexerLearn\Model\Indexer;

use Magento\Framework\Indexer\AbstractProcessor;

class ProductStatsProcessor extends AbstractProcessor
{
    /**
     * Indexer ID
     */
    const INDEXER_ID = 'dudenkoff_product_stats';
}
