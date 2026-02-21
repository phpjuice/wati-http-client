# Changelog

All notable changes to `phpjuice/wati-http-client` will be documented in this file.

## 1.0.1 - 2026-02-20

- Custom exceptions for error handling:
    - `WatiException` - Base exception
    - `WatiApiException` - API error responses with status code and response data
    - `AuthenticationException` - 401 errors
    - `RateLimitException` - 429 errors with retry-after support
    - `ValidationException` - 400/422 errors
- Configurable HTTP options (timeout, connect_timeout, verify, proxy, debug)
- Proper tenant ID handling in URLs with trailing slash preservation
- Request path normalization for correct URI resolution with base URLs containing paths

## 1.0.0 - 2026-02-20

- Initial release of Wati HTTP Client (PHP 8.3+ support)
