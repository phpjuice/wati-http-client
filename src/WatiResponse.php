<?php

declare(strict_types=1);

namespace Wati\Http;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

final class WatiResponse extends Response implements ResponseInterface
{
    public static function fromResponse(ResponseInterface $response): self
    {
        return new self(
            $response->getStatusCode(),
            $response->getHeaders(),
            $response->getBody(),
            $response->getProtocolVersion(),
            $response->getReasonPhrase()
        );
    }

    public function json(): mixed
    {
        return json_decode($this->getBody()->getContents(), true);
    }

    public function isSuccessful(): bool
    {
        $json = $this->json();

        if (! is_array($json)) {
            return false;
        }

        return ($json['result'] ?? null) === 'success';
    }
}
