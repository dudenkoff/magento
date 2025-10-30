# Magento 2 Indexer Concepts - Deep Dive

## 📖 Table of Contents

1. [What is Indexing?](#what-is-indexing)
2. [Why Magento Uses Indexing](#why-magento-uses-indexing)
3. [Indexer Architecture](#indexer-architecture)
4. [Indexer Modes](#indexer-modes)
5. [🚨 Common Misconception: Direct Database Changes](#-common-misconception-direct-database-changes)
6. [Materialized Views (Mview)](#materialized-views-mview)
7. [Changelog Mechanism](#changelog-mechanism)
8. [Indexer States](#indexer-states)
9. [Full vs Partial Reindex](#full-vs-partial-reindex)
10. [Performance Considerations](#performance-considerations)
11. [Advanced Topics](#advanced-topics)

---

## What is Indexing?

**Indexing** is the process of transforming data into an optimized format for fast retrieval.

### The Problem

Imagine calculating product prices on every page load:

```php
// SLOW - Calculated every time
$finalPrice = $product->getBasePrice()
    - $product->getSpecialPrice()
    - $product->getCatalogRuleDiscount()
    - $product->getTierPriceDiscount()
    + $product->getTaxAmount();
```

For a category page with 50 products, this means:
- 50 database queries for base prices
- 50 calculations for special prices
- 50 catalog rule checks
- 50 tier price calculations
- 50 tax calculations

**Total: 250+ operations per page load** 😱

### The Solution

Pre-calculate and store the final price:

```php
// FAST - Just read from index
$finalPrice = $indexTable->getFinalPrice($productId);
```

Now it's just **1 database read**. This is what indexing does.

---

## Why Magento Uses Indexing

Magento is a complex e-commerce system with:

1. **Complex Relationships**: Products → Categories → Rules → Prices
2. **Heavy Calculations**: Tax, discounts, tier pricing, inventory
3. **High Traffic**: Thousands of visitors browsing simultaneously
4. **Frequent Reads, Rare Writes**: Products viewed far more than edited

### Without Indexing
```
Customer browses category
  ↓
Calculate prices for 50 products (slow)
  ↓
Apply catalog rules (slow)
  ↓
Check stock (slow)
  ↓
Filter by attributes (slow)
  ↓
Page load: 5+ seconds ❌
```

### With Indexing
```
Customer browses category
  ↓
Read pre-calculated data from index
  ↓
Page load: 0.5 seconds ✅
```

---

## Indexer Architecture

### Core Components

```
┌─────────────────────────────────────────────────────────────┐
│                     1. SOURCE DATA                          │
│              (Your tables: products, stats, etc.)           │
└────────────────────────────┬────────────────────────────────┘
                             │
                             │ Data changes
                             ↓
┌─────────────────────────────────────────────────────────────┐
│                     2. CHANGELOG                            │
│           Tracks WHAT changed and WHEN                      │
│           (automatically via database triggers)             │
└────────────────────────────┬────────────────────────────────┘
                             │
                             │ Processed by
                             ↓
┌─────────────────────────────────────────────────────────────┐
│                     3. INDEXER ACTION                       │
│              Your custom logic to transform data            │
│              (implements ActionInterface)                   │
└────────────────────────────┬────────────────────────────────┘
                             │
                             │ Writes to
                             ↓
┌─────────────────────────────────────────────────────────────┐
│                     4. INDEX TABLE                          │
│           Optimized, pre-calculated data                    │
│           (fast reads for frontend/admin)                   │
└─────────────────────────────────────────────────────────────┘
```

### Configuration Files

#### 1. `etc/indexer.xml`
Declares your indexer:
```xml
<indexer id="dudenkoff_product_stats" 
         view_id="dudenkoff_product_stats" 
         class="Dudenkoff\IndexerLearn\Model\Indexer\ProductStats">
    <title>Product Statistics Indexer</title>
    <description>Aggregates product statistics</description>
</indexer>
```

#### 2. `etc/mview.xml`
Configures change tracking:
```xml
<view id="dudenkoff_product_stats" 
      class="Dudenkoff\IndexerLearn\Model\Indexer\ProductStats">
    <subscriptions>
        <!-- Watch this table for changes -->
        <table name="dudenkoff_product_stats" entity_column="entity_id" />
    </subscriptions>
</view>
```

#### 3. `etc/db_schema.xml`
Defines source and index tables.

---

## Indexer Modes

Magento indexers can run in two modes:

### 1. Update on Save (Realtime Mode)

```bash
bin/magento indexer:set-mode realtime dudenkoff_product_stats
```

**How it works:**
```
1. Admin saves product (via Magento ORM/Models)
2. Product model fires save event (catalog_product_save_after)
3. Mview system detects event
4. Indexer IMMEDIATELY runs (same request)
5. Index updated
6. Save completes
```

**⚠️ CRITICAL: Realtime Mode Limitation**

Realtime mode **ONLY works when you use Magento's ORM** (models). It relies on **application-level events** to trigger immediate reindexing.

```php
// ✅ WORKS - Triggers realtime reindex
$product->setName('New Name')->save();

// ❌ DOESN'T WORK - Bypasses events
$connection->update('catalog_product_entity', 
    ['name' => 'New Name'], 
    ['entity_id = ?' => 1]
);
```

**What happens with direct DB changes:**
1. ✅ Database trigger logs change to changelog table
2. ❌ But no application event fires
3. ❌ So no immediate reindex happens
4. ❌ Index becomes stale until manual reindex

**Solution:** Always use Magento's models, or manually trigger reindex after direct DB changes.

**Pros:**
- ✅ Index always 100% up-to-date (when using Magento ORM)
- ✅ No delay between change and reflection

**Cons:**
- ❌ Slower save operations
- ❌ May timeout on bulk updates
- ❌ Higher server load during saves
- ❌ **Doesn't work with direct database changes**

**Use when:**
- Real-time accuracy is critical
- Low traffic stores
- Small catalogs (<10k products)
- All changes go through Magento's ORM

### 2. Update on Schedule (Deferred Mode)

```bash
bin/magento indexer:set-mode schedule dudenkoff_product_stats
```

**How it works:**
```
1. Admin saves product
2. Change logged to changelog table
3. Indexer marked as "Invalid"
4. Save completes (fast!)
5. [Later] Cron job processes changelog
6. Only changed items reindexed
7. Indexer marked as "Valid"
```

**Pros:**
- ✅ Fast save operations
- ✅ Batch processing (efficient)
- ✅ No save timeouts

**Cons:**
- ❌ Index may be stale until cron runs
- ❌ Requires properly configured cron

**Use when:**
- High traffic stores
- Large catalogs (>10k products)
- Bulk imports/updates
- Changes don't need immediate reflection

**Cron Jobs:**
```bash
# Runs every minute by default
indexer_update_all_views  # Processes all scheduled indexers
indexer_clean_all_changelogs  # Cleans processed changelog entries
```

---

## 🚨 Common Misconception: Direct Database Changes

### The Problem

Many developers assume that in **realtime mode**, any database change will trigger immediate reindexing because mview uses database triggers. **This is WRONG.**

### The Reality

```
┌─────────────────────────────────────────────────────────────────┐
│                    TWO SEPARATE SYSTEMS                         │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  1. CHANGE LOGGING (Database Triggers)                         │
│     - Always runs (schedule or realtime mode)                  │
│     - Triggered by ANY database change                         │
│     - Logs to changelog table                                  │
│     ✅ Works with direct DB changes                            │
│                                                                 │
│  2. IMMEDIATE REINDEXING (Application Events)                  │
│     - Only in realtime mode                                    │
│     - Triggered by Magento ORM save/delete operations          │
│     - Reads changelog and reindexes immediately                │
│     ❌ Does NOT work with direct DB changes                    │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### Example Scenarios

#### Scenario 1: Using Magento Models (Realtime Mode)

```php
// Using Magento's ORM
$product = $productRepository->getById(1);
$product->setPrice(99.99);
$productRepository->save($product);

// What happens:
// 1. ✅ Model fires 'catalog_product_save_after' event
// 2. ✅ Database trigger logs change to changelog
// 3. ✅ Mview event observer sees the event
// 4. ✅ Calls $indexer->executeList([1]) immediately
// 5. ✅ Index updated in same request
```

#### Scenario 2: Direct Database Update (Realtime Mode)

```php
// Direct database update
$connection->update(
    'catalog_product_entity_decimal',
    ['value' => 99.99],
    ['entity_id = ?' => 1, 'attribute_id = ?' => $priceAttributeId]
);

// What happens:
// 1. ✅ Database trigger logs change to changelog
// 2. ❌ NO application event fires
// 3. ❌ NO immediate reindex
// 4. ❌ Index is now stale
// 5. ❌ Frontend shows old price until manual reindex
```

#### Scenario 3: Direct Database Update (Schedule Mode)

```php
// Direct database update
$connection->update(
    'catalog_product_entity_decimal',
    ['value' => 99.99],
    ['entity_id = ?' => 1]
);

// What happens:
// 1. ✅ Database trigger logs change to changelog
// 2. ⏳ Indexer marked as "Invalid"
// 3. ⏳ Wait for cron (indexer_update_all_views)
// 4. ✅ Cron reads changelog and reindexes product 1
// 5. ✅ Eventually consistent
```

### Solutions

#### Option 1: Always Use Magento's ORM (Recommended)

```php
// ✅ CORRECT
$product = $productRepository->getById(1);
$product->setPrice(99.99);
$productRepository->save($product);
```

#### Option 2: Manually Trigger Reindex After Direct DB Changes

```php
// Direct DB update
$connection->update('catalog_product_entity_decimal', 
    ['value' => 99.99], 
    ['entity_id = ?' => 1]
);

// Manually trigger reindex
$indexer = $indexerRegistry->get('catalog_product_price');
$indexer->executeList([1]); // Reindex only product 1
```

#### Option 3: Use Schedule Mode for Bulk Operations

If you're doing bulk imports/updates, use schedule mode:

```bash
# Switch to schedule mode for bulk operation
bin/magento indexer:set-mode schedule catalog_product_price

# Do your bulk direct DB updates
# ...changes logged to changelog automatically...

# Cron will process them
# OR manually trigger: bin/magento indexer:reindex catalog_product_price
```

### Summary Table

| Change Method | Realtime Mode | Schedule Mode |
|---------------|---------------|---------------|
| **Magento ORM** | ✅ Immediate reindex | ✅ Logged, processed by cron |
| **Direct DB Update** | ❌ Only logged, no reindex | ✅ Logged, processed by cron |
| **Mass Action** | ✅ Immediate but slow | ✅ Fast, processed by cron |
| **Import/Export** | ✅ Immediate but may timeout | ✅ Recommended |

---

## Materialized Views (Mview)

Mview is Magento's system for **tracking database changes automatically**.

### How It Works

When you configure mview in `mview.xml`:

```xml
<view id="dudenkoff_product_stats">
    <subscriptions>
        <table name="dudenkoff_product_stats" entity_column="entity_id" />
    </subscriptions>
</view>
```

Magento automatically:

1. **Creates database triggers** on `dudenkoff_product_stats`
2. **Creates changelog table** `dudenkoff_product_stats_cl`
3. **Logs changes** whenever rows are inserted/updated/deleted

### Changelog Table Structure

```sql
CREATE TABLE dudenkoff_product_stats_cl (
    version_id INT PRIMARY KEY AUTO_INCREMENT,  -- Unique change ID
    entity_id INT NOT NULL                       -- Which row changed
);
```

### Example Flow

```
Step 1: Product stat updated
UPDATE dudenkoff_product_stats 
SET view_count = view_count + 1 
WHERE entity_id = 42;

Step 2: Database trigger fires (automatically)
INSERT INTO dudenkoff_product_stats_cl (entity_id) VALUES (42);

Step 3: Indexer marked as "Invalid"

Step 4: Cron runs indexer_update_all_views
- Reads dudenkoff_product_stats_cl
- Finds entity_id = 42 changed
- Calls indexer->execute([42])
- Reindexes only product 42
- Deletes processed changelog entries

Step 5: Indexer marked as "Valid"
```

### Benefits

- ✅ **Automatic tracking**: No manual code needed
- ✅ **Efficient**: Only tracks IDs, not full data
- ✅ **Batch processing**: Multiple changes grouped
- ✅ **Database-level tracking**: Triggers log ALL changes (even direct DB updates)

### ⚠️ Important Distinction

**What mview DOES:**
- ✅ Database triggers always log changes to changelog table
- ✅ Works even with direct database modifications
- ✅ Tracks WHAT changed and WHEN

**What mview DOESN'T DO (in realtime mode):**
- ❌ Triggers don't cause immediate reindexing
- ❌ Immediate reindex requires application events
- ❌ Direct DB changes bypass application events
- ❌ Result: Change logged, but not immediately reindexed

**Bottom line:** 
- In **schedule mode**: Direct DB changes work fine (cron will process changelog)
- In **realtime mode**: Direct DB changes break immediate reindexing

---

## Changelog Mechanism

### Viewing Changelog Data

```sql
-- Check what's in the changelog
SELECT * FROM dudenkoff_product_stats_cl;

-- Example output:
-- version_id | entity_id
-- ---------- | ---------
-- 1          | 42
-- 2          | 43
-- 3          | 42  (changed again)
```

### Changelog Processing

The mview system processes changelogs in batches:

```php
// Simplified version of what Magento does
$changelog = $this->getChangelog();
$changeIds = $changelog->getList($lastVersionId, $currentVersionId);
// Returns: [42, 43, 42]

$uniqueIds = array_unique($changeIds);
// Returns: [42, 43]

$indexer->execute($uniqueIds);
// Reindexes only products 42 and 43

$changelog->clear($currentVersionId);
// Removes processed entries
```

### Changelog States

| State | Meaning | Action Required |
|-------|---------|-----------------|
| Empty | No changes since last reindex | None |
| Has entries | Changes waiting to be processed | Wait for cron or manual reindex |
| Growing rapidly | Heavy write load | Normal for schedule mode |

---

## Indexer States

Magento tracks indexer status in `indexer_state` table.

### States

| State | Description | Causes |
|-------|-------------|--------|
| **Valid** | Index is up-to-date | After successful reindex |
| **Invalid** | Index is stale | Data changed (schedule mode) |
| **Working** | Currently reindexing | During reindex process |

### Checking State

```bash
bin/magento indexer:status

# Output:
# Product Statistics Indexer (Learning):
#   Status: Invalid
#   Updated: 2025-10-27 10:30:00
#   Mode: Update on Schedule
```

### State Transitions

```
[Valid] 
   ↓
Data changes
   ↓
[Invalid] ← Stays here until reindex
   ↓
Manual reindex OR cron runs
   ↓
[Working]
   ↓
Reindex completes
   ↓
[Valid]
```

---

## Full vs Partial Reindex

### Full Reindex

Rebuilds the **entire index** from scratch.

```bash
bin/magento indexer:reindex dudenkoff_product_stats
```

**Process:**
1. Truncate index table (delete all)
2. Read ALL source data
3. Calculate ALL derived data
4. Insert ALL into index table

**When used:**
- Manual reindex command
- First time running indexer
- After major data changes
- Index corruption

**Performance:**
```
100 products    → ~1 second
1,000 products  → ~5 seconds
10,000 products → ~30 seconds
100,000 products → ~5 minutes
```

### Partial Reindex

Reindexes only **changed rows**.

```bash
# Triggered automatically by cron in schedule mode
```

**Process:**
1. Read changelog: which IDs changed?
2. Fetch only those rows from source
3. Calculate only those rows
4. Update/insert only those rows in index

**Performance:**
```
10 changed products → ~0.1 seconds (regardless of catalog size!)
```

This is why **schedule mode is much more efficient** for large catalogs.

---

## Performance Considerations

### Indexing Speed Factors

1. **Catalog Size**: More products = longer full reindex
2. **Data Complexity**: Complex calculations = slower indexing
3. **Server Resources**: CPU/RAM/disk speed
4. **Database Performance**: Indexes, query optimization
5. **Concurrent Load**: Running during peak traffic = slower

### Optimization Strategies

#### 1. Use Batch Operations

```php
// ❌ BAD: Insert one at a time
foreach ($data as $row) {
    $connection->insert($table, $row);
}

// ✅ GOOD: Batch insert
$connection->insertMultiple($table, $data);
```

#### 2. Use Appropriate Mode

- **Realtime**: Small stores, critical data
- **Schedule**: Large stores, analytics data

#### 3. Run During Off-Peak Hours

```bash
# Schedule full reindex at 3 AM
0 3 * * * /path/to/bin/magento indexer:reindex
```

#### 4. Monitor Changelog Growth

```sql
SELECT COUNT(*) FROM dudenkoff_product_stats_cl;
-- If this grows to thousands, reindex more frequently
```

#### 5. Use Partial Reindex When Possible

```php
// ❌ BAD: Full reindex after small change
$indexer->executeFull();

// ✅ GOOD: Partial reindex
$indexer->executeList([$changedId]);
```

---

## Advanced Topics

### 1. Composite Indexers

Some indexers depend on others:

```xml
<indexer id="catalog_product_price">
    <dependencies>
        <indexer id="catalog_product_flat" />
        <indexer id="catalog_category_product" />
    </dependencies>
</indexer>
```

Dependencies reindex first automatically.

### 2. Shared Indexes

Multiple indexers can share the same index table:

```xml
<view id="catalog_category_product" table="catalog_category_product_index">
    <!-- Shared by multiple indexers -->
</view>
```

### 3. Custom Dimensions

For multi-store setups:

```php
public function executeFull()
{
    foreach ($this->dimensionProvider->getIterator() as $dimension) {
        $storeId = $dimension->getValue();
        // Index separately per store
    }
}
```

### 4. Index Locking

Prevents concurrent reindex:

```bash
# If stuck, remove lock
rm -f var/locks/indexer_reindex_*.lock
```

### 5. External Indexers

Advanced: Use external search engines (Elasticsearch, Algolia) as indexes.

---

## Summary Cheat Sheet

| Concept | Quick Summary |
|---------|---------------|
| **Indexing** | Pre-calculate data for fast reads |
| **Source Table** | Your original data |
| **Index Table** | Pre-calculated, optimized data |
| **Mview** | Automatic change tracking system |
| **Changelog** | Log of which rows changed |
| **Realtime Mode** | Reindex immediately on save (via Magento ORM only) |
| **Schedule Mode** | Reindex later via cron |
| **Full Reindex** | Rebuild entire index |
| **Partial Reindex** | Update only changed rows |
| **Valid State** | Index is current |
| **Invalid State** | Index needs update |
| **Direct DB Changes** | ⚠️ Break realtime mode; use schedule mode or manual reindex |

---

## Next Steps

1. ✅ Read this document
2. 👉 Try the hands-on [EXAMPLES.md](EXAMPLES.md)
3. 👉 Review the commented source code
4. 👉 Build your own custom indexer!

---

*For questions or improvements, contribute to this educational module!*


