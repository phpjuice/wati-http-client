<?php

declare(strict_types=1);

namespace Tests\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Wati\Http\WatiClient;
use Wati\Http\WatiEnvironment;
use Wati\Http\WatiRequest;

/**
 * @return array{0: Client, 1: MockHandler}
 */
function createMockClient(): array
{
    $response = json_encode([
        'status' => 'success',
        'data' => [],
    ]);
    assert($response !== false);

    $mockHandler = new MockHandler([
        new Response(200, ['Content-Type' => 'application/json'], $response),
        new Response(200, ['Content-Type' => 'application/json'], $response),
        new Response(200, ['Content-Type' => 'application/json'], $response),
    ]);

    $handlerStack = HandlerStack::create($mockHandler);
    $client = new Client(['handler' => $handlerStack]);

    return [$client, $mockHandler];
}

it('can create a client', function (): void {
    $env = new WatiEnvironment('https://example.wati.io', 'test-token');
    $client = new WatiClient($env);
    expect($client->getEnvironment())->toBe($env);
});

it('has authorization header', function (): void {
    $env = new WatiEnvironment('https://example.wati.io', 'test-token');
    $client = new WatiClient($env);

    $request = new class('GET', '/api/v1/contacts') extends WatiRequest {};
    expect($client->hasAuthHeader($request))->toBeFalse();

    $request = $request->withHeader('Authorization', 'Bearer test');
    expect($client->hasAuthHeader($request))->toBeTrue();
});

it('injects sdk headers', function (): void {
    $env = new WatiEnvironment('https://example.wati.io', 'test-token');
    $client = new WatiClient($env);
    /** @var Client $mockClient */
    /** @var MockHandler $mockHandler */
    [$mockClient, $mockHandler] = createMockClient();
    $client->setClient($mockClient);

    $request = new class('GET', '/api/v1/contacts') extends WatiRequest {};
    $client->send($request);

    $lastRequest = $mockHandler->getLastRequest();
    assert($lastRequest !== null);
    expect($lastRequest->getHeaderLine('Authorization'))->toBe('Bearer test-token')
        ->and($lastRequest->getHeaderLine('sdk_name'))->toBe('Wati PHP SDK')
        ->and($lastRequest->getHeaderLine('sdk_version'))->toBe('1.0.0')
        ->and($lastRequest->getHeaderLine('User-Agent'))->toBe('WatiHttp-PHP HTTP/1.1');
});

it('can execute a request', function (): void {
    $env = new WatiEnvironment('https://example.wati.io', 'test-token');
    $client = new WatiClient($env);
    /** @var Client $mockClient */
    [$mockClient] = createMockClient();
    $client->setClient($mockClient);

    $request = new class('GET', '/api/v1/contacts') extends WatiRequest {};
    $response = $client->send($request);

    expect($response->getStatusCode())->toBe(200);
});

it('accepts custom timeout option', function (): void {
    $env = new WatiEnvironment('https://example.wati.io', 'test-token');
    $client = new WatiClient($env, ['timeout' => 60]);

    expect($client->getEnvironment())->toBe($env);
});

it('accepts custom connect_timeout option', function (): void {
    $env = new WatiEnvironment('https://example.wati.io', 'test-token');
    $client = new WatiClient($env, ['connect_timeout' => 20]);

    expect($client->getEnvironment())->toBe($env);
});

it('accepts verify ssl option', function (): void {
    $env = new WatiEnvironment('https://example.wati.io', 'test-token');
    $client = new WatiClient($env, ['verify' => false]);

    expect($client->getEnvironment())->toBe($env);
});

it('accepts proxy option', function (): void {
    $env = new WatiEnvironment('https://example.wati.io', 'test-token');
    $client = new WatiClient($env, ['proxy' => 'tcp://localhost:8080']);

    expect($client->getEnvironment())->toBe($env);
});

it('accepts debug option', function (): void {
    $env = new WatiEnvironment('https://example.wati.io', 'test-token');
    $client = new WatiClient($env, ['debug' => true]);

    expect($client->getEnvironment())->toBe($env);
});

it('accepts multiple options', function (): void {
    $env = new WatiEnvironment('https://example.wati.io', 'test-token');
    $client = new WatiClient($env, [
        'timeout' => 60,
        'connect_timeout' => 20,
        'verify' => true,
        'debug' => false,
    ]);

    expect($client->getEnvironment())->toBe($env);
});
