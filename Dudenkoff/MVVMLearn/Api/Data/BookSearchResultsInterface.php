<?php
/**
 * Copyright © Dudenkoff. All rights reserved.
 * Book Search Results Interface
 */

namespace Dudenkoff\MVVMLearn\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface BookSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get books list
     *
     * @return \Dudenkoff\MVVMLearn\Api\Data\BookInterface[]
     */
    public function getItems();

    /**
     * Set books list
     *
     * @param \Dudenkoff\MVVMLearn\Api\Data\BookInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

