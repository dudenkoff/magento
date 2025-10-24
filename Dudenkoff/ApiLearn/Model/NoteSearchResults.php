<?php
/**
 * Note Search Results
 * 
 * Container for search/list results.
 * Extends Magento's standard search results.
 */

namespace Dudenkoff\ApiLearn\Model;

use Dudenkoff\ApiLearn\Api\Data\NoteSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

class NoteSearchResults extends SearchResults implements NoteSearchResultsInterface
{
    // Inherits all methods from SearchResults
    // - getItems() / setItems()
    // - getTotalCount() / setTotalCount()
    // - getSearchCriteria() / setSearchCriteria()
}


