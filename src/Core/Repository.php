<?php
declare(strict_types=1);
namespace NazmulIslam\Utility\Core;
/**
 * Description of Repository
 *
 * @author nazmulislam
 */
abstract class Repository
{
    public $cache;

    public function __construct()
    {
        global $objFilesCache;
        $this->cache =  $objFilesCache;
    }

    public function clearCache(string $cacheKey):void
    {
        if($this->cache->hasItem($cacheKey))
        {
            $this->cache->deleteItem($cacheKey);
        }
        
    }
}
