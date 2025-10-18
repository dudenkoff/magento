# Magento 2 Dependency Injection Cheatsheet

## Quick Reference

### 1. Basic Constructor Injection

```php
class MyService
{
    private $logger;
    
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
```

### 2. Interface Preference (di.xml)

```xml
<!-- Map interface to implementation -->
<preference for="Vendor\Module\Api\ServiceInterface" 
            type="Vendor\Module\Model\Service" />
```

### 3. Constructor Arguments (di.xml)

```xml
<type name="Vendor\Module\Model\Service">
    <arguments>
        <!-- String -->
        <argument name="apiKey" xsi:type="string">abc123</argument>
        
        <!-- Number -->
        <argument name="timeout" xsi:type="number">30</argument>
        
        <!-- Boolean -->
        <argument name="enabled" xsi:type="boolean">true</argument>
        
        <!-- Array -->
        <argument name="options" xsi:type="array">
            <item name="retry" xsi:type="number">3</item>
            <item name="cache" xsi:type="boolean">true</item>
        </argument>
        
        <!-- Object -->
        <argument name="logger" xsi:type="object">Psr\Log\LoggerInterface</argument>
        
        <!-- Null -->
        <argument name="optional" xsi:type="null" />
    </arguments>
</type>
```

### 4. Virtual Type (di.xml)

```xml
<!-- Create configured instance without PHP file -->
<virtualType name="SpecialLogger" type="Monolog\Logger">
    <arguments>
        <argument name="name" xsi:type="string">special</argument>
    </arguments>
</virtualType>

<!-- Use it -->
<type name="Vendor\Module\Model\Service">
    <arguments>
        <argument name="logger" xsi:type="object">SpecialLogger</argument>
    </arguments>
</type>
```

### 5. Plugin - Before

```php
class MyPlugin
{
    /**
     * Runs BEFORE original method
     * Can modify arguments
     * Return array of new arguments or null
     */
    public function beforeMethodName($subject, $arg1, $arg2)
    {
        // Modify arguments
        $arg1 = strtoupper($arg1);
        
        return [$arg1, $arg2]; // or null
    }
}
```

```xml
<type name="Vendor\Module\Model\Target">
    <plugin name="my_plugin" 
            type="Vendor\Module\Plugin\MyPlugin" 
            sortOrder="10" />
</type>
```

### 6. Plugin - After

```php
class MyPlugin
{
    /**
     * Runs AFTER original method
     * Can modify result
     * Return modified result
     */
    public function afterMethodName($subject, $result, $arg1, $arg2)
    {
        // Modify result
        $result['custom'] = 'value';
        
        return $result;
    }
}
```

### 7. Plugin - Around

```php
class MyPlugin
{
    /**
     * WRAPS original method
     * Full control
     * MUST call $proceed
     * Use sparingly!
     */
    public function aroundMethodName($subject, callable $proceed, $arg1, $arg2)
    {
        // Before
        $startTime = microtime(true);
        
        // Call original
        $result = $proceed($arg1, $arg2);
        
        // After
        $duration = microtime(true) - $startTime;
        
        return $result;
    }
}
```

### 8. Factory Pattern

```php
// Inject Factory
public function __construct(
    \Vendor\Module\Model\MessageFactory $messageFactory
) {
    $this->messageFactory = $messageFactory;
}

// Create instances
$message = $this->messageFactory->create();

// With data
$message = $this->messageFactory->create([
    'data' => ['text' => 'Hello', 'author' => 'Admin']
]);
```

### 9. Proxy Pattern (di.xml)

```xml
<!-- Delay instantiation until actually used -->
<type name="Vendor\Module\Service\MyService">
    <arguments>
        <argument name="heavyObject" xsi:type="object">
            Vendor\Module\Model\HeavyObject\Proxy
        </argument>
    </arguments>
</type>
```

### 10. Shared vs Non-Shared (di.xml)

```xml
<!-- Singleton (default) -->
<type name="Vendor\Module\Model\Config" shared="true" />

<!-- New instance each time -->
<type name="Vendor\Module\Model\ShoppingCart" shared="false" />
```

## Common Patterns

### Pattern: Multiple Implementations in Array

```php
public function __construct(array $processors = [])
{
    $this->processors = $processors;
}
```

```xml
<type name="Vendor\Module\Service\ProcessorChain">
    <arguments>
        <argument name="processors" xsi:type="array">
            <item name="step1" xsi:type="object">Processor1</item>
            <item name="step2" xsi:type="object">Processor2</item>
            <item name="step3" xsi:type="object">Processor3</item>
        </argument>
    </arguments>
</type>
```

### Pattern: Optional Dependency

```php
public function __construct(
    LoggerInterface $logger,
    ?CacheInterface $cache = null
) {
    $this->logger = $logger;
    $this->cache = $cache;
}
```

### Pattern: Override Magento Core

```xml
<!-- Override product repository -->
<preference for="Magento\Catalog\Api\ProductRepositoryInterface" 
            type="Vendor\Module\Model\CustomProductRepository" />
```

### Pattern: Different Config Per Area

```
etc/di.xml              → Global (all areas)
etc/frontend/di.xml     → Frontend only
etc/adminhtml/di.xml    → Admin only
etc/webapi_rest/di.xml  → REST API only
etc/webapi_soap/di.xml  → SOAP API only
```

## Command Line

```bash
# Enable module
bin/magento module:enable Vendor_Module

# Setup upgrade
bin/magento setup:upgrade

# Compile DI (after di.xml changes)
bin/magento setup:di:compile

# Clear cache
bin/magento cache:flush

# Clean generated files (development)
rm -rf generated/code/* generated/metadata/*

# Regenerate
bin/magento setup:di:compile
```

## File Structure

```
Vendor/Module/
├── etc/
│   ├── module.xml              # Module declaration
│   ├── di.xml                  # DI configuration
│   ├── frontend/di.xml         # Frontend DI
│   └── adminhtml/di.xml        # Admin DI
├── Api/
│   └── ServiceInterface.php    # Interface
├── Model/
│   └── Service.php             # Implementation
├── Plugin/
│   └── MyPlugin.php            # Interceptor
└── registration.php            # Module registration
```

## Rules

### ✅ DO:
- Inject dependencies in constructor
- Type-hint interfaces
- Use factories for multiple instances
- Use plugins to modify behavior
- Configure via di.xml

### ❌ DON'T:
- Call ObjectManager::getInstance()
- Use `new` for dependencies
- Put business logic in constructor
- Overuse `around` plugins
- Create circular dependencies

## Debugging

### Check if module enabled:
```bash
bin/magento module:status
```

### Check DI configuration:
```bash
# Generated interceptors
ls generated/code/Vendor/Module/Model/Service/

# Check for Proxy
ls generated/code/Vendor/Module/Model/Service/Proxy.php
```

### Verify plugin:
```bash
grep -r "YourPlugin" var/cache/
```

### Clear everything:
```bash
rm -rf var/cache/* var/page_cache/* var/view_preprocessed/* \
       pub/static/* generated/*
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento cache:flush
```

## Testing DI

### Test Preference:

1. Create interface
2. Create implementation
3. Add preference in di.xml
4. Inject interface
5. Run: `bin/magento setup:di:compile`
6. Test: Instance should be your implementation

### Test Plugin:

1. Create plugin class
2. Add plugin in di.xml
3. Run: `bin/magento cache:flush`
4. Execute target method
5. Verify: Plugin methods should execute

### Test Virtual Type:

1. Create virtual type in di.xml
2. Use it in another class
3. Run: `bin/magento setup:di:compile`
4. Instantiate class
5. Verify: Configuration applied

## Common Errors

### "Class does not exist"
```bash
bin/magento setup:di:compile
composer dump-autoload
```

### "Plugin not working"
```bash
bin/magento cache:flush
# Check disabled="false" in di.xml
# Check method is public
```

### "Changes not reflecting"
```bash
bin/magento setup:di:compile
bin/magento cache:flush
# Or in development:
rm -rf var/cache/* generated/*
```

### "Circular dependency"
- Use Proxy to break cycle
- Restructure dependencies
- Use event/observer pattern instead

## Performance Tips

1. **Use Proxies** for heavy objects
2. **Avoid around plugins** (use before/after)
3. **Set shared="false"** only when needed
4. **Use virtual types** instead of new classes
5. **Cache di:compile** output in production

## Quick Examples

### Add logging to ANY method:

```php
class LoggerPlugin
{
    private $logger;
    
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    
    public function beforeSave($subject, $entity)
    {
        $this->logger->info('Saving: ' . get_class($entity));
        return [$entity];
    }
}
```

```xml
<type name="Magento\Framework\Model\ResourceModel\Db\AbstractDb">
    <plugin name="db_logger" type="Vendor\Module\Plugin\LoggerPlugin" />
</type>
```

### Switch service based on config:

```php
// Interface
interface PaymentInterface { }

// Implementations
class StripePayment implements PaymentInterface { }
class PaypalPayment implements PaymentInterface { }
```

```xml
<!-- Use Stripe by default -->
<preference for="PaymentInterface" type="StripePayment" />

<!-- Or use PayPal -->
<preference for="PaymentInterface" type="PaypalPayment" />
```

### Add custom data to product:

```php
class ProductPlugin
{
    public function afterGet($subject, $result)
    {
        $result->setCustomAttribute('is_featured', true);
        return $result;
    }
}
```

```xml
<type name="Magento\Catalog\Model\ProductRepository">
    <plugin name="add_featured" type="Vendor\Module\Plugin\ProductPlugin" />
</type>
```

---

**Remember**: Magento's DI system is powerful. Learn it well and your code will be flexible, testable, and maintainable!

