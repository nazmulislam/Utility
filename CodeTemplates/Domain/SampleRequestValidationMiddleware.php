<?php

declare(strict_types=1);

namespace NazmulIslam\Utility\Domain\[DomainFolder];

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use NazmulIslam\Utility\Requests\RequestValidation;
use Valitron\Validator;

class [ClassName]RequestValidationMiddleware extends RequestValidation
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
        $validator->rule('required', ['[title_field]']);
        return $validator->validate();
    }
}
