<?php
declare(strict_types=1);

namespace NazmulIslam\Utility\Http\Middleware;


use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
/**
 * Authenticates a logged in user.
 * Class AuthenticationMiddleware
 * @package NazmulIslam\Utility\Http\Middleware
 */
class SqlInjectionMiddleware
{
    use ResponseTrait;
    protected $inputs = [];
    protected $suspect = null; 

    public $options = [
                            'log'    => true,
                            'unset'  => true,
                            'exit'   => true,
                            'errMsg' => 'Not allowed',
                        ];
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
       
           $body = $request->getParsedBody();
        $queryParams = $request->getQueryParams();
        $inputs = array_merge($body ?? [], $queryParams ?? []);
           $this->inputs = (isset($inputs) && is_array($inputs)) ? $inputs : [];
           $this->detect();
           if($this->suspect)
           {
               return self::jsonResponse($response, ['SQLMiddlewareError' => 'ILLEGAL_TOKEN_ISSUER'], 401);
           }
        
        
       return $handler->handle($request);
    }
    
    public function detect() {
        $result = self::parseQuery();
        if ($result) {
            if ($this->options['log']) {
                    self::logQuery();
            }
        }
    }

    

    private function parseQuery()
    {
        $operators = array(
//            'select * ',
//            'select ',
            'union all ',
            'union ',
//            ' all ',
            ' where ',
            ' and 1 ',
//            ' and ',
//            ' or ',
            ' 1=1 ',
            ' 2=2 ',
            ' -- ',
//            'select *',
//            'select',
            'union all',
            'union',
//            'all',
            'where',
            'and 1',
//            'and',
//            'or',
            '1=1',
            '1 = 1',
            '1 =1',
            '1= 1',
            '2=2',
            '2 = 2',
            '2 =2',
            '2= 2',
            '--',
        );

        foreach($this->inputs as $key => $val)
        {
            $k = (isset($key) && is_string($key)) ? urldecode(strtolower($key)) : '';
            foreach($operators as $operator)
            {
                if (preg_match("/".$operator."/i", $k)) {
                    $this->suspect = "operator: '".$operator."', key: '".$k."'";
                    return true;
                }
                if(isset($val) && is_array($val)) {
                    foreach ($val as $subValue) {
                        $subValue = (isset($subValue) && is_string($subValue)) ? urldecode(strtolower($subValue)) : '';
                        if (preg_match("/".$operator."/i", $subValue)) {
                            $this->suspect = "operator: '".$operator."', val: '".$subValue."'";
                            return true;
                        }
                    }
                } else {
                     $subValue = (isset($val) && is_string($val)) ? urldecode(strtolower($val)) : '';
                     if (preg_match("/".$operator."/i", $subValue)) {
                        $this->suspect = "operator: '".$operator."', val: '".$subValue."'";
                        return true;
                    }
                }
            }
        }
    }
    
    private function checkSuspect($val,$operator):bool
    {
        if(isset($val) && is_array($val)) {
            foreach ($val as $subValue) {
                if(isset($subValue) && is_string($subValue)) {
                    $subValue = (isset($subValue) && is_string($subValue)) ? urldecode(strtolower($subValue)) : '';
                    if (preg_match("/".$operator."/i", $subValue)) {
                        $this->suspect = "operator: '".$operator."', val: '".$subValue."'";
                        return true;
                    }
                } else if(isset($subValue) && is_array($subValue)) {
                    self::checkSuspect($subValue,$operator);
                } 
            }
        } 
        /**
         * @TODO refactor the sub value is missing
         */
        // else if(isset($subValue) && is_string($subValue)) {
        //     $subValue = (isset($subValue) && is_string($subValue)) ? urldecode(strtolower($subValue)) : '';
        //     if (preg_match("/".$operator."/i", $subValue)) {
        //         $this->suspect = "operator: '".$operator."', val: '".$subValue."'";
        //         return true;
        //     }
        // }

        return false;
    }
    
    private function logQuery()
    {
        $data  = date('d-m-Y H:i:s') . ' - ';
        $data .= $_SERVER['REMOTE_ADDR'] . ' - ';
        $data .= 'Suspect: ['.(isset($this->suspect) ? $this->suspect :'').'] ';
        $data .= json_encode($_SERVER);
        $path = __DIR__.'/../../../'.$_ENV['SQL_INJECTION_LOG_PATH'];
        file_put_contents($path, $data . PHP_EOL, FILE_APPEND);
    }
}
