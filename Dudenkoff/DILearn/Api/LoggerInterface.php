<?php
/**
 * Logger Interface
 * 
 * WHAT IS AN INTERFACE?
 * - An interface is a contract that defines what methods a class MUST implement
 * - It defines WHAT to do, but not HOW to do it
 * - Multiple classes can implement the same interface differently
 * 
 * WHY USE INTERFACES IN DI?
 * - Loose coupling: Code depends on interface, not concrete implementation
 * - Flexibility: Easy to swap implementations without changing dependent code
 * - Testability: Easy to create mock implementations for testing
 */

namespace Dudenkoff\DILearn\Api;

interface LoggerInterface
{
    /**
     * Log a message
     *
     * @param string $message
     * @return void
     */
    public function log(string $message): void;

    /**
     * Get all logged messages
     *
     * @return array
     */
    public function getMessages(): array;
}

