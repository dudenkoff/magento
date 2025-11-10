<?php
/**
 * Cacheable Block - HTML will be cached
 */
namespace Dudenkoff\CacheLearn\Block;

use Magento\Framework\View\Element\Template;
use Dudenkoff\CacheLearn\Helper\CacheInfo;
use Magento\Framework\App\Cache\StateInterface;

class CacheableBlock extends Template
{
    private $cacheInfo;
    private $cacheState;

    public function __construct(
        Template\Context $context,
        CacheInfo $cacheInfo,
        StateInterface $cacheState,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->cacheInfo = $cacheInfo;
        $this->cacheState = $cacheState;
    }

    public function getGeneratedTime(): string
    {
        return date('Y-m-d H:i:s');
    }

    public function getBlockCacheFiles(): array
    {
        return $this->cacheInfo->findCacheFiles('BLOCK');
    }

    /**
     * Get cache key info for this block
     * This is what Magento uses to generate the cache key
     */
    public function getCacheKeyInfo()
    {
        // Override to have predictable cache key
        return [
            $this->getNameInLayout(),
            $this->getTemplateFile(),
            $this->_storeManager->getStore()->getCode()
        ];
    }

    /**
     * Get THIS block's actual cache key
     */
    public function getBlockCacheKey(): string
    {
        return $this->getCacheKey();
    }

    /**
     * Get the actual cache file path for this block
     * This finds the real file Magento created
     */
    public function getActualCacheFilePath(): ?string
    {
        $cacheKey = $this->getBlockCacheKey();
        
        // Magento transforms cache key to filename
        // The cache key is already prefixed with BLOCK_ and hashed
        $searchPattern = strtoupper(str_replace('BLOCK_', 'BLOCK_', $cacheKey));
        
        $cacheDir = $this->cacheInfo->getCacheDir();
        
        // Search for file matching this cache key
        $files = glob($cacheDir . '/mage--*/*' . $searchPattern);
        
        if (!empty($files)) {
            return $files[0];
        }
        
        // Alternative: search by any part of the cache key
        $files = glob($cacheDir . '/mage--*/mage---*BLOCK*');
        foreach ($files as $file) {
            $filename = basename($file);
            if (strpos($filename, $searchPattern) !== false) {
                return $file;
            }
        }
        
        return null;
    }

    /**
     * Get expected cache filename pattern
     */
    public function getCacheFilePattern(): string
    {
        $cacheKey = $this->getBlockCacheKey();
        return "mage---*" . strtoupper($cacheKey);
    }

    /**
     * Check if block_html cache is enabled
     */
    public function isBlockCacheEnabled(): bool
    {
        return $this->cacheState->isEnabled(\Magento\Framework\App\Cache\Type\Block::TYPE_IDENTIFIER);
    }

    public function getCacheInfo(): CacheInfo
    {
        return $this->cacheInfo;
    }
}

