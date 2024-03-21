<?php
declare(strict_types=1);
/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace NazmulIslam\Utility\Core;

/**
 * Description of EnvironmentVariables
 *
 * @author nazmulislam
 */
class EnvironmentVariables 
{
   static  public function  getEnv(string $key)
   {
        return $_ENV[$key];
   }

   
}
