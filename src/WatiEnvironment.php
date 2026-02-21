<?php

declare(strict_types=1);

namespace Wati\Http;

class WatiEnvironment
{
    protected string $endpoint;

    protected string $bearerToken;

    /**
     * Create a new Wati environment.
     *
     * @param  string  $endpoint  API endpoint URL from Wati dashboard (includes tenant ID).
     *                            Example: https://live-mt-server.wati.io/372813
     * @param  string  $bearerToken  The bearer token for authentication.
     */
    public function __construct(string $endpoint, string $bearerToken)
    {
        // Normalize bearer token: strip "Bearer " prefix if present
        if (str_starts_with(strtolower($bearerToken), 'bearer ')) {
            $bearerToken = substr($bearerToken, strlen('bearer '));
        }
        $this->bearerToken = $bearerToken;

        $parsed = parse_url($endpoint);

        // Check if the URL contains a path (tenant ID)
        $hasPath = isset($parsed['path']) && $parsed['path'] !== '/';

        // Ensure URLs with paths (tenant IDs) end with a trailing slash for proper URI resolution.
        // This ensures relative paths are appended correctly:
        //   base: https://server/tenant/ + path: api/v1/contacts -> https://server/tenant/api/v1/contacts
        $this->endpoint = $hasPath ? rtrim($endpoint, '/').'/' : rtrim($endpoint, '/');
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
