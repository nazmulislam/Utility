<?php

declare(strict_types=1);

namespace NazmulIslam\Utility\Http\Middleware;

use NazmulIslam\Utility\Authentication\AuthenticationJWTToken;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use NazmulIslam\Utility\Logger\Logger;
use NazmulIslam\Utility\Http\Middleware\AuthMiddlewareTrait;

/**
 * Authenticates a logged in user.
 * Class AuthenticationMiddleware
 * @package NazmulIslam\Utility\Http\Middleware
 */
class AuthMiddleware
{
  use ResponseTrait;
  use AuthMiddlewareTrait;
  /**
   * 
   * Checks if the users access token is valid and stores user in request
   * @param Request $request
   * @param RequestHandler $handler
   * @return \Psr\Http\Message\ResponseInterface
   * @throws \Exception
   */
  public function __invoke(Request $request, RequestHandler $handler)
  {

    $response = new Response();
    /**
     * Check refresh Token
     */
    if (!$request->hasHeader('refresh-token')) {

      Logger::debug('TOKEN_DOES_EXIST_IN_HEADER : hasheader', []);
      return self::jsonResponse($response, ['message' => 'refresh token is not present', 'refreshToken' => 'NOT_VALID'], 401);
    }



    try {
      // JWT::decode($request->getHeader('refresh-token')[0], new Key(REFRESH_TOKEN_SECRET, 'HS256'));
    } catch (\Exception $ex) {
      return self::jsonResponse($response, ['message' => 'REFRESH_TOKEN_HAS_EXPIRED', 'refreshToken' => 'NOT_VALID'], 401);
    }


    /**
     * Check Authorisation Access Token
     */
    if (!$request->hasHeader('authorization')) {

      Logger::debug('TOKEN_DOES_EXIST_IN_HEADER : hasheader', []);
      return self::jsonResponse($response, ['message' => 'TOKEN_DOES_EXIST_IN_HEADER'], 401);
    }

    $authorization = $request->getHeader('authorization')[0];
    if (!$authorization) {


      Logger::debug('TOKEN_DOES_EXIST_IN_HEADER : getHeader', []);
      return self::jsonResponse($response, ['error' => 'TOKEN_DOES_EXIST_IN_HEADER'], 401);
    }


    try {

      $explodedAuthorization = explode(" ", $authorization);
      //get payload
      $jwt = isset($explodedAuthorization[1]) ? $explodedAuthorization[1] : null;
      if (empty($jwt)) {
        return self::jsonResponse($response, ['error' => 'ILLEGAL_TOKEN_ISSUER'], 401);
      }
      $payload = AuthenticationJWTToken::decodeJWTToken(token: $jwt, tokenSecret: ACCESS_TOKEN_SECRET);



      if ($this->checkifAllowedOriginUrlIsValid(issuer: $payload->iss, allowedClientUrls: ALLOWED_CLIENT_URLS) === false) {

        return self::jsonResponse($response, ['message' => 'ILLEGAL_TOKEN_ISSUER'], 401);
      }
    } catch (\Exception $ex) {
      Logger::info('IN_VALID_TOKEN', ['message' => $ex->getMessage()]);
      return self::jsonResponse($response, ['message' => $ex->getMessage(), 'accessToken' => 'NOT_VALID', 'refreshToken' => 'IS_VALID'], 401);
    }

    return $handler->handle($request);
  }
}
