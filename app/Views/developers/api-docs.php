<?php
/** @var string $titulo */
$t = fn(string $k) => \LRV\Core\I18n::t($k);
?>
<!DOCTYPE html>
<html lang="<?= \LRV\Core\I18n::idioma() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo) ?> — LRV Cloud Manager</title>
    <?php require __DIR__ . '/../_partials/estilo.php'; ?>
    <style>
        .docs-container { max-width: 960px; margin: 0 auto; padding: 40px 24px; }
        .docs-nav { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 40px; }
        .docs-nav a {
            padding: 8px 16px; border-radius: 8px;
            background: var(--bg-card, #1e293b); color: var(--text-secondary, #94a3b8);
            text-decoration: none; font-size: 0.9rem; transition: all 0.2s;
        }
        .docs-nav a:hover, .docs-nav a.active { background: var(--primary, #3b82f6); color: #fff; }
        .docs-section { margin-bottom: 48px; }
        .docs-section h2 { color: var(--text-primary, #e2e8f0); margin-bottom: 16px; }
        .docs-section p, .docs-section li { color: var(--text-secondary, #94a3b8); line-height: 1.8; }
        .code-block {
            background: #0f172a; border: 1px solid #334155; border-radius: 8px;
            padding: 16px; overflow-x: auto; font-family: 'JetBrains Mono', monospace;
            font-size: 0.85rem; color: #e2e8f0; margin: 16px 0;
        }
        .endpoint-badge {
            display: inline-block; padding: 2px 8px; border-radius: 4px;
            font-size: 0.75rem; font-weight: 700; margin-right: 8px;
        }
        .badge-get { background: #065f46; color: #6ee7b7; }
        .badge-post { background: #1e3a5f; color: #60a5fa; }
        .badge-delete { background: #7f1d1d; color: #fca5a5; }
        .quick-start { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; }
        .quick-card {
            background: var(--bg-card, #1e293b); border-radius: 12px; padding: 20px;
            border: 1px solid #334155; transition: border-color 0.2s;
        }
        .quick-card:hover { border-color: var(--primary, #3b82f6); }
        .quick-card h3 { color: var(--text-primary, #e2e8f0); margin-bottom: 8px; }
        .quick-card p { font-size: 0.9rem; }
        .downloads { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 16px; }
        .downloads a {
            display: inline-flex; align-items: center; gap: 8px; padding: 10px 16px;
            background: var(--bg-card, #1e293b); border: 1px solid #334155; border-radius: 8px;
            color: var(--text-primary, #e2e8f0); text-decoration: none; font-size: 0.9rem;
        }
        .downloads a:hover { border-color: var(--primary, #3b82f6); }
    </style>
</head>
<body>
    <?php require __DIR__ . '/../_partials/navbar-publica.php'; ?>

    <div class="docs-container">
        <h1 style="color: var(--text-primary, #e2e8f0); margin-bottom: 8px;"><?= $t('api_docs.titulo') ?></h1>
        <p style="color: var(--text-secondary, #94a3b8); margin-bottom: 32px;"><?= $t('api_docs.subtitulo') ?></p>

        <nav class="docs-nav">
            <a href="#introducao" class="active"><?= $t('api_docs.nav_intro') ?></a>
            <a href="#autenticacao"><?= $t('api_docs.nav_auth') ?></a>
            <a href="#rate-limit"><?= $t('api_docs.nav_rate_limit') ?></a>
            <a href="#endpoints"><?= $t('api_docs.nav_endpoints') ?></a>
            <a href="#webhooks"><?= $t('api_docs.nav_webhooks') ?></a>
            <a href="#erros"><?= $t('api_docs.nav_erros') ?></a>
            <a href="#sdks"><?= $t('api_docs.nav_sdks') ?></a>
            <a href="/developers/api/swagger"><?= $t('api_docs.nav_playground') ?></a>
        </nav>

        <!-- Introdução -->
        <section class="docs-section" id="introducao">
            <h2><?= $t('api_docs.intro_titulo') ?></h2>
            <p><?= $t('api_docs.intro_desc') ?></p>

            <div class="code-block">
curl -H "X-API-Key: lrv_live_sua_chave_aqui" \
     https://seudominio.com/api/v1/hosting
            </div>
        </section>

        <!-- Autenticação -->
        <section class="docs-section" id="autenticacao">
            <h2><?= $t('api_docs.auth_titulo') ?></h2>
            <p><?= $t('api_docs.auth_desc') ?></p>

            <h3><?= $t('api_docs.auth_api_key') ?></h3>
            <div class="code-block">
# <?= $t('api_docs.auth_via_header') ?>
curl -H "X-API-Key: lrv_live_sua_chave" \
     https://seudominio.com/api/v1/hosting
            </div>

            <h3><?= $t('api_docs.auth_bearer') ?></h3>
            <div class="code-block">
# <?= $t('api_docs.auth_obter_token') ?>
curl -X POST https://seudominio.com/api/v1/auth/token \
     -H "Content-Type: application/json" \
     -d '{"api_key": "lrv_live_sua_chave"}'

# <?= $t('api_docs.auth_usar_token') ?>
curl -H "Authorization: Bearer seu_access_token" \
     https://seudominio.com/api/v1/tickets
            </div>
        </section>

        <!-- Rate Limit -->
        <section class="docs-section" id="rate-limit">
            <h2><?= $t('api_docs.rate_titulo') ?></h2>
            <p><?= $t('api_docs.rate_desc') ?></p>
            <div class="code-block">
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 58
X-RateLimit-Reset: 1720540800
            </div>
        </section>

        <!-- Endpoints -->
        <section class="docs-section" id="endpoints">
            <h2><?= $t('api_docs.endpoints_titulo') ?></h2>
            <p><?= $t('api_docs.endpoints_desc') ?></p>

            <div style="margin-top: 16px;">
                <p><span class="endpoint-badge badge-get">GET</span> <code>/api/v1/hosting</code> — <?= $t('api_docs.ep_hosting_list') ?></p>
                <p><span class="endpoint-badge badge-get">GET</span> <code>/api/v1/tickets</code> — <?= $t('api_docs.ep_tickets_list') ?></p>
                <p><span class="endpoint-badge badge-post">POST</span> <code>/api/v1/tickets</code> — <?= $t('api_docs.ep_tickets_create') ?></p>
                <p><span class="endpoint-badge badge-get">GET</span> <code>/api/v1/subscriptions</code> — <?= $t('api_docs.ep_subs_list') ?></p>
                <p><span class="endpoint-badge badge-get">GET</span> <code>/api/v1/domains</code> — <?= $t('api_docs.ep_domains_list') ?></p>
                <p><span class="endpoint-badge badge-post">POST</span> <code>/api/v1/domains</code> — <?= $t('api_docs.ep_domains_create') ?></p>
                <p><span class="endpoint-badge badge-get">GET</span> <code>/api/v1/webhooks</code> — <?= $t('api_docs.ep_webhooks_list') ?></p>
                <p><span class="endpoint-badge badge-get">GET</span> <code>/api/v1/status</code> — <?= $t('api_docs.ep_status') ?></p>
            </div>

            <p style="margin-top: 16px;">
                <a href="/developers/api/swagger" style="color: var(--primary, #3b82f6);"><?= $t('api_docs.ver_todos_endpoints') ?></a>
            </p>
        </section>

        <!-- Webhooks -->
        <section class="docs-section" id="webhooks">
            <h2><?= $t('api_docs.webhooks_titulo') ?></h2>
            <p><?= $t('api_docs.webhooks_desc') ?></p>

            <div class="code-block">
// <?= $t('api_docs.webhooks_validar') ?>
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'] ?? '';
$expected = 'sha256=' . hash_hmac('sha256', $payload, $secret);
$valid = hash_equals($expected, $signature);
            </div>
        </section>

        <!-- Erros -->
        <section class="docs-section" id="erros">
            <h2><?= $t('api_docs.erros_titulo') ?></h2>
            <div class="code-block">
{
    "success": false,
    "error": {
        "code": "VALIDATION_ERROR",
        "message": "The given data was invalid.",
        "details": [
            {"field": "domain", "message": "Invalid domain format."}
        ]
    }
}
            </div>
        </section>

        <!-- SDKs e Downloads -->
        <section class="docs-section" id="sdks">
            <h2><?= $t('api_docs.sdks_titulo') ?></h2>
            <p><?= $t('api_docs.sdks_desc') ?></p>

            <h3><?= $t('api_docs.downloads_titulo') ?></h3>
            <div class="downloads">
                <a href="/api/v1/openapi.yaml" download><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg> OpenAPI YAML</a>
                <a href="/api/v1/openapi.json"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg> OpenAPI JSON</a>
                <a href="/developers/api/postman.json" download><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg> Postman Collection</a>
                <a href="/developers/api/bruno.json" download><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg> Bruno Collection</a>
                <a href="/developers/api/insomnia.json" download><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg> Insomnia Export</a>
                <a href="/developers/api/swagger"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg> API Explorer</a>
            </div>

            <h3 style="margin-top: 24px;">SDKs</h3>
            <div class="quick-start">
                <div class="quick-card">
                    <h3>PHP</h3>
                    <p><code>composer require lrv/cloud-manager-sdk</code></p>
                </div>
                <div class="quick-card">
                    <h3>JavaScript / TypeScript</h3>
                    <p><code>npm install @lrv/cloud-manager</code></p>
                </div>
                <div class="quick-card">
                    <h3>Python</h3>
                    <p><code>pip install lrv-cloud-manager</code></p>
                </div>
            </div>
        </section>
    </div>

    <?php require __DIR__ . '/../_partials/footer.php'; ?>
</body>
</html>
