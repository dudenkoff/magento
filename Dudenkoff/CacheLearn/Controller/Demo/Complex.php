<?php
/**
 * Copyright © Dudenkoff. All rights reserved.
 * Complex Data Cache Demo
 * 
 * URL: /cachelearn/demo/complex
 * 
 * Demonstrates caching arrays and objects
 */

namespace Dudenkoff\CacheLearn\Controller\Demo;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Dudenkoff\CacheLearn\Service\CacheService;

class Complex implements HttpGetActionInterface
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
        $cacheKey = 'complex_example';
        
        // Use the "remember" pattern for clean code
        $data = $this->cacheService->remember($cacheKey, function() {
            // Simulate expensive operation (database query, API call, etc.)
            sleep(2); // Simulate 2 second delay
            
            return [
                'products' => [
                    ['id' => 1, 'name' => 'Product A', 'price' => 29.99],
                    ['id' => 2, 'name' => 'Product B', 'price' => 49.99],
                    ['id' => 3, 'name' => 'Product C', 'price' => 19.99],
                ],
                'generated_at' => date('Y-m-d H:i:s'),
                'calculation_time' => '2 seconds'
            ];
        }, 300); // Cache for 5 minutes
        
        $isCached = $this->cacheService->isCached($cacheKey);
        
        return $result->setData([
            'status' => $isCached ? 'cache_hit' : 'cache_miss',
            'message' => $isCached 
                ? 'Complex data loaded instantly from cache!' 
                : 'Expensive operation executed (2s delay), result cached.',
            'data' => $data,
            'from_cache' => $isCached,
            'tip' => 'Refresh to see how cache speeds up response time!'
        ]);
    }
}

