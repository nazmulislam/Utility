<?php

declare(strict_types=1);

namespace NazmulIslam\Utility\GUID;

/**
 * Description of Utility
 *
 * @author nazmulislam
 */
class GUID
{



    static public function createGuidForTableIds($model, $field): string
    {
        $guid = self::getGUID();


        while (!!$model->where($field, $guid)->first()) {


            $guid =  self::getGUID();
        }

        return $guid;
    }

    static public function getGUID(): string
    {
        if (function_exists('com_create_guid') === true) {
            return trim(com_create_guid(), '{}');
        }

        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }


    // Create GUID (Globally Unique Identifier)
    function create_guid()
    {
        $guid = '';
        $namespace = rand(11111, 99999);
        $uid = uniqid('', true);
        $data = $namespace;
        $data .= $_SERVER['REQUEST_TIME'];
        $data .= $_SERVER['HTTP_USER_AGENT'];
        $data .= $_SERVER['REMOTE_ADDR'];
        $data .= $_SERVER['REMOTE_PORT'];
        $hash = strtoupper(hash('ripemd128', $uid . $guid . md5($data)));
        $guid = substr($hash,  0,  8) . '-' .
            substr($hash,  8,  4) . '-' .
            substr($hash, 12,  4) . '-' .
            substr($hash, 16,  4) . '-' .
            substr($hash, 20, 12);
        return $guid;
    }
}
