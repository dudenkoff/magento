# Setup Guide - IndexerLearn Module

## 📋 Prerequisites

- Magento 2.4+ installed and running
- Command-line access to Magento
- Basic understanding of Magento CLI commands
- Cron configured (for testing schedule mode)

---

## 🚀 Installation

### Step 1: Enable the Module

The module is already in place at `app/code/Dudenkoff/IndexerLearn/`.

```bash
cd /path/to/magento

# Enable the module
bin/magento module:enable Dudenkoff_IndexerLearn

# Run setup
bin/magento setup:upgrade

# Deploy static content (if needed)
bin/magento setup:static-content:deploy -f

# Compile DI (if needed)
bin/magento setup:di:compile

# Clean cache
bin/magento cache:clean
```

### Step 2: Verify Installation

```bash
bin/magento module:status Dudenkoff_IndexerLearn
```

**Expected output:**
```
Module is enabled
```

### Step 3: Verify Database Tables

```bash
# Connect to MySQL
mysql -u [user] -p [database_name]

# Check tables were created
SHOW TABLES LIKE 'dudenkoff_product%';
```

**Expected output:**
```
dudenkoff_product_stats
dudenkoff_product_stats_cl
dudenkoff_product_stats_idx
```

**Table descriptions:**
- `dudenkoff_product_stats` - Source table (your data)
- `dudenkoff_product_stats_cl` - Changelog table (tracks changes)
- `dudenkoff_product_stats_idx` - Index table (pre-calculated data)

### Step 4: Verify Indexer Registration

```bash
bin/magento indexer:status
```

**Expected output (look for):**
```
Product Statistics Indexer (Learning):
  Status: Invalid
  Updated: 2025-10-27 10:00:00
  Mode: Update by Schedule
```

If you don't see it, run:
```bash
bin/magento cache:clean
bin/magento indexer:info
```

---

## 📊 Generate Test Data

Now let's create sample data to work with:

```bash
# Generate 100 product statistics
bin/magento dudenkoff:indexer:generate-data 100
```

**What this does:**
- Creates 100 rows in `dudenkoff_product_stats`
- Each row has random view counts, purchase counts, and revenue
- Product IDs: 1001-1100

**Output:**
```
Generating 100 sample product statistics...
Inserted batch... (100/100)
✓ Successfully generated 100 product statistics records

Next steps:
1. Check indexer status: bin/magento indexer:status dudenkoff_product_stats
2. Run full reindex:    bin/magento indexer:reindex dudenkoff_product_stats
3. View indexed data:   bin/magento dudenkoff:indexer:show-stats
```

### Verify Data Was Created

```bash
# Using CLI
bin/magento dudenkoff:indexer:show-stats

# Using MySQL
mysql -u [user] -p [database] -e "SELECT COUNT(*) FROM dudenkoff_product_stats;"
```

---

## 🔧 Initial Configuration

### Check Indexer Mode

```bash
bin/magento indexer:show-mode dudenkoff_product_stats
```

**Output:**
```
Product Statistics Indexer (Learning):    Update on Schedule
```

### Change Indexer Mode

#### Option 1: Update on Save (Realtime)

```bash
bin/magento indexer:set-mode realtime dudenkoff_product_stats
```

**Use case:** Testing immediate updates

#### Option 2: Update on Schedule

```bash
bin/magento indexer:set-mode schedule dudenkoff_product_stats
```

**Use case:** Testing changelog and cron-based updates

---

## 🏃 First Reindex

### Run Full Reindex

```bash
bin/magento indexer:reindex dudenkoff_product_stats
```

**What happens:**
1. Reads all rows from `dudenkoff_product_stats`
2. Calculates conversion rates, AOV, popularity tiers
3. Writes to `dudenkoff_product_stats_idx`

**Expected output:**
```
Product Statistics Indexer (Learning) index has been rebuilt successfully in 00:00:01
```

### Check Indexer Status

```bash
bin/magento indexer:status dudenkoff_product_stats
```

**Expected output:**
```
Product Statistics Indexer (Learning):
  Status: Valid    ← Should now be "Valid"
  Updated: 2025-10-27 10:05:00
  Mode: Update by Schedule
```

### View Indexed Data

```bash
bin/magento dudenkoff:indexer:show-stats
```

**Expected output:**
```
=== Indexer Statistics ===
Source table records:  100
Indexed table records: 100

Product ID | Views | Purchases | Revenue   | Conv. Rate % | AOV      | Tier   | Indexed At
-----------|-------|-----------|-----------|--------------|----------|--------|-------------------
1042       | 1823  | 412       | $18556.00 | 22.60%       | $45.04   | high   | 2025-10-27 10:05:12
1015       | 1654  | 389       | $15123.00 | 23.52%       | $38.87   | high   | 2025-10-27 10:05:12
...
```

---

## ⚙️ Cron Configuration (For Schedule Mode)

If testing "Update on Schedule" mode, ensure cron is running.

### Check Cron Status

```bash
bin/magento cron:status
```

### Run Cron Manually (Development)

```bash
# Run all pending cron jobs
bin/magento cron:run

# Or run specific indexer cron jobs
bin/magento cron:run --group index
```

### Key Cron Jobs for Indexers

| Job Name | Schedule | Purpose |
|----------|----------|---------|
| `indexer_update_all_views` | Every minute | Processes changelogs for all scheduled indexers |
| `indexer_clean_all_changelogs` | Every minute | Cleans up processed changelog entries |

### Enable Cron (Production)

Add to your server's crontab:

```bash
crontab -e
```

Add:
```
* * * * * /path/to/php /path/to/magento/bin/magento cron:run >> /path/to/magento/var/log/magento.cron.log
```

---

## 🧪 Verify Everything Works

### Test 1: Full Reindex

```bash
bin/magento indexer:reindex dudenkoff_product_stats
bin/magento dudenkoff:indexer:show-stats --limit 5
```

**Expected:** See 5 products with calculated metrics

### Test 2: Change Data (Realtime Mode)

```bash
# Set to realtime mode
bin/magento indexer:set-mode realtime dudenkoff_product_stats

# Update some data manually
mysql -u [user] -p [database] -e "
UPDATE dudenkoff_product_stats 
SET view_count = view_count + 1000 
WHERE product_id = 1001;
"

# Check if index auto-updated
bin/magento dudenkoff:indexer:show-stats --limit 5
```

**Expected:** Product 1001 should show updated stats immediately

### Test 3: Change Data (Schedule Mode)

```bash
# Set to schedule mode
bin/magento indexer:set-mode schedule dudenkoff_product_stats
bin/magento indexer:reindex dudenkoff_product_stats

# Update some data
mysql -u [user] -p [database] -e "
UPDATE dudenkoff_product_stats 
SET view_count = view_count + 500 
WHERE product_id = 1002;
"

# Check indexer status
bin/magento indexer:status dudenkoff_product_stats
```

**Expected:** Status should be "Invalid"

```bash
# Run cron or manual reindex
bin/magento cron:run --group index
# OR
bin/magento indexer:reindex dudenkoff_product_stats

# Check status again
bin/magento indexer:status dudenkoff_product_stats
```

**Expected:** Status should be "Valid" again

### Test 4: View Changelog

```bash
# Make some changes
mysql -u [user] -p [database] -e "
UPDATE dudenkoff_product_stats 
SET view_count = view_count + 1 
WHERE product_id IN (1001, 1002, 1003);
"

# View changelog entries
mysql -u [user] -p [database] -e "
SELECT * FROM dudenkoff_product_stats_cl;
"
```

**Expected:** See entries for entity_ids corresponding to products 1001-1003

---

## 🐛 Troubleshooting

### Issue: Module not showing in indexer:status

**Solution:**
```bash
bin/magento cache:clean
bin/magento setup:upgrade
bin/magento module:enable Dudenkoff_IndexerLearn
```

### Issue: Tables not created

**Solution:**
```bash
bin/magento setup:db-schema:upgrade
```

Or manually check:
```bash
mysql -u [user] -p [database] -e "SHOW CREATE TABLE dudenkoff_product_stats;"
```

### Issue: Indexer stuck in "Working" status

**Solution:**
```bash
# Reset the indexer
bin/magento indexer:reset dudenkoff_product_stats
bin/magento indexer:reindex dudenkoff_product_stats
```

### Issue: Permission denied errors

**Solution:**
```bash
# Fix permissions
cd /path/to/magento
find var generated pub/static pub/media app/etc -type f -exec chmod 664 {} \;
find var generated pub/static pub/media app/etc -type d -exec chmod 775 {} \;
```

### Issue: Cron not running

**Check cron status:**
```bash
ps aux | grep cron
```

**Check cron logs:**
```bash
tail -f var/log/magento.cron.log
```

**Manually trigger:**
```bash
bin/magento cron:run
```

### Issue: Changelog table growing too large

**This is normal in schedule mode. Clean it:**
```bash
bin/magento cron:run --group index
```

Or manually:
```bash
mysql -u [user] -p [database] -e "
TRUNCATE TABLE dudenkoff_product_stats_cl;
"
# Then reindex
bin/magento indexer:reindex dudenkoff_product_stats
```

### Issue: No data in index table

**Check source table:**
```bash
mysql -u [user] -p [database] -e "
SELECT COUNT(*) FROM dudenkoff_product_stats;
"
```

If empty:
```bash
bin/magento dudenkoff:indexer:generate-data 100
bin/magento indexer:reindex dudenkoff_product_stats
```

---

## 🧹 Cleanup (Optional)

To remove test data:

```bash
bin/magento dudenkoff:indexer:clear-data
```

**Warning:** This deletes ALL data from both source and index tables.

---

## ✅ Setup Complete!

You're now ready to explore the indexer. Next steps:

1. 📖 Read [INDEXER_CONCEPTS.md](INDEXER_CONCEPTS.md) to understand the theory
2. 🧪 Try [EXAMPLES.md](EXAMPLES.md) for hands-on experiments
3. 💻 Review the source code (heavily commented)

---

## 📞 Getting Help

If you encounter issues:

1. Check logs: `var/log/system.log`, `var/log/exception.log`
2. Enable developer mode: `bin/magento deploy:mode:set developer`
3. Check indexer logs for `[IndexerLearn]` entries
4. Review the module's source code comments

Happy learning! 🚀


