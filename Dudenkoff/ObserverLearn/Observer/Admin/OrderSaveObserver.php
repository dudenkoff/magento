<?php
/**
 * Order Save Observer (Admin Only)
 * 
 * Detects when orders are modified in admin panel.
 */

namespace Dudenkoff\ObserverLearn\Observer\Admin;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Psr\Log\LoggerInterface;

class OrderSaveObserver implements ObserverInterface
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getData('order');
        
        if ($order) {
            $this->logger->info("[ADMIN] Order saved from admin", [
                'order_id' => $order->getId(),
                'increment_id' => $order->getIncrementId(),
                'status' => $order->getStatus(),
                'grand_total' => $order->getGrandTotal()
            ]);
            
            // Admin-specific actions:
            // - Notify warehouse
            // - Update ERP system
            // - Log admin changes
        }
    }
}

