<?php
/**
 * Order Processed Analytics Observer
 * 
 * MULTIPLE OBSERVERS ON SAME EVENT:
 * 
 * The same event can have multiple observers.
 * Each observer handles one specific concern.
 * 
 * EXAMPLE:
 * Event: dudenkoff_order_processed
 * - Observer 1: Send notification (this class)
 * - Observer 2: Update analytics (OrderProcessedAnalyticsObserver)
 * 
 * BENEFITS:
 * - Single Responsibility: Each observer does ONE thing
 * - Easy to add/remove functionality
 * - Observers don't depend on each other
 * 
 * THIS OBSERVER:
 * Listens to: dudenkoff_order_processed (same as notification observer)
 * Purpose: Update analytics/statistics
 */

namespace Dudenkoff\ObserverLearn\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Psr\Log\LoggerInterface;

class OrderProcessedAnalyticsObserver implements ObserverInterface
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
     * This is the SECOND observer for the same event.
     * It handles a different concern (analytics vs notification).
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $orderId = $observer->getEvent()->getData('order_id');
        $status = $observer->getEvent()->getData('status');
        
        $this->logger->info("Order processed - updating analytics", [
            'order_id' => $orderId,
            'status' => $status,
            'timestamp' => time()
        ]);
        
        // Here you would:
        // - Update statistics
        // - Send to analytics service (Google Analytics, etc.)
        // - Update reports
        // - Track KPIs
        // - etc.
    }
}

