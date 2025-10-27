<?php
/**
 * Order Processed Notification Observer
 * 
 * CUSTOM EVENTS:
 * You can dispatch your own events anywhere in your code:
 * 
 * $this->eventManager->dispatch('event_name', ['data' => $value]);
 * 
 * Then create observers to handle them.
 * 
 * BENEFITS:
 * - Decouples code (sender doesn't know about receivers)
 * - Multiple observers can respond to same event
 * - Easy to add new functionality without changing existing code
 * - Follows Open/Closed Principle
 * 
 * THIS OBSERVER:
 * Listens to: dudenkoff_order_processed (custom event)
 * Purpose: Send notification when order is processed
 */

namespace Dudenkoff\ObserverLearn\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Psr\Log\LoggerInterface;

class OrderProcessedNotificationObserver implements ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Execute observer
     * 
     * This responds to a CUSTOM event that we dispatch ourselves.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        // Get custom event data
        $orderId = $observer->getEvent()->getData('order_id');
        $status = $observer->getEvent()->getData('status');
        $customer = $observer->getEvent()->getData('customer');
        
        $this->logger->info("Order processed - sending notification", [
            'order_id' => $orderId,
            'status' => $status,
            'customer' => $customer
        ]);
        
        // Here you would:
        // - Send email notification
        // - Send SMS
        // - Push notification
        // - Update external system
        // - etc.
    }
}

