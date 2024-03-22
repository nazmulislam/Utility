<?php
declare(strict_types=1);


namespace NazmulIslam\Utility\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Valitron\Validator;
use NazmulIslam\Utility\Requests\RequestValidation;

class PaginationRequestValidationMiddleware extends RequestValidation
{
    /**
     * Middleware to validate the login request
     * @param Request $request
     * @param RequestHandler $handler
     * @return ResponseInterface
     */
    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface
    {
        $body = $request->getParsedBody() ?? [];
        $queryParams = $request->getQueryParams() ?? [];
        $input = array_merge($body,$queryParams);
        $validator = new Validator($input);
        if(!$this->validate($validator)) {
            $errors = ["status"=>false,"error"=>$validator->errors()];
            \NazmulIslam\Utility\Logger\Logger::error('Pagination valaidtion error',['uri'=>$request->getUri(),'inputs'=>$input,'validation_error'=>$errors]);
            return $this->returnErrors($errors);
        }
        return $handler->handle($request);
    }

    protected function validate(\Valitron\Validator &$validator): bool
    {
        $rules = [
            'required' => ['page','per_page','sort'],
            
            'numeric' =>  ['page','per_page'],
           
        ];
       $validator->rules($rules);
       return $validator->validate();

    }
}
