<?php
/**
 * Copyright © Dudenkoff. All rights reserved.
 * Book Repository Interface (Service Contract)
 * 
 * REPOSITORY PATTERN:
 * - Service contract for data access
 * - Abstracts storage mechanism
 * - API-safe interface
 */

namespace Dudenkoff\MVVMLearn\Api;

use Dudenkoff\MVVMLearn\Api\Data\BookInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;

interface BookRepositoryInterface
{
    /**
     * Save book
     *
     * @param BookInterface $book
     * @return BookInterface
     * @throws CouldNotSaveException
     */
    public function save(BookInterface $book);

    /**
     * Get book by ID
     *
     * @param int $bookId
     * @return BookInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $bookId);

    /**
     * Get book by ISBN
     *
     * @param string $isbn
     * @return BookInterface
     * @throws NoSuchEntityException
     */
    public function getByIsbn(string $isbn);

    /**
     * Delete book
     *
     * @param BookInterface $book
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(BookInterface $book);

    /**
     * Delete book by ID
     *
     * @param int $bookId
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function deleteById(int $bookId);

    /**
     * Get list of books by search criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Dudenkoff\MVVMLearn\Api\Data\BookSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);
}

