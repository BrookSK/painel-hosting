<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\Csrf;
use LRV\Core\I18n;

$plano = is_array($plano ?? null) ? $plano : [];
$addons = is_array($addons ?? null) ? $addons : [];
$precoBase = (float)($plano['price_monthly'] ?? 0);
$planId = (int)($plano['id'] ?? 0);

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
      <div style="font-size:20px;font-weight:700;color:#1e293b;">
        <?php echo View::e(I18n::preco($precoBase)); ?><span style="font-size:13px;font-weight:400;color:#64748b;">/mês</span>
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
        <label style="display:flex;align-items:center;gap:12px;padding:14px;border:1.5px solid #e2e8f0;border-radius:12px;cursor:pointer;transition:all .15s;" class="addon-card" data-price="<?php echo $aPrice; ?>">
          <input type="checkbox" name="addons[]" value="<?php echo $aId; ?>" class="addon-check" style="accent-color:#4F46E5;width:18px;height:18px;flex-shrink:0;" />
          <div style="flex:1;">
            <div style="font-size:14px;font-weight:600;color:#1e293b;"><?php echo View::e($aName); ?></div>
            <?php if ($aDesc !== ''): ?>
              <div style="font-size:12px;color:#64748b;margin-top:2px;"><?php echo View::e($aDesc); ?></div>
            <?php endif; ?>
          </div>
          <div style="font-size:14px;font-weight:700;color:#4F46E5;white-space:nowrap;">
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
        <div style="font-size:11px;color:#94a3b8;">por mês</div>
      </div>

      <form method="post" action="/cliente/assinar" style="margin-top:16px;">
        <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>" />
        <input type="hidden" name="plan_id" value="<?php echo $planId; ?>" />
        <input type="hidden" name="addons_ids" id="addons_ids" value="" />

        <div style="margin-bottom:12px;">
          <label style="display:block;font-size:13px;margin-bottom:6px;"><?php echo View::e(I18n::t('checkout.forma_pagamento')); ?></label>
          <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <?php if (I18n::idioma() === 'pt-BR'): ?>
              <label style="display:flex;align-items:center;gap:6px;padding:10px 16px;border:1.5px solid #e2e8f0;border-radius:10px;cursor:pointer;font-size:13px;flex:1;justify-content:center;" class="gw-label">
                <input type="radio" name="gateway" value="PIX" checked style="accent-color:#4F46E5;" /> PIX
              </label>
              <label style="display:flex;align-items:center;gap:6px;padding:10px 16px;border:1.5px solid #e2e8f0;border-radius:10px;cursor:pointer;font-size:13px;flex:1;justify-content:center;" class="gw-label">
                <input type="radio" name="gateway" value="BOLETO" style="accent-color:#4F46E5;" /> Boleto
              </label>
              <label style="display:flex;align-items:center;gap:6px;padding:10px 16px;border:1.5px solid #e2e8f0;border-radius:10px;cursor:pointer;font-size:13px;flex:1;justify-content:center;" class="gw-label">
                <input type="radio" name="gateway" value="CREDIT_CARD" style="accent-color:#4F46E5;" /> <?php echo View::e(I18n::t('checkout.cartao')); ?>
              </label>
            <?php else: ?>
              <input type="hidden" name="gateway" value="stripe" />
              <label style="display:flex;align-items:center;gap:6px;padding:10px 16px;border:1.5px solid #4F46E5;border-radius:10px;font-size:13px;flex:1;justify-content:center;background:#f5f3ff;">
                💳 <?php echo View::e(I18n::t('checkout.cartao')); ?> (Stripe)
              </label>
            <?php endif; ?>
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
  var checks=document.querySelectorAll('.addon-check');
  var resumo=document.getElementById('addons-resumo');
  var total=document.getElementById('total-preco');
  var idsInput=document.getElementById('addons_ids');
  var fmt=<?php echo json_encode(I18n::idioma() === 'pt-BR' ? 'BRL' : 'USD'); ?>;

  function formatPreco(v){
    if(fmt==='BRL') return 'R$ '+v.toFixed(2).replace('.',',');
    return '$ '+v.toFixed(2);
  }

  function atualizar(){
    var soma=0;var html='';var ids=[];
    checks.forEach(function(cb){
      var card=cb.closest('.addon-card');
      var p=parseFloat(card.dataset.price)||0;
      if(cb.checked){
        soma+=p;ids.push(cb.value);
        var nome=card.querySelector('div[style*="font-weight:600"]').textContent;
        html+='<div style="display:flex;justify-content:space-between;color:#475569;"><span>'+nome+'</span><span>+'+formatPreco(p)+'</span></div>';
        card.style.borderColor='#4F46E5';card.style.background='#f5f3ff';
      }else{
        card.style.borderColor='#e2e8f0';card.style.background='#fff';
      }
    });
    resumo.innerHTML=html;
    total.textContent=formatPreco(base+soma);
    idsInput.value=ids.join(',');
  }

  checks.forEach(function(cb){cb.addEventListener('change',atualizar);});

  // Gateway style
  document.querySelectorAll('.gw-label input').forEach(function(r){
    r.addEventListener('change',function(){
      document.querySelectorAll('.gw-label').forEach(function(l){l.style.borderColor='#e2e8f0';l.style.background='#fff';});
      this.closest('.gw-label').style.borderColor='#4F46E5';this.closest('.gw-label').style.background='#f5f3ff';
    });
  });
  // Init
  var firstGw=document.querySelector('.gw-label');
  if(firstGw){firstGw.style.borderColor='#4F46E5';firstGw.style.background='#f5f3ff';}
})();
</script>

<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
