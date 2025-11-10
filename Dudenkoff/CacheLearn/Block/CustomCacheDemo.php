<?php
/**
 * Custom Cache Demo Block
 */
namespace Dudenkoff\CacheLearn\Block;

use Magento\Framework\View\Element\Template;
use Dudenkoff\CacheLearn\Model\Cache\Type\LearnCache;
use Dudenkoff\CacheLearn\Helper\CacheInfo;
use Magento\Framework\Serialize\SerializerInterface;

class CustomCacheDemo extends Template
{
    private $cache;
    private $cacheInfo;
    private $serializer;

    public function __construct(
        Template\Context $context,
        LearnCache $cache,
        CacheInfo $cacheInfo,
        SerializerInterface $serializer,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->cache = $cache;
        $this->cacheInfo = $cacheInfo;
        $this->serializer = $serializer;
    }

    /**
     * Save data to custom cache
     */
    public function saveToCache(string $key, $data, int $lifetime = 300): void
    {
        $serialized = $this->serializer->serialize($data);
        $this->cache->save($serialized, $key, [], $lifetime);
    }

    /**
     * Load data from custom cache
     */
    public function loadFromCache(string $key)
    {
        $data = $this->cache->load($key);
        if ($data) {
            return $this->serializer->unserialize($data);
        }
        return false;
    }

    /**
     * Get cached example data
     */
    public function getExampleData(): array
    {
        $key = 'learn_cache_example';
        $cached = $this->loadFromCache($key);
        
        if (!$cached) {
            $cached = [
                'generated_at' => date('Y-m-d H:i:s'),
                'random_number' => rand(1000, 9999),
                'message' => 'This data was just generated!'
            ];
            $this->saveToCache($key, $cached, 300);
        }
        
        return $cached;
    }

    /**
     * Get custom cache files
     */
    public function getCustomCacheFiles(): array
    {
        return $this->cacheInfo->findCacheFiles('LEARN_CACHE');
    }

    public function getCacheInfo(): CacheInfo
    {
        return $this->cacheInfo;
    }
}

