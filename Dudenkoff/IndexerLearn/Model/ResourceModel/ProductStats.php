<?php
/**
 * Copyright © Dudenkoff. All rights reserved.
 * Resource Model for Product Stats
 */

namespace Dudenkoff\IndexerLearn\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class ProductStats extends AbstractDb
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('dudenkoff_product_stats', 'entity_id');
    }
}

