<?php
declare(strict_types=1);
namespace NazmulIslam\Utility\Core\Cache;
use Phpfastcache\CacheManager;
use Phpfastcache\Config\ConfigurationOption;

class CacheFiles implements CacheInterface
{
    CONST CACHE_PATH = '';

    public static function getCachedData()
    {
     
        return ;
        
        CacheManager::setDefaultConfig(new ConfigurationOption([
            'path' => __DIR__.'/../../../'.SQL_CACHE_STORAGE]));


        return  CacheManager::getInstance('files');
    }
    
    static function removeDirectory($path) :void
    {

	$files = glob($path . '/*');
	foreach ($files as $file) {
		is_dir($file) ? self::removeDirectory($file) : unlink($file);
	}
	rmdir($path);
    }
    
}