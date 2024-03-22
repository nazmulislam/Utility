<?php

declare(strict_types=1);

namespace NazmulIslam\Utility\Cache;

use Phpfastcache\CacheManager;
use Phpfastcache\Config\ConfigurationOption;
use Phpfastcache\Drivers\Redis\Config as RedisConfig;

class CacheFiles implements CacheInterface
{

    const CACHE_PATH = '';



    public static function getCachedDataFromFile()
    {

        CacheManager::setDefaultConfig(new ConfigurationOption([
            'path' => __DIR__ . '/../../../../../' . $_ENV['SQL_CACHE_STORAGE'],
        ]));
        return CacheManager::getInstance('Files');
    }
    public static function getCachedDataFromRedis(string $host, int $port, string $password, int $database = 0)
    {

        $redisConfig = new RedisConfig();
        $redisConfig->setHost((string) $_ENV['REDIS_CACHE_HOST']);
        $redisConfig->setPort((int) $_ENV['REDIS_CACHE_PORT']);
        $redisConfig->setPassword((string) $_ENV['REDIS_CACHE_PASSWORD']);
        $redisConfig->setDatabase((int) $_ENV['REDIS_CACHE_DATABASE']);
        return CacheManager::getInstance('Redis', $redisConfig);
    }
}
