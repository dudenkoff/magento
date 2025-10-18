<?php
/**
 * Basic Logger Implementation
 * 
 * This is a CONCRETE IMPLEMENTATION of LoggerInterface.
 * It provides the actual logic for the interface methods.
 * 
 * DEPENDENCY INJECTION EXAMPLE:
 * - This class can be injected wherever LoggerInterface is needed
 * - The di.xml preference maps LoggerInterface -> BasicLogger
 */

namespace Dudenkoff\DILearn\Model;

use Dudenkoff\DILearn\Api\LoggerInterface;

class BasicLogger implements LoggerInterface
{
    /**
     * @var array
     */
    private $messages = [];

    /**
     * @var string
     */
    private $prefix;

    /**
     * Constructor Dependency Injection
     * 
     * @param string $prefix Optional prefix for log messages (can be injected via di.xml)
     */
    public function __construct(
        string $prefix = '[LOG]'
    ) {
        $this->prefix = $prefix;
    }

    /**
     * {@inheritdoc}
     */
    public function log(string $message): void
    {
        $this->messages[] = $this->prefix . ' ' . $message;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessages(): array
    {
        return $this->messages;
    }
}

