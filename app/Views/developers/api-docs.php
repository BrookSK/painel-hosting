<?php
declare(strict_types=1);
use LRV\Core\I18n;
use LRV\Core\View;
use LRV\Core\SistemaConfig;

$t = fn(string $k) => I18n::t($k);
$titulo = $titulo ?? $t('api_docs.titulo');
?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<?php require __DIR__ . '/../_partials/seo.php'; ?>
<?php require __DIR__ . '/../_partials/estilo.php'; ?>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth;overflow-x:hidden}
body{font-family:system-ui,-apple-system,'Segoe UI',Roboto,Ubuntu,sans-serif;background:#fff;color:#0f172a}
.doc-hero{background:linear-gradient(135deg,#060d1f 0%,#0B1C3D 30%,#1e3a8a 60%,#4F46E5 85%,#7C3AED 100%);padding:100px 24px 60px;text-align:center;position:relative;overflow:hidden}
.doc-hero::before{content:'';position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.04) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.04) 1px,transparent 1px);background-size:48px 48px;pointer-events:none}
.doc-hero h1{font-size:clamp(28px,5vw,42px);font-weight:900;color:#fff;margin-bottom:12px;position:relative}
.doc-hero p{font-size:16px;color:rgba(255,255,255,.7);max-width:560px;margin:0 auto;position:relative}
.doc-nav{background:#0f172a;padding:12px 24px;position:sticky;top:0;z-index:50}
.doc-nav-inner{max-width:1000px;margin:0 auto;display:flex;gap:8px;flex-wrap:wrap;justify-content:center}
.doc-nav a{padding:7px 16px;border-radius:8px;font-size:13px;font-weight:600;color:rgba(255,255,255,.6);text-decoration:none;transition:all .15s}
.doc-nav a:hover,.doc-nav a.active{background:rgba(255,255,255,.1);color:#fff}
.doc-content{max-width:860px;margin:0 auto;padding:60px 24px}
.doc-section{margin-bottom:56px}
.doc-section h2{font-size:22px;font-weight:800;color:#0f172a;margin-bottom:16px;padding-bottom:12px;border-bottom:2px solid #e2e8f0}
.doc-section h3{font-size:16px;font-weight:700;color:#1e293b;margin:20px 0 8px}
.doc-section p,.doc-section li{font-size:15px;color:#475569;line-height:1.8}
.doc-section ul{padding-left:20px;margin:8px 0}
.code-block{background:#0f172a;border:1px solid #1e293b;border-radius:10px;padding:18px 22px;overflow-x:auto;font-family:'JetBrains Mono','Fira Code',monospace;font-size:.82rem;color:#e2e8f0;line-height:1.7;margin:16px 0}
.code-block .cm{color:#475569}
.code-block .str{color:#86efac}
.endpoint-row{display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid #f1f5f9}
.endpoint-badge{display:inline-block;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:700;letter-spacing:.03em}
.badge-get{background:#DCFCE7;color:#166534}
.badge-post{background:#DBEAFE;color:#1e40af}
.endpoint-row code{font-size:13px;color:#334155;font-family:'JetBrains Mono',monospace}
.endpoint-row span:last-child{color:#64748b;font-size:13px;margin-left:auto}
.downloads-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:10px;margin-top:16px}
.downloads-grid a{display:flex;align-items:center;gap:10px;padding:12px 16px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;color:#0f172a;font-weight:600;font-size:13px;text-decoration:none;transition:border-color .15s,transform .15s}
.downloads-grid a:hover{border-color:#4F46E5;transform:translateY(-1px)}
.downloads-grid a svg{flex-shrink:0}
.sdk-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-top:16px}
@media(max-width:640px){.sdk-grid{grid-template-columns:1fr}}
.sdk-card{background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:16px;text-align:center}
.sdk-card h4{font-size:14px;font-weight:700;color:#0f172a;margin-bottom:4px}
.sdk-card code{font-size:12px;color:#64748b}
</style>
</head>
<body>

<?php require __DIR__ . '/../_partials/navbar-publica.php'; ?>

<section class="doc-hero">
    <h1><?= View::e($titulo) ?></h1>
    <p><?= $t('api_docs.subtitulo') ?></p>
</section>

<nav class="doc-nav">
    <div class="doc-nav-inner">
        <a href="#introducao"><?= $t('api_docs.nav_intro') ?></a>
        <a href="#autenticacao"><?= $t('api_docs.nav_auth') ?></a>
        <a href="#rate-limit"><?= $t('api_docs.nav_rate_limit') ?></a>
        <a href="#endpoints"><?= $t('api_docs.nav_endpoints') ?></a>
        <a href="#webhooks"><?= $t('api_docs.nav_webhooks') ?></a>
        <a href="#erros"><?= $t('api_docs.nav_erros') ?></a>
        <a href="#sdks"><?= $t('api_docs.nav_sdks') ?></a>
        <a href="/developers/api/swagger"><?= $t('api_docs.nav_playground') ?></a>
    </div>
</nav>

<div class="doc-content">

    <section class="doc-section" id="introducao">
        <h2><?= $t('api_docs.intro_titulo') ?></h2>
        <p><?= $t('api_docs.intro_desc') ?></p>
        <div class="code-block"><span class="cm"># <?= $t('dev_landing.code_comment') ?></span>
curl -H <span class="str">"X-API-Key: lrv_live_sua_chave_aqui"</span> \
     <span class="str">https://seudominio.com/api/v1/hosting</span></div>
    </section>

    <section class="doc-section" id="autenticacao">
        <h2><?= $t('api_docs.auth_titulo') ?></h2>
        <p><?= $t('api_docs.auth_desc') ?></p>

        <h3><?= $t('api_docs.auth_api_key') ?></h3>
        <div class="code-block"><span class="cm"># <?= $t('api_docs.auth_via_header') ?></span>
curl -H <span class="str">"X-API-Key: lrv_live_sua_chave"</span> \
     <span class="str">https://seudominio.com/api/v1/hosting</span></div>

        <h3><?= $t('api_docs.auth_bearer') ?></h3>
        <div class="code-block"><span class="cm"># <?= $t('api_docs.auth_obter_token') ?></span>
curl -X POST <span class="str">https://seudominio.com/api/v1/auth/token</span> \
     -H <span class="str">"Content-Type: application/json"</span> \
     -d <span class="str">'{"api_key": "lrv_live_sua_chave"}'</span>

<span class="cm"># <?= $t('api_docs.auth_usar_token') ?></span>
curl -H <span class="str">"Authorization: Bearer seu_access_token"</span> \
     <span class="str">https://seudominio.com/api/v1/tickets</span></div>
    </section>

    <section class="doc-section" id="rate-limit">
        <h2><?= $t('api_docs.rate_titulo') ?></h2>
        <p><?= $t('api_docs.rate_desc') ?></p>
        <div class="code-block">X-RateLimit-Limit: 60
X-RateLimit-Remaining: 58
X-RateLimit-Reset: 1720540800</div>
    </section>

    <section class="doc-section" id="endpoints">
        <h2><?= $t('api_docs.endpoints_titulo') ?></h2>
        <p><?= $t('api_docs.endpoints_desc') ?></p>

        <div style="margin-top:16px">
            <div class="endpoint-row"><span class="endpoint-badge badge-get">GET</span> <code>/api/v1/hosting</code> <span><?= $t('api_docs.ep_hosting_list') ?></span></div>
            <div class="endpoint-row"><span class="endpoint-badge badge-get">GET</span> <code>/api/v1/tickets</code> <span><?= $t('api_docs.ep_tickets_list') ?></span></div>
            <div class="endpoint-row"><span class="endpoint-badge badge-post">POST</span> <code>/api/v1/tickets</code> <span><?= $t('api_docs.ep_tickets_create') ?></span></div>
            <div class="endpoint-row"><span class="endpoint-badge badge-get">GET</span> <code>/api/v1/subscriptions</code> <span><?= $t('api_docs.ep_subs_list') ?></span></div>
            <div class="endpoint-row"><span class="endpoint-badge badge-get">GET</span> <code>/api/v1/domains</code> <span><?= $t('api_docs.ep_domains_list') ?></span></div>
            <div class="endpoint-row"><span class="endpoint-badge badge-post">POST</span> <code>/api/v1/domains</code> <span><?= $t('api_docs.ep_domains_create') ?></span></div>
            <div class="endpoint-row"><span class="endpoint-badge badge-get">GET</span> <code>/api/v1/webhooks</code> <span><?= $t('api_docs.ep_webhooks_list') ?></span></div>
            <div class="endpoint-row"><span class="endpoint-badge badge-get">GET</span> <code>/api/v1/status</code> <span><?= $t('api_docs.ep_status') ?></span></div>
        </div>

        <p style="margin-top:20px"><a href="/developers/api/swagger" style="color:#4F46E5;font-weight:600"><?= $t('api_docs.ver_todos_endpoints') ?></a></p>
    </section>

    <section class="doc-section" id="webhooks">
        <h2><?= $t('api_docs.webhooks_titulo') ?></h2>
        <p><?= $t('api_docs.webhooks_desc') ?></p>
        <div class="code-block"><span class="cm">// <?= $t('api_docs.webhooks_validar') ?></span>
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'] ?? '';
$expected = 'sha256=' . hash_hmac('sha256', $payload, $secret);
$valid = hash_equals($expected, $signature);</div>
    </section>

    <section class="doc-section" id="erros">
        <h2><?= $t('api_docs.erros_titulo') ?></h2>
        <div class="code-block">{
    "success": false,
    "error": {
        "code": "VALIDATION_ERROR",
        "message": "The given data was invalid.",
        "details": [
            {"field": "domain", "message": "Invalid domain format."}
        ]
    }
}</div>
    </section>

    <section class="doc-section" id="sdks">
        <h2><?= $t('api_docs.sdks_titulo') ?></h2>
        <p><?= $t('api_docs.sdks_desc') ?></p>

        <h3><?= $t('api_docs.downloads_titulo') ?></h3>
        <div class="downloads-grid">
            <a href="/api/v1/openapi.yaml" download><svg viewBox="0 0 24 24" fill="none" stroke="#4F46E5" stroke-width="2" width="18" height="18"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg> OpenAPI YAML</a>
            <a href="/api/v1/openapi.json"><svg viewBox="0 0 24 24" fill="none" stroke="#4F46E5" stroke-width="2" width="18" height="18"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg> OpenAPI JSON</a>
            <a href="/developers/api/postman.json" download><svg viewBox="0 0 24 24" fill="none" stroke="#4F46E5" stroke-width="2" width="18" height="18"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg> Postman</a>
            <a href="/developers/api/bruno.json" download><svg viewBox="0 0 24 24" fill="none" stroke="#4F46E5" stroke-width="2" width="18" height="18"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg> Bruno</a>
            <a href="/developers/api/insomnia.json" download><svg viewBox="0 0 24 24" fill="none" stroke="#4F46E5" stroke-width="2" width="18" height="18"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg> Insomnia</a>
            <a href="/developers/api/swagger"><svg viewBox="0 0 24 24" fill="none" stroke="#4F46E5" stroke-width="2" width="18" height="18"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg> API Explorer</a>
        </div>

        <h3 style="margin-top:32px">SDKs</h3>
        <div class="sdk-grid">
            <div class="sdk-card"><h4>PHP</h4><code>composer require lrv/cloud-manager-sdk</code></div>
            <div class="sdk-card"><h4>JavaScript</h4><code>npm install @lrv/cloud-manager</code></div>
            <div class="sdk-card"><h4>Python</h4><code>pip install lrv-cloud-manager</code></div>
        </div>
    </section>
</div>

<?php require __DIR__ . '/../_partials/footer.php'; ?>
</body>
</html>
