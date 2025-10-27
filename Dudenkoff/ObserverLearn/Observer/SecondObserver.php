<?php
/**
 * Second Observer
 * 
 * Executes SECOND for dudenkoff_demo_event.
 */

namespace Dudenkoff\ObserverLearn\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Psr\Log\LoggerInterface;

class SecondObserver implements ObserverInterface
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        $this->logger->info("[2/3] SecondObserver executed");
    }
}

