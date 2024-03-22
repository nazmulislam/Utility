<?php

declare(strict_types=1);

namespace App\Utility\Email;

/**
 * Description of Utility
 *
 * @author nazmulislam
 */
class EmailTemplate
{
   
    /**
     *
     * @param string $file
     * @param array $data
     * @return string
     * @throws \Exception
     */


    public static function emailTemplate(string $file, array $data = []): string
    {
        if (empty($file)) {
            throw new \Exception('Param $path cannot be empty');
        }

        if (strpos($file, '.php') === FALSE) {
            $file = $file . '.php';
        }

        if (!\file_exists(__DIR__ . '/../../../../Templates/Email/' . $file)) {
            throw new \Exception('The Template Path: ' . $file . ' does not exist');
        }
        \ob_start();
        include(__DIR__ . '/../../../../Templates/Email/' . $file);

        return ob_get_clean();
    }

  

    static public function getEmailContentMessageForIdentifier(string $identifier, array $emailObjects): string
    {
        $EmailTemplate = \App\Models\App\EmailTemplate::select('id', 'template')->where('identifier', $identifier)->first();
        if (isset($EmailTemplate) && !empty($EmailTemplate)) {
            /**
             * Covert message variables to actual dynamic values.
             */
            $template = isset($EmailTemplate->template) ? $EmailTemplate->template : '';
            if (isset($template) && !empty($template) && isset($emailObjects) && count($emailObjects) > 0) {
                foreach ($emailObjects as $key => $value) {
                    //do not use replace function for keys containing array
                    if (isset($value) && $value != NULL && is_string($value)) {
                        $template = str_replace('{{' . $key . '}}', $value, $template);
                    }
                }
                return $template;
            }
        }
        return '';
    }

    public static function getAllKeysFromEmail(string $body, array $replacements)
    {
        if(isset($body) && $body !== ""){

            $result = preg_replace_callback('/\[(.*?)\]/', function ($matches) use ($replacements) {
                $word = $matches[1];
    
                // Check if a replacement exists for the word
                if (array_key_exists($word, $replacements)) {
                    // return '<'.$replacements[$word].'>';
                    return $replacements[$word];
                }
    
                // If no replacement found, keep the original word
                return $word;
            }, $body);
    
            $text = nl2br($result);
    
            return $text;
        } else {
            return $body;
        }
    }
}
