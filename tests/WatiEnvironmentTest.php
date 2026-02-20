<?php

declare(strict_types=1);

namespace Tests\Http;

use Wati\Http\WatiEnvironment;

it('can create an environment', function (): void {
    $env = new WatiEnvironment('https://example.wati.io', 'test-bearer-token');
    expect($env->baseUrl())->toBe('https://example.wati.io')
        ->and($env->bearerToken())->toBe('test-bearer-token');
});

it('returns authorization string', function (): void {
    $env = new WatiEnvironment('https://example.wati.io', 'test-bearer-token');
    expect($env->authorizationString())->toBe('Bearer test-bearer-token');
});

it('trims trailing slash from endpoint', function (): void {
    $env = new WatiEnvironment('https://example.wati.io/', 'test-bearer-token');
    expect($env->baseUrl())->toBe('https://example.wati.io');
});
