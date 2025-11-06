# Installation & Setup Guide

## ✅ Prerequisites

- Magento 2.4.x installed
- Command line access
- Composer (optional, not required for this module)

## 🚀 Installation Steps

### Step 1: Verify Module Files

The module should be located at:
```
app/code/Dudenkoff/CacheLearn/
```

Check files exist:
```bash
ls -la app/code/Dudenkoff/CacheLearn/
```

### Step 2: Enable the Module

```bash
cd /path/to/your/magento

# Enable the module
bin/magento module:enable Dudenkoff_CacheLearn

# You should see: "The following modules have been enabled:"
# - Dudenkoff_CacheLearn
```

### Step 3: Run Setup Upgrade

```bash
# Apply module setup
bin/magento setup:upgrade

# This will:
# - Register the module
# - Register the custom cache type
# - Update database schema (if needed)
```

### Step 4: Deploy Static Content (if needed)

```bash
# For production mode
bin/magento setup:static-content:deploy -f

# For developer mode, this is automatic
```

### Step 5: Enable Custom Cache Type

```bash
# Enable just the custom cache
bin/magento cache:enable dudenkoff_custom_cache

# Or enable all caches
bin/magento cache:enable
```

### Step 6: Verify Installation

```bash
# Check cache status
bin/magento cache:status

# Look for this line:
# dudenkoff_custom_cache                1

# Check module is enabled
bin/magento module:status Dudenkoff_CacheLearn

# Should show under "List of enabled modules:"
```

## 🧪 Test Installation

### Method 1: Visit Demo Page

Open in your browser:
```
http://your-magento-site.com/cachelearn
```

You should see the interactive demo page with examples and documentation.

### Method 2: Test API Endpoints

```bash
# Test simple cache
curl http://your-site.com/cachelearn/demo/simple

# Test complex cache
curl http://your-site.com/cachelearn/demo/complex

# Test cache clearing
curl http://your-site.com/cachelearn/demo/clear
```

### Method 3: Check Logs

```bash
# Watch system log for cache operations
tail -f var/log/system.log | grep -i cache
```

## 🎯 Quick Test

Run these commands in order:

```bash
# 1. Enable everything
bin/magento module:enable Dudenkoff_CacheLearn
bin/magento setup:upgrade
bin/magento cache:enable

# 2. Test the demo
curl http://your-site.com/cachelearn/demo/simple

# 3. Check cache status
bin/magento cache:status | grep dudenkoff
```

Expected output:
```
dudenkoff_custom_cache                1
```

## 🔧 Configuration

### Admin Panel

The custom cache type appears in:
```
System > Tools > Cache Management
```

You'll see:
- **Cache Type:** Dudenkoff Custom Cache
- **Status:** Enabled/Disabled
- **Actions:** Refresh, Enable, Disable

### Command Line

```bash
# View status
bin/magento cache:status

# Enable
bin/magento cache:enable dudenkoff_custom_cache

# Disable
bin/magento cache:disable dudenkoff_custom_cache

# Clean (remove invalid entries)
bin/magento cache:clean dudenkoff_custom_cache

# Flush (remove all entries)
bin/magento cache:flush dudenkoff_custom_cache
```

## 🐛 Troubleshooting

### Issue: Module not appearing

**Solution:**
```bash
# Clear generated files
rm -rf generated/code generated/metadata

# Re-enable
bin/magento module:enable Dudenkoff_CacheLearn
bin/magento setup:upgrade
```

### Issue: Cache type not showing

**Solution:**
```bash
# Flush config cache
bin/magento cache:clean config

# Verify cache.xml exists
cat app/code/Dudenkoff/CacheLearn/etc/cache.xml

# Re-run setup
bin/magento setup:upgrade
```

### Issue: Demo page shows 404

**Solution:**
```bash
# Clear layout and full page cache
bin/magento cache:clean layout full_page

# Check route registration
cat app/code/Dudenkoff/CacheLearn/etc/frontend/routes.xml

# Redeploy static content
bin/magento setup:static-content:deploy -f
```

### Issue: Permission denied

**Solution:**
```bash
# Fix permissions
chmod -R 755 app/code/Dudenkoff/CacheLearn
chown -R www-data:www-data app/code/Dudenkoff/CacheLearn

# Or your web server user
chown -R nginx:nginx app/code/Dudenkoff/CacheLearn
```

### Issue: Cache not working

**Solution:**
```bash
# Check if disabled in env.php
cat app/etc/env.php | grep -A 10 cache

# Enable all caches
bin/magento cache:enable

# Verify cache backend is configured
# Redis: Check app/etc/env.php
# File: Check var/cache/ is writable
```

## 📋 Post-Installation Checklist

- [ ] Module enabled: `bin/magento module:status | grep CacheLearn`
- [ ] Cache type registered: `bin/magento cache:status | grep dudenkoff`
- [ ] Cache type enabled
- [ ] Demo page accessible: `/cachelearn`
- [ ] API endpoints working
- [ ] No errors in logs: `tail var/log/system.log`

## 🎓 Next Steps

1. **Visit demo page:** http://your-site.com/cachelearn
2. **Read Quick Start:** [QUICK_START.md](QUICK_START.md)
3. **Try examples:** Test all three API endpoints
4. **Read documentation:** [README.md](README.md)
5. **Study code:** `Service/CacheService.php`

## 📝 Environment Considerations

### Development Mode

```bash
# Set to developer mode
bin/magento deploy:mode:set developer

# Advantages:
# - Automatic static content deployment
# - Detailed error messages
# - No need to clear cache as often
```

### Production Mode

```bash
# Set to production mode
bin/magento deploy:mode:set production

# Requirements:
# - All caches should be enabled
# - Static content must be deployed
# - Code must be compiled
```

## 🔐 Permissions

The module requires standard Magento permissions:

```bash
# File permissions
find app/code/Dudenkoff/CacheLearn/ -type f -exec chmod 644 {} \;
find app/code/Dudenkoff/CacheLearn/ -type d -exec chmod 755 {} \;

# Cache directory (must be writable)
chmod -R 777 var/cache/
chmod -R 777 var/page_cache/

# Or more secure:
chown -R www-data:www-data var/cache/ var/page_cache/
```

## 📊 Verify Cache Backend

### Check Current Backend

```bash
# View cache configuration
cat app/etc/env.php | grep -A 20 "'cache'"
```

### Redis Backend (Recommended for Production)

```php
'cache' => [
    'frontend' => [
        'default' => [
            'backend' => 'Cm_Cache_Backend_Redis',
            'backend_options' => [
                'server' => '127.0.0.1',
                'port' => '6379'
            ]
        ]
    ]
]
```

### File Backend (Default)

```php
'cache' => [
    'frontend' => [
        'default' => [
            'backend' => 'Cm_Cache_Backend_File'
        ]
    ]
]
```

## 🚨 Known Issues

### None currently!

This module:
- ✅ Doesn't modify database
- ✅ Doesn't modify core files
- ✅ Uses standard Magento APIs
- ✅ Follows best practices
- ✅ Is safe for development environments

## 📞 Support

If you encounter issues:

1. **Check logs:** `var/log/system.log` and `var/log/exception.log`
2. **Review documentation:** All MD files in module directory
3. **Verify installation:** Follow checklist above
4. **Test in isolation:** Disable other modules temporarily

## ⚠️ Important Notes

- This is an **educational module** for learning purposes
- Safe to install on **development** environments
- For **production**, test thoroughly first
- Can be safely **uninstalled** without data loss

## 🎉 Success!

If you can see the demo page at `/cachelearn`, you're all set!

**Next:** Read [QUICK_START.md](QUICK_START.md) to learn caching in 5 minutes!

---

**Installation Time:** ~5 minutes  
**Difficulty:** Easy  
**Requirements:** Magento 2.4.x


