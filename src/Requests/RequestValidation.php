<?php

declare(strict_types=1);

namespace NazmulIslam\Utility\Requests;

use Slim\Psr7\Response;

/**
 * Abstract class to for all validation middleware to inherit
 * Class RequestValidation
 * @package NazmulIslam\Utility\Requests
 */
abstract class RequestValidation
{

    /**
     * The classes implementation of validation methods
     * @param \Valitron\Validator $validator
     * @return bool
     */
    abstract protected function validate(\Valitron\Validator &$validator): bool;

    /**
     * returns 422 status with json array of errors
     * @param array $errors
     * @return Response
     */
    protected function returnErrors(array $errors): Response
    {
        $response = new Response(422);
        $response->getBody()->write((string)json_encode($errors));
        $response->withHeader('Content-Type', 'application/json');
        return $response;
    }
}
