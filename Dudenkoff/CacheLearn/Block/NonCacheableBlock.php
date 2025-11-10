<?php
/**
 * Non-Cacheable Block - Always regenerated
 */
namespace Dudenkoff\CacheLearn\Block;

use Magento\Framework\View\Element\Template;

class NonCacheableBlock extends Template
{
    public function __construct(Template\Context $context, array $data = [])
    {
        parent::__construct($context, $data);
    }

    public function getCurrentTime(): string
    {
        return date('Y-m-d H:i:s');
    }
}

