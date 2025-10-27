<?php
/**
 * Add to Cart Observer (Frontend Only)
 * 
 * Tracks when products are added to cart on frontend.
 */

namespace Dudenkoff\ObserverLearn\Observer\Frontend;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Psr\Log\LoggerInterface;

class AddToCartObserver implements ObserverInterface
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        $product = $observer->getEvent()->getData('product');
        
        if ($product) {
            $this->logger->info("[FRONTEND] Product added to cart", [
                'product_id' => $product->getId(),
                'product_name' => $product->getName(),
                'sku' => $product->getSku(),
                'price' => $product->getPrice()
            ]);
            
            // Track for analytics, recommendations, etc.
        }
    }
}

