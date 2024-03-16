<?php
declare(strict_types=1);
namespace NazmulIslam\Utility;
/**
 * Description of Utility
 *
 * @author nazmulislam
 */
class Debug
{
    
    public static function getScriptMemoryUsage(): string
    {
        return "Script memory usage: ".Utility::formatBytes(memory_get_usage());
    }

    public static function getScriptPeakMemoryUsage(): string
    {
        return "Script peak memory usage: ".Utility::formatBytes(memory_get_peak_usage());
    }
    
    public static function getAllVarsInMemory():array
    {
        //return  get_defined_vars();
         echo "<pre>",print_r(get_defined_vars());die;
    }

    public static function getVarByKeyInMemory(string $key):array
    {
        $memory = get_defined_vars();
        if(array_key_exists($key, $memory))
        {
            echo "<pre>",print_r($memory[$key]);die;
        }
        echo "<pre>",print_r($memory[$key]);die;
    }

}