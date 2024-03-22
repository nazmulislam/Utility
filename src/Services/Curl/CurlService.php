<?php

declare(strict_types=1);

namespace NazmulIslam\Utility\Services\Curl;

use Curl\Curl;


class CurlService
{
    private $curl;

    public function __construct()
    {
        $this->initializeService();
    }

    private function initializeService() : void
    {
        $this->curl = new Curl();
    }

    public static function createQuery(string $attribute, string $query,string $element = '*')
    {
        return "//{$element}[contains(@{$attribute}, '{$query}')]";
    }

    public function getURLResponse(string $url, array $queryParameters = []) : string | null
    {
        $this->curl->get(url: $url, data: $queryParameters);
        return $this->curl->response;
    }

    public function setProxy(string $proxy, string $port) : void
    {
        $this->curl->setProxy(proxy: $proxy, port: $port);
    }

    public function getURLWithProxy(string $proxy, string $port, string $url, array $queryParameters = []) : string | null
    {
        $this->setProxy(proxy: $proxy, port: $port);
        return $this->getURLResponse(url: $url, queryParameters: $queryParameters);
    }

    public function extractAttributeFromHTML(string $html, string $query, string $attribute) : array | null
    {
        $links = [];
        if (!empty($html)) {
            $dom = new \DOMDocument();
            @$dom->loadHTML($html);
            $xpath = new \DOMXPath($dom);
            $linkNodes = $xpath->query($query);
            foreach ($linkNodes as $linkNode) {
                $links[] = $linkNode->getAttribute($attribute);
            }
        }
        
        return $links;
    }
    
    public function extractTextContentFromHTML(string $html, string $query) : array | null
    {
        $data = [];
        if (!empty($html)) {
            $dom = new \DOMDocument();
            @$dom->loadHTML($html);
            $xpath = new \DOMXPath($dom);
            $nodes = $xpath->query($query);
            foreach ($nodes as $node) {
                $data[] = $node->textContent;
            }
        }

        return $data;    
    }

    public function setCookies(array $cookies) : void
    {
        $this->curl->setCookies($cookies);
    }

    public function setHeaders(array $headers) : void
    {
        $this->curl->setHeaders($headers);
    }

    public function getResponseHeaders()
    {
        return $this->curl->getResponseHeaders();
    }
    
    public function getResponseCookies()
    {
        return $this->curl->getResponseCookies();
    }

    public function getResponseCookie(string $key)
    {
        return $this->curl->getCookie($key);
    }

    public function getCurl()
    {
        return $this->curl;
    }

}
