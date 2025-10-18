<?php
/**
 * Heavy Service
 * 
 * DEMONSTRATES: Proxy Pattern
 * 
 * This service uses HeavyProcessor which is expensive to instantiate.
 * In di.xml, we inject HeavyProcessor\Proxy instead.
 * 
 * PROXY BENEFITS:
 * - HeavyProcessor is NOT instantiated when HeavyService is created
 * - It's only instantiated when you call a method on it
 * - Improves performance when the heavy object might not be needed
 * 
 * WHEN TO USE PROXIES:
 * - Heavy object that's not always used
 * - Breaking circular dependencies
 * - Lazy loading expensive resources
 */

namespace Dudenkoff\DILearn\Service;

use Dudenkoff\DILearn\Model\HeavyProcessor;

class HeavyService
{
    /**
     * @var HeavyProcessor
     */
    private $processor;

    /**
     * Constructor
     * 
     * Even though type-hint says HeavyProcessor, 
     * di.xml injects HeavyProcessor\Proxy
     *
     * @param HeavyProcessor $processor
     */
    public function __construct(
        HeavyProcessor $processor
    ) {
        // At this point, if using Proxy, HeavyProcessor is NOT instantiated yet!
        $this->processor = $processor;
    }

    /**
     * Process data (might not always be called)
     *
     * @param string $data
     * @return string
     */
    public function processData(string $data): string
    {
        // HeavyProcessor is instantiated HERE (if using Proxy)
        // Only when we actually call a method on it
        return $this->processor->process($data);
    }

    /**
     * Do something that doesn't need the processor
     *
     * @return string
     */
    public function doSomethingElse(): string
    {
        // HeavyProcessor is never instantiated if only this method is called
        return "Did something without using the heavy processor";
    }
}

