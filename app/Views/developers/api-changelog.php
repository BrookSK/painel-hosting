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
<?php require __DIR__ . '/../_partials/seo.php'; ?>
<?php require __DIR__ . '/../_partials/estilo.php'; ?>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{font-family:system-ui,-apple-system,'Segoe UI',Roboto,Ubuntu,sans-serif;background:#fff;color:#0f172a}
.cl-hero{background:linear-gradient(135deg,#060d1f 0%,#0B1C3D 30%,#1e3a8a 60%,#4F46E5 85%,#7C3AED 100%);padding:100px 24px 60px;text-align:center;position:relative;overflow:hidden}
.cl-hero::before{content:'';position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.04) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.04) 1px,transparent 1px);background-size:48px 48px;pointer-events:none}
.cl-hero h1{font-size:clamp(28px,5vw,42px);font-weight:900;color:#fff;margin-bottom:12px;position:relative}
.cl-hero p{font-size:16px;color:rgba(255,255,255,.7);position:relative}
.cl-content{max-width:760px;margin:0 auto;padding:60px 24px}
.cl-version{margin-bottom:48px;padding:32px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:16px}
.cl-version-header{display:flex;align-items:center;gap:12px;margin-bottom:20px}
.cl-version-tag{background:#4F46E5;color:#fff;padding:5px 14px;border-radius:8px;font-weight:800;font-size:14px;font-family:monospace}
.cl-version-date{color:#64748b;font-size:14px}
.cl-version-badge{background:#EDE9FE;color:#6D28D9;padding:3px 10px;border-radius:99px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em}
.cl-changes h4{font-size:14px;font-weight:700;color:#0f172a;margin-bottom:10px;margin-top:16px}
.cl-changes ul{list-style:none;padding:0}
.cl-changes li{padding:8px 0;font-size:14px;color:#475569;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;gap:8px}
.cl-changes li svg{flex-shrink:0}
.cl-back{text-align:center;margin-top:40px}
.cl-back a{color:#4F46E5;font-weight:600;font-size:14px;text-decoration:none}
.cl-back a:hover{text-decoration:underline}
</style>
</head>
<body>

<?php require __DIR__ . '/../_partials/navbar-publica.php'; ?>

<section class="cl-hero">
    <h1><?= $t('api_changelog.titulo') ?></h1>
    <p><?= $t('api_changelog.subtitulo') ?></p>
</section>

<div class="cl-content">
    <div class="cl-version">
        <div class="cl-version-header">
            <span class="cl-version-tag">v1.0.0</span>
            <span class="cl-version-date">2026-07-09</span>
            <span class="cl-version-badge"><?= $t('api_changelog.major_release') ?></span>
        </div>

        <div class="cl-changes">
            <h4><?= $t('api_changelog.novidades') ?></h4>
            <ul>
                <li><svg viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2" width="16" height="16"><polyline points="20 6 9 17 4 12"/></svg> <?= $t('api_changelog.item_lancamento') ?></li>
                <li><svg viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2" width="16" height="16"><polyline points="20 6 9 17 4 12"/></svg> <?= $t('api_changelog.item_auth') ?></li>
                <li><svg viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2" width="16" height="16"><polyline points="20 6 9 17 4 12"/></svg> <?= $t('api_changelog.item_endpoints1') ?></li>
                <li><svg viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2" width="16" height="16"><polyline points="20 6 9 17 4 12"/></svg> <?= $t('api_changelog.item_endpoints2') ?></li>
                <li><svg viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2" width="16" height="16"><polyline points="20 6 9 17 4 12"/></svg> <?= $t('api_changelog.item_webhooks') ?></li>
                <li><svg viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2" width="16" height="16"><polyline points="20 6 9 17 4 12"/></svg> <?= $t('api_changelog.item_ratelimit') ?></li>
                <li><svg viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2" width="16" height="16"><polyline points="20 6 9 17 4 12"/></svg> <?= $t('api_changelog.item_logs') ?></li>
                <li><svg viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2" width="16" height="16"><polyline points="20 6 9 17 4 12"/></svg> <?= $t('api_changelog.item_sandbox') ?></li>
                <li><svg viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2" width="16" height="16"><polyline points="20 6 9 17 4 12"/></svg> <?= $t('api_changelog.item_openapi') ?></li>
                <li><svg viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2" width="16" height="16"><polyline points="20 6 9 17 4 12"/></svg> <?= $t('api_changelog.item_swagger') ?></li>
                <li><svg viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2" width="16" height="16"><polyline points="20 6 9 17 4 12"/></svg> <?= $t('api_changelog.item_colecoes') ?></li>
                <li><svg viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2" width="16" height="16"><polyline points="20 6 9 17 4 12"/></svg> <?= $t('api_changelog.item_docs_i18n') ?></li>
                <li><svg viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2" width="16" height="16"><polyline points="20 6 9 17 4 12"/></svg> <?= $t('api_changelog.item_sdks') ?></li>
            </ul>
        </div>
    </div>

    <div class="cl-back">
        <a href="/developers/api">&larr; <?= $t('api_docs.documentacao') ?></a>
        &nbsp;&nbsp;&bull;&nbsp;&nbsp;
        <a href="/developers/api/swagger">API Explorer &rarr;</a>
    </div>
</div>

<?php require __DIR__ . '/../_partials/footer.php'; ?>
</body>
</html>
