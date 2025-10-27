<?php
/**
 * Disabled Observer
 * 
 * DISABLING OBSERVERS:
 * 
 * In events.xml, you can set disabled="true":
 * <observer name="..." instance="..." disabled="true" />
 * 
 * WHEN TO DISABLE:
 * - Testing (temporarily disable functionality)
 * - Feature flags (enable/disable features)
 * - Debugging (isolate issues)
 * - Conditional functionality
 * 
 * THIS OBSERVER:
 * Configured as disabled in events.xml
 * Will NOT execute even if event is dispatched
 */

namespace Dudenkoff\ObserverLearn\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Psr\Log\LoggerInterface;

class DisabledObserver implements ObserverInterface
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
     * This will NOT execute because observer is disabled in events.xml
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $this->logger->info("This message will never appear - observer is disabled!");
    }
}

