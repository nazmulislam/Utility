<?php
declare(strict_types=1);
namespace NazmulIslam\Utility\Cache;

/**
 *
 * @author nazmulislam
 */
interface CacheInterface
{
    public static function getCachedData(string $driver);
}
