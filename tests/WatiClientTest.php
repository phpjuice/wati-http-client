<?php

declare(strict_types=1);

namespace Tests\Http;

use Closure;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
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
        ->and($lastRequest->getHeaderLine('SDK_Name'))->toBe('Wati PHP SDK')
        ->and($lastRequest->getHeaderLine('SDK_Version'))->toBe('1.0.0')
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

it('normalizes request path by removing leading slash', function (): void {
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
    expect($lastRequest->getUri()->getPath())->toBe('api/v1/contacts');
});

it('preserves tenant id in url when base url contains path', function (): void {
    $env = new WatiEnvironment('https://live-mt-server.wati.io/372813', 'test-token');
    $client = new WatiClient($env);

    // Create a client that captures the effective URL
    $capturedUrl = null;
    $response = json_encode(['status' => 'success']);
    assert($response !== false);

    $mockHandler = new MockHandler([
        new Response(200, ['Content-Type' => 'application/json'], $response),
    ]);

    $handlerStack = HandlerStack::create($mockHandler);
    $handlerStack->push(function (callable $handler) use (&$capturedUrl): Closure {
        return function (RequestInterface $request, array $options) use ($handler, &$capturedUrl) {
            $capturedUrl = (string) $request->getUri();

            return $handler($request, $options);
        };
    });

    $client->setClient(new Client(['handler' => $handlerStack, 'base_uri' => $env->baseUrl()]));

    $request = new class('GET', '/api/v1/getContacts') extends WatiRequest {};
    $client->send($request);

    expect($capturedUrl)->toBe('https://live-mt-server.wati.io/372813/api/v1/getContacts');
});
