<?php
/**
 * Helper to get cache file information
 */
namespace Dudenkoff\CacheLearn\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Filesystem\DirectoryList;

class CacheInfo extends AbstractHelper
{
    private $directoryList;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        DirectoryList $directoryList
    ) {
        parent::__construct($context);
        $this->directoryList = $directoryList;
    }

    /**
     * Get cache directory path
     */
    public function getCacheDir(): string
    {
        return $this->directoryList->getPath(DirectoryList::CACHE);
    }

}

