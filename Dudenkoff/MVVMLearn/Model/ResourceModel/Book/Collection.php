<?php
/**
 * Copyright © Dudenkoff. All rights reserved.
 * Book Collection - Handles multiple book records
 * 
 * COLLECTION LAYER:
 * - Extends AbstractCollection for multiple record operations
 * - Provides filtering, sorting, pagination
 * - Lazy loading and query optimization
 */

namespace Dudenkoff\MVVMLearn\Model\ResourceModel\Book;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Dudenkoff\MVVMLearn\Model\Book as BookModel;
use Dudenkoff\MVVMLearn\Model\ResourceModel\Book as BookResource;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'book_id';

    /**
     * Initialize resource collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(BookModel::class, BookResource::class);
    }

    /**
     * Filter by status
     *
     * @param int $status
     * @return $this
     */
    public function addStatusFilter(int $status = 1)
    {
        $this->addFieldToFilter('status', $status);
        return $this;
    }

    /**
     * Filter books in stock
     *
     * @return $this
     */
    public function addInStockFilter()
    {
        $this->addFieldToFilter('stock_qty', ['gt' => 0]);
        return $this;
    }

    /**
     * Filter by author
     *
     * @param string $author
     * @return $this
     */
    public function addAuthorFilter(string $author)
    {
        $this->addFieldToFilter('author', ['like' => "%{$author}%"]);
        return $this;
    }

    /**
     * Filter by price range
     *
     * @param float $minPrice
     * @param float|null $maxPrice
     * @return $this
     */
    public function addPriceFilter(float $minPrice, ?float $maxPrice = null)
    {
        $this->addFieldToFilter('price', ['gteq' => $minPrice]);
        if ($maxPrice !== null) {
            $this->addFieldToFilter('price', ['lteq' => $maxPrice]);
        }
        return $this;
    }

    /**
     * Order by title
     *
     * @param string $direction
     * @return $this
     */
    public function setOrderByTitle(string $direction = 'ASC')
    {
        $this->setOrder('title', $direction);
        return $this;
    }

    /**
     * Example: Get total inventory value
     *
     * @return float
     */
    public function getTotalValue(): float
    {
        $this->getSelect()->reset(\Zend_Db_Select::COLUMNS);
        $this->getSelect()->columns(['total' => new \Zend_Db_Expr('SUM(price * stock_qty)')]);
        return (float)$this->getConnection()->fetchOne($this->getSelect());
    }
}

