<?php

declare(strict_types=1);

namespace Tests\Http;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Wati\Http\Exceptions\AuthenticationException;
use Wati\Http\Exceptions\RateLimitException;
use Wati\Http\Exceptions\ValidationException;
use Wati\Http\Exceptions\WatiApiException;
use Wati\Http\Exceptions\WatiException;
use Wati\Http\WatiClient;
use Wati\Http\WatiEnvironment;
use Wati\Http\WatiRequest;

it('throws AuthenticationException on 401', function (): void {
    $env = new WatiEnvironment('https://your-instance.wati.io', 'test-token');
    $client = new WatiClient($env);

    $mockHandler = new MockHandler([
        new Response(401, ['Content-Type' => 'application/json'], '{"error": "Unauthorized"}'),
    ]);
    $client->setClient(new Client(['handler' => HandlerStack::create($mockHandler)]));

    $request = new class('GET', '/api/v1/contacts') extends WatiRequest {};
    $client->send($request);
})->throws(AuthenticationException::class);

it('throws RateLimitException on 429', function (): void {
    $env = new WatiEnvironment('https://your-instance.wati.io', 'test-token');
    $client = new WatiClient($env);

    $mockHandler = new MockHandler([
        new Response(429, ['Content-Type' => 'application/json', 'Retry-After' => '60'], '{"error": "Rate limit exceeded"}'),
    ]);
    $client->setClient(new Client(['handler' => HandlerStack::create($mockHandler)]));

    $request = new class('GET', '/api/v1/contacts') extends WatiRequest {};
    $client->send($request);
})->throws(RateLimitException::class);

it('throws ValidationException on 400', function (): void {
    $env = new WatiEnvironment('https://your-instance.wati.io', 'test-token');
    $client = new WatiClient($env);

    $mockHandler = new MockHandler([
        new Response(400, ['Content-Type' => 'application/json'], '{"error": "Bad request"}'),
    ]);
    $client->setClient(new Client(['handler' => HandlerStack::create($mockHandler)]));

    $request = new class('GET', '/api/v1/contacts') extends WatiRequest {};
    $client->send($request);
})->throws(ValidationException::class);

it('throws ValidationException on 422', function (): void {
    $env = new WatiEnvironment('https://your-instance.wati.io', 'test-token');
    $client = new WatiClient($env);

    $mockHandler = new MockHandler([
        new Response(422, ['Content-Type' => 'application/json'], '{"error": "Unprocessable entity"}'),
    ]);
    $client->setClient(new Client(['handler' => HandlerStack::create($mockHandler)]));

    $request = new class('GET', '/api/v1/contacts') extends WatiRequest {};
    $client->send($request);
})->throws(ValidationException::class);

it('throws WatiApiException on 404', function (): void {
    $env = new WatiEnvironment('https://your-instance.wati.io', 'test-token');
    $client = new WatiClient($env);

    $mockHandler = new MockHandler([
        new Response(404, ['Content-Type' => 'application/json'], '{"error": "Not found"}'),
    ]);
    $client->setClient(new Client(['handler' => HandlerStack::create($mockHandler)]));

    $request = new class('GET', '/api/v1/contacts/999') extends WatiRequest {};
    $client->send($request);
})->throws(WatiApiException::class);

it('throws WatiApiException on 500', function (): void {
    $env = new WatiEnvironment('https://your-instance.wati.io', 'test-token');
    $client = new WatiClient($env);

    $mockHandler = new MockHandler([
        new Response(500, ['Content-Type' => 'application/json'], '{"error": "Internal server error"}'),
    ]);
    $client->setClient(new Client(['handler' => HandlerStack::create($mockHandler)]));

    $request = new class('GET', '/api/v1/contacts') extends WatiRequest {};
    $client->send($request);
})->throws(WatiApiException::class);

it('includes response data in exception', function (): void {
    $env = new WatiEnvironment('https://your-instance.wati.io', 'test-token');
    $client = new WatiClient($env);

    $mockHandler = new MockHandler([
        new Response(401, ['Content-Type' => 'application/json'], '{"error": "Invalid token", "code": "AUTH_FAILED"}'),
    ]);
    $client->setClient(new Client(['handler' => HandlerStack::create($mockHandler)]));

    $request = new class('GET', '/api/v1/contacts') extends WatiRequest {};

    try {
        $client->send($request);
    } catch (WatiApiException $e) {
        expect($e->getStatusCode())->toBe(401)
            ->and($e->getResponseData())->toBe(['error' => 'Invalid token', 'code' => 'AUTH_FAILED']);
    }
});

it('includes retry-after in RateLimitException', function (): void {
    $env = new WatiEnvironment('https://your-instance.wati.io', 'test-token');
    $client = new WatiClient($env);

    $mockHandler = new MockHandler([
        new Response(429, ['Content-Type' => 'application/json', 'Retry-After' => '120'], '{"error": "Rate limit exceeded"}'),
    ]);
    $client->setClient(new Client(['handler' => HandlerStack::create($mockHandler)]));

    $request = new class('GET', '/api/v1/contacts') extends WatiRequest {};

    try {
        $client->send($request);
    } catch (RateLimitException $e) {
        expect($e->getRetryAfter())->toBe(120);
    }
});

it('throws WatiException on connection failure', function (): void {
    $env = new WatiEnvironment('https://your-instance.wati.io', 'test-token');
    $client = new WatiClient($env);

    $connectException = new ConnectException(
        'Connection refused',
        new Request('GET', '/api/v1/contacts')
    );

    $mockHandler = new MockHandler([$connectException]);
    $client->setClient(new Client(['handler' => HandlerStack::create($mockHandler)]));

    $request = new class('GET', '/api/v1/contacts') extends WatiRequest {};

    try {
        $client->send($request);
    } catch (WatiException $e) {
        expect($e->getMessage())->toContain('Failed to connect to Wati API')
            ->and($e->getPrevious())->toBeInstanceOf(ConnectException::class);
    }
});

it('throws WatiException on generic guzzle error', function (): void {
    $env = new WatiEnvironment('https://your-instance.wati.io', 'test-token');
    $client = new WatiClient($env);

    $guzzleException = new class('Too many redirects') extends Exception implements GuzzleException {};

    $mockHandler = new MockHandler([$guzzleException]);
    $client->setClient(new Client(['handler' => HandlerStack::create($mockHandler)]));

    $request = new class('GET', '/api/v1/contacts') extends WatiRequest {};

    try {
        $client->send($request);
    } catch (WatiException $e) {
        expect($e->getMessage())->toContain('Failed to connect to Wati API: Too many redirects')
            ->and($e->getPrevious())->toBeInstanceOf(GuzzleException::class);
    }
});
