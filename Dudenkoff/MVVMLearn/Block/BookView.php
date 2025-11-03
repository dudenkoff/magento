<?php
/**
 * Copyright © Dudenkoff. All rights reserved.
 * Block - Book Detail View
 */

namespace Dudenkoff\MVVMLearn\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\DataObject\IdentityInterface;
use Dudenkoff\MVVMLearn\Api\Data\BookInterface;
use Dudenkoff\MVVMLearn\Api\BookRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class BookView extends Template implements IdentityInterface
{
    /**
     * @var BookRepositoryInterface
     */
    private $bookRepository;

    /**
     * @var BookInterface|null|false
     */
    private $book;

    /**
     * @param Context $context
     * @param BookRepositoryInterface $bookRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        BookRepositoryInterface $bookRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->bookRepository = $bookRepository;
    }

    /**
     * Get current book
     * 
     * Loads book from DB once and caches it.
     * Also sets the page title on first load.
     *
     * @return BookInterface|null
     */
    public function getBook(): ?BookInterface
    {
        if ($this->book === null) {
            $bookId = (int)$this->getRequest()->getParam('id');
            if ($bookId) {
                try {
                    $this->book = $this->bookRepository->getById($bookId);
                    // Set page title when book is loaded
                    $this->pageConfig->getTitle()->set($this->book->getTitle());
                } catch (NoSuchEntityException $e) {
                    $this->book = false;
                }
            } else {
                $this->book = false;
            }
        }
        return $this->book ?: null;
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
     * Get stock status label
     *
     * @return string
     */
    public function getStockStatusLabel(): string
    {
        $book = $this->getBook();
        if (!$book) {
            return 'Unknown';
        }

        $qty = $book->getStockQty();
        if ($qty > 10) {
            return 'In Stock';
        } elseif ($qty > 0) {
            return 'Low Stock (' . $qty . ' left)';
        }
        return 'Out of Stock';
    }

    /**
     * Get back to list URL
     *
     * @return string
     */
    public function getBackUrl(): string
    {
        return $this->getUrl('books');
    }

    /**
     * Get cache identities for Full Page Cache
     * 
     * FPC INTEGRATION:
     * - When book is saved, this page's cache is invalidated
     * - Returns the same tags as the model
     * - Automatic cache management
     * 
     * @return array
     */
    public function getIdentities()
    {
        $book = $this->getBook();
        if ($book instanceof IdentityInterface) {
            return $book->getIdentities();  // Use Model's cache tags
        }
        return [];
    }
}

