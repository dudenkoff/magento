# Magento 2 Dependency Injection Learning Module

## Overview

This module is designed to help you understand how Dependency Injection (DI) works in Magento 2 through practical, hands-on examples with detailed explanations.

## What is Dependency Injection?

**Dependency Injection** is a design pattern where objects receive their dependencies from external sources rather than creating them internally. Instead of a class creating its own dependencies, they are "injected" into the class through its constructor.

### Benefits:
- **Loose Coupling**: Classes depend on interfaces, not concrete implementations
- **Testability**: Easy to swap dependencies with mocks for testing
- **Flexibility**: Change implementations without modifying dependent code
- **Maintainability**: Clear declaration of dependencies in constructor

## Quick Start

### 1. Enable the Module

```bash
# Go to Magento root directory
cd /home/dudenkoff/Projects/magento

# Enable the module
bin/magento module:enable Dudenkoff_DILearn

# Run setup upgrade
bin/magento setup:upgrade

# Compile DI
bin/magento setup:di:compile

# Clear cache
bin/magento cache:flush
```

### 2. Run the Demo Command

```bash
bin/magento dudenkoff:di:demo YourName
```

This command demonstrates all DI concepts in action!

## Module Structure

```
app/code/Dudenkoff/DILearn/
├── Api/
│   └── LoggerInterface.php          # Interface definition
├── Model/
│   ├── BasicLogger.php              # Interface implementation
│   ├── AdvancedLogger.php           # Alternative implementation
│   ├── Counter.php                  # Non-shared instance example
│   ├── Message.php                  # Factory pattern example
│   └── HeavyProcessor.php           # Proxy pattern example
├── Service/
│   ├── GreetingService.php          # Main service with DI
│   ├── NotificationService.php      # Virtual type example
│   └── HeavyService.php             # Proxy usage example
├── Plugin/
│   └── GreetingLoggerPlugin.php     # Plugin/Interceptor example
├── Console/
│   └── Command/
│       └── DemoCommand.php          # CLI command to test everything
├── etc/
│   ├── module.xml                   # Module declaration
│   └── di.xml                       # DI CONFIGURATION - THE KEY FILE!
├── registration.php                 # Module registration
├── README.md                        # This file
└── DI_CONCEPTS.md                   # Detailed concept explanations
```

## Key Concepts Demonstrated

### 1. Constructor Injection
**File**: `Service/GreetingService.php`

Dependencies are declared in the constructor. Magento's Object Manager automatically injects them.

```php
public function __construct(
    LoggerInterface $logger,
    MessageFactory $messageFactory
) {
    $this->logger = $logger;
    $this->messageFactory = $messageFactory;
}
```

### 2. Interface Preferences
**File**: `etc/di.xml` (lines 17-19)

Map interfaces to concrete implementations:

```xml
<preference for="Dudenkoff\DILearn\Api\LoggerInterface" 
            type="Dudenkoff\DILearn\Model\BasicLogger" />
```

### 3. Constructor Arguments
**File**: `etc/di.xml` (lines 30-51)

Inject values, arrays, and objects via XML:

```xml
<type name="Dudenkoff\DILearn\Service\GreetingService">
    <arguments>
        <argument name="defaultGreeting" xsi:type="string">Hello from DI.xml!</argument>
    </arguments>
</type>
```

### 4. Virtual Types
**File**: `etc/di.xml` (lines 58-67)

Create configured instances without creating PHP files:

```xml
<virtualType name="Dudenkoff\DILearn\Model\SpecialLogger" 
             type="Dudenkoff\DILearn\Model\BasicLogger">
    <arguments>
        <argument name="prefix" xsi:type="string">[SPECIAL]</argument>
    </arguments>
</virtualType>
```

### 5. Plugins (Interceptors)
**File**: `Plugin/GreetingLoggerPlugin.php`

Modify method behavior without changing the class:

```xml
<type name="Dudenkoff\DILearn\Service\GreetingService">
    <plugin name="greeting_logger_plugin" 
            type="Dudenkoff\DILearn\Plugin\GreetingLoggerPlugin" />
</type>
```

**Three types**: `before`, `after`, `around`

### 6. Factory Pattern
**File**: `Model/Message.php` & `Service/GreetingService.php`

Create new instances dynamically:

```php
// Inject the factory
public function __construct(MessageFactory $messageFactory)

// Create new instances
$message = $this->messageFactory->create(['data' => [...]]);
```

### 7. Proxy Pattern
**File**: `etc/di.xml` (lines 91-97)

Delay instantiation until actually needed:

```xml
<type name="Dudenkoff\DILearn\Service\HeavyService">
    <arguments>
        <argument name="processor" xsi:type="object">
            Dudenkoff\DILearn\Model\HeavyProcessor\Proxy
        </argument>
    </arguments>
</type>
```

### 8. Shared vs Non-Shared
**File**: `etc/di.xml` (lines 83-86)

Control instance lifecycle:

```xml
<type name="Dudenkoff\DILearn\Model\Counter" shared="false">
    <!-- New instance each time -->
</type>
```

## Common DI Patterns in Magento 2

### ✅ DO:
- Type-hint interfaces in constructors
- Declare all dependencies in constructor
- Use factories for creating new instances
- Use plugins to modify behavior
- Configure via `di.xml`

### ❌ DON'T:
- Call `ObjectManager::getInstance()` directly (except in factories/CLI)
- Create dependencies using `new` keyword
- Put business logic in constructors
- Use `around` plugins unless absolutely necessary
- Modify constructor signatures of Magento core classes

## Files to Study

Study these files in order:

1. **etc/di.xml** - See all DI configurations
2. **Api/LoggerInterface.php** - Understand interfaces
3. **Model/BasicLogger.php** - See implementation
4. **Service/GreetingService.php** - See DI in action
5. **Plugin/GreetingLoggerPlugin.php** - Learn plugins
6. **Console/Command/DemoCommand.php** - See it all together
7. **DI_CONCEPTS.md** - Deep dive into concepts

## Testing the Module

### Run the Demo Command

```bash
bin/magento dudenkoff:di:demo John
```

### Experiment: Switch Logger Implementation

1. Edit `etc/di.xml`
2. Comment out line 18-19 (BasicLogger preference)
3. Uncomment lines 26-27 (AdvancedLogger preference)
4. Run: `bin/magento setup:di:compile`
5. Run: `bin/magento dudenkoff:di:demo John`
6. See the different output!

### Experiment: Modify Configuration

1. Edit `etc/di.xml`, line 36
2. Change the greeting text
3. Run: `bin/magento cache:flush`
4. Run: `bin/magento dudenkoff:di:demo John`
5. See your custom greeting!

### Experiment: Disable Plugin

1. Edit `etc/di.xml`, line 78
2. Change `disabled="false"` to `disabled="true"`
3. Run: `bin/magento cache:flush`
4. Run: `bin/magento dudenkoff:di:demo John`
5. Notice: No more "[Via Plugin]" suffix!

## Real-World Applications

### Example 1: Swapping Payment Processors

```php
// Interface
interface PaymentProcessorInterface {
    public function charge($amount);
}

// Stripe implementation
class StripeProcessor implements PaymentProcessorInterface { ... }

// PayPal implementation
class PaypalProcessor implements PaymentProcessorInterface { ... }

// In di.xml, switch between them:
<preference for="PaymentProcessorInterface" type="StripeProcessor" />
```

### Example 2: Adding Custom Logging

```php
// Use plugin to log all payment transactions
class PaymentLoggerPlugin {
    public function afterCharge(PaymentProcessorInterface $subject, $result) {
        $this->logger->log('Payment processed: ' . $result);
        return $result;
    }
}
```

### Example 3: Product Repository with Caching

```php
// Use virtual type for cached version
<virtualType name="CachedProductRepository" type="ProductRepository">
    <arguments>
        <argument name="cache" xsi:type="object">CacheInterface</argument>
    </arguments>
</virtualType>
```

## Troubleshooting

### Module not showing up?

```bash
bin/magento module:status
bin/magento module:enable Dudenkoff_DILearn
bin/magento setup:upgrade
```

### Command not found?

```bash
bin/magento setup:di:compile
bin/magento cache:flush
bin/magento list | grep dudenkoff
```

### Changes not reflecting?

```bash
# Always run these after di.xml changes
bin/magento cache:flush
bin/magento setup:di:compile

# In development mode:
rm -rf var/cache/* var/page_cache/* generated/*
bin/magento setup:di:compile
```

### Class not found errors?

```bash
# Regenerate autoloader
composer dump-autoload
bin/magento setup:di:compile
```

## Further Reading

- [Magento DevDocs: Dependency Injection](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/depend-inj.html)
- [Magento DevDocs: Object Manager](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/object-manager.html)
- [Magento DevDocs: Plugins](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/plugins.html)
- See `DI_CONCEPTS.md` for detailed explanations

## Author

Created for learning purposes to understand Magento 2's powerful Dependency Injection system.

## License

Educational use only.

