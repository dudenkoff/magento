<?php
/**
 * Copyright © Dudenkoff. All rights reserved.
 * Block - Book List (ViewModel in MVVM)
 * 
 * BLOCK/VIEWMODEL:
 * - Prepares data for the view (template)
 * - Contains presentation logic
 * - Bridges Model and View
 */

namespace Dudenkoff\MVVMLearn\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\DataObject\IdentityInterface;
use Dudenkoff\MVVMLearn\Model\ResourceModel\Book\CollectionFactory;
use Dudenkoff\MVVMLearn\Model\Book;

class BookList extends Template implements IdentityInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param Context $context
     * @param CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Get book collection
     *
     * @return \Dudenkoff\MVVMLearn\Model\ResourceModel\Book\Collection
     */
    public function getBookCollection()
    {
        $collection = $this->collectionFactory->create();
        $collection->addStatusFilter(1);
        $collection->setOrderByTitle('ASC');
        return $collection;
    }

    /**
     * Get book view URL
     *
     * @param int $bookId
     * @return string
     */
    public function getBookUrl(int $bookId): string
    {
        return $this->getUrl('books/book/view', ['id' => $bookId]);
    }

    /**
     * Format price
     *
     * @param float $price
     * @return string
     */
    public function formatPrice(float $price): string
    {
        return '$' . number_format($price, 2);
    }

    /**
     * Check if book is in stock
     *
     * @param \Dudenkoff\MVVMLearn\Model\Book $book
     * @return bool
     */
    public function isInStock($book): bool
    {
        return $book->getStockQty() > 0;
    }

    /**
     * Get cache identities for Full Page Cache
     * 
     * FPC INTEGRATION:
     * - When ANY book is saved, this page's cache is invalidated
     * - Collects all book IDs from the collection
     * - Returns cache tags for all displayed books
     * 
     * @return array
     */
    public function getIdentities()
    {
        $identities = [];
        
        foreach ($this->getBookCollection() as $book) {
            $identities = array_merge($identities, $book->getIdentities());
        }
        
        // Also add general tag for "all books" pages
        $identities[] = Book::CACHE_TAG;
        
        return $identities;
    }
}

