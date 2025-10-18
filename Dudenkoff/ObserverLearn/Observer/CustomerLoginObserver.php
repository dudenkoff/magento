<?php
/**
 * Customer Login Observer
 * 
 * WHAT IS AN OBSERVER?
 * An observer is a class that "observes" (listens to) events.
 * When an event is dispatched, all registered observers execute.
 * 
 * OBSERVER STRUCTURE:
 * - Must implement ObserverInterface
 * - Must have execute() method
 * - Receives Observer object containing event data
 * 
 * THIS OBSERVER:
 * Listens to: customer_login event (dispatched by Magento core)
 * Purpose: Log customer login activity
 * When: After customer successfully logs in
 */

namespace Dudenkoff\ObserverLearn\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Psr\Log\LoggerInterface;

class CustomerLoginObserver implements ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor
     * 
     * Dependencies are injected via DI (just like any other class)
     *
     * @param LoggerInterface $logger
     */
    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * Execute observer
     * 
     * This method is called when the customer_login event is dispatched.
     * 
     * HOW TO GET EVENT DATA:
     * $observer->getEvent()->getData('key')        - Get specific data
     * $observer->getEvent()->getData()             - Get all data
     * $observer->getEvent()->getCustomer()         - If passed as named param
     * 
     * IMPORTANT:
     * - This method should NOT return anything
     * - Use type void or no return type
     * - Exceptions will be caught by Magento
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        // Get the customer object from the event
        // Events pass data as named parameters
        $customer = $observer->getEvent()->getData('customer');
        
        if ($customer) {
            $customerId = $customer->getId();
            $customerEmail = $customer->getEmail();
            $customerName = $customer->getName();
            
            // Log the login
            $this->logger->info('Customer logged in', [
                'customer_id' => $customerId,
                'email' => $customerEmail,
                'name' => $customerName,
                'time' => date('Y-m-d H:i:s')
            ]);
            
            // You could do other things here:
            // - Update last login time
            // - Send notification
            // - Track analytics
            // - Award loyalty points
            // - etc.
        }
    }
}

