<?php

declare(strict_types=1);

namespace  NazmulIslam\Utility\Authentication;

use Firebase\JWT\JWT;
/**
 * Class to handle authentication activities
 * Class Authentication
 * @package NazmulIslam\Utility\Domain\Authentication
 */
class AuthenticationToken
{

 
    /**
     * Generates a new access token if refresh token is still valid else
     * if refresh token is invalid then they are effectively logged out.
     */
    public function createExpireToken(string $accessTokenExpiryTime)
    {
        $issuedAt = date_create();


        date_add($issuedAt, date_interval_create_from_date_string($accessTokenExpiryTime));
        return date_timestamp_get($issuedAt);
    }
    /**
     * Creates the access token
     */
    public function createAccessToken(int $expiredAt, string $accessTokenSecret,array $payload): string
    {

        // $issuer =  (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
        // $issuedAt = time();

        // //Time until access token expires 15 minutes
        // //$expiredAt = $issuedAt + ($minutes * $seconds);
        // $payload = [...[
        //     'iss' => $issuer,
        //     'iat' => $issuedAt,
        //     'exp' => $expiredAt,
            
        // ],...$data];

        $jwt =  JWT::encode($payload, $$accessTokenSecret, 'HS256');
        return $jwt;
    }

    /**
     * Creates the refresh token
     */
    public function createRefreshToken(string $refreshTokenSecret, array $payload): string
    {

        // $issuer = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
        // $issuedAt = time();
        // $payload = [

        //     ...[
        //         'iss' => $issuer,
        //     'iat' => $issuedAt,
        //     'exp' => $expiredAt,
        //     'userId' => $this->user->user_id,
        //     'tokenVersion' => $this->user->refresh_token_count,
        //     ], ...$data
        // ];
            
           
        $jwt =  JWT::encode($payload, $refreshTokenSecret, 'HS256');
        return $jwt;
    }

    /**
     * Sets refresh token in httponly cookie.
     */
    public function sendRefreshToken()
    {

    }
}
