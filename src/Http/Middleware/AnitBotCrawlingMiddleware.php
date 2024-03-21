<?php
declare(strict_types=1);

namespace NazmulIslam\Utility\Http\Middleware;


use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use NazmulIslam\Utility\Values\SuspectRequestValues;
use Slim\Psr7\Response;
/**
 * Authenticates a logged in user.
 * Class AuthenticationMiddleware
 * @package NazmulIslam\Utility\Http\Middleware
 */
class AnitBotCrawlingMiddleware
{
    use ResponseTrait;
  
    public bool $isSuspectedQueryParam = false;
    public bool $isSuspectedUri = false;
    /**
     * Checks if the users access token is valid and stores user in request
     * @param Request $request
     * @param RequestHandler $handler
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Exception
     */
    public function __invoke(Request $request, RequestHandler $handler)
    {
        $response = new Response();
      
           
           $queryParams = $request->getQueryParams();
           
          
           $this->checkForSuspectQueryParams(queryParams:array_keys($queryParams));
          
           
           if($this->isSuspectedQueryParam === true)
           {
                
                 
                return self::jsonResponse($response, ['suspect' => 'malicious bot'], 403);
           }
           else
           {
               return $handler->handle($request);
           }
          
            
        
        
       return $handler->handle($request);
    }
    

    public function checkForSuspectQueryParams(array $queryParams):void
    {
        
        
            foreach($queryParams as $param)
            {
               if(in_array($param,SuspectRequestValues::SUSPECT_QUERY_PARAMS))
                {
                   
                    $this->isSuspectedQueryParam = true;
                    break;
                }
            }
            
        
        
        
    }
    
    
}
