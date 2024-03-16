<?php
declare(strict_types=1);
namespace NazmulIslam\Utility\UrlShortener;



class UrlShortener
{
    public static $chars = "abcdfghjkmnpqrstvwxyz|ABCDFGHJKLMNPQRSTVWXYZ|0123456789";
    public static $checkUrlExists = false;
    public static $codeLength = 7;


    /**
     *
     */
    public static function validateUrlFormat($url)
    {
        $regex = "((https?|ftp)\:\/\/)?";
        $regex .= "([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?";
        $regex .= "([a-z0-9-.]*)\.([a-z]{2,3})";
        $regex .= "(\:[0-9]{2,5})?";
        $regex .= "(\/([a-z0-9+\$_-]\.?)+)*\/?";
        $regex .= "(\?[a-z+&\$_.-][a-z0-9;:@&%=+\/\$_.-]*)?";
        $regex .= "(#[a-z_.-][a-z0-9+\$_.-]*)?";
        $data = true ;
        if (!preg_match("/^$regex$/i", $url)) {
            $data = [''] ;
        }
        return $data ;
    }

    /**
     *
     */
    public static function verifyUrlExists($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        $response = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return (!empty($response) && $response != 404);
    }

    /**
     *
     */

    public static function createShortCode($url)
    {
        $shortCode = self::generateRandomString(self::$codeLength);
        return $shortCode;
    }

    /**
     *
     */

    public static function generateRandomString($length = 6)
    {
        $sets = explode('|', self::$chars);
        $all = '';
        $randString = '';
        foreach($sets as $set) {
            $randString .= $set[array_rand(str_split($set))];
            $all .= $set;
        }
        $all = str_split($all);
        for($i = 0; $i < $length - count($sets); $i++) {
            $randString .= $all[array_rand($all)];
        }
        $randString = str_shuffle($randString);
        return $randString;
    }


    /**
     *
     */
    public static function validateShortCode($code)
    {
        $rawChars = str_replace('|', '', self::$chars);
        return preg_match("|[".$rawChars."]+|", $code);
    }



}
