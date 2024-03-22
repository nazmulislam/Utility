<?php

declare(strict_types=1);

namespace NazmulIslam\Utility\Whoops;

class WhoopsHandler
{
public $whoops;

   public function __construct()
   {
        $this->whoops =  new \Whoops\Run();
   }

   public static function jsonResponseHandler()
   {
        return new \Whoops\Handler\JsonResponseHandler();
   }
}
