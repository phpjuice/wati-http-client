<?php

declare(strict_types=1);

namespace Wati\Http\Exceptions;

use Psr\Http\Message\ResponseInterface;
use Throwable;

final class RateLimitException extends WatiApiException
{
    private ?int $retryAfter = null;

    public function __construct(
        string $message = 'Rate limit exceeded. Please retry after some time.',
        ?ResponseInterface $response = null,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, 429, $response, $previous);

        if ($response instanceof ResponseInterface) {
            $retryAfter = $response->getHeaderLine('Retry-After');
            $this->retryAfter = $retryAfter !== '' ? (int) $retryAfter : null;
        }
    }

    public function getRetryAfter(): ?int
    {
        return $this->retryAfter;
    }
}
