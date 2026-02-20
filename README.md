# Wati HTTP Client

[![CI](https://github.com/phpjuice/wati-http-client/actions/workflows/php.yml/badge.svg)](https://github.com/phpjuice/wati-http-client/actions/workflows/php.yml)
[![PHP Version](https://img.shields.io/badge/PHP-8.3%20%7C%208.4-777BB4?logo=php&logoColor=white)](https://php.net)
[![Latest Stable Version](http://poser.pugx.org/phpjuice/wati-http-client/v)](https://packagist.org/packages/phpjuice/wati-http-client)
[![Total Downloads](http://poser.pugx.org/phpjuice/wati-http-client/downloads)](https://packagist.org/packages/phpjuice/wati-http-client)
[![License](http://poser.pugx.org/phpjuice/wati-http-client/license)](https://packagist.org/packages/phpjuice/wati-http-client)

A PHP HTTP Client for the [Wati.io](https://wati.io) WhatsApp API. Provides a simple, fluent API to interact with Wati's
REST API.

## Installation

This package requires PHP 8.3 or higher.

```bash
composer require "phpjuice/wati-http-client"
```

## Setup

### Get Your Credentials

1. Log in to your [Wati Account](https://app.wati.io)
2. Navigate to **API Docs** in the top menu
3. Copy your **API Endpoint URL** and **Bearer Token**

### Create a Client

```php
<?php

use Wati\Http\WatiClient;
use Wati\Http\WatiEnvironment;

// Your API endpoint and bearer token from the Wati dashboard
$endpoint = "https://your-instance.wati.io";
$bearerToken = "your-bearer-token";

// Create environment
$environment = new WatiEnvironment($endpoint, $bearerToken);

// Create client
$client = new WatiClient($environment);
```

#### With Custom Options

```php
<?php

$client = new WatiClient($environment, [
    'timeout' => 60,           // Request timeout in seconds (default: 30)
    'connect_timeout' => 15,   // Connection timeout in seconds (default: 10)
    'verify' => true,          // Verify SSL certificate (default: true)
    'proxy' => 'tcp://localhost:8080',  // Proxy URL (default: null)
    'debug' => false,          // Enable debug mode (default: false)
]);
```

## Usage

### Making Requests

Extend `WatiRequest` to create your API requests:

```php
<?php

use Wati\Http\WatiRequest;
use GuzzleHttp\Psr7\Utils;

class GetContactsRequest extends WatiRequest
{
    public function __construct(int $page = 1, int $pageSize = 50)
    {
        parent::__construct(
            'GET',
            "/api/v1/getContacts?page={$page}&pageSize={$pageSize}",
            ['Accept' => 'application/json']
        );
    }
}

class SendTemplateMessageRequest extends WatiRequest
{
    public function __construct(string $phoneNumber, string $templateName, array $parameters = [])
    {
        $body = json_encode([
            'template_name' => $templateName,
            'broadcast_name' => $templateName,
            'parameters' => $parameters,
        ]);

        parent::__construct(
            'POST',
            "/api/v1/sendTemplateMessage?whatsappNumber={$phoneNumber}",
            [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
            Utils::streamFor($body)
        );
    }
}
```

### Execute Requests

```php
<?php

use GuzzleHttp\Utils;

// Get contacts
$response = $client->send(new GetContactsRequest());
$data = Utils::jsonDecode($response->getBody()->getContents(), true);

// Send a template message
$response = $client->send(new SendTemplateMessageRequest(
    '1234567890',
    'hello_world',
    ['name' => 'John']
));
```

## API Reference

For full API documentation, visit [Wati API Docs](https://docs.wati.io/reference/introduction).

### Available Endpoints

- **Messaging**: Send templates, session messages, interactive messages
- **Contacts**: Get, add, update contacts
- **Conversations**: Messages, status updates
- **Templates**: Get and send message templates
- **Campaigns**: Manage broadcasts

## Error Handling

The client throws specific exceptions for different error scenarios:

```php
<?php

use Wati\Http\Exceptions\AuthenticationException;
use Wati\Http\Exceptions\RateLimitException;
use Wati\Http\Exceptions\ValidationException;
use Wati\Http\Exceptions\WatiApiException;
use Wati\Http\Exceptions\WatiException;

try {
    $response = $client->send(new GetContactsRequest());
} catch (AuthenticationException $e) {
    // Invalid bearer token - check credentials
    echo "Auth failed: " . $e->getMessage();
} catch (RateLimitException $e) {
    // Rate limited - wait and retry
    $retryAfter = $e->getRetryAfter(); // seconds to wait
} catch (ValidationException $e) {
    // Invalid request parameters
    $errors = $e->getResponseData();
} catch (WatiApiException $e) {
    // Other API errors (4xx, 5xx)
    $statusCode = $e->getStatusCode();
    $data = $e->getResponseData();
} catch (WatiException $e) {
    // Connection or other HTTP errors
    echo "Request failed: " . $e->getMessage();
}
```

## Changelog

Please see the [CHANGELOG](changelog.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](./.github/CONTRIBUTING.md) for details.

## Security

If you discover any security-related issues, please email the author instead of using the issue tracker.

## License

Please see the [License](./LICENSE) file.
