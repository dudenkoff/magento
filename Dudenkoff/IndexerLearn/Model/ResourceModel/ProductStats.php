<?php
/**
 * Copyright © Dudenkoff. All rights reserved.
 * Resource Model for Product Stats (Source Table)
 */

namespace Dudenkoff\IndexerLearn\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class ProductStats extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('dudenkoff_product_stats', 'entity_id');
    }

    /**
     * Get all product IDs from source table
     *
     * @return array
     */
    public function getAllProductIds(): array
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getMainTable(), ['product_id']);
        
        return $connection->fetchCol($select);
    }

    /**
     * Increment view count for a product
     *
     * @param int $productId
     * @return void
     */
    public function incrementViewCount(int $productId): void
    {
        $connection = $this->getConnection();
        $connection->update(
            $this->getMainTable(),
            ['view_count' => new \Zend_Db_Expr('view_count + 1')],
            ['product_id = ?' => $productId]
        );
    }

    /**
     * Update purchase data for a product
     *
     * @param int $productId
     * @param float $revenue
     * @return void
     */
    public function recordPurchase(int $productId, float $revenue): void
    {
        $connection = $this->getConnection();
        $connection->update(
            $this->getMainTable(),
            [
                'purchase_count' => new \Zend_Db_Expr('purchase_count + 1'),
                'revenue' => new \Zend_Db_Expr('revenue + ' . $revenue)
            ],
            ['product_id = ?' => $productId]
        );
    }
}

