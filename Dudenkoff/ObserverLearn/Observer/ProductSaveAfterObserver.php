<?php
/**
 * Product Save After Observer
 * 
 * AFTER EVENT USE CASES:
 * - Send notifications
 * - Update related data
 * - Clear caches
 * - Sync with external systems
 * - Log changes
 * - Update search index
 * 
 * THIS OBSERVER:
 * Listens to: catalog_product_save_after
 * Purpose: Perform actions after product is saved
 */

namespace Dudenkoff\ObserverLearn\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Psr\Log\LoggerInterface;

class ProductSaveAfterObserver implements ObserverInterface
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
     * Execute after product save
     * 
     * At this point:
     * - Product is already saved to database
     * - Has ID assigned
     * - Cannot prevent the save
     * - Good for follow-up actions
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $observer->getEvent()->getData('product');
        
        if ($product) {
            $productId = $product->getId();
            $productName = $product->getName();
            $sku = $product->getSku();
            
            // Log the save
            $this->logger->info("Product saved successfully", [
                'id' => $productId,
                'name' => $productName,
                'sku' => $sku,
                'is_new' => $product->isObjectNew()
            ]);
            
            // Possible actions here:
            // - Send notification to admin
            // - Update analytics
            // - Sync with external system
            // - Clear custom caches
            // - Update related products
            // - Generate thumbnails
            // - etc.
            
            // Example: Detect price changes
            if ($product->dataHasChangedFor('price')) {
                $oldPrice = $product->getOrigData('price');
                $newPrice = $product->getPrice();
                $this->logger->info("Product price changed", [
                    'product_id' => $productId,
                    'old_price' => $oldPrice,
                    'new_price' => $newPrice
                ]);
            }
        }
    }
}

