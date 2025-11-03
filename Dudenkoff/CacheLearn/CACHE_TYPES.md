# Magento Cache Types Explained

## Overview

Magento 2 uses multiple cache types to optimize different aspects of the application. Each cache type serves a specific purpose and can be managed independently.

## Built-in Cache Types

### 1. Configuration (config)

**Identifier:** `config`

**What it caches:**
- System configuration merged from all modules
- Store-specific configuration
- Module declarations
- DI configuration

**When to clear:**
- After modifying any XML configuration files
- After enabling/disabling modules
- After changing system configuration in admin

**CLI:**
```bash
bin/magento cache:clean config
```

---

### 2. Layout (layout)

**Identifier:** `layout`

**What it caches:**
- Compiled page layouts
- Layout XML instructions
- Block structure

**When to clear:**
- After modifying layout XML files
- After adding/removing blocks
- After changing block structure

**CLI:**
```bash
bin/magento cache:clean layout
```

---

### 3. Block HTML Output (block_html)

**Identifier:** `block_html`

**What it caches:**
- HTML output of blocks
- Generated HTML fragments
- Partial page content

**When to clear:**
- After modifying templates
- After changing block logic
- When content doesn't update

**CLI:**
```bash
bin/magento cache:clean block_html
```

---

### 4. Collections Data (collections)

**Identifier:** `collections`

**What it caches:**
- Database query results
- Collection data
- Aggregated data sets

**When to clear:**
- After database changes
- After modifying collection classes
- When data seems stale

**CLI:**
```bash
bin/magento cache:clean collections
```

---

### 5. Reflection Data (reflection)

**Identifier:** `reflection`

**What it caches:**
- Class reflection data
- API metadata
- Interface declarations

**When to clear:**
- After adding/modifying classes
- After changing method signatures
- After API changes

**CLI:**
```bash
bin/magento cache:clean reflection
```

---

### 6. Database DDL (db_ddl)

**Identifier:** `db_ddl`

**What it caches:**
- Database table structures
- Column definitions
- Index information

**When to clear:**
- After database schema changes
- After running setup:upgrade
- After modifying db_schema.xml

**CLI:**
```bash
bin/magento cache:clean db_ddl
```

---

### 7. Compiled Config (compiled_config)

**Identifier:** `compiled_config`

**What it caches:**
- Compiled Dependency Injection configuration
- Generated proxies and factories
- Constructor parameters

**When to clear:**
- After modifying di.xml
- After adding new dependencies
- After constructor changes

**CLI:**
```bash
bin/magento cache:clean compiled_config
```

---

### 8. EAV (eav)

**Identifier:** `eav`

**What it caches:**
- EAV attribute metadata
- Attribute options
- Entity type data

**When to clear:**
- After adding/modifying product attributes
- After customer attribute changes
- After category attribute updates

**CLI:**
```bash
bin/magento cache:clean eav
```

---

### 9. Customer Notification (customer_notification)

**Identifier:** `customer_notification`

**What it caches:**
- Customer notification messages
- System messages
- Alert messages

**When to clear:**
- Rarely needed
- When notifications don't appear

**CLI:**
```bash
bin/magento cache:clean customer_notification
```

---

### 10. Web Services Configuration (config_webservice)

**Identifier:** `config_webservice`

**What it caches:**
- API configuration
- SOAP/REST definitions
- WebAPI metadata

**When to clear:**
- After modifying webapi.xml
- After API changes
- After service contract modifications

**CLI:**
```bash
bin/magento cache:clean config_webservice
```

---

### 11. Integrations Configuration (config_integration)

**Identifier:** `config_integration`

**What it caches:**
- Third-party integration settings
- OAuth configuration
- Integration API settings

**When to clear:**
- After adding/modifying integrations
- After OAuth changes

**CLI:**
```bash
bin/magento cache:clean config_integration
```

---

### 12. Integrations API Configuration (config_integration_api)

**Identifier:** `config_integration_api`

**What it caches:**
- Integration API definitions
- Service contracts for integrations
- API permissions

**When to clear:**
- After modifying integration APIs
- After permission changes

**CLI:**
```bash
bin/magento cache:clean config_integration_api
```

---

### 13. Full Page Cache (full_page)

**Identifier:** `full_page`

**What it caches:**
- Complete rendered HTML pages
- Entire page output
- Public pages only

**When to clear:**
- After content changes
- After theme modifications
- When pages show old content

**Important:**
- Most impactful for performance
- Uses separate cache backend (Varnish or built-in)
- Can be configured per page

**CLI:**
```bash
bin/magento cache:clean full_page
```

---

### 14. Translations (translate)

**Identifier:** `translate`

**What it caches:**
- Translation strings
- i18n dictionaries
- Locale-specific text

**When to clear:**
- After adding translation CSV files
- After changing phrases
- When translations don't update

**CLI:**
```bash
bin/magento cache:clean translate
```

---

## Custom Cache Type (Our Example)

### Dudenkoff Custom Cache (dudenkoff_custom_cache)

**Identifier:** `dudenkoff_custom_cache`

**What it caches:**
- Custom module data
- Educational examples
- Demo data

**Implementation:**
```php
class CustomCache extends TagScope
{
    const TYPE_IDENTIFIER = 'dudenkoff_custom_cache';
    const CACHE_TAG = 'DUDENKOFF_CUSTOM';
}
```

**Usage:**
```php
// Save
$this->cacheService->saveData('my_key', $data);

// Load
$data = $this->cacheService->loadData('my_key');

// Remove
$this->cacheService->remove('my_key');
```

**CLI:**
```bash
bin/magento cache:clean dudenkoff_custom_cache
```

---

## Cache Management Commands

### View Status
```bash
bin/magento cache:status
```

### Enable All
```bash
bin/magento cache:enable
```

### Enable Specific
```bash
bin/magento cache:enable config layout block_html
```

### Disable All
```bash
bin/magento cache:disable
```

### Disable Specific
```bash
bin/magento cache:disable full_page
```

### Clean (Remove Invalid)
```bash
bin/magento cache:clean
```

### Flush (Remove All)
```bash
bin/magento cache:flush
```

### Clean Specific Types
```bash
bin/magento cache:clean config layout
```

---

## Cache Type Comparison

| Cache Type | Impact | Frequency of Change | Clear Cost |
|------------|--------|---------------------|------------|
| full_page | ⭐⭐⭐⭐⭐ | Low | Low |
| block_html | ⭐⭐⭐⭐ | Medium | Low |
| config | ⭐⭐⭐ | Low | Medium |
| layout | ⭐⭐⭐ | Low | Medium |
| collections | ⭐⭐⭐ | Medium | Low |
| compiled_config | ⭐⭐⭐ | Low | High |
| db_ddl | ⭐⭐ | Very Low | Low |
| eav | ⭐⭐⭐ | Low | Medium |
| translate | ⭐⭐ | Low | Low |
| reflection | ⭐⭐ | Low | Medium |

**Legend:**
- **Impact:** How much it affects performance
- **Frequency:** How often you need to clear it
- **Clear Cost:** How expensive it is to regenerate

---

## Development Best Practices

### During Development

**Disable these for faster iteration:**
```bash
bin/magento cache:disable block_html layout full_page
```

**Keep enabled for performance:**
```bash
bin/magento cache:enable config compiled_config
```

### In Production

**Always enable all cache types:**
```bash
bin/magento cache:enable
```

**Use Varnish for full_page:**
```bash
# Configure in Admin: Stores > Configuration > Advanced > System > Full Page Cache
```

### Deployment

**Standard deployment cache workflow:**
```bash
# 1. Disable maintenance mode
bin/magento maintenance:enable

# 2. Clear cache
bin/magento cache:flush

# 3. Run setup:upgrade
bin/magento setup:upgrade

# 4. Compile DI
bin/magento setup:di:compile

# 5. Deploy static content
bin/magento setup:static-content:deploy

# 6. Enable all caches
bin/magento cache:enable

# 7. Disable maintenance mode
bin/magento maintenance:disable
```

---

## Cache Troubleshooting

### Problem: Changes Not Appearing

**Solution:**
```bash
# Try cleaning specific cache
bin/magento cache:clean config layout block_html

# If that doesn't work, flush all
bin/magento cache:flush

# Nuclear option
rm -rf var/cache/* var/page_cache/* generated/*
bin/magento setup:upgrade
```

### Problem: Slow Admin Panel

**Possible causes:**
- Full page cache disabled
- Too many cache types disabled
- Compilation needed

**Solution:**
```bash
bin/magento cache:enable
bin/magento setup:di:compile
```

### Problem: API Returns Stale Data

**Solution:**
```bash
bin/magento cache:clean config_webservice collections
```

---

## Creating Custom Cache Types

### When to Create Custom Cache Type

✅ **Good reasons:**
- You have a lot of module-specific cached data
- You want independent cache management
- You need custom cache cleaning logic
- You want cache to appear in admin/CLI

❌ **Don't create if:**
- You only need to cache a few values
- Existing cache types work fine
- You don't need special management

### How to Create

**1. Define in etc/cache.xml:**
```xml
<type name="my_custom_cache" 
      instance="Vendor\Module\Model\Cache\Type\CustomCache">
    <label>My Custom Cache</label>
</type>
```

**2. Create class:**
```php
class CustomCache extends TagScope
{
    const TYPE_IDENTIFIER = 'my_custom_cache';
    const CACHE_TAG = 'MY_CUSTOM';
}
```

**3. Use in code:**
```php
$this->cache->save($data, $key, [CustomCache::CACHE_TAG]);
```

---

## Summary

- **13 built-in cache types** serve different purposes
- Each can be managed **independently**
- **full_page** has biggest performance impact
- Clear **specific types** when needed, not always all
- **Custom cache types** useful for complex modules
- Use **CLI commands** for cache management
- **Disable carefully** in development
- **Always enable** in production

For hands-on learning, try our examples at `/cachelearn`! 🚀

