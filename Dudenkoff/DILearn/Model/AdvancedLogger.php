<?php
/**
 * Advanced Logger Implementation
 * 
 * Alternative implementation of LoggerInterface.
 * This demonstrates how you can swap implementations easily.
 * 
 * TO USE THIS:
 * Uncomment the second preference in di.xml to switch from BasicLogger to this.
 */

namespace Dudenkoff\DILearn\Model;

use Dudenkoff\DILearn\Api\LoggerInterface;

class AdvancedLogger implements LoggerInterface
{
    /**
     * @var array
     */
    private $messages = [];

    /**
     * {@inheritdoc}
     */
    public function log(string $message): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $this->messages[] = "[{$timestamp}] [ADVANCED] {$message}";
    }

    /**
     * {@inheritdoc}
     */
    public function getMessages(): array
    {
        return $this->messages;
    }
}

