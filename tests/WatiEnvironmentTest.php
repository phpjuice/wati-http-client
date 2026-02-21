<?php

declare(strict_types=1);

namespace Tests\Http;

use Wati\Http\WatiEnvironment;

it('can create an environment', function (): void {
    $env = new WatiEnvironment('https://your-instance.wati.io', 'test-bearer-token');
    expect($env->baseUrl())->toBe('https://your-instance.wati.io')
        ->and($env->bearerToken())->toBe('test-bearer-token');
});

it('returns authorization string', function (): void {
    $env = new WatiEnvironment('https://your-instance.wati.io', 'test-bearer-token');
    expect($env->authorizationString())->toBe('Bearer test-bearer-token');
});

it('trims trailing slash from endpoint', function (): void {
    $env = new WatiEnvironment('https://your-instance.wati.io/', 'test-bearer-token');
    expect($env->baseUrl())->toBe('https://your-instance.wati.io');
});

it('adds trailing slash to endpoint with tenant id path', function (): void {
    $env = new WatiEnvironment('https://your-instance.wati.io/123456', 'test-bearer-token');
    expect($env->baseUrl())->toBe('https://your-instance.wati.io/123456/');
});

it('preserves trailing slash on endpoint with tenant id path', function (): void {
    $env = new WatiEnvironment('https://your-instance.wati.io/123456/', 'test-bearer-token');
    expect($env->baseUrl())->toBe('https://your-instance.wati.io/123456/');
});

it('strips Bearer prefix from token', function (): void {
    $env = new WatiEnvironment('https://your-instance.wati.io', 'Bearer my-token');
    expect($env->authorizationString())->toBe('Bearer my-token');
});
it('strips lowercase bearer prefix from token', function (): void {
    $env = new WatiEnvironment('https://your-instance.wati.io', 'bearer my-token');
    expect($env->authorizationString())->toBe('Bearer my-token');
});
it('accepts token without bearer prefix', function (): void {
    $env = new WatiEnvironment('https://your-instance.wati.io', 'my-token');
    expect($env->authorizationString())->toBe('Bearer my-token');
});
