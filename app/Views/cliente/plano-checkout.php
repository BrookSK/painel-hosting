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
$hasUpfront = ((float)($plano['price_annual_upfront'] ?? 0)) > 0 || ((float)($plano['price_annual_upfront_usd'] ?? 0)) > 0;

$pageTitle = 'Configurar plano';
$clienteNome = (string)($cliente['name'] ?? '');
$clienteEmail = (string)($cliente['email'] ?? '');
$extraHead = '<script src="https://js.stripe.com/v3/"></script>';
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
      <div id="planPriceDisplay" style="font-size:20px;font-weight:700;color:#1e293b;"></div>
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

    <!-- Período: Mensal ou Anual à vista -->
    <div class="card-new" style="margin-bottom:14px;">
      <div style="font-size:13px;font-weight:600;margin-bottom:8px;">📅 Período</div>
      <div style="display:flex;flex-direction:column;gap:8px;">
        <label style="display:flex;align-items:center;justify-content:space-between;padding:14px;border:1.5px solid #4F46E5;border-radius:10px;cursor:pointer;background:#f5f3ff;" class="per-label" data-per="monthly">
          <div style="display:flex;align-items:center;gap:8px;">
            <input type="radio" name="sel_periodo" value="monthly" checked onchange="selPeriodo('monthly')" style="accent-color:#4F46E5;" />
            <div>
              <span style="font-size:14px;font-weight:600;">Mensal</span>
              <div style="font-size:12px;color:#64748b;">Cobrado todo mês, cancele quando quiser</div>
            </div>
          </div>
          <span id="precoMensal" style="font-size:14px;font-weight:700;color:#4F46E5;"></span>
        </label>
        <?php if ($hasUpfront): ?>
        <label style="display:flex;align-items:center;justify-content:space-between;padding:14px;border:1.5px solid #e2e8f0;border-radius:10px;cursor:pointer;" class="per-label" data-per="annual">
          <div style="display:flex;align-items:center;gap:8px;">
            <input type="radio" name="sel_periodo" value="annual" onchange="selPeriodo('annual')" style="accent-color:#4F46E5;" />
            <div>
              <span style="font-size:14px;font-weight:600;">Anual à vista</span>
              <span style="font-size:11px;padding:2px 8px;border-radius:99px;background:#dcfce7;color:#166534;font-weight:600;">Melhor preço</span>
              <div style="font-size:12px;color:#64748b;">Pagamento único — 12 meses</div>
            </div>
          </div>
          <div style="text-align:right;">
            <span id="precoAnual" style="font-size:14px;font-weight:700;color:#4F46E5;"></span>
            <div id="economiaAnual" style="font-size:11px;color:#16a34a;font-weight:600;"></div>
          </div>
        </label>
        <?php endif; ?>
      </div>
    </div>

    <!-- Addons -->
    <?php if (!empty($addons)): ?>
    <div class="card-new">
      <div class="card-new-title" style="margin-bottom:4px;">Serviços adicionais</div>
      <p style="font-size:12px;color:#64748b;margin-bottom:14px;">Selecione os serviços extras que deseja incluir.</p>
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
          <div style="text-align:right;" class="addon-price-display">
            <div style="font-size:14px;font-weight:700;color:#4F46E5;white-space:nowrap;" class="addon-price-line"></div>
            <div style="font-size:11px;color:#64748b;" class="addon-price-sub"></div>
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
        <div id="resumo-linhas"></div>
        <div style="border-top:1px solid #e2e8f0;padding-top:10px;margin-top:4px;display:flex;justify-content:space-between;font-size:18px;font-weight:700;">
          <span>Total</span>
          <span id="total-preco" style="color:#4F46E5;"></span>
        </div>
        <div style="font-size:12px;color:#64748b;" id="perInfo"></div>
      </div>

      <form id="assinarForm" onsubmit="return submeterAssinar(event)">
        <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>" />
        <input type="hidden" name="plan_id" value="<?php echo $planId; ?>" />
        <input type="hidden" name="addons_ids" id="addons_ids" value="" />
        <input type="hidden" name="periodo" id="hidden_periodo" value="1" />
        <input type="hidden" name="currency" id="hidden_currency" value="<?php echo $isBrl ? 'BRL' : 'USD'; ?>" />

        <?php if ($clienteCpf === ''): ?>
        <div style="margin-bottom:12px;" id="cpfField">
          <label style="display:block;font-size:13px;margin-bottom:6px;"><?php echo View::e(I18n::t('checkout.cpf_cnpj')); ?></label>
          <input class="input" type="text" name="cpf_cnpj" placeholder="000.000.000-00" maxlength="18" inputmode="numeric" style="max-width:240px;" />
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
          <div style="display:none;gap:8px;flex-wrap:wrap;" id="gwUsd">
            <label style="display:flex;align-items:center;gap:6px;padding:10px 16px;border:1.5px solid #4F46E5;border-radius:10px;font-size:13px;flex:1;justify-content:center;background:#f5f3ff;">
              💳 Cartão de crédito
            </label>
            <div style="width:100%;margin-top:10px;">
              <div id="stripe-card-element" style="padding:12px;border:1.5px solid #e2e8f0;border-radius:10px;background:#fff;min-height:40px;"></div>
              <div id="stripe-card-errors" style="color:#ef4444;font-size:12px;margin-top:6px;"></div>
            </div>
          </div>
        </div>

        <button class="botao" type="submit" id="btnAssinar" style="width:100%;font-size:15px;padding:14px;">Assinar agora</button>
        <div id="assinarErro" style="display:none;margin-top:10px;background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:10px 14px;border-radius:10px;font-size:13px;"></div>
      </form>
    </div>
  </div>
</div>

<script>
(function(){
  var taxaUsd=<?php echo \LRV\Core\ConfiguracoesSistema::taxaConversaoUsd(); ?>;
  var baseBrl=<?php echo $precoBase; ?>;
  var baseUsd=<?php echo (float)($plano['price_monthly_usd'] ?? 0); ?>;
  var upfrontBrl=<?php echo (float)($plano['price_annual_upfront'] ?? 0); ?>;
  var upfrontUsd=<?php echo (float)($plano['price_annual_upfront_usd'] ?? 0); ?>;
  var planName=<?php echo json_encode((string)($plano['name'] ?? '')); ?>;

  var checks=document.querySelectorAll('.addon-check');
  var selectedCurrency=<?php echo json_encode($isBrl ? 'BRL' : 'USD'); ?>;
  var selectedPeriodo='monthly'; // 'monthly' ou 'annual'

  function toUsd(brl,fixedUsd){return fixedUsd>0?fixedUsd:Math.round(brl/taxaUsd*100)/100;}
  function fmt(v){return selectedCurrency==='BRL'?'R$ '+v.toFixed(2).replace('.',','):'US$ '+v.toFixed(2);}

  function getMonthlyPrice(){return selectedCurrency==='USD'?toUsd(baseBrl,baseUsd):baseBrl;}
  function getUpfrontPrice(){return selectedCurrency==='USD'?toUsd(upfrontBrl,upfrontUsd):upfrontBrl;}

  function getAddonMonthly(card){
    var p=parseFloat(card.dataset.price)||0;
    var pu=parseFloat(card.dataset.priceUsd)||0;
    return selectedCurrency==='USD'?toUsd(p,pu):p;
  }
  function getAddonAnnual(card){
    var p=parseFloat(card.dataset.price)||0;
    var pa=parseFloat(card.dataset.priceAnnual)||0;
    var pau=parseFloat(card.dataset.priceAnnualUsd)||0;
    if(selectedCurrency==='USD') return toUsd(pa>0?pa:p,pau)*12;
    return (pa>0?pa:p)*12;
  }

  window.selCurrency=function(cur){
    selectedCurrency=cur;
    document.querySelectorAll('.cur-label').forEach(function(l){l.style.borderColor='#e2e8f0';l.style.background='#fff';});
    var sel=document.querySelector('input[name="sel_currency"][value="'+cur+'"]');
    if(sel){sel.closest('.cur-label').style.borderColor='#4F46E5';sel.closest('.cur-label').style.background='#f5f3ff';}
    var cpf=document.getElementById('cpfField');if(cpf)cpf.style.display=cur==='USD'?'none':'block';
    document.getElementById('gwBrl').style.display=cur==='BRL'?'flex':'none';
    document.getElementById('gwUsd').style.display=cur==='USD'?'flex':'none';
    document.getElementById('hidden_currency').value=cur;
    atualizar();
  };

  window.selPeriodo=function(p){
    selectedPeriodo=p;
    document.querySelectorAll('.per-label').forEach(function(l){l.style.borderColor='#e2e8f0';l.style.background='#fff';});
    var sel=document.querySelector('input[name="sel_periodo"][value="'+p+'"]');
    if(sel){sel.closest('.per-label').style.borderColor='#4F46E5';sel.closest('.per-label').style.background='#f5f3ff';}
    document.getElementById('hidden_periodo').value=p==='annual'?12:1;
    atualizar();
  };

  function atualizar(){
    var monthly=getMonthlyPrice();
    var upfront=getUpfrontPrice();
    var isAnnual=selectedPeriodo==='annual';

    // Preço do plano
    var planTotal=isAnnual?upfront:monthly;
    var planLabel=isAnnual?fmt(upfront)+' <span style="font-size:13px;font-weight:400;color:#64748b;">/ano</span>':fmt(monthly)+' <span style="font-size:13px;font-weight:400;color:#64748b;">/mês</span>';
    document.getElementById('planPriceDisplay').innerHTML=planLabel;

    // Period labels
    document.getElementById('precoMensal').textContent=fmt(monthly)+'/mês';
    var pa=document.getElementById('precoAnual');if(pa)pa.textContent=fmt(upfront)+'/ano';
    var ec=document.getElementById('economiaAnual');
    if(ec&&upfront>0){
      var economia=monthly*12-upfront;
      ec.textContent=economia>0?'Economia de '+fmt(economia):'';
    }

    // Addons
    var addonsTotal=0;var resumoHtml='';var ids=[];
    checks.forEach(function(cb){
      var card=cb.closest('.addon-card');
      var addonMes=getAddonMonthly(card);
      var addonAno=getAddonAnnual(card);
      var addonVal=isAnnual?addonAno:addonMes;
      // Update display
      var pLine=card.querySelector('.addon-price-line');
      var pSub=card.querySelector('.addon-price-sub');
      if(isAnnual){
        if(pLine)pLine.textContent='+'+fmt(addonAno)+'/ano';
        if(pSub)pSub.textContent='≈ '+fmt(addonAno/12)+'/mês';
      }else{
        if(pLine)pLine.textContent='+'+fmt(addonMes)+'/mês';
        if(pSub)pSub.textContent='';
      }
      if(cb.checked){
        addonsTotal+=addonVal;ids.push(cb.value);
        var nome=card.querySelector('div[style*="font-weight:600"]').textContent;
        resumoHtml+='<div style="display:flex;justify-content:space-between;color:#475569;"><span>'+nome+'</span><span>+'+fmt(addonVal)+'</span></div>';
        card.style.borderColor='#4F46E5';card.style.background='#f5f3ff';
      }else{
        card.style.borderColor='#e2e8f0';card.style.background='#fff';
      }
    });

    var totalGeral=planTotal+addonsTotal;

    // Resumo
    var rl=document.getElementById('resumo-linhas');
    rl.innerHTML='<div style="display:flex;justify-content:space-between;"><span>'+planName+'</span><span>'+fmt(planTotal)+'</span></div>'+resumoHtml;
    document.getElementById('total-preco').textContent=fmt(totalGeral);
    document.getElementById('addons_ids').value=ids.join(',');

    var perInfo=document.getElementById('perInfo');
    if(perInfo){
      if(isAnnual){
        perInfo.textContent='pagamento único anual (à vista)';
      }else{
        perInfo.textContent='cobrado mensalmente';
      }
    }
  }

  checks.forEach(function(cb){cb.addEventListener('change',atualizar);});
  document.querySelectorAll('.gw-label input[type=radio]').forEach(function(r){
    r.addEventListener('change',function(){
      document.querySelectorAll('.gw-label').forEach(function(l){l.style.borderColor='#e2e8f0';l.style.background='#fff';});
      this.closest('.gw-label').style.borderColor='#4F46E5';this.closest('.gw-label').style.background='#f5f3ff';
    });
  });

  atualizar();
  <?php if (!$isBrl): ?>selCurrency('USD');<?php endif; ?>

  // Stripe Elements
  var stripePublicKey=<?php echo json_encode(\LRV\Core\ConfiguracoesSistema::stripePublishableKey()); ?>;
  var stripeInstance=null,stripeCard=null;
  if(stripePublicKey && typeof Stripe!=='undefined'){
    stripeInstance=Stripe(stripePublicKey);
    var els=stripeInstance.elements();
    stripeCard=els.create('card',{style:{base:{fontSize:'15px',color:'#1e293b','::placeholder':{color:'#94a3b8'}}}});
    stripeCard.mount('#stripe-card-element');
    stripeCard.on('change',function(ev){document.getElementById('stripe-card-errors').textContent=ev.error?ev.error.message:'';});
  }

  window.submeterAssinar=function(e){
    e.preventDefault();
    var btn=document.getElementById('btnAssinar');
    var erro=document.getElementById('assinarErro');
    erro.style.display='none';
    btn.disabled=true;btn.textContent='Processando...';

    var form=document.getElementById('assinarForm');
    var fd=new FormData(form);
    if(selectedCurrency==='USD') fd.set('gateway','stripe');

    fetch('/cliente/assinar',{method:'POST',body:fd,credentials:'same-origin'})
      .then(function(r){return r.json();})
      .then(function(d){
        // Stripe inline: confirmar com card element
        if(d.ok&&d.payment_type==='stripe_inline'&&d.client_secret){
          if(!stripeInstance||!stripeCard){
            // Fallback: redirect se Stripe.js não carregou (CSP bloqueou)
            if(d.redirect){window.location.href=d.redirect;return;}
            erro.textContent='Stripe não disponível. Tente desativar extensões do browser ou use outro navegador.';
            erro.style.display='block';btn.disabled=false;btn.textContent='Assinar agora';
            return;
          }
          btn.textContent='Confirmando pagamento...';
          stripeInstance.confirmCardPayment(d.client_secret,{payment_method:{card:stripeCard}})
            .then(function(result){
              if(result.error){
                erro.textContent=result.error.message;erro.style.display='block';
                btn.disabled=false;btn.textContent='Assinar agora';
              }else{
                btn.textContent='Pagamento confirmado!';
                window.location.href='/cliente/assinaturas';
              }
            });
          return;
        }
        // Redirect (Asaas ou fallback Stripe Checkout)
        if(d.ok&&d.redirect){window.location.href=d.redirect;return;}
        if(d.erro){
          erro.textContent=d.erro;erro.style.display='block';
          btn.disabled=false;btn.textContent='Assinar agora';
        }
      })
      .catch(function(){
        erro.textContent='Erro de conexão. Tente novamente.';
        erro.style.display='block';btn.disabled=false;btn.textContent='Assinar agora';
      });
    return false;
  };
})();
</script>

<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
