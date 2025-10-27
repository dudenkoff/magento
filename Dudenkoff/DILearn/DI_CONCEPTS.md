# Dependency Injection Deep Dive

## Table of Contents

1. [What is Dependency Injection?](#what-is-dependency-injection)
2. [Why Use DI?](#why-use-di)
3. [Magento 2 DI Components](#magento-2-di-components)
4. [Core Concepts Explained](#core-concepts-explained)
5. [Advanced Patterns](#advanced-patterns)
6. [Best Practices](#best-practices)
7. [Common Mistakes](#common-mistakes)

---

## What is Dependency Injection?

### Traditional Approach (Bad)

```php
class OrderProcessor
{
    private $logger;
    
    public function __construct()
    {
        // Creating dependency inside the class - TIGHT COUPLING
        $this->logger = new FileLogger('/var/log/orders.log');
    }
    
    public function process($order)
    {
        $this->logger->log('Processing order: ' . $order->getId());
        // ... process order
    }
}
```

**Problems:**
- ❌ Hard to test (can't mock FileLogger)
- ❌ Hard to change (what if we want DatabaseLogger?)
- ❌ Violates Single Responsibility Principle
- ❌ Creates hidden dependencies

### Dependency Injection Approach (Good)

```php
class OrderProcessor
{
    private $logger;
    
    // Dependencies are INJECTED through constructor
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    
    public function process($order)
    {
        $this->logger->log('Processing order: ' . $order->getId());
        // ... process order
    }
}
```

**Benefits:**
- ✅ Easy to test (inject mock logger)
- ✅ Easy to change (switch logger implementation in di.xml)
- ✅ Dependencies are explicit and visible
- ✅ Follows SOLID principles

---

## Why Use DI?

### 1. Loose Coupling

Code depends on **abstractions** (interfaces), not **concrete implementations**.

```php
// Depends on interface, not implementation
public function __construct(LoggerInterface $logger) { }

// di.xml controls which implementation is used
<preference for="LoggerInterface" type="FileLogger" />
```

Change implementation without touching code:

```xml
<!-- Switch to different logger -->
<preference for="LoggerInterface" type="DatabaseLogger" />
```

### 2. Testability

Easy to inject mock objects for unit testing:

```php
// In your test
$mockLogger = $this->createMock(LoggerInterface::class);
$orderProcessor = new OrderProcessor($mockLogger);
$mockLogger->expects($this->once())->method('log');
```

### 3. Maintainability

All dependencies are clearly declared in constructor:

```php
public function __construct(
    LoggerInterface $logger,
    EmailSender $emailSender,
    OrderRepository $orderRepository,
    ConfigProvider $config
) {
    // Clear list of what this class needs
}
```

### 4. Flexibility

Configuration is separated from code:

```xml
<!-- Change behavior without code changes -->
<type name="OrderProcessor">
    <arguments>
        <argument name="maxRetries" xsi:type="number">5</argument>
    </arguments>
</type>
```

---

## Magento 2 DI Components

### 1. Object Manager

The **Object Manager** is Magento's DI container. It:
- Creates objects
- Injects dependencies
- Manages object lifecycle
- Resolves configurations from di.xml

**Important**: Don't use ObjectManager directly in your code!

```php
// ❌ NEVER DO THIS (except in factories/CLI)
$logger = \Magento\Framework\App\ObjectManager::getInstance()
    ->get(LoggerInterface::class);

// ✅ DO THIS INSTEAD
public function __construct(LoggerInterface $logger) {
    $this->logger = $logger;
}
```

### 2. di.xml Configuration

The `di.xml` file is the **heart** of Magento's DI system.

**Scope Levels** (in order of priority):

1. `app/code/Vendor/Module/etc/di.xml` - Global
2. `app/code/Vendor/Module/etc/frontend/di.xml` - Frontend only
3. `app/code/Vendor/Module/etc/adminhtml/di.xml` - Admin only
4. `app/code/Vendor/Module/etc/webapi_rest/di.xml` - REST API only
5. `app/code/Vendor/Module/etc/webapi_soap/di.xml` - SOAP API only

### 3. Generated Code

Magento auto-generates classes in `generated/code/`:

- **Factories**: `*Factory` classes
- **Proxies**: `*\Proxy` classes
- **Interceptors**: Plugin implementations

```bash
# Generate DI code
bin/magento setup:di:compile
```

---

## Core Concepts Explained

### 1. Preference (Interface Mapping)

**What**: Maps an interface (or class) to its implementation.

**When to use**: 
- Implementing an interface
- Overriding a core Magento class
- Switching implementations

**Example**:

```xml
<preference for="Magento\Catalog\Api\ProductRepositoryInterface" 
            type="Magento\Catalog\Model\ProductRepository" />
```

**Practical Example**:

```php
// Your custom implementation
namespace Vendor\Module\Model;

class CustomProductRepository implements ProductRepositoryInterface
{
    // Your custom logic
}
```

```xml
<!-- Override Magento's product repository -->
<preference for="Magento\Catalog\Api\ProductRepositoryInterface" 
            type="Vendor\Module\Model\CustomProductRepository" />
```

### 2. Type Configuration (Constructor Arguments)

**What**: Configure constructor parameters for a class.

**Argument Types**:
- `string` - String value
- `number` - Integer or float
- `boolean` - True/false
- `array` - Array of values
- `object` - Another class instance
- `null` - Null value
- `const` - Class constant

**Example**:

```xml
<type name="Vendor\Module\Service\EmailService">
    <arguments>
        <!-- String -->
        <argument name="senderEmail" xsi:type="string">noreply@example.com</argument>
        
        <!-- Number -->
        <argument name="maxRetries" xsi:type="number">3</argument>
        
        <!-- Boolean -->
        <argument name="isEnabled" xsi:type="boolean">true</argument>
        
        <!-- Array -->
        <argument name="allowedDomains" xsi:type="array">
            <item name="gmail" xsi:type="string">gmail.com</item>
            <item name="yahoo" xsi:type="string">yahoo.com</item>
        </argument>
        
        <!-- Object (another class) -->
        <argument name="logger" xsi:type="object">Psr\Log\LoggerInterface</argument>
        
        <!-- Constant -->
        <argument name="mode" xsi:type="const">Vendor\Module\Model\Config::MODE_ASYNC</argument>
    </arguments>
</type>
```

### 3. Virtual Types

**What**: Creates a "virtual" class with custom configuration without creating a PHP file.

**When to use**:
- Need multiple differently-configured instances of the same class
- Want to avoid creating unnecessary PHP files
- Creating specialized versions of existing classes

**Example**:

```xml
<!-- Create a virtual type for admin notifications -->
<virtualType name="AdminNotificationLogger" type="Magento\Framework\Logger\Monolog">
    <arguments>
        <argument name="name" xsi:type="string">admin_notifications</argument>
        <argument name="handlers" xsi:type="array">
            <item name="system" xsi:type="object">AdminNotificationHandler</item>
        </argument>
    </arguments>
</virtualType>

<!-- Use it in another class -->
<type name="Vendor\Module\Service\AdminService">
    <arguments>
        <argument name="logger" xsi:type="object">AdminNotificationLogger</argument>
    </arguments>
</type>
```

**Real-World Example**: Different loggers for different purposes

```xml
<!-- Error logger -->
<virtualType name="ErrorLogger" type="Monolog\Logger">
    <arguments>
        <argument name="name" xsi:type="string">error</argument>
    </arguments>
</virtualType>

<!-- Debug logger -->
<virtualType name="DebugLogger" type="Monolog\Logger">
    <arguments>
        <argument name="name" xsi:type="string">debug</argument>
    </arguments>
</virtualType>
```

### 4. Plugins (Interceptors)

**What**: Modify the behavior of any public method without changing the class.

**Three Types**:

#### Before Plugin

Runs **before** the original method. Can modify arguments.

```php
public function beforeSave(ProductRepository $subject, ProductInterface $product)
{
    // Modify the product before saving
    $product->setSku(strtoupper($product->getSku()));
    
    // Return array of modified arguments
    return [$product];
    
    // Or return null to keep original arguments
    // return null;
}
```

#### After Plugin

Runs **after** the original method. Can modify the result.

```php
public function afterGet(ProductRepository $subject, ProductInterface $result, $sku)
{
    // Modify the result
    $result->setCustomAttribute('loaded_from_plugin', true);
    
    return $result;
}
```

#### Around Plugin

**Wraps** the original method. Full control.

⚠️ **WARNING**: Use sparingly! Performance impact and conflict risk.

```php
public function aroundSave(
    ProductRepository $subject,
    callable $proceed,
    ProductInterface $product
) {
    // Before original method
    $startTime = microtime(true);
    
    // Call original method
    $result = $proceed($product);
    
    // After original method
    $duration = microtime(true) - $startTime;
    $this->logger->info("Save took {$duration} seconds");
    
    return $result;
}
```

**Configuration**:

```xml
<type name="Magento\Catalog\Model\ProductRepository">
    <plugin name="my_product_plugin" 
            type="Vendor\Module\Plugin\ProductRepositoryPlugin" 
            sortOrder="10" 
            disabled="false" />
</type>
```

**Plugin Priority**:
- `sortOrder` determines execution order
- Lower numbers run first
- Negative numbers allowed

### 5. Factories

**What**: Auto-generated classes that create new instances.

**When to use**:
- Creating multiple instances of a class
- Objects that represent data (models, data objects)
- When you need `new` keyword functionality

**How it works**:

```php
// Inject the Factory (Magento auto-generates it)
public function __construct(
    \Vendor\Module\Model\MessageFactory $messageFactory
) {
    $this->messageFactory = $messageFactory;
}

// Create new instances
public function createMessage($text)
{
    $message = $this->messageFactory->create();
    $message->setText($text);
    return $message;
    
    // Or pass data in constructor
    $message = $this->messageFactory->create([
        'data' => ['text' => $text, 'author' => 'Admin']
    ]);
}
```

**Factory vs Direct Injection**:

```php
// ❌ Don't inject the model directly if you need multiple instances
public function __construct(Message $message) // Only get one instance

// ✅ Inject the factory instead
public function __construct(MessageFactory $messageFactory) // Can create many
```

### 6. Proxies

**What**: Lazy loading wrapper that delays object creation until actually needed.

**When to use**:
- Heavy objects (database connections, API clients)
- Objects that might not be used
- Breaking circular dependencies
- Improving performance

**How it works**:

```php
// Heavy object
class ApiClient
{
    public function __construct()
    {
        // Expensive: connects to API, loads config, etc.
    }
}

// Without proxy: ApiClient created when Service is created
class Service
{
    public function __construct(ApiClient $client) { }
}

// With proxy: ApiClient created only when method is called
<type name="Service">
    <arguments>
        <argument name="client" xsi:type="object">ApiClient\Proxy</argument>
    </arguments>
</type>
```

**Example**:

```xml
<type name="Vendor\Module\Service\PaymentService">
    <arguments>
        <!-- Proxy delays instantiation -->
        <argument name="stripeClient" xsi:type="object">
            Vendor\Module\Client\StripeClient\Proxy
        </argument>
    </arguments>
</type>
```

### 7. Shared vs Non-Shared

**What**: Controls whether same instance is reused (singleton) or new instance created each time.

**Default**: `shared="true"` (singleton)

**Shared (default)**:

```xml
<type name="Vendor\Module\Model\Config" shared="true">
    <!-- Same instance everywhere (singleton) -->
</type>
```

**Non-Shared**:

```xml
<type name="Vendor\Module\Model\Cart" shared="false">
    <!-- New instance each time -->
</type>
```

**When to use non-shared**:
- Objects that hold state per request
- Objects that should be independent
- Testing scenarios

---

## Advanced Patterns

### 1. Constructor Promotion (PHP 8+)

```php
public function __construct(
    private LoggerInterface $logger,
    private ProductRepository $productRepository
) {
    // Properties auto-created and assigned!
    // No need for: $this->logger = $logger;
}
```

### 2. Optional Dependencies

```php
public function __construct(
    LoggerInterface $logger,
    ?CacheInterface $cache = null  // Optional
) {
    $this->logger = $logger;
    $this->cache = $cache;
}

public function getData()
{
    if ($this->cache) {
        // Use cache if available
    }
}
```

### 3. Array of Implementations

```php
public function __construct(
    array $processors = []  // Array of processor instances
) {
    $this->processors = $processors;
}
```

```xml
<type name="Vendor\Module\Service\ProcessorChain">
    <arguments>
        <argument name="processors" xsi:type="array">
            <item name="validator" xsi:type="object">ValidatorProcessor</item>
            <item name="transformer" xsi:type="object">TransformerProcessor</item>
            <item name="saver" xsi:type="object">SaverProcessor</item>
        </argument>
    </arguments>
</type>
```

### 4. Plugin Inheritance

Plugins apply to subclasses too!

```php
class BaseRepository { }
class ProductRepository extends BaseRepository { }

// Plugin on BaseRepository applies to ProductRepository too
<type name="BaseRepository">
    <plugin name="logger" type="LoggerPlugin" />
</type>
```

---

## Best Practices

### ✅ DO:

1. **Type-hint interfaces**
   ```php
   public function __construct(LoggerInterface $logger) // ✅
   public function __construct(FileLogger $logger)      // ❌
   ```

2. **Declare ALL dependencies in constructor**
   ```php
   public function __construct($dep1, $dep2, $dep3) // All here
   ```

3. **Use factories for creating multiple instances**
   ```php
   $message = $this->messageFactory->create();
   ```

4. **Use plugins to modify behavior**
   ```php
   // Don't override classes, use plugins
   ```

5. **Use di.xml for configuration**
   ```xml
   <argument name="enabled" xsi:type="boolean">true</argument>
   ```

### ❌ DON'T:

1. **Don't use ObjectManager directly**
   ```php
   ObjectManager::getInstance()->get() // ❌ NEVER!
   ```

2. **Don't use `new` keyword for dependencies**
   ```php
   $logger = new FileLogger(); // ❌
   ```

3. **Don't put logic in constructors**
   ```php
   public function __construct() {
       // No business logic here!
   }
   ```

4. **Don't use `around` plugins unless necessary**
   ```php
   // Prefer `before` or `after`
   ```

5. **Don't create circular dependencies**
   ```php
   // A depends on B, B depends on A - ❌
   ```

---

## Common Mistakes

### Mistake 1: Using ObjectManager

```php
// ❌ WRONG
$product = \Magento\Framework\App\ObjectManager::getInstance()
    ->create(ProductInterface::class);

// ✅ CORRECT
public function __construct(ProductFactory $productFactory) {
    $this->productFactory = $productFactory;
}
$product = $this->productFactory->create();
```

### Mistake 2: Creating Dependencies

```php
// ❌ WRONG
public function __construct() {
    $this->logger = new FileLogger();
}

// ✅ CORRECT
public function __construct(LoggerInterface $logger) {
    $this->logger = $logger;
}
```

### Mistake 3: Dependencies in Methods

```php
// ❌ WRONG
public function process(LoggerInterface $logger) {
    // Don't pass dependencies as method parameters
}

// ✅ CORRECT
private $logger;
public function __construct(LoggerInterface $logger) {
    $this->logger = $logger;
}
public function process() {
    $this->logger->log();
}
```

### Mistake 4: Wrong Preference Scope

```php
// ❌ WRONG - Affects all areas
<config>
    <preference for="Interface" type="FrontendImpl" />
</config>

// ✅ CORRECT - Only frontend
<config>
    <!-- In etc/frontend/di.xml -->
    <preference for="Interface" type="FrontendImpl" />
</config>
```

### Mistake 5: Modifying Core Classes

```php
// ❌ WRONG - Don't edit core files

// ✅ CORRECT - Use plugins or preferences
<type name="Magento\Catalog\Model\Product">
    <plugin name="my_plugin" type="Vendor\Module\Plugin\ProductPlugin" />
</type>
```

---

## Summary

Dependency Injection in Magento 2 is powerful when used correctly:

1. **Inject dependencies** through constructor
2. **Type-hint interfaces** for flexibility
3. **Configure via di.xml** not hardcode
4. **Use plugins** to modify behavior
5. **Use factories** for multiple instances
6. **Use proxies** for heavy objects
7. **Never use ObjectManager** directly

Master these concepts and you'll write better, more maintainable Magento 2 code!

