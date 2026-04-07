<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\Csrf;
use LRV\Core\I18n;
use LRV\Core\SistemaConfig;

$plano   = is_array($plano ?? null) ? $plano : [];
$addons  = is_array($addons ?? null) ? $addons : [];
$upsell  = is_array($upsell ?? null) ? $upsell : null;
$d6      = (float)($desconto_6m ?? 5);
$d12     = (float)($desconto_12m ?? 10);
$preco   = (float)($plano['price_monthly'] ?? 0);
$planId  = (int)($plano['id'] ?? 0);
$cpu     = (int)($plano['cpu'] ?? 0);
$ramGb   = round((int)($plano['ram'] ?? 0) / 1024);
$discoGb = round((int)($plano['storage'] ?? 0) / 1024);
$isBrl   = I18n::moedaCodigo() === 'BRL';
$moeda   = $isBrl ? 'BRL' : 'USD';
$moedaJs = I18n::moedaCodigo();
?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title><?php echo View::e((string)($plano['name'] ?? '')); ?> — <?php echo View::e(SistemaConfig::nome()); ?></title>
  <?php require __DIR__ . '/../_partials/seo.php'; ?>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
  <style>
    body{background:#fff;max-width:100vw}
    .wz-steps{display:flex;gap:0;align-items:center;justify-content:center;padding:28px 16px 8px}
    .wz-step{display:flex;align-items:center;gap:8px;font-size:13px;color:#94a3b8}
    .wz-step.active{color:#4F46E5;font-weight:600}
    .wz-step.done{color:#16a34a}
    .wz-step-dot{width:30px;height:30px;border-radius:50%;border:2px solid #e2e8f0;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;background:#fff}
    .wz-step.active .wz-step-dot{border-color:#4F46E5;background:linear-gradient(135deg,#4F46E5,#7C3AED);color:#fff}
    .wz-step.done .wz-step-dot{border-color:#16a34a;background:#16a34a;color:#fff}
    .wz-line{width:48px;height:2px;background:#e2e8f0;margin:0 6px}
    .wz-line.done{background:#16a34a}
    .wz-panel{display:none}
    .wz-panel.active{display:block}
    .wz-field{margin-bottom:14px}
    .wz-label{display:block;font-size:13px;font-weight:600;margin-bottom:5px;color:#334155}
    .periodo-opt{padding:14px 18px;border:1.5px solid #e2e8f0;border-radius:16px;cursor:pointer;transition:all .15s;display:flex;justify-content:space-between;align-items:center;background:#fff}
    .periodo-opt.sel{border-color:#4F46E5;background:#f5f3ff}
    .periodo-opt:hover{border-color:#7C3AED}
    .desc-badge{background:#dcfce7;color:#166534;font-size:11px;font-weight:700;padding:2px 8px;border-radius:999px}
    .addon-opt{padding:14px;border:1.5px solid #e2e8f0;border-radius:16px;cursor:pointer;display:flex;align-items:center;gap:12px;transition:all .15s;background:#fff}
    .addon-opt.sel{border-color:#4F46E5;background:#f5f3ff}
    .gw-opt{padding:14px 18px;border:1.5px solid #e2e8f0;border-radius:16px;cursor:pointer;text-align:center;font-size:14px;font-weight:600;transition:all .15s;flex:1;background:#fff}
    .gw-opt.sel{border-color:#4F46E5;background:#f5f3ff}
    .gw-opt:hover{border-color:#7C3AED}
    .upsell-card{border:2px solid #4F46E5;background:#f5f3ff;position:relative}
    .upsell-badge{position:absolute;top:-12px;left:50%;transform:translateX(-50%);background:linear-gradient(135deg,#4F46E5,#7C3AED);color:#fff;font-size:11px;font-weight:700;padding:3px 16px;border-radius:99px;white-space:nowrap}
    .overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:200;align-items:center;justify-content:center}
    .overlay.show{display:flex}
    .erro-msg{background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:12px 14px;border-radius:12px;font-size:13px;margin-bottom:12px;display:none}
    .spinner-sm{display:inline-block;width:14px;height:14px;border:2px solid #e2e8f0;border-top-color:#4F46E5;border-radius:50%;animation:spin .6s linear infinite;vertical-align:middle;margin-right:6px}
    @keyframes spin{to{transform:rotate(360deg)}}
    @media(max-width:600px){.wz-step span{display:none}.wz-line{width:24px}}
  </style>
</head>
<body>
<?php require __DIR__ . '/../_partials/navbar-publica.php'; ?>

<div class="wz-steps" id="stepsBar">
  <div class="wz-step active" data-s="0"><div class="wz-step-dot">1</div><span><?php echo View::e(I18n::t('wz.step_plano')); ?></span></div>
  <div class="wz-line"></div>
  <div class="wz-step" data-s="1"><div class="wz-step-dot">2</div><span><?php echo View::e(I18n::t('wz.step_config')); ?></span></div>
  <div class="wz-line"></div>
  <div class="wz-step" data-s="2"><div class="wz-step-dot">3</div><span><?php echo View::e(I18n::t('wz.step_dados')); ?></span></div>
  <div class="wz-line"></div>
  <div class="wz-step" data-s="3"><div class="wz-step-dot">4</div><span><?php echo View::e(I18n::t('wz.step_pagamento')); ?></span></div>
</div>

<div class="conteudo" style="max-width:860px;">

<!-- ═══ STEP 0: Landing do plano ═══ -->
<div class="wz-panel active" id="step0">
  <div class="card">
    <h2 class="titulo" style="font-size:24px;margin-bottom:4px;"><?php echo View::e((string)($plano['name'] ?? '')); ?></h2>
    <p class="texto" style="margin-bottom:16px;"><?php echo View::e((string)($plano['description'] ?? '')); ?></p>
    <div class="linha" style="gap:8px;margin-bottom:16px;">
      <span class="badge"><?php echo $cpu; ?> vCPU</span>
      <span class="badge"><?php echo $ramGb; ?> GB RAM</span>
      <span class="badge"><?php echo $discoGb; ?> GB SSD</span>
    </div>
    <div style="font-size:28px;font-weight:800;color:#0f172a;margin-bottom:8px;">
      <?php echo View::e(I18n::preco($preco)); ?><span style="font-size:14px;font-weight:400;color:#64748b;">/mês</span>
    </div>
    <ul class="texto" style="padding-left:18px;margin:16px 0;line-height:2.2;">
      <li><?php echo View::e(I18n::t('wz.vps_dedicado')); ?></li>
      <li><?php echo View::e(I18n::t('wz.painel_completo')); ?></li>
      <li><?php echo View::e(I18n::t('wz.backups_auto')); ?></li>
      <li><?php echo View::e(I18n::t('wz.monitoramento_24')); ?></li>
      <li><?php echo View::e(I18n::t('wz.suporte_incluso')); ?></li>
    </ul>
    <button class="botao" style="width:100%;justify-content:center;font-size:15px;padding:14px;" onclick="irPasso(1)"><?php echo View::e(I18n::t('wz.continuar_plano')); ?></button>
  </div>

  <?php if ($upsell): ?>
  <div class="card upsell-card" style="margin-top:8px;">
    <div class="upsell-badge"><?php echo View::e(I18n::t('wz.recomendado')); ?></div>
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-top:6px;">
      <div>
        <div class="titulo" style="font-size:18px;margin-bottom:4px;"><?php echo View::e((string)($upsell['name'] ?? '')); ?></div>
        <p class="texto" style="margin-bottom:8px;"><?php echo View::e((string)($upsell['description'] ?? '')); ?></p>
        <div class="linha" style="gap:6px;">
          <span class="badge"><?php echo (int)($upsell['cpu'] ?? 0); ?> vCPU</span>
          <span class="badge"><?php echo round((int)($upsell['ram'] ?? 0) / 1024); ?> GB RAM</span>
          <span class="badge"><?php echo round((int)($upsell['storage'] ?? 0) / 1024); ?> GB SSD</span>
        </div>
      </div>
      <div style="text-align:right;">
        <div style="font-size:22px;font-weight:800;color:#4F46E5;"><?php echo View::e(I18n::preco((float)($upsell['price_monthly'] ?? 0))); ?><span style="font-size:12px;font-weight:400;color:#64748b;">/mês</span></div>
        <a href="/contratar?plan_id=<?php echo (int)($upsell['id'] ?? 0); ?>" class="botao sm" style="margin-top:8px;"><?php echo View::e(I18n::t('wz.escolher_este')); ?></a>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>

<!-- ═══ STEP 1: Configuração ═══ -->
<div class="wz-panel" id="step1">
  <div class="card">
    <div class="titulo" style="font-size:18px;"><?php echo View::e(I18n::t('wz.config_vps')); ?></div>
    <p class="texto"><?php echo View::e(I18n::tf('wz.plano_inclui', (string)($plano['name'] ?? ''))); ?></p>
    <div class="grid-3" style="margin-top:12px;margin-bottom:8px;">
      <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:14px;text-align:center;">
        <div style="font-size:24px;font-weight:800;color:#4F46E5;"><?php echo $cpu; ?></div>
        <div style="font-size:12px;color:#64748b;">vCPU</div>
      </div>
      <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:14px;text-align:center;">
        <div style="font-size:24px;font-weight:800;color:#4F46E5;"><?php echo $ramGb; ?> GB</div>
        <div style="font-size:12px;color:#64748b;">RAM</div>
      </div>
      <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:14px;text-align:center;">
        <div style="font-size:24px;font-weight:800;color:#4F46E5;"><?php echo $discoGb; ?> GB</div>
        <div style="font-size:12px;color:#64748b;">SSD</div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="subtitulo"><?php echo View::e(I18n::t('wz.qtd_servidores')); ?></div>
    <p class="texto" style="font-size:13px;"><?php echo View::e(I18n::t('wz.qtd_desc')); ?></p>
    <div class="linha" style="gap:12px;margin-top:8px;">
      <button class="botao ghost sm" onclick="alterarQtd(-1)" style="width:40px;padding:8px 0;justify-content:center;font-size:18px;">−</button>
      <input type="number" id="qtdServidores" value="1" min="1" max="20" class="input" style="width:70px;text-align:center;font-size:18px;font-weight:700;" onchange="atualizarResumo()"/>
      <button class="botao ghost sm" onclick="alterarQtd(1)" style="width:40px;padding:8px 0;justify-content:center;font-size:18px;">+</button>
    </div>
  </div>

  <div class="card">
    <div class="subtitulo">💱 Moeda de pagamento</div>
    <div class="linha" style="gap:8px;margin-top:8px;">
      <div class="currency-opt gw-opt sel" data-cur="BRL" onclick="selCurrency('BRL')">🇧🇷 Real (BRL)</div>
      <div class="currency-opt gw-opt" data-cur="USD" onclick="selCurrency('USD')">🇺🇸 Dólar (USD)</div>
    </div>
    <p class="texto" style="font-size:12px;margin-top:6px;">BRL: PIX, Boleto ou Cartão. USD: Cartão via Stripe.</p>
  </div>

  <div class="card">
    <div class="subtitulo"><?php echo View::e(I18n::t('wz.periodo')); ?></div>
    <div style="display:flex;flex-direction:column;gap:8px;margin-top:10px;">
      <div class="periodo-opt sel" data-periodo="monthly" onclick="selPeriodo('monthly')">
        <div><strong><?php echo View::e(I18n::t('wz.mensal')); ?></strong><br><span style="font-size:12px;color:#64748b;"><?php echo View::e(I18n::t('wz.sem_compromisso')); ?></span></div>
        <div style="text-align:right;">
          <div style="font-weight:700;" id="precoMensalWz"></div>
          <div style="font-size:11px;color:#94a3b8;" id="cobradoMensalWz"></div>
        </div>
      </div>
      <?php
        $hasUpfrontWz = ((float)($plano['price_annual_upfront'] ?? 0)) > 0 || ((float)($plano['price_annual_upfront_usd'] ?? 0)) > 0;
      ?>
      <?php if ($hasUpfrontWz): ?>
      <div class="periodo-opt" data-periodo="annual" onclick="selPeriodo('annual')">
        <div><strong>Anual à vista</strong> <span class="desc-badge" style="background:#dcfce7;color:#166534;">Melhor preço</span><br><span style="font-size:12px;color:#64748b;">Pagamento único — 12 meses</span></div>
        <div style="text-align:right;">
          <div style="font-weight:700;" id="precoAnualWz"></div>
          <div style="font-size:11px;color:#16a34a;" id="economiaWz"></div>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <?php if (!empty($addons)): ?>
  <div class="card">
    <div class="subtitulo"><?php echo View::e(I18n::t('wz.addons')); ?></div>
    <p class="texto" style="font-size:13px;"><?php echo View::e(I18n::t('wz.addons_desc')); ?></p>
    <div style="display:flex;flex-direction:column;gap:8px;margin-top:8px;">
      <?php
        $preAddons = is_array($pre_addons ?? null) ? $pre_addons : [];
      ?>
      <?php foreach ($addons as $a):
        $aId = (int)($a['id'] ?? 0);
        $aPreSel = in_array($aId, $preAddons, true);
      ?>
      <div class="addon-opt<?php echo $aPreSel ? ' sel' : ''; ?>" data-addon-id="<?php echo $aId; ?>" data-addon-price="<?php echo (float)($a['price'] ?? 0); ?>" data-addon-price-usd="<?php echo (float)($a['price_usd'] ?? 0); ?>" data-addon-price-annual="<?php echo (float)($a['price_annual'] ?? 0); ?>" data-addon-price-annual-usd="<?php echo (float)($a['price_annual_usd'] ?? 0); ?>" onclick="toggleAddon(this)">
        <input type="checkbox" <?php echo $aPreSel ? 'checked' : ''; ?> style="accent-color:#4F46E5;width:18px;height:18px;pointer-events:none;"/>
        <div style="flex:1;">
          <div style="font-weight:600;font-size:14px;"><?php echo View::e((string)($a['name'] ?? '')); ?></div>
          <?php if (($a['description'] ?? '') !== ''): ?>
            <div style="font-size:12px;color:#64748b;"><?php echo View::e((string)($a['description'] ?? '')); ?></div>
          <?php endif; ?>
        </div>
        <div style="font-weight:700;color:#4F46E5;white-space:nowrap;">+<?php echo View::e(I18n::preco((float)($a['price'] ?? 0))); ?>/mês</div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <div class="card" style="background:#f8fafc;">
    <div class="subtitulo"><?php echo View::e(I18n::t('wz.resumo')); ?></div>
    <div id="resumoConfig" style="font-size:14px;line-height:2;"></div>
    <div style="border-top:1px solid #e2e8f0;padding-top:10px;margin-top:8px;display:flex;justify-content:space-between;font-size:20px;font-weight:800;">
      <span><?php echo View::e(I18n::t('wz.total')); ?></span>
      <span id="resumoTotal" style="color:#4F46E5;"></span>
    </div>
  </div>

  <div class="linha" style="gap:8px;margin-top:16px;">
    <button class="botao ghost" onclick="irPasso(0)"><?php echo View::e(I18n::t('wz.voltar')); ?></button>
    <button class="botao" style="flex:1;justify-content:center;" onclick="avancarStep1()"><?php echo View::e(I18n::t('wz.proximo')); ?></button>
  </div>
</div>

<!-- ═══ STEP 2: Dados pessoais ═══ -->
<div class="wz-panel" id="step2">
  <div class="card">
    <div class="titulo" style="font-size:18px;"><?php echo View::e(I18n::t('wz.crie_conta')); ?></div>
    <p class="texto"><?php echo View::e(I18n::t('wz.crie_conta_desc')); ?></p>
    <div id="erroConta" class="erro-msg"></div>
    <div class="wz-field">
      <label class="wz-label"><?php echo View::e(I18n::t('wz.nome_completo')); ?> *</label>
      <input class="input" type="text" id="cNome" required placeholder="<?php echo View::e(I18n::t('wz.nome_completo')); ?>"/>
    </div>
    <div class="wz-field">
      <label class="wz-label">E-mail *</label>
      <input class="input" type="email" id="cEmail" required placeholder="seu@email.com"/>
    </div>
    <div class="wz-field" id="cpfField">
      <label class="wz-label"><?php echo View::e(I18n::t('wz.cpf_cnpj')); ?> *</label>
      <input class="input" type="text" id="cCpf" placeholder="000.000.000-00" maxlength="18" inputmode="numeric"/>
    </div>
    <div class="wz-field">
      <label class="wz-label"><?php echo View::e(I18n::t('wz.celular')); ?></label>
      <div class="linha" style="gap:8px;">
        <select class="input" id="cDdi" style="width:110px;flex-shrink:0;">
          <option value="+55">🇧🇷 +55</option>
          <option value="+1">🇺🇸 +1</option>
          <option value="+351">🇵🇹 +351</option>
          <option value="+34">🇪🇸 +34</option>
          <option value="+44">🇬🇧 +44</option>
          <option value="+49">🇩🇪 +49</option>
          <option value="+33">🇫🇷 +33</option>
          <option value="+39">🇮🇹 +39</option>
          <option value="+81">🇯🇵 +81</option>
          <option value="+86">🇨🇳 +86</option>
          <option value="+91">🇮🇳 +91</option>
          <option value="+54">🇦🇷 +54</option>
          <option value="+56">🇨🇱 +56</option>
          <option value="+57">🇨🇴 +57</option>
          <option value="+52">🇲🇽 +52</option>
          <option value="+598">🇺🇾 +598</option>
          <option value="+595">🇵🇾 +595</option>
        </select>
        <input class="input" type="tel" id="cCelular" placeholder="(11) 99999-0000" style="flex:1;"/>
      </div>
    </div>
    <div class="linha" style="gap:8px;">
      <div class="wz-field" style="flex:1;">
        <label class="wz-label"><?php echo View::e(I18n::t('wz.pais')); ?></label>
        <select class="input" id="cPais">
          <option value="BR">🇧🇷 Brasil</option>
          <option value="US">🇺🇸 United States</option>
          <option value="PT">🇵🇹 Portugal</option>
          <option value="ES">🇪🇸 España</option>
          <option value="AR">🇦🇷 Argentina</option>
          <option value="CL">🇨🇱 Chile</option>
          <option value="CO">🇨🇴 Colombia</option>
          <option value="MX">🇲🇽 México</option>
          <option value="UY">🇺🇾 Uruguay</option>
          <option value="PY">🇵🇾 Paraguay</option>
          <option value="DE">🇩🇪 Deutschland</option>
          <option value="FR">🇫🇷 France</option>
          <option value="GB">🇬🇧 United Kingdom</option>
          <option value="IT">🇮🇹 Italia</option>
          <option value="JP">🇯🇵 日本</option>
        </select>
      </div>
      <div class="wz-field" style="flex:1;">
        <label class="wz-label"><?php echo View::e(I18n::t('wz.idioma_preferido')); ?></label>
        <select class="input" id="cIdioma">
          <option value="pt-BR" <?php echo I18n::idioma() === 'pt-BR' ? 'selected' : ''; ?>>🇧🇷 Português</option>
          <option value="en-US" <?php echo I18n::idioma() === 'en-US' ? 'selected' : ''; ?>>🇺🇸 English</option>
          <option value="es-ES" <?php echo I18n::idioma() === 'es-ES' ? 'selected' : ''; ?>>🇪🇸 Español</option>
        </select>
      </div>
    </div>
    <div class="wz-field">
      <label class="wz-label"><?php echo View::e(I18n::t('wz.senha')); ?> * <span style="font-size:11px;color:#94a3b8;">(<?php echo View::e(I18n::t('wz.senha_min')); ?>)</span></label>
      <input class="input" type="password" id="cSenha" required minlength="8" placeholder="••••••••"/>
    </div>
    <div class="wz-field">
      <label class="wz-label"><?php echo View::e(I18n::t('wz.confirmar_senha')); ?> *</label>
      <input class="input" type="password" id="cSenha2" required minlength="8" placeholder="••••••••"/>
    </div>
  </div>
  <div class="linha" style="gap:8px;margin-top:16px;">
    <button class="botao ghost" onclick="irPasso(1)"><?php echo View::e(I18n::t('wz.voltar')); ?></button>
    <button class="botao" style="flex:1;justify-content:center;" onclick="avancarStep2()"><?php echo View::e(I18n::t('wz.proximo')); ?></button>
  </div>
</div>

<!-- ═══ STEP 3: Pagamento ═══ -->
<div class="wz-panel" id="step3">
  <div class="card">
    <div class="titulo" style="font-size:18px;"><?php echo View::e(I18n::t('wz.forma_pagamento')); ?></div>
    <p class="texto"><?php echo View::e(I18n::t('wz.forma_pagamento_desc')); ?></p>
    <div id="erroPayment" class="erro-msg"></div>
    <div class="linha" style="gap:8px;" id="gwBrl">
      <div class="gw-opt sel" data-gw="PIX" onclick="selGateway('PIX')">💠 PIX</div>
      <div class="gw-opt" data-gw="BOLETO" onclick="selGateway('BOLETO')">📄 Boleto</div>
      <div class="gw-opt" data-gw="CREDIT_CARD" onclick="selGateway('CREDIT_CARD')">💳 Cartão</div>
    </div>
    <div class="linha" style="gap:8px;display:none;" id="gwUsd">
      <div class="gw-opt sel" data-gw="stripe" onclick="selGateway('stripe')">💳 Card (Stripe)</div>
    </div>
    <?php if (!$isBrl): ?>
    <script>document.addEventListener('DOMContentLoaded',function(){selCurrency('USD');});</script>
    <?php endif; ?>
    <!-- Campos cartão de crédito -->
    <div id="ccFields" style="display:none;margin-top:16px;">
      <div class="wz-field">
        <label class="wz-label"><?php echo View::e(I18n::t('wz.nome_cartao')); ?> *</label>
        <input class="input" type="text" id="ccNome" placeholder="Como está no cartão" autocomplete="cc-name"/>
      </div>
      <div class="wz-field">
        <label class="wz-label"><?php echo View::e(I18n::t('wz.numero_cartao')); ?> *</label>
        <input class="input" type="text" id="ccNumero" placeholder="0000 0000 0000 0000" maxlength="19" inputmode="numeric" autocomplete="cc-number"/>
      </div>
      <div class="linha" style="gap:8px;">
        <div class="wz-field" style="flex:1;">
          <label class="wz-label"><?php echo View::e(I18n::t('wz.validade')); ?> *</label>
          <input class="input" type="text" id="ccValidade" placeholder="MM/AA" maxlength="5" inputmode="numeric" autocomplete="cc-exp"/>
        </div>
        <div class="wz-field" style="flex:1;">
          <label class="wz-label">CVV *</label>
          <input class="input" type="text" id="ccCvv" placeholder="000" maxlength="4" inputmode="numeric" autocomplete="cc-csc"/>
        </div>
      </div>
    </div>
  </div>

  <div class="card" style="background:#f8fafc;">
    <div class="subtitulo"><?php echo View::e(I18n::t('wz.resumo_pedido')); ?></div>
    <div id="resumoFinal" style="font-size:14px;line-height:2;"></div>
    <div style="border-top:1px solid #e2e8f0;padding-top:10px;margin-top:8px;display:flex;justify-content:space-between;font-size:20px;font-weight:800;">
      <span><?php echo View::e(I18n::t('wz.total')); ?></span>
      <span id="resumoFinalTotal" style="color:#4F46E5;"></span>
    </div>
  </div>

  <div class="linha" style="gap:8px;margin-top:16px;">
    <button class="botao ghost" onclick="irPasso(2)"><?php echo View::e(I18n::t('wz.voltar')); ?></button>
    <button class="botao sec" style="flex:1;justify-content:center;font-size:15px;padding:14px;" id="btnFinalizar" onclick="finalizar()"><?php echo View::e(I18n::t('wz.finalizar')); ?></button>
  </div>
</div>

</div><!-- /conteudo -->

<?php require __DIR__ . '/../_partials/footer.php'; ?>

<!-- Modal upsell -->
<div class="overlay" id="modalUpsell">
  <div class="modal">
    <div style="text-align:center;">
      <div style="font-size:32px;margin-bottom:8px;">🚀</div>
      <div class="titulo" style="font-size:18px;" id="upsellTitulo"></div>
      <p class="texto" id="upsellDesc"></p>
      <div class="linha" style="gap:8px;justify-content:center;margin-top:16px;">
        <button class="botao ghost" onclick="fecharUpsell()"><?php echo View::e(I18n::t('wz.nao_obrigado')); ?></button>
        <button class="botao" id="upsellAceitar" onclick="aceitarUpsell()">Aceitar oferta</button>
      </div>
    </div>
  </div>
</div>

<script>
// Máscaras visuais
(function(){
  function maskCpfCnpj(el){
    el.addEventListener('input',function(){
      var v=this.value.replace(/\D/g,'');
      if(v.length<=11){
        v=v.replace(/(\d{3})(\d)/,'$1.$2');
        v=v.replace(/(\d{3})(\d)/,'$1.$2');
        v=v.replace(/(\d{3})(\d{1,2})$/,'$1-$2');
      }else{
        v=v.substring(0,14);
        v=v.replace(/^(\d{2})(\d)/,'$1.$2');
        v=v.replace(/^(\d{2})\.(\d{3})(\d)/,'$1.$2.$3');
        v=v.replace(/\.(\d{3})(\d)/,'.$1/$2');
        v=v.replace(/(\d{4})(\d)/,'$1-$2');
      }
      this.value=v;
    });
  }
  function maskPhone(el){
    el.addEventListener('input',function(){
      var v=this.value.replace(/\D/g,'');
      if(v.length<=10){
        v=v.replace(/^(\d{2})(\d)/,'($1) $2');
        v=v.replace(/(\d{4})(\d)/,'$1-$2');
      }else{
        v=v.substring(0,11);
        v=v.replace(/^(\d{2})(\d)/,'($1) $2');
        v=v.replace(/(\d{5})(\d)/,'$1-$2');
      }
      this.value=v;
    });
  }
  function maskCard(el){
    el.addEventListener('input',function(){
      var v=this.value.replace(/\D/g,'').substring(0,16);
      v=v.replace(/(\d{4})(?=\d)/g,'$1 ');
      this.value=v;
    });
  }
  function maskExpiry(el){
    el.addEventListener('input',function(){
      var v=this.value.replace(/\D/g,'').substring(0,4);
      if(v.length>=3) v=v.substring(0,2)+'/'+v.substring(2);
      this.value=v;
    });
  }
  var cpf=document.getElementById('cCpf');if(cpf)maskCpfCnpj(cpf);
  var cel=document.getElementById('cCelular');if(cel)maskPhone(cel);
  var ccn=document.getElementById('ccNumero');if(ccn)maskCard(ccn);
  var ccv=document.getElementById('ccValidade');if(ccv)maskExpiry(ccv);
})();
</script>

<script>
(function(){
  var planId=<?php echo $planId; ?>;
  var precoBase=<?php echo $preco; ?>;
  var d6=<?php echo $d6; ?>;
  var d12=<?php echo $d12; ?>;
  var moeda=<?php echo json_encode($moeda); ?>;
  var csrf=<?php echo json_encode(Csrf::token()); ?>;
  // Multi-pricing: preços fixos por período (se definidos no plano)
  var priceSemiannual=<?php echo (float)($plano['price_semiannual'] ?? 0); ?>;
  var priceAnnual=<?php echo (float)($plano['price_annual'] ?? 0); ?>;
  var priceAnnualUpfront=<?php echo (float)($plano['price_annual_upfront'] ?? 0); ?>;
  var priceMonthlyUsd=<?php echo (float)($plano['price_monthly_usd'] ?? 0); ?>;
  var priceSemiannualUsd=<?php echo (float)($plano['price_semiannual_usd'] ?? 0); ?>;
  var priceAnnualUsd=<?php echo (float)($plano['price_annual_usd'] ?? 0); ?>;
  var priceAnnualUpfrontUsd=<?php echo (float)($plano['price_annual_upfront_usd'] ?? 0); ?>;
  var maxInstAnnual=<?php echo (int)($plano['max_installments_annual'] ?? 12); ?>;
  var maxInstSemiannual=<?php echo (int)($plano['max_installments_semiannual'] ?? 6); ?>;
  var taxaUsd=<?php echo \LRV\Core\ConfiguracoesSistema::taxaConversaoUsd(); ?>;
  var selectedCurrency=moeda;

  function toUsd(brl, fixedUsd){
    if(fixedUsd>0) return fixedUsd;
    return Math.round(brl/taxaUsd*100)/100;
  }
  var upsell=<?php echo $upsell ? json_encode(['id'=>(int)$upsell['id'],'name'=>$upsell['name'],'price'=>(float)$upsell['price_monthly']]) : 'null'; ?>;
  var passo=0,periodo='monthly',gateway=<?php echo json_encode($isBrl?'PIX':'stripe'); ?>,addonsIds=<?php echo json_encode(array_values($preAddons)); ?>;
  var T=<?php echo json_encode([
    'mes'=>I18n::t('assinaturas.mes'),'sem'=>'sem','ano'=>'ano',
    'equiv'=>I18n::t('wz.equiv_mensal'),'cobrado_por'=>I18n::t('wz.cobrado_por'),
    'com_desc'=>I18n::t('wz.com_desconto'),'desconto'=>I18n::t('wz.desconto'),
    'aplicado'=>I18n::t('wz.aplicado'),'addons'=>I18n::t('wz.addons'),
    'finalizar'=>I18n::t('wz.finalizar'),'processando'=>I18n::t('wz.processando'),
    'erro_campos'=>I18n::t('wz.erro_campos'),'erro_senha_min'=>I18n::t('wz.erro_senha_min'),
    'erro_senha_diff'=>I18n::t('wz.erro_senha_diff'),'erro_cartao'=>I18n::t('wz.erro_cartao'),
    'erro_conexao'=>I18n::t('wz.erro_conexao'),'erro_processar'=>I18n::t('wz.erro_processar'),
    'pix_titulo'=>I18n::t('wz.pix_titulo'),'pix_desc'=>I18n::t('wz.pix_desc'),
    'pix_copiar'=>I18n::t('wz.pix_copiar'),'pix_copiado'=>I18n::t('wz.pix_copiado'),
    'boleto_titulo'=>I18n::t('wz.boleto_titulo'),'boleto_desc'=>I18n::t('wz.boleto_desc'),
    'boleto_copiar'=>I18n::t('wz.boleto_copiar'),'boleto_baixar'=>I18n::t('wz.boleto_baixar'),
    'ir_assinaturas'=>I18n::t('wz.ir_assinaturas'),
    'pix_aguardando'=>I18n::t('wz.pix_aguardando'),
    'pix_confirmado'=>I18n::t('wz.pix_confirmado'),
    'upsell_titulo'=>I18n::t('wz.upsell_desconto'),'upsell_desc_tpl'=>I18n::t('wz.upsell_desc'),
    'quero_desconto'=>I18n::t('wz.quero_desconto'),'nao_obrigado'=>I18n::t('wz.nao_obrigado'),
  ]); ?>;

  function fmt(v){
    if(selectedCurrency==='BRL') return 'R$ '+v.toFixed(2).replace('.',',');
    return 'US$ '+v.toFixed(2);
  }
  function getMonthlyPrice(){
    return selectedCurrency==='USD'?toUsd(precoBase,priceMonthlyUsd):precoBase;
  }
  function getUpfrontPrice(){
    return selectedCurrency==='USD'?toUsd(priceAnnualUpfront,priceAnnualUpfrontUsd):priceAnnualUpfront;
  }
  function getAddonMonthly(el){
    var p=parseFloat(el.dataset.addonPrice)||0;
    var pu=parseFloat(el.dataset.addonPriceUsd)||0;
    return selectedCurrency==='USD'?toUsd(p,pu):p;
  }
  function getAddonAnnualTotal(el){
    var p=parseFloat(el.dataset.addonPrice)||0;
    var pa=parseFloat(el.dataset.addonPriceAnnual)||0;
    var pau=parseFloat(el.dataset.addonPriceAnnualUsd)||0;
    if(selectedCurrency==='USD') return toUsd(pa>0?pa:p,pau)*12;
    return (pa>0?pa:p)*12;
  }

  window.selCurrency=function(cur){
    selectedCurrency=cur;
    document.querySelectorAll('.currency-opt').forEach(function(el){el.classList.toggle('sel',el.dataset.cur===cur);});
    var cpfField=document.getElementById('cpfField');
    if(cpfField) cpfField.style.display=cur==='USD'?'none':'block';
    var gwBrl=document.getElementById('gwBrl');
    var gwUsd=document.getElementById('gwUsd');
    if(gwBrl) gwBrl.style.display=cur==='BRL'?'flex':'none';
    if(gwUsd) gwUsd.style.display=cur==='USD'?'flex':'none';
    if(cur==='USD') gateway='stripe'; else gateway='PIX';
    atualizarResumo();
  };

  window.selPeriodo=function(p){
    periodo=p;
    document.querySelectorAll('.periodo-opt').forEach(function(el){el.classList.toggle('sel',el.dataset.periodo===p);});
    atualizarResumo();
  };
  window.alterarQtd=function(d){
    var el=document.getElementById('qtdServidores');
    el.value=Math.max(1,Math.min(20,parseInt(el.value||'1')+d));
    atualizarResumo();
  };
  window.toggleAddon=function(el){
    var cb=el.querySelector('input[type=checkbox]');
    cb.checked=!cb.checked;el.classList.toggle('sel',cb.checked);
    var id=parseInt(el.dataset.addonId);
    if(cb.checked){if(addonsIds.indexOf(id)===-1)addonsIds.push(id);}
    else{addonsIds=addonsIds.filter(function(x){return x!==id;});}
    atualizarResumo();
  };
  window.selGateway=function(gw){
    gateway=gw;
    document.querySelectorAll('.gw-opt').forEach(function(el){el.classList.toggle('sel',el.dataset.gw===gw);});
    var ccf=document.getElementById('ccFields');
    if(ccf) ccf.style.display=(gw==='CREDIT_CARD')?'block':'none';
  };

  window.atualizarResumo=function(){
    var qtd=Math.max(1,parseInt(document.getElementById('qtdServidores').value||'1'));
    var isAnnual=periodo==='annual';
    var monthly=getMonthlyPrice();
    var upfront=getUpfrontPrice();
    var planTotal=isAnnual?upfront*qtd:monthly*qtd;

    // Update period labels
    var pm=document.getElementById('precoMensalWz');if(pm)pm.textContent=fmt(monthly)+'/mês';
    var cm=document.getElementById('cobradoMensalWz');if(cm)cm.textContent='Cobrado '+fmt(monthly);
    var pa=document.getElementById('precoAnualWz');if(pa)pa.textContent=fmt(upfront)+'/ano';
    var ec=document.getElementById('economiaWz');
    if(ec&&upfront>0){var economia=monthly*12-upfront;ec.textContent=economia>0?'Economia de '+fmt(economia):'';}

    // Addons
    var addonsTotal=0;
    document.querySelectorAll('.addon-opt').forEach(function(el){
      if(el.classList.contains('sel')){
        var addonVal=isAnnual?getAddonAnnualTotal(el):getAddonMonthly(el);
        addonsTotal+=addonVal*qtd;
      }
    });

    var totalGeral=planTotal+addonsTotal;
    var perSuffix=isAnnual?'/'+T.ano:'/'+T.mes;
    var perLabel=isAnnual?T.ano:T.mes;

    var html='<div style="display:flex;justify-content:space-between;"><span>'+qtd+'x <?php echo View::e((string)($plano['name'] ?? '')); ?></span><span>'+fmt(planTotal)+perSuffix+'</span></div>';
    if(addonsTotal>0){
      html+='<div style="display:flex;justify-content:space-between;color:#475569;"><span>'+T.addons+'</span><span>+'+fmt(addonsTotal)+perSuffix+'</span></div>';
    }
    if(isAnnual){
      html+='<div style="display:flex;justify-content:space-between;font-size:12px;color:#64748b;margin-top:4px;"><span>Pagamento único anual</span></div>';
    }else{
      html+='<div style="display:flex;justify-content:space-between;font-size:12px;color:#64748b;margin-top:4px;"><span>Cobrado mensalmente</span></div>';
    }

    document.getElementById('resumoConfig').innerHTML=html;
    document.getElementById('resumoTotal').textContent=fmt(totalGeral);
    document.getElementById('resumoFinal').innerHTML=html;
    document.getElementById('resumoFinalTotal').textContent=fmt(totalGeral);
  };
  atualizarResumo();

  window.irPasso=function(p){
    passo=p;
    for(var i=0;i<4;i++){var panel=document.getElementById('step'+i);if(panel)panel.classList.toggle('active',i===p);}
    var steps=document.querySelectorAll('.wz-step'),lines=document.querySelectorAll('.wz-line');
    steps.forEach(function(s,idx){s.classList.remove('active','done');if(idx<p)s.classList.add('done');else if(idx===p)s.classList.add('active');});
    lines.forEach(function(l,idx){l.classList.toggle('done',idx<p);});
    window.scrollTo(0,0);
  };

  var upsellShown=false;
  window.avancarStep1=function(){
    if(!upsellShown&&periodo==='monthly'&&priceAnnualUpfront>0){
      var upfront=getUpfrontPrice();
      var monthly=getMonthlyPrice();
      document.getElementById('upsellTitulo').textContent='Economize com o plano anual';
      document.getElementById('upsellDesc').textContent='Pague '+fmt(upfront)+' à vista em vez de '+fmt(monthly*12)+' (12x '+fmt(monthly)+')';
      document.getElementById('upsellAceitar').textContent=T.quero_desconto;
      document.getElementById('upsellAceitar').onclick=function(){selPeriodo('annual');fecharUpsell();irPasso(2);};
      document.getElementById('modalUpsell').classList.add('show');
      upsellShown=true;return;
    }
    irPasso(2);
  };
  window.fecharUpsell=function(){document.getElementById('modalUpsell').classList.remove('show');irPasso(2);};
  window.aceitarUpsell=function(){};

  window.avancarStep2=function(){
    var nome=document.getElementById('cNome').value.trim(),email=document.getElementById('cEmail').value.trim(),
        cpf=document.getElementById('cCpf').value.trim(),senha=document.getElementById('cSenha').value,
        senha2=document.getElementById('cSenha2').value,errEl=document.getElementById('erroConta');
    if(!nome||!email||!senha){errEl.textContent=T.erro_campos;errEl.style.display='block';return;}
    if(selectedCurrency==='BRL'&&!cpf){errEl.textContent=T.erro_campos+' CPF/CNPJ é obrigatório para pagamento em BRL.';errEl.style.display='block';return;}
    if(senha.length<8){errEl.textContent=T.erro_senha_min;errEl.style.display='block';return;}
    if(senha!==senha2){errEl.textContent=T.erro_senha_diff;errEl.style.display='block';return;}
    errEl.style.display='none';atualizarResumo();irPasso(3);
  };

  window.finalizar=function(){
    var btn=document.getElementById('btnFinalizar');
    var errEl=document.getElementById('erroPayment');errEl.style.display='none';

    // Validar cartão se selecionado
    if(gateway==='CREDIT_CARD'){
      var ccNome=document.getElementById('ccNome').value.trim();
      var ccNum=document.getElementById('ccNumero').value.replace(/\s/g,'');
      var ccVal=document.getElementById('ccValidade').value.trim();
      var ccCvv=document.getElementById('ccCvv').value.trim();
      if(!ccNome||ccNum.length<13||!ccVal||ccCvv.length<3){
        errEl.textContent=T.erro_cartao;errEl.style.display='block';return;
      }
    }

    btn.disabled=true;btn.textContent=T.processando;
    var body=new FormData();
    body.append('_csrf',csrf);body.append('plan_id',planId);
    body.append('nome',document.getElementById('cNome').value.trim());
    body.append('email',document.getElementById('cEmail').value.trim());
    body.append('cpf_cnpj',document.getElementById('cCpf').value.trim());
    body.append('ddi',document.getElementById('cDdi').value);
    body.append('celular',document.getElementById('cCelular').value.trim());
    body.append('senha',document.getElementById('cSenha').value);
    body.append('country',document.getElementById('cPais').value);
    body.append('preferred_lang',document.getElementById('cIdioma').value);
    body.append('gateway',gateway);body.append('periodo',periodo==='annual'?12:1);
    body.append('quantidade',document.getElementById('qtdServidores').value);
    body.append('addons_ids',addonsIds.join(','));
    body.append('currency',selectedCurrency);

    if(gateway==='CREDIT_CARD'){
      body.append('cc_nome',document.getElementById('ccNome').value.trim());
      body.append('cc_numero',document.getElementById('ccNumero').value.replace(/\s/g,''));
      body.append('cc_validade',document.getElementById('ccValidade').value.trim());
      body.append('cc_cvv',document.getElementById('ccCvv').value.trim());
    }

    fetch('/contratar/finalizar',{method:'POST',body:body,credentials:'same-origin'})
      .then(function(r){return r.json();})
      .then(function(data){
        if(data.ok&&data.payment_type==='pix'){
          // Mostrar PIX inline
          var html='<div class="card" style="text-align:center;padding:28px;">';
          html+='<div style="font-size:32px;margin-bottom:8px;">✅</div>';
          html+='<div class="titulo" style="font-size:18px;">'+T.pix_titulo+'</div>';
          html+='<p class="texto">'+T.pix_desc+'</p>';
          if(data.pix_image) html+='<img src="data:image/png;base64,'+data.pix_image+'" style="max-width:220px;margin:16px auto;display:block;border-radius:12px;"/>';
          if(data.pix_payload){
            html+='<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:12px;margin:12px 0;word-break:break-all;font-size:12px;font-family:monospace;">'+data.pix_payload+'</div>';
            html+='<button class="botao ghost sm" id="btnCopiarPix" onclick="navigator.clipboard.writeText(\''+data.pix_payload.replace(/'/g,"\\'")+'\');this.textContent=\''+T.pix_copiado+'\'">'+T.pix_copiar+'</button>';
          }
          html+='<div id="pixStatusMsg" style="margin-top:16px;font-size:13px;color:#64748b;"><span class="spinner-sm"></span> '+T.pix_aguardando+'</div>';
          html+='<div style="margin-top:16px;"><a href="'+data.redirect+'" class="botao">'+T.ir_assinaturas+'</a></div>';
          html+='</div>';
          document.getElementById('step3').innerHTML=html;
          // Polling para detectar pagamento confirmado
          if(data.sub_id){
            var pixPollId=setInterval(function(){
              fetch('/cliente/pagamento/status?sub='+data.sub_id,{credentials:'same-origin'})
                .then(function(r){return r.json();})
                .then(function(st){
                  if(st.ok&&st.pago){
                    clearInterval(pixPollId);
                    var msg=document.getElementById('pixStatusMsg');
                    if(msg) msg.innerHTML='<div style="color:#16a34a;font-weight:600;font-size:15px;">'+T.pix_confirmado+'</div>';
                    setTimeout(function(){window.location.href='/cliente/assinaturas';},2000);
                  }
                }).catch(function(){});
            },5000);
          }
        } else if(data.ok&&data.payment_type==='boleto'){
          // Mostrar Boleto inline
          var html='<div class="card" style="text-align:center;padding:28px;">';
          html+='<div style="font-size:32px;margin-bottom:8px;">✅</div>';
          html+='<div class="titulo" style="font-size:18px;">'+T.boleto_titulo+'</div>';
          html+='<p class="texto">'+T.boleto_desc+'</p>';
          if(data.boleto_linha){
            html+='<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:12px;margin:12px 0;word-break:break-all;font-size:13px;font-family:monospace;">'+data.boleto_linha+'</div>';
            html+='<button class="botao ghost sm" onclick="navigator.clipboard.writeText(\''+data.boleto_linha.replace(/'/g,"\\'")+'\');this.textContent=\''+T.pix_copiado+'\'">'+T.boleto_copiar+'</button>';
          }
          if(data.boleto_url) html+=' <a href="'+data.boleto_url+'" target="_blank" class="botao sm" style="margin-left:8px;">'+T.boleto_baixar+'</a>';
          html+='<div style="margin-top:16px;"><a href="'+data.redirect+'" class="botao">'+T.ir_assinaturas+'</a></div>';
          html+='</div>';
          document.getElementById('step3').innerHTML=html;
        } else if(data.ok&&data.redirect){
          window.location.href=data.redirect;
        } else{
          errEl.textContent=data.erro||T.erro_processar;errEl.style.display='block';btn.disabled=false;btn.textContent=T.finalizar;
        }
      })
      .catch(function(){errEl.textContent=T.erro_conexao;errEl.style.display='block';btn.disabled=false;btn.textContent=T.finalizar;});
  };
})();
</script>
</body>
</html>
