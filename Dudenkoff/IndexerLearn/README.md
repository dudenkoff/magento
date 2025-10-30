# Magento 2 Indexer Learning Module

**A comprehensive educational module to understand how Magento 2 indexation works.**

## 🎯 What You'll Learn

This module demonstrates:
- ✅ How Magento indexers work under the hood
- ✅ Full vs partial reindexing
- ✅ Update on Save vs Update on Schedule modes
- ✅ Materialized views (mview) and changelog tracking
- ✅ Index invalidation and invalidation triggers
- ✅ Performance optimization through pre-calculation
- ✅ Custom indexer implementation from scratch

## 📚 Module Overview

This module creates a **Product Statistics Indexer** that:

1. **Source Data**: Tracks product views, purchases, and revenue in `dudenkoff_product_stats`
2. **Index Data**: Pre-calculates derived metrics in `dudenkoff_product_stats_idx`:
   - Conversion rate (purchases/views)
   - Average order value
   - Popularity tier classification
3. **Demonstrates**: How expensive calculations are done once during indexing instead of on every query

## 🚀 Quick Start

### 1. Enable the Module

```bash
bin/magento module:enable Dudenkoff_IndexerLearn
bin/magento setup:upgrade
bin/magento setup:db-schema:upgrade
bin/magento cache:clean
```

### 2. Generate Sample Data

```bash
bin/magento dudenkoff:indexer:generate-data 100
```

### 3. Check Indexer Status

```bash
bin/magento indexer:status dudenkoff_product_stats
```

### 4. Run the Indexer

```bash
bin/magento indexer:reindex dudenkoff_product_stats
```

### 5. View Indexed Data

```bash
bin/magento dudenkoff:indexer:show-stats
```

## 📖 What Each Command Does

| Command | Purpose |
|---------|---------|
| `dudenkoff:indexer:generate-data [count]` | Creates test data in the source table |
| `dudenkoff:indexer:show-stats [--limit] [--tier]` | Displays indexed statistics |
| `dudenkoff:indexer:clear-data` | Cleans up all test data |
| `indexer:status dudenkoff_product_stats` | Shows indexer state (Valid/Invalid/Working) |
| `indexer:reindex dudenkoff_product_stats` | Manually runs full reindex |
| `indexer:set-mode {realtime\|schedule} dudenkoff_product_stats` | Changes indexer mode |

## 🎓 Learning Path

Follow this order for best understanding:

1. **[SETUP.md](SETUP.md)** - Installation and initial setup
2. **[INDEXER_CONCEPTS.md](INDEXER_CONCEPTS.md)** - Core concepts explained
3. **[EXAMPLES.md](EXAMPLES.md)** - Hands-on examples and experiments
4. **Code Review** - Read the heavily commented source code

## 📁 Key Files to Study

### Configuration Files
- `etc/indexer.xml` - Indexer declaration
- `etc/mview.xml` - Change tracking configuration
- `etc/db_schema.xml` - Database tables (source + index)

### Core Implementation
- `Model/Indexer/ProductStats.php` - Main indexer logic (⭐ START HERE)
- `Model/ResourceModel/ProductStats.php` - Source table operations
- `Model/ResourceModel/ProductStatsIndex.php` - Index table operations

### Supporting Files
- `Observer/InvalidateStatsIndexObserver.php` - Manual invalidation example
- `Console/Command/` - CLI tools for testing

## 💡 Key Concepts Demonstrated

### 1. Why Indexing Matters

**Without Indexing (Slow):**
```sql
-- Calculate on EVERY page load
SELECT 
    product_id,
    view_count,
    purchase_count,
    (purchase_count / view_count * 100) as conversion_rate,  -- Calculated!
    (revenue / purchase_count) as avg_order_value            -- Calculated!
FROM product_stats
WHERE view_count > 1000
ORDER BY conversion_rate DESC;
```

**With Indexing (Fast):**
```sql
-- Pre-calculated during indexing
SELECT 
    product_id,
    view_count,
    purchase_count,
    conversion_rate,      -- Already calculated!
    average_order_value   -- Already calculated!
FROM product_stats_idx   -- Index table
WHERE popularity_tier = 'high'
ORDER BY conversion_rate DESC;
```

### 2. Indexer Modes

**Update on Save (Realtime)**
- ✅ Always up-to-date (when using Magento ORM)
- ❌ Slower saves
- ⚠️ **Requires application events** - doesn't work with direct DB changes
- 💡 Use for: Critical data that must be instantly accurate

**Update on Schedule**
- ✅ Fast saves
- ✅ Works with ANY database change (direct or via ORM)
- ❌ May be stale until cron runs
- 💡 Use for: Analytics, reports, non-critical data, bulk operations

### 3. How Mview Works

When you change data:
```
1. Row updated in dudenkoff_product_stats
2. Trigger logs change → dudenkoff_product_stats_cl (changelog)
3. Indexer marked as "Invalid"
4. Cron job processes changelog → reindexes only changed IDs
5. Changelog cleared, indexer marked as "Valid"
```

## 🔍 Real-World Use Cases

Magento's built-in indexers use the same concepts:

- **Catalog Price Index**: Pre-calculates final prices with discounts, tier pricing
- **Category Product Index**: Pre-builds product-to-category relationships
- **Catalog Search Index**: Pre-processes products for search
- **Stock Status Index**: Pre-calculates saleable quantities

## 🧪 Experiments to Try

1. **Test Full Reindex**: Time how long it takes with 1000 vs 10,000 products
2. **Test Partial Reindex**: Update one row, see only that ID reindexed
3. **Compare Modes**: Switch between realtime and schedule, observe behavior
4. **Watch Changelog**: Check `dudenkoff_product_stats_cl` table during updates
5. **Index Invalidation**: Trigger manual invalidation via observer

## 📊 Module Architecture

```
┌─────────────────────────────────────────────────────────┐
│                    Your Application                      │
│          (Frontend, Admin, API, Observers)               │
└───────────────────┬─────────────────────────────────────┘
                    │
                    │ Updates data
                    ↓
┌─────────────────────────────────────────────────────────┐
│           SOURCE TABLE: dudenkoff_product_stats          │
│     product_id | view_count | purchase_count | revenue  │
└───────────────────┬─────────────────────────────────────┘
                    │
                    │ Triggers DB changelog
                    ↓
┌─────────────────────────────────────────────────────────┐
│        CHANGELOG: dudenkoff_product_stats_cl             │
│              Tracks which IDs changed                    │
└───────────────────┬─────────────────────────────────────┘
                    │
                    │ Indexer processes changes
                    ↓
┌─────────────────────────────────────────────────────────┐
│      INDEXER: Model/Indexer/ProductStats.php            │
│         - Reads source data                              │
│         - Calculates derived metrics                     │
│         - Writes to index table                          │
└───────────────────┬─────────────────────────────────────┘
                    │
                    │ Outputs indexed data
                    ↓
┌─────────────────────────────────────────────────────────┐
│       INDEX TABLE: dudenkoff_product_stats_idx           │
│  product_id | conversion_rate | avg_order_value | ...   │
│            PRE-CALCULATED FAST DATA                      │
└─────────────────────────────────────────────────────────┘
                    │
                    │ Fast queries
                    ↓
              [Your Application]
```

## 🐛 Troubleshooting

### Indexer stuck in "Processing"
```bash
bin/magento indexer:reset dudenkoff_product_stats
bin/magento indexer:reindex dudenkoff_product_stats
```

### Changelog table growing too large
```bash
# This is normal - cron job clears it after processing
# Check cron is running: indexer_update_all_views
```

### Changes not reflected in index
```bash
# Check indexer mode
bin/magento indexer:show-mode dudenkoff_product_stats

# If "schedule", wait for cron or manually reindex
bin/magento indexer:reindex dudenkoff_product_stats
```

## 🤝 Contributing

This is an educational module. Feel free to:
- Add more complex examples
- Extend with additional metrics
- Add test cases
- Improve documentation

## 📝 License

Copyright © Dudenkoff. All rights reserved.

## 🔗 Additional Resources

- [Official Magento Indexing Guide](https://developer.adobe.com/commerce/php/development/components/indexing/)
- [Mview Documentation](https://developer.adobe.com/commerce/php/development/components/indexing/#mview)
- Other Dudenkoff learning modules: `DILearn`, `ObserverLearn`, `ApiLearn`


