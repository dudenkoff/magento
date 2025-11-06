# Block Cache Explained

## Your Block: `name="cache.demo"`

### Current Configuration

```xml
<page cacheable="false">  ← PAGE is not cacheable
    <body>
        <referenceContainer name="content">
            <block class="Dudenkoff\CacheLearn\Block\CacheDemo" 
                   name="cache.demo" 
                   template="Dudenkoff_CacheLearn::demo.phtml"
                   cacheable="false"/>  ← BLOCK is not cacheable
        </referenceContainer>
    </body>
</page>
```

### Answer: **NO cache file exists!**

**Why:** The block is set to `cacheable="false"` (and the page is too), so Magento doesn't cache it at all.

---

## Block Cache File Naming

If the block WAS cacheable, the filename would be:

```
mage---69d_BLOCK_<SHA256_HASH>
```

Where the hash is calculated from:
- Block class name
- Template path
- Block name
- Cache key (if specified)
- Additional cache key info

### Example from Your System

Looking at your cache directory, here's a cacheable block:

```
var/cache/mage--b/mage---69d_BLOCK_31494E2ECED4FC42134A20E9F5C8B35707633FB388557F784BB077C8B288FF12
```

**Breakdown:**
```
mage---69d_BLOCK_31494E2ECED4FC42134A20E9F5C8B35707633FB388557F784BB077C8B288FF12
│      │   │     │
│      │   │     └── SHA256 hash (64 hex characters)
│      │   └── Cache type identifier (BLOCK)
│      └── Cache prefix (69d_)
└── Magento cache prefix
```

---

## How to Make Your Block Cacheable

### Option 1: Remove cacheable="false" from Block

```xml
<page cacheable="true">  <!-- Must be true or remove -->
    <body>
        <referenceContainer name="content">
            <block class="Dudenkoff\CacheLearn\Block\CacheDemo" 
                   name="cache.demo" 
                   template="Dudenkoff_CacheLearn::demo.phtml"/>
                   <!-- Removed cacheable="false" -->
        </referenceContainer>
    </body>
</page>
```

**Result:** Block HTML will be cached

### Option 2: Add Cache Lifetime

```xml
<block class="Dudenkoff\CacheLearn\Block\CacheDemo" 
       name="cache.demo" 
       template="Dudenkoff_CacheLearn::demo.phtml"
       cacheable="true"
       cache_lifetime="3600"/>  <!-- Cache for 1 hour -->
```

### Option 3: Add Cache Key

```xml
<block class="Dudenkoff\CacheLearn\Block\CacheDemo" 
       name="cache.demo" 
       template="Dudenkoff_CacheLearn::demo.phtml"
       cacheable="true"
       cache_key="cache_demo_block"/>
```

---

## Calculate Expected Cache Filename

If you made it cacheable, here's how to find the filename:

### Method 1: Search by Block Identifier

```bash
# Find block cache files
find var/cache -name "*BLOCK*" -type f | while read file; do
    echo "=== $file ==="
    head -1 "$file"
done
```

### Method 2: Enable and Check

```bash
# 1. Make block cacheable in XML
# 2. Clear cache
rm -rf var/cache/*

# 3. Visit the page
curl http://your-site.com/cachelearn

# 4. Find the new block cache file
find var/cache -name "*BLOCK*" -type f -newermt "1 minute ago"
```

### Method 3: Calculate Hash (Advanced)

The hash is calculated from:

```php
// Pseudo-code for block cache key generation
$cacheKey = [
    'block_class' => 'Dudenkoff\CacheLearn\Block\CacheDemo',
    'template' => 'Dudenkoff_CacheLearn::demo.phtml',
    'name' => 'cache.demo',
    'store_id' => 1,
    'theme' => 'Magento/luma',
    // ... more parameters
];

$hash = hash('sha256', serialize($cacheKey));
$filename = "mage---69d_BLOCK_{$hash}";
```

**Result:** Something like:
```
mage---69d_BLOCK_A1B2C3D4E5F6...  (64 character hash)
```

---

## Experiment: See Block Cache in Action

### Step 1: Make Block Cacheable

Edit `view/frontend/layout/cachelearn_index_index.xml`:

```xml
<?xml version="1.0"?>
<page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
      xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd"
      cacheable="true">  <!-- Changed to true -->
    <body>
        <referenceContainer name="content">
            <block class="Dudenkoff\CacheLearn\Block\CacheDemo" 
                   name="cache.demo" 
                   template="Dudenkoff_CacheLearn::demo.phtml"
                   cacheable="true"
                   cache_lifetime="3600"/>
        </referenceContainer>
    </body>
</page>
```

### Step 2: Clear Cache and Visit

```bash
# Clear cache
rm -rf var/cache/* var/page_cache/*

# Visit page
curl http://your-site.com/cachelearn

# Find block cache file
find var/cache -name "*BLOCK*" -type f -newer /tmp 2>/dev/null | grep -v tags
```

### Step 3: Examine Cache File

```bash
# Find the file
BLOCK_FILE=$(find var/cache -name "mage---69d_BLOCK_*" -type f | head -1)

# View contents
cat "$BLOCK_FILE"
```

**You'll see:**
```
Line 1: Metadata (mtime, expire, tags)
Line 2: Cached HTML of the entire block
```

---

## Why Your Block Isn't Cached (And Shouldn't Be!)

### Reason 1: Dynamic Content

```php
// In your block (Block/CacheDemo.php)
public function getCurrentTime(): string
{
    return date('Y-m-d H:i:s');  // Changes every second!
}
```

If cached, time would be frozen! ❌

### Reason 2: Educational Purpose

```php
// You want to demonstrate custom cache, not block cache
public function getCachedTime(): string
{
    $cacheKey = 'demo_timestamp';
    
    $cached = $this->cacheService->loadSimpleData($cacheKey);
    // This is what you're teaching!
}
```

### Reason 3: Cache Demos Need Fresh Data

Your page SHOWS cache behavior - it can't be cached itself!

---

## Block Cache vs Custom Cache

### Block Cache (What You're NOT Using)

```
┌─────────────────────────────────┐
│  Entire Block HTML Cached       │
├─────────────────────────────────┤
│  File: mage---69d_BLOCK_<hash>  │
│  Contains: Full HTML output     │
│  Lifetime: Set in XML           │
│  Automatic: Yes (if enabled)    │
└─────────────────────────────────┘
```

**Example:**
```html
<!-- Entire block output cached -->
<div class="cache-demo">
    <h1>Demo</h1>
    <p>Current Time: 2025-11-03 19:30:00</p>
</div>
```

### Custom Cache (What You ARE Teaching)

```
┌─────────────────────────────────────────┐
│  Specific Data Cached                   │
├─────────────────────────────────────────┤
│  File: mage---69d_DUDENKOFF_..._<name>  │
│  Contains: Just the data                │
│  Lifetime: Set in code                  │
│  Manual: Yes (you control it)           │
└─────────────────────────────────────────┘
```

**Example:**
```
Just the timestamp string: "2025-11-03 19:30:00"
```

---

## Summary

### Your Current Setup

```
Page: cacheable="false"
Block: cacheable="false"
Result: NO block cache file
Custom Cache: Still works! (separate system)
```

### Cache Files That Exist

```bash
# Your custom cache files (what you're teaching)
var/cache/mage--7/mage---69d_DUDENKOFF_CACHELEARN_DEMO_TIMESTAMP
var/cache/mage--2/mage---69d_DUDENKOFF_CACHELEARN_EXPENSIVE_CALCULATION

# NO block cache file (by design)
# Would be: var/cache/mage--X/mage---69d_BLOCK_<hash> (doesn't exist)
```

### To Answer Your Question Directly

**Q: What's the cache file name for `name="cache.demo"` block?**

**A: There is NO cache file because:**
1. Block is set to `cacheable="false"`
2. Page is set to `cacheable="false"`
3. Even if cacheable, it would be: `mage---69d_BLOCK_<calculated_hash>`

**If you want to see block cache in action, change both to `cacheable="true"` and add `cache_lifetime="3600"`**

---

## Proof: Check Your Cache Directory

```bash
# Search for any block cache related to your module
find var/cache -name "*BLOCK*" -type f | xargs grep -l "CacheDemo" 2>/dev/null

# Expected output: (nothing - no block cache exists)

# But your custom cache files exist:
find var/cache -name "*DUDENKOFF*" -type f
# Output: Shows your custom cache files!
```

---

**Key Insight:** You're teaching **custom cache** (data caching), not **block cache** (HTML caching). That's why the block isn't cached - it's the canvas that SHOWS cache behavior, not the thing being cached! 🎯


