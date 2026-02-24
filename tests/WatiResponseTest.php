<?php

declare(strict_types=1);

namespace Tests\Http;

use GuzzleHttp\Psr7\Response;
use Wati\Http\WatiResponse;

it('can create from response', function (): void {
    $response = new Response(200, ['Content-Type' => 'application/json'], '{"key":"value"}');
    $watiResponse = WatiResponse::fromResponse($response);

    expect($watiResponse->getStatusCode())->toBe(200)
        ->and($watiResponse->getHeaderLine('Content-Type'))->toBe('application/json')
        ->and($watiResponse->getBody()->getContents())->toBe('{"key":"value"}');
});

it('can parse json body', function (): void {
    $response = new Response(200, [], '{"name":"John","age":30}');
    $watiResponse = WatiResponse::fromResponse($response);

    expect($watiResponse->json())->toBe(['name' => 'John', 'age' => 30]);
});

it('returns null for invalid json', function (): void {
    $response = new Response(200, [], 'not valid json');
    $watiResponse = WatiResponse::fromResponse($response);

    expect($watiResponse->json())->toBeNull();
});

it('returns true when result is success', function (): void {
    $response = new Response(200, [], '{"result":"success","data":[]}');
    $watiResponse = WatiResponse::fromResponse($response);

    expect($watiResponse->isSuccessful())->toBeTrue();
});

it('returns false when result is not success', function (): void {
    $response = new Response(200, [], '{"result":"error","message":"Failed"}');
    $watiResponse = WatiResponse::fromResponse($response);

    expect($watiResponse->isSuccessful())->toBeFalse();
});

it('returns false when result key is missing', function (): void {
    $response = new Response(200, [], '{"status":"ok","data":[]}');
    $watiResponse = WatiResponse::fromResponse($response);

    expect($watiResponse->isSuccessful())->toBeFalse();
});

it('returns false for non array json', function (): void {
    $response = new Response(200, [], '"just a string"');
    $watiResponse = WatiResponse::fromResponse($response);

    expect($watiResponse->isSuccessful())->toBeFalse();
});

it('preserves protocol version and reason phrase', function (): void {
    $response = new Response(404, [], 'Not Found', '1.1', 'Not Found');
    $watiResponse = WatiResponse::fromResponse($response);

    expect($watiResponse->getProtocolVersion())->toBe('1.1')
        ->and($watiResponse->getReasonPhrase())->toBe('Not Found');
});
