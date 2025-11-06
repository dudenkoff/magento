<?php
/**
 * Copyright © Dudenkoff. All rights reserved.
 * Simple Cache Demo
 * 
 * URL: /cachelearn/demo/simple
 * 
 * Demonstrates basic cache operations with simple strings
 */

namespace Dudenkoff\CacheLearn\Controller\Demo;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Dudenkoff\CacheLearn\Service\CacheService;

class Simple implements HttpGetActionInterface
{
    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * @var CacheService
     */
    private $cacheService;

    /**
     * @param JsonFactory $jsonFactory
     * @param CacheService $cacheService
     */
    public function __construct(
        JsonFactory $jsonFactory,
        CacheService $cacheService
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->cacheService = $cacheService;
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->jsonFactory->create();
        
        $cacheKey = 'simple_example';
        
        // Try to load from cache
        $cachedValue = $this->cacheService->loadSimpleData($cacheKey);
        
        if ($cachedValue === false) {
            // Not in cache - simulate expensive operation
            $value = 'This is cached data generated at ' . date('Y-m-d H:i:s');
            
            // Save to cache for 60 seconds
            $this->cacheService->saveSimpleData($cacheKey, $value, 60);
            
            return $result->setData([
                'status' => 'cache_miss',
                'message' => 'Data was NOT in cache. Generated new data and saved to cache.',
                'data' => $value,
                'from_cache' => false,
                'tip' => 'Refresh the page within 60 seconds to see cached data!'
            ]);
        }
        
        return $result->setData([
            'status' => 'cache_hit',
            'message' => 'Data loaded from cache!',
            'data' => $cachedValue,
            'from_cache' => true,
            'tip' => 'This data will expire 60 seconds after it was first cached.'
        ]);
    }
}


