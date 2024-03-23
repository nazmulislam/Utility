<?php

declare(strict_types=1);

namespace  NazmulIslam\Utility\Authentication;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthenticationJWTToken
{
    static public function createJWTTokenExpiryDate(string $tokenExpiryTime)
    {
        $issuedAt = date_create();
        date_add($issuedAt, date_interval_create_from_date_string($tokenExpiryTime));
        return date_timestamp_get($issuedAt);
    }


    static public function createJWTToken(string $tokenSecret, array $payload): string
    {

        $jwt =  JWT::encode($payload, $tokenSecret, 'HS256');
        return $jwt;
    }

    static public function decodeJWTToken(string $token, string $tokenSecret)
    {

        return JWT::decode($token, new Key($tokenSecret, 'HS256'));
    }
}
