<?php

declare(strict_types=1);

namespace  NazmulIslam\Utility\Cookie;


class Cookie
{

    public function setCookie(string $cookie, int|string $value, bool $httponly = true,bool $secure=true, string $sameSite = 'None'): void
    {
        setcookie($cookie, $value, [
            'httponly' => $httponly,
            'secure' => $secure,
            'SameSite' => $sameSite
        ]);
    }
    public function clearCookie(string $key, bool $httponly = true,bool $secure=true, string $sameSite = 'None'): void
    {
        setcookie($key, '', [
            'httponly' => $httponly,
            'secure' => $secure,
            'SameSite' => $sameSite
        ]);
    }
 
}
