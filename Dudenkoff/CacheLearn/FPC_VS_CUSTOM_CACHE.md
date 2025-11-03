# Full Page Cache vs Custom Cache

## What Just Happened?

You discovered an important concept: **Magento has multiple cache layers** that work at different levels!

## The Problem

The demo page showed:
```
Current Time (Not Cached): 2025-11-03 15:54:50
```

And the time **wasn't changing** on refresh. Why?

## The Answer: Full Page Cache (FPC)

Even though the PHP code generates a new timestamp every time:
```php
public function getCurrentTime(): string
{
    return date('Y-m-d H:i:s'); // This executes every time!
}
```

**Full Page Cache** cached the entire HTML page, including that timestamp!

## Magento's Cache Layers

```
┌─────────────────────────────────────┐
│  REQUEST COMES IN                   │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│  LAYER 1: Full Page Cache (FPC)    │ ◄── This was caching the whole page!
│  - Caches entire HTML response      │
│  - Fastest (serves HTML directly)   │
│  - Enabled by default in production │
└──────────────┬──────────────────────┘
               │ (cache miss)
               ▼
┌─────────────────────────────────────┐
│  LAYER 2: Block Cache               │
│  - Caches individual block HTML     │
│  - Per-block control                │
└──────────────┬──────────────────────┘
               │ (cache miss)
               ▼
┌─────────────────────────────────────┐
│  LAYER 3: Custom Cache              │ ◄── This is what we're teaching!
│  - Caches specific data             │
│  - Full control over what/when      │
│  - What CacheService demonstrates   │
└──────────────┬──────────────────────┘
               │ (cache miss)
               ▼
┌─────────────────────────────────────┐
│  LAYER 4: Database/Source           │
│  - Actual data generation           │
│  - Slowest (but most accurate)      │
└─────────────────────────────────────┘
```

## The Solution

We set the demo page to `cacheable="false"`:

```xml
<page cacheable="false">
    <body>
        <referenceContainer name="content">
            <block cacheable="false" 
                   class="Dudenkoff\CacheLearn\Block\CacheDemo"/>
        </referenceContainer>
    </body>
</page>
```

**Now:**
- ✅ FPC is disabled for this page
- ✅ Current Time updates on every refresh
- ✅ You can see custom cache in action
- ✅ Perfect for learning!

## When to Use Each Layer

### Full Page Cache (FPC)
```
✅ Use for:
- Product pages (mostly static)
- Category pages
- CMS pages
- Public content

❌ Don't use for:
- Cart pages
- Checkout
- Customer account pages
- Forms with CSRF tokens
```

### Block Cache
```
✅ Use for:
- Reusable blocks (header, footer)
- Product lists
- Navigation menus
- Static blocks

❌ Don't use for:
- User-specific content
- Forms
- Dynamic data
```

### Custom Cache (What We're Teaching!)
```
✅ Use for:
- Database query results
- API responses
- Calculations
- Configuration data
- Any expensive operation result

❌ Don't use for:
- Entire pages (use FPC)
- HTML blocks (use Block Cache)
```

## Real Example: Product Page

```
Product Page Request
│
├─ FPC (full_page) ─────────────► Entire HTML cached
│
├─ Block Cache ──────────────────► Navigation menu cached
│  └─ Custom Cache ──────────────► Category tree data cached
│
├─ Block Cache ──────────────────► Product details cached
│  └─ Custom Cache ──────────────► Product from DB cached
│
└─ Block: Add to Cart ───────────► NOT cached (dynamic)
   └─ Custom Cache ──────────────► Product stock qty cached
```

## Testing Different Layers

### Test FPC
```bash
# Enable FPC
bin/magento cache:enable full_page

# Visit product page twice
# First visit: Slow (cache miss)
# Second visit: Fast! (cache hit)

# Check if page is cached
curl -I http://your-site.com/product-page.html | grep X-Magento-Cache
# X-Magento-Cache-Control: max-age=86400, public
# X-Magento-Cache-Debug: HIT
```

### Test Block Cache
```php
// In layout XML
<block cacheable="true" cache_lifetime="3600">
    <!-- Block content -->
</block>
```

### Test Custom Cache (Our Module!)
```php
// Visit our demos
http://your-site.com/cachelearn/demo/simple
http://your-site.com/cachelearn/demo/complex
```

## Why Our Demo Page is Uncacheable

The demo page needs to:
1. Show "Current Time" that updates every second
2. Demonstrate cache hits vs misses in real-time
3. Allow you to see custom cache in action

If FPC was enabled:
- Current time would be frozen ❌
- You couldn't see the difference between cached/uncached ❌
- The learning experience would be confusing ❌

## Key Takeaways

1. **FPC caches the entire page** - Even "non-cached" PHP code won't run
2. **Different cache layers for different purposes** - Choose the right one
3. **cacheable="false" disables FPC** - Use for dynamic pages
4. **Custom cache is about DATA, not HTML** - This is what we're teaching
5. **Production uses all layers** - For maximum performance

## Production Best Practice

In production, you want:

```php
┌─────────────────────────────────────┐
│  Static Content (Products, CMS)     │
├─────────────────────────────────────┤
│  FPC: ✅ Enabled                    │
│  Block Cache: ✅ Enabled            │
│  Custom Cache: ✅ Enabled           │
│  Result: Maximum Performance! 🚀    │
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│  Dynamic Content (Cart, Account)    │
├─────────────────────────────────────┤
│  FPC: ❌ Disabled (cacheable=false) │
│  Block Cache: ⚠️ Selective          │
│  Custom Cache: ✅ Enabled           │
│  Result: Fast & Dynamic! ⚡         │
└─────────────────────────────────────┘
```

## Commands to Fix FPC Issues

```bash
# Clear FPC when content changes
bin/magento cache:clean full_page

# Disable FPC for development
bin/magento cache:disable full_page

# Enable FPC for production
bin/magento cache:enable full_page

# Check FPC status
bin/magento cache:status | grep full_page

# View what's in cache (if using Redis)
redis-cli
> KEYS *FPC*
```

## Learning Exercise

Try this experiment:

1. **Enable FPC for our demo page:**
   ```xml
   <!-- Change cacheable="false" to cacheable="true" -->
   <page cacheable="true">
   ```

2. **Clear cache and visit page:**
   ```bash
   bin/magento cache:flush
   curl http://your-site.com/cachelearn
   ```

3. **Notice:** Current Time is now frozen!

4. **Change it back:**
   ```xml
   <page cacheable="false">
   ```

5. **Understanding:** This is why we disabled FPC for the demo!

## Summary

- ✅ **FPC** caches entire HTML pages (fastest, least flexible)
- ✅ **Block Cache** caches individual block HTML (fast, somewhat flexible)
- ✅ **Custom Cache** caches data/objects (flexible, what we're teaching!)
- ✅ **No Cache** for truly dynamic content (slowest, most flexible)

Choose the **right cache layer** for your use case!

---

**Now you understand:** The time wasn't changing because of FPC, not a bug in the custom cache! This is actually a great example of how powerful (and sometimes unexpected) FPC can be! 🎓

