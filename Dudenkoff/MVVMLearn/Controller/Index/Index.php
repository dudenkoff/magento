<?php
/**
 * Copyright © Dudenkoff. All rights reserved.
 * Frontend Controller - Book List Page
 * 
 * CONTROLLER:
 * - Handles HTTP requests
 * - Processes input
 * - Returns response (page/redire ct/json)
 * 
 * URL: /books/index/index (or /books/)
 */

namespace Dudenkoff\MVVMLearn\Controller\Index;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;

class Index implements HttpGetActionInterface
{
    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @param PageFactory $pageFactory
     */
    public function __construct(
        PageFactory $pageFactory
    ) {
        $this->pageFactory = $pageFactory;
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        return $this->pageFactory->create();
    }
}

