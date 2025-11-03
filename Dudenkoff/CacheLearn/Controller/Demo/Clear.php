<?php
/**
 * Copyright © Dudenkoff. All rights reserved.
 * Clear Cache Demo
 * 
 * URL: /cachelearn/demo/clear
 * 
 * Demonstrates cache invalidation
 */

namespace Dudenkoff\CacheLearn\Controller\Demo;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Dudenkoff\CacheLearn\Service\CacheService;

class Clear implements HttpGetActionInterface
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
        
        // Clear specific cache entries
        $this->cacheService->remove('simple_example');
        $this->cacheService->remove('complex_example');
        
        return $result->setData([
            'status' => 'success',
            'message' => 'Cache entries cleared successfully!',
            'cleared' => [
                'simple_example',
                'complex_example'
            ],
            'tip' => 'Now visit /cachelearn/demo/simple or /cachelearn/demo/complex to regenerate cache.'
        ]);
    }
}

