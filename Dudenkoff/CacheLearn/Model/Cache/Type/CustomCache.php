<?php
/**
 * Copyright © Dudenkoff. All rights reserved.
 * Custom Cache Type Implementation
 * 
 * WHAT IS THIS?
 * This class defines a custom cache type that appears in Magento's cache management.
 * It groups related cached data together under one cache type.
 * 
 * WHY USE CUSTOM CACHE TYPES?
 * - Organize cache data logically
 * - Allow selective cache clearing (clear only your cache, not everything)
 * - Better cache management for complex modules
 * - Appears in Admin and CLI for easy control
 * 
 * HOW IT WORKS:
 * 1. Extends Magento's base cache type
 * 2. Defines unique cache type identifier
 * 3. Sets cache tag for invalidation
 * 4. Automatically appears in cache management UI
 */

namespace Dudenkoff\CacheLearn\Model\Cache\Type;

use Magento\Framework\App\Cache\Type\FrontendPool;
use Magento\Framework\Cache\Frontend\Decorator\TagScope;

class CustomCache extends TagScope
{
    /**
     * Cache type code unique among all cache types
     * This is what you'll see in CLI and what you'll use when saving/loading cache
     */
    const TYPE_IDENTIFIER = 'dudenkoff_custom_cache';

    /**
     * Cache tag used to distinguish the cache type from all other cache types
     * Used for cache invalidation
     */
    const CACHE_TAG = 'DUDENKOFF_CUSTOM';

    /**
     * @param FrontendPool $cacheFrontendPool
     */
    public function __construct(
        FrontendPool $cacheFrontendPool
    ) {
        parent::__construct(
            $cacheFrontendPool->get(self::TYPE_IDENTIFIER),
            self::CACHE_TAG
        );
    }
}

