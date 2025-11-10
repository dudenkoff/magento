<?php
/**
 * Block Cache Demo Page
 * URL: /cachelearn/blockcache
 */
namespace Dudenkoff\CacheLearn\Controller\BlockCache;

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
        $page->getConfig()->getTitle()->set('Block Cache Demo - Cacheable vs Non-Cacheable');
        return $page;
    }
}

