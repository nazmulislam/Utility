<?php
declare(strict_types=1);
namespace NazmulIslam\Utility\HTMLParser;

/**
 * Description of HTMLParser
 *
 * @author nazmulislam
 */
class HTMLParser
{

    static function parseHtmlPage(string $url)
    {
        // Fetch remote html
        $contents = file_get_contents($url);

        // Get rid of style, script etc
        $search = array('@<script[^>]*?>.*?</script>@si', // Strip out javascript
            '@<head>.*?</head>@siU', // Lose the head section
            '@<style[^>]*?>.*?</style>@siU', // Strip style tags properly
            '@<![\s\S]*?--[ \t\n\r]*>@'         // Strip multi-line comments including CDATA
        );

        $contents = preg_replace($search, '', $contents);

        $result = array_count_values(
                str_word_count(
                        strip_tags($contents), 1
                )
        );
    }

    static function htmlPageToString(string $url): string
    {
        $contents ='';
        // Fetch remote html
        // Create a curl handle to a non-existing location
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_NOSIGNAL, 1); // Required to fix bug
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
        curl_setopt($ch, CURLOPT_TIMEOUT, 8); //timeout in seconds
        
        $contents = curl_exec($ch);
        $info = curl_getinfo($ch);
       
        if ($contents === false)
        {
            
            return '';
        }
        else
        {
             
           
           
            // Close handle
            
            // Get rid of style, script etc
            $search = array('@<script[^>]*?>.*?</script>@si', // Strip out javascript
                '@<head>.*?</head>@siU', // Lose the head section
                '@<style[^>]*?>.*?</style>@siU', // Strip style tags properly
                '@<![\s\S]*?--[ \t\n\r]*>@'         // Strip multi-line comments including CDATA
            );

            $contents = preg_replace($search, '', strtolower($contents));

            
            return strip_tags($contents);
        }
    }

    static public function getAndCountWordFrquencyOfString(string $string): array
    {
        return array_count_values(
                str_word_count(
                        $string, 1
                )
        );
    }

    static public function stringToWords(string $string): array
    {
        return str_word_count($string, 1);
    }

    static public function stringToWordsWithCount(string $string): array
    {
        return array_count_values(
                str_word_count(
                        $string, 1
                )
        );
    }

}
