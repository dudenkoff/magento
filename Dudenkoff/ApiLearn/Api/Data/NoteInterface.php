<?php
/**
 * Note Data Interface
 * 
 * WHAT IS A DATA INTERFACE?
 * A data interface defines the structure of a data object.
 * It's a contract that says "a Note must have these properties".
 * 
 * KEY CONCEPTS:
 * 1. Getters - Methods to retrieve data
 * 2. Setters - Methods to set data (return $this for chaining)
 * 3. Constants - Field names to avoid magic strings
 * 4. Type hints - Strict typing for API contracts
 * 
 * WHY USE INTERFACES?
 * - API stability (interface doesn't change, implementation can)
 * - Multiple implementations possible
 * - Clear contract for API consumers
 * - Auto-generates API documentation
 * 
 * MAGENTO CONVENTIONS:
 * - Located in Api/Data/ directory
 * - Named {Entity}Interface
 * - Getters: get{Field}()
 * - Setters: set{Field}($value)
 * - Setters return $this for method chaining
 */

namespace Dudenkoff\ApiLearn\Api\Data;

interface NoteInterface
{
    /**
     * Constants for field names
     * 
     * Use these instead of magic strings:
     * Good: $note->setData(NoteInterface::TITLE, 'value');
     * Bad:  $note->setData('title', 'value');
     */
    const NOTE_ID = 'note_id';
    const TITLE = 'title';
    const CONTENT = 'content';
    const AUTHOR = 'author';
    const IS_PUBLISHED = 'is_published';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * Get note ID
     *
     * @return int|null
     */
    public function getNoteId();

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle();

    /**
     * Get content
     *
     * @return string
     */
    public function getContent();

    /**
     * Get author
     *
     * @return string|null
     */
    public function getAuthor();

    /**
     * Get published status
     *
     * @return bool
     */
    public function getIsPublished();

    /**
     * Get creation time
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Get update time
     *
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set note ID
     *
     * @param int $noteId
     * @return $this
     */
    public function setNoteId($noteId);

    /**
     * Set title
     *
     * @param string $title
     * @return $this
     */
    public function setTitle($title);

    /**
     * Set content
     *
     * @param string $content
     * @return $this
     */
    public function setContent($content);

    /**
     * Set author
     *
     * @param string $author
     * @return $this
     */
    public function setAuthor($author);

    /**
     * Set published status
     *
     * @param bool $isPublished
     * @return $this
     */
    public function setIsPublished($isPublished);

    /**
     * Set creation time
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Set update time
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt);
}


