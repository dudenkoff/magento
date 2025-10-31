<?php
/**
 * Copyright © Dudenkoff. All rights reserved.
 * Book Model - Represents a Book entity
 * 
 * MODEL LAYER:
 * - Extends AbstractModel for ORM functionality
 * - Implements data interface for type safety
 * - Contains business logic and data validation
 */

namespace Dudenkoff\MVVMLearn\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;
use Dudenkoff\MVVMLearn\Api\Data\BookInterface;

class Book extends AbstractModel implements BookInterface, IdentityInterface
{
    /**
     * Cache tag for cache invalidation
     */
    const CACHE_TAG = 'dudenkoff_book';

    /**
     * @var string
     * 
     * Cache tag used by Magento to invalidate cache when model is saved/deleted
     * This is automatically processed by FlushCacheByTags plugin
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Dudenkoff\MVVMLearn\Model\ResourceModel\Book::class);
    }

    /**
     * @inheritdoc
     */
    public function getBookId()
    {
        return $this->getData(self::BOOK_ID);
    }

    /**
     * @inheritdoc
     */
    public function setBookId($bookId)
    {
        return $this->setData(self::BOOK_ID, $bookId);
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * @inheritdoc
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * @inheritdoc
     */
    public function getAuthor()
    {
        return $this->getData(self::AUTHOR);
    }

    /**
     * @inheritdoc
     */
    public function setAuthor($author)
    {
        return $this->setData(self::AUTHOR, $author);
    }

    /**
     * @inheritdoc
     */
    public function getIsbn()
    {
        return $this->getData(self::ISBN);
    }

    /**
     * @inheritdoc
     */
    public function setIsbn($isbn)
    {
        return $this->setData(self::ISBN, $isbn);
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * @inheritdoc
     */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * @inheritdoc
     */
    public function getPrice()
    {
        return $this->getData(self::PRICE);
    }

    /**
     * @inheritdoc
     */
    public function setPrice($price)
    {
        return $this->setData(self::PRICE, $price);
    }

    /**
     * @inheritdoc
     */
    public function getStockQty()
    {
        return $this->getData(self::STOCK_QTY);
    }

    /**
     * @inheritdoc
     */
    public function setStockQty($stockQty)
    {
        return $this->setData(self::STOCK_QTY, $stockQty);
    }

    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @inheritdoc
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * @inheritdoc
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritdoc
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * Business logic example: Check if book is in stock
     *
     * @return bool
     */
    public function isInStock(): bool
    {
        return $this->getStockQty() > 0;
    }

    /**
     * Business logic example: Apply discount
     *
     * @param float $discountPercent
     * @return float
     */
    public function getDiscountedPrice(float $discountPercent): float
    {
        return $this->getPrice() * (1 - $discountPercent / 100);
    }

    /**
     * Get cache identities for FPC (Full Page Cache)
     * 
     * CACHE PATTERN:
     * - Returns array of cache tags for this specific entity
     * - Used by Magento's cache system to invalidate related pages
     * - When this model is saved/deleted, these tags are cleaned
     * 
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getBookId()];
    }
}

