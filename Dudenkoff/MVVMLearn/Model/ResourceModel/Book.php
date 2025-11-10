<?php
/**
 * Copyright © Dudenkoff. All rights reserved.
 * Book Resource Model - Handles database operations
 * 
 * RESOURCE MODEL LAYER:
 * - Extends AbstractDb for database abstraction
 * - Contains complex SQL queries
 * - Database transaction handling
 */

namespace Dudenkoff\MVVMLearn\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

class Book extends AbstractDb
{
    /**
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('dudenkoff_book', 'book_id');
    }

    /**
     * Example: Get books by author using custom SQL
     *
     * @param string $author
     * @return array
     */
    public function getBooksByAuthor(string $author): array
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getMainTable())
            ->where('author LIKE ?', "%{$author}%")
            ->where('status = ?', 1)
            ->order('created_at DESC');

        return $connection->fetchAll($select);
    }

    /**
     * Example: Update stock quantity
     *
     * @param int $bookId
     * @param int $qty
     * @return int
     */
    public function updateStockQty(int $bookId, int $qty): int
    {
        $connection = $this->getConnection();
        return $connection->update(
            $this->getMainTable(),
            ['stock_qty' => new \Zend_Db_Expr("stock_qty + ({$qty})")],
            ['book_id = ?' => $bookId]
        );
    }

    /**
     * Example: Get total value of inventory
     *
     * @return float
     */
    public function getTotalInventoryValue(): float
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getMainTable(), ['total' => new \Zend_Db_Expr('SUM(price * stock_qty)')])
            ->where('status = ?', 1);

        return (float)$connection->fetchOne($select);
    }
}

