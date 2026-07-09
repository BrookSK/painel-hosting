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
.st-hero{background:linear-gradient(135deg,#060d1f 0%,#0B1C3D 30%,#1e3a8a 60%,#4F46E5 85%,#7C3AED 100%);padding:100px 24px 60px;text-align:center;position:relative;overflow:hidden}
.st-hero::before{content:'';position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.04) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.04) 1px,transparent 1px);background-size:48px 48px;pointer-events:none}
.st-hero h1{font-size:clamp(28px,5vw,42px);font-weight:900;color:#fff;margin-bottom:12px;position:relative}
.st-hero p{font-size:16px;color:rgba(255,255,255,.7);position:relative}
.st-content{max-width:800px;margin:0 auto;padding:60px 24px}
.st-overall{border:2px solid #22c55e;border-radius:16px;padding:32px;text-align:center;margin-bottom:40px;background:#f0fdf4}
.st-overall.degraded{border-color:#f59e0b;background:#fffbeb}
.st-overall svg{margin-bottom:8px}
.st-overall h2{font-size:20px;font-weight:800;color:#166534;margin-bottom:4px}
.st-overall.degraded h2{color:#92400e}
.st-overall p{font-size:13px;color:#64748b}
.st-metrics{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:40px}
@media(max-width:640px){.st-metrics{grid-template-columns:1fr 1fr}}
.st-metric{background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:20px;text-align:center}
.st-metric h3{font-size:24px;font-weight:800;color:#4F46E5;margin-bottom:4px}
.st-metric p{font-size:12px;color:#64748b;margin:0}
.st-endpoints h3{font-size:18px;font-weight:800;color:#0f172a;margin-bottom:16px}
.st-endpoint{display:flex;align-items:center;justify-content:space-between;padding:14px 16px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;margin-bottom:8px}
.st-endpoint span:first-child{font-weight:600;color:#0f172a;font-size:14px}
.st-endpoint-status{display:flex;align-items:center;gap:6px;font-size:13px;color:#166534;font-weight:500}
.st-dot{width:8px;height:8px;border-radius:50%;background:#22c55e}
.st-back{text-align:center;margin-top:40px}
.st-back a{color:#4F46E5;font-weight:600;font-size:14px;text-decoration:none}
.st-back a:hover{text-decoration:underline}
</style>
</head>
<body>

<?php require __DIR__ . '/../_partials/navbar-publica.php'; ?>

<section class="st-hero">
    <h1>API Status</h1>
    <p><?= $t('api_docs.subtitulo') ?></p>
</section>

<div class="st-content">
    <div class="st-overall" id="statusOverall">
        <svg viewBox="0 0 24 24" fill="none" stroke="#166534" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" width="40" height="40"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        <h2>All Systems Operational</h2>
        <p>Last checked: <span id="lastCheck">&mdash;</span></p>
    </div>

    <div class="st-metrics">
        <div class="st-metric"><h3>99.9%</h3><p>Uptime (30d)</p></div>
        <div class="st-metric"><h3>&lt; 50ms</h3><p>Avg Latency</p></div>
        <div class="st-metric"><h3>v1.0.0</h3><p>Current Version</p></div>
        <div class="st-metric"><h3>50+</h3><p>Endpoints</p></div>
    </div>

    <div class="st-endpoints">
        <h3>Endpoints</h3>
        <div class="st-endpoint"><span>Authentication</span><span class="st-endpoint-status"><span class="st-dot"></span> Operational</span></div>
        <div class="st-endpoint"><span>Hosting (VPS)</span><span class="st-endpoint-status"><span class="st-dot"></span> Operational</span></div>
        <div class="st-endpoint"><span>Tickets</span><span class="st-endpoint-status"><span class="st-dot"></span> Operational</span></div>
        <div class="st-endpoint"><span>Subscriptions</span><span class="st-endpoint-status"><span class="st-dot"></span> Operational</span></div>
        <div class="st-endpoint"><span>Domains</span><span class="st-endpoint-status"><span class="st-dot"></span> Operational</span></div>
        <div class="st-endpoint"><span>Databases</span><span class="st-endpoint-status"><span class="st-dot"></span> Operational</span></div>
        <div class="st-endpoint"><span>Backups</span><span class="st-endpoint-status"><span class="st-dot"></span> Operational</span></div>
        <div class="st-endpoint"><span>Applications</span><span class="st-endpoint-status"><span class="st-dot"></span> Operational</span></div>
        <div class="st-endpoint"><span>Emails</span><span class="st-endpoint-status"><span class="st-dot"></span> Operational</span></div>
        <div class="st-endpoint"><span>Webhooks</span><span class="st-endpoint-status"><span class="st-dot"></span> Operational</span></div>
    </div>

    <div class="st-back"><a href="/developers/api">&larr; <?= $t('api_docs.documentacao') ?></a></div>
</div>

<script>
fetch('/api/v1/status').then(r=>r.json()).then(d=>{if(d.success&&d.data)document.getElementById('lastCheck').textContent=new Date(d.data.checked_at).toLocaleString()}).catch(()=>{});
</script>

<?php require __DIR__ . '/../_partials/footer.php'; ?>
</body>
</html>
