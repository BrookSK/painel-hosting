# LRV Cloud Manager - PHP SDK

Official PHP SDK for the LRV Cloud Manager Public API.

## Installation

```bash
composer require lrv/cloud-manager-sdk
```

## Quick Start

```php
<?php

require 'vendor/autoload.php';

use LRVCloud\Client;

$client = new Client('lrv_live_your_api_key_here');

// List your VPS
$vps = $client->hosting()->list();

// Create a ticket
$ticket = $client->tickets()->create([
    'subject' => 'Server issue',
    'message' => 'Details about the issue...',
    'priority' => 'high',
]);

// List domains
$domains = $client->domains()->list();

// Create a backup
$backup = $client->backups()->create(['vps_id' => 1]);
```

## Authentication

```php
// Using API Key directly (recommended for server-side)
$client = new Client('lrv_live_your_api_key');

// Using Bearer Token
$client = new Client();
$tokens = $client->auth()->issueTokens('lrv_live_your_api_key');
$client->setBearerToken($tokens['access_token']);
```

## Available Resources

| Resource | Methods |
|----------|---------|
| `hosting()` | list, show, restart, metrics |
| `tickets()` | list, show, create, reply, close |
| `subscriptions()` | list, show, invoices |
| `domains()` | list, show, create, remove |
| `databases()` | list, create, remove |
| `backups()` | list, create, restore |
| `applications()` | list, catalog, install, status |
| `emails()` | list, create, remove |
| `webhooks()` | list, create, update, remove, events, deliveries, resend |
| `status()` | index, incidents |
| `logs()` | list |

## Pagination

```php
$result = $client->tickets()->list(['page' => 2, 'per_page' => 10]);
// $result['data'] — array of tickets
// $result['meta'] — pagination info (current_page, total, last_page, per_page)
```

## Error Handling

```php
try {
    $vps = $client->hosting()->show(999);
} catch (LRVCloud\Exceptions\NotFoundException $e) {
    echo "VPS not found";
} catch (LRVCloud\Exceptions\RateLimitException $e) {
    echo "Rate limited. Retry after: " . $e->retryAfter . " seconds";
} catch (LRVCloud\Exceptions\ApiException $e) {
    echo "Error: " . $e->getMessage();
    echo "Code: " . $e->errorCode;
}
```

## Environments

```php
// Production (default)
$client = new Client('lrv_live_...');

// Sandbox
$client = new Client('lrv_test_...');
$client->setBaseUrl('https://your-domain.com');
```

## Requirements

- PHP 8.1+
- ext-json
- ext-curl
