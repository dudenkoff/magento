# Magento 2 Event/Observer Cheatsheet

## Quick Reference

### Create an Observer

**1. Create Observer Class**

```php
namespace Vendor\Module\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

class MyObserver implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        // Get event data
        $data = $observer->getEvent()->getData('key');
        
        // Your logic here
    }
}
```

**2. Register in events.xml**

```xml
<!-- app/code/Vendor/Module/etc/events.xml -->
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:framework:Event/etc/events.xsd">
    <event name="event_name">
        <observer name="unique_observer_name" 
                  instance="Vendor\Module\Observer\MyObserver" />
    </event>
</config>
```

### Dispatch an Event

```php
namespace Vendor\Module\Model;

use Magento\Framework\Event\ManagerInterface;

class MyClass
{
    private $eventManager;
    
    public function __construct(ManagerInterface $eventManager)
    {
        $this->eventManager = $eventManager;
    }
    
    public function doSomething()
    {
        // Dispatch event
        $this->eventManager->dispatch('my_custom_event', [
            'product' => $product,
            'qty' => 5,
            'data' => ['key' => 'value']
        ]);
    }
}
```

## Event Configuration

### Global Events

```xml
<!-- etc/events.xml -->
<event name="event_name">
    <observer name="observer_name" instance="Vendor\Module\Observer\MyObserver" />
</event>
```

### Frontend Only

```xml
<!-- etc/frontend/events.xml -->
<event name="event_name">
    <observer name="observer_name" instance="Vendor\Module\Observer\FrontendObserver" />
</event>
```

### Admin Only

```xml
<!-- etc/adminhtml/events.xml -->
<event name="event_name">
    <observer name="observer_name" instance="Vendor\Module\Observer\AdminObserver" />
</event>
```

### Disabled Observer

```xml
<event name="event_name">
    <observer name="observer_name" 
              instance="Vendor\Module\Observer\MyObserver"
              disabled="true" />
</event>
```

### Shared Instance (rarely needed)

```xml
<event name="event_name">
    <observer name="observer_name" 
              instance="Vendor\Module\Observer\MyObserver"
              shared="true" />
</event>
```

## Getting Event Data

### Basic

```php
// Get all data
$allData = $observer->getEvent()->getData();

// Get specific key
$value = $observer->getEvent()->getData('key');

// Get with default
$value = $observer->getEvent()->getData('key', 'default');
```

### Named Parameters

```php
// If dispatched as: dispatch('event', ['product' => $product])
$product = $observer->getEvent()->getProduct();

// Magic getter: getData('product_id')
$productId = $observer->getEvent()->getProductId();
```

### Objects

```php
// Common patterns
$product = $observer->getEvent()->getData('product');
$order = $observer->getEvent()->getOrder();
$customer = $observer->getEvent()->getCustomer();
$request = $observer->getEvent()->getRequest();
```

## Common Core Events

### Customer Events

```php
customer_login                    // Customer logs in
customer_logout                   // Customer logs out  
customer_register_success         // Registration success
customer_save_before              // Before customer save
customer_save_after               // After customer save
customer_delete_before            // Before delete
customer_delete_after             // After delete
customer_address_save_after       // Address saved
```

### Product Events

```php
catalog_product_save_before       // Before product save
catalog_product_save_after        // After product save
catalog_product_delete_before     // Before delete
catalog_product_delete_after      // After delete
catalog_product_load_after        // After product loaded
catalog_product_import_finish_before // Before import finish
```

### Order Events

```php
sales_order_place_before          // Before order placed
sales_order_place_after           // After order placed
sales_order_save_before           // Before order save
sales_order_save_after            // After order save
sales_order_invoice_save_after    // Invoice saved
sales_order_shipment_save_after   // Shipment saved
```

### Cart Events

```php
checkout_cart_add_product_complete    // Product added to cart
checkout_cart_product_add_before      // Before adding product
checkout_cart_save_before             // Before cart save
checkout_cart_save_after              // After cart save
checkout_cart_update_items_before     // Before update items
checkout_cart_update_items_after      // After update items
```

### Category Events

```php
catalog_category_save_before      // Before category save
catalog_category_save_after       // After category save
catalog_category_delete_before    // Before delete
catalog_category_delete_after     // After delete
```

### Controller Events

```php
controller_action_predispatch                           // Before ANY controller
controller_action_postdispatch                          // After ANY controller
controller_action_predispatch_{route}                   // Before route
controller_action_postdispatch_{route}                  // After route
controller_action_predispatch_{route}_{controller}_{action} // Specific
controller_action_postdispatch_{route}_{controller}_{action} // Specific
```

### Admin Events

```php
backend_auth_user_login_success   // Admin login success
backend_auth_user_login_failed    // Admin login failed
admin_user_save_before            // Before admin user save
admin_user_save_after             // After admin user save
```

### Layout Events

```php
layout_generate_blocks_before     // Before blocks generated
layout_generate_blocks_after      // After blocks generated
layout_load_before                // Before layout load
core_layout_render_element        // Rendering element
```

## Event Naming Patterns

### Core Magento

```
{entity}_{action}_{when}

Examples:
- catalog_product_save_before
- sales_order_place_after
- customer_login
```

### Custom Events

```
{vendor}_{module}_{action}

Examples:
- acme_inventory_updated
- mycompany_shipment_dispatched
- vendor_module_custom_event
```

## Common Use Cases

### Log Activity

```php
public function execute(Observer $observer)
{
    $customer = $observer->getEvent()->getCustomer();
    
    $this->logger->info('Customer action', [
        'customer_id' => $customer->getId(),
        'email' => $customer->getEmail()
    ]);
}
```

### Modify Object Before Save

```php
public function execute(Observer $observer)
{
    $product = $observer->getEvent()->getProduct();
    
    // Modify product
    $product->setSku(strtoupper($product->getSku()));
}
```

### Send Notification

```php
public function execute(Observer $observer)
{
    $order = $observer->getEvent()->getOrder();
    
    $this->emailSender->send(
        $order->getCustomerEmail(),
        'Order Confirmation',
        ['order' => $order]
    );
}
```

### Update Related Data

```php
public function execute(Observer $observer)
{
    $product = $observer->getEvent()->getProduct();
    
    // Update related records
    $this->relatedProducts->updateStock($product->getId());
}
```

### Prevent Action (throw exception)

```php
public function execute(Observer $observer)
{
    $product = $observer->getEvent()->getProduct();
    
    if ($product->getPrice() < 0) {
        throw new \Magento\Framework\Exception\LocalizedException(
            __('Price cannot be negative')
        );
    }
}
```

## Best Practices

### ✅ DO

```php
// Keep observers simple and focused
public function execute(Observer $observer)
{
    $order = $observer->getEvent()->getOrder();
    $this->notifier->sendOrderEmail($order);
}

// Handle exceptions
public function execute(Observer $observer)
{
    try {
        // Your code
    } catch (\Exception $e) {
        $this->logger->error($e->getMessage());
    }
}

// Use descriptive event names
$this->eventManager->dispatch('acme_order_processed', [...]);

// Pass relevant data only
$this->eventManager->dispatch('my_event', [
    'order_id' => $order->getId(),
    'status' => $order->getStatus()
]);
```

### ❌ DON'T

```php
// Don't put heavy processing
public function execute(Observer $observer)
{
    // Bad: Long API call
    $this->apiClient->syncAllProducts(); // Takes 5 minutes!
}

// Don't create circular dispatches
public function execute(Observer $observer)
{
    // Bad: Dispatches same event again
    $this->eventManager->dispatch('my_event', [...]);
}

// Don't pass too much data
$this->eventManager->dispatch('event', [
    'everything' => $this->getEverything() // Huge array
]);

// Don't return values (observers return void)
public function execute(Observer $observer)
{
    return $something; // Wrong! Return void
}
```

## Debugging

### Check if Observer is Registered

```bash
# Search events.xml files
grep -r "my_observer" app/code/Vendor/Module/etc/

# Check module enabled
bin/magento module:status | grep Module
```

### Watch Events Execute

```bash
# Add logging in observer
$this->logger->info('Observer executed!');

# Watch logs
tail -f var/log/system.log
```

### Find Event Dispatches

```bash
# Search for event dispatch
grep -r "dispatch('event_name" vendor/magento/

# Find all dispatches in class
grep "dispatch(" vendor/magento/module-catalog/Model/Product.php
```

### Test Observer

```php
// Create test command
public function execute(InputInterface $input, OutputInterface $output)
{
    $this->eventManager->dispatch('my_test_event', [
        'test' => 'data'
    ]);
    
    $output->writeln('Event dispatched - check logs');
}
```

## Commands

```bash
# Enable module
bin/magento module:enable Vendor_Module

# Setup
bin/magento setup:upgrade

# Clear cache
bin/magento cache:flush

# Watch logs
tail -f var/log/system.log

# Search for events
grep -r "eventManager->dispatch" vendor/magento/

# Find observer registration
find app/code -name "events.xml"
```

## Event Data Patterns

### Simple Values

```php
$this->eventManager->dispatch('event', [
    'value' => 123,
    'text' => 'hello',
    'flag' => true
]);

// In observer
$value = $observer->getEvent()->getData('value'); // 123
```

### Objects

```php
$this->eventManager->dispatch('event', [
    'product' => $product,
    'customer' => $customer
]);

// In observer
$product = $observer->getEvent()->getProduct();
$customer = $observer->getEvent()->getCustomer();
```

### Arrays

```php
$this->eventManager->dispatch('event', [
    'items' => [1, 2, 3],
    'config' => ['key' => 'value']
]);

// In observer
$items = $observer->getEvent()->getData('items');
```

## Area Detection

```php
// In observer, detect current area
public function execute(Observer $observer)
{
    $area = $this->state->getAreaCode();
    
    if ($area === 'frontend') {
        // Frontend logic
    } elseif ($area === 'adminhtml') {
        // Admin logic
    }
}
```

## Complete Example

```php
// 1. Dispatch event
namespace Acme\Shop\Model;

use Magento\Framework\Event\ManagerInterface;

class OrderProcessor
{
    private $eventManager;
    
    public function __construct(ManagerInterface $eventManager)
    {
        $this->eventManager = $eventManager;
    }
    
    public function process($order)
    {
        // Process order...
        
        // Dispatch event
        $this->eventManager->dispatch('acme_order_processed', [
            'order' => $order,
            'processor' => $this
        ]);
    }
}

// 2. Create observer
namespace Acme\Shop\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

class OrderProcessedObserver implements ObserverInterface
{
    private $logger;
    private $emailSender;
    
    public function __construct($logger, $emailSender)
    {
        $this->logger = $logger;
        $this->emailSender = $emailSender;
    }
    
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        
        $this->logger->info('Order processed: ' . $order->getId());
        $this->emailSender->send($order->getCustomerEmail());
    }
}

// 3. Register in events.xml
<config>
    <event name="acme_order_processed">
        <observer name="acme_order_notification" 
                  instance="Acme\Shop\Observer\OrderProcessedObserver" />
    </event>
</config>
```

---

**Pro Tip**: Start with simple observers and gradually add complexity. Always test in a development environment first!

