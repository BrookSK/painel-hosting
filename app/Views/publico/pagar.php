<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;
use LRV\Core\SistemaConfig;

$_nome = SistemaConfig::nome();
$_logo = SistemaConfig::logoUrl();

$tela        = (string)($tela ?? 'erro');
$token       = (string)($token ?? '');
$link        = is_array($link ?? null) ? $link : [];
$currency    = strtoupper((string)($currency ?? 'BRL'));
$sub         = is_array($sub ?? null) ? $sub : null;
$cobranca    = is_array($cobranca ?? null) ? $cobranca : null;
$pixData     = is_array($pixData ?? null) ? $pixData : null;
$boletoData  = is_array($boletoData ?? null) ? $boletoData : null;
$billingType = (string)($billingType ?? '');
$tituloErro  = (string)($tituloErro ?? '');
$msgErro     = (string)($msgErro ?? '');

$planName    = (string)($link['plan_name'] ?? '');
$clientName  = (string)($link['client_name'] ?? '');
$clientDoc   = (string)($link['client_document'] ?? '');

// Preço exibido
$precoBrl = (float)($link['price_monthly'] ?? 0);
$precoUsd = (float)($link['price_monthly_usd'] ?? 0);
$precoFmt = $currency === 'USD'
    ? ('US$ ' . number_format($precoUsd > 0 ? $precoUsd : $precoBrl, 2, '.', ','))
    : ('R$ ' . number_format($precoBrl, 2, ',', '.'));

// Dados de pagamento (quando na tela de pagamento Asaas)
$subId = (int)($sub['id'] ?? 0);
$subStatus = strtoupper((string)($sub['status'] ?? ''));
$paymentStatus = strtoupper((string)($cobranca['status'] ?? ''));
$paymentValue = (float)($cobranca['value'] ?? $precoBrl);
$paymentValueFmt = 'R$ ' . number_format($paymentValue, 2, ',', '.');
$pago = in_array($paymentStatus, ['CONFIRMED', 'RECEIVED'], true) || $subStatus === 'ACTIVE';

$pixPayload = (string)($pixData['payload'] ?? '');
$pixBase64 = (string)($pixData['encodedImage'] ?? '');
$boletoUrl = (string)($boletoData['bankSlipUrl'] ?? '');
$boletoLinha = (string)($boletoData['identificationField'] ?? '');
?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Pagamento — <?php echo View::e($_nome); ?></title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
  <style>
    body{background:#f1f5f9;min-height:100vh;padding:24px 16px;margin:0;}
    .pp-wrap{width:100%;max-width:560px;margin:0 auto;}
    .pp-brand{display:flex;align-items:center;justify-content:center;gap:10px;margin-bottom:20px;}
    .pp-brand img{height:30px;width:auto;background:#0f172a;padding:6px 12px;border-radius:10px;}
    .pp-brand-name{font-size:17px;font-weight:800;color:#0f172a;}
    .pp-card{background:#fff;border:1px solid #e2e8f0;border-radius:18px;padding:28px 26px;box-shadow:0 4px 24px rgba(15,23,42,.06);margin-bottom:16px;}
    .pp-title{font-size:21px;font-weight:800;color:#0f172a;letter-spacing:-.02em;margin-bottom:4px;}
    .pp-sub{font-size:14px;color:#64748b;margin-bottom:4px;}
    .pp-resumo{background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:14px 16px;margin-bottom:20px;}
    .pp-resumo-linha{display:flex;justify-content:space-between;font-size:14px;color:#334155;padding:3px 0;}
    .pp-resumo-linha strong{color:#0f172a;}
    .pp-metodos{display:grid;gap:10px;}
    .pp-metodo{display:flex;align-items:center;gap:12px;padding:14px 16px;border:1.5px solid #e2e8f0;border-radius:12px;cursor:pointer;transition:all .15s;background:#fff;text-align:left;width:100%;font-size:15px;color:#0f172a;font-weight:600;}
    .pp-metodo:hover{border-color:#4F46E5;background:#f5f3ff;}
    .pp-metodo svg{flex-shrink:0;color:#4F46E5;}
    .pp-metodo small{display:block;font-weight:400;color:#64748b;font-size:12px;margin-top:2px;}
    .pp-campo{margin-bottom:14px;}
    .pp-label{display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;}
    .pp-erro{background:#fef2f2;color:#dc2626;padding:12px 14px;border-radius:10px;font-size:14px;margin-bottom:16px;}
    .pp-badge-aguardando{display:inline-flex;align-items:center;gap:8px;background:#dbeafe;color:#1e40af;padding:6px 14px;border-radius:20px;font-size:13px;font-weight:600;margin-bottom:16px;}
    .pp-dot{display:inline-block;width:8px;height:8px;background:#3b82f6;border-radius:50%;animation:pulse 1.5s infinite;}
    .pp-foot{text-align:center;font-size:12px;color:#94a3b8;margin-top:4px;}
    @keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}
  </style>
</head>
<body>
<div class="pp-wrap">
  <div class="pp-brand">
    <?php if ($_logo !== ''): ?>
      <img src="<?php echo View::e($_logo); ?>" alt="logo" />
    <?php else: ?>
      <svg width="28" height="28" viewBox="0 0 26 26" fill="none"><rect width="26" height="26" rx="7" fill="#4F46E5"/><path d="M6 13h14M13 6v14" stroke="#fff" stroke-width="2.2" stroke-linecap="round"/></svg>
      <span class="pp-brand-name"><?php echo View::e($_nome); ?></span>
    <?php endif; ?>
  </div>

<?php if ($tela === 'erro'): ?>
  <div class="pp-card" style="text-align:center;">
    <div style="font-size:40px;margin-bottom:8px;">⚠️</div>
    <div class="pp-title"><?php echo View::e($tituloErro !== '' ? $tituloErro : 'Link indisponível'); ?></div>
    <p class="pp-sub"><?php echo View::e($msgErro !== '' ? $msgErro : 'Este link de pagamento não está disponível.'); ?></p>
  </div>

<?php elseif ($tela === 'confirmado'): ?>
  <div class="pp-card" style="text-align:center;">
    <div style="margin-bottom:10px;"><svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="16 9 11 14 8 11"/></svg></div>
    <div class="pp-title">Pagamento confirmado!</div>
    <p class="pp-sub">Recebemos o seu pagamento do plano <strong><?php echo View::e($planName); ?></strong>. Tudo certo — não é preciso fazer mais nada.</p>
  </div>

<?php elseif ($tela === 'escolha'): ?>
  <div class="pp-card">
    <div class="pp-title">Finalizar pagamento</div>
    <p class="pp-sub">Escolha como você quer pagar.</p>

    <div class="pp-resumo">
      <div class="pp-resumo-linha"><span>Plano</span><strong><?php echo View::e($planName); ?></strong></div>
      <?php if ($clientName !== ''): ?><div class="pp-resumo-linha"><span>Cliente</span><strong><?php echo View::e($clientName); ?></strong></div><?php endif; ?>
      <div class="pp-resumo-linha"><span>Valor</span><strong><?php echo $precoFmt; ?><?php echo ((int)($link['periodo'] ?? 1) >= 12 ? '/ano' : '/mês'); ?></strong></div>
    </div>

    <?php if ($currency === 'USD'): ?>
      <!-- USD → Stripe (cartão), vai direto ao checkout -->
      <form method="post" action="/pagar/<?php echo View::e($token); ?>/iniciar">
        <button type="submit" class="pp-metodo" style="justify-content:center;">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
          Pagar com cartão (internacional)
        </button>
      </form>
    <?php else: ?>
      <!-- BRL → Asaas: PIX, boleto ou cartão -->
      <?php if ($clientDoc === ''): ?>
      <form method="post" action="/pagar/<?php echo View::e($token); ?>/iniciar" id="form-metodo">
        <div class="pp-campo">
          <label class="pp-label">CPF ou CNPJ</label>
          <input class="input" type="text" name="cpf_cnpj" required inputmode="numeric" placeholder="Somente números" />
          <p style="font-size:12px;color:#94a3b8;margin-top:4px;">Necessário para emitir a cobrança.</p>
        </div>
        <input type="hidden" name="metodo" id="metodo-field" value="PIX" />
        <div class="pp-metodos">
          <button type="submit" class="pp-metodo" onclick="document.getElementById('metodo-field').value='PIX'">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M2 12h20"/></svg>
            <span>PIX<small>Aprovação na hora</small></span>
          </button>
          <button type="submit" class="pp-metodo" onclick="document.getElementById('metodo-field').value='BOLETO'">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            <span>Boleto bancário<small>Compensa em 1-2 dias úteis</small></span>
          </button>
          <button type="submit" class="pp-metodo" onclick="document.getElementById('metodo-field').value='CREDIT_CARD'">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            <span>Cartão de crédito<small>Aprovação na hora</small></span>
          </button>
        </div>
      </form>
      <?php else: ?>
      <div class="pp-metodos">
        <form method="post" action="/pagar/<?php echo View::e($token); ?>/iniciar"><input type="hidden" name="metodo" value="PIX" />
          <button type="submit" class="pp-metodo"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M2 12h20"/></svg><span>PIX<small>Aprovação na hora</small></span></button>
        </form>
        <form method="post" action="/pagar/<?php echo View::e($token); ?>/iniciar"><input type="hidden" name="metodo" value="BOLETO" />
          <button type="submit" class="pp-metodo"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg><span>Boleto bancário<small>Compensa em 1-2 dias úteis</small></span></button>
        </form>
        <form method="post" action="/pagar/<?php echo View::e($token); ?>/iniciar"><input type="hidden" name="metodo" value="CREDIT_CARD" />
          <button type="submit" class="pp-metodo"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg><span>Cartão de crédito<small>Aprovação na hora</small></span></button>
        </form>
      </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
  <p class="pp-foot">Pagamento processado com segurança. Seus dados não ficam salvos neste link.</p>

<?php elseif ($tela === 'pagamento'): ?>
  <div class="pp-card">
    <div class="pp-title"><?php echo View::e($planName); ?></div>
    <p class="pp-sub"><?php echo $paymentValueFmt; ?><?php echo ((int)($link['periodo'] ?? 1) >= 12 ? '/ano' : '/mês'); ?></p>
  </div>

  <?php if ($pago): ?>
  <div class="pp-card" style="text-align:center;">
    <div style="margin-bottom:10px;"><svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="16 9 11 14 8 11"/></svg></div>
    <div class="pp-title">Pagamento confirmado!</div>
    <p class="pp-sub">Tudo certo. Obrigado!</p>
  </div>

  <?php elseif ($billingType === 'PIX'): ?>
  <div class="pp-card" style="text-align:center;" id="pix-area">
    <div class="pp-badge-aguardando"><span class="pp-dot"></span> Aguardando pagamento via PIX</div>
    <?php if ($pixBase64 !== ''): ?>
    <div style="margin:16px auto;max-width:220px;"><img id="pix-qr" src="data:image/png;base64,<?php echo $pixBase64; ?>" alt="QR Code PIX" style="width:100%;border-radius:12px;border:2px solid #e2e8f0;" /></div>
    <?php endif; ?>
    <?php if ($pixPayload !== ''): ?>
    <div style="margin:12px auto;max-width:460px;">
      <label class="pp-label" style="text-align:left;">PIX copia e cola</label>
      <div style="display:flex;gap:8px;">
        <input type="text" id="pix-payload" value="<?php echo View::e($pixPayload); ?>" readonly style="flex:1;padding:10px 12px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:12px;background:#f8fafc;font-family:monospace;" />
        <button type="button" onclick="ppCopiar('pix-payload', this)" class="botao sm" style="white-space:nowrap;">Copiar</button>
      </div>
    </div>
    <?php endif; ?>
    <p class="pp-sub" style="margin-top:14px;">Abra o app do seu banco, escolha PIX e escaneie o QR Code ou cole o código. A confirmação é automática.</p>
  </div>

  <?php elseif ($billingType === 'BOLETO'): ?>
  <div class="pp-card">
    <div style="text-align:center;margin-bottom:16px;">
      <div class="pp-badge-aguardando" style="background:#fef3c7;color:#92400e;"><span class="pp-dot" style="background:#d97706;"></span> Aguardando pagamento do boleto</div>
    </div>
    <?php if ($boletoLinha !== ''): ?>
    <div class="pp-campo">
      <label class="pp-label">Linha digitável</label>
      <div style="display:flex;gap:8px;">
        <input type="text" id="boleto-linha" value="<?php echo View::e($boletoLinha); ?>" readonly style="flex:1;padding:10px 12px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:12px;background:#f8fafc;font-family:monospace;" />
        <button type="button" onclick="ppCopiar('boleto-linha', this)" class="botao sm" style="white-space:nowrap;">Copiar</button>
      </div>
    </div>
    <?php endif; ?>
    <?php if ($boletoUrl !== ''): ?>
    <div style="text-align:center;margin-top:16px;"><a class="botao" href="<?php echo View::e($boletoUrl); ?>" target="_blank" rel="noreferrer">Baixar / imprimir boleto</a></div>
    <?php endif; ?>
    <p class="pp-sub" style="margin-top:16px;text-align:center;">Após o pagamento, o boleto leva 1 a 2 dias úteis para compensar.</p>
  </div>

  <?php elseif ($billingType === 'CREDIT_CARD'): ?>
  <div class="pp-card" id="card-area">
    <div class="pp-title" style="font-size:18px;margin-bottom:16px;">Pagamento com cartão</div>
    <div id="card-erro" class="pp-erro" style="display:none;"></div>
    <div id="card-sucesso" style="display:none;background:#f0fdf4;color:#16a34a;padding:16px;border-radius:10px;font-size:14px;text-align:center;">Pagamento aprovado! Obrigado.</div>

    <form id="form-cartao" onsubmit="return ppEnviarCartao(event)">
      <div style="font-size:13px;font-weight:600;color:#475569;margin-bottom:10px;">Dados do cartão</div>
      <div style="display:grid;gap:10px;margin-bottom:16px;">
        <input class="input" type="text" name="holder_name" placeholder="Nome impresso no cartão" required autocomplete="cc-name" />
        <input class="input" type="text" name="number" placeholder="Número do cartão" required maxlength="19" autocomplete="cc-number" inputmode="numeric" />
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;">
          <input class="input" type="text" name="exp_month" placeholder="MM" required maxlength="2" autocomplete="cc-exp-month" inputmode="numeric" />
          <input class="input" type="text" name="exp_year" placeholder="AAAA" required maxlength="4" autocomplete="cc-exp-year" inputmode="numeric" />
          <input class="input" type="text" name="ccv" placeholder="CVV" required maxlength="4" autocomplete="cc-csc" inputmode="numeric" />
        </div>
      </div>
      <div style="font-size:13px;font-weight:600;color:#475569;margin-bottom:10px;">Dados do titular</div>
      <div style="display:grid;gap:10px;margin-bottom:20px;">
        <input class="input" type="text" name="holder_cpf" placeholder="CPF/CNPJ" required inputmode="numeric" />
        <input class="input" type="email" name="holder_email" placeholder="E-mail" required autocomplete="email" />
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
          <input class="input" type="text" name="holder_phone" placeholder="Telefone" required inputmode="tel" />
          <input class="input" type="text" name="holder_cep" placeholder="CEP" required maxlength="9" inputmode="numeric" />
        </div>
        <input class="input" type="text" name="holder_address_number" placeholder="Número do endereço" required />
      </div>
      <button class="botao" type="submit" style="width:100%;font-size:15px;padding:14px;" id="btn-pagar">Pagar <?php echo $paymentValueFmt; ?></button>
    </form>
  </div>

  <?php else: ?>
  <div class="pp-card" style="text-align:center;">
    <p class="pp-sub">Não foi possível carregar os dados de pagamento. Atualize a página ou solicite um novo link.</p>
  </div>
  <?php endif; ?>

  <p class="pp-foot">Pagamento processado com segurança via gateway. Seus dados do cartão não são armazenados por nós.</p>
<?php endif; ?>
</div>

<script>
function ppCopiar(id, btn){
  var el = document.getElementById(id);
  if(!el) return;
  navigator.clipboard.writeText(el.value).then(function(){
    var o = btn.textContent; btn.textContent='Copiado!'; btn.disabled=true;
    setTimeout(function(){btn.textContent=o;btn.disabled=false;},2000);
  });
}

<?php if ($tela === 'pagamento' && !$pago && in_array($billingType, ['PIX','BOLETO'], true)): ?>
(function(){
  var token = <?php echo json_encode($token); ?>;
  var bt = <?php echo json_encode($billingType); ?>;
  var interval = setInterval(function(){
    fetch('/pagar/'+token+'/status')
      .then(function(r){return r.json();})
      .then(function(d){
        if(d.pago){ clearInterval(interval); location.reload(); }
        if(d.pix && d.pix.encodedImage && bt==='PIX'){
          var qr=document.getElementById('pix-qr'); if(qr) qr.src='data:image/png;base64,'+d.pix.encodedImage;
          var pl=document.getElementById('pix-payload'); if(pl && d.pix.payload) pl.value=d.pix.payload;
        }
      }).catch(function(){});
  }, 5000);
})();
<?php endif; ?>

<?php if ($tela === 'pagamento' && $billingType === 'CREDIT_CARD'): ?>
function ppEnviarCartao(e){
  e.preventDefault();
  var token = <?php echo json_encode($token); ?>;
  var form = document.getElementById('form-cartao');
  var btn = document.getElementById('btn-pagar');
  var erroEl = document.getElementById('card-erro');
  var sucessoEl = document.getElementById('card-sucesso');
  erroEl.style.display='none';
  var btnTxt = btn.textContent;
  btn.disabled=true; btn.textContent='Processando...';
  var fd = new FormData(form);
  fetch('/pagar/'+token+'/cartao', { method:'POST', body:fd })
    .then(function(r){return r.json();})
    .then(function(d){
      if(d.ok && d.pago){
        form.style.display='none'; sucessoEl.style.display='block';
      } else if(d.ok && !d.pago){
        erroEl.textContent='Pagamento em análise. Você será avisado assim que for aprovado.'; erroEl.style.display='block';
        btn.disabled=false; btn.textContent=btnTxt;
      } else {
        erroEl.textContent=d.erro||'Não foi possível processar o pagamento.'; erroEl.style.display='block';
        btn.disabled=false; btn.textContent=btnTxt;
      }
    })
    .catch(function(){
      erroEl.textContent='Erro de conexão. Tente novamente.'; erroEl.style.display='block';
      btn.disabled=false; btn.textContent=btnTxt;
    });
  return false;
}
<?php endif; ?>
</script>
</body>
</html>
