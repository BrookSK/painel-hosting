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
$cpu         = (int)($link['cpu'] ?? 0);
$ramMb       = (int)($link['ram'] ?? 0);
$storageMb   = (int)($link['storage'] ?? 0);
$periodo     = (int)($link['periodo'] ?? 1);
$periodoLabel = $periodo >= 12 ? '/ano' : '/mês';

$precoBrl = (float)($link['price_monthly'] ?? 0);
$precoUsd = (float)($link['price_monthly_usd'] ?? 0);
$precoFmt = $currency === 'USD'
    ? ('US$ ' . number_format($precoUsd > 0 ? $precoUsd : $precoBrl, 2, '.', ','))
    : ('R$ ' . number_format($precoBrl, 2, ',', '.'));

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
  <meta name="referrer" content="no-referrer" />
  <title>Pagamento — <?php echo View::e($_nome); ?></title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
  <style>
    :root{ --pp-primary:#4F46E5; --pp-primary-dark:#4338ca; --pp-ink:#0f172a; --pp-muted:#64748b; --pp-line:#e8ebf1; }
    *{box-sizing:border-box;}
    body{
      background:linear-gradient(160deg,#eef2ff 0%,#f1f5f9 45%,#faf5ff 100%);
      min-height:100vh;margin:0;padding:32px 16px;
      font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;
      -webkit-font-smoothing:antialiased;
    }
    .pp-wrap{width:100%;max-width:460px;margin:0 auto;}
    .pp-brand{display:flex;align-items:center;justify-content:center;gap:10px;margin-bottom:22px;}
    .pp-brand img{height:34px;width:auto;}
    .pp-brand-name{font-size:17px;font-weight:800;color:var(--pp-ink);}
    .pp-card{
      background:#fff;border:1px solid var(--pp-line);border-radius:20px;padding:28px 26px;
      box-shadow:0 10px 40px -12px rgba(79,70,229,.18), 0 2px 8px rgba(15,23,42,.04);margin-bottom:14px;
    }
    .pp-title{font-size:22px;font-weight:800;color:var(--pp-ink);letter-spacing:-.02em;margin:0 0 4px;}
    .pp-sub{font-size:14px;color:var(--pp-muted);margin:0 0 20px;line-height:1.5;}

    /* resumo do plano */
    .pp-resumo{
      background:linear-gradient(135deg,#4F46E5,#6366f1);border-radius:16px;padding:18px 20px;margin-bottom:22px;color:#fff;
      box-shadow:0 8px 24px -8px rgba(79,70,229,.5);
    }
    .pp-resumo-top{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;margin-bottom:14px;}
    .pp-resumo-plano{font-size:17px;font-weight:800;letter-spacing:-.01em;}
    .pp-resumo-cliente{font-size:12px;color:rgba(255,255,255,.75);margin-top:2px;}
    .pp-resumo-preco{font-size:22px;font-weight:800;white-space:nowrap;}
    .pp-resumo-preco small{font-size:12px;font-weight:600;opacity:.8;}
    .pp-specs{display:flex;gap:14px;flex-wrap:wrap;font-size:12px;color:rgba(255,255,255,.9);border-top:1px solid rgba(255,255,255,.18);padding-top:12px;}
    .pp-specs span{display:inline-flex;align-items:center;gap:5px;}

    /* campo */
    .pp-campo{margin-bottom:18px;}
    .pp-label{display:block;font-size:13px;font-weight:700;color:#374151;margin-bottom:7px;}
    .pp-input{width:100%;padding:12px 14px;border:1.5px solid var(--pp-line);border-radius:12px;font-size:15px;color:var(--pp-ink);transition:border-color .15s, box-shadow .15s;background:#fff;}
    .pp-input:focus{outline:none;border-color:var(--pp-primary);box-shadow:0 0 0 4px rgba(79,70,229,.1);}
    .pp-hint{font-size:12px;color:#94a3b8;margin-top:5px;}

    /* métodos */
    .pp-sec-label{font-size:12px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin:0 0 10px;}
    .pp-metodos{display:grid;gap:10px;}
    .pp-metodo{
      display:flex;align-items:center;gap:14px;padding:15px 16px;border:1.5px solid var(--pp-line);border-radius:14px;
      cursor:pointer;transition:all .15s;background:#fff;text-align:left;width:100%;font-family:inherit;
    }
    .pp-metodo:hover{border-color:var(--pp-primary);background:#f5f3ff;transform:translateY(-1px);box-shadow:0 6px 16px -8px rgba(79,70,229,.4);}
    .pp-metodo:active{transform:translateY(0);}
    .pp-metodo-ic{width:42px;height:42px;border-radius:11px;background:#eef2ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;color:var(--pp-primary);}
    .pp-metodo-tx{flex:1;}
    .pp-metodo-tx b{display:block;font-size:15px;font-weight:700;color:var(--pp-ink);}
    .pp-metodo-tx small{display:block;font-size:12px;color:var(--pp-muted);margin-top:1px;}
    .pp-metodo-arrow{color:#cbd5e1;flex-shrink:0;}

    .pp-btn{
      width:100%;padding:15px;border:none;border-radius:13px;font-size:15px;font-weight:700;cursor:pointer;
      background:linear-gradient(135deg,#4F46E5,#6366f1);color:#fff;transition:filter .15s, transform .1s;font-family:inherit;
      box-shadow:0 8px 20px -8px rgba(79,70,229,.6);
    }
    .pp-btn:hover{filter:brightness(1.05);}
    .pp-btn:active{transform:translateY(1px);}
    .pp-btn:disabled{opacity:.6;cursor:not-allowed;}
    .pp-btn-sec{background:#f1f5f9;color:#334155;box-shadow:none;}

    .pp-erro{background:#fef2f2;color:#dc2626;padding:12px 14px;border-radius:12px;font-size:14px;margin-bottom:16px;border:1px solid #fecaca;}
    .pp-badge{display:inline-flex;align-items:center;gap:8px;background:#eff6ff;color:#1e40af;padding:7px 15px;border-radius:30px;font-size:13px;font-weight:700;margin-bottom:18px;}
    .pp-dot{display:inline-block;width:8px;height:8px;background:#3b82f6;border-radius:50%;animation:pulse 1.4s infinite;}
    .pp-foot{text-align:center;font-size:12px;color:#94a3b8;margin-top:6px;display:flex;align-items:center;justify-content:center;gap:6px;}
    .pp-grid2{display:grid;grid-template-columns:1fr 1fr;gap:10px;}
    .pp-grid3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;}
    .pp-copybox{display:flex;gap:8px;}
    .pp-copybox input{flex:1;padding:11px 13px;border:1.5px solid var(--pp-line);border-radius:11px;font-size:12px;background:#f8fafc;font-family:monospace;color:#475569;}
    .pp-copybtn{white-space:nowrap;padding:0 16px;border:none;border-radius:11px;background:var(--pp-primary);color:#fff;font-weight:700;font-size:13px;cursor:pointer;}
    .pp-ok-ic{width:64px;height:64px;margin:0 auto 14px;border-radius:50%;background:#dcfce7;display:flex;align-items:center;justify-content:center;}
    @keyframes pulse{0%,100%{opacity:1}50%{opacity:.35}}
    @media(max-width:480px){ .pp-card{padding:22px 18px;} .pp-resumo-preco{font-size:19px;} }
  </style>
</head>
<body>
<div class="pp-wrap">
  <div class="pp-brand">
    <?php if ($_logo !== ''): ?>
      <img src="<?php echo View::e($_logo); ?>" alt="<?php echo View::e($_nome); ?>" />
    <?php else: ?>
      <svg width="30" height="30" viewBox="0 0 26 26" fill="none"><rect width="26" height="26" rx="7" fill="#4F46E5"/><path d="M6 13h14M13 6v14" stroke="#fff" stroke-width="2.2" stroke-linecap="round"/></svg>
      <span class="pp-brand-name"><?php echo View::e($_nome); ?></span>
    <?php endif; ?>
  </div>

<?php if ($tela === 'erro'): ?>
  <div class="pp-card" style="text-align:center;">
    <div style="width:64px;height:64px;margin:0 auto 14px;border-radius:50%;background:#fef3c7;display:flex;align-items:center;justify-content:center;">
      <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
    </div>
    <h1 class="pp-title"><?php echo View::e($tituloErro !== '' ? $tituloErro : 'Link indisponível'); ?></h1>
    <p class="pp-sub" style="margin-bottom:0;"><?php echo View::e($msgErro !== '' ? $msgErro : 'Este link de pagamento não está disponível.'); ?></p>
    <?php if ($token !== ''): ?>
    <a href="/pagar/<?php echo View::e($token); ?>" class="pp-btn pp-btn-sec" style="display:inline-block;text-decoration:none;margin-top:18px;width:auto;padding:12px 24px;">Voltar</a>
    <?php endif; ?>
  </div>

<?php elseif ($tela === 'confirmado'): ?>
  <div class="pp-card" style="text-align:center;">
    <div class="pp-ok-ic"><svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div>
    <h1 class="pp-title">Pagamento confirmado!</h1>
    <p class="pp-sub" style="margin-bottom:0;">Recebemos o pagamento do plano <strong><?php echo View::e($planName); ?></strong>. Está tudo certo — você não precisa fazer mais nada.</p>
  </div>

<?php elseif ($tela === 'escolha'): ?>
  <div class="pp-card">
    <h1 class="pp-title">Finalizar pagamento</h1>
    <p class="pp-sub">Confira os dados e escolha como quer pagar.</p>

    <div class="pp-resumo">
      <div class="pp-resumo-top">
        <div>
          <div class="pp-resumo-plano"><?php echo View::e($planName); ?></div>
          <?php if ($clientName !== ''): ?><div class="pp-resumo-cliente"><?php echo View::e($clientName); ?></div><?php endif; ?>
        </div>
        <div class="pp-resumo-preco"><?php echo $precoFmt; ?><small><?php echo $periodoLabel; ?></small></div>
      </div>
      <?php if ($cpu > 0 || $ramMb > 0 || $storageMb > 0): ?>
      <div class="pp-specs">
        <?php if ($cpu > 0): ?><span><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="4" width="16" height="16" rx="2"/><rect x="9" y="9" width="6" height="6"/><path d="M9 1v3M15 1v3M9 20v3M15 20v3M20 9h3M20 14h3M1 9h3M1 14h3"/></svg><?php echo $cpu; ?> vCPU</span><?php endif; ?>
        <?php if ($ramMb > 0): ?><span><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="10" rx="1"/><path d="M6 7v10M10 7v10M14 7v10M18 7v10"/></svg><?php echo round($ramMb / 1024); ?> GB RAM</span><?php endif; ?>
        <?php if ($storageMb > 0): ?><span><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg><?php echo round($storageMb / 1024); ?> GB SSD</span><?php endif; ?>
      </div>
      <?php endif; ?>
    </div>

    <?php if ($currency === 'USD'): ?>
      <form method="post" action="/pagar/<?php echo View::e($token); ?>/iniciar">
        <p class="pp-sec-label">Forma de pagamento</p>
        <button type="submit" class="pp-metodo" style="margin-bottom:8px;">
          <span class="pp-metodo-ic"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg></span>
          <span class="pp-metodo-tx"><b>Cartão de crédito</b><small>Pagamento internacional seguro</small></span>
          <span class="pp-metodo-arrow"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg></span>
        </button>
      </form>
    <?php else: ?>
      <form method="post" action="/pagar/<?php echo View::e($token); ?>/iniciar" id="form-metodo">
        <div class="pp-campo">
          <label class="pp-label">CPF ou CNPJ do pagador</label>
          <input class="pp-input" type="text" name="cpf_cnpj" id="cpf-field" inputmode="numeric"
                 value="<?php echo View::e($clientDoc); ?>"
                 placeholder="Somente números" required />
          <p class="pp-hint">Necessário para emitir a cobrança (nota fiscal e identificação).</p>
        </div>

        <input type="hidden" name="metodo" id="metodo-field" value="PIX" />
        <p class="pp-sec-label">Como você quer pagar?</p>
        <div class="pp-metodos">
          <button type="submit" class="pp-metodo" onclick="document.getElementById('metodo-field').value='PIX'">
            <span class="pp-metodo-ic"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2 2 12l10 10 10-10z"/><path d="M8 12h8M12 8v8" stroke-width="1.6"/></svg></span>
            <span class="pp-metodo-tx"><b>PIX</b><small>Aprovação na hora</small></span>
            <span class="pp-metodo-arrow"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg></span>
          </button>
          <button type="submit" class="pp-metodo" onclick="document.getElementById('metodo-field').value='CREDIT_CARD'">
            <span class="pp-metodo-ic"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg></span>
            <span class="pp-metodo-tx"><b>Cartão de crédito</b><small>Aprovação na hora</small></span>
            <span class="pp-metodo-arrow"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg></span>
          </button>
          <button type="submit" class="pp-metodo" onclick="document.getElementById('metodo-field').value='BOLETO'">
            <span class="pp-metodo-ic"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></span>
            <span class="pp-metodo-tx"><b>Boleto bancário</b><small>Compensa em 1-2 dias úteis</small></span>
            <span class="pp-metodo-arrow"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg></span>
          </button>
        </div>
      </form>
    <?php endif; ?>
  </div>
  <p class="pp-foot"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg> Pagamento processado com segurança</p>

<?php elseif ($tela === 'pagamento'): ?>
  <div class="pp-card">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;">
      <div>
        <h1 class="pp-title" style="font-size:18px;margin:0;"><?php echo View::e($planName); ?></h1>
        <?php if ($clientName !== ''): ?><p class="pp-sub" style="margin:2px 0 0;font-size:13px;"><?php echo View::e($clientName); ?></p><?php endif; ?>
      </div>
      <div style="font-size:19px;font-weight:800;color:var(--pp-ink);white-space:nowrap;"><?php echo $paymentValueFmt; ?></div>
    </div>
  </div>

  <?php if ($pago): ?>
  <div class="pp-card" style="text-align:center;">
    <div class="pp-ok-ic"><svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div>
    <h1 class="pp-title">Pagamento confirmado!</h1>
    <p class="pp-sub" style="margin-bottom:0;">Tudo certo. Muito obrigado!</p>
  </div>

  <?php elseif ($billingType === 'PIX'): ?>
  <div class="pp-card" style="text-align:center;" id="pix-area">
    <div class="pp-badge"><span class="pp-dot"></span> Aguardando pagamento</div>
    <?php if ($pixBase64 !== ''): ?>
    <div style="margin:4px auto 18px;max-width:230px;"><img id="pix-qr" src="data:image/png;base64,<?php echo $pixBase64; ?>" alt="QR Code PIX" style="width:100%;border-radius:14px;border:1px solid var(--pp-line);padding:8px;background:#fff;" /></div>
    <?php endif; ?>
    <p style="font-size:14px;color:var(--pp-ink);font-weight:600;margin:0 0 10px;">Escaneie o QR Code no app do seu banco</p>
    <?php if ($pixPayload !== ''): ?>
    <div style="text-align:left;margin-top:14px;">
      <label class="pp-label">Ou use o PIX copia e cola</label>
      <div class="pp-copybox">
        <input type="text" id="pix-payload" value="<?php echo View::e($pixPayload); ?>" readonly />
        <button type="button" class="pp-copybtn" onclick="ppCopiar('pix-payload', this)">Copiar</button>
      </div>
    </div>
    <?php endif; ?>
    <p class="pp-sub" style="margin:16px 0 0;font-size:13px;">A confirmação é automática assim que o PIX cair.</p>
  </div>

  <?php elseif ($billingType === 'BOLETO'): ?>
  <div class="pp-card">
    <div style="text-align:center;"><div class="pp-badge" style="background:#fffbeb;color:#92400e;"><span class="pp-dot" style="background:#d97706;"></span> Aguardando pagamento</div></div>
    <?php if ($boletoLinha !== ''): ?>
    <div class="pp-campo">
      <label class="pp-label">Linha digitável</label>
      <div class="pp-copybox">
        <input type="text" id="boleto-linha" value="<?php echo View::e($boletoLinha); ?>" readonly />
        <button type="button" class="pp-copybtn" onclick="ppCopiar('boleto-linha', this)">Copiar</button>
      </div>
    </div>
    <?php endif; ?>
    <?php if ($boletoUrl !== ''): ?>
    <a class="pp-btn" href="<?php echo View::e($boletoUrl); ?>" target="_blank" rel="noreferrer" style="display:block;text-align:center;text-decoration:none;margin-top:6px;">Baixar / imprimir boleto</a>
    <?php endif; ?>
    <p class="pp-sub" style="margin:16px 0 0;text-align:center;font-size:13px;">Após o pagamento, o boleto leva 1 a 2 dias úteis para compensar.</p>
  </div>

  <?php elseif ($billingType === 'CREDIT_CARD'): ?>
  <div class="pp-card" id="card-area">
    <h2 class="pp-title" style="font-size:17px;margin-bottom:16px;">Dados do cartão</h2>
    <div id="card-erro" class="pp-erro" style="display:none;"></div>
    <div id="card-sucesso" style="display:none;text-align:center;padding:8px 0;">
      <div class="pp-ok-ic" style="width:52px;height:52px;"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div>
      <p style="font-weight:700;color:#16a34a;margin:0;">Pagamento aprovado! Obrigado.</p>
    </div>

    <form id="form-cartao" onsubmit="return ppEnviarCartao(event)">
      <div class="pp-campo"><input class="pp-input" type="text" name="holder_name" placeholder="Nome impresso no cartão" required autocomplete="cc-name" /></div>
      <div class="pp-campo"><input class="pp-input" type="text" name="number" placeholder="Número do cartão" required maxlength="19" autocomplete="cc-number" inputmode="numeric" /></div>
      <div class="pp-campo pp-grid3">
        <input class="pp-input" type="text" name="exp_month" placeholder="MM" required maxlength="2" autocomplete="cc-exp-month" inputmode="numeric" />
        <input class="pp-input" type="text" name="exp_year" placeholder="AAAA" required maxlength="4" autocomplete="cc-exp-year" inputmode="numeric" />
        <input class="pp-input" type="text" name="ccv" placeholder="CVV" required maxlength="4" autocomplete="cc-csc" inputmode="numeric" />
      </div>
      <p class="pp-sec-label" style="margin-top:4px;">Dados do titular</p>
      <div class="pp-campo"><input class="pp-input" type="text" name="holder_cpf" placeholder="CPF/CNPJ" required inputmode="numeric" value="<?php echo View::e($clientDoc); ?>" /></div>
      <div class="pp-campo"><input class="pp-input" type="email" name="holder_email" placeholder="E-mail" required autocomplete="email" /></div>
      <div class="pp-campo pp-grid2">
        <input class="pp-input" type="text" name="holder_phone" placeholder="Telefone" required inputmode="tel" />
        <input class="pp-input" type="text" name="holder_cep" placeholder="CEP" required maxlength="9" inputmode="numeric" />
      </div>
      <div class="pp-campo"><input class="pp-input" type="text" name="holder_address_number" placeholder="Número do endereço" required /></div>
      <button class="pp-btn" type="submit" id="btn-pagar">Pagar <?php echo $paymentValueFmt; ?></button>
    </form>
  </div>

  <?php else: ?>
  <div class="pp-card" style="text-align:center;">
    <p class="pp-sub" style="margin:0;">Não foi possível carregar os dados de pagamento. Atualize a página ou solicite um novo link.</p>
  </div>
  <?php endif; ?>

  <p class="pp-foot"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg> Seus dados de cartão não são armazenados por nós</p>
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
