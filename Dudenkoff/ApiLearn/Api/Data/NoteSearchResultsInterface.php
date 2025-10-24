<?php
/**
 * Note Search Results Interface
 * 
 * WHAT IS A SEARCH RESULTS INTERFACE?
 * Used for getList() repository methods.
 * Contains:
 * - Array of items (NoteInterface[])
 * - Search criteria used
 * - Total count
 * 
 * MAGENTO CONVENTIONS:
 * - Extends SearchResultsInterface
 * - Named {Entity}SearchResultsInterface
 * - getItems() returns array of data interfaces
 * - setItems() accepts array of data interfaces
 * 
 * WHY?
 * Standardizes list/search API responses across all Magento.
 * Includes pagination info, filters, sorting, etc.
 */

namespace Dudenkoff\ApiLearn\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface NoteSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get notes list
     *
     * @return \Dudenkoff\ApiLearn\Api\Data\NoteInterface[]
     */
    public function getItems();

    /**
     * Set notes list
     *
     * @param \Dudenkoff\ApiLearn\Api\Data\NoteInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}


