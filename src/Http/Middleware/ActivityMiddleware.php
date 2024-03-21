<?php
namespace NazmulIslam\Utility\Http\Middleware;

use NazmulIslam\Utility\Models\NazmulIslam\Utility\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

/**
 * Authenticates a logged in user.
 * Class AuthenticationMiddleware
 * @package NazmulIslam\Utility\Http\Middleware
 */
class ActivityMiddleware
{
    /**
     * Checks if the users access token is valid and stores user in request
     * @param Request $request
     * @param RequestHandler $handler
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Exception
     */
    public function __invoke(Request $request, RequestHandler $handler)
    {
        //get authorization header
        if(!$request->hasHeader('authorization')) {
            throw new \Exception("not authenticated");
        }
        $authorization = $request->getHeader('authorization')[0];
        if(!$authorization) {
            throw new \Exception("not authenticated");
        }
        //get payload
        //$jwt = explode(" ", $authorization)[1];
         $jwt = isset($_COOKIE['jid']) ? $_COOKIE['jid'] : NULL;
        try {
            //$payload = JWT::decode($jwt, ACCESS_TOKEN_SECRET, array('HS256'));
            
            $payload = JWT::decode($jwt, new Key(REFRESH_TOKEN_SECRET, 'HS256'));
        } catch (\Exception $ex) {
            throw new \Exception("not authenticated");
        }
        $userId = $payload->userId;
        $route = $request->getAttribute('route');
        $user = User::find($userId);
        //Adds the user into the request
        $request = $request->withAttribute('user', $user);
        return $handler->handle($request);
    }
}
