<?php

declare(strict_types=1);

namespace Wati\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class WatiClient
{
    protected Client $client;

    public function __construct(protected WatiEnvironment $environment)
    {
        $this->client = new Client([
            'base_uri' => $this->environment->baseUrl(),
            'timeout' => 30,
            'connect_timeout' => 10,
        ]);
    }

    /** @throws GuzzleException */
    public function send(RequestInterface $request): ResponseInterface
    {
        if (! $this->hasAuthHeader($request)) {
            $request = $request->withHeader('Authorization', $this->environment->authorizationString());
        }

        $request = $this->injectUserAgentHeaders($request);
        $request = $this->injectSdkHeaders($request);

        return $this->client->send($request);
    }

    public function hasAuthHeader(RequestInterface $request): bool
    {
        return array_key_exists('Authorization', $request->getHeaders());
    }

    protected function injectUserAgentHeaders(RequestInterface $request): RequestInterface
    {
        return $request->withHeader('User-Agent', 'WatiHttp-PHP HTTP/1.1');
    }

    protected function injectSdkHeaders(RequestInterface $request): RequestInterface
    {
        return $request
            ->withHeader('sdk_name', 'Wati PHP SDK')
            ->withHeader('sdk_version', '1.0.0')
            ->withHeader('sdk_tech_stack', 'PHP '.PHP_VERSION);
    }

    public function setClient(Client $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getEnvironment(): WatiEnvironment
    {
        return $this->environment;
    }
}
