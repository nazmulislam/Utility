<?php

declare(strict_types=1);

namespace NazmulIslam\Utility\Http\Middleware;

use NazmulIslam\Utility\Logger\Logger;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Routing\RouteContext;
use Slim\Psr7\Response;
use NazmulIslam\Utility\Http\Middleware\AuthMiddlewareTrait;


class CorsMiddleware
{

    use ResponseTrait;
    use AuthMiddlewareTrait;
    public function __invoke(Request $request, RequestHandler $handler)
    {

        $routeContext = RouteContext::fromRequest($request);
        $routingResults = $routeContext->getRoutingResults();

        $headers = $request->getHeaders();

        if (strtolower(CORS_ALLOW_ALL) === 'false') {
            $remoteUrl = isset($headers['Origin'][0]) ? trim($headers['Origin'][0]) : trim($headers['Host'][0]);



            if ($this->checkifAllowedOriginUrlIsValid($remoteUrl, ALLOWED_CLIENT_URLS) === false) {
                $response = new Response();
                Logger::debug($_SERVER['REMOTE_ADDR'] ?? ' ' . 'notInAllowedList', [$remoteUrl]);
                exit(0);
            }
        } else {
            $remoteUrl = '*';
        }


        // Access-Control headers are received during OPTIONS requests
        //Returns the origin header for options request
        if ($request->getMethod() == 'OPTIONS') {

            $requestHeaders = $request->getHeaderLine('Access-Control-Request-Headers');

            header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, OPTIONS, DELETE");
            header('Access-Control-Allow-Headers: ' . $requestHeaders);
            header('Access-Control-Allow-Origin: ' . $remoteUrl);
            header('Access-Control-Allow-Credentials: true');
            exit(0);
        }

        $methods = $routingResults->getAllowedMethods();
        $requestHeaders = $request->getHeaderLine('Access-Control-Request-Headers');

        $response = $handler->handle($request);

        $response = $response->withHeader('Access-Control-Allow-Methods', implode(',', $methods));
        $response = $response->withHeader('Access-Control-Allow-Headers', $requestHeaders);
        $response = $response->withHeader('Access-Control-Allow-Origin', $remoteUrl);
        $response = $response->withHeader('Content-Type', 'application/json');

        //$response = $response->withHeader('Access-Control-Expose-Headers', ['tag, link, X-RateLimit-Limit, X-RateLimit-Remaining, X-RateLimit-Reset, X-OAuth-Scopes, X-Accepted-OAuth-Scopes']);
        // Optional: Allow Ajax CORS requests with Authorization header
        $response = $response->withHeader('Access-Control-Allow-Credentials', 'true');

        return $response;
    }
}
