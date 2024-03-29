<?php

declare(strict_types=1);

namespace NazmulIslam\Utility\Cache;

use Phpfastcache\CacheManager;
use Phpfastcache\Config\ConfigurationOption;
use Phpfastcache\Drivers\Redis\Config as RedisConfig;

class CacheFiles implements CacheInterface
{

    const CACHE_PATH = '';



    public static function getCachedDataFromFile(string $sqlCacheStorage)
    {

        CacheManager::setDefaultConfig(new ConfigurationOption([
            'path' => __DIR__ . '/../../../../../' . $sqlCacheStorage,
        ]));
        return CacheManager::getInstance('Files');
    }
    public static function getCachedDataFromRedis(string $host, int $port, string $password, int $database = 0)
    {

        $redisConfig = new RedisConfig();
        $redisConfig->setHost($host);
        $redisConfig->setPort($port);
        $redisConfig->setPassword($password);
        $redisConfig->setDatabase((int) $database);
        return CacheManager::getInstance('Redis', $redisConfig);
    }
}
