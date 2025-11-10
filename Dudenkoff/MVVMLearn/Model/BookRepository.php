<?php
/**
 * Copyright © Dudenkoff. All rights reserved.
 * Book Repository Implementation
 * 
 * REPOSITORY IMPLEMENTATION:
 * - Implements service contract
 * - Handles CRUD operations
 * - Search criteria processing
 */

namespace Dudenkoff\MVVMLearn\Model;

use Dudenkoff\MVVMLearn\Api\BookRepositoryInterface;
use Dudenkoff\MVVMLearn\Api\Data\BookInterface;
use Dudenkoff\MVVMLearn\Api\Data\BookSearchResultsInterface;
use Dudenkoff\MVVMLearn\Api\Data\BookSearchResultsInterfaceFactory;
use Dudenkoff\MVVMLearn\Model\BookFactory;
use Dudenkoff\MVVMLearn\Model\ResourceModel\Book as BookResource;
use Dudenkoff\MVVMLearn\Model\ResourceModel\Book\CollectionFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;

class BookRepository implements BookRepositoryInterface
{
    /**
     * @var BookFactory
     */
    private $bookFactory;

    /**
     * @var BookResource
     */
    private $bookResource;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var BookSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @param BookFactory $bookFactory
     * @param BookResource $bookResource
     * @param CollectionFactory $collectionFactory
     * @param BookSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        BookFactory $bookFactory,
        BookResource $bookResource,
        CollectionFactory $collectionFactory,
        BookSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->bookFactory = $bookFactory;
        $this->bookResource = $bookResource;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritdoc
     */
    public function save(BookInterface $book)
    {
        try {
            $this->bookResource->save($book);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __('Could not save book: %1', $e->getMessage())
            );
        }
        return $book;
    }

    /**
     * @inheritdoc
     */
    public function getById(int $bookId)
    {
        $book = $this->bookFactory->create();
        $this->bookResource->load($book, $bookId);
        if (!$book->getBookId()) {
            throw new NoSuchEntityException(
                __('Book with id "%1" does not exist.', $bookId)
            );
        }
        return $book;
    }

    /**
     * @inheritdoc
     */
    public function getByIsbn(string $isbn)
    {
        $book = $this->bookFactory->create();
        $this->bookResource->load($book, $isbn, 'isbn');
        if (!$book->getBookId()) {
            throw new NoSuchEntityException(
                __('Book with ISBN "%1" does not exist.', $isbn)
            );
        }
        return $book;
    }

    /**
     * @inheritdoc
     */
    public function delete(BookInterface $book)
    {
        try {
            $this->bookResource->delete($book);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(
                __('Could not delete book: %1', $e->getMessage())
            );
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById(int $bookId)
    {
        return $this->delete($this->getById($bookId));
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->collectionFactory->create();
        
        $this->collectionProcessor->process($searchCriteria, $collection);
        
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        
        return $searchResults;
    }
}

