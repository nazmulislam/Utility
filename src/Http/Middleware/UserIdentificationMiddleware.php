<?php

declare(strict_types=1);

namespace NazmulIslam\Utility\Http\Middleware;

use NazmulIslam\Utility\Domain\User\UserRepository;
use NazmulIslam\Utility\Domain\User\UserService;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;


/**
 * Authenticates a logged in user.
 * Class AuthenticationMiddleware
 * @package NazmulIslam\Utility\Http\Middleware
 */
class UserIdentificationMiddleware
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

        $this->userService = new UserService();
        $this->userRepository = new UserRepository();
        $refreshTokenHeader = $request->getHeader('refresh-token');

        if (!empty($refreshTokenHeader) && !empty($refreshTokenHeader[0])) {

            $payload = JWT::decode($refreshTokenHeader[0], new Key(REFRESH_TOKEN_SECRET, 'HS256'));

            if (isset($payload->userGuid)) {
                $user = $this->userService->getUserByGuid(guid: $payload->userGuid, fields: ['user.user_id', 'user.user_guid', 'user.username',  'user.first_name', 'user.last_name'], userRepository: $this->userRepository);

                if (!empty($user) && isset($user)) {
                    $GLOBALS['user'] = $user;
                    $GLOBALS['user_fullname'] = (isset($user->first_name) ? ucfirst($user->first_name) : '') . ' ' . (isset($user->last_name) ? ucfirst($user->last_name) : '');
                    $request = $request->withAttribute('user', $user);
                }
            }
        }




        return $handler->handle($request);
    }
}
