<?php
declare(strict_types=1);
namespace NazmulIslam\Utility\GEOIP;

class GEOIP {
    
    public static function getCountryFromIPAddress(string $ipAddress): ?string
    {
         $curlUrl = 'https://www.iplocate.io/api/lookup/' . $ipAddress;
            $ch = curl_init();
            //step2
            curl_setopt($ch, CURLOPT_URL, $curlUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, false);
            //step3
            $result = json_decode(curl_exec($ch), true);
            //step4
            curl_close($ch);
            //step5
            return $country = isset($result['country']) ? $result['country'] : null;
    }

    public static function getlocationFromIPAddress(string $ipAddress): ?array
    {
        $curlUrl = 'https://www.iplocate.io/api/lookup/' . $ipAddress;
        $ch = curl_init();
        //step2
        curl_setopt($ch, CURLOPT_URL, $curlUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        //step3
        $result = json_decode(curl_exec($ch), true);
        //step4
        curl_close($ch);
        //step5
        return isset($result) ? $result : null;
    }

}
