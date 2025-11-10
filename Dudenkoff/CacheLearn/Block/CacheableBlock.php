<?php
/**
 * Cacheable Block - HTML will be cached
 */
namespace Dudenkoff\CacheLearn\Block;

use Dudenkoff\CacheLearn\Helper\CacheInfo;
use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\Escaper;
use Magento\Framework\View\Element\Template;

class CacheableBlock extends Template
{
    private const CACHE_INFO_PLACEHOLDER = '<!--CACHE_FILE_INFO_PLACEHOLDER-->';

    private $cacheInfo;
    private $cacheState;
    private $cacheJustSaved = false;
    private $escaper;

    public function __construct(
        Template\Context $context,
        CacheInfo $cacheInfo,
        StateInterface $cacheState,
        Escaper $escaper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->cacheInfo = $cacheInfo;
        $this->cacheState = $cacheState;
        $this->escaper = $escaper;
    }

    public function getGeneratedTime(): string
    {
        return date('Y-m-d H:i:s');
    }

    /**
     * Ensure the rendered HTML contains live cache metadata even on the first request.
     */
    public function toHtml()
    {
        $this->cacheJustSaved = false;
        $html = parent::toHtml();

        $replacement = $this->buildCacheFileInfoHtml();
        $updatedHtml = str_replace(self::CACHE_INFO_PLACEHOLDER, $replacement, $html);

        if ($updatedHtml !== $html && $this->cacheJustSaved) {
            $this->cacheJustSaved = false;
            $this->_saveCache($updatedHtml);
        }

        return $updatedHtml;
    }

    /**
     * Tracks whether Magento saved the block output during this request.
     *
     * @param string $data
     * @return void
     */
    protected function _saveCache($data)
    {
        $this->cacheJustSaved = true;
        parent::_saveCache($data);
    }

    /**
     * Returns a marker that will later be replaced with live cache metadata.
     */
    public function renderCacheFileInfoPlaceholder(): string
    {
        return self::CACHE_INFO_PLACEHOLDER;
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

    /**
     * Build HTML snippet describing the cache file state.
     */
    private function buildCacheFileInfoHtml(): string
    {
        $cacheKey = $this->getBlockCacheKey();
        $cacheInfo = $this->getCacheInfo();
        $actualPath = $this->getActualCacheFilePath();

        if (!$actualPath || !file_exists($actualPath)) {
            return '<div>Cache file not available yet.</div>';
        }

        return '<div>Full Path: ' . $this->escaper->escapeHtml($actualPath) . '</div>';
    }
}

