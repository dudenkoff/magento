<?php
/**
 * Copyright © Dudenkoff. All rights reserved.
 * Model for Product Stats
 * 
 * This is a simple Model to demonstrate the plugin pattern for automatic reindexing
 */

namespace Dudenkoff\IndexerLearn\Model;

use Magento\Framework\Model\AbstractModel;

class ProductStats extends AbstractModel
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(\Dudenkoff\IndexerLearn\Model\ResourceModel\ProductStats::class);
    }
}

