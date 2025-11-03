# Cache File Structure Explained

## Your Cache File

**Filename:** `mage---69d_DUDENKOFF_CACHELEARN_DEMO_TIMESTAMP`

**Contents:**
```
Line 1: a:4:{s:4:"hash";s:0:"";s:5:"mtime";i:1762190192;s:6:"expire";i:1762190492;s:4:"tags";s:36:"69d_DUDENKOFF_CUSTOM,69d_SIMPLE_DATA";}
Line 2: 2025-11-03 17:16:32
```

---

## Part 1: Filename Structure

### Breakdown: `mage---69d_DUDENKOFF_CACHELEARN_DEMO_TIMESTAMP`

```
mage---69d_DUDENKOFF_CACHELEARN_DEMO_TIMESTAMP
│     │   │
│     │   └── Cache Key (transformed)
│     └── Cache Prefix (3-char hash)
└── Magento Cache Prefix
```

#### 1. `mage---` 
**Magento's File Cache Prefix**
- Fixed prefix for all Magento cache files
- Helps identify cache files vs other files

#### 2. `69d_`
**Cache Backend Prefix (3-character hash)**
- Generated from your cache configuration
- Ensures cache isolation between different stores/environments
- Comes from `app/etc/env.php` cache configuration
- Usually a hash of the cache backend settings

#### 3. `DUDENKOFF_CACHELEARN_DEMO_TIMESTAMP`
**Your Cache Key (transformed to uppercase)**

The transformation happens like this:

```php
// In your code (Block/CacheDemo.php line 76):
$cacheKey = 'demo_timestamp';

// Service adds prefix (Service/CacheService.php line 264):
$finalKey = 'dudenkoff_cachelearn_' . $key;  
// Result: 'dudenkoff_cachelearn_demo_timestamp'

// Magento transforms for filesystem:
// 1. Converts to uppercase
// 2. Adds backend prefix
// Final filename: mage---69d_DUDENKOFF_CACHELEARN_DEMO_TIMESTAMP
```

---

## Part 2: File Contents

### Line 1: Metadata (Serialized PHP Array)

```php
a:4:{s:4:"hash";s:0:"";s:5:"mtime";i:1762190192;s:6:"expire";i:1762190492;s:4:"tags";s:36:"69d_DUDENKOFF_CUSTOM,69d_SIMPLE_DATA";}
```

This is **PHP serialized data**. Let's decode it:

```php
// Unserialized structure:
[
    'hash' => '',                              // Checksum (empty = not used)
    'mtime' => 1762190192,                     // Modified time (Unix timestamp)
    'expire' => 1762190492,                    // Expiration time (Unix timestamp)
    'tags' => '69d_DUDENKOFF_CUSTOM,69d_SIMPLE_DATA'  // Cache tags
]
```

#### Metadata Fields Explained:

**1. `hash`**
```
Value: "" (empty)
Purpose: Optional checksum for data integrity
Status: Not used in this case
```

**2. `mtime` (Modified Time)**
```
Value: 1762190192
Converted: Sun Nov 03 2025 17:16:32 GMT
Purpose: When the cache entry was created/modified
```

**3. `expire` (Expiration Time)**
```
Value: 1762190492
Converted: Sun Nov 03 2025 17:21:32 GMT
Purpose: When the cache entry will expire
Lifetime: 1762190492 - 1762190192 = 300 seconds (5 minutes)
```

**4. `tags`**
```
Value: "69d_DUDENKOFF_CUSTOM,69d_SIMPLE_DATA"
Purpose: Tags for group invalidation
Tags: 
  - DUDENKOFF_CUSTOM (your custom cache type)
  - SIMPLE_DATA (tag from saveSimpleData method)
```

The tags allow you to clear related cache entries:
```php
// Clear all entries with "SIMPLE_DATA" tag
$this->cacheService->cleanByTag('simple_data');
```

### Line 2: Actual Cached Data

```
2025-11-03 17:16:32
```

This is the **actual data** you saved to cache. In this case, it's the timestamp string from:

```php
// From Block/CacheDemo.php, getCachedTime() method
$timestamp = date('Y-m-d H:i:s');
$this->cacheService->saveSimpleData($cacheKey, $timestamp, 300);
```

---

## How It All Works Together

### Step 1: Saving to Cache

```php
// In your block (Block/CacheDemo.php line 76-80)
$cacheKey = 'demo_timestamp';
$timestamp = date('Y-m-d H:i:s');  // "2025-11-03 17:16:32"
$this->cacheService->saveSimpleData($cacheKey, $timestamp, 300);
```

### Step 2: CacheService Processing

```php
// Service/CacheService.php
public function saveSimpleData(string $key, string $value, ?int $lifetime = null): bool
{
    $cacheKey = $this->getCacheKey($key);  
    // 'dudenkoff_cachelearn_demo_timestamp'
    
    $tags = [
        CustomCache::CACHE_TAG,  // 'DUDENKOFF_CUSTOM'
        'simple_data'            // 'SIMPLE_DATA'
    ];
    
    return $this->cache->save(
        $value,           // "2025-11-03 17:16:32"
        $cacheKey,        // 'dudenkoff_cachelearn_demo_timestamp'
        $tags,            // ['DUDENKOFF_CUSTOM', 'simple_data']
        300               // 5 minutes
    );
}
```

### Step 3: Magento File Cache Backend

Magento's cache backend:

1. **Generates filename:**
   - Takes key: `dudenkoff_cachelearn_demo_timestamp`
   - Adds prefix: `69d_` (from cache config)
   - Converts to uppercase: `DUDENKOFF_CACHELEARN_DEMO_TIMESTAMP`
   - Adds Magento prefix: `mage---69d_DUDENKOFF_CACHELEARN_DEMO_TIMESTAMP`

2. **Calculates expiration:**
   - Current time: `1762190192`
   - Lifetime: `300` seconds
   - Expiration: `1762190192 + 300 = 1762190492`

3. **Prepares tags:**
   - Tags: `['DUDENKOFF_CUSTOM', 'simple_data']`
   - Adds prefix to each: `69d_DUDENKOFF_CUSTOM`, `69d_SIMPLE_DATA`
   - Joins: `"69d_DUDENKOFF_CUSTOM,69d_SIMPLE_DATA"`

4. **Writes file:**
   ```
   Line 1: Metadata (serialized)
   Line 2: Your data
   ```

### Step 4: File Structure in var/cache/

```
var/cache/
├── mage--5/                                   # Directory (last digit of hash)
│   └── mage---69d_DUDENKOFF_CACHELEARN_SIMPLE_EXAMPLE
├── mage--7/                                   # Directory
│   └── mage---69d_DUDENKOFF_CACHELEARN_DEMO_TIMESTAMP  ← Your file!
└── mage--9/                                   # Directory
    └── mage---69d_DUDENKOFF_CACHELEARN_EXPENSIVE_CALCULATION
```

Files are distributed across directories to avoid having thousands of files in one directory (filesystem performance).

---

## Cache Lifecycle

### 1. Save (t=0)
```
Time: 17:16:32
File created: mage---69d_DUDENKOFF_CACHELEARN_DEMO_TIMESTAMP
Expires: 17:21:32 (5 minutes later)
```

### 2. Load (t=30s)
```
Time: 17:17:02
Check expiration: 1762190492 > current time? YES
Status: Cache HIT
Return: "2025-11-03 17:16:32"
```

### 3. Expired (t=6min)
```
Time: 17:22:32
Check expiration: 1762190492 > current time? NO
Status: Cache MISS
Action: Delete file, regenerate data
```

---

## Why This File Structure?

### Advantages:

**1. Human Readable**
- Can inspect cache contents manually
- Easy to debug

**2. Tag Support**
- Multiple tags per entry
- Group invalidation possible

**3. Distributed Storage**
- Files spread across directories
- Better filesystem performance

**4. Metadata First**
- Quick expiration check (read line 1 only)
- Don't need to read entire file

**5. Simple Implementation**
- No external dependencies
- Works everywhere
- Easy backup/restore

### Disadvantages:

**1. Slower than Redis/Memcached**
- File I/O vs memory access

**2. File System Limitations**
- Number of files per directory
- Disk space

**3. No Automatic Cleanup**
- Expired files stay until accessed
- Needs periodic cleanup

---

## Alternative: Redis Cache

For comparison, if you used Redis:

```php
// Redis stores as key-value:
Key: "69d_DUDENKOFF_CACHELEARN_DEMO_TIMESTAMP"
Value: "2025-11-03 17:16:32"
TTL: 300 seconds
Tags: Stored separately in Redis sets
```

**Redis advantages:**
- Much faster (in-memory)
- Automatic expiration
- Better for high traffic

**File cache advantages:**
- No external service needed
- Easier to debug
- Simple backup

---

## Debugging Cache Files

### View Cache File

```bash
# View metadata and data
cat var/cache/mage--7/mage---69d_DUDENKOFF_CACHELEARN_DEMO_TIMESTAMP

# Pretty print metadata (PHP)
php -r "var_dump(unserialize(file('var/cache/mage--7/mage---69d_DUDENKOFF_CACHELEARN_DEMO_TIMESTAMP')[0]));"
```

### Find Cache Files by Tag

```bash
# Find all files with SIMPLE_DATA tag
grep -r "SIMPLE_DATA" var/cache/
```

### Check Expiration

```bash
# View all cache files with their modification times
find var/cache/ -name "mage---69d_DUDENKOFF_*" -exec ls -lh {} \;
```

### Manual Cleanup

```bash
# Remove specific cache entry
rm var/cache/mage--7/mage---69d_DUDENKOFF_CACHELEARN_DEMO_TIMESTAMP

# Remove all your module's cache
rm var/cache/*/mage---69d_DUDENKOFF_CACHELEARN_*

# Remove all cache
bin/magento cache:flush
```

---

## Cache Key Naming Best Practices

### Good Cache Keys

```php
// Descriptive and unique
'product_details_123'
'category_tree_store_1'
'customer_orders_456'
'api_response_weather_london'

// With prefix (what our module does)
'dudenkoff_cachelearn_demo_timestamp'
'dudenkoff_cachelearn_simple_example'
```

### Bad Cache Keys

```php
// Too generic
'data'
'cache'
'temp'

// Collision risk
'item_1'  // Could be product, category, anything!

// Dynamic parts that never hit cache
'data_' . time()  // Changes every second!
```

---

## Summary

**Your cache file:**
```
mage---69d_DUDENKOFF_CACHELEARN_DEMO_TIMESTAMP
│
├── Line 1: Metadata
│   ├── hash: "" (not used)
│   ├── mtime: 1762190192 (created: 17:16:32)
│   ├── expire: 1762190492 (expires: 17:21:32)
│   └── tags: "69d_DUDENKOFF_CUSTOM,69d_SIMPLE_DATA"
│
└── Line 2: Data
    └── "2025-11-03 17:16:32" (your cached timestamp)
```

**The journey:**
```
PHP Code: 'demo_timestamp'
    ↓
Service: 'dudenkoff_cachelearn_demo_timestamp'
    ↓
Magento: 'mage---69d_DUDENKOFF_CACHELEARN_DEMO_TIMESTAMP'
    ↓
Filesystem: var/cache/mage--7/mage---69d_DUDENKOFF_CACHELEARN_DEMO_TIMESTAMP
```

**Cache lifecycle:**
```
Save → Store (mtime + expire + tags + data)
    ↓
Load → Check expire > now? → YES: return data, NO: delete & miss
    ↓
Clean by tag → Find all files with tag → Delete them
    ↓
Flush → Delete all cache files
```

---

Now you understand exactly how Magento stores cache on the filesystem! 🎉

