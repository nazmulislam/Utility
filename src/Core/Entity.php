<?php
declare(strict_types=1);
namespace NazmulIslam\Utility\Core;

use NazmulIslam\Utility\Cache\CacheFiles;

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
        $objFilesCache = CACHE_DRIVER === 'Files' ? CacheFiles::getCachedDataFromFile() : CacheFiles::getCachedDataFromRedis(host:REDIS_CACHE_HOST,port:REDIS_CACHE_PORT,password:REDIS_CACHE_PASSWORD,database:REDIS_CACHE_DATABASE);
        $this->cache =  $objFilesCache;
    }

    public function clearCache(string $cacheKey):void
    {
        $this->cache->deleteItem($cacheKey);
    }
   
}
