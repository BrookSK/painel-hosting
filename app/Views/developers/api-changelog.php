<?php
$t = fn(string $k) => \LRV\Core\I18n::t($k);
?>
<!DOCTYPE html>
<html lang="<?= \LRV\Core\I18n::idioma() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Changelog — LRV Cloud Manager</title>
    <?php require __DIR__ . '/../_partials/estilo.php'; ?>
    <style>
        .changelog-container { max-width: 800px; margin: 0 auto; padding: 40px 24px; }
        .version-block { margin-bottom: 40px; }
        .version-header {
            display: flex; align-items: center; gap: 12px; margin-bottom: 16px;
        }
        .version-tag {
            background: #1e3a5f; color: #60a5fa; padding: 4px 12px; border-radius: 6px;
            font-weight: 700; font-family: monospace;
        }
        .version-date { color: #64748b; font-size: 0.9rem; }
        .version-badge-major { background: #7c3aed20; color: #a78bfa; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600; }
        .version-badge-minor { background: #059669; color: #6ee7b7; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600; }
        .version-badge-patch { background: #d9770620; color: #fbbf24; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600; }
        .changes-section { margin-bottom: 12px; }
        .changes-section h4 { color: #e2e8f0; font-size: 0.9rem; margin-bottom: 8px; }
        .changes-section ul { list-style: none; padding: 0; }
        .changes-section li {
            padding: 6px 0; color: #94a3b8; font-size: 0.9rem;
            border-bottom: 1px solid #1e293b;
        }
        .changes-section li::before { content: '•'; color: #3b82f6; margin-right: 8px; }
        .tag-new::before { content: '✦'; color: #22c55e !important; }
        .tag-changed::before { content: '↻'; color: #f59e0b !important; }
        .tag-deprecated::before { content: '⚠'; color: #ef4444 !important; }
        .tag-fixed::before { content: '✓'; color: #06b6d4 !important; }
    </style>
</head>
<body>
    <?php require __DIR__ . '/../_partials/navbar-publica.php'; ?>

    <div class="changelog-container">
        <h1 style="color: #e2e8f0; margin-bottom: 8px;">API Changelog</h1>
        <p style="color: #94a3b8; margin-bottom: 32px;">Histórico de versões e mudanças da API pública.</p>

        <div class="version-block">
            <div class="version-header">
                <span class="version-tag">v1.0.0</span>
                <span class="version-date">2026-07-09</span>
                <span class="version-badge-major">Major</span>
            </div>

            <div class="changes-section">
                <h4>🆕 Novidades</h4>
                <ul>
                    <li class="tag-new">API Pública v1 lançada</li>
                    <li class="tag-new">Autenticação via API Keys e Bearer Tokens</li>
                    <li class="tag-new">Endpoints: Hosting, Tickets, Subscriptions, Domains</li>
                    <li class="tag-new">Endpoints: Databases, Backups, Applications, Emails</li>
                    <li class="tag-new">Sistema de Webhooks com assinatura HMAC SHA-256</li>
                    <li class="tag-new">Rate limiting configurável por API Key</li>
                    <li class="tag-new">Logging completo de requisições</li>
                    <li class="tag-new">Ambientes Sandbox e Production separados</li>
                    <li class="tag-new">Especificação OpenAPI 3.1</li>
                    <li class="tag-new">Swagger UI / API Explorer interativo</li>
                    <li class="tag-new">Coleções Postman, Bruno e Insomnia</li>
                    <li class="tag-new">Documentação pública multilíngue (PT/EN/ES)</li>
                    <li class="tag-new">SDKs: PHP, JavaScript/TypeScript, Python (estrutura base)</li>
                </ul>
            </div>
        </div>

        <div style="text-align:center;margin-top:32px;">
            <a href="/developers/api" style="color:#3b82f6;">← <?= $t('api_docs.documentacao') ?></a>
            &nbsp;&nbsp;|&nbsp;&nbsp;
            <a href="/developers/api/swagger" style="color:#3b82f6;">API Explorer →</a>
        </div>
    </div>

    <?php require __DIR__ . '/../_partials/cookie-banner.php'; ?>
</body>
</html>
