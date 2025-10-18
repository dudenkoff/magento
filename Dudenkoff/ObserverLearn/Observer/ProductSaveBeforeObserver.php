<?php
/**
 * Product Save Before Observer
 * 
 * BEFORE vs AFTER EVENTS:
 * 
 * BEFORE events (_before):
 * - Execute BEFORE the operation
 * - Can MODIFY the object being saved
 * - Can PREVENT the operation (throw exception)
 * - Data not yet in database
 * 
 * AFTER events (_after):
 * - Execute AFTER the operation
 * - Object already saved to database
 * - Can perform follow-up actions
 * - Cannot prevent the operation
 * 
 * THIS OBSERVER:
 * Listens to: catalog_product_save_before
 * Purpose: Validate/modify product before saving
 */

namespace Dudenkoff\ObserverLearn\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Psr\Log\LoggerInterface;

class ProductSaveBeforeObserver implements ObserverInterface
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
     * Execute before product save
     * 
     * WHAT YOU CAN DO HERE:
     * - Validate product data
     * - Modify product attributes
     * - Set default values
     * - Throw exception to prevent save
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $observer->getEvent()->getData('product');
        
        if ($product) {
            // Example: Auto-uppercase SKU
            $sku = $product->getSku();
            if ($sku) {
                $product->setSku(strtoupper($sku));
                $this->logger->info("Product SKU uppercased: {$sku} -> " . $product->getSku());
            }
            
            // Example: Validate price
            $price = $product->getPrice();
            if ($price !== null && $price < 0) {
                $this->logger->warning("Negative price detected for product: " . $product->getName());
                // Could throw exception here to prevent save:
                // throw new \Magento\Framework\Exception\LocalizedException(
                //     __('Product price cannot be negative')
                // );
            }
            
            // Example: Set default meta description
            if (!$product->getMetaDescription()) {
                $product->setMetaDescription(
                    substr($product->getDescription() ?? '', 0, 160)
                );
            }
        }
    }
}

