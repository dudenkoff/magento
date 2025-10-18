<?php
/**
 * Admin Login Observer (Admin Only)
 * 
 * Logs admin user logins for security auditing.
 * Only executes in admin area.
 */

namespace Dudenkoff\ObserverLearn\Observer\Admin;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Psr\Log\LoggerInterface;

class AdminLoginObserver implements ObserverInterface
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        $user = $observer->getEvent()->getData('user');
        
        if ($user) {
            $this->logger->info("[ADMIN] Admin user logged in", [
                'username' => $user->getUserName(),
                'user_id' => $user->getId(),
                'email' => $user->getEmail(),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            
            // Security actions:
            // - Log IP address
            // - Check for suspicious activity
            // - Send notification
            // - Update last login time
        }
    }
}

