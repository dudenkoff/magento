# Hands-On Examples - IndexerLearn Module

This guide provides practical, step-by-step examples to help you understand Magento indexation through experimentation.

---

## 📚 Table of Contents

1. [Example 1: Basic Full Reindex](#example-1-basic-full-reindex)
2. [Example 2: Comparing Index vs Source Queries](#example-2-comparing-index-vs-source-queries)
3. [Example 3: Testing Realtime Mode](#example-3-testing-realtime-mode)
4. [Example 4: Testing Schedule Mode](#example-4-testing-schedule-mode)
5. [Example 5: Understanding Changelog](#example-5-understanding-changelog)
6. [Example 6: Partial Reindex Performance](#example-6-partial-reindex-performance)
7. [Example 7: Manual Index Invalidation](#example-7-manual-index-invalidation)
8. [Example 8: Bulk Data Updates](#example-8-bulk-data-updates)
9. [Example 9: Index Corruption Recovery](#example-9-index-corruption-recovery)
10. [Example 10: Production Workflow Simulation](#example-10-production-workflow-simulation)

---

## Example 1: Basic Full Reindex

**Goal:** Understand what a full reindex does.

### Step 1: Generate data

```bash
bin/magento dudenkoff:indexer:generate-data 50
```

### Step 2: Check initial state

```bash
# Check indexer status
bin/magento indexer:status dudenkoff_product_stats

# Check source table
mysql -u root -p magento -e "SELECT COUNT(*) as source_count FROM dudenkoff_product_stats;"

# Check index table (should be empty initially)
mysql -u root -p magento -e "SELECT COUNT(*) as index_count FROM dudenkoff_product_stats_idx;"
```

### Step 3: Run full reindex

```bash
time bin/magento indexer:reindex dudenkoff_product_stats
```

**Note the time it took.**

### Step 4: Verify results

```bash
# Check index table now
mysql -u root -p magento -e "SELECT COUNT(*) as index_count FROM dudenkoff_product_stats_idx;"

# View sample data
bin/magento dudenkoff:indexer:show-stats --limit 10
```

### 🔍 What Happened?

1. Indexer read all 50 rows from source table
2. Calculated conversion_rate, average_order_value, popularity_tier for each
3. Inserted 50 rows into index table
4. Marked indexer as "Valid"

### 💡 Key Takeaway

Full reindex rebuilds the ENTIRE index from scratch, regardless of what changed.

---

## Example 2: Comparing Index vs Source Queries

**Goal:** See why indexes are faster.

### Step 1: Generate larger dataset

```bash
bin/magento dudenkoff:indexer:generate-data 1000
bin/magento indexer:reindex dudenkoff_product_stats
```

### Step 2: Query WITHOUT index (slow)

```bash
mysql -u root -p magento -e "
SELECT 
    product_id,
    view_count,
    purchase_count,
    revenue,
    ROUND((purchase_count / view_count * 100), 2) as conversion_rate,
    ROUND((revenue / purchase_count), 4) as avg_order_value,
    CASE 
        WHEN view_count >= 1000 THEN 'high'
        WHEN view_count >= 100 THEN 'medium'
        ELSE 'low'
    END as popularity_tier
FROM dudenkoff_product_stats
WHERE view_count > 500
ORDER BY (purchase_count / view_count * 100) DESC
LIMIT 10;
"
```

**Note: This calculates everything on-the-fly.**

### Step 3: Query WITH index (fast)

```bash
mysql -u root -p magento -e "
SELECT 
    product_id,
    view_count,
    purchase_count,
    revenue,
    conversion_rate,
    average_order_value,
    popularity_tier
FROM dudenkoff_product_stats_idx
WHERE popularity_tier IN ('high', 'medium')
ORDER BY conversion_rate DESC
LIMIT 10;
"
```

**Note: This reads pre-calculated values.**

### 🔍 What's Different?

| Aspect | Without Index | With Index |
|--------|---------------|------------|
| Calculations | Done every query | Pre-calculated once |
| CPU usage | High | Low |
| Query speed | Slower | Faster |
| Data freshness | Always current | Current after reindex |

### 💡 Key Takeaway

Indexes trade storage space and reindex time for query speed.

---

## Example 3: Testing Realtime Mode

**Goal:** See how "Update on Save" mode works.

### Step 1: Set to realtime mode

```bash
bin/magento indexer:set-mode realtime dudenkoff_product_stats
bin/magento indexer:reindex dudenkoff_product_stats
```

### Step 2: View a product's current stats

```bash
mysql -u root -p magento -e "
SELECT * FROM dudenkoff_product_stats_idx WHERE product_id = 1001;
"
```

Note the conversion_rate value.

### Step 3: Update source data

```bash
mysql -u root -p magento -e "
UPDATE dudenkoff_product_stats 
SET view_count = view_count + 100,
    purchase_count = purchase_count + 50
WHERE product_id = 1001;
"
```

### Step 4: Check index immediately

```bash
mysql -u root -p magento -e "
SELECT * FROM dudenkoff_product_stats_idx WHERE product_id = 1001;
"
```

### 🔍 What Happened?

The index updated IMMEDIATELY when you changed the source data.

**Behind the scenes:**
1. Your UPDATE triggered database trigger
2. Changelog entry created
3. Indexer detected change (realtime mode)
4. Indexer IMMEDIATELY reindexed product 1001
5. Index table updated

### Step 5: Check indexer status

```bash
bin/magento indexer:status dudenkoff_product_stats
```

Should still show "Valid" (never became invalid).

### 💡 Key Takeaway

Realtime mode keeps index always up-to-date but adds overhead to write operations.

---

## Example 4: Testing Schedule Mode

**Goal:** See how "Update on Schedule" mode works.

### Step 1: Set to schedule mode

```bash
bin/magento indexer:set-mode schedule dudenkoff_product_stats
bin/magento indexer:reindex dudenkoff_product_stats
```

### Step 2: View current stats

```bash
mysql -u root -p magento -e "
SELECT * FROM dudenkoff_product_stats_idx WHERE product_id = 1002;
"
```

### Step 3: Update source data

```bash
mysql -u root -p magento -e "
UPDATE dudenkoff_product_stats 
SET view_count = 9999,
    purchase_count = 500
WHERE product_id = 1002;
"
```

### Step 4: Check index immediately

```bash
mysql -u root -p magento -e "
SELECT * FROM dudenkoff_product_stats_idx WHERE product_id = 1002;
"
```

**Note:** Index hasn't updated yet!

### Step 5: Check indexer status

```bash
bin/magento indexer:status dudenkoff_product_stats
```

Should show "Invalid" now.

### Step 6: Check changelog

```bash
mysql -u root -p magento -e "
SELECT * FROM dudenkoff_product_stats_cl ORDER BY version_id DESC LIMIT 5;
"
```

You should see an entry for product 1002's entity_id.

### Step 7: Process the changelog

```bash
# Simulate cron job
bin/magento cron:run --group index

# Or manually reindex
bin/magento indexer:reindex dudenkoff_product_stats
```

### Step 8: Check index again

```bash
mysql -u root -p magento -e "
SELECT * FROM dudenkoff_product_stats_idx WHERE product_id = 1002;
"
```

Now it's updated!

### 🔍 What Happened?

1. Update created changelog entry
2. Indexer marked as "Invalid"
3. Index stayed stale until cron/manual reindex
4. Reindex processed only changed IDs
5. Index updated, status back to "Valid"

### 💡 Key Takeaway

Schedule mode delays indexing for better write performance, but index may be temporarily stale.

---

## Example 5: Understanding Changelog

**Goal:** Deep dive into the changelog mechanism.

### Step 1: Ensure schedule mode and empty changelog

```bash
bin/magento indexer:set-mode schedule dudenkoff_product_stats
bin/magento indexer:reindex dudenkoff_product_stats

# Changelog should be empty after reindex
mysql -u root -p magento -e "SELECT * FROM dudenkoff_product_stats_cl;"
```

### Step 2: Make multiple changes to same product

```bash
mysql -u root -p magento -e "
UPDATE dudenkoff_product_stats SET view_count = view_count + 1 WHERE product_id = 1010;
UPDATE dudenkoff_product_stats SET view_count = view_count + 1 WHERE product_id = 1010;
UPDATE dudenkoff_product_stats SET view_count = view_count + 1 WHERE product_id = 1010;
"
```

### Step 3: Check changelog

```bash
mysql -u root -p magento -e "
SELECT * FROM dudenkoff_product_stats_cl;
"
```

**Expected:** 3 entries (one for each update) with same entity_id but different version_ids.

### Step 4: Make changes to different products

```bash
mysql -u root -p magento -e "
UPDATE dudenkoff_product_stats SET view_count = view_count + 1 WHERE product_id = 1011;
UPDATE dudenkoff_product_stats SET view_count = view_count + 1 WHERE product_id = 1012;
UPDATE dudenkoff_product_stats SET view_count = view_count + 1 WHERE product_id = 1013;
"
```

### Step 5: Check changelog again

```bash
mysql -u root -p magento -e "
SELECT * FROM dudenkoff_product_stats_cl ORDER BY version_id;
"
```

**Expected:** 6 total entries.

### Step 6: Process changelog

```bash
bin/magento indexer:reindex dudenkoff_product_stats
```

### Step 7: Check changelog after processing

```bash
mysql -u root -p magento -e "
SELECT * FROM dudenkoff_product_stats_cl;
"
```

**Expected:** Empty (entries removed after processing).

### 🔍 What Happened?

1. Each UPDATE created a changelog entry
2. Same product_id can have multiple entries (version_id differs)
3. Indexer processes unique entity_ids (de-duplicates automatically)
4. After reindexing, changelog cleared

### 💡 Key Takeaway

Changelog tracks WHAT changed, not the full data. It's a lightweight change log.

---

## Example 6: Partial Reindex Performance

**Goal:** Compare full vs partial reindex speed.

### Step 1: Create large dataset

```bash
bin/magento dudenkoff:indexer:generate-data 5000
bin/magento indexer:reindex dudenkoff_product_stats
```

### Step 2: Time a full reindex

```bash
time bin/magento indexer:reindex dudenkoff_product_stats
```

**Note the time** (e.g., 15 seconds for 5000 products).

### Step 3: Set to schedule mode and make small change

```bash
bin/magento indexer:set-mode schedule dudenkoff_product_stats
bin/magento indexer:reindex dudenkoff_product_stats

# Change just 5 products
mysql -u root -p magento -e "
UPDATE dudenkoff_product_stats 
SET view_count = view_count + 1 
WHERE product_id IN (1001, 1002, 1003, 1004, 1005);
"
```

### Step 4: Time the partial reindex

```bash
time bin/magento indexer:reindex dudenkoff_product_stats
```

**Note the time** (should be much faster, e.g., 0.5 seconds).

### 🔍 What Happened?

- Full reindex: Processed all 5000 products → 15 seconds
- Partial reindex: Processed only 5 products → 0.5 seconds

### 💡 Key Takeaway

Partial reindexing is MUCH faster for large catalogs when only a few items change.

---

## Example 7: Manual Index Invalidation

**Goal:** Learn how to programmatically invalidate an index.

### Step 1: Create test event dispatcher

Create: `app/code/Dudenkoff/IndexerLearn/Console/Command/TriggerEventCommand.php`

```php
<?php
namespace Dudenkoff\IndexerLearn\Console\Command;

use Magento\Framework\Event\ManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TriggerEventCommand extends Command
{
    private $eventManager;

    public function __construct(ManagerInterface $eventManager)
    {
        parent::__construct();
        $this->eventManager = $eventManager;
    }

    protected function configure()
    {
        $this->setName('dudenkoff:indexer:trigger-event');
        $this->setDescription('Trigger index invalidation event');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Dispatching event...');
        $this->eventManager->dispatch('dudenkoff_stats_updated', [
            'product_ids' => [1001, 1002, 1003]
        ]);
        $output->writeln('Event dispatched!');
        return 0;
    }
}
```

### Step 2: Register command in di.xml

(Already registered if you followed setup)

### Step 3: Set schedule mode and reindex

```bash
bin/magento indexer:set-mode schedule dudenkoff_product_stats
bin/magento indexer:reindex dudenkoff_product_stats
bin/magento indexer:status dudenkoff_product_stats
```

Should show "Valid".

### Step 4: Trigger the event

```bash
bin/magento dudenkoff:indexer:trigger-event
```

### Step 5: Check indexer status

```bash
bin/magento indexer:status dudenkoff_product_stats
```

Should now show "Invalid"!

### Step 6: Check logs

```bash
tail -f var/log/system.log | grep IndexerLearn
```

Look for the invalidation message.

### 🔍 What Happened?

1. Event dispatched
2. Observer caught event
3. Observer called `$indexer->invalidate()`
4. Indexer marked as "Invalid"

### 💡 Key Takeaway

You can manually invalidate indexes from your code when business logic requires it.

---

## Example 8: Bulk Data Updates

**Goal:** See how indexer handles large batches.

### Step 1: Prepare

```bash
bin/magento indexer:set-mode schedule dudenkoff_product_stats
bin/magento indexer:reindex dudenkoff_product_stats
```

### Step 2: Update many rows at once

```bash
mysql -u root -p magento -e "
UPDATE dudenkoff_product_stats 
SET view_count = view_count + FLOOR(RAND() * 100)
WHERE product_id >= 1001 AND product_id <= 1500;
"
```

This updates 500 products.

### Step 3: Check changelog size

```bash
mysql -u root -p magento -e "
SELECT COUNT(*) as changelog_entries FROM dudenkoff_product_stats_cl;
"
```

Should show ~500 entries.

### Step 4: Reindex and time it

```bash
time bin/magento indexer:reindex dudenkoff_product_stats
```

### 🔍 What Happened?

Indexer processed 500 changed IDs efficiently using partial reindex.

### 💡 Key Takeaway

Schedule mode handles bulk updates gracefully by batching changelog processing.

---

## Example 9: Index Corruption Recovery

**Goal:** Learn how to recover from index issues.

### Step 1: Simulate corruption

```bash
# Manually delete some index data
mysql -u root -p magento -e "
DELETE FROM dudenkoff_product_stats_idx WHERE product_id < 1050;
"
```

### Step 2: Check stats

```bash
bin/magento dudenkoff:indexer:show-stats --limit 10
```

Some products missing!

### Step 3: Check indexer status

```bash
bin/magento indexer:status dudenkoff_product_stats
```

Might still show "Valid" (doesn't detect corruption automatically).

### Step 4: Reset and reindex

```bash
bin/magento indexer:reset dudenkoff_product_stats
bin/magento indexer:reindex dudenkoff_product_stats
```

### Step 5: Verify recovery

```bash
bin/magento dudenkoff:indexer:show-stats --limit 10
```

All products back!

### 💡 Key Takeaway

`indexer:reset` forces a full rebuild, useful for recovering from corruption.

---

## Example 10: Production Workflow Simulation

**Goal:** Simulate a typical production indexing workflow.

### Step 1: Initial setup (like going live)

```bash
# Generate production-like data
bin/magento dudenkoff:indexer:generate-data 2000

# Set to schedule mode (production best practice)
bin/magento indexer:set-mode schedule dudenkoff_product_stats

# Initial full reindex
bin/magento indexer:reindex dudenkoff_product_stats
```

### Step 2: Daily operations - product updates

```bash
# Simulate: Admin updates 20 products during the day
mysql -u root -p magento -e "
UPDATE dudenkoff_product_stats 
SET view_count = view_count + FLOOR(RAND() * 50),
    purchase_count = purchase_count + FLOOR(RAND() * 10)
WHERE product_id BETWEEN 1001 AND 1020;
"
```

### Step 3: Check impact

```bash
# Indexer marked invalid
bin/magento indexer:status dudenkoff_product_stats

# But operations continue normally (no blocking)
```

### Step 4: Scheduled cron run

```bash
# This would run automatically every minute in production
bin/magento cron:run --group index

# Check status
bin/magento indexer:status dudenkoff_product_stats
```

Now "Valid" again!

### Step 5: Weekly maintenance - full reindex

```bash
# In production, schedule this during off-peak hours
# Example: 3 AM Sunday
bin/magento indexer:reindex dudenkoff_product_stats
```

### 💡 Production Best Practices

1. ✅ Use "Update on Schedule" for all but critical indexers
2. ✅ Let cron handle regular updates
3. ✅ Schedule full reindexes during off-peak hours
4. ✅ Monitor changelog growth
5. ✅ Set up alerting for "Invalid" status lasting too long

---

## 🎓 Summary

You've learned:

- ✅ How to run full and partial reindexes
- ✅ Difference between realtime and schedule modes
- ✅ How changelog tracks changes
- ✅ How to manually invalidate indexes
- ✅ Performance characteristics of different modes
- ✅ Recovery from index corruption
- ✅ Production workflow best practices

---

## 🚀 Next Steps

1. Build your own custom indexer for your use case
2. Review Magento's core indexers (catalog_product_price, etc.)
3. Optimize existing indexers in your project
4. Implement monitoring for indexer status

---

## 💡 Challenge Yourself

Try these advanced experiments:

1. **Multi-table indexing**: Extend the indexer to join with Magento's catalog_product_entity
2. **Dimensional indexing**: Add store_id dimension for multi-store indexing
3. **Custom scheduling**: Create a custom cron that reindexes specific products
4. **Performance tuning**: Optimize the indexer for 100,000+ products
5. **Index monitoring**: Build a dashboard showing indexer health

Happy learning! 🎉

