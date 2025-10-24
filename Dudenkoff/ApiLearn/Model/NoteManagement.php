<?php
/**
 * Note Management Service Implementation
 * 
 * WHAT IS THIS?
 * Implementation of custom business logic beyond basic CRUD.
 * This demonstrates service contracts for complex operations.
 * 
 * REPOSITORY vs MANAGEMENT:
 * - Repository: Data persistence (save, get, delete)
 * - Management: Business logic (publish, process, calculate)
 */

namespace Dudenkoff\ApiLearn\Model;

use Dudenkoff\ApiLearn\Api\NoteManagementInterface;
use Dudenkoff\ApiLearn\Api\NoteRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

class NoteManagement implements NoteManagementInterface
{
    /**
     * @var NoteRepositoryInterface
     */
    private $noteRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor
     *
     * @param NoteRepositoryInterface $noteRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        NoteRepositoryInterface $noteRepository,
        LoggerInterface $logger
    ) {
        $this->noteRepository = $noteRepository;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function publish($noteId)
    {
        $note = $this->noteRepository->getById($noteId);
        
        // Business logic: Mark as published
        $note->setIsPublished(true);
        $note->setUpdatedAt(date('Y-m-d H:i:s'));
        
        // Save the note
        $note = $this->noteRepository->save($note);
        
        // Additional business logic
        $this->logger->info("Note published", [
            'note_id' => $noteId,
            'title' => $note->getTitle()
        ]);
        
        // In production: send notifications, update cache, etc.
        
        return $note;
    }

    /**
     * {@inheritdoc}
     */
    public function unpublish($noteId)
    {
        $note = $this->noteRepository->getById($noteId);
        
        $note->setIsPublished(false);
        $note->setUpdatedAt(date('Y-m-d H:i:s'));
        
        $note = $this->noteRepository->save($note);
        
        $this->logger->info("Note unpublished", ['note_id' => $noteId]);
        
        return $note;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatistics($noteId)
    {
        $note = $this->noteRepository->getById($noteId);
        
        // Calculate statistics (demo data)
        return [
            'note_id' => $note->getNoteId(),
            'title' => $note->getTitle(),
            'character_count' => strlen($note->getContent()),
            'word_count' => str_word_count($note->getContent()),
            'is_published' => $note->getIsPublished(),
            'created_at' => $note->getCreatedAt(),
            'updated_at' => $note->getUpdatedAt()
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function bulkPublish(array $noteIds)
    {
        $results = [];
        
        foreach ($noteIds as $noteId) {
            try {
                $this->publish($noteId);
                $results[$noteId] = [
                    'success' => true,
                    'message' => 'Published successfully'
                ];
            } catch (\Exception $e) {
                $results[$noteId] = [
                    'success' => false,
                    'message' => $e->getMessage()
                ];
            }
        }
        
        return $results;
    }
}

