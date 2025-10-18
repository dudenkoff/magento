<?php
/**
 * Controller Predispatch Observer
 * 
 * CONTROLLER DISPATCH EVENTS:
 * 
 * Magento dispatches events during request processing:
 * 
 * 1. controller_action_predispatch         - Before ANY controller
 * 2. controller_action_predispatch_[route] - Before specific route
 * 3. controller_action_predispatch_[route]_[controller]_[action] - Specific action
 * 4. [Controller executes]
 * 5. controller_action_postdispatch_[route]_[controller]_[action] - After specific
 * 6. controller_action_postdispatch_[route] - After route
 * 7. controller_action_postdispatch        - After ANY controller
 * 
 * USE CASES:
 * - Logging
 * - Authentication checks
 * - Redirects
 * - Setting layout handles
 * - Modifying request/response
 */

namespace Dudenkoff\ObserverLearn\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Psr\Log\LoggerInterface;

class ControllerPredispatchObserver implements ObserverInterface
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Execute before controller action
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Framework\App\RequestInterface $request */
        $request = $observer->getEvent()->getData('request');
        
        if ($request) {
            $this->logger->info("[PREDISPATCH] Controller about to execute", [
                'module' => $request->getModuleName(),
                'controller' => $request->getControllerName(),
                'action' => $request->getActionName(),
                'full_action' => $request->getFullActionName()
            ]);
        }
    }
}

