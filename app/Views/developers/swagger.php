<?php
declare(strict_types=1);
use LRV\Core\I18n;
use LRV\Core\View;
$t = fn(string $k) => I18n::t($k);
?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>API Explorer — LRV Cloud Manager</title>
<?php require __DIR__ . '/../_partials/seo.php'; ?>
<?php require __DIR__ . '/../_partials/estilo.php'; ?>
<style><?php
$swCss = dirname(__DIR__, 2) . '/public/assets/vendor/swagger-ui/swagger-ui.css';
if (is_file($swCss)) { echo file_get_contents($swCss); }
?></style>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:system-ui,-apple-system,'Segoe UI',Roboto,Ubuntu,sans-serif;background:#fff;color:#0f172a}
.sw-hero{background:linear-gradient(135deg,#060d1f 0%,#0B1C3D 30%,#1e3a8a 60%,#4F46E5 85%,#7C3AED 100%);padding:100px 24px 40px;text-align:center;position:relative;overflow:hidden}
.sw-hero::before{content:'';position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.04) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.04) 1px,transparent 1px);background-size:48px 48px;pointer-events:none}
.sw-hero h1{font-size:clamp(24px,4vw,36px);font-weight:900;color:#fff;margin-bottom:8px;position:relative;display:flex;align-items:center;justify-content:center;gap:10px}
.sw-hero p{font-size:15px;color:rgba(255,255,255,.65);position:relative}
.sw-hero-links{display:flex;gap:16px;justify-content:center;margin-top:16px;position:relative}
.sw-hero-links a{color:rgba(255,255,255,.7);font-size:13px;font-weight:600;text-decoration:none;transition:color .15s}
.sw-hero-links a:hover{color:#fff}
.sw-container{max-width:1200px;margin:0 auto;padding:40px 24px 60px;min-height:500px}
#swagger-ui .topbar{display:none}
#swagger-ui .swagger-ui .info{margin:0 0 20px}
</style>
</head>
<body>

<?php require __DIR__ . '/../_partials/navbar-publica.php'; ?>

<section class="sw-hero">
    <h1><svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="28" height="28"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg> API Explorer</h1>
    <p><?= $t('api_swagger.subtitulo') ?></p>
    <div class="sw-hero-links">
        <a href="/developers/api"><?= $t('api_docs.documentacao') ?></a>
        <a href="/api/v1/openapi.yaml" download>OpenAPI YAML</a>
        <a href="/developers"><?= $t('nav.inicio') ?></a>
    </div>
</section>

<div class="sw-container">
    <div id="swagger-ui"></div>
    <noscript><p style="text-align:center;padding:40px;color:#64748b">JavaScript é necessário para exibir o API Explorer.</p></noscript>
</div>

<script><?php
$swJs = dirname(__DIR__, 2) . '/public/assets/vendor/swagger-ui/swagger-ui-bundle.js';
if (is_file($swJs)) { readfile($swJs); }
?></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    SwaggerUIBundle({
        url: '/api/v1/openapi.yaml',
        dom_id: '#swagger-ui',
        deepLinking: true,
        presets: [SwaggerUIBundle.presets.apis, SwaggerUIBundle.SwaggerUIStandalonePreset],
        layout: 'BaseLayout',
        persistAuthorization: true,
        tryItOutEnabled: true,
        defaultModelsExpandDepth: -1
    });
});
</script>

<?php require __DIR__ . '/../_partials/footer.php'; ?>
</body>
</html>
