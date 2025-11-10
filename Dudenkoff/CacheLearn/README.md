# Magento 2 Cache Learning Module

Complete, hands-on module for learning **all types of Magento caching** with real examples and file-level visibility.

## 🎯 What You'll Learn

1. **Block Cache** - How individual blocks can be cached
2. **Full Page Cache (FPC)** - How entire pages are cached
3. **Custom Cache** - How to create and use custom cache types
4. **Cache Files** - Where cache is stored and how to find your cache files
5. **Cache Management** - CLI commands to manage cache

## 📦 Installation

```bash
cd /path/to/magento

# Enable module
bin/magento module:enable Dudenkoff_CacheLearn

# Run setup
bin/magento setup:upgrade

# Enable the custom cache type
bin/magento cache:enable learn_cache

# Clear cache
bin/magento cache:flush
```

## 🚀 Quick Start

### Page 1: Block Cache Demo
```
URL: http://your-site.com/cachelearn/blockcache
```

**What you'll see:**
- 🔴 **Non-Cacheable Block** - Time updates every refresh
- 🟢 **Cacheable Block** - Time frozen for 5 minutes
- Real cache file paths in `var/cache/`
- CLI commands to manage block cache

### Page 2: Custom Cache Demo
```
URL: http://your-site.com/cachelearn/customcache
```

**What you'll see:**
- 🔵 **Custom cached data** - Random number cached for 5 minutes
- Actual cache files in `var/cache/`
- How to save/load custom data
- CLI commands for custom cache

## 📚 Module Structure

```
Dudenkoff/CacheLearn/
├── Model/Cache/Type/
│   └── LearnCache.php          # Custom cache type definition
├── Helper/
│   └── CacheInfo.php           # Helper to show cache file paths
├── Block/
│   ├── CacheableBlock.php      # Cacheable block
│   ├── NonCacheableBlock.php   # Non-cacheable block
│   └── CustomCacheDemo.php     # Custom cache demo
├── Controller/
│   ├── BlockCache/Index.php    # Page 1 controller
│   └── CustomCache/Index.php   # Page 2 controller
└── view/frontend/
    ├── layout/
    │   ├── cachelearn_blockcache_index.xml
    │   └── cachelearn_customcache_index.xml
    └── templates/
        ├── cacheable_block.phtml
        ├── noncacheable_block.phtml
        └── custom_cache_demo.phtml
```

## 🔍 Understanding Cache Types

### 1. Block Cache

**What:** Caches individual block HTML output

**When to use:**
- Reusable blocks (header, footer, menus)
- Product lists, widgets
- Content that doesn't change often

**File location:**
```
var/cache/mage--X/mage---69d_BLOCK_<SHA256_HASH>
```

**In layout XML:**
```xml
<block cacheable="true">
    <arguments>
        <argument name="cache_lifetime" xsi:type="number">300</argument>
    </arguments>
</block>
```

**Pros:**
- ✅ Fast - HTML is pre-generated
- ✅ Selective - only cache what you want
- ✅ Configurable lifetime per block

**Cons:**
- ❌ Can't cache user-specific content
- ❌ Must set cacheable="false" on page if page has dynamic content

### 2. Full Page Cache (FPC)

**What:** Caches entire page HTML

**When to use:**
- Product pages
- Category pages
- CMS pages
- Public content

**File location:**
```
var/page_cache/mage--X/mage---69d_<HASH>
```

**In layout XML:**
```xml
<page cacheable="true">
```

**Pros:**
- ✅ Fastest - entire page served from cache
- ✅ Huge performance boost
- ✅ Can use Varnish for even better performance

**Cons:**
- ❌ Not suitable for dynamic/user-specific pages
- ❌ All blocks must be cacheable or use cache holes

### 3. Custom Cache (Your Data)

**What:** Cache your specific data (arrays, objects, strings)

**When to use:**
- Database query results
- API responses
- Expensive calculations
- Configuration data

**File location:**
```
var/cache/mage--X/mage---69d_LEARN_CACHE_<YOUR_KEY>
```

**In code:**
```php
// Save
$this->cache->save($data, $key, $tags, $lifetime);

// Load
$data = $this->cache->load($key);
```

**Pros:**
- ✅ Full control over what's cached
- ✅ Works with any data type (after serialization)
- ✅ Independent of page/block cache
- ✅ Can cache user-specific data with unique keys

**Cons:**
- ❌ Requires manual implementation
- ❌ Must handle serialization/deserialization
- ❌ Need to manage cache invalidation

## 💻 CLI Commands Reference

### View Cache Status
```bash
bin/magento cache:status

# You should see:
# learn_cache                            1  (enabled)
```

### Enable/Disable Cache

```bash
# Enable custom cache
bin/magento cache:enable learn_cache

# Enable block cache
bin/magento cache:enable block_html

# Enable FPC
bin/magento cache:enable full_page

# Disable all for development
bin/magento cache:disable
```

### Clean/Flush Cache

```bash
# Clean custom cache (removes invalid entries)
bin/magento cache:clean learn_cache

# Flush custom cache (removes all entries)
bin/magento cache:flush learn_cache

# Clean specific types
bin/magento cache:clean block_html full_page

# Flush everything
bin/magento cache:flush
```

### Find Cache Files

```bash
# Find custom cache files
find var/cache -name "*LEARN_CACHE*" -type f

# Find block cache files
find var/cache -name "*BLOCK*" -type f

# Find page cache files
find var/page_cache -name "mage---*" -type f

# View cache file content
cat var/cache/mage--X/mage---69d_LEARN_CACHE_EXAMPLE
```

### Remove Cache Files Manually

```bash
# Remove custom cache files
rm -rf var/cache/*/mage---*LEARN_CACHE*

# Remove block cache files
rm -rf var/cache/*/mage---*BLOCK*

# Remove all page cache
rm -rf var/page_cache/*

# Remove everything
rm -rf var/cache/* var/page_cache/*
```

## 🧪 Testing Scenarios

### Test 1: Block Cache Behavior

```bash
# 1. Visit block cache page
curl http://localhost/cachelearn/blockcache

# 2. Note both timestamps
# 3. Wait 3 seconds and refresh
# 4. Observe:
#    - Red block (non-cacheable): Time CHANGES ✅
#    - Green block (cacheable): Time FROZEN ✅

# 5. Clear block cache
bin/magento cache:clean block_html

# 6. Refresh page
#    - Green block: New time generated ✅
```

### Test 2: Custom Cache Behavior

```bash
# 1. Visit custom cache page
curl http://localhost/cachelearn/customcache

# 2. Note the random number
# 3. Refresh multiple times
# 4. Observe: SAME random number (cached) ✅

# 5. Clear custom cache
bin/magento cache:clean learn_cache

# 6. Refresh page
# 7. Observe: NEW random number ✅
```

### Test 3: Cache File Location

```bash
# 1. Clear all cache
rm -rf var/cache/* var/page_cache/*

# 2. Visit custom cache page
curl http://localhost/cachelearn/customcache

# 3. Find the cache file
find var/cache -name "*LEARN_CACHE*" -type f

# 4. View the file
ls -lh var/cache/mage--*/mage---*LEARN_CACHE*

# 5. See the content
cat var/cache/mage--X/mage---69d_LEARN_CACHE_EXAMPLE
```

## 📖 Cache File Structure

All Magento cache files have the same structure:

```
Line 1: Metadata (PHP serialized array)
        - hash: Checksum
        - mtime: Modified time
        - expire: Expiration timestamp
        - tags: Cache tags

Line 2: Your cached data
```

**Example:**
```
a:4:{s:4:"hash";s:0:"";s:5:"mtime";i:1234567890;s:6:"expire";i:1234568190;s:4:"tags";s:16:"69d_LEARN_CACHE";}
{"generated_at":"2025-11-06 12:00:00","random_number":1234}
```

## 🎓 Learning Path

### Beginner (30 minutes)

1. ✅ Install the module
2. ✅ Visit both demo pages
3. ✅ Observe cache behavior
4. ✅ Run the CLI commands

### Intermediate (1 hour)

1. ✅ Find cache files on disk
2. ✅ View cache file contents
3. ✅ Clear specific cache types
4. ✅ Understand the difference between block/page/custom cache

### Advanced (2+ hours)

1. ✅ Read all the source code
2. ✅ Modify cache lifetimes
3. ✅ Create your own custom cache type
4. ✅ Implement caching in your own module

## 🔑 Key Takeaways

### Block Cache
```
✓ Caches: Block HTML
✓ Location: var/cache/*/BLOCK_*
✓ Control: Layout XML (cacheable="true")
✓ Use for: Reusable blocks
```

### Full Page Cache
```
✓ Caches: Entire page HTML
✓ Location: var/page_cache/
✓ Control: Layout XML (page cacheable="true")
✓ Use for: Public pages
```

### Custom Cache
```
✓ Caches: Your data
✓ Location: var/cache/*/YOUR_CACHE_TAG*
✓ Control: PHP code
✓ Use for: Database results, API calls, calculations
```

## ❓ Troubleshooting

### Cache not working?

```bash
# Check if cache type is enabled
bin/magento cache:status | grep learn_cache

# Enable it
bin/magento cache:enable learn_cache

# Clear and rebuild
bin/magento cache:flush
bin/magento setup:upgrade
```

### Can't find cache files?

```bash
# Make sure you visited the page first
curl http://localhost/cachelearn/customcache

# Then search
find var/cache -type f -newermt "1 minute ago"
```

### Time still not updating?

```bash
# Clear ALL cache
rm -rf var/cache/* var/page_cache/* generated/*

# Disable FPC for development
bin/magento cache:disable full_page
```

## 📝 Summary

This module teaches you **everything about Magento caching** through:

- ✅ **Live demos** - See cache in action
- ✅ **File visibility** - See actual cache files
- ✅ **CLI commands** - Learn cache management
- ✅ **Real examples** - Block, Page, and Custom cache
- ✅ **Complete code** - Learn by reading source

**URLs:**
- Block Cache: `/cachelearn/blockcache`
- Custom Cache: `/cachelearn/customcache`

**Now you fully understand Magento caching!** 🎉

