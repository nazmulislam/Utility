<?php

declare(strict_types=1);

namespace NazmulIslam\Utility\Domain\[DomainFolder];

use NazmulIslam\Utility\Core\Controller;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use NazmulIslam\Utility\Domain\[DomainFolder]\[ClassName]Service;
use NazmulIslam\Utility\Domain\[DomainFolder]\[ClassName]Repository;

class [ClassName]Controller extends Controller
{
    public function create(Request $request, Response $response, [ClassName]Service $[instanceName]Service, [ClassName]Repository $[instanceName]Repository): Response
    {
        $input = $request->getParsedBody();
        $return = $[instanceName]Service->create(input: $input,[instanceName]Repository: $[instanceName]Repository);
        return $this->jsonResponse(response: $response, data: $return, status: 200);
    }

    public function delete(Response $response, [ClassName]Service $[instanceName]Service, int $[parameterId], [ClassName]Repository $[instanceName]Repository): Response
    {
        $return = $[instanceName]Service->delete([parameterId]: $[parameterId], [instanceName]Repository: $[instanceName]Repository);
        return self::jsonResponse(response: $response, data: $return, status: 200);
    }


    public function update(Request $request, Response $response, [ClassName]Service $[instanceName]Service, int $[parameterId], [ClassName]Repository $[instanceName]Repository)
    {
        $input = $request->getParsedBody();
        $return = $[instanceName]Service->update(input: $input, [parameterId]: $[parameterId], [instanceName]Repository: $[instanceName]Repository);
        return self::jsonResponse(response: $response, data: $return, status: 200);
    }

    public function getListPaginated(Request $request, Response $response, [ClassName]Service $[instanceName]Service, [ClassName]Repository $[instanceName]Repository): Response
    {
        $body = $request->getParsedBody();
        $queryParams = $request->getQueryParams();
        $input = array_merge($body ?? [], $queryParams ?? []);

        $data = $[instanceName]Service->getListPaginated(input: $input, [instanceName]Repository: $[instanceName]Repository);
        return $this->jsonResponse(response: $response, data: $data, status: 200);
    }
    
    public function get[ModelName]ById(int $[parameterId], Response $response, [ClassName]Service $[instanceName]Service, [ClassName]Repository $[instanceName]Repository): Response
    {
        $data = $[instanceName]Service->get[ModelName]ById([parameterId]:$[parameterId], [instanceName]Repository: $[instanceName]Repository);
        return $this->jsonResponse(response: $response, data: $data, status: 200);
    }
    

    
}
