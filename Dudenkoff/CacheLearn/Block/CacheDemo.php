<?php
/**
 * Copyright © Dudenkoff. All rights reserved.
 * Cache Demo Block
 * 
 * Demonstrates cache usage in blocks
 */

namespace Dudenkoff\CacheLearn\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Dudenkoff\CacheLearn\Service\CacheService;

class CacheDemo extends Template
{
    /**
     * @var CacheService
     */
    private $cacheService;

    /**
     * @param Context $context
     * @param CacheService $cacheService
     * @param array $data
     */
    public function __construct(
        Context $context,
        CacheService $cacheService,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->cacheService = $cacheService;
    }

    /**
     * Get expensive calculation result (cached)
     * 
     * This demonstrates how to cache expensive operations in blocks
     * 
     * @return int
     */
    public function getExpensiveCalculation(): int
    {
        return $this->cacheService->remember('expensive_calculation', function() {
            // Simulate expensive calculation
            $result = 0;
            for ($i = 0; $i < 1000000; $i++) {
                $result += $i;
            }
            return $result;
        }, 600); // Cache for 10 minutes
    }

    /**
     * Get current timestamp (NOT cached)
     * 
     * Compare this with cached data to see the difference
     * 
     * @return string
     */
    public function getCurrentTime(): string
    {
        return date('Y-m-d H:i:s');
    }

    /**
     * Get cached timestamp
     * 
     * This will show when the cache was first created
     * 
     * @return string
     */
    public function getCachedTime(): string
    {
        $cacheKey = 'demo_timestamp';
        
        $cached = $this->cacheService->loadSimpleData($cacheKey);
        
        if ($cached === false) {
            $timestamp = date('Y-m-d H:i:s');
            $this->cacheService->saveSimpleData($cacheKey, $timestamp, 300);
            return $timestamp . ' (just cached)';
        }
        
        return $cached . ' (from cache)';
    }

    /**
     * Check if cache is working
     * 
     * @return bool
     */
    public function isCacheEnabled(): bool
    {
        return $this->cacheService->isCached('simple_example') || 
               $this->cacheService->isCached('complex_example');
    }
}

