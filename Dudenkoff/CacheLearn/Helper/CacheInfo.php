<?php
/**
 * Helper to get cache file information
 */
namespace Dudenkoff\CacheLearn\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class CacheInfo extends AbstractHelper
{
    private $filesystem;
    private $directoryList;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        Filesystem $filesystem,
        DirectoryList $directoryList
    ) {
        parent::__construct($context);
        $this->filesystem = $filesystem;
        $this->directoryList = $directoryList;
    }

    /**
     * Get cache directory path
     */
    public function getCacheDir(): string
    {
        return $this->directoryList->getPath(DirectoryList::CACHE);
    }

    /**
     * Get page cache directory path
     */
    public function getPageCacheDir(): string
    {
        return $this->directoryList->getPath(DirectoryList::CACHE) . '/../page_cache';
    }

    /**
     * Find cache files matching pattern
     */
    public function findCacheFiles(string $pattern): array
    {
        $cacheDir = $this->getCacheDir();
        $command = sprintf('find %s -name "*%s*" -type f 2>/dev/null', escapeshellarg($cacheDir), escapeshellarg($pattern));
        exec($command, $output);
        return $output ?: [];
    }

    /**
     * Find page cache files
     */
    public function findPageCacheFiles(string $pattern = ''): array
    {
        $pageCacheDir = $this->getPageCacheDir();
        $searchPattern = $pattern ? "*{$pattern}*" : "mage---*";
        $command = sprintf('find %s -name %s -type f 2>/dev/null', escapeshellarg($pageCacheDir), escapeshellarg($searchPattern));
        exec($command, $output);
        return $output ?: [];
    }

    /**
     * Get file size
     */
    public function getFileSize(string $filePath): string
    {
        if (!file_exists($filePath)) {
            return 'N/A';
        }
        $bytes = filesize($filePath);
        if ($bytes < 1024) {
            return $bytes . ' B';
        } elseif ($bytes < 1048576) {
            return round($bytes / 1024, 2) . ' KB';
        } else {
            return round($bytes / 1048576, 2) . ' MB';
        }
    }

    /**
     * Get file modification time
     */
    public function getFileModTime(string $filePath): string
    {
        if (!file_exists($filePath)) {
            return 'N/A';
        }
        return date('Y-m-d H:i:s', filemtime($filePath));
    }

    /**
     * Get cache file content preview
     */
    public function getFilePreview(string $filePath, int $lines = 5): array
    {
        if (!file_exists($filePath)) {
            return [];
        }
        $content = file($filePath);
        return array_slice($content, 0, $lines);
    }
}

