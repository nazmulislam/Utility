<?php

declare(strict_types=1);

namespace NazmulIslam\Utility\HTTPClient;

use GuzzleHttp\Client;

/**
 * Description of HTTPClient
 *
 * @author nazmulislam
 */
class HTTPClient
{
    static $base_uri;
    static $endpoint;
    static $method;
    static $timeout;
    static $client;
    static $response;
    static $data;
    static $authToken;

    static function requestWithJson(string $base_uri, string $endpoint, string $method, array $data, string $authToken = NULL, float $timeout = 30.0)
    {
        self::$base_uri = $base_uri;
        self::$method = strtoupper($method);
        self::$endpoint = $endpoint;
        self::$timeout = $timeout;
        self::$authToken = $authToken;
        self::$data = $data;

        self::HTTPRequestWithJson();

        return json_decode(self::$response->getBody(), TRUE);
    }

    static function HTTPRequestWithJson()
    {
        $headers = [
            'Authorization' => self::$authToken
        ];
        self::$client = new Client();
        self::$response = self::$client->request(
            self::$method,
            self::$base_uri . self::$endpoint,
            [
                \GuzzleHttp\RequestOptions::JSON => self::$data,
                'headers' => $headers
            ]
        );
    }

    static function request(string $base_uri, string $endpoint, string $method, array $data, string $authToken = NULL, float $timeout = 2.0)
    {
        self::$base_uri = $base_uri;
        self::$method = strtoupper($method);
        self::$endpoint = $endpoint;
        self::$timeout = $timeout;
        self::$authToken = $authToken;
        self::$data = $data;

        self::HTTPRequest();

        return self::$response->getBody();
    }

    static function HTTPRequest()
    {
        $headers = [
            'Authorization' => self::$authToken
        ];
        self::$client = new Client();
        self::$response = self::$client->request(
            self::$method,
            self::$base_uri . self::$endpoint,
            [
                'form_params' => self::$data,
                'headers' => $headers
            ]
        );
    }

    static function multiRequest(array $data, array $options = [])
    {


        // array of curl handles
        $curly = [];
        // data to be returned
        $result = [];

        // multi handle
        $mh = curl_multi_init();

        // loop through $data and create curl handles
        // then add them to the multi-handle
        foreach ($data as $id => $d) {

            $curly[$id] = curl_init();

            $url = (is_array($d) && !empty($d['url'])) ? $d['url'] : $d;
            curl_setopt($curly[$id], CURLOPT_URL, $url);
            curl_setopt($curly[$id], CURLOPT_HEADER, 0);
            curl_setopt($curly[$id], CURLOPT_RETURNTRANSFER, 1);

            // post?
            if (is_array($d)) {
                if (!empty($d['post'])) {
                    curl_setopt($curly[$id], CURLOPT_POST, 1);
                    curl_setopt($curly[$id], CURLOPT_POSTFIELDS, $d['post']);
                }
            }

            // extra options?
            if (!empty($options)) {
                curl_setopt_array($curly[$id], $options);
            }

            curl_multi_add_handle($mh, $curly[$id]);
        }

        // execute the handles
        $running = null;
        do {
            curl_multi_exec($mh, $running);
        } while ($running > 0);

        $tempArray = [];
        // get content and remove handles
        foreach ($curly as $id => $c) {
            //$result[$id] = json_decode(curl_multi_getcontent($c));
            $tempArray[] = json_decode(curl_multi_getcontent($c));
            curl_multi_remove_handle($mh, $c);
        }
        $items = [];
        $i = 0;

        foreach ($tempArray as $pages) {
            if (isset($pages->items)) {
                foreach ($pages->items as $item) {
                    $items[] = $item;
                }
            }
        }

        // all done
        curl_multi_close($mh);

        return $items;
    }

    static function multiRequestWithHeaders(array $data, array $options = [], array $header = [])
    {

        // array of curl handles
        $curly = [];
        // data to be returned
        $result = [];

        // multi handle
        $mh = curl_multi_init();

        // loop through $data and create curl handles
        // then add them to the multi-handle
        foreach ($data as $id => $d) {

            $curly[$id] = curl_init();

            $headers = array(
                'Content-Type: application/json',
                'Host: ' . $header['host'],
                'Ocp-Apim-Subscription-Key: ' . $header['key']
            );

            $url = (is_array($d) && !empty($d['url'])) ? $d['url'] : $d;
            curl_setopt($curly[$id], CURLOPT_URL, $url);
            curl_setopt($curly[$id], CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curly[$id], CURLOPT_HEADER, 0);
            curl_setopt($curly[$id], CURLOPT_RETURNTRANSFER, 1);


            // extra options?
            if (!empty($options)) {
                curl_setopt_array($curly[$id], $options);
            }

            curl_multi_add_handle($mh, $curly[$id]);
        }

        // execute the handles
        $running = null;
        do {
            curl_multi_exec($mh, $running);
        } while ($running > 0);


        $tempArray = [];
        // get content and remove handles
        foreach ($curly as $id => $c) {

            $tempArray[] = json_decode(curl_multi_getcontent($c));
            curl_multi_remove_handle($mh, $c);
        }
        $items = [];
        $i = 0;
        foreach ($tempArray as $pages) {
            if (isset($pages->webPages->value)) {
                foreach ($pages->webPages->value as $item) {
                    $items[] = $item;
                }
            }
        }



        // all done
        curl_multi_close($mh);

        return $items;
    }

    static function requestWithHeaders(array $data, array $options = [], array $header = [])
    {

        $ch = curl_init();
        $headers = array(
            'Content-Type: application/json',
            'Host: ' . $header['host'],
            'Ocp-Apim-Subscription-Key: ' . $header['key']
        );
        $url = isset($data[0]) ? $data[0] : '';
        $url = str_replace(' ', '%20', $url);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        //execute post
        $result = curl_exec($ch);

        //close connection
        curl_close($ch);
        if (!empty($result)) {
            $result = json_decode($result, true);
        }
        return $result;
    }
}
