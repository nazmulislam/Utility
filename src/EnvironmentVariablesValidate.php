<?php

declare(strict_types=1);

namespace NazmulIslam\Utility;

use NazmulIslam\Utility\Values\EnvironmentVariables as ENV_VARS;

/**
 * Description of EnvironmentVariablesValidate
 *
 * @author nazmulislam
 */
class EnvironmentVariablesValidate {

   
    /**
     * This method checks the variables in the php $_ENV against the Configurations, it highlights missing values in $_ENV
     * @return array
     */
    static public function validateVariablesWithEnv(): array {
        $vars = \array_keys(ENV_VARS::VARS);
        $missingVars = [];
        foreach ($vars as $var) {
            if (!array_key_exists($var, $_ENV) && empty($_ENV[$var]) && ENV_VARS::VARS[$var]['required'] === true) {
                $missingVars[] = $var;
            }
        }
        return $missingVars;
    }

   

    /**
     * Check if a new var has add on local by developer, and not added to environment variable configuration
     */
    static public function checkIfNewVarIsAddedToLocalEnvAndNotInConfiguration(string $path):array
    {
    
        $result = \array_diff(self::getEnvironmentVariablesFromEnvFile($path), self::getEnvironmentVariablesFromConfigurationValues());
 
        return is_array($result) && count($result) > 0 ? $result : [];
    }
    
    static public function checkIfNewVarIsAddedToConfigurationNotInLocalEnvFile(string $path):array
    {
     

        $result = \array_diff(self::getEnvironmentVariablesFromConfigurationValues(),self::getEnvironmentVariablesFromEnvFile($path));
 
        return is_array($result) && count($result) > 0 ? $result : [];
    }

    static private function getEnvironmentVariablesFromEnvFile(string $path):array 
    {
        $varsFromEnvFile = \file($path, FILE_SKIP_EMPTY_LINES|FILE_IGNORE_NEW_LINES);
        $variablesFromEnvFileAsAssociativeArray = self::convertIndexArrayToAssociativeArray($varsFromEnvFile);
        return array_unique(\array_keys($variablesFromEnvFileAsAssociativeArray));
        
    }
    
    static private function getEnvironmentVariablesFromConfigurationValues():array 
    {
        return $varsFromConfigurationAsKeys = \array_unique(\array_keys(ENV_VARS::VARS));
    }
    static private function convertIndexArrayToAssociativeArray(array $array):array
    {
        $data = [];
        foreach ($array as $var) {

            $temp = explode('=', $var);
            if (is_array($temp) && count($temp) === 2) {
                /**
                 * Remove any comments
                 */
                if (!\str_starts_with($temp[0], '#')) {
                    $data[trim($temp[0])] = trim($temp[1]);
                }
            }
        }
        return $data;
    }
}
