<?php
declare(strict_types=1);
use LRV\Core\I18n;
use LRV\Core\View;
$t = fn(string $k) => I18n::t($k);
?>
<!DOCTYPE html>
<html lang="<?= I18n::idioma() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $t('dev_landing.titulo') ?> — LRV Cloud Manager</title>
    <?php require __DIR__ . '/../_partials/estilo.php'; ?>
    <style>
        .dev-hero {
            min-height: 70vh; display: flex; align-items: center; justify-content: center;
            text-align: center; padding: 80px 24px 60px;
            background: radial-gradient(ellipse at 50% 0%, rgba(59,130,246,.12) 0%, transparent 60%);
        }
        .dev-hero h1 { font-size: clamp(2rem, 5vw, 3.2rem); color: #e2e8f0; margin-bottom: 16px; line-height: 1.2; }
        .dev-hero p { font-size: 1.15rem; color: #94a3b8; max-width: 700px; margin: 0 auto 32px; line-height: 1.7; }
        .dev-hero-actions { display: flex; flex-wrap: wrap; gap: 12px; justify-content: center; }
        .dev-hero-actions a {
            padding: 14px 28px; border-radius: 10px; font-weight: 600; font-size: 1rem;
            text-decoration: none; transition: all .2s;
        }
        .btn-primary-dev { background: #3b82f6; color: #fff; }
        .btn-primary-dev:hover { background: #2563eb; transform: translateY(-1px); }
        .btn-ghost-dev { background: transparent; border: 1px solid #334155; color: #e2e8f0; }
        .btn-ghost-dev:hover { border-color: #3b82f6; color: #3b82f6; }

        .dev-code-preview {
            max-width: 620px; margin: 40px auto 0; background: #0f172a; border: 1px solid #1e293b;
            border-radius: 12px; padding: 20px 24px; text-align: left; overflow-x: auto;
            font-family: 'JetBrains Mono', 'Fira Code', monospace; font-size: 0.85rem;
            color: #94a3b8; line-height: 1.7;
        }
        .dev-code-preview .keyword { color: #c084fc; }
        .dev-code-preview .string { color: #86efac; }
        .dev-code-preview .comment { color: #475569; }
        .dev-code-preview .func { color: #60a5fa; }
        .dev-code-preview .var { color: #e2e8f0; }

        .dev-features {
            max-width: 1100px; margin: 0 auto; padding: 80px 24px;
            display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px;
        }
        .dev-feature-card {
            background: #16213e; border: 1px solid #1e293b; border-radius: 14px;
            padding: 28px; transition: border-color .2s, transform .2s;
        }
        .dev-feature-card:hover { border-color: #3b82f6; transform: translateY(-2px); }
        .dev-feature-icon { font-size: 2rem; margin-bottom: 12px; }
        .dev-feature-card h3 { color: #e2e8f0; margin-bottom: 8px; font-size: 1.1rem; }
        .dev-feature-card p { color: #94a3b8; font-size: 0.95rem; line-height: 1.6; }

        .dev-sandbox {
            max-width: 900px; margin: 0 auto; padding: 60px 24px;
        }
        .dev-sandbox-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-top: 32px; }
        @media (max-width: 700px) { .dev-sandbox-grid { grid-template-columns: 1fr; } }
        .sandbox-card {
            background: #16213e; border-radius: 12px; padding: 24px; border: 1px solid #1e293b;
        }
        .sandbox-card h4 { color: #e2e8f0; margin-bottom: 8px; display: flex; align-items: center; gap: 8px; }
        .sandbox-card p { color: #94a3b8; font-size: 0.9rem; line-height: 1.6; }
        .sandbox-card ul { padding-left: 20px; color: #94a3b8; font-size: 0.9rem; line-height: 2; }
        .badge-live { background: #22c55e20; color: #22c55e; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700; }
        .badge-test { background: #f59e0b20; color: #f59e0b; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700; }

        .dev-cta-section {
            text-align: center; padding: 80px 24px;
            background: radial-gradient(ellipse at 50% 100%, rgba(59,130,246,.08) 0%, transparent 50%);
        }
        .dev-cta-section h2 { color: #e2e8f0; font-size: 1.8rem; margin-bottom: 12px; }
        .dev-cta-section p { color: #94a3b8; margin-bottom: 32px; max-width: 600px; margin-left: auto; margin-right: auto; }

        .dev-quick-links {
            max-width: 900px; margin: 0 auto; padding: 0 24px 60px;
            display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px;
        }
        .dev-quick-link {
            display: flex; align-items: center; gap: 10px; padding: 14px 18px;
            background: #1e293b; border-radius: 10px; text-decoration: none;
            color: #e2e8f0; font-weight: 500; font-size: 0.9rem;
            border: 1px solid #334155; transition: border-color .2s;
        }
        .dev-quick-link:hover { border-color: #3b82f6; }
    </style>
</head>
<body>
    <?php require __DIR__ . '/../_partials/navbar-publica.php'; ?>

    <!-- HERO -->
    <section class="dev-hero">
        <div>
            <h1><?= $t('dev_landing.hero_titulo') ?></h1>
            <p><?= $t('dev_landing.hero_subtitulo') ?></p>

            <div class="dev-hero-actions">
                <a href="/developers/api" class="btn-primary-dev"><?= $t('dev_landing.cta_docs') ?></a>
                <a href="/developers/api/swagger" class="btn-ghost-dev"><?= $t('dev_landing.cta_explorer') ?></a>
                <a href="/cliente/entrar" class="btn-ghost-dev"><?= $t('dev_landing.cta_painel') ?></a>
            </div>

            <div class="dev-code-preview">
                <span class="comment">// <?= $t('dev_landing.code_comment') ?></span><br>
                <span class="var">curl</span> -H <span class="string">"X-API-Key: lrv_live_sua_chave"</span> \<br>
                &nbsp;&nbsp;&nbsp;&nbsp; <span class="string">https://seudominio.com/api/v1/hosting</span><br><br>
                <span class="comment">// <?= $t('dev_landing.code_response') ?></span><br>
                { <span class="string">"success"</span>: <span class="keyword">true</span>, <span class="string">"data"</span>: [...] }
            </div>
        </div>
    </section>

    <!-- FEATURES -->
    <section class="dev-features">
        <div class="dev-feature-card">
            <div class="dev-feature-icon">🔑</div>
            <h3><?= $t('dev_landing.feat_auth_titulo') ?></h3>
            <p><?= $t('dev_landing.feat_auth_desc') ?></p>
        </div>
        <div class="dev-feature-card">
            <div class="dev-feature-icon">⚡</div>
            <h3><?= $t('dev_landing.feat_rest_titulo') ?></h3>
            <p><?= $t('dev_landing.feat_rest_desc') ?></p>
        </div>
        <div class="dev-feature-card">
            <div class="dev-feature-icon">🔔</div>
            <h3><?= $t('dev_landing.feat_webhooks_titulo') ?></h3>
            <p><?= $t('dev_landing.feat_webhooks_desc') ?></p>
        </div>
        <div class="dev-feature-card">
            <div class="dev-feature-icon">🧪</div>
            <h3><?= $t('dev_landing.feat_sandbox_titulo') ?></h3>
            <p><?= $t('dev_landing.feat_sandbox_desc') ?></p>
        </div>
        <div class="dev-feature-card">
            <div class="dev-feature-icon">📊</div>
            <h3><?= $t('dev_landing.feat_rate_titulo') ?></h3>
            <p><?= $t('dev_landing.feat_rate_desc') ?></p>
        </div>
        <div class="dev-feature-card">
            <div class="dev-feature-icon">📖</div>
            <h3><?= $t('dev_landing.feat_docs_titulo') ?></h3>
            <p><?= $t('dev_landing.feat_docs_desc') ?></p>
        </div>
    </section>

    <!-- SANDBOX vs PRODUCTION -->
    <section class="dev-sandbox">
        <h2 style="color:#e2e8f0;text-align:center;margin-bottom:8px;"><?= $t('dev_landing.sandbox_titulo') ?></h2>
        <p style="color:#94a3b8;text-align:center;"><?= $t('dev_landing.sandbox_subtitulo') ?></p>

        <div class="dev-sandbox-grid">
            <div class="sandbox-card">
                <h4><span class="badge-test">SANDBOX</span> <?= $t('dev_landing.sandbox_card_titulo') ?></h4>
                <p><?= $t('dev_landing.sandbox_card_desc') ?></p>
                <ul>
                    <li><?= $t('dev_landing.sandbox_item1') ?></li>
                    <li><?= $t('dev_landing.sandbox_item2') ?></li>
                    <li><?= $t('dev_landing.sandbox_item3') ?></li>
                    <li><?= $t('dev_landing.sandbox_item4') ?></li>
                </ul>
            </div>
            <div class="sandbox-card">
                <h4><span class="badge-live">PRODUCTION</span> <?= $t('dev_landing.prod_card_titulo') ?></h4>
                <p><?= $t('dev_landing.prod_card_desc') ?></p>
                <ul>
                    <li><?= $t('dev_landing.prod_item1') ?></li>
                    <li><?= $t('dev_landing.prod_item2') ?></li>
                    <li><?= $t('dev_landing.prod_item3') ?></li>
                    <li><?= $t('dev_landing.prod_item4') ?></li>
                </ul>
            </div>
        </div>
    </section>

    <!-- QUICK LINKS -->
    <div class="dev-quick-links">
        <a href="/developers/api" class="dev-quick-link">📄 <?= $t('dev_landing.link_docs') ?></a>
        <a href="/developers/api/swagger" class="dev-quick-link">🧪 <?= $t('dev_landing.link_explorer') ?></a>
        <a href="/developers/api/postman.json" class="dev-quick-link" download>📮 Postman</a>
        <a href="/developers/api/bruno.json" class="dev-quick-link" download>🐻 Bruno</a>
        <a href="/developers/api/insomnia.json" class="dev-quick-link" download>🌙 Insomnia</a>
        <a href="/api/v1/openapi.yaml" class="dev-quick-link" download>📋 OpenAPI</a>
        <a href="/developers/api/changelog" class="dev-quick-link">📝 Changelog</a>
        <a href="/developers/api/status" class="dev-quick-link">🟢 Status</a>
    </div>

    <!-- CTA FINAL -->
    <section class="dev-cta-section">
        <h2><?= $t('dev_landing.cta_titulo') ?></h2>
        <p><?= $t('dev_landing.cta_desc') ?></p>
        <div class="dev-hero-actions">
            <a href="/cliente/entrar" class="btn-primary-dev"><?= $t('dev_landing.cta_login') ?></a>
            <a href="/developers/api" class="btn-ghost-dev"><?= $t('dev_landing.cta_ver_docs') ?></a>
        </div>
    </section>

    <?php require __DIR__ . '/../_partials/cookie-banner.php'; ?>
</body>
</html>
