<?php

declare(strict_types=1);

namespace Wati\Http\Exceptions;

use Psr\Http\Message\ResponseInterface;
use Throwable;

final class ValidationException extends WatiApiException
{
    public function __construct(
        string $message = 'Validation failed. Check your request parameters.',
        ?ResponseInterface $response = null,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, 400, $response, $previous);
    }
}
