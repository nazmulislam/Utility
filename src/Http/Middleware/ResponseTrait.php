<?php

declare(strict_types=1);
namespace NazmulIslam\Utility\Http\Middleware;
use Slim\Psr7\Response;

/**
 *
 * @author nazmulislam
 */
trait ResponseTrait
{
   protected static function jsonResponse(Response $response, $data, int $status = 200): Response {
       if(is_array($data))
       {
           $response->getBody()->write((string)json_encode($data));
       }
       else
       {
           $response->getBody()->write((string)$data);
       }

        $response = $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
        return $response;
    }
}
