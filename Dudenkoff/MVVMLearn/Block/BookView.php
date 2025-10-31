<?php
/**
 * Copyright © Dudenkoff. All rights reserved.
 * Block - Book Detail View
 */

namespace Dudenkoff\MVVMLearn\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\DataObject\IdentityInterface;
use Dudenkoff\MVVMLearn\Api\Data\BookInterface;

class BookView extends Template implements IdentityInterface
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
    }

    /**
     * Get current book
     *
     * @return BookInterface|null
     */
    public function getBook(): ?BookInterface
    {
        return $this->registry->registry('current_book');
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
        if ($book) {
            return $book->getIdentities();  // Use Model's cache tags
        }
        return [];
    }
}

