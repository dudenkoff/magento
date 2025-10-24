<?php
/**
 * Note Management Interface
 * 
 * WHAT IS A MANAGEMENT INTERFACE?
 * A service contract for business logic operations beyond simple CRUD.
 * Examples: publish, archive, send, calculate, etc.
 * 
 * REPOSITORY vs MANAGEMENT:
 * - Repository: CRUD operations (save, get, delete)
 * - Management: Business logic operations (publish, approve, process)
 * 
 * WHEN TO USE:
 * - Complex operations involving multiple entities
 * - Business workflows
 * - Operations that don't fit CRUD pattern
 * - Actions that trigger side effects
 * 
 * EXAMPLE USE CASES:
 * - Publish/unpublish content
 * - Calculate totals
 * - Send notifications
 * - Process bulk operations
 * - Approve/reject workflows
 */

namespace Dudenkoff\ApiLearn\Api;

use Dudenkoff\ApiLearn\Api\Data\NoteInterface;

interface NoteManagementInterface
{
    /**
     * Publish a note
     * 
     * Business logic: Mark as published + send notifications + log
     *
     * @param int $noteId
     * @return \Dudenkoff\ApiLearn\Api\Data\NoteInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function publish($noteId);

    /**
     * Unpublish a note
     *
     * @param int $noteId
     * @return \Dudenkoff\ApiLearn\Api\Data\NoteInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function unpublish($noteId);

    /**
     * Get statistics for a note
     * 
     * Custom data not stored in entity
     *
     * @param int $noteId
     * @return array Statistics array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStatistics($noteId);

    /**
     * Bulk publish notes
     * 
     * Process multiple notes at once
     *
     * @param int[] $noteIds
     * @return array Results with success/failure for each ID
     */
    public function bulkPublish(array $noteIds);
}


