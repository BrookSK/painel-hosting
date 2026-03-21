<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;

$metricasArr = is_array($metricas ?? null) ? $metricas : [];
$vpsId       = (int)($vps['id'] ?? 0);

$labels = []; $cpuData = []; $ramData = []; $diskData = [];
foreach (array_slice($metricasArr, -30) as $m) {
    if (!is_array($m)) continue;
    $labels[]   = substr((string)($m['timestamp'] ?? ''), 11, 5);
    $cpuData[]  = round((float)($m['cpu_usage']  ?? 0), 2);
    $ramData[]  = round((float)($m['ram_usage']  ?? 0), 2);
    $diskData[] = round((float)($m['disk_usage'] ?? 0), 2);
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

$pageTitle    = I18n::tf('monitoramento.titulo_vps', $vpsId);
$clienteNome  = (string)($cliente['name'] ?? '');
$clienteEmail = (string)($cliente['email'] ?? '');
require __DIR__ . '/../_partials/layout-cliente-inicio.php';
?>

<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:24px;">
  <div>
    <div class="page-title"><?php echo View::e(I18n::t('monitoramento.titulo')); ?></div>
    <div class="page-subtitle" style="margin-bottom:0;">VPS #<?php echo $vpsId; ?></div>
  </div>
  <a href="/cliente/monitoramento" class="botao ghost sm">← <?php echo View::e(I18n::t('geral.voltar')); ?></a>
</div>

<?php if (empty($servidor)): ?>
  <div class="card-new"><p style="margin:0;color:#64748b;"><?php echo View::e(I18n::t('monitoramento.sem_node')); ?></p></div>
<?php else: ?>

  <div class="card-new" style="margin-bottom:14px;">
    <div class="grid">
      <div>
        <div style="font-size:13px;margin-bottom:4px;"><strong><?php echo View::e(I18n::t('monitoramento.servidor')); ?>:</strong> <?php echo View::e((string)($servidor['hostname'] ?? '')); ?></div>
        <div style="font-size:13px;"><strong><?php echo View::e(I18n::t('monitoramento.ip')); ?>:</strong> <?php echo View::e((string)($servidor['ip_address'] ?? '')); ?></div>
      </div>
      <div>
        <div style="font-size:13px;margin-bottom:4px;"><strong><?php echo View::e(I18n::t('geral.status')); ?>:</strong> <?php echo View::e((string)($servidor['status'] ?? '')); ?></div>
        <div style="font-size:13px;"><strong><?php echo View::e(I18n::t('monitoramento.coletas')); ?>:</strong> <?php echo count($metricasArr); ?></div>
      </div>
    </div>
  </div>

  <?php if ($ultima !== null):
    $cv = (float)($ultima['cpu_usage'] ?? 0);
    $rv = (float)($ultima['ram_usage'] ?? 0);
    $dv = (float)($ultima['disk_usage'] ?? 0);
  ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px;margin-bottom:18px;">
      <?php foreach ([[I18n::t('monitoramento.cpu_agora'), $cv], [I18n::t('monitoramento.ram_agora'), $rv], [I18n::t('monitoramento.disco_agora'), $dv]] as [$lbl, $val]): ?>
        <div class="card-new" style="text-align:center;">
          <div style="font-size:28px;font-weight:700;line-height:1;margin-bottom:4px;color:<?php echo pctColor($val); ?>;"><?php echo number_format($val, 1); ?>%</div>
          <div style="font-size:12px;color:#64748b;"><?php echo View::e($lbl); ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($cpuData)): ?>
    <p style="font-size:12px;color:#94a3b8;margin-bottom:14px;"><?php echo View::e(I18n::t('monitoramento.atualizacao_auto')); ?> <span id="lastRefresh"><?php echo View::e(I18n::t('monitoramento.agora')); ?></span></p>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:14px;margin-bottom:18px;">
      <?php foreach ([['chartCpu',I18n::t('monitoramento.cpu') . ' (%)','#4F46E5'],['chartRam',I18n::t('monitoramento.ram') . ' (%)','#7C3AED'],['chartDisk',I18n::t('monitoramento.disco') . ' (%)','#0ea5e9']] as [$cid,$clbl,$ccol]): ?>
        <div class="card-new">
          <div style="font-size:13px;font-weight:600;margin-bottom:8px;color:<?php echo $ccol; ?>;"><?php echo View::e($clbl); ?></div>
          <canvas id="<?php echo $cid; ?>" height="110"></canvas>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <div class="card-new">
    <div class="card-new-title" style="margin-bottom:12px;"><?php echo View::e(I18n::t('monitoramento.historico')); ?></div>
    <div style="overflow:auto;">
      <table style="width:100%;border-collapse:collapse;">
        <thead>
          <tr>
            <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;"><?php echo View::e(I18n::t('monitoramento.data_hora')); ?></th>
            <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;"><?php echo View::e(I18n::t('monitoramento.cpu')); ?></th>
            <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;"><?php echo View::e(I18n::t('monitoramento.ram')); ?></th>
            <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;"><?php echo View::e(I18n::t('monitoramento.disco')); ?></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach (array_reverse($metricasArr) as $m):
            $cv2 = (float)($m['cpu_usage'] ?? 0);
            $rv2 = (float)($m['ram_usage'] ?? 0);
            $dv2 = (float)($m['disk_usage'] ?? 0);
          ?>
            <tr>
              <td style="padding:10px;border-bottom:1px solid #f1f5f9;font-size:13px;"><?php echo View::e((string)($m['timestamp'] ?? '')); ?></td>
              <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><span style="color:<?php echo pctColor($cv2); ?>;font-weight:600;"><?php echo number_format($cv2, 2); ?>%</span></td>
              <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><span style="color:<?php echo pctColor($rv2); ?>;font-weight:600;"><?php echo number_format($rv2, 2); ?>%</span></td>
              <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><span style="color:<?php echo pctColor($dv2); ?>;font-weight:600;"><?php echo number_format($dv2, 2); ?>%</span></td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($metricasArr)): ?>
            <tr><td colspan="4" style="padding:12px;color:#94a3b8;"><?php echo View::e(I18n::t('monitoramento.sem_metricas')); ?></td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

<?php endif; ?>

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

<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
