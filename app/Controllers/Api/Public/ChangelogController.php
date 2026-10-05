<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Api\Public;

use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;

/**
 * Endpoint de Changelog da API Pública.
 * - GET /api/v1/changelog → Lista de versões e mudanças
 */
final class ChangelogController extends BaseApiController
{
    /**
     * Changelog estático (pode ser movido para BD no futuro).
     */
    public function index(Requisicao $req): Resposta
    {
        $changelog = [
            [
                'version' => '1.1.0',
                'date' => '2026-10-03',
                'type' => 'minor',
                'changes' => [
                    'new' => [
                        'Clients endpoints: register and manage end clients (GET/POST /clients, GET /clients/show)',
                        'VPS provisioning via API (POST /hosting), plus suspend and stop (POST /hosting/suspend, /hosting/stop)',
                        'Git-based application deploy (POST /applications/git, /git/deploy, GET /applications/git/show)',
                        'Staging environment with temporary subdomain and automatic SSL (staging_subdomain flag)',
                        'New webhook events: client.created, hosting.created, hosting.ready, application.installed, application.deployed',
                        'Documented POST /databases schema (credentials returned once at creation)',
                        'Git repositories: create repo in the organization and manage collaborators (POST /git/repositories, /collaborators)',
                        'New webhook events: git.repository.created, git.collaborator.added, git.collaborator.removed',
                    ],
                    'changed' => [],
                    'deprecated' => [],
                    'fixed' => [],
                ],
            ],
            [
                'version' => '1.0.0',
                'date' => '2026-07-09',
                'type' => 'major',
                'changes' => [
                    'new' => [
                        'Public API v1 launched',
                        'Authentication via API Keys and Bearer Tokens',
                        'Endpoints: Hosting, Tickets, Subscriptions, Domains, Webhooks, Status',
                        'Endpoints: Databases, Backups, Applications, Emails',
                        'Rate limiting per API Key (configurable)',
                        'Webhook system with HMAC SHA-256 signatures',
                        'Full request logging and audit trail',
                        'Sandbox and Production environments',
                        'OpenAPI 3.1 specification',
                        'Swagger UI interactive documentation',
                        'API Explorer / Playground',
                    ],
                    'changed' => [],
                    'deprecated' => [],
                    'fixed' => [],
                ],
            ],
        ];

        return $this->sucesso([
            'versions' => $changelog,
            'current_version' => '1.1.0',
            'api_status' => 'stable',
        ]);
    }
}
