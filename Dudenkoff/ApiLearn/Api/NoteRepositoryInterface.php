<?php
/**
 * Note Repository Interface
 * 
 * WHAT IS A REPOSITORY?
 * A repository handles data persistence (CRUD operations).
 * It's the gateway between your business logic and data storage.
 * 
 * STANDARD REPOSITORY METHODS:
 * - save($entity) - Create or update
 * - getById($id) - Retrieve by ID
 * - getList($searchCriteria) - Search/filter/sort
 * - delete($entity) - Delete by object
 * - deleteById($id) - Delete by ID
 * 
 * WHY USE REPOSITORY PATTERN?
 * - Separates business logic from data access
 * - Easy to test (mock repository)
 * - Can swap storage (DB, API, cache)
 * - Consistent interface across entities
 * 
 * API MAPPING:
 * - POST /notes → save()
 * - GET /notes/:id → getById()
 * - GET /notes/search → getList()
 * - PUT /notes/:id → save()
 * - DELETE /notes/:id → deleteById()
 */

namespace Dudenkoff\ApiLearn\Api;

use Dudenkoff\ApiLearn\Api\Data\NoteInterface;
use Dudenkoff\ApiLearn\Api\Data\NoteSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

interface NoteRepositoryInterface
{
    /**
     * Save note
     * 
     * Used for both CREATE and UPDATE:
     * - If note_id exists → UPDATE
     * - If note_id is null → CREATE
     *
     * @param \Dudenkoff\ApiLearn\Api\Data\NoteInterface $note
     * @return \Dudenkoff\ApiLearn\Api\Data\NoteInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(NoteInterface $note);

    /**
     * Retrieve note by ID
     *
     * @param int $noteId
     * @return \Dudenkoff\ApiLearn\Api\Data\NoteInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($noteId);

    /**
     * Retrieve notes matching the specified criteria
     * 
     * SearchCriteria allows:
     * - Filtering: where conditions
     * - Sorting: order by
     * - Pagination: limit, offset
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Dudenkoff\ApiLearn\Api\Data\NoteSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete note
     *
     * @param \Dudenkoff\ApiLearn\Api\Data\NoteInterface $note
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(NoteInterface $note);

    /**
     * Delete note by ID
     *
     * @param int $noteId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($noteId);
}


