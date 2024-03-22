<?php

declare(strict_types=1);

namespace NazmulIslam\Utility\Cache;

/**
 *
 * @author nazmulislam
 */
interface CacheInterface
{

    public static function getCachedDataFromFile();
    public static function getCachedDataFromRedis(string $host, int $port, string $password, int $database = 0);
}
