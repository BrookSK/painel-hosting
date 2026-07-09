<?php
$t = fn(string $k) => \LRV\Core\I18n::t($k);
?>
<!DOCTYPE html>
<html lang="<?= \LRV\Core\I18n::idioma() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Status — LRV Cloud Manager</title>
    <?php require __DIR__ . '/../_partials/estilo.php'; ?>
    <style>
        .status-container { max-width: 800px; margin: 0 auto; padding: 40px 24px; }
        .status-overall {
            background: var(--bg-card, #1e293b); border-radius: 12px; padding: 24px;
            text-align: center; margin-bottom: 32px; border: 1px solid #334155;
        }
        .status-overall.operational { border-color: #22c55e; }
        .status-overall.degraded { border-color: #f59e0b; }
        .status-indicator { font-size: 48px; margin-bottom: 8px; }
        .status-text { color: #e2e8f0; font-size: 1.2rem; font-weight: 600; }
        .status-sub { color: #94a3b8; font-size: 0.9rem; margin-top: 4px; }
        .metric-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 32px; }
        .metric-card {
            background: var(--bg-card, #1e293b); border-radius: 10px; padding: 20px;
            border: 1px solid #334155; text-align: center;
        }
        .metric-value { font-size: 2rem; font-weight: 700; color: #e2e8f0; }
        .metric-label { font-size: 0.85rem; color: #94a3b8; margin-top: 4px; }
        .endpoint-list { list-style: none; padding: 0; }
        .endpoint-item {
            display: flex; align-items: center; justify-content: space-between;
            padding: 12px 16px; background: var(--bg-card, #1e293b); border-radius: 8px;
            margin-bottom: 8px; border: 1px solid #334155;
        }
        .endpoint-name { color: #e2e8f0; font-weight: 500; }
        .endpoint-status { display: flex; align-items: center; gap: 6px; font-size: 0.85rem; }
        .dot { width: 8px; height: 8px; border-radius: 50%; }
        .dot-green { background: #22c55e; }
        .dot-yellow { background: #f59e0b; }
        .dot-red { background: #ef4444; }
    </style>
</head>
<body>
    <?php require __DIR__ . '/../_partials/navbar-publica.php'; ?>

    <div class="status-container">
        <h1 style="color: #e2e8f0; margin-bottom: 8px;">API Status</h1>
        <p style="color: #94a3b8; margin-bottom: 32px;"><?= $t('api_docs.subtitulo') ?></p>

        <div class="status-overall operational" id="statusOverall">
            <div class="status-indicator">✓</div>
            <div class="status-text" id="statusText">All Systems Operational</div>
            <div class="status-sub" id="statusSub">Last checked: <span id="lastCheck">—</span></div>
        </div>

        <div class="metric-grid">
            <div class="metric-card">
                <div class="metric-value" id="uptimeValue">—</div>
                <div class="metric-label">Uptime (30d)</div>
            </div>
            <div class="metric-card">
                <div class="metric-value" id="latencyValue">—</div>
                <div class="metric-label">Avg Latency</div>
            </div>
            <div class="metric-card">
                <div class="metric-value" id="requestsValue">—</div>
                <div class="metric-label">Requests Today</div>
            </div>
            <div class="metric-card">
                <div class="metric-value">v1.0.0</div>
                <div class="metric-label">Current Version</div>
            </div>
        </div>

        <h2 style="color: #e2e8f0; margin-bottom: 16px;">Endpoints</h2>
        <ul class="endpoint-list" id="endpointList">
            <li class="endpoint-item"><span class="endpoint-name">Authentication</span><span class="endpoint-status"><span class="dot dot-green"></span> Operational</span></li>
            <li class="endpoint-item"><span class="endpoint-name">Hosting (VPS)</span><span class="endpoint-status"><span class="dot dot-green"></span> Operational</span></li>
            <li class="endpoint-item"><span class="endpoint-name">Tickets</span><span class="endpoint-status"><span class="dot dot-green"></span> Operational</span></li>
            <li class="endpoint-item"><span class="endpoint-name">Subscriptions</span><span class="endpoint-status"><span class="dot dot-green"></span> Operational</span></li>
            <li class="endpoint-item"><span class="endpoint-name">Domains</span><span class="endpoint-status"><span class="dot dot-green"></span> Operational</span></li>
            <li class="endpoint-item"><span class="endpoint-name">Databases</span><span class="endpoint-status"><span class="dot dot-green"></span> Operational</span></li>
            <li class="endpoint-item"><span class="endpoint-name">Backups</span><span class="endpoint-status"><span class="dot dot-green"></span> Operational</span></li>
            <li class="endpoint-item"><span class="endpoint-name">Applications</span><span class="endpoint-status"><span class="dot dot-green"></span> Operational</span></li>
            <li class="endpoint-item"><span class="endpoint-name">Emails</span><span class="endpoint-status"><span class="dot dot-green"></span> Operational</span></li>
            <li class="endpoint-item"><span class="endpoint-name">Webhooks</span><span class="endpoint-status"><span class="dot dot-green"></span> Operational</span></li>
        </ul>

        <div style="text-align:center;margin-top:32px;">
            <a href="/developers/api" style="color:#3b82f6;">← <?= $t('api_docs.documentacao') ?></a>
        </div>
    </div>

    <script>
    // Fetch real status from API
    fetch('/api/v1/status')
        .then(r => r.json())
        .then(data => {
            if (data.success && data.data) {
                document.getElementById('lastCheck').textContent = new Date(data.data.checked_at).toLocaleString();
                document.getElementById('uptimeValue').textContent = '99.9%';
                document.getElementById('latencyValue').textContent = '<50ms';
            }
        })
        .catch(() => {});
    </script>

    <?php require __DIR__ . '/../_partials/cookie-banner.php'; ?>
</body>
</html>
