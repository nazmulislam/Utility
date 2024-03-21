<?php

namespace NazmulIslam\Utility\Core\Traits;

use NazmulIslam\Utility\Core\ServiceApiResponse;

trait MakesApiRequests
{
    
    private function get($endpoint, $data = []): ServiceApiResponse
    {
        return $this->makeRequest($endpoint, $data, false);
    }

    private function post($endpoint, $data = []): ServiceApiResponse
    {
        return $this->jsonRequest($endpoint, $data, 'POST');
    }

    private function put($endpoint, $data = []): ServiceApiResponse
    {
        return $this->jsonRequest($endpoint, $data, 'PUT');
    }

    private function patch($endpoint, $data = []): ServiceApiResponse
    {
        return $this->jsonRequest($endpoint, $data, 'PATCH');
    }

    private function delete($endpoint): ServiceApiResponse
    {
        return $this->jsonRequest($endpoint, [], 'DELETE');
    }

    private function jsonRequest($endpoint, $data = [], $customRequestType = null): ServiceApiResponse
    {
        return $this->makeRequest($endpoint, $data, true, $customRequestType);
    }

    private function makeRequest($endpoint, $data, bool $isJson, ?string $customRequestType = null): ServiceApiResponse
    {
        // filter out null values so they are removed from both signature header generation and query building,
        // otherwise there might be a mismatch between the two and the generated signature could be invalid
        $data = array_filter($data, fn($item) => ! is_null($item));

        $headers = $this->getRequestHeaders($data);

        if ( ! $isJson) {
            $queryParams = http_build_query($data);
            if ($queryParams) {
                $endpoint = $endpoint . '?' . $queryParams;
            }
        }

        $curl = $this->getBaseCurl(
            $endpoint,
            $headers,
            $isJson ? $data : null,
            $isJson ? $customRequestType : null
        );

        $response = $this->executeCurl($curl);

        return $response;
    }

    private function getRequestHeaders($data = [])
    {
        $headers = [
            'Authorization:' . $_ENV['COMPANY_HOUSE_API_KEY'],
            'Accept:application/json',
        ];
        return $headers;
    }

    private function getBaseCurl($url, $headers, ?array $data, ?string $customRequestType)
    {
        $curl = curl_init($url);

        if ($customRequestType) {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $customRequestType);
        }

        if (isset($data)) {
            $payload = ($data) ? json_encode($data) : '{}';
            curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
            $headers = array_merge($headers, [
                'Content-Type:application/json',
            ]);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        return $curl;
    }

    private function executeCurl($curl): ServiceApiResponse
    {
        $output   = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $endpoint = curl_getinfo($curl, CURLINFO_EFFECTIVE_URL);
        curl_close($curl);

        $data = json_decode($output, true);

        return new ServiceApiResponse($httpCode, $data ?? [], $endpoint);
    }
}