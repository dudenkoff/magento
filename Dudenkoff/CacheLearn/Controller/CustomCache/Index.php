<?php
/**
 * Custom Cache Demo Page
 * URL: /cachelearn/customcache
 */
namespace Dudenkoff\CacheLearn\Controller\CustomCache;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;

class Index implements HttpGetActionInterface
{
    private $pageFactory;

    public function __construct(PageFactory $pageFactory)
    {
        $this->pageFactory = $pageFactory;
    }

    public function execute()
    {
        $page = $this->pageFactory->create();
        $page->getConfig()->getTitle()->set('Custom Cache Demo - Data Caching');
        return $page;
    }
}

