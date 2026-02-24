<?php

declare(strict_types=1);

namespace Wati\Http;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

abstract class WatiResponse extends Response implements ResponseInterface {}
