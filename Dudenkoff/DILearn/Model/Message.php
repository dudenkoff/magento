<?php
/**
 * Message Model
 * 
 * DEMONSTRATES: Factory Pattern
 * 
 * Magento automatically generates a Factory class for this:
 * Dudenkoff\DILearn\Model\MessageFactory
 * 
 * WHEN TO USE FACTORIES:
 * - When you need to create NEW instances dynamically
 * - When the object is not a dependency but needs to be created
 * - For objects that represent data (like this Message)
 * 
 * HOW TO USE:
 * 1. Inject MessageFactory (not Message) in constructor
 * 2. Call $this->messageFactory->create() to get new instance
 * 3. Optionally pass data: $factory->create(['data' => [...]])
 */

namespace Dudenkoff\DILearn\Model;

class Message
{
    /**
     * @var string
     */
    private $text;

    /**
     * @var string
     */
    private $author;

    /**
     * @var int
     */
    private $timestamp;

    /**
     * Constructor
     *
     * @param array $data Initial data
     */
    public function __construct(array $data = [])
    {
        $this->text = $data['text'] ?? '';
        $this->author = $data['author'] ?? 'Unknown';
        $this->timestamp = $data['timestamp'] ?? time();
    }

    /**
     * Set message text
     *
     * @param string $text
     * @return $this
     */
    public function setText(string $text): self
    {
        $this->text = $text;
        return $this;
    }

    /**
     * Get message text
     *
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * Set author
     *
     * @param string $author
     * @return $this
     */
    public function setAuthor(string $author): self
    {
        $this->author = $author;
        return $this;
    }

    /**
     * Get author
     *
     * @return string
     */
    public function getAuthor(): string
    {
        return $this->author;
    }

    /**
     * Get timestamp
     *
     * @return int
     */
    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    /**
     * Get formatted message
     *
     * @return string
     */
    public function getFormatted(): string
    {
        return sprintf(
            '[%s] %s: %s',
            date('Y-m-d H:i:s', $this->timestamp),
            $this->author,
            $this->text
        );
    }
}

