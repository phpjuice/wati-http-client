<?php

declare(strict_types=1);

namespace Wati\Http\Exceptions;

use Psr\Http\Message\ResponseInterface;
use Throwable;

class WatiApiException extends WatiException
{
    /** @var array<mixed>|null */
    protected ?array $responseData = null;

    public function __construct(
        string $message,
        protected int $statusCode,
        ?ResponseInterface $response = null,
        ?Throwable $previous = null
    ) {
        if ($response instanceof ResponseInterface) {
            $body = $response->getBody()->getContents();
            $decoded = json_decode($body, true);
            $this->responseData = is_array($decoded) ? $decoded : null;
        }

        parent::__construct($message, $statusCode, $previous);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /** @return array<mixed>|null */
    public function getResponseData(): ?array
    {
        return $this->responseData;
    }
}
