<?php

declare(strict_types=1);

namespace Wati\Http;

final readonly class WatiEnvironment
{
    public const int BEARER_PREFIX_LENGTH = 7; // strlen('bearer ')

    private string $endpoint;

    private string $bearerToken;

    /**
     * Create a new Wati environment.
     *
     * @param  string  $endpoint  API endpoint URL from Wati dashboard (includes tenant ID).
     *                            Example: https://your-instance.wati.io/123456
     * @param  string  $bearerToken  The bearer token for authentication.
     */
    public function __construct(string $endpoint, string $bearerToken)
    {
        // Normalize bearer token: strip "Bearer " prefix if present
        if (str_starts_with(strtolower($bearerToken), 'bearer ')) {
            $bearerToken = substr($bearerToken, self::BEARER_PREFIX_LENGTH);
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
