<!DOCTYPE html>
<html lang="<?= \LRV\Core\I18n::idioma() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Explorer — LRV Cloud Manager</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui.css">
    <style>
        body { margin: 0; padding: 0; background: #1a1a2e; }
        #swagger-ui .topbar { display: none; }
        #swagger-ui .swagger-ui { max-width: 1200px; margin: 0 auto; }
        .swagger-ui .info .title { color: #e2e8f0; }
        .swagger-ui .scheme-container { background: #16213e; }
        .header-bar {
            background: #16213e;
            padding: 16px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #2a3a5e;
        }
        .header-bar a { color: #60a5fa; text-decoration: none; font-weight: 500; }
        .header-bar h1 { color: #e2e8f0; margin: 0; font-size: 1.2rem; }
        .header-bar .nav-links { display: flex; gap: 24px; }
    </style>
</head>
<body>
    <div class="header-bar">
        <h1>🔗 LRV Cloud Manager API</h1>
        <div class="nav-links">
            <a href="/developers/api"><?= \LRV\Core\I18n::t('api_docs.documentacao') ?></a>
            <a href="/api/v1/openapi.yaml" download>OpenAPI YAML</a>
            <a href="/"><?= \LRV\Core\I18n::t('nav.inicio') ?></a>
        </div>
    </div>

    <div id="swagger-ui"></div>

    <script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
    <script>
        SwaggerUIBundle({
            url: '/api/v1/openapi.yaml',
            dom_id: '#swagger-ui',
            deepLinking: true,
            presets: [
                SwaggerUIBundle.presets.apis,
                SwaggerUIBundle.SwaggerUIStandalonePreset
            ],
            layout: 'BaseLayout',
            persistAuthorization: true,
            tryItOutEnabled: true
        });
    </script>
</body>
</html>
