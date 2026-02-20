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

$endpoint = 'https://your-instance.wati.io';
$bearerToken = 'your-bearer-token';

$environment = new WatiEnvironment($endpoint, $bearerToken);
$client = new WatiClient($environment);
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
└── WatiRequest.php      # Base request class
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
