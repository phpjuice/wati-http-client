<?php

declare(strict_types=1);

namespace Wati\Http\Exceptions;

use Psr\Http\Message\ResponseInterface;
use Throwable;

class AuthenticationException extends WatiApiException
{
    public function __construct(
        string $message = 'Authentication failed. Check your bearer token.',
        ?ResponseInterface $response = null,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, 401, $response, $previous);
    }
}
