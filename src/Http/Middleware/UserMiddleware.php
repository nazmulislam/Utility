<?php

declare(strict_types=1);

namespace NazmulIslam\Utility\Http\Middleware;

use NazmulIslam\Utility\Domain\User\UserRepository;
use NazmulIslam\Utility\Domain\User\UserService;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

/**
 * Authenticates a logged in user.
 * Class AuthenticationMiddleware
 * @package NazmulIslam\Utility\Http\Middleware
 */
class UserMiddleware
{

    use ResponseTrait;
    public UserService $userService;
    public UserRepository $userRepository;

    /**
     * This middle is only to get the userId from the middle if it exists and make it availble to the request as an attribute. it mainly for public routes.
     * @param Request $request
     * @param RequestHandler $handler
     * @return type
     */
    public function __invoke(Request $request, RequestHandler $handler)
    {

        $response = new Response();
        $this->userService = new UserService();
        $this->userRepository = new UserRepository();
        if ($request->hasHeader('authorization')) {
            $authorization = $request->getHeader('authorization')[0];
            if ($authorization) {

                //get payload
                //$jwt = explode(" ", $authorization)[1];
                $jwt = isset($_COOKIE['jid']) ? $_COOKIE['jid'] : NULL;
                try {
                    //$payload = JWT::decode($jwt, ACCESS_TOKEN_SECRET, array('HS256'));
                    $payload = JWT::decode($jwt, new Key(REFRESH_TOKEN_SECRET, 'HS256'));

                    $allowedOriginUrls = explode(',', ALLOWED_CLIENT_URLS);

                    if (!in_array($payload->iss, $allowedOriginUrls)) {
                        // \NazmulIslam\Utility\Utility\Logger\Logger::debug('not valid issuer', [$payload->iss, 'allowed_urls' => ALLOWED_CLIENT_URLS]);
                        // \NazmulIslam\Utility\Utility\Logger\Logger::debug('ILLEGAL_TOKEN_ISSUER', []);

                        return self::jsonResponse($response, ['error' => 'ILLEGAL_TOKEN_ISSUER'], 401);
                    }
                } catch (\Exception $ex) {
                    /**
                     *  the JWT obkect throws an exception if token has expired
                     */
                    if ($ex->getMessage()) {

                        return self::jsonResponse($response, ['error' => $ex->getMessage()], 401);
                    }
                }

                $fields = ['user.id', 'user.username', 'user.user_meta_data', 'user.first_name', 'user.last_name'];
                $user = $this->userService->getUserByGuid(guid: $payload->userGuid, fields: $fields, userRepository: $this->userRepository);


                if (!empty($user) && isset($user)) {

                    $request = $request->withAttribute('user', $user);
                }
            }
        }



        return $handler->handle($request);
    }
}
