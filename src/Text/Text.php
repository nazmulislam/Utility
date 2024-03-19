<?php
declare(strict_types=1);
namespace NazmulIslam\Utility\Text;


class Text
{
    public static string $pascalString;
    public static string $snakeCaseString;
    public static string $hyphenCaseString;
     
     
    public static function pascalCase(string $textString):string
    {
        self::$pascalString = str_replace("-","",ucfirst($textString));
        self::$pascalString = str_replace("_","", self::$pascalString);
        self::$pascalString = str_replace(" ","",self::$pascalString);
        return self::$pascalString;
    }
    
    public static function snakeCase(string $textString):string
    {
        self::$snakeCaseString = $textString;
        if(str_contains(self::$snakeCaseString,"-"))
        {
            self::$snakeCaseString = str_replace('-', '_', self::$snakeCaseString);
        }
        
        $parts = preg_split('/(?=[A-Z])/', self::$snakeCaseString );
        self::$snakeCaseString  = implode('_', $parts);
        
        if("_" === substr(self::$snakeCaseString , 0,1))
        {
           self::$snakeCaseString  = strtolower(substr(self::$snakeCaseString , 1));
        }
        
        return strtolower(self::$snakeCaseString );
    }
    
    public static function camelCase(string $textString):string
    {
     
        
        
        return lcFirst($textString);
    }
    
    public static function hyphenCase(string $textString):string
    {
     
        self::$hyphenCaseString = self::snakeCase($textString);
        
        if(str_contains(self::$hyphenCaseString,"_"))
        {
            self::$hyphenCaseString = str_replace('_', '-', self::$hyphenCaseString);
        }
        
        return strtolower(self::$hyphenCaseString);
    }

}
