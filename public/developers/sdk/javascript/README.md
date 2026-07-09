# LRV Cloud Manager - JavaScript/TypeScript SDK

Official JavaScript SDK for the LRV Cloud Manager Public API.

## Installation

```bash
npm install @lrv/cloud-manager
# or
yarn add @lrv/cloud-manager
```

## Quick Start

```typescript
import { LRVClient } from '@lrv/cloud-manager';

const client = new LRVClient('lrv_live_your_api_key_here');

// List your VPS
const vps = await client.hosting.list();

// Create a ticket
const ticket = await client.tickets.create({
  subject: 'Server issue',
  message: 'Details about the issue...',
  priority: 'high',
});

// List domains
const domains = await client.domains.list();

// Create a backup
const backup = await client.backups.create({ vps_id: 1 });
```

## Authentication

```typescript
// Using API Key directly (recommended)
const client = new LRVClient('lrv_live_your_api_key');

// Using Bearer Token
const client = new LRVClient();
const tokens = await client.auth.issueTokens('lrv_live_your_api_key');
client.setBearerToken(tokens.access_token);
```

## Available Resources

| Resource | Methods |
|----------|---------|
| `hosting` | list, show, restart, metrics |
| `tickets` | list, show, create, reply, close |
| `subscriptions` | list, show, invoices |
| `domains` | list, show, create, remove |
| `databases` | list, create, remove |
| `backups` | list, create, restore |
| `applications` | list, catalog, install, status |
| `emails` | list, create, remove |
| `webhooks` | list, create, update, remove, events, deliveries, resend |
| `status` | index, incidents |
| `logs` | list |

## TypeScript Support

Full TypeScript types included:

```typescript
import { LRVClient, VPS, Ticket, Domain } from '@lrv/cloud-manager';

const client = new LRVClient('lrv_live_...');
const vps: VPS[] = await client.hosting.list();
```

## Pagination

```typescript
const result = await client.tickets.list({ page: 2, per_page: 10 });
console.log(result.data);   // Ticket[]
console.log(result.meta);   // { current_page, total, last_page, per_page }
```

## Error Handling

```typescript
import { NotFoundException, RateLimitError, ApiError } from '@lrv/cloud-manager';

try {
  await client.hosting.show(999);
} catch (e) {
  if (e instanceof NotFoundException) {
    console.log('VPS not found');
  } else if (e instanceof RateLimitError) {
    console.log(`Retry after ${e.retryAfter} seconds`);
  } else if (e instanceof ApiError) {
    console.log(`Error: ${e.code} - ${e.message}`);
  }
}
```

## Environments

```typescript
// Production (default)
const client = new LRVClient('lrv_live_...');

// Sandbox
const client = new LRVClient('lrv_test_...', {
  baseUrl: 'https://your-domain.com',
});
```

## Requirements

- Node.js 18+ or modern browser
- fetch API (native or polyfill)
