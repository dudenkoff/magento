<?php
/**
 * Event Dispatcher Model
 * 
 * HOW TO DISPATCH EVENTS:
 * 
 * Inject EventManagerInterface and call dispatch():
 * 
 * $this->eventManager->dispatch('event_name', [
 *     'key' => $value,
 *     'object' => $object
 * ]);
 * 
 * BEST PRACTICES:
 * - Use descriptive event names
 * - Prefix with module name (vendor_module_action)
 * - Pass relevant data
 * - Document dispatched events
 * - Don't pass too much data
 * 
 * THIS CLASS:
 * Demonstrates dispatching custom events from your code.
 */

namespace Dudenkoff\ObserverLearn\Model;

use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Psr\Log\LoggerInterface;

class EventDispatcher
{
    /**
     * @var EventManagerInterface
     */
    private $eventManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor
     *
     * @param EventManagerInterface $eventManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        EventManagerInterface $eventManager,
        LoggerInterface $logger
    ) {
        $this->eventManager = $eventManager;
        $this->logger = $logger;
    }

    /**
     * Process an order and dispatch event
     * 
     * This demonstrates dispatching a custom event.
     * Multiple observers can listen to this event.
     *
     * @param int $orderId
     * @param string $status
     * @param string $customer
     * @return void
     */
    public function processOrder(int $orderId, string $status, string $customer): void
    {
        $this->logger->info("Processing order #{$orderId}");
        
        // Do some processing...
        // ...
        
        // Dispatch custom event
        // Any observers registered for 'dudenkoff_order_processed' will execute
        $this->eventManager->dispatch('dudenkoff_order_processed', [
            'order_id' => $orderId,
            'status' => $status,
            'customer' => $customer,
            'timestamp' => time()
        ]);
        
        $this->logger->info("Order #{$orderId} processed, event dispatched");
    }

    /**
     * Demonstrate event with multiple observers
     * 
     * This event has 3 observers that will execute in order.
     *
     * @return void
     */
    public function demoMultipleObservers(): void
    {
        $this->logger->info("=== Dispatching dudenkoff_demo_event ===");
        
        // This will trigger FirstObserver, SecondObserver, ThirdObserver
        $this->eventManager->dispatch('dudenkoff_demo_event', [
            'message' => 'Hello from EventDispatcher',
            'data' => ['foo' => 'bar', 'baz' => 123]
        ]);
        
        $this->logger->info("=== Event dispatched, check logs for observer execution ===");
    }

    /**
     * Demonstrate disabled observer
     * 
     * Even though DisabledObserver is registered for this event,
     * it won't execute because it's disabled in events.xml.
     *
     * @return void
     */
    public function demoDisabledObserver(): void
    {
        $this->logger->info("Dispatching dudenkoff_custom_event (has disabled observer)");
        
        $this->eventManager->dispatch('dudenkoff_custom_event', [
            'test' => 'This should NOT trigger DisabledObserver'
        ]);
        
        $this->logger->info("Event dispatched - DisabledObserver should NOT have executed");
    }

    /**
     * Pass objects in events
     * 
     * You can pass any type of data, including objects.
     * Observers can then work with these objects.
     *
     * @return void
     */
    public function demoObjectInEvent(): void
    {
        // Create a mock object
        $mockOrder = new \stdClass();
        $mockOrder->id = 12345;
        $mockOrder->total = 99.99;
        $mockOrder->status = 'processing';
        
        $this->logger->info("Dispatching event with object");
        
        $this->eventManager->dispatch('dudenkoff_order_processed', [
            'order' => $mockOrder,
            'order_id' => $mockOrder->id,
            'status' => $mockOrder->status,
            'customer' => 'John Doe'
        ]);
    }
}

