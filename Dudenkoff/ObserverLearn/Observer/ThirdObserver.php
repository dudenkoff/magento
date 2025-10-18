<?php
/**
 * Third Observer
 * 
 * Executes THIRD (last) for dudenkoff_demo_event.
 */

namespace Dudenkoff\ObserverLearn\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Psr\Log\LoggerInterface;

class ThirdObserver implements ObserverInterface
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        $this->logger->info("[3/3] ThirdObserver executed - all done!");
    }
}

