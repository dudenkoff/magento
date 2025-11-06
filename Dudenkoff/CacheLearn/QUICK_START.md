# Quick Start Guide - 5 Minutes to Caching

## Installation (2 minutes)

```bash
# 1. Enable module
bin/magento module:enable Dudenkoff_CacheLearn

# 2. Run setup
bin/magento setup:upgrade

# 3. Enable cache type
bin/magento cache:enable dudenkoff_custom_cache

# 4. Verify
bin/magento cache:status | grep dudenkoff
```

✅ You should see: `dudenkoff_custom_cache: 1`

## Try It Now (3 minutes)

### 1. Visit Demo Page

Open in browser:
```
http://your-magento-site.com/cachelearn
```

### 2. Test Simple Cache

Click "Test Simple Cache" or visit:
```
http://your-magento-site.com/cachelearn/demo/simple
```

**First call:** Cache miss - generates new data  
**Second call:** Cache hit - loads from cache!

### 3. Test Complex Cache

Click "Test Complex Cache" or visit:
```
http://your-magento-site.com/cachelearn/demo/complex
```

**First call:** 2 second delay (simulates expensive operation)  
**Refresh:** Instant response! (from cache)

### 4. Clear Cache

Click "Clear Demo Cache" or visit:
```
http://your-magento-site.com/cachelearn/demo/clear
```

Then try the demos again - you'll see cache miss and regeneration!

## Use in Your Code

### Basic Usage

```php
<?php

use Dudenkoff\CacheLearn\Service\CacheService;

class YourClass
{
    private $cacheService;
    
    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }
    
    public function getMyData()
    {
        // Try cache first, generate if not found
        return $this->cacheService->remember('my_key', function() {
            // Your expensive operation here
            return $this->loadFromDatabase();
        }, 3600); // Cache for 1 hour
    }
}
```

That's it! You're caching! 🎉

## Next Steps

1. **Read the code:** Open `Service/CacheService.php` and read the comments
2. **Try examples:** Experiment with the API endpoints
3. **Read docs:** Check out [README.md](README.md) for complete guide
4. **Implement:** Add caching to your own modules!

## Quick Reference

```php
// Save simple data
$this->cacheService->saveSimpleData('key', 'value', 3600);

// Load simple data
$value = $this->cacheService->loadSimpleData('key');

// Save array/object
$this->cacheService->saveComplexData('key', ['data' => 'value'], 3600);

// Load array/object
$data = $this->cacheService->loadComplexData('key');

// Check if cached
if ($this->cacheService->isCached('key')) {
    // ...
}

// Remove specific entry
$this->cacheService->remove('key');

// Cache-aside pattern (best!)
$data = $this->cacheService->remember('key', function() {
    return $expensiveOperation();
}, 3600);
```

## CLI Commands

```bash
# View cache status
bin/magento cache:status

# Enable your cache type
bin/magento cache:enable dudenkoff_custom_cache

# Clear your cache type
bin/magento cache:clean dudenkoff_custom_cache

# Flush all cache
bin/magento cache:flush
```

## Troubleshooting

**Cache not working?**
```bash
# Make sure it's enabled
bin/magento cache:status

# Enable it
bin/magento cache:enable

# Check logs
tail -f var/log/system.log
```

**Changes not appearing?**
```bash
# Clear cache
bin/magento cache:flush

# Clear generated files
rm -rf generated/* var/cache/*

# Run setup
bin/magento setup:upgrade
```

---

**Need help?** Check [README.md](README.md) for detailed documentation!

**Ready to dive deeper?** Read [BEST_PRACTICES.md](BEST_PRACTICES.md) for advanced patterns!


