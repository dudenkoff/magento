# Caching Best Practices & Patterns

## Table of Contents

1. [Cache Patterns](#cache-patterns)
2. [Performance Optimization](#performance-optimization)
3. [Cache Invalidation Strategies](#cache-invalidation-strategies)
4. [Common Pitfalls](#common-pitfalls)
5. [Security Considerations](#security-considerations)
6. [Testing & Debugging](#testing--debugging)

---

## Cache Patterns

### 1. Cache-Aside Pattern (Lazy Loading)

**Most Common Pattern**

```php
public function getData($id)
{
    $cacheKey = "data_{$id}";
    
    // Try cache first
    $data = $this->cache->load($cacheKey);
    
    if ($data === false) {
        // Cache miss - load from source
        $data = $this->loadFromDatabase($id);
        
        // Save to cache
        $this->cache->save(
            $this->serializer->serialize($data),
            $cacheKey,
            ['my_tag'],
            3600
        );
    } else {
        // Cache hit - deserialize
        $data = $this->serializer->unserialize($data);
    }
    
    return $data;
}
```

**Benefits:**
- ✅ Simple to implement
- ✅ Only caches data that's actually requested
- ✅ Easy to understand

**Drawbacks:**
- ❌ First request is always slow (cache miss)
- ❌ Multiple concurrent requests may duplicate work

---

### 2. Write-Through Pattern

**Data is written to cache AND database simultaneously**

```php
public function saveProduct($product)
{
    // Save to database
    $this->productRepository->save($product);
    
    // Immediately update cache
    $cacheKey = "product_{$product->getId()}";
    $this->cache->save(
        $this->serializer->serialize($product),
        $cacheKey,
        ['product_cache'],
        3600
    );
    
    return $product;
}
```

**Benefits:**
- ✅ Cache is always up-to-date
- ✅ No cache misses for recently saved data
- ✅ Consistent data

**Drawbacks:**
- ❌ Write operations are slower
- ❌ Cache may contain unused data

---

### 3. Write-Behind Pattern (Write-Back)

**Data is written to cache first, then asynchronously to database**

```php
public function updateCounter($key)
{
    // Update cache immediately
    $cacheKey = "counter_{$key}";
    $value = $this->cache->load($cacheKey);
    $value = $value ? (int)$value + 1 : 1;
    $this->cache->save($value, $cacheKey, ['counter'], 300);
    
    // Queue database update for later
    $this->messagePublisher->publish('counter.update', [
        'key' => $key,
        'value' => $value
    ]);
    
    return $value;
}
```

**Benefits:**
- ✅ Very fast writes
- ✅ Reduces database load
- ✅ Good for high-frequency updates

**Drawbacks:**
- ❌ Risk of data loss
- ❌ Complex to implement
- ❌ Eventual consistency

---

### 4. Read-Through Pattern

**Cache handles loading from source automatically**

```php
class CachingRepository
{
    public function getById($id)
    {
        return $this->cacheService->remember(
            "entity_{$id}",
            function() use ($id) {
                return $this->repository->getById($id);
            },
            3600
        );
    }
}
```

**Benefits:**
- ✅ Clean code
- ✅ Centralized cache logic
- ✅ Easy to maintain

**Drawbacks:**
- ❌ Requires abstraction layer
- ❌ First request is slow

---

### 5. Cache Warming (Pre-loading)

**Proactively load cache before it's needed**

```php
public function warmCache()
{
    // Load frequently accessed data into cache
    $popularProducts = $this->getPopularProducts();
    
    foreach ($popularProducts as $product) {
        $cacheKey = "product_{$product->getId()}";
        $this->cache->save(
            $this->serializer->serialize($product),
            $cacheKey,
            ['product_cache'],
            3600
        );
    }
}
```

**When to use:**
- After cache flush
- After deployment
- For known hot data
- During low-traffic periods

**Implementation:**
```bash
# Cron job or post-deployment script
bin/magento cache:flush
bin/magento mymodule:cache:warm
```

---

## Performance Optimization

### 1. Cache Key Design

**Bad:**
```php
// Too generic - collision risk
$key = "data";

// Too long - wastes memory
$key = "this_is_a_very_long_key_name_with_lots_of_details_" . $id;

// Dynamic parts that don't matter
$key = "data_" . time(); // Never hits cache!
```

**Good:**
```php
// Descriptive with necessary context
$key = "product_details_{$productId}";

// Include variation factors
$key = "category_tree_{$storeId}_{$customerGroup}";

// Use prefixes for namespacing
$key = "mymodule_api_response_{$endpoint}_{$hash}";
```

---

### 2. Cache Lifetime Strategy

```php
class CacheLifetimes
{
    // Static data that rarely changes
    const LIFETIME_VERY_LONG = 86400 * 7; // 1 week
    
    // Configuration, categories
    const LIFETIME_LONG = 86400; // 24 hours
    
    // Product data, prices
    const LIFETIME_MEDIUM = 3600; // 1 hour
    
    // Dynamic content
    const LIFETIME_SHORT = 300; // 5 minutes
    
    // Real-time but cacheable
    const LIFETIME_VERY_SHORT = 60; // 1 minute
    
    // Never expires (manual invalidation only)
    const LIFETIME_INFINITE = null;
}
```

**Decision Matrix:**

| Data Type | Lifetime | Reason |
|-----------|----------|--------|
| Site configuration | Very Long | Changes during deployment only |
| Category structure | Long | Admin changes infrequent |
| Product details | Medium | Inventory/price updates |
| Search results | Short | Catalog updates common |
| Live inventory | Very Short | Stock changes frequently |
| Cart totals | Don't cache | User-specific, changes often |

---

### 3. Selective Caching

**Don't cache everything - be strategic!**

```php
public function getProductData($productId)
{
    // Cache the expensive part only
    $baseData = $this->cacheService->remember(
        "product_base_{$productId}",
        fn() => $this->loadProductBase($productId),
        3600
    );
    
    // Don't cache user-specific data
    $wishlistStatus = $this->getWishlistStatus($productId);
    $customerPrice = $this->getCustomerPrice($productId);
    
    return array_merge($baseData, [
        'in_wishlist' => $wishlistStatus,
        'customer_price' => $customerPrice
    ]);
}
```

---

### 4. Cache Stampede Prevention

**Problem:** When cache expires, multiple requests try to regenerate it simultaneously.

**Solution: Use locking**

```php
public function getExpensiveData($key)
{
    $cacheKey = "data_{$key}";
    
    // Try cache
    $data = $this->cache->load($cacheKey);
    if ($data !== false) {
        return $this->serializer->unserialize($data);
    }
    
    // Use lock to prevent stampede
    $lockKey = "lock_{$cacheKey}";
    
    if (!$this->acquireLock($lockKey)) {
        // Another process is regenerating
        // Wait and try cache again
        sleep(1);
        $data = $this->cache->load($cacheKey);
        if ($data !== false) {
            return $this->serializer->unserialize($data);
        }
    }
    
    try {
        // Generate data
        $data = $this->generateExpensiveData($key);
        
        // Save to cache
        $this->cache->save(
            $this->serializer->serialize($data),
            $cacheKey,
            ['my_tag'],
            3600
        );
        
        return $data;
    } finally {
        $this->releaseLock($lockKey);
    }
}
```

---

## Cache Invalidation Strategies

> "There are only two hard things in Computer Science: cache invalidation and naming things." - Phil Karlton

### 1. Time-Based Expiration (TTL)

**Simplest approach:**

```php
// Expires automatically after 1 hour
$this->cache->save($data, $key, ['tag'], 3600);
```

**Pros:**
- ✅ Simple
- ✅ Automatic
- ✅ No manual invalidation needed

**Cons:**
- ❌ May serve stale data
- ❌ May regenerate when not needed

---

### 2. Event-Based Invalidation

**Invalidate when data changes:**

```php
// Observer
class InvalidateCacheObserver implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $product = $observer->getProduct();
        
        // Invalidate specific product cache
        $this->cacheService->remove("product_{$product->getId()}");
        
        // Invalidate related caches
        $this->cacheService->cleanByTag('product_list');
    }
}
```

**Configuration (etc/events.xml):**
```xml
<event name="catalog_product_save_after">
    <observer name="invalidate_product_cache" 
              instance="Vendor\Module\Observer\InvalidateCacheObserver"/>
</event>
```

---

### 3. Tag-Based Invalidation

**Group related cache entries:**

```php
// Save with multiple tags
$this->cache->save($data, $key, [
    'product_cache',
    'category_5',
    'sale_items'
], 3600);

// Later, invalidate all sale items
$this->cache->clean(['sale_items']);
```

**Tag Hierarchy Example:**
```php
private function getCacheTags($product)
{
    return [
        "product_{$product->getId()}",
        "category_{$product->getCategoryId()}",
        "brand_{$product->getBrandId()}",
        'all_products'
    ];
}
```

---

### 4. Version-Based Invalidation

**Change cache key when data structure changes:**

```php
class CacheManager
{
    const CACHE_VERSION = 'v2'; // Increment when structure changes
    
    private function getCacheKey($id)
    {
        return self::CACHE_VERSION . "_product_{$id}";
    }
}
```

---

## Common Pitfalls

### 1. ❌ Caching Objects Without Serialization

**Wrong:**
```php
$product = $this->productRepository->getById($id);
$this->cache->save($product, $key); // Object can't be cached!
```

**Right:**
```php
$product = $this->productRepository->getById($id);
$this->cache->save(
    $this->serializer->serialize($product->getData()),
    $key
);
```

---

### 2. ❌ Forgetting to Set Lifetime

**Wrong:**
```php
$this->cache->save($data, $key, ['tag']); // Infinite lifetime!
```

**Right:**
```php
$this->cache->save($data, $key, ['tag'], 3600); // 1 hour
```

---

### 3. ❌ Caching User-Specific Data in Shared Cache

**Wrong:**
```php
// Everyone gets the same cart!
$cart = $this->getCustomerCart();
$this->cache->save($cart, 'cart');
```

**Right:**
```php
// Each customer has own cache key
$customerId = $this->session->getCustomerId();
$cart = $this->getCustomerCart();
$this->cache->save($cart, "cart_{$customerId}");
```

---

### 4. ❌ Not Handling Cache Miss

**Wrong:**
```php
$data = $this->cache->load($key);
return $data; // Returns false on miss!
```

**Right:**
```php
$data = $this->cache->load($key);
if ($data === false) {
    $data = $this->generateData();
    $this->cache->save($data, $key);
}
return $data;
```

---

### 5. ❌ Cache Key Collision

**Wrong:**
```php
// Different data types, same key!
$this->cache->save($product, "item_{$id}");
$this->cache->save($category, "item_{$id}"); // Overwrites!
```

**Right:**
```php
$this->cache->save($product, "product_{$id}");
$this->cache->save($category, "category_{$id}");
```

---

## Security Considerations

### 1. Don't Cache Sensitive Data

**Never cache:**
- ❌ Passwords (even hashed)
- ❌ Credit card numbers
- ❌ Personal identification numbers
- ❌ Authentication tokens
- ❌ Session data (use session storage instead)

### 2. Validate Cache Data

```php
public function getCachedProduct($id)
{
    $data = $this->cache->load("product_{$id}");
    
    if ($data !== false) {
        $product = $this->serializer->unserialize($data);
        
        // Validate structure
        if (!isset($product['id'], $product['name'])) {
            $this->cache->remove("product_{$id}");
            return $this->loadFromDatabase($id);
        }
        
        return $product;
    }
    
    return $this->loadFromDatabase($id);
}
```

### 3. Sanitize Cache Keys

```php
private function sanitizeCacheKey($key)
{
    // Remove special characters
    $key = preg_replace('/[^a-zA-Z0-9_]/', '_', $key);
    
    // Limit length
    if (strlen($key) > 200) {
        $key = substr($key, 0, 200);
    }
    
    return $key;
}
```

---

## Testing & Debugging

### 1. Test Cache Hit/Miss

```php
public function testCacheHit()
{
    $key = 'test_key';
    $value = 'test_value';
    
    // Save
    $this->cacheService->saveSimpleData($key, $value);
    
    // Load
    $cached = $this->cacheService->loadSimpleData($key);
    
    $this->assertEquals($value, $cached);
}

public function testCacheMiss()
{
    $result = $this->cacheService->loadSimpleData('non_existent_key');
    $this->assertFalse($result);
}
```

### 2. Measure Cache Performance

```php
public function getCachedData($key)
{
    $startTime = microtime(true);
    
    $data = $this->cache->load($key);
    
    $loadTime = (microtime(true) - $startTime) * 1000;
    
    $this->logger->debug("Cache load time: {$loadTime}ms", [
        'key' => $key,
        'hit' => $data !== false
    ]);
    
    return $data;
}
```

### 3. Debug Cache Issues

```bash
# Check cache status
bin/magento cache:status

# Enable cache debugging in env.php
'cache' => [
    'frontend' => [
        'default' => [
            'backend_options' => [
                'enable_two_levels_cache' => true
            ]
        ]
    ]
]

# Monitor cache operations in logs
tail -f var/log/system.log | grep -i cache

# Check Redis (if using Redis)
redis-cli
> KEYS *
> GET "cache_key"
> TTL "cache_key"
```

### 4. Load Testing

```bash
# Use Apache Bench to test with/without cache
ab -n 1000 -c 10 http://your-site.com/cachelearn/demo/complex

# Compare results:
# With cache: ~5ms per request
# Without cache: ~2000ms per request
```

---

## Summary Checklist

**Before implementing cache:**
- [ ] Is this data expensive to generate?
- [ ] Does it change infrequently?
- [ ] Is it safe to cache (not user-specific/sensitive)?
- [ ] What's the appropriate lifetime?
- [ ] How will I invalidate it when it changes?

**When implementing:**
- [ ] Use descriptive cache keys
- [ ] Set appropriate lifetime
- [ ] Handle cache misses
- [ ] Add proper tags for invalidation
- [ ] Serialize complex data
- [ ] Consider cache stampede for popular data

**After implementing:**
- [ ] Test cache hit/miss scenarios
- [ ] Measure performance improvement
- [ ] Monitor cache size
- [ ] Set up proper invalidation
- [ ] Document cache strategy

---

## Real-World Examples

### Example 1: Product Collection

```php
public function getProductCollection($categoryId)
{
    return $this->cacheService->remember(
        "products_category_{$categoryId}",
        function() use ($categoryId) {
            return $this->collection
                ->addCategoryFilter($categoryId)
                ->addAttributeToSelect('*')
                ->setPageSize(20)
                ->load()
                ->getData();
        },
        3600 // 1 hour
    );
}
```

### Example 2: API Response

```php
public function getWeatherData($city)
{
    return $this->cacheService->remember(
        "weather_" . strtolower($city),
        function() use ($city) {
            $response = $this->httpClient->get(
                "https://api.weather.com/city/{$city}"
            );
            return json_decode($response->getBody(), true);
        },
        1800 // 30 minutes
    );
}
```

### Example 3: Expensive Calculation

```php
public function getMonthlyReport($month)
{
    return $this->cacheService->remember(
        "monthly_report_{$month}",
        function() use ($month) {
            $orders = $this->orderCollection->addMonthFilter($month);
            
            return [
                'total_revenue' => $orders->getTotalRevenue(),
                'order_count' => $orders->count(),
                'average_order' => $orders->getAverageOrderValue(),
                'top_products' => $orders->getTopProducts(10)
            ];
        },
        86400 // 24 hours
    );
}
```

---

## Additional Resources

- [README.md](README.md) - Getting started guide
- [CACHE_TYPES.md](CACHE_TYPES.md) - All cache types explained
- [Magento DevDocs](https://devdocs.magento.com/)

---

**Happy Caching! 🚀**

Remember: Good caching improves performance, bad caching causes bugs!

