<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;
use LRV\App\Services\Plans\PlanFeatureService;

$sub = $subscription ?? [];
$planos = $planos ?? [];
$currentPlanId = $current_plan_id ?? 0;
$currentName = (string)($sub['plan_name'] ?? '');
$currentPrice = (float)($sub['price_monthly'] ?? 0);
$currentCpu = (int)($sub['cpu'] ?? 0);
$currentRam = round((int)($sub['ram'] ?? 0) / 1024);
$currentStorage = round((int)($sub['storage'] ?? 0) / 1024);
$subId = (int)($sub['id'] ?? 0);
$planType = (string)($sub['plan_type'] ?? 'vps');
$badge = PlanFeatureService::tipoPlanoBadge($planType);
$isUsd = I18n::moedaCodigo() === 'USD';
$curSymbol = $isUsd ? 'US$' : 'R$';
$convRate = 5.0;
try { $convRate = \LRV\Core\ConfiguracoesSistema::taxaConversaoUsd(); } catch (\Throwable) {}

$pageTitle = 'Alterar plano';
require __DIR__ . '/../_partials/layout-cliente-inicio.php';
?>

<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:24px;">
  <div>
    <div class="page-title">Alterar plano</div>
    <div class="page-subtitle" style="margin-bottom:0;">Escolha um novo plano para sua assinatura</div>
  </div>
  <a class="botao ghost sm" href="/cliente/assinaturas">← Voltar</a>
</div>

<!-- Plano atual -->
<div class="card-new" style="margin-bottom:20px;border-left:4px solid #4F46E5;">
  <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
    <span style="font-size:24px;"><?php echo $badge[3]; ?></span>
    <div>
      <div style="font-size:14px;font-weight:700;color:#0f172a;">Plano atual: <?php echo View::e($currentName); ?></div>
      <div style="font-size:12px;color:#64748b;"><?php echo $currentCpu; ?> vCPU · <?php echo $currentRam; ?> GB RAM · <?php echo $currentStorage; ?> GB SSD · <?php echo View::e(I18n::preco($currentPrice)); ?>/mês</div>
    </div>
    <span style="background:<?php echo $badge[1]; ?>;color:<?php echo $badge[2]; ?>;font-size:10px;font-weight:700;padding:2px 8px;border-radius:6px;margin-left:auto;"><?php echo View::e($badge[0]); ?></span>
  </div>
</div>

<?php if (empty($planos)): ?>
  <div class="card-new" style="text-align:center;padding:40px;">
    <div style="font-size:36px;margin-bottom:12px;">📋</div>
    <div style="font-size:15px;font-weight:600;margin-bottom:8px;">Nenhum outro plano disponível</div>
    <div style="font-size:13px;color:#64748b;">Não há outros planos do tipo <?php echo View::e($badge[0]); ?> disponíveis no momento.</div>
  </div>
<?php else: ?>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;margin-bottom:24px;">
  <?php foreach ($planos as $p):
    $pId = (int)($p['id'] ?? 0);
    $pName = (string)($p['name'] ?? '');
    $pDesc = (string)($p['description'] ?? '');
    $pPriceBrl = (float)($p['price_monthly'] ?? 0);
    $pPriceUsd = (float)($p['price_monthly_usd'] ?? 0);
    $pCpu = (int)($p['cpu'] ?? 0);
    $pRam = round((int)($p['ram'] ?? 0) / 1024);
    $pStorage = round((int)($p['storage'] ?? 0) / 1024);
    $pFeatured = (int)($p['is_featured'] ?? 0) === 1;
    $isUpgrade = $pPriceBrl > $currentPrice;
    $displayPrice = $isUsd ? ($pPriceUsd > 0 ? $pPriceUsd : round($pPriceBrl / $convRate, 2)) : $pPriceBrl;
    $diff = $pPriceBrl - $currentPrice;
  ?>
  <div class="card-new" style="<?php echo $pFeatured ? 'border-color:#4F46E5;box-shadow:0 4px 20px rgba(79,70,229,.12);' : ''; ?>position:relative;">
    <?php if ($pFeatured): ?>
      <div style="position:absolute;top:-10px;right:16px;background:linear-gradient(135deg,#4F46E5,#7C3AED);color:#fff;padding:3px 12px;border-radius:12px;font-size:10px;font-weight:700;text-transform:uppercase;">Popular</div>
    <?php endif; ?>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
      <div style="font-size:16px;font-weight:700;color:#0f172a;"><?php echo View::e($pName); ?></div>
      <span style="background:<?php echo $isUpgrade ? '#dcfce7' : '#fef3c7'; ?>;color:<?php echo $isUpgrade ? '#166534' : '#92400e'; ?>;font-size:10px;font-weight:700;padding:2px 8px;border-radius:6px;">
        <?php echo $isUpgrade ? '⬆ Upgrade' : '⬇ Downgrade'; ?>
      </span>
    </div>
    <?php if ($pDesc !== ''): ?>
      <div style="font-size:12px;color:#64748b;margin-bottom:12px;line-height:1.5;"><?php echo View::e($pDesc); ?></div>
    <?php endif; ?>
    <div style="font-size:28px;font-weight:900;color:#4F46E5;margin-bottom:4px;">
      <?php echo $curSymbol; ?> <?php echo $isUsd ? number_format($displayPrice, 2, '.', ',') : number_format($displayPrice, 2, ',', '.'); ?>
      <span style="font-size:13px;font-weight:400;color:#94a3b8;">/mês</span>
    </div>
    <div style="display:flex;gap:12px;font-size:12px;color:#64748b;margin-bottom:14px;padding-bottom:14px;border-bottom:1px solid #f1f5f9;">
      <span><?php echo $pCpu; ?> vCPU</span>
      <span><?php echo $pRam; ?> GB RAM</span>
      <span><?php echo $pStorage; ?> GB SSD</span>
    </div>
    <?php if ($diff != 0): ?>
      <div style="font-size:12px;color:<?php echo $diff > 0 ? '#166534' : '#92400e'; ?>;margin-bottom:12px;">
        <?php echo $diff > 0 ? '+' : ''; ?><?php echo View::e(I18n::preco(abs($diff))); ?>/mês em relação ao plano atual
      </div>
    <?php endif; ?>
    <button class="botao<?php echo $isUpgrade ? '' : ' ghost'; ?> sm" style="width:100%;" onclick="confirmarUpgrade(<?php echo $subId; ?>,<?php echo $pId; ?>,'<?php echo View::e($pName); ?>','<?php echo $isUpgrade ? 'upgrade' : 'downgrade'; ?>')">
      <?php echo $isUpgrade ? '⬆ Fazer upgrade' : '⬇ Fazer downgrade'; ?>
    </button>
  </div>
  <?php endforeach; ?>
</div>

<!-- Info -->
<div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:14px 16px;display:flex;align-items:flex-start;gap:10px;">
  <span style="font-size:18px;">💡</span>
  <div style="font-size:13px;color:#1e40af;line-height:1.6;">
    <strong>Como funciona:</strong> A alteração de plano é imediata. Os novos recursos (CPU, RAM, disco) são aplicados automaticamente no seu servidor. O valor da assinatura é atualizado a partir da próxima cobrança.
  </div>
</div>

<?php endif; ?>

<!-- Modal de confirmação -->
<div id="upgradeModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:999;align-items:center;justify-content:center;">
  <div style="background:#fff;border-radius:20px;padding:32px;max-width:420px;width:90%;text-align:center;">
    <div style="font-size:36px;margin-bottom:12px;" id="upgradeIcon">⬆</div>
    <div style="font-size:18px;font-weight:700;margin-bottom:8px;" id="upgradeTitle">Confirmar upgrade</div>
    <div style="font-size:14px;color:#64748b;margin-bottom:20px;" id="upgradeDesc">Deseja alterar para o plano <strong id="upgradePlanName"></strong>?</div>
    <div id="upgradeErro" style="display:none;background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:10px;border-radius:8px;font-size:13px;margin-bottom:12px;"></div>
    <div style="display:flex;gap:10px;justify-content:center;">
      <button class="botao ghost sm" onclick="fecharUpgradeModal()">Cancelar</button>
      <button class="botao sm" id="upgradeBtn" onclick="executarUpgrade()">Confirmar</button>
    </div>
  </div>
</div>

<script>
var _upSubId=0,_upPlanId=0;
function confirmarUpgrade(subId,planId,planName,type){
  _upSubId=subId;_upPlanId=planId;
  document.getElementById('upgradePlanName').textContent=planName;
  document.getElementById('upgradeIcon').textContent=type==='upgrade'?'⬆':'⬇';
  document.getElementById('upgradeTitle').textContent=type==='upgrade'?'Confirmar upgrade':'Confirmar downgrade';
  document.getElementById('upgradeErro').style.display='none';
  document.getElementById('upgradeModal').style.display='flex';
}
function fecharUpgradeModal(){document.getElementById('upgradeModal').style.display='none';}
function executarUpgrade(){
  var btn=document.getElementById('upgradeBtn');
  btn.disabled=true;btn.textContent='Processando...';
  document.getElementById('upgradeErro').style.display='none';
  var fd=new FormData();
  fd.append('_csrf','<?php echo View::e(\LRV\Core\Csrf::token()); ?>');
  fd.append('subscription_id',_upSubId);
  fd.append('new_plan_id',_upPlanId);
  fetch('/cliente/assinaturas/upgrade',{method:'POST',body:fd,credentials:'same-origin'})
    .then(function(r){return r.json();})
    .then(function(d){
      if(d.ok){
        window.location.href='/cliente/assinaturas';
      } else {
        document.getElementById('upgradeErro').textContent=d.erro||'Erro ao processar.';
        document.getElementById('upgradeErro').style.display='block';
        btn.disabled=false;btn.textContent='Confirmar';
      }
    })
    .catch(function(){
      document.getElementById('upgradeErro').textContent='Erro de conexão.';
      document.getElementById('upgradeErro').style.display='block';
      btn.disabled=false;btn.textContent='Confirmar';
    });
}
</script>

<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
