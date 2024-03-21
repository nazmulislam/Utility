<?php
declare(strict_types=1);
use NazmulIslam\Utility\Http\Middleware\PaginationRequestValidationMiddleware;
use NazmulIslam\Utility\Http\Middleware\AuthMiddleware;
use NazmulIslam\Utility\Domain\[DomainFolder]\[ClassName]Controller;
use NazmulIslam\Utility\Domain\[DomainFolder]\[ClassName]RequestValidationMiddleware;


$app->group('/[RouteGroup]', function ($app) {
    
    $app->post('/', [[ClassName]Controller::class, 'create'])->add(new [ClassName]RequestValidationMiddleware());
    $app->put('/{[parameterId]}', [[ClassName]Controller::class, 'update'])->add(new [ClassName]RequestValidationMiddleware());
    $app->get('/{[parameterId]}', [[ClassName]Controller::class, 'get[ModelName]ById']);
    $app->delete('/{[parameterId]}', [[ClassName]Controller::class,'delete']);
    $app->post('/list/paginated', [[ClassName]Controller::class, 'getListPaginated'])->add(new PaginationRequestValidationMiddleware());
    $app->get('/list/paginated', [[ClassName]Controller::class, 'getListPaginated'])->add(new PaginationRequestValidationMiddleware());

})->add(new AuthMiddleware());
