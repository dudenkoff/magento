# Xdebug Setup Guide

## ✅ Status: Xdebug is Installed and Configured!

Xdebug v3.4.7 is now installed in your Docker container.

## 📋 Current Configuration

**From `docker/php/xdebug.ini`:**
```ini
xdebug.mode=develop,debug,coverage
xdebug.start_with_request=yes
xdebug.discover_client_host=true
xdebug.client_host=172.18.0.1
xdebug.client_port=9003
xdebug.idekey=PHPSTORM
xdebug.log=/var/www/html/var/log/xdebug.log
xdebug.log_level=7
```

## 🔧 Configure Your IDE (Cursor/VS Code)

### 1. Install PHP Debug Extension
- Search for "PHP Debug" by Felix Becker
- Install it in Cursor/VS Code

### 2. Configure launch.json

Create `.vscode/launch.json`:

```json
{
    "version": "0.2.0",
    "configurations": [
        {
            "name": "Listen for Xdebug",
            "type": "php",
            "request": "launch",
            "port": 9003,
            "pathMappings": {
                "/var/www/html": "${workspaceFolder}"
            },
            "log": true,
            "xdebugSettings": {
                "max_data": 65535,
                "show_hidden": 1,
                "max_children": 100,
                "max_depth": 5
            }
        }
    ]
}
```

### 3. Start Debugging

1. Set a breakpoint in your code
2. Click "Run and Debug" in sidebar (or press F5)
3. Select "Listen for Xdebug"
4. Run your command or access via browser

## 🧪 Test Xdebug

### Test 1: Verify Xdebug is Loaded

```bash
docker-compose exec magento php -v
```

**Expected output:**
```
PHP 8.2.29 (cli) (built: Oct 21 2025 01:35:11) (NTS)
    with Xdebug v3.4.7, Copyright (c) 2002-2025, by Derick Rethans
```

### Test 2: Check Xdebug Configuration

```bash
docker-compose exec magento php -i | grep xdebug.mode
```

**Expected:**
```
xdebug.mode => develop,debug,coverage
```

### Test 3: Check Xdebug Log

```bash
tail -f var/log/xdebug.log
```

## 🎯 Debugging Your Indexer

### Example: Debug Model Save

1. Open `Plugin/ReindexAfterSavePlugin.php`
2. Set breakpoint on line 70 (`$this->productStatsProcessor->reindexRow($entityId);`)
3. Start debugger in IDE (F5)
4. Run command:

```bash
docker-compose exec magento bin/magento dudenkoff:indexer:test-model-save -p 1 --views=10
```

5. Debugger should pause at your breakpoint!

### Example: Debug Indexer

1. Open `Model/Indexer/ProductStats.php`
2. Set breakpoint in `executeFull()` method
3. Start debugger
4. Run:

```bash
docker-compose exec magento bin/magento indexer:reindex dudenkoff_product_stats
```

## 🐛 Troubleshooting

### Issue: Debugger Not Connecting

**Check:**
1. Port 9003 is not blocked by firewall
2. IDE is listening on port 9003
3. Path mappings are correct

**Fix client_host if needed:**

```bash
# Find Docker gateway IP
docker network inspect magento_magento_network | grep Gateway

# Update docker/php/xdebug.ini with correct IP
# Then rebuild:
docker-compose down
docker-compose up -d --build
```

### Issue: No Breakpoints Hit

**Check:**
1. IDE is in debug mode (listening)
2. Path mappings: `/var/www/html` → `${workspaceFolder}`
3. Breakpoint is on executable line (not comment/blank)

### Issue: Xdebug Log Shows Errors

```bash
# Check log
tail -20 var/log/xdebug.log

# Common issues:
# - Connection refused: IDE not listening
# - Timeout: Wrong client_host IP
# - Path not found: Wrong path mappings
```

## 🎓 Xdebug CLI Debugging

For CLI commands, Xdebug starts automatically with `start_with_request=yes`.

**Just:**
1. Start IDE debugger (F5)
2. Run your CLI command
3. Debugger will connect automatically!

## ✅ Verification Checklist

- [x] Xdebug installed (v3.4.7)
- [x] Mode set to: develop,debug,coverage
- [x] Client port: 9003
- [x] Start with request: yes
- [x] Log file: var/log/xdebug.log
- [ ] IDE configured with path mappings
- [ ] Firewall allows port 9003
- [ ] IDE listening for connections

## 🚀 You're Ready!

Xdebug is fully configured and ready to use. Happy debugging! 🐛

