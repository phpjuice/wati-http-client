<?php

declare(strict_types=1);

namespace Wati\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Wati\Http\Exceptions\AuthenticationException;
use Wati\Http\Exceptions\RateLimitException;
use Wati\Http\Exceptions\ValidationException;
use Wati\Http\Exceptions\WatiApiException;
use Wati\Http\Exceptions\WatiException;

class WatiClient
{
    protected Client $client;

    /**
     * @var array{
     *     timeout: int,
     *     connect_timeout: int,
     *     verify: bool,
     *     debug: bool,
     *     proxy: null|string
     * }
     */
    protected array $defaultOptions = [
        'timeout' => 30,
        'connect_timeout' => 10,
        'verify' => true,
        'debug' => false,
        'proxy' => null,
    ];

    /**
     * @param  array<string, int|bool|string|null>  $options
     */
    public function __construct(
        protected WatiEnvironment $environment,
        array $options = []
    ) {
        $config = array_merge($this->defaultOptions, $options);
        $config['base_uri'] = $this->environment->baseUrl();

        $this->client = new Client(array_filter($config, fn ($value): bool => $value !== null));
    }

    /**
     * @throws WatiException
     * @throws WatiApiException
     */
    public function send(RequestInterface $request): ResponseInterface
    {
        $request = $this->normalizeRequestPath($request);

        if (! $this->hasAuthHeader($request)) {
            $request = $request->withHeader('Authorization', $this->environment->authorizationString());
        }

        $request = $this->injectUserAgentHeaders($request);
        $request = $this->injectSdkHeaders($request);

        try {
            return $this->client->send($request);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            throw match ($statusCode) {
                401 => new AuthenticationException(response: $response, previous: $e),
                429 => new RateLimitException(response: $response, previous: $e),
                400, 422 => new ValidationException(response: $response, previous: $e),
                default => new WatiApiException(
                    $e->getMessage(),
                    $statusCode,
                    $response,
                    $e
                ),
            };
        } catch (ServerException $e) {
            $response = $e->getResponse();
            throw new WatiApiException(
                'Wati API server error: '.$e->getMessage(),
                $response->getStatusCode(),
                $response,
                $e
            );
        } catch (ConnectException|GuzzleException $e) {
            throw new WatiException(
                'Failed to connect to Wati API: '.$e->getMessage(),
                0,
                $e
            );
        }
    }

    protected function normalizeRequestPath(RequestInterface $request): RequestInterface
    {
        $uri = $request->getUri();
        $path = $uri->getPath();

        if (str_starts_with($path, '/')) {
            $uri = $uri->withPath(substr($path, 1));
            $request = $request->withUri($uri);
        }

        return $request;
    }

    protected function hasAuthHeader(RequestInterface $request): bool
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
            ->withHeader('Wati-SDK-Name', 'wati-http-client')
            ->withHeader('Wati-SDK-Version', '1.0.0')
            ->withHeader('Wati-SDK-Language', 'PHP');
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
