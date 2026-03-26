<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;

$metricas = is_array($metricas ?? null) ? $metricas : [];
$total = count($metricas);
// Reverter para ordem cronológica (mais antigo primeiro)
$metricas = array_reverse($metricas);

$labels = [];
$fullTimestamps = [];
$cpuData = [];
$ramData = [];
$diskData = [];
foreach ($metricas as $m) {
    $ts = (string)($m['timestamp'] ?? '');
    $labels[] = substr($ts, 11, 5); // HH:MM
    $fullTimestamps[] = $ts;
    $cpuData[] = round((float)($m['cpu_usage'] ?? 0), 1);
    $ramData[] = round((float)($m['ram_usage'] ?? 0), 1);
    $diskData[] = round((float)($m['disk_usage'] ?? 0), 1);
}

$pageTitle = 'Monitoramento — ' . (string)($servidor['hostname'] ?? '');
require __DIR__ . '/../_partials/layout-equipe-inicio.php';
?>
<div class="page-title">Monitoramento</div>
<div class="page-subtitle">Servidor <?php echo View::e((string)($servidor['hostname'] ?? '')); ?></div>

<div class="card-new" style="margin-bottom:14px;">
  <div class="grid">
    <div>
      <div class="texto" style="margin:0;"><strong>ID:</strong> #<?php echo (int)($servidor['id'] ?? 0); ?></div>
      <div class="texto" style="margin:0;"><strong>IP:</strong> <?php echo View::e((string)($servidor['ip_address'] ?? '')); ?></div>
    </div>
    <div>
      <div class="texto" style="margin:0;"><strong>Status:</strong> <?php echo View::e((string)($servidor['status'] ?? '')); ?></div>
      <div class="texto" style="margin:0;"><strong>Coletas:</strong> <?php echo $total; ?> (últimas 4h)</div>
    </div>
  </div>
</div>

<?php if ($total > 0): ?>
<!-- Gauges atuais -->
<?php
  $lastCpu = end($cpuData) ?: 0;
  $lastRam = end($ramData) ?: 0;
  $lastDisk = end($diskData) ?: 0;
  function gaugeColor(float $v): string {
      if ($v >= 90) return '#ef4444';
      if ($v >= 70) return '#f59e0b';
      return '#10b981';
  }
?>
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:20px;">
  <?php foreach ([['CPU', $lastCpu], ['RAM', $lastRam], ['Disco', $lastDisk]] as [$label, $val]): ?>
  <div class="card-new" style="text-align:center;padding:20px;">
    <div style="position:relative;width:90px;height:90px;margin:0 auto 10px;">
      <svg viewBox="0 0 36 36" style="width:90px;height:90px;transform:rotate(-90deg);">
        <circle cx="18" cy="18" r="15.9" fill="none" stroke="#e2e8f0" stroke-width="3"/>
        <circle cx="18" cy="18" r="15.9" fill="none" stroke="<?php echo gaugeColor($val); ?>" stroke-width="3"
                stroke-dasharray="<?php echo round($val, 1); ?> <?php echo round(100 - $val, 1); ?>"
                stroke-linecap="round"/>
      </svg>
      <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:700;color:#1e293b;">
        <?php echo number_format($val, 1, ',', ''); ?>%
      </div>
    </div>
    <div style="font-size:13px;font-weight:600;color:#475569;"><?php echo $label; ?></div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Gráficos -->
<div class="card-new" style="margin-bottom:14px;">
  <div class="card-new-title" style="margin-bottom:12px;">CPU (%)</div>
  <canvas id="chartCpu" height="180" style="width:100%;"></canvas>
</div>
<div class="card-new" style="margin-bottom:14px;">
  <div class="card-new-title" style="margin-bottom:12px;">RAM (%)</div>
  <canvas id="chartRam" height="180" style="width:100%;"></canvas>
</div>
<div class="card-new" style="margin-bottom:14px;">
  <div class="card-new-title" style="margin-bottom:12px;">Disco (%)</div>
  <canvas id="chartDisk" height="180" style="width:100%;"></canvas>
</div>

<!-- Tabela resumida (últimas 12 coletas) -->
<div class="card-new">
  <div class="card-new-title" style="margin-bottom:8px;">Últimas coletas</div>
  <div style="overflow:auto;max-height:400px;">
    <table>
      <thead><tr><th>Hora</th><th>CPU</th><th>RAM</th><th>Disco</th></tr></thead>
      <tbody>
        <?php foreach (array_slice(array_reverse($metricas), 0, 12) as $m): ?>
        <tr>
          <td><?php echo View::e((string)($m['timestamp'] ?? '')); ?></td>
          <td><code><?php echo number_format((float)($m['cpu_usage'] ?? 0), 2, ',', '.'); ?>%</code></td>
          <td><code><?php echo number_format((float)($m['ram_usage'] ?? 0), 2, ',', '.'); ?>%</code></td>
          <td><code><?php echo number_format((float)($m['disk_usage'] ?? 0), 2, ',', '.'); ?>%</code></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
var labels=<?php echo json_encode($labels); ?>;
var fullTs=<?php echo json_encode($fullTimestamps); ?>;
var cpuD=<?php echo json_encode($cpuData); ?>;
var ramD=<?php echo json_encode($ramData); ?>;
var diskD=<?php echo json_encode($diskData); ?>;

var chartInstances={};

function drawChart(canvasId,data,color,unit){
  var c=document.getElementById(canvasId);if(!c)return;
  var ctx=c.getContext('2d');
  var dpr=window.devicePixelRatio||1;
  var rect=c.getBoundingClientRect();
  c.width=rect.width*dpr;c.height=rect.height*dpr;
  ctx.scale(dpr,dpr);
  var W=rect.width,H=rect.height,pad={t:10,r:10,b:28,l:40};
  var gW=W-pad.l-pad.r,gH=H-pad.t-pad.b;
  var n=data.length;if(n<2)return;
  var maxV=Math.max(100,...data);

  // Grid
  ctx.strokeStyle='#e2e8f0';ctx.lineWidth=0.5;ctx.font='11px system-ui';ctx.fillStyle='#94a3b8';ctx.textAlign='right';
  for(var i=0;i<=4;i++){
    var y=pad.t+gH-gH*(i/4);
    ctx.beginPath();ctx.moveTo(pad.l,y);ctx.lineTo(W-pad.r,y);ctx.stroke();
    ctx.fillText(Math.round(maxV*i/4)+'%',pad.l-6,y+4);
  }
  // X labels — garantir que não ultrapasse o array
  ctx.textAlign='center';ctx.fillStyle='#94a3b8';
  var step=Math.max(1,Math.floor(n/8));
  for(var i=0;i<n;i+=step){
    if(i<labels.length){
      var x=pad.l+gW*i/(n-1);
      ctx.fillText(labels[i]||'',x,H-6);
    }
  }
  // Line
  ctx.beginPath();ctx.strokeStyle=color;ctx.lineWidth=2;ctx.lineJoin='round';
  for(var i=0;i<n;i++){
    var x=pad.l+gW*i/(n-1);
    var y=pad.t+gH-gH*(data[i]/maxV);
    if(i===0)ctx.moveTo(x,y);else ctx.lineTo(x,y);
  }
  ctx.stroke();
  // Fill
  ctx.lineTo(pad.l+gW,pad.t+gH);ctx.lineTo(pad.l,pad.t+gH);ctx.closePath();
  ctx.fillStyle=color.replace(')',',0.08)').replace('rgb','rgba');ctx.fill();

  // Guardar metadados para tooltip
  chartInstances[canvasId]={data:data,color:color,unit:unit||'%',pad:pad,gW:gW,gH:gH,maxV:maxV,n:n,W:W,H:H};
}

// Tooltip
var tooltip=document.createElement('div');
tooltip.style.cssText='position:fixed;pointer-events:none;background:#1e293b;color:#fff;font-size:12px;padding:8px 12px;border-radius:6px;display:none;z-index:9999;line-height:1.5;box-shadow:0 4px 12px rgba(0,0,0,.15);';
document.body.appendChild(tooltip);

function handleChartHover(e){
  var c=e.target;
  var info=chartInstances[c.id];
  if(!info)return;
  var rect=c.getBoundingClientRect();
  var mx=e.clientX-rect.left;
  var my=e.clientY-rect.top;
  var p=info.pad;
  // Verificar se está na área do gráfico
  if(mx<p.l||mx>p.l+info.gW||my<p.t||my>p.t+info.gH){
    tooltip.style.display='none';return;
  }
  // Encontrar ponto mais próximo
  var idx=Math.round((mx-p.l)/info.gW*(info.n-1));
  if(idx<0)idx=0;if(idx>=info.n)idx=info.n-1;
  var val=info.data[idx];
  var ts=fullTs[idx]||'';
  // Posição do ponto
  var px=p.l+info.gW*idx/(info.n-1);
  var py=p.t+info.gH-info.gH*(val/info.maxV);
  // Desenhar indicador (redesenhar gráfico + ponto)
  drawChart(c.id,info.data,info.color,info.unit);
  var ctx=c.getContext('2d');
  var dpr=window.devicePixelRatio||1;
  ctx.save();ctx.scale(dpr,dpr);
  // Linha vertical
  ctx.beginPath();ctx.strokeStyle='rgba(148,163,184,0.4)';ctx.lineWidth=1;ctx.setLineDash([4,3]);
  ctx.moveTo(px,p.t);ctx.lineTo(px,p.t+info.gH);ctx.stroke();ctx.setLineDash([]);
  // Ponto
  ctx.beginPath();ctx.arc(px,py,5,0,Math.PI*2);ctx.fillStyle=info.color;ctx.fill();
  ctx.beginPath();ctx.arc(px,py,3,0,Math.PI*2);ctx.fillStyle='#fff';ctx.fill();
  ctx.restore();
  // Tooltip
  tooltip.innerHTML='<div style="font-weight:600;margin-bottom:2px;">'+ts+'</div><div>'+val.toFixed(1)+info.unit+'</div>';
  tooltip.style.display='block';
  var tx=e.clientX+14;var ty=e.clientY-50;
  if(tx+160>window.innerWidth)tx=e.clientX-160;
  if(ty<0)ty=e.clientY+14;
  tooltip.style.left=tx+'px';tooltip.style.top=ty+'px';
}

function handleChartLeave(e){
  var c=e.target;var info=chartInstances[c.id];
  if(!info)return;
  tooltip.style.display='none';
  drawChart(c.id,info.data,info.color,info.unit);
}

drawChart('chartCpu',cpuD,'rgb(79,70,229)','%');
drawChart('chartRam',ramD,'rgb(16,185,129)','%');
drawChart('chartDisk',diskD,'rgb(245,158,11)','%');

['chartCpu','chartRam','chartDisk'].forEach(function(id){
  var el=document.getElementById(id);
  if(!el)return;
  el.style.cursor='crosshair';
  el.addEventListener('mousemove',handleChartHover);
  el.addEventListener('mouseleave',handleChartLeave);
});

window.addEventListener('resize',function(){
  drawChart('chartCpu',cpuD,'rgb(79,70,229)','%');
  drawChart('chartRam',ramD,'rgb(16,185,129)','%');
  drawChart('chartDisk',diskD,'rgb(245,158,11)','%');
});
</script>

<?php else: ?>
<div class="card-new"><p class="texto">Ainda não há métricas para este servidor.</p></div>
<?php endif; ?>

<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
