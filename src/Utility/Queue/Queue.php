<?php
declare(strict_types=1);
namespace NazmulIslam\Utility\Queue;

use NazmulIslam\Utility\Logger\Logger;

/**
 * Description of QueueController
 *
 * @author nazmulislam
 */
class Queue
{

     static function addToQueue(array $args,string $queue,string $class,string $host=null,$port=null,int $database = 0)
    {
        try
        {
           
            if(!array_key_exists('httpHost', $args))
            {
                if(isset($_SERVER['HTTP_HOST']) && strtolower(php_sapi_name()) !== 'cli')
                {
                    $args['httpHost'] = $_SERVER['HTTP_HOST'];
                }
            }
            if(!array_key_exists('httpScheme', $args))
            {
                if(isset($_SERVER['REQUEST_SCHEME']) && strtolower(php_sapi_name()) !== 'cli')
                {
                    $args['httpScheme'] = $_SERVER['REQUEST_SCHEME'];
                }
            }
            if(!array_key_exists('tenant', $args) && strtolower(php_sapi_name()) !== 'cli')
            {
                if(isset($GLOBALS['TENANT']))
                {
                    $args['tenant'] = $GLOBALS['TENANT'];
                }
                else 
                {
                            $headers = \getallheaders();
                            if(!isset($headers['X-Tenant']) || empty($headers['X-Tenant']))
                            {
                                throw new \Exception('Header X-Tenant is set in web request');
                            }
                            
                             $args['tenant'] = $headers['X-Tenant'] ?? null;
                }
            }
            else if(!array_key_exists('tenant', $args) && strtolower(php_sapi_name()) === 'cli')
            {
                throw new \Exception('tenant is not set in args for queue set in CLI');
                 \Resque_Event::trigger('onFailure', ['tenant is not set in args for queue set in CLI']);
            }
            $pass = $_ENV['REDIS_PASSWORD'];
            
            if(!isset($pass) || empty($pass))
            {

                throw new \Exception('REDIS ENVIRONMENT PASSWORD IS NOT SET');

            }
            
            if(empty($class))
            {
                throw new \RuntimeException('Parameter $jobController cannot be empty');
            }
            if(empty($host))
            {
                $host = $_ENV['REDIS_HOST'];
            }
            if(empty($port))
            {
                $port = $_ENV['REDIS_PORT'];
            }
            $server = !empty($host) ? $host : $pass;

            \Resque::setBackend(server:"redis://ignored:{$pass}@{$server}:{$port}",database:$database);

            
            return \Resque::enqueue(queue:$queue,class:$class,args:$args,trackStatus:true,prefix:'');

        }
        catch (\Exception $ex)
        {
           Logger::error($ex->getMessage(),$ex->getTrace());
            //echo $ex->getMessage();
            //echo $ex->getTraceAsString();

        }

    }

     static function addBackgroundAndNotificationData($creatorId, array $data,array $actionsAfterService = [], array $notificationObjects = []) : array
    {
        if(isset($actionsAfterService) && isset($actionsAfterService['notification']))
        {
            $options  = [
                        'user_ids' => [$creatorId],
                        'channels' => [],
                    ];
            $addtionalOptions = $actionsAfterService['notification'];
            /**
             * Covert message variables to actual dynamic values.
             */
            if(isset($addtionalOptions['message']) && !empty($addtionalOptions['message']) && isset($notificationObjects) && count($notificationObjects) > 0)
            {
                foreach($notificationObjects as $key => $value) {
                    //don not use replace function for keys containing array
                    if(isset($value) && $value!=NULL && is_string($value)){
                        $addtionalOptions['message'] = str_replace('{{'.$key.'}}', $value,$addtionalOptions['message']);
                    }
                }
            }
            $optionsFinal = array_merge($options,$addtionalOptions);
            $data['notification'] =  $optionsFinal;
        }
        
        $data['creator_id'] = $creatorId;
        return $data;
    }

    static function getTenantName()
    {

        // extract username
        if(!empty($_SERVER['HTTP_HOST']))
        {
            $hostInfo = explode('.', $_SERVER['HTTP_HOST']);
            return array_shift($hostInfo);
        }

    }
}
