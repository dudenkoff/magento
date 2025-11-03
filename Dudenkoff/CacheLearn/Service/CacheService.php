<?php
/**
 * Copyright © Dudenkoff. All rights reserved.
 * Cache Service - Educational Examples
 * 
 * This service demonstrates all the essential cache operations:
 * 1. Saving data to cache
 * 2. Loading data from cache
 * 3. Checking if data exists in cache
 * 4. Removing specific cache entries
 * 5. Cleaning cache by tags
 * 
 * IMPLEMENTATION APPROACH:
 * - Uses CustomCache (specific cache type) instead of CacheInterface
 * - CustomCache::CACHE_TAG is automatically added by TagScope
 * - Cleaner code with automatic tag management
 * - Tightly coupled to our custom cache type
 * 
 * CACHE BENEFITS:
 * - Faster data retrieval (memory vs database)
 * - Reduced database load
 * - Better performance
 * - Lower server costs
 * 
 * WHEN TO USE CACHE:
 * ✓ Data that doesn't change often
 * ✓ Expensive operations (complex queries, API calls)
 * ✓ Calculated results
 * ✓ Configuration data
 * 
 * WHEN NOT TO USE CACHE:
 * ✗ Frequently changing data
 * ✗ User-specific sensitive data
 * ✗ Simple operations (faster without cache overhead)
 */

namespace Dudenkoff\CacheLearn\Service;

use Magento\Framework\Serialize\SerializerInterface;
use Dudenkoff\CacheLearn\Model\Cache\Type\CustomCache;
use Psr\Log\LoggerInterface;

class CacheService
{
    /**
     * @var CustomCache
     */
    private $cache;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Cache lifetime in seconds
     * null = infinite (until manually cleared)
     * 3600 = 1 hour
     * 86400 = 1 day
     */
    const CACHE_LIFETIME = 3600; // 1 hour

    /**
     * @param CustomCache $cache
     * @param SerializerInterface $serializer
     * @param LoggerInterface $logger
     */
    public function __construct(
        CustomCache $cache,
        SerializerInterface $serializer,
        LoggerInterface $logger
    ) {
        $this->cache = $cache;
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    /**
     * EXAMPLE 1: Save simple string to cache
     * 
     * Note: CustomCache::CACHE_TAG is automatically added by TagScope!
     * We only need to specify additional tags here.
     * 
     * @param string $key Unique identifier for this cache entry
     * @param string $value Data to cache
     * @param int|null $lifetime Cache lifetime in seconds (null = infinite)
     * @return bool
     */
    public function saveSimpleData(string $key, string $value, ?int $lifetime = null): bool
    {
        $cacheKey = $this->getCacheKey($key);
        $lifetime = $lifetime ?? self::CACHE_LIFETIME;
        
        $tags = [
            'simple_data'  // CACHE_TAG automatically added by TagScope
        ];

        $this->logger->info("Saving to cache: {$cacheKey}");
        
        return $this->cache->save(
            $value,
            $cacheKey,
            $tags,
            $lifetime
        );
    }

    /**
     * EXAMPLE 2: Save complex data (arrays/objects) to cache
     * 
     * Complex data must be serialized before caching.
     * CustomCache::CACHE_TAG is automatically added by TagScope.
     * 
     * @param string $key
     * @param mixed $data Array or object to cache
     * @param int|null $lifetime
     * @return bool
     */
    public function saveComplexData(string $key, $data, ?int $lifetime = null): bool
    {
        $cacheKey = $this->getCacheKey($key);
        $lifetime = $lifetime ?? self::CACHE_LIFETIME;
        
        // Serialize complex data to string
        $serializedData = $this->serializer->serialize($data);
        
        $tags = [
            'complex_data'  // CACHE_TAG automatically added by TagScope
        ];

        $this->logger->info("Saving complex data to cache: {$cacheKey}");
        
        return $this->cache->save(
            $serializedData,
            $cacheKey,
            $tags,
            $lifetime
        );
    }

    /**
     * EXAMPLE 3: Load simple data from cache
     * 
     * @param string $key
     * @return string|false False if not found in cache
     */
    public function loadSimpleData(string $key)
    {
        $cacheKey = $this->getCacheKey($key);
        $data = $this->cache->load($cacheKey);
        
        if ($data === false) {
            $this->logger->info("Cache MISS: {$cacheKey}");
        } else {
            $this->logger->info("Cache HIT: {$cacheKey}");
        }
        
        return $data;
    }

    /**
     * EXAMPLE 4: Load complex data from cache
     * 
     * @param string $key
     * @return mixed|false Unserialized data or false if not found
     */
    public function loadComplexData(string $key)
    {
        $cacheKey = $this->getCacheKey($key);
        $data = $this->cache->load($cacheKey);
        
        if ($data === false) {
            $this->logger->info("Cache MISS: {$cacheKey}");
            return false;
        }
        
        $this->logger->info("Cache HIT: {$cacheKey}");
        
        // Unserialize data back to original format
        return $this->serializer->unserialize($data);
    }

    /**
     * EXAMPLE 5: Check if data exists in cache (without loading it)
     * 
     * @param string $key
     * @return bool
     */
    public function isCached(string $key): bool
    {
        $cacheKey = $this->getCacheKey($key);
        return $this->cache->load($cacheKey) !== false;
    }

    /**
     * EXAMPLE 6: Remove specific cache entry
     * 
     * @param string $key
     * @return bool
     */
    public function remove(string $key): bool
    {
        $cacheKey = $this->getCacheKey($key);
        $this->logger->info("Removing from cache: {$cacheKey}");
        return $this->cache->remove($cacheKey);
    }

    /**
     * EXAMPLE 7: Clear all cache entries with specific tag
     * 
     * This is useful when you want to clear a group of related cache entries
     * without clearing the entire cache type.
     * 
     * Note: When using CustomCache directly, the clean() method will
     * automatically include the CACHE_TAG in the cleaning scope.
     * 
     * @param string $tag
     * @return bool
     */
    public function cleanByTag(string $tag): bool
    {
        $this->logger->info("Cleaning cache by tag: {$tag}");
        return $this->cache->clean([\Zend_Cache::CLEANING_MODE_MATCHING_TAG, $tag]);
    }

    /**
     * EXAMPLE 8: Cache expensive operation result
     * 
     * This demonstrates the cache-aside pattern:
     * 1. Try to load from cache
     * 2. If not in cache, execute expensive operation
     * 3. Save result to cache
     * 4. Return result
     * 
     * @param string $key
     * @param callable $callback The expensive operation
     * @param int|null $lifetime
     * @return mixed
     */
    public function remember(string $key, callable $callback, ?int $lifetime = null)
    {
        // Try to load from cache first
        $cachedData = $this->loadComplexData($key);
        
        if ($cachedData !== false) {
            $this->logger->info("Using cached result for: {$key}");
            return $cachedData;
        }
        
        // Not in cache - execute the expensive operation
        $this->logger->info("Cache miss - executing operation for: {$key}");
        $result = $callback();
        
        // Save result to cache for next time
        $this->saveComplexData($key, $result, $lifetime);
        
        return $result;
    }

    /**
     * Generate cache key with module prefix
     * This ensures our keys don't conflict with other modules
     * 
     * @param string $key
     * @return string
     */
    private function getCacheKey(string $key): string
    {
        return 'dudenkoff_cachelearn_' . $key;
    }
}

