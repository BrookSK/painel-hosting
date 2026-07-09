# LRV Cloud Manager - Python SDK

Official Python SDK for the LRV Cloud Manager Public API.

## Installation

```bash
pip install lrv-cloud-manager
```

## Quick Start

```python
from lrvcloud import Client

client = Client("lrv_live_your_api_key_here")

# List your VPS
vps_list = client.hosting.list()

# Create a ticket
ticket = client.tickets.create(
    subject="Server issue",
    message="Details about the issue...",
    priority="high",
)

# List domains
domains = client.domains.list()

# Create a backup
backup = client.backups.create(vps_id=1)
```

## Authentication

```python
# Using API Key directly (recommended)
client = Client("lrv_live_your_api_key")

# Using Bearer Token
client = Client()
tokens = client.auth.issue_tokens("lrv_live_your_api_key")
client.set_bearer_token(tokens["access_token"])
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

## Pagination

```python
result = client.tickets.list(page=2, per_page=10)
print(result["data"])   # List of tickets
print(result["meta"])   # {"current_page": 2, "total": 50, ...}
```

## Error Handling

```python
from lrvcloud.exceptions import NotFoundException, RateLimitError, ApiError

try:
    vps = client.hosting.show(999)
except NotFoundException:
    print("VPS not found")
except RateLimitError as e:
    print(f"Rate limited. Retry after {e.retry_after} seconds")
except ApiError as e:
    print(f"Error: {e.code} - {e.message}")
```

## Environments

```python
# Production (default)
client = Client("lrv_live_...")

# Sandbox
client = Client("lrv_test_...", base_url="https://your-domain.com")
```

## Requirements

- Python 3.9+
- requests library
