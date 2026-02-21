# AGENTS.md - Wati HTTP Client Setup Guide

This guide helps AI agents integrate the Wati HTTP Client library into PHP projects.

## Quick Start

### 1. Install via Composer

```bash
composer require phpjuice/wati-http-client
```

### 2. Create Client

```php
use Wati\Http\WatiClient;
use Wati\Http\WatiEnvironment;

// Get this URL from your Wati Dashboard (API Docs section)
// It includes your tenant ID: https://live-mt-server.wati.io/{tenantId}
$endpoint = 'https://live-mt-server.wati.io/372813';
$bearerToken = 'your-bearer-token';

$environment = new WatiEnvironment($endpoint, $bearerToken);
$client = new WatiClient($environment);
```

#### With Custom Options

```php
$client = new WatiClient($environment, [
    'timeout' => 60,           // Request timeout in seconds (default: 30)
    'connect_timeout' => 15,   // Connection timeout in seconds (default: 10)
    'verify' => true,          // Verify SSL certificate (default: true)
    'proxy' => 'tcp://localhost:8080',  // Proxy URL (default: null)
    'debug' => false,          // Enable debug mode (default: false)
]);
```

### 3. Make API Requests

Extend `WatiRequest` to create requests:

```php
use Wati\Http\WatiRequest;
use GuzzleHttp\Psr7\Utils;

class GetContactsRequest extends WatiRequest
{
    public function __construct()
    {
        parent::__construct('GET', '/api/v1/getContacts', [
            'Accept' => 'application/json',
        ]);
    }
}

$response = $client->send(new GetContactsRequest());
$data = json_decode($response->getBody()->getContents(), true);
```

## Requirements

- PHP 8.3+
- Guzzle 7.x
- ext-json

## Testing

```bash
composer install
composer test
composer types
```

## Project Structure

```
src/
├── WatiClient.php       # Main HTTP client
├── WatiEnvironment.php  # Holds endpoint + token
├── WatiRequest.php      # Base request class
└── Exceptions/
    ├── WatiException.php          # Base exception
    ├── WatiApiException.php       # API error responses
    ├── AuthenticationException.php # 401 errors
    ├── RateLimitException.php     # 429 errors
    └── ValidationException.php    # 400/422 errors
```

## Error Handling

The client throws specific exceptions for different error scenarios:

```php
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

## Common Operations

### Send Template Message

```php
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
            ['Content-Type' => 'application/json'],
            Utils::streamFor($body)
        );
    }
}

$response = $client->send(new SendTemplateMessageRequest(
    '1234567890',
    'hello_template',
    ['name' => 'John']
));
```

### Add Contact

```php
class AddContactRequest extends WatiRequest
{
    public function __construct(string $phoneNumber, string $name)
    {
        $body = json_encode([
            'whatsappNumber' => $phoneNumber,
            'name' => $name,
        ]);

        parent::__construct(
            'POST',
            '/api/v1/addContact',
            ['Content-Type' => 'application/json'],
            Utils::streamFor($body)
        );
    }
}
```
