<?php
/**
 * Non-Cacheable Block - Always regenerated
 */
namespace Dudenkoff\CacheLearn\Block;

use Magento\Framework\View\Element\Template;
use Dudenkoff\CacheLearn\Helper\CacheInfo;

class NonCacheableBlock extends Template
{
    private $cacheInfo;

    public function __construct(
        Template\Context $context,
        CacheInfo $cacheInfo,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->cacheInfo = $cacheInfo;
    }

    public function getCurrentTime(): string
    {
        return date('Y-m-d H:i:s');
    }

    public function getPageCacheFiles(): array
    {
        return $this->cacheInfo->findPageCacheFiles();
    }

    public function getCacheInfo(): CacheInfo
    {
        return $this->cacheInfo;
    }
}

