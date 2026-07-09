<?php

declare(strict_types=1);

/**
 * ROTAS DA API PÚBLICA v1
 * Prefixo: /api/v1/
 *
 * Autenticação: API Key (header X-API-Key) ou Bearer Token (Authorization: Bearer <token>)
 * Rate Limit: Configurável por API Key (padrão: 60 req/min)
 * CSRF: Não se aplica (sem sessão)
 * Formato: JSON
 */

use LRV\App\Controllers\Api\Public\AuthController;
use LRV\App\Controllers\Api\Public\ApiKeysController;
use LRV\App\Controllers\Api\Public\HostingController;
use LRV\App\Controllers\Api\Public\TicketsController;
use LRV\App\Controllers\Api\Public\SubscriptionsController;
use LRV\App\Controllers\Api\Public\DomainsController;
use LRV\App\Controllers\Api\Public\WebhooksController;
use LRV\App\Controllers\Api\Public\StatusApiController;
use LRV\App\Controllers\Api\Public\DatabasesController;
use LRV\App\Controllers\Api\Public\BackupsController;
use LRV\App\Controllers\Api\Public\ApplicationsController;
use LRV\App\Controllers\Api\Public\EmailsController;
use LRV\App\Controllers\Api\Public\LogsController;
use LRV\App\Controllers\Api\Public\ChangelogController;
use LRV\Core\ApiMiddleware;

// ── Middlewares base para todos os endpoints autenticados ──
$apiAuth = [
    ApiMiddleware::cors(),
    ApiMiddleware::securityHeaders(),
    ApiMiddleware::iniciarTimer(),
    ApiMiddleware::autenticar(),
    ApiMiddleware::rateLimitApiKey(),
    ApiMiddleware::sandboxGuard(),
];

// ── Middlewares para endpoints públicos (sem auth) ──
$apiPublic = [
    ApiMiddleware::cors(),
    ApiMiddleware::securityHeaders(),
    ApiMiddleware::iniciarTimer(),
    ApiMiddleware::rateLimitIpApi(30, 60),
];

// ════════════════════════════════════════════════════════════
// AUTH — Emissão e renovação de tokens
// ════════════════════════════════════════════════════════════
$roteador->post('/api/v1/auth/token', [AuthController::class, 'emitirToken'], $apiPublic);
$roteador->post('/api/v1/auth/refresh', [AuthController::class, 'renovarToken'], $apiPublic);
$roteador->post('/api/v1/auth/revoke', [AuthController::class, 'revogar'], $apiAuth);

// ════════════════════════════════════════════════════════════
// API KEYS — Gerenciamento de chaves
// ════════════════════════════════════════════════════════════
$roteador->get('/api/v1/keys', [ApiKeysController::class, 'listar'], $apiAuth);
$roteador->post('/api/v1/keys', [ApiKeysController::class, 'criar'], $apiAuth);
$roteador->post('/api/v1/keys/revoke', [ApiKeysController::class, 'revogar'], $apiAuth);
$roteador->post('/api/v1/keys/rotate', [ApiKeysController::class, 'rotacionar'], $apiAuth);

// ════════════════════════════════════════════════════════════
// HOSTING (VPS)
// ════════════════════════════════════════════════════════════
$roteador->get('/api/v1/hosting', [HostingController::class, 'listar'], $apiAuth);
$roteador->get('/api/v1/hosting/show', [HostingController::class, 'show'], $apiAuth);
$roteador->post('/api/v1/hosting/restart', [HostingController::class, 'reiniciar'], $apiAuth);
$roteador->get('/api/v1/hosting/metrics', [HostingController::class, 'metricas'], $apiAuth);

// ════════════════════════════════════════════════════════════
// TICKETS
// ════════════════════════════════════════════════════════════
$roteador->get('/api/v1/tickets', [TicketsController::class, 'listar'], $apiAuth);
$roteador->get('/api/v1/tickets/show', [TicketsController::class, 'show'], $apiAuth);
$roteador->post('/api/v1/tickets', [TicketsController::class, 'criar'], $apiAuth);
$roteador->post('/api/v1/tickets/reply', [TicketsController::class, 'responder'], $apiAuth);
$roteador->post('/api/v1/tickets/close', [TicketsController::class, 'fechar'], $apiAuth);

// ════════════════════════════════════════════════════════════
// SUBSCRIPTIONS (BILLING)
// ════════════════════════════════════════════════════════════
$roteador->get('/api/v1/subscriptions', [SubscriptionsController::class, 'listar'], $apiAuth);
$roteador->get('/api/v1/subscriptions/show', [SubscriptionsController::class, 'show'], $apiAuth);
$roteador->get('/api/v1/subscriptions/invoices', [SubscriptionsController::class, 'faturas'], $apiAuth);

// ════════════════════════════════════════════════════════════
// DOMAINS
// ════════════════════════════════════════════════════════════
$roteador->get('/api/v1/domains', [DomainsController::class, 'listar'], $apiAuth);
$roteador->get('/api/v1/domains/show', [DomainsController::class, 'show'], $apiAuth);
$roteador->post('/api/v1/domains', [DomainsController::class, 'criar'], $apiAuth);
$roteador->post('/api/v1/domains/remove', [DomainsController::class, 'remover'], $apiAuth);

// ════════════════════════════════════════════════════════════
// DATABASES
// ════════════════════════════════════════════════════════════
$roteador->get('/api/v1/databases', [DatabasesController::class, 'listar'], $apiAuth);
$roteador->post('/api/v1/databases', [DatabasesController::class, 'criar'], $apiAuth);
$roteador->post('/api/v1/databases/remove', [DatabasesController::class, 'remover'], $apiAuth);

// ════════════════════════════════════════════════════════════
// BACKUPS
// ════════════════════════════════════════════════════════════
$roteador->get('/api/v1/backups', [BackupsController::class, 'listar'], $apiAuth);
$roteador->post('/api/v1/backups', [BackupsController::class, 'criar'], $apiAuth);
$roteador->post('/api/v1/backups/restore', [BackupsController::class, 'restaurar'], $apiAuth);

// ════════════════════════════════════════════════════════════
// APPLICATIONS
// ════════════════════════════════════════════════════════════
$roteador->get('/api/v1/applications', [ApplicationsController::class, 'listar'], $apiAuth);
$roteador->get('/api/v1/applications/catalog', [ApplicationsController::class, 'catalogo'], $apiPublic);
$roteador->post('/api/v1/applications/install', [ApplicationsController::class, 'instalar'], $apiAuth);
$roteador->get('/api/v1/applications/status', [ApplicationsController::class, 'status'], $apiAuth);

// ════════════════════════════════════════════════════════════
// EMAILS
// ════════════════════════════════════════════════════════════
$roteador->get('/api/v1/emails', [EmailsController::class, 'listar'], $apiAuth);
$roteador->post('/api/v1/emails', [EmailsController::class, 'criar'], $apiAuth);
$roteador->post('/api/v1/emails/remove', [EmailsController::class, 'remover'], $apiAuth);

// ════════════════════════════════════════════════════════════
// LOGS
// ════════════════════════════════════════════════════════════
$roteador->get('/api/v1/logs', [LogsController::class, 'listar'], $apiAuth);

// ════════════════════════════════════════════════════════════
// CHANGELOG
// ════════════════════════════════════════════════════════════
$roteador->get('/api/v1/changelog', [ChangelogController::class, 'index'], $apiPublic);

// ════════════════════════════════════════════════════════════
// WEBHOOKS
// ════════════════════════════════════════════════════════════
$roteador->get('/api/v1/webhooks', [WebhooksController::class, 'listar'], $apiAuth);
$roteador->post('/api/v1/webhooks', [WebhooksController::class, 'criar'], $apiAuth);
$roteador->post('/api/v1/webhooks/update', [WebhooksController::class, 'atualizar'], $apiAuth);
$roteador->post('/api/v1/webhooks/remove', [WebhooksController::class, 'remover'], $apiAuth);
$roteador->get('/api/v1/webhooks/events', [WebhooksController::class, 'eventos'], $apiPublic);
$roteador->get('/api/v1/webhooks/deliveries', [WebhooksController::class, 'deliveries'], $apiAuth);
$roteador->post('/api/v1/webhooks/resend', [WebhooksController::class, 'reenviar'], $apiAuth);

// ════════════════════════════════════════════════════════════
// STATUS (público)
// ════════════════════════════════════════════════════════════
$roteador->get('/api/v1/status', [StatusApiController::class, 'index'], $apiPublic);
$roteador->get('/api/v1/status/incidents', [StatusApiController::class, 'incidentes'], $apiPublic);

// ════════════════════════════════════════════════════════════
// DOCUMENTAÇÃO & OPENAPI SPEC
// ════════════════════════════════════════════════════════════
use LRV\App\Controllers\Api\Public\DocsController;
use LRV\App\Controllers\Api\Public\ExportController;

$roteador->get('/developers', [DocsController::class, 'landing']);
$roteador->get('/developers/api', [DocsController::class, 'index']);
$roteador->get('/developers/api/swagger', [DocsController::class, 'swagger']);
$roteador->get('/developers/api/changelog', [DocsController::class, 'changelog']);
$roteador->get('/developers/api/status', [DocsController::class, 'statusPage']);
$roteador->get('/api/v1/openapi.json', [DocsController::class, 'openapiJson'], $apiPublic);
$roteador->get('/api/v1/openapi.yaml', [DocsController::class, 'openapiYaml'], $apiPublic);

// ── Downloads de Coleções ──
$roteador->get('/developers/api/postman.json', [ExportController::class, 'postman']);
$roteador->get('/developers/api/bruno.json', [ExportController::class, 'bruno']);
$roteador->get('/developers/api/insomnia.json', [ExportController::class, 'insomnia']);
