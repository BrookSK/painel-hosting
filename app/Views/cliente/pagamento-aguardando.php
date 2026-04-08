<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\Csrf;
use LRV\Core\I18n;

$sub = is_array($sub ?? null) ? $sub : [];
$cobranca = is_array($cobranca ?? null) ? $cobranca : null;
$pixData = is_array($pixData ?? null) ? $pixData : null;
$boletoData = is_array($boletoData ?? null) ? $boletoData : null;
$billingType = (string)($billingType ?? '');

$subId = (int)($sub['id'] ?? 0);
$planName = (string)($sub['plan_name'] ?? '');
$priceMonthly = (float)($sub['price_monthly'] ?? 0);
$subStatus = strtoupper((string)($sub['status'] ?? ''));

$paymentStatus = strtoupper((string)($cobranca['status'] ?? ''));
$paymentValue = (float)($cobranca['value'] ?? $priceMonthly);
$dueDate = (string)($cobranca['dueDate'] ?? '');
$pago = in_array($paymentStatus, ['CONFIRMED', 'RECEIVED'], true) || $subStatus === 'ACTIVE';

$pixPayload = (string)($pixData['payload'] ?? '');
$pixBase64 = (string)($pixData['encodedImage'] ?? '');
$pixExpiration = (string)($pixData['expirationDate'] ?? '');

$boletoUrl = (string)($boletoData['bankSlipUrl'] ?? '');
$boletoLinhaDigitavel = (string)($boletoData['identificationField'] ?? '');

$pageTitle = I18n::t('pagamento.titulo');
$clienteNome = '';
$clienteEmail = '';
require __DIR__ . '/../_partials/layout-cliente-inicio.php';
?>

<div style="max-width:680px;margin:0 auto;">
  <div style="margin-bottom:24px;">
    <div class="page-title"><?php echo View::e(I18n::t('pagamento.titulo')); ?></div>
    <div class="page-subtitle" style="margin-bottom:0;"><?php echo View::e($planName); ?> — <?php echo View::e(I18n::precoPlano($sub)); ?>/mês</div>
  </div>

  <?php if ($pago): ?>
  <!-- PAGAMENTO CONFIRMADO -->
  <div class="card-new" style="text-align:center;padding:32px 20px;">
    <div style="font-size:48px;margin-bottom:12px;">✅</div>
    <div style="font-size:20px;font-weight:700;margin-bottom:8px;"><?php echo View::e(I18n::t('pagamento.confirmado_titulo')); ?></div>
    <p style="font-size:14px;color:#64748b;margin-bottom:20px;"><?php echo View::e(I18n::t('pagamento.confirmado_sub')); ?></p>
    <a class="botao" href="/cliente/assinaturas"><?php echo View::e(I18n::t('pagamento.ver_assinaturas')); ?></a>
  </div>

  <?php elseif ($billingType === 'PIX'): ?>
  <!-- PIX -->
  <div class="card-new" id="pix-area" style="text-align:center;padding:24px 20px;">
    <div style="display:inline-flex;align-items:center;gap:8px;background:#dbeafe;color:#1e40af;padding:6px 14px;border-radius:20px;font-size:13px;font-weight:600;margin-bottom:16px;">
      <span style="display:inline-block;width:8px;height:8px;background:#3b82f6;border-radius:50%;animation:pulse 1.5s infinite;"></span>
      <?php echo View::e(I18n::t('pagamento.aguardando_pix')); ?>
    </div>

    <?php if ($pixBase64 !== ''): ?>
    <div style="margin:16px auto;max-width:220px;">
      <img src="data:image/png;base64,<?php echo $pixBase64; ?>" alt="QR Code PIX" style="width:100%;border-radius:12px;border:2px solid #e2e8f0;" id="pix-qr" />
    </div>
    <?php endif; ?>

    <?php if ($pixPayload !== ''): ?>
    <div style="margin:12px auto;max-width:480px;">
      <label style="display:block;font-size:12px;color:#64748b;margin-bottom:6px;"><?php echo View::e(I18n::t('pagamento.pix_copia_cola')); ?></label>
      <div style="display:flex;gap:8px;">
        <input type="text" id="pix-payload" value="<?php echo View::e($pixPayload); ?>" readonly style="flex:1;padding:10px 12px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:12px;background:#f8fafc;font-family:monospace;" />
        <button type="button" onclick="copiarPix()" class="botao sm" style="white-space:nowrap;" id="btn-copiar">
          <?php echo View::e(I18n::t('pagamento.copiar')); ?>
        </button>
      </div>
    </div>
    <?php endif; ?>

    <?php if ($pixExpiration !== ''): ?>
    <p style="font-size:12px;color:#94a3b8;margin-top:12px;">
      <?php echo View::e(I18n::t('pagamento.pix_expira')); ?>: <span id="pix-timer"><?php echo View::e($pixExpiration); ?></span>
    </p>
    <?php endif; ?>

    <p style="font-size:13px;color:#64748b;margin-top:16px;"><?php echo View::e(I18n::t('pagamento.pix_instrucao')); ?></p>
  </div>

  <?php elseif ($billingType === 'BOLETO'): ?>
  <!-- BOLETO -->
  <div class="card-new" style="padding:24px 20px;">
    <div style="text-align:center;margin-bottom:20px;">
      <div style="font-size:48px;margin-bottom:8px;">📄</div>
      <div style="font-size:18px;font-weight:700;margin-bottom:4px;"><?php echo View::e(I18n::t('pagamento.boleto_titulo')); ?></div>
      <div style="background:#fef3c7;color:#92400e;padding:8px 16px;border-radius:10px;font-size:13px;display:inline-block;margin-top:8px;">
        ⚠️ <?php echo View::e(I18n::t('pagamento.boleto_aviso')); ?>
      </div>
    </div>

    <?php if ($boletoLinhaDigitavel !== ''): ?>
    <div style="margin:16px 0;">
      <label style="display:block;font-size:12px;color:#64748b;margin-bottom:6px;"><?php echo View::e(I18n::t('pagamento.boleto_linha')); ?></label>
      <div style="display:flex;gap:8px;">
        <input type="text" id="boleto-linha" value="<?php echo View::e($boletoLinhaDigitavel); ?>" readonly style="flex:1;padding:10px 12px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:12px;background:#f8fafc;font-family:monospace;" />
        <button type="button" onclick="copiarBoleto()" class="botao sm" style="white-space:nowrap;" id="btn-copiar-boleto">
          <?php echo View::e(I18n::t('pagamento.copiar')); ?>
        </button>
      </div>
    </div>
    <?php endif; ?>

    <?php if ($boletoUrl !== ''): ?>
    <div style="text-align:center;margin-top:16px;">
      <a class="botao" href="<?php echo View::e($boletoUrl); ?>" target="_blank" rel="noreferrer">
        <?php echo View::e(I18n::t('pagamento.boleto_baixar')); ?>
      </a>
    </div>
    <?php endif; ?>

    <p style="font-size:13px;color:#64748b;margin-top:16px;text-align:center;"><?php echo View::e(I18n::t('pagamento.boleto_instrucao')); ?></p>
  </div>

  <?php elseif ($billingType === 'CREDIT_CARD'): ?>
  <!-- CARTÃO DE CRÉDITO (Asaas) -->
  <div class="card-new" style="padding:24px 20px;" id="card-area">
    <div style="text-align:center;margin-bottom:20px;">
      <div style="font-size:36px;margin-bottom:8px;">💳</div>
      <div style="font-size:18px;font-weight:700;"><?php echo View::e(I18n::t('pagamento.cartao_titulo')); ?></div>
    </div>

    <div id="card-erro" style="display:none;background:#fef2f2;color:#dc2626;padding:10px 14px;border-radius:10px;font-size:13px;margin-bottom:16px;text-align:center;"></div>
    <div id="card-sucesso" style="display:none;background:#f0fdf4;color:#16a34a;padding:16px;border-radius:10px;font-size:14px;text-align:center;">
      ✅ <?php echo View::e(I18n::t('pagamento.cartao_sucesso')); ?>
    </div>

    <form id="form-cartao" onsubmit="return enviarCartao(event)">
      <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>" />
      <input type="hidden" name="sub_id" value="<?php echo $subId; ?>" />

      <div style="font-size:13px;font-weight:600;color:#475569;margin-bottom:10px;"><?php echo View::e(I18n::t('pagamento.dados_cartao')); ?></div>
      <div style="display:grid;gap:10px;margin-bottom:16px;">
        <input class="input" type="text" name="holder_name" placeholder="<?php echo View::e(I18n::t('pagamento.nome_cartao')); ?>" required autocomplete="cc-name" />
        <input class="input" type="text" name="number" placeholder="<?php echo View::e(I18n::t('pagamento.numero_cartao')); ?>" required maxlength="19" autocomplete="cc-number" inputmode="numeric" />
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;">
          <input class="input" type="text" name="exp_month" placeholder="MM" required maxlength="2" autocomplete="cc-exp-month" inputmode="numeric" />
          <input class="input" type="text" name="exp_year" placeholder="AAAA" required maxlength="4" autocomplete="cc-exp-year" inputmode="numeric" />
          <input class="input" type="text" name="ccv" placeholder="CVV" required maxlength="4" autocomplete="cc-csc" inputmode="numeric" />
        </div>
      </div>

      <div style="font-size:13px;font-weight:600;color:#475569;margin-bottom:10px;"><?php echo View::e(I18n::t('pagamento.dados_titular')); ?></div>
      <div style="display:grid;gap:10px;margin-bottom:20px;">
        <input class="input" type="text" name="holder_cpf" placeholder="CPF/CNPJ" required inputmode="numeric" />
        <input class="input" type="email" name="holder_email" placeholder="E-mail" required autocomplete="email" />
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
          <input class="input" type="text" name="holder_phone" placeholder="<?php echo View::e(I18n::t('pagamento.telefone')); ?>" required inputmode="tel" />
          <input class="input" type="text" name="holder_cep" placeholder="CEP" required maxlength="9" inputmode="numeric" />
        </div>
        <input class="input" type="text" name="holder_address_number" placeholder="<?php echo View::e(I18n::t('pagamento.numero_endereco')); ?>" required />
      </div>

      <button class="botao" type="submit" style="width:100%;font-size:15px;padding:14px;" id="btn-pagar">
        <?php echo View::e(I18n::t('pagamento.pagar')); ?> <?php echo View::e(I18n::preco($paymentValue)); ?>
      </button>
    </form>
  </div>

  <?php else: ?>
  <div class="card-new" style="text-align:center;padding:24px;">
    <div class="erro"><?php echo View::e(I18n::t('pagamento.erro_tipo')); ?></div>
    <a class="botao ghost sm" href="/cliente/planos" style="margin-top:12px;"><?php echo View::e(I18n::t('pagamento.voltar_planos')); ?></a>
  </div>
  <?php endif; ?>
</div>

<style>
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}
</style>

<script>
(function(){
  var subId=<?php echo $subId; ?>;
  var pago=<?php echo $pago ? 'true' : 'false'; ?>;
  var billingType=<?php echo json_encode($billingType); ?>;

  function copiarTexto(el,btn){
    if(!el)return;
    navigator.clipboard.writeText(el.value).then(function(){
      var orig=btn.textContent;
      btn.textContent='<?php echo View::e(I18n::t('pagamento.copiado')); ?>';
      btn.disabled=true;
      setTimeout(function(){btn.textContent=orig;btn.disabled=false;},2000);
    });
  }
  window.copiarPix=function(){copiarTexto(document.getElementById('pix-payload'),document.getElementById('btn-copiar'));};
  window.copiarBoleto=function(){copiarTexto(document.getElementById('boleto-linha'),document.getElementById('btn-copiar-boleto'));};

  // Polling para PIX e Boleto
  if(!pago && (billingType==='PIX'||billingType==='BOLETO')){
    var interval=setInterval(function(){
      fetch('/cliente/pagamento/status?sub='+subId,{credentials:'same-origin'})
        .then(function(r){return r.json();})
        .then(function(d){
          if(d.pago){
            clearInterval(interval);
            location.reload();
          }
          // Atualizar QR code se mudou (PIX expirado e regenerado)
          if(d.pix && d.pix.encodedImage && billingType==='PIX'){
            var qr=document.getElementById('pix-qr');
            if(qr) qr.src='data:image/png;base64,'+d.pix.encodedImage;
            var pl=document.getElementById('pix-payload');
            if(pl && d.pix.payload) pl.value=d.pix.payload;
          }
        })
        .catch(function(){});
    }, 5000);
  }

  // Cartão de crédito (Asaas)
  window.enviarCartao=function(e){
    e.preventDefault();
    var form=document.getElementById('form-cartao');
    var btn=document.getElementById('btn-pagar');
    var erroEl=document.getElementById('card-erro');
    var sucessoEl=document.getElementById('card-sucesso');
    erroEl.style.display='none';

    btn.disabled=true;
    btn.textContent='<?php echo View::e(I18n::t('pagamento.processando')); ?>';

    var fd=new FormData(form);
    fetch('/cliente/pagamento/cartao',{
      method:'POST',
      credentials:'same-origin',
      headers:{'X-CSRF-Token':fd.get('_csrf')},
      body:fd
    })
    .then(function(r){return r.json();})
    .then(function(d){
      if(d.ok && d.pago){
        form.style.display='none';
        sucessoEl.style.display='block';
        setTimeout(function(){location.href='/cliente/assinaturas';},2000);
      } else if(d.ok && !d.pago){
        erroEl.textContent='<?php echo View::e(I18n::t('pagamento.cartao_analise')); ?>';
        erroEl.style.display='block';
        btn.disabled=false;
        btn.textContent='<?php echo View::e(I18n::t('pagamento.pagar')); ?> <?php echo View::e(I18n::preco($paymentValue)); ?>';
      } else {
        erroEl.textContent=d.erro||'<?php echo View::e(I18n::t('pagamento.erro_generico')); ?>';
        erroEl.style.display='block';
        btn.disabled=false;
        btn.textContent='<?php echo View::e(I18n::t('pagamento.pagar')); ?> <?php echo View::e(I18n::preco($paymentValue)); ?>';
      }
    })
    .catch(function(){
      erroEl.textContent='<?php echo View::e(I18n::t('pagamento.erro_generico')); ?>';
      erroEl.style.display='block';
      btn.disabled=false;
      btn.textContent='<?php echo View::e(I18n::t('pagamento.pagar')); ?> <?php echo View::e(I18n::preco($paymentValue)); ?>';
    });
    return false;
  };
})();
</script>

<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
