<?php
/**
 * Note Repository Implementation
 * 
 * WHAT IS THIS?
 * This is the concrete implementation of NoteRepositoryInterface.
 * It handles all CRUD operations for Note entities using DATABASE.
 * 
 * KEY METHODS:
 * - save() - Create or update note in database
 * - getById() - Retrieve note from database by ID
 * - getList() - Search database with criteria
 * - delete() - Delete note from database
 * - deleteById() - Delete by ID from database
 * 
 * DATABASE OPERATIONS:
 * Uses Magento's Resource Model and Collection for database access.
 */

namespace Dudenkoff\ApiLearn\Model;

use Dudenkoff\ApiLearn\Api\Data\NoteInterface;
use Dudenkoff\ApiLearn\Api\Data\NoteInterfaceFactory;
use Dudenkoff\ApiLearn\Api\Data\NoteSearchResultsInterface;
use Dudenkoff\ApiLearn\Api\Data\NoteSearchResultsInterfaceFactory;
use Dudenkoff\ApiLearn\Api\NoteRepositoryInterface;
use Dudenkoff\ApiLearn\Model\ResourceModel\Note as NoteResource;
use Dudenkoff\ApiLearn\Model\ResourceModel\Note\CollectionFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;

class NoteRepository implements NoteRepositoryInterface
{
    /**
     * @var NoteInterfaceFactory
     */
    private $noteFactory;

    /**
     * @var NoteResource
     */
    private $noteResource;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var NoteSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * Constructor
     *
     * @param NoteInterfaceFactory $noteFactory
     * @param NoteResource $noteResource
     * @param CollectionFactory $collectionFactory
     * @param NoteSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        NoteInterfaceFactory $noteFactory,
        NoteResource $noteResource,
        CollectionFactory $collectionFactory,
        NoteSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->noteFactory = $noteFactory;
        $this->noteResource = $noteResource;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function save(NoteInterface $note)
    {
        try {
            // Use resource model to save to database
            $this->noteResource->save($note);
            return $note;
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __('Could not save note: %1', $e->getMessage())
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getById($noteId)
    {
        // Create new note instance
        $note = $this->noteFactory->create();
        
        // Load from database
        $this->noteResource->load($note, $noteId);
        
        // Check if found
        if (!$note->getId()) {
            throw new NoSuchEntityException(
                __('Note with ID "%1" does not exist.', $noteId)
            );
        }
        
        return $note;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        // Create collection (represents SELECT query)
        $collection = $this->collectionFactory->create();
        
        // Apply search criteria to collection
        // This handles:
        // - Filters (WHERE clauses)
        // - Sorting (ORDER BY)
        // - Pagination (LIMIT/OFFSET)
        $this->collectionProcessor->process($searchCriteria, $collection);
        
        // Create search results
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(NoteInterface $note)
    {
        try {
            $this->noteResource->delete($note);
            return true;
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(
                __('Could not delete note: %1', $e->getMessage())
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($noteId)
    {
        // Load note first
        $note = $this->getById($noteId);
        
        // Delete it
        return $this->delete($note);
    }
}
