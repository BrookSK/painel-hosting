<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\Csrf;
use LRV\Core\I18n;

$plano = is_array($plano ?? null) ? $plano : [];
$addons = is_array($addons ?? null) ? $addons : [];
$precoBase = (float)($plano['price_monthly'] ?? 0);
$planId = (int)($plano['id'] ?? 0);
$clienteCpf = trim((string)($cliente['cpf_cnpj'] ?? ''));
$isBrl = I18n::moedaCodigo() === 'BRL';

$pageTitle = 'Configurar plano';
$clienteNome = (string)($cliente['name'] ?? '');
$clienteEmail = (string)($cliente['email'] ?? '');
require __DIR__ . '/../_partials/layout-cliente-inicio.php';
?>

<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:24px;">
  <div>
    <div class="page-title">Configurar plano</div>
    <div class="page-subtitle" style="margin-bottom:0;"><?php echo View::e((string)($plano['name'] ?? '')); ?></div>
  </div>
  <a href="/cliente/planos" class="botao ghost sm">← Voltar aos planos</a>
</div>

<div class="grid" style="grid-template-columns:1fr 340px;gap:16px;align-items:start;">
  <div>
    <!-- Resumo do plano -->
    <div class="card-new" style="margin-bottom:14px;">
      <div class="card-new-title" style="margin-bottom:8px;"><?php echo View::e((string)($plano['name'] ?? '')); ?></div>
      <p style="font-size:13px;color:#64748b;margin-bottom:12px;"><?php echo View::e((string)($plano['description'] ?? '')); ?></p>
      <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px;">
        <span class="badge-new"><?php echo (int)($plano['cpu'] ?? 0); ?> vCPU</span>
        <span class="badge-new"><?php echo round((int)($plano['ram'] ?? 0) / 1024); ?> GB RAM</span>
        <span class="badge-new"><?php echo round((int)($plano['storage'] ?? 0) / 1024); ?> GB SSD</span>
      </div>
      <div id="planPriceDisplay" style="font-size:20px;font-weight:700;color:#1e293b;">
        <?php echo View::e(I18n::preco($precoBase)); ?><span style="font-size:13px;font-weight:400;color:#64748b;">/mês</span>
      </div>
    </div>

    <!-- Moeda -->
    <div class="card-new" style="margin-bottom:14px;">
      <div style="font-size:13px;font-weight:600;margin-bottom:8px;">💱 Moeda</div>
      <div style="display:flex;gap:8px;">
        <label style="display:flex;align-items:center;gap:6px;padding:10px 16px;border:1.5px solid #4F46E5;border-radius:10px;cursor:pointer;font-size:13px;flex:1;justify-content:center;background:#f5f3ff;" class="cur-label">
          <input type="radio" name="sel_currency" value="BRL" <?php echo $isBrl ? 'checked' : ''; ?> onchange="selCurrency('BRL')" style="accent-color:#4F46E5;" /> 🇧🇷 Real
        </label>
        <label style="display:flex;align-items:center;gap:6px;padding:10px 16px;border:1.5px solid #e2e8f0;border-radius:10px;cursor:pointer;font-size:13px;flex:1;justify-content:center;" class="cur-label">
          <input type="radio" name="sel_currency" value="USD" <?php echo !$isBrl ? 'checked' : ''; ?> onchange="selCurrency('USD')" style="accent-color:#4F46E5;" /> 🇺🇸 Dólar
        </label>
      </div>
    </div>

    <!-- Período -->
    <div class="card-new" style="margin-bottom:14px;">
      <div style="font-size:13px;font-weight:600;margin-bottom:8px;">📅 Período</div>
      <div style="display:flex;flex-direction:column;gap:8px;">
        <label style="display:flex;align-items:center;justify-content:space-between;padding:12px 14px;border:1.5px solid #4F46E5;border-radius:10px;cursor:pointer;background:#f5f3ff;" class="per-label" data-per="1">
          <div style="display:flex;align-items:center;gap:8px;">
            <input type="radio" name="sel_periodo" value="1" checked onchange="selPeriodo(1)" style="accent-color:#4F46E5;" />
            <span style="font-size:13px;font-weight:600;">Mensal</span>
          </div>
          <span class="per-price" style="font-size:13px;font-weight:700;color:#4F46E5;"></span>
        </label>
        <?php if ((float)($plano['price_semiannual'] ?? 0) > 0 || (float)($plano['price_semiannual_usd'] ?? 0) > 0): ?>
        <label style="display:flex;align-items:center;justify-content:space-between;padding:12px 14px;border:1.5px solid #e2e8f0;border-radius:10px;cursor:pointer;" class="per-label" data-per="6">
          <div style="display:flex;align-items:center;gap:8px;">
            <input type="radio" name="sel_periodo" value="6" onchange="selPeriodo(6)" style="accent-color:#4F46E5;" />
            <span style="font-size:13px;font-weight:600;">Semestral</span>
            <span style="font-size:11px;padding:2px 8px;border-radius:99px;background:#dcfce7;color:#166534;font-weight:600;">Economia</span>
          </div>
          <span class="per-price" style="font-size:13px;font-weight:700;color:#4F46E5;"></span>
        </label>
        <?php endif; ?>
        <?php if ((float)($plano['price_annual'] ?? 0) > 0 || (float)($plano['price_annual_usd'] ?? 0) > 0): ?>
        <label style="display:flex;align-items:center;justify-content:space-between;padding:12px 14px;border:1.5px solid #e2e8f0;border-radius:10px;cursor:pointer;" class="per-label" data-per="12">
          <div style="display:flex;align-items:center;gap:8px;">
            <input type="radio" name="sel_periodo" value="12" onchange="selPeriodo(12)" style="accent-color:#4F46E5;" />
            <span style="font-size:13px;font-weight:600;">Anual</span>
            <span style="font-size:11px;padding:2px 8px;border-radius:99px;background:#dcfce7;color:#166534;font-weight:600;">Melhor preço</span>
          </div>
          <span class="per-price" style="font-size:13px;font-weight:700;color:#4F46E5;"></span>
        </label>
        <?php endif; ?>
      </div>
    </div>

    <!-- Addons selecionáveis -->
    <?php if (!empty($addons)): ?>
    <div class="card-new">
      <div class="card-new-title" style="margin-bottom:4px;">Serviços adicionais</div>
      <p style="font-size:12px;color:#64748b;margin-bottom:14px;">Selecione os serviços extras que deseja incluir na sua assinatura.</p>
      <div style="display:flex;flex-direction:column;gap:10px;">
        <?php foreach ($addons as $i => $a):
          $aId = (int)($a['id'] ?? $i);
          $aName = (string)($a['name'] ?? '');
          $aDesc = (string)($a['description'] ?? '');
          $aPrice = (float)($a['price'] ?? 0);
        ?>
        <label style="display:flex;align-items:center;gap:12px;padding:14px;border:1.5px solid #e2e8f0;border-radius:12px;cursor:pointer;transition:all .15s;" class="addon-card" data-price="<?php echo $aPrice; ?>" data-price-usd="<?php echo (float)($a['price_usd'] ?? 0); ?>" data-price-annual="<?php echo (float)($a['price_annual'] ?? 0); ?>" data-price-annual-usd="<?php echo (float)($a['price_annual_usd'] ?? 0); ?>">
          <input type="checkbox" name="addons[]" value="<?php echo $aId; ?>" class="addon-check" style="accent-color:#4F46E5;width:18px;height:18px;flex-shrink:0;" />
          <div style="flex:1;">
            <div style="font-size:14px;font-weight:600;color:#1e293b;"><?php echo View::e($aName); ?></div>
            <?php if ($aDesc !== ''): ?>
              <div style="font-size:12px;color:#64748b;margin-top:2px;"><?php echo View::e($aDesc); ?></div>
            <?php endif; ?>
          </div>
          <div style="font-size:14px;font-weight:700;color:#4F46E5;white-space:nowrap;" class="addon-price-display">
            +<?php echo View::e(I18n::preco($aPrice)); ?>/mês
          </div>
        </label>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <!-- Resumo do pedido -->
  <div>
    <div class="card-new" style="position:sticky;top:20px;">
      <div class="card-new-title" style="margin-bottom:12px;">Resumo do pedido</div>
      <div style="display:flex;flex-direction:column;gap:8px;font-size:14px;">
        <div style="display:flex;justify-content:space-between;">
          <span><?php echo View::e((string)($plano['name'] ?? '')); ?></span>
          <span><?php echo View::e(I18n::preco($precoBase)); ?></span>
        </div>
        <div id="addons-resumo"></div>
        <div style="border-top:1px solid #e2e8f0;padding-top:10px;margin-top:4px;display:flex;justify-content:space-between;font-size:18px;font-weight:700;">
          <span>Total</span>
          <span id="total-preco" style="color:#4F46E5;"><?php echo View::e(I18n::preco($precoBase)); ?></span>
        </div>
        <div style="font-size:11px;color:#94a3b8;" id="perInfo">por mês</div>
      </div>

      <form method="post" action="/cliente/assinar" style="margin-top:16px;">
        <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>" />
        <input type="hidden" name="plan_id" value="<?php echo $planId; ?>" />
        <input type="hidden" name="addons_ids" id="addons_ids" value="" />

        <?php if ($clienteCpf === ''): ?>
        <div style="margin-bottom:12px;" id="cpfField">
          <label style="display:block;font-size:13px;margin-bottom:6px;"><?php echo View::e(I18n::t('checkout.cpf_cnpj')); ?></label>
          <input class="input" type="text" name="cpf_cnpj" placeholder="000.000.000-00" maxlength="18" inputmode="numeric" style="max-width:240px;" />
          <p style="font-size:11px;color:#94a3b8;margin-top:4px;"><?php echo View::e(I18n::t('checkout.cpf_obrigatorio')); ?></p>
        </div>
        <?php endif; ?>

        <div style="margin-bottom:12px;">
          <label style="display:block;font-size:13px;margin-bottom:6px;"><?php echo View::e(I18n::t('checkout.forma_pagamento')); ?></label>
          <div style="display:flex;gap:8px;flex-wrap:wrap;" id="gwBrl">
            <label style="display:flex;align-items:center;gap:6px;padding:10px 16px;border:1.5px solid #4F46E5;border-radius:10px;cursor:pointer;font-size:13px;flex:1;justify-content:center;background:#f5f3ff;" class="gw-label">
              <input type="radio" name="gateway" value="PIX" checked style="accent-color:#4F46E5;" /> PIX
            </label>
            <label style="display:flex;align-items:center;gap:6px;padding:10px 16px;border:1.5px solid #e2e8f0;border-radius:10px;cursor:pointer;font-size:13px;flex:1;justify-content:center;" class="gw-label">
              <input type="radio" name="gateway" value="BOLETO" style="accent-color:#4F46E5;" /> Boleto
            </label>
            <label style="display:flex;align-items:center;gap:6px;padding:10px 16px;border:1.5px solid #e2e8f0;border-radius:10px;cursor:pointer;font-size:13px;flex:1;justify-content:center;" class="gw-label">
              <input type="radio" name="gateway" value="CREDIT_CARD" style="accent-color:#4F46E5;" /> <?php echo View::e(I18n::t('checkout.cartao')); ?>
            </label>
          </div>
          <div style="display:flex;gap:8px;flex-wrap:wrap;display:none;" id="gwUsd">
            <label style="display:flex;align-items:center;gap:6px;padding:10px 16px;border:1.5px solid #4F46E5;border-radius:10px;font-size:13px;flex:1;justify-content:center;background:#f5f3ff;">
              <input type="hidden" name="gateway_usd" value="stripe" />
              💳 <?php echo View::e(I18n::t('checkout.cartao')); ?> (Stripe)
            </label>
          </div>
        </div>

        <button class="botao" type="submit" style="width:100%;font-size:15px;padding:14px;">Assinar agora</button>
      </form>
    </div>
  </div>
</div>

<script>
(function(){
  var base=<?php echo $precoBase; ?>;
  var baseUsd=<?php echo (float)($plano['price_monthly_usd'] ?? 0); ?>;
  var priceSemiannual=<?php echo (float)($plano['price_semiannual'] ?? 0); ?>;
  var priceSemiannualUsd=<?php echo (float)($plano['price_semiannual_usd'] ?? 0); ?>;
  var priceAnnual=<?php echo (float)($plano['price_annual'] ?? 0); ?>;
  var priceAnnualUsd=<?php echo (float)($plano['price_annual_usd'] ?? 0); ?>;
  var checks=document.querySelectorAll('.addon-check');
  var resumo=document.getElementById('addons-resumo');
  var total=document.getElementById('total-preco');
  var idsInput=document.getElementById('addons_ids');
  var curInput=document.querySelector('[name="sel_currency"]:checked');
  var selectedCurrency=curInput?curInput.value:'BRL';
  var selectedPeriodo=1;

  function fmt(v){
    if(selectedCurrency==='BRL') return 'R$ '+v.toFixed(2).replace('.',',');
    return 'US$ '+v.toFixed(2);
  }

  function getPlanPrice(){
    if(selectedCurrency==='USD'){
      if(selectedPeriodo===12 && priceAnnualUsd>0) return priceAnnualUsd;
      if(selectedPeriodo===6 && priceSemiannualUsd>0) return priceSemiannualUsd;
      return baseUsd>0?baseUsd:base;
    }
    if(selectedPeriodo===12 && priceAnnual>0) return priceAnnual;
    if(selectedPeriodo===6 && priceSemiannual>0) return priceSemiannual;
    return base;
  }

  function getAddonPrice(card){
    if(selectedCurrency==='USD'){
      if(selectedPeriodo>=12){var v=parseFloat(card.dataset.priceAnnualUsd);if(v>0)return v;}
      var u=parseFloat(card.dataset.priceUsd);if(u>0)return u;
    }
    if(selectedPeriodo>=12){var a=parseFloat(card.dataset.priceAnnual);if(a>0)return a;}
    return parseFloat(card.dataset.price)||0;
  }

  window.selCurrency=function(cur){
    selectedCurrency=cur;
    document.querySelectorAll('.cur-label').forEach(function(l){l.style.borderColor='#e2e8f0';l.style.background='#fff';});
    var sel=document.querySelector('input[name="sel_currency"][value="'+cur+'"]');
    if(sel)sel.closest('.cur-label').style.borderColor='#4F46E5',sel.closest('.cur-label').style.background='#f5f3ff';
    // CPF field
    var cpf=document.getElementById('cpfField');if(cpf)cpf.style.display=cur==='USD'?'none':'block';
    // Gateways
    document.getElementById('gwBrl').style.display=cur==='BRL'?'flex':'none';
    document.getElementById('gwUsd').style.display=cur==='USD'?'flex':'none';
    atualizar();
  };

  window.selPeriodo=function(p){
    selectedPeriodo=p;
    document.querySelectorAll('.per-label').forEach(function(l){l.style.borderColor='#e2e8f0';l.style.background='#fff';});
    var sel=document.querySelector('input[name="sel_periodo"][value="'+p+'"]');
    if(sel)sel.closest('.per-label').style.borderColor='#4F46E5',sel.closest('.per-label').style.background='#f5f3ff';
    atualizar();
  };

  function atualizar(){
    var planPrice=getPlanPrice();
    var perLabel=selectedPeriodo===1?'/mês':selectedPeriodo===6?'/mês (6 meses)':'/mês (12 meses)';
    var totalPlan=planPrice*selectedPeriodo;

    // Update period prices
    document.querySelectorAll('.per-label').forEach(function(l){
      var per=parseInt(l.dataset.per);
      var oldPer=selectedPeriodo;selectedPeriodo=per;
      var pp=getPlanPrice();
      selectedPeriodo=oldPer;
      var priceEl=l.querySelector('.per-price');
      if(priceEl) priceEl.textContent=fmt(pp)+'/mês';
    });

    // Plan price display
    document.getElementById('planPriceDisplay').innerHTML=fmt(planPrice)+'<span style="font-size:13px;font-weight:400;color:#64748b;">'+perLabel+'</span>';

    // Addons
    var soma=0;var html='';var ids=[];
    checks.forEach(function(cb){
      var card=cb.closest('.addon-card');
      var p=getAddonPrice(card);
      // Update addon price display
      var priceDisp=card.querySelector('.addon-price-display');
      if(priceDisp) priceDisp.textContent='+'+fmt(p)+'/mês';
      if(cb.checked){
        soma+=p*selectedPeriodo;ids.push(cb.value);
        var nome=card.querySelector('div[style*="font-weight:600"]').textContent;
        html+='<div style="display:flex;justify-content:space-between;color:#475569;"><span>'+nome+'</span><span>+'+fmt(p*selectedPeriodo)+'</span></div>';
        card.style.borderColor='#4F46E5';card.style.background='#f5f3ff';
      }else{
        card.style.borderColor='#e2e8f0';card.style.background='#fff';
      }
    });
    resumo.innerHTML=html;
    total.textContent=fmt(totalPlan+soma);
    idsInput.value=ids.join(',');

    // Update "por mês" label
    var perSuffix=selectedPeriodo===1?'por mês':'total ('+selectedPeriodo+' meses)';
    var perInfo=document.getElementById('perInfo');if(perInfo)perInfo.textContent=perSuffix;
  }

  checks.forEach(function(cb){cb.addEventListener('change',atualizar);});

  // Gateway style
  document.querySelectorAll('.gw-label input[type=radio]').forEach(function(r){
    r.addEventListener('change',function(){
      document.querySelectorAll('.gw-label').forEach(function(l){l.style.borderColor='#e2e8f0';l.style.background='#fff';});
      this.closest('.gw-label').style.borderColor='#4F46E5';this.closest('.gw-label').style.background='#f5f3ff';
    });
  });

  // Init
  atualizar();
  <?php if (!$isBrl): ?>selCurrency('USD');<?php endif; ?>
})();
</script>

<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
