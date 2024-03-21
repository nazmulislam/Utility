<?php

declare(strict_types=1);

namespace NazmulIslam\Utility\Core;

use Psr\Http\Message\ResponseInterface;
use Psr\Container\ContainerInterface;

class Controller
{
    protected $container;
    public function __construct(ContainerInterface $container){
       $this->container = $container;
   }
    protected static function jsonResponse(ResponseInterface $response, $data, int $status = 200): ResponseInterface 
    {

        if (is_array($data))
        {
            $response->getBody()->write((string)json_encode($data));
        }
        else
        {
            $response->getBody()->write((string)$data);
        }
        
        $response->withHeader('Content-Type', 'application/json')->withStatus($status);
        return $response;
    }
}