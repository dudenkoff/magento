<?php
/**
 * Copyright © Dudenkoff. All rights reserved.
 * Book Search Results
 */

namespace Dudenkoff\MVVMLearn\Model;

use Magento\Framework\Api\SearchResults;
use Dudenkoff\MVVMLearn\Api\Data\BookSearchResultsInterface;

class BookSearchResults extends SearchResults implements BookSearchResultsInterface
{
}

