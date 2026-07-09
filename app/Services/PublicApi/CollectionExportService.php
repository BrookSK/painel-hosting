<?php

declare(strict_types=1);

namespace LRV\App\Services\PublicApi;

/**
 * Serviço para gerar coleções exportáveis automaticamente a partir da spec OpenAPI.
 * Gera: Postman Collection (v2.1), Bruno Collection (.bru), Insomnia (v4).
 */
final class CollectionExportService
{
    private string $baseUrl;
    private array $endpoints;

    public function __construct(string $baseUrl = '{{base_url}}')
    {
        $this->baseUrl = $baseUrl;
        $this->endpoints = $this->definirEndpoints();
    }

    /**
     * Gera Postman Collection v2.1 (JSON).
     */
    public function gerarPostman(): string
    {
        $items = [];
        $folders = [];

        foreach ($this->endpoints as $ep) {
            $folder = $ep['folder'];
            if (!isset($folders[$folder])) {
                $folders[$folder] = [];
            }
            $folders[$folder][] = [
                'name' => $ep['name'],
                'request' => [
                    'method' => $ep['method'],
                    'header' => $this->postmanHeaders($ep),
                    'url' => [
                        'raw' => $this->baseUrl . $ep['path'] . ($ep['query'] ?? ''),
                        'host' => ['{{base_url}}'],
                        'path' => array_filter(explode('/', $ep['path'])),
                        'query' => $ep['queryParams'] ?? [],
                    ],
                    'body' => $ep['body'] ?? null,
                    'description' => $ep['description'] ?? '',
                ],
            ];
        }

        foreach ($folders as $name => $folderItems) {
            $items[] = [
                'name' => $name,
                'item' => $folderItems,
            ];
        }

        $collection = [
            'info' => [
                'name' => 'LRV Cloud Manager - Public API v1',
                '_postman_id' => 'lrv-api-v1-' . date('Ymd'),
                'description' => 'API Pública do LRV Cloud Manager. Autenticação via X-API-Key ou Bearer Token.',
                'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
            ],
            'auth' => [
                'type' => 'apikey',
                'apikey' => [
                    ['key' => 'key', 'value' => 'X-API-Key', 'type' => 'string'],
                    ['key' => 'value', 'value' => '{{api_key}}', 'type' => 'string'],
                    ['key' => 'in', 'value' => 'header', 'type' => 'string'],
                ],
            ],
            'variable' => [
                ['key' => 'base_url', 'value' => 'https://seudominio.com', 'type' => 'string'],
                ['key' => 'api_key', 'value' => 'lrv_live_sua_chave_aqui', 'type' => 'string'],
                ['key' => 'access_token', 'value' => '', 'type' => 'string'],
                ['key' => 'environment', 'value' => 'production', 'type' => 'string'],
            ],
            'item' => $items,
        ];

        return json_encode($collection, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Gera coleção no formato Bruno (.json export).
     */
    public function gerarBruno(): string
    {
        $items = [];

        foreach ($this->endpoints as $ep) {
            $items[] = [
                'type' => 'http',
                'name' => $ep['name'],
                'seq' => count($items) + 1,
                'request' => [
                    'method' => $ep['method'],
                    'url' => '{{base_url}}' . $ep['path'],
                    'headers' => [
                        ['name' => 'X-API-Key', 'value' => '{{api_key}}', 'enabled' => true],
                        ['name' => 'Content-Type', 'value' => 'application/json', 'enabled' => true],
                    ],
                    'body' => $ep['body_raw'] ?? null,
                ],
                'folder' => $ep['folder'],
            ];
        }

        $collection = [
            'name' => 'LRV Cloud Manager API v1',
            'version' => '1',
            'type' => 'collection',
            'items' => $items,
            'environments' => [
                [
                    'name' => 'Production',
                    'variables' => [
                        ['name' => 'base_url', 'value' => 'https://seudominio.com'],
                        ['name' => 'api_key', 'value' => 'lrv_live_...'],
                    ],
                ],
                [
                    'name' => 'Sandbox',
                    'variables' => [
                        ['name' => 'base_url', 'value' => 'https://seudominio.com'],
                        ['name' => 'api_key', 'value' => 'lrv_test_...'],
                    ],
                ],
            ],
        ];

        return json_encode($collection, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Gera exportação compatível com Insomnia v4.
     */
    public function gerarInsomnia(): string
    {
        $resources = [];
        $workspaceId = 'wrk_lrv_api_v1';

        $resources[] = [
            '_type' => 'workspace',
            '_id' => $workspaceId,
            'name' => 'LRV Cloud Manager API v1',
            'description' => 'API Pública do LRV Cloud Manager',
        ];

        // Ambiente Production
        $resources[] = [
            '_type' => 'environment',
            '_id' => 'env_production',
            'parentId' => $workspaceId,
            'name' => 'Production',
            'data' => [
                'base_url' => 'https://seudominio.com',
                'api_key' => 'lrv_live_sua_chave',
            ],
        ];

        // Ambiente Sandbox
        $resources[] = [
            '_type' => 'environment',
            '_id' => 'env_sandbox',
            'parentId' => $workspaceId,
            'name' => 'Sandbox',
            'data' => [
                'base_url' => 'https://seudominio.com',
                'api_key' => 'lrv_test_sua_chave',
            ],
        ];

        // Pastas
        $folderIds = [];
        foreach ($this->endpoints as $ep) {
            $folder = $ep['folder'];
            if (!isset($folderIds[$folder])) {
                $folderId = 'fld_' . strtolower(str_replace(' ', '_', $folder));
                $folderIds[$folder] = $folderId;
                $resources[] = [
                    '_type' => 'request_group',
                    '_id' => $folderId,
                    'parentId' => $workspaceId,
                    'name' => $folder,
                ];
            }
        }

        // Requests
        foreach ($this->endpoints as $i => $ep) {
            $reqId = 'req_' . ($i + 1);
            $folderId = $folderIds[$ep['folder']] ?? $workspaceId;

            $headers = [
                ['name' => 'X-API-Key', 'value' => '{{ _.api_key }}'],
                ['name' => 'Content-Type', 'value' => 'application/json'],
            ];

            $resource = [
                '_type' => 'request',
                '_id' => $reqId,
                'parentId' => $folderId,
                'name' => $ep['name'],
                'method' => $ep['method'],
                'url' => '{{ _.base_url }}' . $ep['path'],
                'headers' => $headers,
            ];

            if (isset($ep['body_raw'])) {
                $resource['body'] = [
                    'mimeType' => 'application/json',
                    'text' => $ep['body_raw'],
                ];
            }

            $resources[] = $resource;
        }

        return json_encode([
            '_type' => 'export',
            '__export_format' => 4,
            '__export_date' => date('c'),
            '__export_source' => 'lrv_cloud_manager',
            'resources' => $resources,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function postmanHeaders(array $ep): array
    {
        $headers = [
            ['key' => 'X-API-Key', 'value' => '{{api_key}}', 'type' => 'text'],
        ];
        if (in_array($ep['method'], ['POST', 'PUT', 'PATCH'], true)) {
            $headers[] = ['key' => 'Content-Type', 'value' => 'application/json', 'type' => 'text'];
        }
        return $headers;
    }

    private function definirEndpoints(): array
    {
        return [
            // Auth
            ['folder' => 'Authentication', 'name' => 'Issue Tokens', 'method' => 'POST', 'path' => '/api/v1/auth/token', 'description' => 'Exchange API Key for access + refresh tokens', 'body_raw' => '{"api_key": "lrv_live_your_key"}'],
            ['folder' => 'Authentication', 'name' => 'Refresh Token', 'method' => 'POST', 'path' => '/api/v1/auth/refresh', 'body_raw' => '{"refresh_token": "your_refresh_token"}'],
            ['folder' => 'Authentication', 'name' => 'Revoke Tokens', 'method' => 'POST', 'path' => '/api/v1/auth/revoke'],

            // Keys
            ['folder' => 'API Keys', 'name' => 'List Keys', 'method' => 'GET', 'path' => '/api/v1/keys'],
            ['folder' => 'API Keys', 'name' => 'Create Key', 'method' => 'POST', 'path' => '/api/v1/keys', 'body_raw' => '{"name": "My App", "environment": "production", "scopes": ["hosting.read", "tickets.read"]}'],
            ['folder' => 'API Keys', 'name' => 'Revoke Key', 'method' => 'POST', 'path' => '/api/v1/keys/revoke', 'query' => '?id=1'],
            ['folder' => 'API Keys', 'name' => 'Rotate Key', 'method' => 'POST', 'path' => '/api/v1/keys/rotate', 'query' => '?id=1'],

            // Hosting
            ['folder' => 'Hosting', 'name' => 'List VPS', 'method' => 'GET', 'path' => '/api/v1/hosting'],
            ['folder' => 'Hosting', 'name' => 'VPS Details', 'method' => 'GET', 'path' => '/api/v1/hosting/show', 'query' => '?id=1'],
            ['folder' => 'Hosting', 'name' => 'Restart VPS', 'method' => 'POST', 'path' => '/api/v1/hosting/restart', 'query' => '?id=1'],
            ['folder' => 'Hosting', 'name' => 'VPS Metrics', 'method' => 'GET', 'path' => '/api/v1/hosting/metrics', 'query' => '?id=1&hours=24'],

            // Tickets
            ['folder' => 'Tickets', 'name' => 'List Tickets', 'method' => 'GET', 'path' => '/api/v1/tickets'],
            ['folder' => 'Tickets', 'name' => 'Ticket Details', 'method' => 'GET', 'path' => '/api/v1/tickets/show', 'query' => '?id=1'],
            ['folder' => 'Tickets', 'name' => 'Create Ticket', 'method' => 'POST', 'path' => '/api/v1/tickets', 'body_raw' => '{"subject": "Server issue", "message": "Details...", "priority": "high", "department": "suporte"}'],
            ['folder' => 'Tickets', 'name' => 'Reply to Ticket', 'method' => 'POST', 'path' => '/api/v1/tickets/reply', 'body_raw' => '{"ticket_id": 1, "message": "Additional info..."}'],
            ['folder' => 'Tickets', 'name' => 'Close Ticket', 'method' => 'POST', 'path' => '/api/v1/tickets/close', 'query' => '?id=1'],

            // Subscriptions
            ['folder' => 'Subscriptions', 'name' => 'List Subscriptions', 'method' => 'GET', 'path' => '/api/v1/subscriptions'],
            ['folder' => 'Subscriptions', 'name' => 'Subscription Details', 'method' => 'GET', 'path' => '/api/v1/subscriptions/show', 'query' => '?id=1'],
            ['folder' => 'Subscriptions', 'name' => 'Subscription Invoices', 'method' => 'GET', 'path' => '/api/v1/subscriptions/invoices', 'query' => '?subscription_id=1'],

            // Domains
            ['folder' => 'Domains', 'name' => 'List Domains', 'method' => 'GET', 'path' => '/api/v1/domains'],
            ['folder' => 'Domains', 'name' => 'Domain Details', 'method' => 'GET', 'path' => '/api/v1/domains/show', 'query' => '?id=1'],
            ['folder' => 'Domains', 'name' => 'Add Domain', 'method' => 'POST', 'path' => '/api/v1/domains', 'body_raw' => '{"domain": "example.com", "vps_id": 1, "type": "addon"}'],
            ['folder' => 'Domains', 'name' => 'Remove Domain', 'method' => 'POST', 'path' => '/api/v1/domains/remove', 'query' => '?id=1'],

            // Databases
            ['folder' => 'Databases', 'name' => 'List Databases', 'method' => 'GET', 'path' => '/api/v1/databases'],
            ['folder' => 'Databases', 'name' => 'Create Database', 'method' => 'POST', 'path' => '/api/v1/databases', 'body_raw' => '{"vps_id": 1, "db_name": "myapp_db", "db_type": "mysql"}'],
            ['folder' => 'Databases', 'name' => 'Remove Database', 'method' => 'POST', 'path' => '/api/v1/databases/remove', 'query' => '?id=1'],

            // Backups
            ['folder' => 'Backups', 'name' => 'List Backups', 'method' => 'GET', 'path' => '/api/v1/backups'],
            ['folder' => 'Backups', 'name' => 'Create Backup', 'method' => 'POST', 'path' => '/api/v1/backups', 'body_raw' => '{"vps_id": 1}'],
            ['folder' => 'Backups', 'name' => 'Restore Backup', 'method' => 'POST', 'path' => '/api/v1/backups/restore', 'body_raw' => '{"backup_id": 1}'],

            // Applications
            ['folder' => 'Applications', 'name' => 'List Applications', 'method' => 'GET', 'path' => '/api/v1/applications'],
            ['folder' => 'Applications', 'name' => 'Application Catalog', 'method' => 'GET', 'path' => '/api/v1/applications/catalog'],
            ['folder' => 'Applications', 'name' => 'Install Application', 'method' => 'POST', 'path' => '/api/v1/applications/install', 'body_raw' => '{"template_id": 1, "vps_id": 1, "domain": "app.example.com"}'],
            ['folder' => 'Applications', 'name' => 'Application Status', 'method' => 'GET', 'path' => '/api/v1/applications/status', 'query' => '?id=1'],

            // Emails
            ['folder' => 'Emails', 'name' => 'List Emails', 'method' => 'GET', 'path' => '/api/v1/emails'],
            ['folder' => 'Emails', 'name' => 'Create Email', 'method' => 'POST', 'path' => '/api/v1/emails', 'body_raw' => '{"email_address": "user@example.com", "password": "SecurePass123!", "quota_mb": 1024}'],
            ['folder' => 'Emails', 'name' => 'Remove Email', 'method' => 'POST', 'path' => '/api/v1/emails/remove', 'query' => '?id=1'],

            // Webhooks
            ['folder' => 'Webhooks', 'name' => 'List Webhooks', 'method' => 'GET', 'path' => '/api/v1/webhooks'],
            ['folder' => 'Webhooks', 'name' => 'Available Events', 'method' => 'GET', 'path' => '/api/v1/webhooks/events'],
            ['folder' => 'Webhooks', 'name' => 'Create Webhook', 'method' => 'POST', 'path' => '/api/v1/webhooks', 'body_raw' => '{"url": "https://your-server.com/webhook", "events": ["ticket.created", "payment.received"]}'],
            ['folder' => 'Webhooks', 'name' => 'Delivery History', 'method' => 'GET', 'path' => '/api/v1/webhooks/deliveries', 'query' => '?webhook_id=1'],
            ['folder' => 'Webhooks', 'name' => 'Resend Delivery', 'method' => 'POST', 'path' => '/api/v1/webhooks/resend', 'query' => '?delivery_id=1'],

            // Status & Logs
            ['folder' => 'Status', 'name' => 'API Status', 'method' => 'GET', 'path' => '/api/v1/status'],
            ['folder' => 'Status', 'name' => 'Incidents', 'method' => 'GET', 'path' => '/api/v1/status/incidents'],
            ['folder' => 'Logs', 'name' => 'Request Logs', 'method' => 'GET', 'path' => '/api/v1/logs'],
            ['folder' => 'Changelog', 'name' => 'API Changelog', 'method' => 'GET', 'path' => '/api/v1/changelog'],
        ];
    }
}
