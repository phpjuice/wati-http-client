<?php

declare(strict_types=1);

namespace Wati\Http;

class WatiEnvironment
{
    protected string $endpoint;

    public function __construct(string $endpoint, protected string $bearerToken)
    {
        $this->endpoint = rtrim($endpoint, '/');
    }

    public function baseUrl(): string
    {
        return $this->endpoint;
    }

    public function bearerToken(): string
    {
        return $this->bearerToken;
    }

    public function authorizationString(): string
    {
        return 'Bearer '.$this->bearerToken;
    }
}
