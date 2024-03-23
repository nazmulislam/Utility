<?php

declare(strict_types=1);

namespace NazmulIslam\Utility\Http\Middleware;
trait AuthMiddlewareTrait
{
    private function checkifAllowedOriginUrlIsValid(string $issuer, string $allowedClientUrls): bool
    {
        $allowedOriginUrls = explode(',', $allowedClientUrls);

        if (!in_array($issuer, $allowedOriginUrls)) {
            return false;
        }

        return true;
    }
}
