<?php
declare(strict_types=1);
use LRV\Core\I18n;
use LRV\Core\View;

$metricasArr = is_array($metricas ?? null) ? $metricas : [];
$vpsId       = (int) ($vps['id'] ?? 0);

$labels = []; $cpuData = []; $ramData = []; $diskData = [];
foreach (array_slice($metricasArr, -30) as $m) {
    if (!is_array($m)) continue;
    $labels[]   = substr((string) ($m['timestamp'] ?? ''), 11, 5);
    $cpuData[]  = round((float) ($m['cpu_usage']  ?? 0), 2);
    $ramData[]  = round((float) ($m['ram_usage']  ?? 0), 2);
    $diskData[] = round((float) ($m['disk_usage'] ?? 0), 2);
}
$ultima     = !empty($metricasArr) ? end($metricasArr) : null;
$labelsJson = json_encode($labels, JSON_UNESCAPED_UNICODE);
$cpuJson    = json_encode($cpuData);
$ramJson    = json_encode($ramData);
$diskJson   = json_encode($diskData);

function pctColor(float $v): string {
    if ($v >= 90) return '#ef4444';
    if ($v >= 70) return '#f59e0b';
    return '#10b981';
}
?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Monitoramento — VPS #<?php echo $vpsId; ?></title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
  <style>
    .metric-cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px;margin-bottom:18px;}
    .metric-card{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:14px 12px;text-align:center;}
    .metric-val{font-size:28px;font-weight:700;line-height:1;margin-bottom:4px;}
    .metric-lbl{font-size:12px;color:#64748b;}
    .chart-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:14px;margin-bottom:18px;}
    .chart-wrap{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:14px;}
    .chart-title{font-size:13px;font-weight:600;margin-bottom:8px;}
  </style>
</head>
<body>
  <div class="topo">
    <div class="conteudo linha" style="justify-content:space-between;">
      <div>
        <div style="font-size:18px;font-weight:700;">Monitoramento</div>
        <div style="opacity:.9;font-size:13px;">VPS #<?php echo $vpsId; ?></div>
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
      <div class="card"><p class="texto" style="margin:0;">Esta VPS ainda não possui node associado para monitoramento.</p></div>
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

      <?php if ($ultima !== null):
        $cv = (float) ($ultima['cpu_usage'] ?? 0);
        $rv = (float) ($ultima['ram_usage'] ?? 0);
        $dv = (float) ($ultima['disk_usage'] ?? 0);
      ?>
        <div class="metric-cards">
          <div class="metric-card">
            <div class="metric-val" style="color:<?php echo pctColor($cv); ?>;"><?php echo number_format($cv, 1); ?>%</div>
            <div class="metric-lbl">CPU agora</div>
          </div>
          <div class="metric-card">
            <div class="metric-val" style="color:<?php echo pctColor($rv); ?>;"><?php echo number_format($rv, 1); ?>%</div>
            <div class="metric-lbl">RAM agora</div>
          </div>
          <div class="metric-card">
            <div class="metric-val" style="color:<?php echo pctColor($dv); ?>;"><?php echo number_format($dv, 1); ?>%</div>
            <div class="metric-lbl">Disco agora</div>
          </div>
        </div>
      <?php endif; ?>

      <?php if (!empty($cpuData)): ?>
        <p style="font-size:12px;color:#94a3b8;margin-bottom:14px;">Atualização automática a cada 30s. Última: <span id="lastRefresh">agora</span></p>
        <div class="chart-grid">
          <div class="chart-wrap">
            <div class="chart-title" style="color:#4F46E5;">CPU (%)</div>
            <canvas id="chartCpu" height="110"></canvas>
          </div>
          <div class="chart-wrap">
            <div class="chart-title" style="color:#7C3AED;">RAM (%)</div>
            <canvas id="chartRam" height="110"></canvas>
          </div>
          <div class="chart-wrap">
            <div class="chart-title" style="color:#0ea5e9;">Disco (%)</div>
            <canvas id="chartDisk" height="110"></canvas>
          </div>
        </div>
      <?php endif; ?>

      <div class="card">
        <h2 class="titulo" style="font-size:15px;margin-bottom:12px;">Histórico</h2>
        <div style="overflow:auto;">
          <table style="width:100%;border-collapse:collapse;">
            <thead>
              <tr>
                <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;">Data/Hora</th>
                <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;">CPU</th>
                <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;">RAM</th>
                <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;">Disco</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach (array_reverse($metricasArr) as $m):
                $cv2 = (float) ($m['cpu_usage'] ?? 0);
                $rv2 = (float) ($m['ram_usage'] ?? 0);
                $dv2 = (float) ($m['disk_usage'] ?? 0);
              ?>
                <tr>
                  <td style="padding:10px;border-bottom:1px solid #f1f5f9;font-size:13px;"><?php echo View::e((string) ($m['timestamp'] ?? '')); ?></td>
                  <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><span style="color:<?php echo pctColor($cv2); ?>;font-weight:600;"><?php echo number_format($cv2, 2); ?>%</span></td>
                  <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><span style="color:<?php echo pctColor($rv2); ?>;font-weight:600;"><?php echo number_format($rv2, 2); ?>%</span></td>
                  <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><span style="color:<?php echo pctColor($dv2); ?>;font-weight:600;"><?php echo number_format($dv2, 2); ?>%</span></td>
                </tr>
              <?php endforeach; ?>
              <?php if (empty($metricasArr)): ?>
                <tr><td colspan="4" style="padding:12px;color:#94a3b8;">Ainda não há métricas.</td></tr>
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
(function(){
  var labels=<?php echo $labelsJson; ?>;
  var cpu=<?php echo $cpuJson; ?>;
  var ram=<?php echo $ramJson; ?>;
  var disk=<?php echo $diskJson; ?>;
  var opts={plugins:{legend:{display:false}},scales:{y:{min:0,max:100,grid:{color:'#f1f5f9'},ticks:{callback:function(v){return v+'%';},font:{size:11}}},x:{ticks:{maxTicksLimit:8,font:{size:11}},grid:{display:false}}},animation:{duration:300}};
  function mkChart(id,data,color){
    var ctx=document.getElementById(id);
    if(!ctx)return;
    new Chart(ctx,{type:'line',data:{labels:labels,datasets:[{data:data,borderColor:color,backgroundColor:color+'18',borderWidth:2,pointRadius:2,pointHoverRadius:4,tension:0.35,fill:true}]},options:opts});
  }
  mkChart('chartCpu',cpu,'#4F46E5');
  mkChart('chartRam',ram,'#7C3AED');
  mkChart('chartDisk',disk,'#0ea5e9');
  setTimeout(function(){
    document.getElementById('lastRefresh').textContent=new Date().toLocaleTimeString('pt-BR',{hour:'2-digit',minute:'2-digit',second:'2-digit'});
    location.reload();
  },30000);
})();
</script>
<?php endif; ?>
<?php require __DIR__ . '/../_partials/footer.php'; ?>
</body>
</html>
