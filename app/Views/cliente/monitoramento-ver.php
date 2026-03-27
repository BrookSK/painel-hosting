<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;

$vps = is_array($vps ?? null) ? $vps : [];
$stats = is_array($container_stats ?? null) ? $container_stats : null;
$metricas = is_array($metricas ?? null) ? $metricas : [];
$vpsId = (int)($vps['id'] ?? 0);
$cpuTotal = (int)($vps['cpu'] ?? 0);
$ramMb = (int)($vps['ram'] ?? 0);
$ramGb = round($ramMb / 1024, 1);
$discoMb = (int)($vps['storage'] ?? 0);
$discoGb = round($discoMb / 1024);

$pageTitle = 'Monitoramento VPS #' . $vpsId;
require __DIR__ . '/../_partials/layout-cliente-inicio.php';
?>

<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:24px;">
  <div>
    <div class="page-title">Monitoramento</div>
    <div class="page-subtitle" style="margin-bottom:0;">VPS #<?php echo $vpsId; ?> · <?php echo $cpuTotal; ?> vCPU · <?php echo $ramGb; ?> GB RAM · <?php echo $discoGb; ?> GB Disco</div>
  </div>
  <a href="/cliente/vps" class="botao ghost sm">← Voltar</a>
</div>

<?php if ($stats): ?>
<!-- Métricas em tempo real do container -->
<div class="grid-3" style="margin-bottom:20px;">
  <div class="stat-card roxo">
    <div class="stat-val"><?php echo number_format($stats['cpu_percent'], 1); ?>%</div>
    <div class="stat-label">CPU agora</div>
  </div>
  <div class="stat-card verde">
    <div class="stat-val"><?php echo number_format($stats['mem_percent'], 1); ?>%</div>
    <div class="stat-label">RAM agora · <?php echo View::e($stats['mem_usage']); ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-val" style="font-size:18px;"><?php echo View::e($stats['block_io']); ?></div>
    <div class="stat-label">I/O Disco</div>
  </div>
</div>

<p style="font-size:12px;color:#94a3b8;margin-bottom:16px;">Dados coletados em tempo real do container Docker. Recarregue a página para atualizar.</p>
<?php else: ?>
<div class="aviso" style="margin-bottom:16px;">
  <?php if ((string)($vps['status'] ?? '') !== 'running'): ?>
    VPS não está em execução. Inicie a VPS para ver as métricas.
  <?php else: ?>
    Não foi possível coletar métricas do container. Tente recarregar a página.
  <?php endif; ?>
</div>
<?php endif; ?>

<!-- Gráficos -->
<?php if (!empty($metricas)): ?>
<div class="grid-3" style="margin-bottom:20px;">
  <div class="card">
    <div style="font-weight:600;font-size:13px;color:#4F46E5;margin-bottom:8px;">CPU (%)</div>
    <canvas id="chartCpu" height="120"></canvas>
  </div>
  <div class="card">
    <div style="font-weight:600;font-size:13px;color:#16a34a;margin-bottom:8px;">RAM (%)</div>
    <canvas id="chartRam" height="120"></canvas>
  </div>
  <div class="card">
    <div style="font-weight:600;font-size:13px;color:#f59e0b;margin-bottom:8px;">Disco (%)</div>
    <canvas id="chartDisco" height="120"></canvas>
  </div>
</div>

<!-- Histórico (últimas 12 coletas) -->
<div class="card">
  <div style="font-weight:600;font-size:14px;margin-bottom:12px;">Histórico</div>
  <div style="overflow:auto;">
    <table style="font-size:13px;">
      <thead>
        <tr><th>Data/Hora</th><th>CPU</th><th>RAM</th><th>Disco</th></tr>
      </thead>
      <tbody>
        <?php foreach ($metricas as $m): ?>
        <tr>
          <td><?php echo View::e((string)($m['timestamp'] ?? '')); ?></td>
          <td style="color:#4F46E5;"><?php echo number_format((float)($m['cpu_usage'] ?? 0), 2); ?>%</td>
          <td style="color:#16a34a;"><?php echo number_format((float)($m['ram_usage'] ?? 0), 2); ?>%</td>
          <td style="color:#f59e0b;"><?php echo number_format((float)($m['disk_usage'] ?? 0), 2); ?>%</td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
(function(){
  var data=<?php echo json_encode(array_reverse($metricas)); ?>;
  function drawChart(canvasId,dataKey,color){
    var canvas=document.getElementById(canvasId);
    if(!canvas)return;
    var ctx=canvas.getContext('2d');
    var w=canvas.width=canvas.offsetWidth;
    var h=canvas.height=120;
    var vals=data.map(function(d){return parseFloat(d[dataKey])||0;});
    var max=Math.max(100,Math.max.apply(null,vals));
    var step=w/(vals.length-1||1);

    ctx.clearRect(0,0,w,h);
    ctx.beginPath();
    ctx.strokeStyle=color;
    ctx.lineWidth=2;
    for(var i=0;i<vals.length;i++){
      var x=i*step;
      var y=h-(vals[i]/max)*h;
      if(i===0)ctx.moveTo(x,y);else ctx.lineTo(x,y);
    }
    ctx.stroke();

    // Fill
    ctx.lineTo((vals.length-1)*step,h);
    ctx.lineTo(0,h);
    ctx.closePath();
    ctx.fillStyle=color.replace(')',',0.1)').replace('rgb','rgba');
    ctx.fill();
  }
  drawChart('chartCpu','cpu_usage','rgb(79,70,229)');
  drawChart('chartRam','ram_usage','rgb(22,163,74)');
  drawChart('chartDisco','disk_usage','rgb(245,158,11)');
})();
</script>
<?php endif; ?>

<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
