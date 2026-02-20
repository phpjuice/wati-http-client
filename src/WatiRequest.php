<?php

declare(strict_types=1);

namespace Wati\Http;

use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;

abstract class WatiRequest extends Request implements RequestInterface {}
