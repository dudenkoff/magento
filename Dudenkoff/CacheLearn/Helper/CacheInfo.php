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

}

