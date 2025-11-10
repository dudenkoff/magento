<?php
/**
 * Unified Cache Demo Page
 * URL: /cachelearn
 */
namespace Dudenkoff\CacheLearn\Controller\Index;

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
        $page->getConfig()->getTitle()->set('Magento Cache Demo Suite');
        return $page;
    }
}



