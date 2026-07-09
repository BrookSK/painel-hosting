<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;

$sub = $subscription ?? [];
$addons = $addons ?? [];
$activeIds = $active_addon_ids ?? [];
$subId = $sub_id ?? 0;
$planName = (string)($sub['plan_name'] ?? '');
$isUsd = I18n::moedaCodigo() === 'USD';
$curSymbol = $isUsd ? 'US$' : 'R$';
$convRate = 5.0;
try { $convRate = \LRV\Core\ConfiguracoesSistema::taxaConversaoUsd(); } catch (\Throwable) {}

$pageTitle = 'Serviços adicionais';
require __DIR__ . '/../_partials/layout-cliente-inicio.php';
?>

<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:24px;">
  <div>
    <div class="page-title">Serviços adicionais</div>
    <div class="page-subtitle" style="margin-bottom:0;">Contrate ou cancele serviços extras para <?php echo View::e($planName); ?></div>
  </div>
  <a class="botao ghost sm" href="/cliente/assinaturas">← Voltar</a>
</div>

<?php if (empty($addons)): ?>
  <div class="card-new" style="text-align:center;padding:40px;">
    <div style="font-size:36px;margin-bottom:12px;"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="16.5" y1="9.4" x2="7.5" y2="4.21"/><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg></div>
    <div style="font-size:15px;font-weight:600;margin-bottom:8px;">Nenhum serviço adicional disponível</div>
    <div style="font-size:13px;color:#64748b;">Este plano não possui serviços adicionais configurados.</div>
  </div>
<?php else: ?>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px;">
  <?php foreach ($addons as $a):
    $aId = (int)($a['id'] ?? 0);
    $aName = (string)($a['name'] ?? '');
    $aDesc = (string)($a['description'] ?? '');
    $aPriceBrl = (float)($a['price'] ?? 0);
    $aPriceUsd = (float)($a['price_usd'] ?? 0);
    $aContratado = (bool)($a['contratado'] ?? false);
    $displayPrice = $isUsd ? ($aPriceUsd > 0 ? $aPriceUsd : round($aPriceBrl / $convRate, 2)) : $aPriceBrl;
  ?>
  <div class="card-new" id="addon-card-<?php echo $aId; ?>" style="<?php echo $aContratado ? 'border-color:#16a34a;background:#f0fdf4;' : ''; ?>">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
      <div style="font-size:15px;font-weight:700;color:#0f172a;"><?php echo View::e($aName); ?></div>
      <?php if ($aContratado): ?>
        <span style="background:#dcfce7;color:#166534;font-size:10px;font-weight:700;padding:2px 8px;border-radius:6px;">✓ Ativo</span>
      <?php endif; ?>
    </div>
    <?php if ($aDesc !== ''): ?>
      <div style="font-size:13px;color:#64748b;margin-bottom:12px;line-height:1.5;"><?php echo View::e($aDesc); ?></div>
    <?php endif; ?>
    <div style="font-size:22px;font-weight:800;color:#4F46E5;margin-bottom:14px;">
      <?php echo $curSymbol; ?> <?php echo $isUsd ? number_format($displayPrice, 2, '.', ',') : number_format($displayPrice, 2, ',', '.'); ?>
      <span style="font-size:12px;font-weight:400;color:#94a3b8;">/mês</span>
    </div>
    <?php if ($aContratado): ?>
      <button class="botao ghost sm" style="width:100%;color:#ef4444;border-color:#fecaca;" onclick="cancelarAddon(<?php echo $aId; ?>,'<?php echo View::e($aName); ?>')">Cancelar serviço</button>
    <?php else: ?>
      <button class="botao sm" style="width:100%;" onclick="contratarAddon(<?php echo $aId; ?>,'<?php echo View::e($aName); ?>')">Contratar</button>
    <?php endif; ?>
  </div>
  <?php endforeach; ?>
</div>

<div style="margin-top:20px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:14px 16px;display:flex;align-items:flex-start;gap:10px;">
  <span style="font-size:18px;"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18h6"/><path d="M10 22h4"/><path d="M12 2a7 7 0 0 0-4 12.7V17h8v-2.3A7 7 0 0 0 12 2z"/></svg></span>
  <div style="font-size:13px;color:#1e40af;line-height:1.6;">
    Ao contratar um serviço adicional, uma cobrança é gerada imediatamente e o valor é adicionado à sua assinatura mensal. Ao cancelar, o serviço é removido e o valor é descontado da próxima cobrança.
  </div>
</div>

<?php endif; ?>

<div id="addonErro" style="display:none;position:fixed;bottom:20px;left:50%;transform:translateX(-50%);background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:12px 20px;border-radius:10px;font-size:13px;z-index:999;box-shadow:0 4px 20px rgba(0,0,0,.15);"></div>
<div id="addonOk" style="display:none;position:fixed;bottom:20px;left:50%;transform:translateX(-50%);background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;padding:12px 20px;border-radius:10px;font-size:13px;z-index:999;box-shadow:0 4px 20px rgba(0,0,0,.15);"></div>

<script>
var _csrf='<?php echo View::e(\LRV\Core\Csrf::token()); ?>';
var _subId=<?php echo (int)$subId; ?>;

function showMsg(type,msg){
  var el=document.getElementById(type==='ok'?'addonOk':'addonErro');
  el.textContent=msg;el.style.display='block';
  setTimeout(function(){el.style.display='none';},4000);
}

function contratarAddon(addonId,name){
  if(!confirm('Contratar "'+name+'"? Uma cobrança será gerada imediatamente.'))return;
  var fd=new FormData();fd.append('_csrf',_csrf);fd.append('subscription_id',_subId);fd.append('addon_id',addonId);
  fetch('/cliente/assinaturas/addons/contratar',{method:'POST',body:fd,credentials:'same-origin'})
    .then(function(r){return r.json();})
    .then(function(d){
      if(d.ok){showMsg('ok',d.mensagem||'Contratado!');setTimeout(function(){location.reload();},1500);}
      else{showMsg('erro',d.erro||'Erro ao contratar.');}
    }).catch(function(){showMsg('erro','Erro de conexão.');});
}

function cancelarAddon(addonId,name){
  if(!confirm('Cancelar "'+name+'"? O serviço será removido da sua assinatura.'))return;
  var fd=new FormData();fd.append('_csrf',_csrf);fd.append('item_id',addonId);
  fetch('/cliente/assinaturas/addons/cancelar',{method:'POST',body:fd,credentials:'same-origin'})
    .then(function(r){return r.json();})
    .then(function(d){
      if(d.ok){showMsg('ok',d.mensagem||'Cancelado!');setTimeout(function(){location.reload();},1500);}
      else{showMsg('erro',d.erro||'Erro ao cancelar.');}
    }).catch(function(){showMsg('erro','Erro de conexão.');});
}
</script>

<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
