<?php
/**
 * Note Model
 * 
 * WHAT IS A MODEL?
 * The concrete implementation of the data interface.
 * Extends AbstractModel for Magento ORM functionality.
 * 
 * KEY FEATURES:
 * - Implements data interface
 * - Uses magic getters/setters via getData/setData
 * - _init() connects to resource model
 * - Can add custom methods
 * 
 * INHERITANCE:
 * AbstractModel provides:
 * - save(), load(), delete()
 * - getData(), setData()
 * - Dirty data tracking
 * - Event dispatching
 * - Caching
 * 
 * BEST PRACTICES:
 * - Keep models thin (business logic in services)
 * - Use constants from interface
 * - Type hint return values
 * - Implement all interface methods
 */

namespace Dudenkoff\ApiLearn\Model;

use Dudenkoff\ApiLearn\Api\Data\NoteInterface;
use Magento\Framework\Model\AbstractModel;

class Note extends AbstractModel implements NoteInterface
{
    /**
     * Cache tag
     */
    const CACHE_TAG = 'dudenkoff_note';

    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * @var string
     */
    protected $_eventPrefix = 'dudenkoff_note';

    /**
     * Initialize resource model
     * 
     * Connects this model to its resource model for database operations
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Dudenkoff\ApiLearn\Model\ResourceModel\Note::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getNoteId()
    {
        return $this->getData(self::NOTE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * {@inheritdoc}
     */
    public function getContent()
    {
        return $this->getData(self::CONTENT);
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthor()
    {
        return $this->getData(self::AUTHOR);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsPublished()
    {
        return (bool) $this->getData(self::IS_PUBLISHED);
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setNoteId($noteId)
    {
        return $this->setData(self::NOTE_ID, $noteId);
    }

    /**
     * {@inheritdoc}
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * {@inheritdoc}
     */
    public function setContent($content)
    {
        return $this->setData(self::CONTENT, $content);
    }

    /**
     * {@inheritdoc}
     */
    public function setAuthor($author)
    {
        return $this->setData(self::AUTHOR, $author);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsPublished($isPublished)
    {
        return $this->setData(self::IS_PUBLISHED, $isPublished);
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}


