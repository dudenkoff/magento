# Indexer Quick Reference Cheat Sheet

## 🚀 Essential Commands

### Module Setup
```bash
bin/magento module:enable Dudenkoff_IndexerLearn
bin/magento setup:upgrade
bin/magento cache:clean
```

### Indexer Management
```bash
# List all indexers
bin/magento indexer:info

# Check status
bin/magento indexer:status [indexer_id]
bin/magento indexer:status dudenkoff_product_stats

# Reindex
bin/magento indexer:reindex [indexer_id]
bin/magento indexer:reindex dudenkoff_product_stats

# Reindex all
bin/magento indexer:reindex

# Reset indexer
bin/magento indexer:reset [indexer_id]

# Show/Set mode
bin/magento indexer:show-mode [indexer_id]
bin/magento indexer:set-mode {realtime|schedule} [indexer_id]
```

### Module-Specific Commands
```bash
# Generate test data
bin/magento dudenkoff:indexer:generate-data [count]

# View indexed statistics
bin/magento dudenkoff:indexer:show-stats [--limit=N] [--tier=low|medium|high]

# Clear all data
bin/magento dudenkoff:indexer:clear-data
```

### Cron Management
```bash
# Run cron manually
bin/magento cron:run
bin/magento cron:run --group index

# Check cron status
bin/magento cron:status
```

---

## 📊 Database Quick Reference

### Tables
```sql
-- Source table (your data)
SELECT * FROM dudenkoff_product_stats LIMIT 10;

-- Changelog table (tracks changes)
SELECT * FROM dudenkoff_product_stats_cl;

-- Index table (pre-calculated data)
SELECT * FROM dudenkoff_product_stats_idx LIMIT 10;

-- Check counts
SELECT 
    (SELECT COUNT(*) FROM dudenkoff_product_stats) as source_count,
    (SELECT COUNT(*) FROM dudenkoff_product_stats_cl) as changelog_count,
    (SELECT COUNT(*) FROM dudenkoff_product_stats_idx) as index_count;
```

### Manual Data Operations
```sql
-- Add/update source data
INSERT INTO dudenkoff_product_stats (product_id, view_count, purchase_count, revenue)
VALUES (9999, 100, 10, 500.00)
ON DUPLICATE KEY UPDATE view_count = view_count + 1;

-- View specific product
SELECT * FROM dudenkoff_product_stats_idx WHERE product_id = 1001;

-- Clear changelog
TRUNCATE TABLE dudenkoff_product_stats_cl;

-- Clear all data
TRUNCATE TABLE dudenkoff_product_stats;
TRUNCATE TABLE dudenkoff_product_stats_idx;
```

---

## 🎯 Common Workflows

### Initial Setup Workflow
```bash
# 1. Enable module
bin/magento module:enable Dudenkoff_IndexerLearn
bin/magento setup:upgrade

# 2. Generate test data
bin/magento dudenkoff:indexer:generate-data 100

# 3. Run initial reindex
bin/magento indexer:reindex dudenkoff_product_stats

# 4. View results
bin/magento dudenkoff:indexer:show-stats
```

### Testing Realtime Mode
```bash
# 1. Set mode
bin/magento indexer:set-mode realtime dudenkoff_product_stats

# 2. Make a change (via MySQL or admin)
# Index updates immediately

# 3. Verify
bin/magento dudenkoff:indexer:show-stats
```

### Testing Schedule Mode
```bash
# 1. Set mode
bin/magento indexer:set-mode schedule dudenkoff_product_stats

# 2. Reindex to clean state
bin/magento indexer:reindex dudenkoff_product_stats

# 3. Make changes
# (Index doesn't update immediately)

# 4. Check status (should be Invalid)
bin/magento indexer:status dudenkoff_product_stats

# 5. Process changes
bin/magento cron:run --group index
# OR
bin/magento indexer:reindex dudenkoff_product_stats
```

### Recovery Workflow
```bash
# If indexer stuck or corrupted:
bin/magento indexer:reset dudenkoff_product_stats
bin/magento cache:clean
bin/magento indexer:reindex dudenkoff_product_stats
```

---

## 🔍 Troubleshooting Quick Fixes

### Problem: Indexer stuck in "Processing"
```bash
bin/magento indexer:reset dudenkoff_product_stats
rm -f var/locks/indexer_reindex_*.lock
bin/magento indexer:reindex dudenkoff_product_stats
```

### Problem: Changes not reflected
```bash
# Check mode
bin/magento indexer:show-mode dudenkoff_product_stats

# If schedule mode, run cron
bin/magento cron:run --group index

# Or force reindex
bin/magento indexer:reindex dudenkoff_product_stats
```

### Problem: Indexer not showing up
```bash
bin/magento cache:clean
bin/magento setup:upgrade
bin/magento indexer:info
```

### Problem: Large changelog table
```bash
# Check size
mysql -u root -p magento -e "
SELECT COUNT(*) FROM dudenkoff_product_stats_cl;
"

# Process it
bin/magento cron:run --group index

# Or clear and full reindex
mysql -u root -p magento -e "TRUNCATE dudenkoff_product_stats_cl;"
bin/magento indexer:reindex dudenkoff_product_stats
```

---

## 📝 File Locations

### Configuration Files
```
app/code/Dudenkoff/IndexerLearn/
├── etc/
│   ├── module.xml          # Module declaration
│   ├── indexer.xml         # Indexer configuration
│   ├── mview.xml           # Changelog tracking config
│   ├── db_schema.xml       # Database schema
│   ├── di.xml              # Dependency injection
│   └── events.xml          # Event observers
```

### Core Implementation
```
app/code/Dudenkoff/IndexerLearn/
├── Model/
│   ├── Indexer/
│   │   └── ProductStats.php              # Main indexer logic ⭐
│   └── ResourceModel/
│       ├── ProductStats.php              # Source table operations
│       └── ProductStatsIndex.php         # Index table operations
├── Observer/
│   └── InvalidateStatsIndexObserver.php  # Manual invalidation
└── Console/Command/
    ├── GenerateDataCommand.php
    ├── ShowStatsCommand.php
    └── ClearDataCommand.php
```

---

## 💡 Key Concepts

### Indexer Modes
| Mode | Update Timing | Speed | Use Case |
|------|--------------|-------|----------|
| **Realtime** | Immediately on save | Slower saves | Critical data, small catalogs |
| **Schedule** | On cron schedule | Faster saves | Large catalogs, bulk updates |

### Indexer States
| State | Meaning | Action Needed |
|-------|---------|---------------|
| **Valid** | Up-to-date | None |
| **Invalid** | Needs reindex | Wait for cron or manual reindex |
| **Working** | Currently reindexing | Wait for completion |

### Reindex Types
| Type | Scope | Speed | Trigger |
|------|-------|-------|---------|
| **Full** | All data | Slower | Manual command, first run |
| **Partial** | Changed data only | Faster | Automatic via changelog |

---

## 🎓 Learning Path

1. ✅ **Setup**: Follow [SETUP.md](SETUP.md)
2. ✅ **Concepts**: Read [INDEXER_CONCEPTS.md](INDEXER_CONCEPTS.md)
3. ✅ **Practice**: Try [EXAMPLES.md](EXAMPLES.md)
4. ✅ **Code Review**: Study `Model/Indexer/ProductStats.php`
5. ✅ **Reference**: Use this cheat sheet

---

## 🔗 Useful Links

- **Module README**: [README.md](README.md)
- **Setup Guide**: [SETUP.md](SETUP.md)
- **Concepts Deep Dive**: [INDEXER_CONCEPTS.md](INDEXER_CONCEPTS.md)
- **Hands-On Examples**: [EXAMPLES.md](EXAMPLES.md)
- **Official Docs**: [Magento DevDocs - Indexing](https://developer.adobe.com/commerce/php/development/components/indexing/)

---

## ⚡ Performance Tips

```bash
# Schedule full reindex during off-peak hours
0 3 * * * /path/to/bin/magento indexer:reindex dudenkoff_product_stats

# Use schedule mode for large catalogs
bin/magento indexer:set-mode schedule dudenkoff_product_stats

# Monitor changelog growth
watch -n 5 'mysql -u root -p magento -e "SELECT COUNT(*) FROM dudenkoff_product_stats_cl;"'

# Batch updates instead of one-by-one
# (Let changelog accumulate, then reindex once)
```

---

## 🐛 Debug Commands

```bash
# Check logs
tail -f var/log/system.log | grep IndexerLearn
tail -f var/log/exception.log

# Enable developer mode
bin/magento deploy:mode:set developer

# Check indexer table status
mysql -u root -p magento -e "SELECT * FROM indexer_state WHERE indexer_id = 'dudenkoff_product_stats';"

# Check for locks
ls -la var/locks/indexer_*

# Clear locks
rm -f var/locks/indexer_reindex_*.lock
```

---

**Quick tip:** Bookmark this page for fast reference! 🔖

