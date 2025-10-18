# Magento 2 Event/Observer Pattern Learning Module

## Overview

This module teaches you how the **Event/Observer pattern** works in Magento 2 through practical, hands-on examples with detailed explanations.

## What is the Event/Observer Pattern?

The **Event/Observer pattern** (also called Publish/Subscribe) is a behavioral design pattern where:

1. **Events** are dispatched when something happens
2. **Observers** listen for events and execute code when they occur
3. The event dispatcher doesn't know about observers (loose coupling)

### Benefits:
- **Decoupling**: Event dispatchers don't depend on observers
- **Extensibility**: Add new functionality without modifying existing code
- **Maintainability**: Each observer handles one specific concern
- **Flexibility**: Enable/disable observers easily

## Quick Start

### 1. Enable the Module

```bash
# Go to Magento root directory
cd /home/dudenkoff/Projects/magento

# Enable the module
bin/magento module:enable Dudenkoff_ObserverLearn

# Run setup upgrade
bin/magento setup:upgrade

# Clear cache
bin/magento cache:flush
```

### 2. Run the Demo Command

```bash
bin/magento dudenkoff:observer:demo
```

### 3. Watch the Logs

Open another terminal and watch the logs to see observers executing:

```bash
tail -f var/log/system.log
```

## Module Structure

```
app/code/Dudenkoff/ObserverLearn/
├── registration.php                          ← Module registration
├── etc/
│   ├── module.xml                            ← Module declaration
│   ├── di.xml                                ← DI configuration
│   ├── events.xml                            ← ⭐ GLOBAL event registrations
│   ├── frontend/
│   │   └── events.xml                        ← Frontend-only events
│   └── adminhtml/
│       └── events.xml                        ← Admin-only events
├── Observer/
│   ├── CustomerLoginObserver.php             ← Core event example
│   ├── ProductSaveBeforeObserver.php         ← Before event
│   ├── ProductSaveAfterObserver.php          ← After event
│   ├── OrderProcessedNotificationObserver.php ← Custom event #1
│   ├── OrderProcessedAnalyticsObserver.php   ← Custom event #2
│   ├── DisabledObserver.php                  ← Disabled observer
│   ├── FirstObserver.php                     ← Execution order #1
│   ├── SecondObserver.php                    ← Execution order #2
│   ├── ThirdObserver.php                     ← Execution order #3
│   ├── ControllerPredispatchObserver.php     ← Controller events
│   ├── ControllerPostdispatchObserver.php    ← Controller events
│   ├── Frontend/
│   │   ├── CustomerRegisterObserver.php      ← Frontend-only
│   │   └── AddToCartObserver.php             ← Frontend-only
│   └── Admin/
│       ├── AdminLoginObserver.php            ← Admin-only
│       └── OrderSaveObserver.php             ← Admin-only
├── Model/
│   └── EventDispatcher.php                   ← ⭐ How to dispatch events
├── Console/Command/
│   └── ObserverDemoCommand.php               ← Demo command
├── README.md                                  ← This file
├── OBSERVER_CONCEPTS.md                       ← Deep dive
├── OBSERVER_CHEATSHEET.md                     ← Quick reference
└── SETUP.md                                   ← Setup guide
```

## Key Concepts Demonstrated

### 1. Listening to Core Magento Events
**Files**: `Observer/CustomerLoginObserver.php`, `Observer/ProductSaveBeforeObserver.php`

Magento dispatches hundreds of events. You can listen to them:

```xml
<!-- etc/events.xml -->
<event name="customer_login">
    <observer name="my_observer" instance="Vendor\Module\Observer\MyObserver" />
</event>
```

### 2. Creating and Dispatching Custom Events
**File**: `Model/EventDispatcher.php`

Dispatch your own events:

```php
$this->eventManager->dispatch('my_custom_event', [
    'data' => $value,
    'object' => $object
]);
```

### 3. Observer Implementation
**File**: `Observer/CustomerLoginObserver.php`

All observers must implement `ObserverInterface`:

```php
class MyObserver implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $data = $observer->getEvent()->getData('key');
        // Your logic here
    }
}
```

### 4. Before vs After Events
**Files**: `Observer/ProductSaveBeforeObserver.php` (before), `Observer/ProductSaveAfterObserver.php` (after)

- **Before** (_before): Execute before operation, can modify/prevent
- **After** (_after): Execute after operation, for follow-up actions

### 5. Multiple Observers on Same Event
**Files**: `Observer/OrderProcessedNotificationObserver.php`, `Observer/OrderProcessedAnalyticsObserver.php`

Multiple observers can listen to the same event. Each handles one concern.

### 6. Area-Specific Observers
**Files**: `etc/frontend/events.xml`, `etc/adminhtml/events.xml`

Register observers for specific areas only:
- `etc/events.xml` - Global (all areas)
- `etc/frontend/events.xml` - Frontend only
- `etc/adminhtml/events.xml` - Admin only

### 7. Disabled Observers
**File**: `Observer/DisabledObserver.php`

Disable observers without removing XML:

```xml
<observer name="my_observer" instance="..." disabled="true" />
```

### 8. Observer Execution Order
**Files**: `Observer/FirstObserver.php`, `SecondObserver.php`, `ThirdObserver.php`

Observers execute in the order they're defined in events.xml.

## Common Core Magento Events

### Customer Events
- `customer_login` - Customer logs in
- `customer_logout` - Customer logs out
- `customer_register_success` - Registration successful
- `customer_save_before` - Before customer save
- `customer_save_after` - After customer save

### Product Events
- `catalog_product_save_before` - Before product save
- `catalog_product_save_after` - After product save
- `catalog_product_delete_before` - Before product delete
- `catalog_product_delete_after` - After product delete

### Order Events
- `sales_order_place_before` - Before order placed
- `sales_order_place_after` - After order placed
- `sales_order_save_before` - Before order save
- `sales_order_save_after` - After order save

### Cart Events
- `checkout_cart_add_product_complete` - Product added to cart
- `checkout_cart_product_add_before` - Before adding to cart
- `checkout_cart_save_before` - Before cart save
- `checkout_cart_save_after` - After cart save

### Controller Events
- `controller_action_predispatch` - Before any controller
- `controller_action_postdispatch` - After any controller
- `controller_action_predispatch_{route}_{controller}_{action}` - Specific action

## Testing the Module

### Run the Demo Command

```bash
bin/magento dudenkoff:observer:demo
```

### Watch Logs in Real-Time

```bash
# System log
tail -f var/log/system.log

# Debug log
tail -f var/log/debug.log

# Both
tail -f var/log/*.log
```

### Test Specific Observers

```bash
# Customer login (if you have a customer account)
# Login on frontend and check logs

# Product save (if you have admin access)
# Edit a product in admin and check logs
```

## Practical Examples

### Example 1: Send Email When Order is Placed

```php
// Observer/OrderPlacedEmailObserver.php
class OrderPlacedEmailObserver implements ObserverInterface
{
    private $emailSender;
    
    public function __construct(EmailSender $emailSender)
    {
        $this->emailSender = $emailSender;
    }
    
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getData('order');
        $this->emailSender->send($order->getCustomerEmail(), 'Order Confirmation');
    }
}
```

```xml
<!-- etc/events.xml -->
<event name="sales_order_place_after">
    <observer name="send_order_email" 
              instance="Vendor\Module\Observer\OrderPlacedEmailObserver" />
</event>
```

### Example 2: Log All Product Changes

```php
// Observer/ProductChangeLogger.php
class ProductChangeLogger implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $product = $observer->getEvent()->getData('product');
        
        if ($product->dataHasChangedFor('price')) {
            $this->logger->info('Price changed', [
                'product_id' => $product->getId(),
                'old_price' => $product->getOrigData('price'),
                'new_price' => $product->getPrice()
            ]);
        }
    }
}
```

### Example 3: Redirect Logged-in Users

```php
// Observer/RedirectLoggedInUsers.php
class RedirectLoggedInUsers implements ObserverInterface
{
    private $customerSession;
    private $redirect;
    
    public function execute(Observer $observer)
    {
        if ($this->customerSession->isLoggedIn()) {
            $controller = $observer->getEvent()->getData('controller_action');
            $this->redirect->redirect($controller->getResponse(), 'customer/account');
        }
    }
}
```

## Best Practices

### ✅ DO:
- Keep observers focused on one task
- Use descriptive event names
- Pass relevant data in events
- Handle exceptions properly
- Log important actions
- Document your events

### ❌ DON'T:
- Put heavy processing in observers
- Create circular event dispatches
- Throw uncaught exceptions
- Modify objects you shouldn't
- Rely on execution order (when possible)
- Pass entire object managers

## Event Naming Conventions

### Core Magento Pattern:
```
{entity}_{action}_{when}
```

Examples:
- `catalog_product_save_before`
- `sales_order_place_after`
- `customer_login`

### Custom Events Pattern:
```
{vendor}_{module}_{action}
```

Examples:
- `dudenkoff_order_processed`
- `acme_inventory_updated`
- `mycompany_shipment_created`

## Troubleshooting

### Observer not executing?

1. **Check registration**:
   ```bash
   grep -r "my_observer" app/code/Vendor/Module/etc/
   ```

2. **Verify module enabled**:
   ```bash
   bin/magento module:status Vendor_Module
   ```

3. **Clear cache**:
   ```bash
   bin/magento cache:flush
   ```

4. **Check logs**:
   ```bash
   tail -f var/log/system.log
   ```

### Event not dispatching?

1. **Verify event name** (check Magento core code)
2. **Check if event actually occurs** (add logging)
3. **Verify area** (frontend vs admin)

### Wrong execution order?

Observers execute in order defined in events.xml. Reorder them if needed.

## Finding Core Events

### Method 1: Search Codebase

```bash
# Find events in Magento core
grep -r "eventManager->dispatch" vendor/magento/

# Find specific event
grep -r "customer_login" vendor/magento/
```

### Method 2: IDE Search

Search for `->dispatch(` in your IDE

### Method 3: Use a List

See `OBSERVER_CONCEPTS.md` for a comprehensive list of core events.

## Further Reading

- `OBSERVER_CONCEPTS.md` - Deep dive into concepts (2000+ words)
- `OBSERVER_CHEATSHEET.md` - Quick reference guide
- `SETUP.md` - Detailed setup instructions
- [Magento DevDocs: Events and Observers](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/events-and-observers.html)

## Files to Study

Study these files in order:

1. **etc/events.xml** - See all event registrations
2. **Observer/CustomerLoginObserver.php** - Basic observer structure
3. **Observer/ProductSaveBeforeObserver.php** - Before event
4. **Observer/ProductSaveAfterObserver.php** - After event
5. **Model/EventDispatcher.php** - How to dispatch events
6. **Console/Command/ObserverDemoCommand.php** - See it all work
7. **OBSERVER_CONCEPTS.md** - Deep understanding

## Author

Created for learning purposes to understand Magento 2's Event/Observer pattern.

## License

Educational use only.

