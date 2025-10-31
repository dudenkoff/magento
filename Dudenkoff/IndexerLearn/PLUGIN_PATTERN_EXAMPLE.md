# Plugin Pattern for Automatic Reindexing

## 🎯 Overview

This example demonstrates the **Magento Plugin Pattern** for automatically triggering indexer updates after model save operations.

## 📁 Files in This Example

### 1. **ProductStatsProcessor** - The Processor
`Model/Indexer/ProductStatsProcessor.php`

```php
class ProductStatsProcessor extends AbstractProcessor
{
    const INDEXER_ID = 'dudenkoff_product_stats';
}
```
- Extends `Magento\Framework\Indexer\AbstractProcessor`
- Automatically handles realtime/schedule mode checking

### 2. **ProductStats** - The Model
`Model/ProductStats.php`

Simple model for CRUD operations on product stats.

### 3. **ProductStatsResource** - The ResourceModel
`Model/ResourceModel/ProductStats.php`

Handles database operations for the model.

### 4. **ReindexAfterSavePlugin** - The Plugin (Key Component)
`Plugin/ReindexAfterSavePlugin.php`

**This is the magic!** Automatically triggers reindex after save:

```php
class ReindexAfterSavePlugin
{
    private $productStatsProcessor;
    
    /**
     * After save - trigger reindex based on mode
     */
    public function afterSave($subject, $result, AbstractModel $object)
    {
        if (!$object->hasDataChanges() && !$object->isObjectNew()) {
            return $result;
        }
        
        // Automatically handles realtime vs schedule mode!
        $this->productStatsProcessor->reindexRow($object->getId());
        
        return $result;
    }
}
```

### 5. **Plugin Configuration** - di.xml
```xml
<!-- Intercept save method of ProductStats ResourceModel -->
<type name="Dudenkoff\IndexerLearn\Model\ResourceModel\ProductStats">
    <plugin name="dudenkoff_reindex_after_save" 
            type="Dudenkoff\IndexerLearn\Plugin\ReindexAfterSavePlugin" 
            sortOrder="100"/>
</type>
```

---

## 🔍 How It Works

### **The Flow:**

```
1. Code calls: $productStatsResource->save($model)
   ↓
2. Original save() method executes
   ↓
3. Data saved to database
   ↓
4. PLUGIN INTERCEPTS (afterSave)
   ↓
5. Plugin checks if data changed
   ↓
6. Plugin calls: $processor->reindexRow($id)
   ↓
7. Processor checks indexer mode:
   - Realtime: triggers immediate reindex ⚡
   - Schedule: does nothing (mview handles it) ⏰
   ↓
8. Returns result to caller
```

---

## 🧪 Testing

### **Test the Plugin:**

```bash
# Make sure you have data
bin/magento setup:upgrade

# Set to REALTIME mode
bin/magento indexer:set-mode realtime dudenkoff_product_stats

# Test model save - plugin will trigger immediate reindex
bin/magento dudenkoff:indexer:test-model-save --product-id=1 --views=10

# Expected output shows plugin intercepting and reindexing
```

### **Output in Realtime Mode:**

```
=== MODEL SAVE WITH PLUGIN DEMO ===

Current Indexer Mode: Realtime (Update on Save)

Step 1: Loading ProductStats model for product 1...
  Current view count: 1500

Step 2: Updating view count (+10)...

Step 3: Saving model...
  Plugin 'ReindexAfterSavePlugin' will intercept the save operation

  ✓ Model saved successfully!
  New view count: 1510

Step 4: What happened behind the scenes:
  1. Model->save() was called
  2. ResourceModel saved data to database
  3. Plugin intercepted afterSave()
  4. Plugin called ProductStatsProcessor->reindexRow()
  5. ⚡ Processor triggered immediate reindex (Realtime mode)
  6. Index is now up-to-date!
```

### **Output in Schedule Mode:**

```bash
# Switch to SCHEDULE mode
bin/magento indexer:set-mode schedule dudenkoff_product_stats

# Test again
bin/magento dudenkoff:indexer:test-model-save --product-id=1 --views=10
```

```
=== MODEL SAVE WITH PLUGIN DEMO ===

Current Indexer Mode: Schedule (Update on Schedule)

Step 1: Loading ProductStats model for product 1...
  Current view count: 1510

Step 2: Updating view count (+10)...

Step 3: Saving model...
  Plugin 'ReindexAfterSavePlugin' will intercept the save operation

  ✓ Model saved successfully!
  New view count: 1520

Step 4: What happened behind the scenes:
  1. Model->save() was called
  2. ResourceModel saved data to database
  3. Plugin intercepted afterSave()
  4. Plugin called ProductStatsProcessor->reindexRow()
  5. ⏰ Processor skipped reindex (Schedule mode)
  6. Change logged to changelog table
  7. Cron will process it later
```

---

## 🎓 Understanding Plugins

### **Plugin Types:**

1. **before** - Runs BEFORE the original method
2. **after** - Runs AFTER the original method (we use this)
3. **around** - Wraps the original method completely

### **afterSave Plugin Signature:**

```php
public function afterSave(
    $subject,           // The ResourceModel being intercepted
    $result,            // Return value from original save()
    AbstractModel $object  // The model that was saved
) {
    // Your logic here
    
    return $result;  // Must return the result
}
```

### **Key Points:**

- ✅ Plugin intercepts ResourceModel save, not Model save
- ✅ Checks if data actually changed before reindexing
- ✅ Uses Processor which handles mode checking automatically
- ✅ Doesn't break save operation if indexing fails
- ✅ Logs what happened for debugging

---

## 💡 When to Use This Pattern

### **Use Plugin Pattern When:**

1. ✅ You want automatic reindexing on model save
2. ✅ You're using Models for CRUD operations
3. ✅ You need tight coupling between save and reindex
4. ✅ You want to extend existing code without modifying it

### **Don't Use Plugin Pattern When:**

1. ❌ Already using mview (it handles it automatically)
2. ❌ Only doing direct database updates (use mview)
3. ❌ Performance is critical (plugins add overhead)
4. ❌ Simple schedule-only indexer (mview is enough)

---

## 🔄 Comparison: mview vs Plugin

### **mview (Database Triggers)**
- ✅ Works with direct DB updates
- ✅ No code coupling
- ✅ Automatic changelog management
- ❌ Requires database triggers

### **Plugin (Application Level)**
- ✅ Works with Model operations
- ✅ More control over when to reindex
- ✅ Can add custom logic
- ❌ Doesn't work with direct DB updates

### **Best Practice:**

**Use BOTH:**
- mview for automatic change tracking (database level)
- Plugin for immediate reindex in realtime mode (application level)

**Actually, in your case with mview configured:**
- mview already triggers reindex in realtime mode automatically
- Plugin is only needed if you want **additional** custom logic

---

## ✅ Summary

You now have:

1. ✅ **ProductStatsProcessor** - Follows Magento's AbstractProcessor pattern
2. ✅ **ReindexAfterSavePlugin** - Automatically reindexes after save
3. ✅ **ProductStatsService** - Business logic examples
4. ✅ **Test Commands** - Interactive demos

**The plugin automatically handles realtime/schedule modes using Magento's built-in processor pattern!** 🚀

---

## 🎯 Key Takeaway

```php
// Just save your model normally
$model->setData('view_count', $newValue);
$resourceModel->save($model);

// Plugin automatically:
// 1. Intercepts the save
// 2. Checks indexer mode
// 3. Reindexes if in realtime mode
// 4. Skips if in schedule mode (mview handles it)
```

**No manual mode checking needed in your business logic!** ✨

