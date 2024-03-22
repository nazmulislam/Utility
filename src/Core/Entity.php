<?php
declare(strict_types=1);
namespace NazmulIslam\Utility\Core;

use NazmulIslam\Utility\Cache\CacheFiles;
use NazmulIslam\Utility\Logger\Logger;

/**
 * Description of Entity
 *
 * @author nazmulislam
 */
abstract class Entity
{
    public $cache;
    public bool $cacheIsEnabled = false;

    public function __construct()
    {
        global $objFilesCache;
        $this->cacheIsEnabled = intval(ENABLE_SQL_CACHE) === 1 ? true : false;
        $objFilesCache = CacheFiles::getCachedData(CACHE_DRIVER);
        $this->cache =  $objFilesCache;
    }

    public function clearCache(string $cacheKey):void
    {
        $this->cache->deleteItem($cacheKey);
    }

    public function clearCacheByTag(array $tags):void
    {
        // foreach($tags as $tag)
        // {
        //     $this->cache->deleteItemsByTag($tag);
        // }
    }

   
}
