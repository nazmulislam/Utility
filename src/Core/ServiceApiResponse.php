<?php

namespace NazmulIslam\Utility\Core;

class ServiceApiResponse
{
    private int    $httpCode;
    private array  $json;
    private string $endpoint;

    public function __construct(int $httpCode, array $json, string $endpoint)
    {
        $this->httpCode = $httpCode;
        $this->json     = $json;
        $this->endpoint = $endpoint;
    }

    public function isOK()
    {
        return $this->httpCode >= 200 && $this->httpCode < 300;
    }

    public function isValidationError()
    {
        return $this->httpCode === 422;
    }

    public function httpCode()
    {
        return $this->httpCode;
    }

    public function json()
    {
        return $this->json;
    }

    public function endpoint()
    {
        return $this->endpoint;
    }
}