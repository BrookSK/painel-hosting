<?php

declare(strict_types=1);

use LRV\Core\I18n;
use LRV\Core\View;

function fmtPctCli2($v): string
{
    if ($v === null || $v === '') { return ''; }
    return number_format((float) $v, 2, ',', '.') . '%';
}

$metricasArr = is_array($metricas ?? null) ? $metricas : [];

// Preparar dados para gráficos (últimas 30 coletas)
$labels = [];
$cpuData = [];
$ramData = [];
$diskData = [];
foreach (array_slice($metricasArr, -30) as $m) {
    if (!is_array($m)) continue;
    $labels[]  = substr((string) ($m['timestamp'] ?? ''), 11, 5); // HH:MM
    $cpuData[] = round((float) ($m['cpu_usage'] ?? 0), 2);
    $ramData[] = round((float) ($m['ram_usage'] ?? 0), 2);
    $diskData[]= round((float) ($m['disk_usage'] ?? 0), 2);
}

$labelsJson  = json_encode($labels, JSON_UNESCAPED_UNICODE);
$cpuJson     = json_encode($cpuData);
$ramJson     = json_encode($ramData);
$diskJson    = json_encode($diskData);

?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Monitoramento</title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
</head>
<body>
  <div class="topo">
    <div class="conteudo linha" style="justify-content:space-between;">
      <div>
        <div style="font-size:18px;font-weight:700;">Monitoramento</div>
        <div style="opacity:.9; font-size:13px;">VPS #<?php echo (int) ($vps['id'] ?? 0); ?></div>
      </div>
      <div class="linha">
        <?php require __DIR__ . '/../_partials/idioma.php'; ?>
        <a href="/cliente/monitoramento">Voltar</a>
        <a href="/cliente/vps">VPS</a>
        <a href="/cliente/sair">Sair</a>
      </div>
    </div>
  </div>

  <div class="conteudo">
    <?php if (empty($servidor)): ?>
      <div class="card">
        <div class="texto" style="margin:0;">Esta VPS ainda não possui node associado para monitoramento.</div>
      </div>
    <?php else: ?>
      <div class="card" style="margin-bottom:14px;">
        <div class="grid">
          <div>
            <div class="texto" style="margin:0;"><strong>Servidor:</strong> <?php echo View::e((string) ($servidor['hostname'] ?? '')); ?></div>
            <div class="texto" style="margin:0;"><strong>IP:</strong> <?php echo View::e((string) ($servidor['ip_address'] ?? '')); ?></div>
          </div>
          <div>
            <div class="texto" style="margin:0;"><strong>Status:</strong> <?php echo View::e((string) ($servidor['status'] ?? '')); ?></div>
            <div class="texto" style="margin:0;"><strong>Coletas:</strong> <?php echo count($metricasArr); ?></div>
          </div>
        </div>
      </div>

      <?php if (!empty($cpuData)): ?>
        <div class="card" style="margin-bottom:14px;">
          <h2 class="titulo" style="font-size:16px; margin-bottom:12px;">Gráficos (últimas <?php echo count($cpuData); ?> coletas)</h2>
          <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(280px,1fr)); gap:14px;">
            <div>
              <div style="font-size:13px; font-weight:600; margin-bottom:6px; color:#4F46E5;">CPU (%)</div>
              <canvas id="chartCpu" height="120"></canvas>
            </div>
            <div>
              <div style="font-size:13px; font-weight:600; margin-bottom:6px; color:#7C3AED;">RAM (%)</div>
              <canvas id="chartRam" height="120"></canvas>
            </div>
            <div>
              <div style="font-size:13px; font-weight:600; margin-bottom:6px; color:#0B1C3D;">Disco (%)</div>
              <canvas id="chartDisk" height="120"></canvas>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <div class="card">
        <h2 class="titulo" style="font-size:16px; margin-bottom:12px;">Últimas métricas</h2>
        <div style="overflow:auto;">
          <table style="width:100%; border-collapse:collapse;">
            <thead>
              <tr>
                <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Data</th>
                <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">CPU</th>
                <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">RAM</th>
                <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Disco</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($metricasArr as $m): ?>
                <tr>
                  <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo View::e((string) ($m['timestamp'] ?? '')); ?></td>
                  <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><code><?php echo View::e(fmtPctCli2($m['cpu_usage'] ?? null)); ?></code></td>
                  <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><code><?php echo View::e(fmtPctCli2($m['ram_usage'] ?? null)); ?></code></td>
                  <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><code><?php echo View::e(fmtPctCli2($m['disk_usage'] ?? null)); ?></code></td>
                </tr>
              <?php endforeach; ?>
              <?php if (empty($metricasArr)): ?>
                <tr>
                  <td colspan="4" style="padding:12px;">Ainda não há métricas para este servidor.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <?php if (!empty($cpuData)): ?>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
  <script>
    (function() {
      var labels = <?php echo $labelsJson; ?>;
      var cpu    = <?php echo $cpuJson; ?>;
      var ram    = <?php echo $ramJson; ?>;
      var disk   = <?php echo $diskJson; ?>;

      function makeChart(id, data, color) {
        var ctx = document.getElementById(id);
        if (!ctx) return;
        new Chart(ctx, {
          type: 'line',
          data: {
            labels: labels,
            datasets: [{
              data: data,
              borderColor: color,
              backgroundColor: color + '22',
              borderWidth: 2,
              pointRadius: 2,
              tension: 0.3,
              fill: true
            }]
          },
          options: {
            plugins: { legend: { display: false } },
            scales: {
              y: { min: 0, max: 100, ticks: { callback: function(v) { return v + '%'; } } },
              x: { ticks: { maxTicksLimit: 8 } }
            }
          }
        });
      }

      makeChart('chartCpu',  cpu,  '#4F46E5');
      makeChart('chartRam',  ram,  '#7C3AED');
      makeChart('chartDisk', disk, '#0B1C3D');
    })();
  </script>
  <?php endif; ?>
</body>
</html>
