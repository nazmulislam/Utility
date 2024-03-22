<?php

declare(strict_types=1);

namespace NazmulIslam\Utility\Requests;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Valitron\Validator;

class LoginRequestValidation extends RequestValidation
{
    /**
     * Middleware to validate the login request
     * @param Request $request
     * @param RequestHandler $handler
     * @return ResponseInterface
     */
    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface
    {
        $body = $request->getParsedBody();
        
        $validator = new Validator($body);
        if(!$this->validate($validator)) {
            return $this->returnErrors($validator->errors());
        }
        return $handler->handle($request);
    }

    protected function validate(\Valitron\Validator &$validator): bool
    {
        $validator->rule('required', ['password', 'email']);
        $validator->rule('email', 'username');
        return $validator->validate();
    }
}
