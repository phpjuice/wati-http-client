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
    
    public function json(): array
    {
        return json_decode($this->getBody()->getContents(), true);
    }

    public function isSuccessful(): bool
    {
        return ($this->json()['result'] ?? null) === 'success';
    }
}
