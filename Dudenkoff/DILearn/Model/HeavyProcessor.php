<?php
/**
 * Heavy Processor
 * 
 * DEMONSTRATES: When to use Proxies
 * 
 * This simulates a "heavy" object that:
 * - Takes time to instantiate
 * - Uses lots of resources
 * - Might not always be needed
 * 
 * PROXY PATTERN:
 * Instead of injecting this directly, inject HeavyProcessor\Proxy
 * The proxy delays instantiation until a method is actually called.
 */

namespace Dudenkoff\DILearn\Model;

class HeavyProcessor
{
    /**
     * @var bool
     */
    private $initialized = false;

    /**
     * Constructor simulates heavy initialization
     */
    public function __construct()
    {
        // Simulate heavy work during instantiation
        // In real scenarios: database connections, API calls, large data loading
        $this->initialized = true;
        // error_log('HeavyProcessor instantiated - this is expensive!');
    }

    /**
     * Process some data
     *
     * @param string $data
     * @return string
     */
    public function process(string $data): string
    {
        return strtoupper($data) . ' [PROCESSED]';
    }

    /**
     * Check if initialized
     *
     * @return bool
     */
    public function isInitialized(): bool
    {
        return $this->initialized;
    }
}

