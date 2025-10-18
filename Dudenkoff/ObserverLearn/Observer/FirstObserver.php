<?php
/**
 * First Observer
 * 
 * OBSERVER EXECUTION ORDER:
 * 
 * When multiple observers listen to the same event,
 * they execute in the order they're defined in events.xml.
 * 
 * THIS OBSERVER:
 * Part of demonstration showing execution order.
 * Executes FIRST for dudenkoff_demo_event.
 */

namespace Dudenkoff\ObserverLearn\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Psr\Log\LoggerInterface;

class FirstObserver implements ObserverInterface
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        $this->logger->info("[1/3] FirstObserver executed");
        
        // Get event data
        $data = $observer->getEvent()->getData();
        $this->logger->info("FirstObserver received data: " . json_encode($data));
    }
}

