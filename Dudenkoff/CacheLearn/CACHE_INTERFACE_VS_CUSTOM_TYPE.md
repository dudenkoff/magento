# CacheInterface vs CustomCache: Design Choices

## The Question

**"Why inject `CacheInterface` and manually add tags when we could inject `CustomCache` directly?"**

You're absolutely right! We COULD do it differently. Let me show you both approaches.

---

## Approach 1: CacheInterface + Manual Tags (Our Current Choice)

### Implementation

```php
namespace Dudenkoff\CacheLearn\Service;

use Magento\Framework\App\CacheInterface;
use Dudenkoff\CacheLearn\Model\Cache\Type\CustomCache;

class CacheService
{
    private $cache;  // ← Generic CacheInterface
    
    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }
    
    public function saveSimpleData(string $key, string $value, ?int $lifetime = null): bool
    {
        $tags = [
            CustomCache::CACHE_TAG,  // ← Manually add tag
            'simple_data'            // ← Additional tag
        ];
        
        return $this->cache->save($value, $key, $tags, $lifetime);
    }
}
```

### How It Works

```
Request → CacheInterface → Generic cache backend
                              ↓
                    Tags: ['DUDENKOFF_CUSTOM', 'simple_data']
                              ↓
                    Save to cache with tags
```

---

## Approach 2: CustomCache Direct Injection (Your Suggestion)

### Implementation

```php
namespace Dudenkoff\CacheLearn\Service;

use Dudenkoff\CacheLearn\Model\Cache\Type\CustomCache;

class CacheService
{
    private $customCache;  // ← Specific CustomCache type
    
    public function __construct(CustomCache $customCache)
    {
        $this->customCache = $customCache;
    }
    
    public function saveSimpleData(string $key, string $value, ?int $lifetime = null): bool
    {
        $tags = [
            'simple_data'  // ← Only additional tags
        ];
        
        // CACHE_TAG automatically added by TagScope!
        return $this->customCache->save($value, $key, $tags, $lifetime);
    }
}
```

### How TagScope Works

```php
// In TagScope (parent of CustomCache)
public function save($data, $identifier, array $tags = [], $lifeTime = null)
{
    $tags[] = $this->getTag();  // ← Automatically adds DUDENKOFF_CUSTOM!
    return parent::save($data, $identifier, $tags, $lifeTime);
}
```

### How It Works

```
Request → CustomCache (extends TagScope)
              ↓
    TagScope.save() automatically adds CACHE_TAG
              ↓
    Tags: ['simple_data'] becomes ['simple_data', 'DUDENKOFF_CUSTOM']
              ↓
    Save to cache with tags
```

---

## Comparison

### Code Difference

**Approach 1 (Current):**
```php
public function __construct(CacheInterface $cache) {
    $this->cache = $cache;
}

$tags = [
    CustomCache::CACHE_TAG,  // ← Manual
    'simple_data'
];
$this->cache->save($value, $key, $tags, $lifetime);
```

**Approach 2 (Your Suggestion):**
```php
public function __construct(CustomCache $customCache) {
    $this->customCache = $customCache;
}

$tags = [
    'simple_data'  // ← No need for CustomCache::CACHE_TAG!
];
$this->customCache->save($value, $key, $tags, $lifetime);
```

---

## Pros and Cons

### Approach 1: CacheInterface + Manual Tags

#### Pros ✅

**1. Flexibility**
```php
// Can easily switch cache types
private $cache;  // Can be ANY cache type

// Use different cache types in different methods
public function saveConfig($data) {
    $this->cache->save($data, $key, ['CONFIG_TAG']);
}

public function saveCustom($data) {
    $this->cache->save($data, $key, ['CUSTOM_TAG']);
}
```

**2. Educational Value**
- Shows exactly how tags work
- Explicit tag management
- Teaches cache concepts better
- No "magic" behind the scenes

**3. Multiple Cache Types**
```php
// Could use multiple cache types in one service
public function __construct(
    CacheInterface $cache,
    \Magento\Framework\App\Cache\Type\Config $configCache
) {
    // Use generic for some, specific for others
}
```

**4. Tag Control**
```php
// Full control over tags per operation
public function saveWithSpecificTags($data) {
    $tags = $this->calculateDynamicTags();  // Custom logic
    $tags[] = CustomCache::CACHE_TAG;
    $this->cache->save($data, $key, $tags);
}
```

**5. Testing**
```php
// Easier to mock generic interface
$mockCache = $this->createMock(CacheInterface::class);
$service = new CacheService($mockCache);
```

#### Cons ❌

**1. Manual Tag Management**
```php
// Must remember to add tag manually
$tags = [
    CustomCache::CACHE_TAG,  // ← Easy to forget!
    'other_tag'
];
```

**2. More Verbose**
```php
// More code to write
$tags = [CustomCache::CACHE_TAG, ...];
```

**3. Potential Mistakes**
```php
// Might forget to add the tag
$this->cache->save($data, $key, ['other_tag']);  // Missing CACHE_TAG!
```

---

### Approach 2: CustomCache Direct

#### Pros ✅

**1. Automatic Tag Management**
```php
// Tag added automatically by TagScope
$this->customCache->save($data, $key, ['additional_tag']);
// Result: ['additional_tag', 'DUDENKOFF_CUSTOM']
```

**2. Less Code**
```php
// No need to manually add CustomCache::CACHE_TAG
$tags = ['simple_data'];  // Cleaner!
```

**3. Type Safety**
```php
// IDE knows exact type
private CustomCache $customCache;  // ← Type hint possible
```

**4. Can't Forget Tag**
```php
// Impossible to save without the cache type tag
// TagScope ALWAYS adds it
```

**5. Cache Type Binding**
```php
// Clearly bound to specific cache type
// Good for dedicated services
```

#### Cons ❌

**1. Less Flexible**
```php
// Locked to one cache type
private CustomCache $customCache;  // Can't easily change
```

**2. Harder to Test**
```php
// Must mock specific class
$mockCache = $this->createMock(CustomCache::class);
// More complex than mocking interface
```

**3. Tighter Coupling**
```php
// Service is coupled to CustomCache implementation
// Changes to CustomCache affect service
```

**4. Less Educational**
```php
// Tag management is "magic"
// Learners don't see how tags work
```

**5. Limited Multi-Type Usage**
```php
// Can't easily use multiple cache types
// Would need to inject each type separately
```

---

## Real-World Examples

### Magento Core Uses Both!

#### Example 1: Generic CacheInterface (Like Our Approach)

```php
// Magento's Config class
class Config
{
    private $cache;  // CacheInterface
    
    public function get($path)
    {
        $tags = [
            \Magento\Framework\App\Cache\Type\Config::CACHE_TAG,
            'config',
            'path_' . $path
        ];
        
        return $this->cache->save($data, $key, $tags);
    }
}
```

#### Example 2: Specific Cache Type (Your Suggestion)

```php
// Some Magento modules
class BlockRepository
{
    private $cache;  // Specific cache type
    
    public function __construct(
        \Magento\Framework\App\Cache\Type\Block $blockCache
    ) {
        $this->cache = $blockCache;
    }
    
    public function save($block)
    {
        // Tag automatically added by TagScope
        return $this->cache->save($block, $key);
    }
}
```

---

## Why We Chose Approach 1 (CacheInterface)

### For This Educational Module

**1. Teaching Purpose**
```php
// Students SEE how tags work
$tags = [
    CustomCache::CACHE_TAG,  // ← Explicitly visible
    'simple_data'
];

// vs

$tags = ['simple_data'];  // ← Where's the cache type tag? Hidden!
```

**2. Shows Best Practices**
```php
// Demonstrates:
// - How to use tags
// - How to manage cache keys
// - How to set lifetimes
// - Full control over caching
```

**3. Flexibility for Examples**
```php
// Can demonstrate multiple scenarios
public function exampleWithManyTags()
{
    $tags = [
        CustomCache::CACHE_TAG,
        'example_tag',
        'category_' . $id,
        'store_' . $storeId
    ];
    // Shows tag strategy clearly
}
```

**4. Easier to Understand**
```php
// Everything is explicit
// No hidden behavior
// Easier to debug
// Clear cache flow
```

---

## When to Use Each Approach

### Use CacheInterface When:

✅ **Building a flexible service**
```php
// Service that might use different cache types
class UniversalCacheService
{
    private $cache;  // Can be anything
}
```

✅ **Teaching/learning**
```php
// Want to show how things work
// Explicit is better than implicit
```

✅ **Need tag control**
```php
// Dynamic tags based on conditions
$tags = $this->calculateTags();
$tags[] = CustomCache::CACHE_TAG;
```

✅ **Testing extensively**
```php
// Easier to mock interface
$mock = $this->createMock(CacheInterface::class);
```

### Use CustomCache When:

✅ **Dedicated cache service**
```php
// Service only uses one cache type
class ProductCacheManager
{
    private $productCache;  // Only products
}
```

✅ **Production code (less verbose)**
```php
// Don't need to see implementation details
// Just want it to work
```

✅ **Type safety is critical**
```php
// Want IDE autocomplete
// Want type hints
private CustomCache $cache;
```

✅ **Guaranteed tag association**
```php
// Can never forget to add cache type tag
// Automatic tagging is safer
```

---

## Hybrid Approach (Best of Both)

You could even combine both:

```php
class CacheService
{
    private $cache;         // Generic for flexibility
    private $customCache;   // Specific for convenience
    
    public function __construct(
        CacheInterface $cache,
        CustomCache $customCache
    ) {
        $this->cache = $cache;
        $this->customCache = $customCache;
    }
    
    // Method 1: Explicit (educational)
    public function saveExplicit($key, $value)
    {
        $tags = [
            CustomCache::CACHE_TAG,
            'explicit_tag'
        ];
        return $this->cache->save($value, $key, $tags);
    }
    
    // Method 2: Automatic (convenient)
    public function saveAutomatic($key, $value)
    {
        $tags = ['auto_tag'];  // CACHE_TAG added automatically
        return $this->customCache->save($value, $key, $tags);
    }
}
```

---

## Refactoring Exercise

Want to try the other approach? Here's how:

### Change CacheService to Use CustomCache

```php
namespace Dudenkoff\CacheLearn\Service;

use Dudenkoff\CacheLearn\Model\Cache\Type\CustomCache;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;

class CacheService
{
    private $cache;  // ← Now CustomCache instead of CacheInterface
    private $serializer;
    private $logger;
    
    public function __construct(
        CustomCache $cache,  // ← Changed!
        SerializerInterface $serializer,
        LoggerInterface $logger
    ) {
        $this->cache = $cache;
        $this->serializer = $serializer;
        $this->logger = $logger;
    }
    
    public function saveSimpleData(string $key, string $value, ?int $lifetime = null): bool
    {
        $cacheKey = $this->getCacheKey($key);
        $lifetime = $lifetime ?? self::CACHE_LIFETIME;
        
        $tags = [
            'simple_data'  // ← Removed CustomCache::CACHE_TAG!
        ];
        
        // CACHE_TAG automatically added by TagScope!
        return $this->cache->save($value, $cacheKey, $tags, $lifetime);
    }
}
```

**Result:** 
- Less code ✅
- Automatic tagging ✅
- Less flexible ⚠️
- Less educational ⚠️

---

## Summary

### Your Question Was Great!

You identified that we could simplify by using `CustomCache` directly. You're absolutely right!

### Why We Didn't

**For an educational module, explicit is better than implicit:**

```php
// Current (explicit - good for learning)
$tags = [
    CustomCache::CACHE_TAG,  // ← You SEE what's happening
    'simple_data'
];
$this->cache->save($value, $key, $tags);

// Alternative (implicit - cleaner but hides details)
$tags = ['simple_data'];  // ← CACHE_TAG added magically
$this->customCache->save($value, $key, $tags);
```

### Both Approaches Are Valid!

- **CacheInterface:** Better for teaching, flexibility, testing
- **CustomCache:** Better for production, convenience, safety

### In Production

I'd probably use `CustomCache` directly for:
- Less code
- Automatic tagging
- Type safety
- Can't forget tags

### For Learning

I recommend `CacheInterface` because:
- Shows how everything works
- Nothing is hidden
- Better understanding
- Full control

---

## Action Item

Want to see both approaches? You could:

1. Keep current implementation for main examples
2. Add a "bonus" example showing CustomCache direct injection
3. Document both in README

Or just stick with current approach since it's more educational!

**Your question shows you understand the system deeply - that's the goal of this module! 🎉**

