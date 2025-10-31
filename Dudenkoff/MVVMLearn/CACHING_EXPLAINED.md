# Why ResourceModels Don't Cache (But Models Do!)

## 🎯 TL;DR

**ResourceModels** = Direct database access (no cache)  
**Models** = Entity layer (WITH cache support)

This is **by design** in Magento's architecture!

---

## 🏗️ The Layered Approach

```
┌─────────────────────────────────────────┐
│         BLOCK/CONTROLLER                │
│         (Uses cache here)               │
└──────────────┬──────────────────────────┘
               ↓
┌──────────────┴──────────────────────────┐
│            MODEL                         │
│      (Cache tags defined here)          │
│      $_cacheTag = 'dudenkoff_book'      │
│      getIdentities() returns tags       │
└──────────────┬──────────────────────────┘
               ↓
┌──────────────┴──────────────────────────┐
│        RESOURCE MODEL                    │
│      (NO CACHE - Always fresh data)     │
│      Direct SQL queries                 │
└──────────────┬──────────────────────────┘
               ↓
         [DATABASE]
```

---

## 📋 Why This Design?

### **ResourceModel = Data Access Layer**

**Purpose:** Get fresh data from database  
**Why no cache?**
- ✅ Always returns current database state
- ✅ Used by both cached and non-cached operations
- ✅ Reusable across different contexts
- ✅ Simple and predictable

**Example:**
```php
// ResourceModel ALWAYS queries database
$bookResource->load($book, 1);
// ↑ SELECT * FROM dudenkoff_book WHERE book_id = 1
```

### **Model = Business Layer**

**Purpose:** Entity with business logic + cache support  
**Why cache here?**
- ✅ Represents complete entity
- ✅ Cache invalidation on save/delete
- ✅ Full Page Cache (FPC) integration
- ✅ Better performance for unchanged data

**Example:**
```php
// Model uses cache tags
protected $_cacheTag = 'dudenkoff_book';

public function getIdentities()
{
    return ['dudenkoff_book_' . $this->getBookId()];
}
```

---

## 🔍 How Magento Cache Works

### 1. **Model-Level Cache** (`$_cacheTag`)

**Defined in Model:**
```php
class Book extends AbstractModel implements IdentityInterface
{
    const CACHE_TAG = 'dudenkoff_book';
    protected $_cacheTag = self::CACHE_TAG;
    
    public function getIdentities()
    {
        // Tag for this specific book: "dudenkoff_book_1"
        return [self::CACHE_TAG . '_' . $this->getBookId()];
    }
}
```

**What happens when you save:**
```php
$book->setPrice(29.99);
$book->save();

// Magento automatically:
// 1. Calls $book->afterSave()
// 2. Calls $book->cleanModelCache()
// 3. Gets cache tags: ['dudenkoff_book_1']
// 4. Invalidates all cache entries with that tag
```

**Handled by:** `Magento\Framework\App\Cache\FlushCacheByTags` (plugin)

---

### 2. **Full Page Cache (FPC)**

**Used in Blocks:**
```php
class BookView extends Template implements IdentityInterface
{
    public function getIdentities()
    {
        $book = $this->getBook();
        if ($book) {
            return $book->getIdentities();  // Uses Model's cache tags
        }
        return [];
    }
}
```

**What happens:**
1. Page is rendered and cached
2. Cache is tagged with: `['dudenkoff_book_1']`
3. When book #1 is saved, cache with that tag is invalidated
4. Next page request regenerates the cache

---

## 💡 Why Not Cache in ResourceModel?

### **Problem if ResourceModel cached:**

```php
// BAD EXAMPLE (if ResourceModel had cache)
$bookResource->load($book, 1);  // ← Returns cached data
// But database was updated directly!
// User would see stale data!
```

### **Current Design (Good):**

```php
// ResourceModel always queries DB
$bookResource->load($book, 1);  // ← Always fresh from DB

// Cache happens at higher layers:
// - Model afterLoad cache
// - Block cache
// - Full Page Cache
```

---

## 📊 Comparison Table

| Layer | Caches? | Why? | Example |
|-------|---------|------|---------|
| **ResourceModel** | ❌ NO | Always need fresh DB data | `$resource->load($model, 1)` |
| **Model** | ✅ YES (tags) | Entity-level cache invalidation | `$_cacheTag = 'book'` |
| **Block** | ✅ YES (FPC) | Page/component caching | `getCacheKeyInfo()` |
| **Collection** | ❌ NO | Query results vary | `$collection->getItems()` |
| **Repository** | ❌ NO | Uses Model (which caches) | `$repo->getById(1)` |

---

## 🎓 Complete Cache Flow Example

### **When you save a book:**

```php
$book = $bookRepository->getById(1);
$book->setPrice(39.99);
$bookRepository->save($book);
```

**What happens internally:**

```
1. Repository calls: $bookResource->save($book)
   ↓
2. ResourceModel executes: UPDATE dudenkoff_book SET price = 39.99 WHERE book_id = 1
   ↓
3. Model's afterSave() is called (by AbstractModel)
   ↓
4. cleanModelCache() is called
   ↓
5. getCacheTags() returns: ['dudenkoff_book']
   ↓
6. getIdentities() returns: ['dudenkoff_book_1']
   ↓
7. FlushCacheByTags plugin cleans all cache with these tags
   ↓
8. Any FPC pages showing book #1 are invalidated
   ↓
9. Next page view regenerates fresh cache
```

---

## ✅ Updated Book Model (With Cache)

Your `Model/Book.php` now includes:

```php
class Book extends AbstractModel implements BookInterface, IdentityInterface
{
    const CACHE_TAG = 'dudenkoff_book';
    protected $_cacheTag = self::CACHE_TAG;
    
    // Returns cache tag for this specific book
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getBookId()];
    }
}
```

**Benefits:**
- ✅ Automatic cache invalidation on save/delete
- ✅ FPC integration
- ✅ Block-level caching works properly
- ✅ No manual cache management needed

---

## 🔍 Where Caching Happens

### ❌ **NO Cache:**

1. **ResourceModel** - Always fresh DB queries
   ```php
   $bookResource->getBooksByAuthor('Jane');  // Always queries DB
   ```

2. **Collection** - Query results change
   ```php
   $collection->addStatusFilter(1)->getItems();  // Always queries DB
   ```

3. **Repository** - Delegates to Model
   ```php
   $repository->getById(1);  // Uses Model's cache mechanism
   ```

### ✅ **WITH Cache:**

1. **Model** - Cache tags for invalidation
   ```php
   $_cacheTag = 'dudenkoff_book';  // Defines cache tag
   getIdentities() // Returns specific tags
   ```

2. **Blocks** - Full Page Cache
   ```php
   protected $_cacheLifetime = 3600;  // Cache for 1 hour
   public function getCacheKeyInfo() { ... }  // Cache key
   public function getIdentities() { ... }  // Cache tags
   ```

---

## 🎯 Key Takeaways

### 1. **ResourceModel Don't Cache Because:**
- They're the data access layer
- Should always return fresh data
- Used by cached and non-cached operations
- Keep it simple and predictable

### 2. **Models DO Cache Because:**
- They're entity representations
- Have cache tags for invalidation
- Integrate with FPC
- Provide automatic cache management

### 3. **The Pattern is:**
```
ResourceModel (no cache) → Model (cache tags) → Block (FPC)
```

---

## 💡 Practical Example

### **Without Cache (ResourceModel):**
```php
// Every call hits database
$books = $bookResource->getBooksByAuthor('Jane');  // DB query
$books = $bookResource->getBooksByAuthor('Jane');  // DB query again
$books = $bookResource->getBooksByAuthor('Jane');  // DB query again
```

### **With Cache (Model + Block):**
```php
// First request
Visit: /books/book/view/id/1
→ DB query
→ Page cached with tag: 'dudenkoff_book_1'

// Second request
Visit: /books/book/view/id/1
→ Served from cache (no DB query!)

// Update book
$book->save();
→ Cache with tag 'dudenkoff_book_1' invalidated

// Third request
Visit: /books/book/view/id/1
→ DB query (cache was invalidated)
→ New cache generated
```

---

## 🚀 Conclusion

**ResourceModels don't cache** = They're the **source of truth**  
**Models DO cache** = They're the **business entities**

This separation allows Magento to:
- ✅ Keep data layer clean and predictable
- ✅ Apply caching at appropriate layers
- ✅ Automatically invalidate cache when needed
- ✅ Balance performance and data freshness

**Your module now demonstrates this perfectly!** 🎯

