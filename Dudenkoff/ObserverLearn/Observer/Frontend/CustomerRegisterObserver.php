<?php
/**
 * Customer Register Observer (Frontend Only)
 * 
 * AREA-SPECIFIC OBSERVERS:
 * 
 * This observer only works in FRONTEND area because it's registered in:
 * etc/frontend/events.xml
 * 
 * AREAS IN MAGENTO:
 * - frontend   - Customer-facing storefront
 * - adminhtml  - Admin panel
 * - webapi_rest - REST API
 * - webapi_soap - SOAP API
 * - crontab    - Cron jobs
 * 
 * WHY AREA-SPECIFIC?
 * - Performance (only load when needed)
 * - Different behavior per area
 * - Separation of concerns
 */

namespace Dudenkoff\ObserverLearn\Observer\Frontend;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Psr\Log\LoggerInterface;

class CustomerRegisterObserver implements ObserverInterface
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Execute when customer registers on frontend
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $customer = $observer->getEvent()->getData('customer');
        
        if ($customer) {
            $this->logger->info("[FRONTEND] New customer registered", [
                'email' => $customer->getEmail(),
                'name' => $customer->getFirstname() . ' ' . $customer->getLastname(),
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            // Frontend-specific actions:
            // - Send welcome email
            // - Award welcome bonus
            // - Track registration analytics
            // - Subscribe to newsletter
        }
    }
}

