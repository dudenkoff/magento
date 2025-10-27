<?php
/**
 * Controller Postdispatch Observer
 * 
 * Executes AFTER controller action completes.
 */

namespace Dudenkoff\ObserverLearn\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Psr\Log\LoggerInterface;

class ControllerPostdispatchObserver implements ObserverInterface
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        $request = $observer->getEvent()->getData('request');
        
        if ($request) {
            $this->logger->info("[POSTDISPATCH] Controller finished executing", [
                'full_action' => $request->getFullActionName()
            ]);
        }
    }
}

