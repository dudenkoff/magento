<?php
/**
 * Copyright © Dudenkoff. All rights reserved.
 * Cache Demo Controller
 * 
 * URL: /cachelearn
 * 
 * This controller demonstrates cache operations in action
 */

namespace Dudenkoff\CacheLearn\Controller\Index;

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
        $page = $this->pageFactory->create();
        $page->getConfig()->getTitle()->set('Cache Learning - Interactive Examples');
        return $page;
    }
}

