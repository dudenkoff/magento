<?php
/**
 * Copyright © Dudenkoff. All rights reserved.
 * Resource Model for Product Stats Index (Index Table)
 */

namespace Dudenkoff\IndexerLearn\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class ProductStatsIndex extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('dudenkoff_product_stats_idx', 'product_id');
    }

    /**
     * Get indexed stats for a product
     *
     * @param int $productId
     * @return array
     */
    public function getProductStats(int $productId): array
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getMainTable())
            ->where('product_id = ?', $productId);
        
        $result = $connection->fetchRow($select);
        return $result ?: [];
    }

    /**
     * Get top products by popularity tier
     *
     * @param string $tier
     * @param int $limit
     * @return array
     */
    public function getProductsByPopularity(string $tier = 'high', int $limit = 10): array
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getMainTable())
            ->where('popularity_tier = ?', $tier)
            ->order('view_count DESC')
            ->limit($limit);
        
        return $connection->fetchAll($select);
    }

    /**
     * Get products with best conversion rates
     *
     * @param int $limit
     * @return array
     */
    public function getTopConverters(int $limit = 10): array
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getMainTable())
            ->where('purchase_count > ?', 0)
            ->order('conversion_rate DESC')
            ->limit($limit);
        
        return $connection->fetchAll($select);
    }
}

