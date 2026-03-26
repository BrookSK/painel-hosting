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
    @media(max-width:600px){.wz-step span{display:none}.wz-line{width:24px}}
  </style>
</head>
<body>
<?php require __DIR__ . '/../_partials/navbar-publica.php'; ?>

<div class="wz-steps" id="stepsBar">
  <div class="wz-step active" data-s="0"><div class="wz-step-dot">1</div><span>Plano</span></div>
  <div class="wz-line"></div>
  <div class="wz-step" data-s="1"><div class="wz-step-dot">2</div><span>Configuração</span></div>
  <div class="wz-line"></div>
  <div class="wz-step" data-s="2"><div class="wz-step-dot">3</div><span>Dados pessoais</span></div>
  <div class="wz-line"></div>
  <div class="wz-step" data-s="3"><div class="wz-step-dot">4</div><span>Pagamento</span></div>
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
      <li>Servidor VPS dedicado com recursos garantidos</li>
      <li>Painel de controle completo</li>
      <li>Backups automáticos</li>
      <li>Monitoramento 24/7</li>
      <li>Suporte técnico incluso</li>
    </ul>
    <button class="botao" style="width:100%;justify-content:center;font-size:15px;padding:14px;" onclick="irPasso(1)">Continuar com este plano</button>
  </div>

  <?php if ($upsell): ?>
  <div class="card upsell-card" style="margin-top:8px;">
    <div class="upsell-badge">⭐ Recomendado</div>
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
        <a href="/contratar?plan_id=<?php echo (int)($upsell['id'] ?? 0); ?>" class="botao sm" style="margin-top:8px;">Escolher este</a>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>

<!-- ═══ STEP 1: Configuração ═══ -->
<div class="wz-panel" id="step1">
  <div class="card">
    <div class="titulo" style="font-size:18px;">Configuração da VPS</div>
    <p class="texto">Seu plano <strong><?php echo View::e((string)($plano['name'] ?? '')); ?></strong> inclui:</p>
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
    <div class="subtitulo">Quantidade de servidores</div>
    <p class="texto" style="font-size:13px;">Cada servidor é uma VPS independente com os recursos do plano.</p>
    <div class="linha" style="gap:12px;margin-top:8px;">
      <button class="botao ghost sm" onclick="alterarQtd(-1)" style="width:40px;padding:8px 0;justify-content:center;font-size:18px;">−</button>
      <input type="number" id="qtdServidores" value="1" min="1" max="20" class="input" style="width:70px;text-align:center;font-size:18px;font-weight:700;" onchange="atualizarResumo()"/>
      <button class="botao ghost sm" onclick="alterarQtd(1)" style="width:40px;padding:8px 0;justify-content:center;font-size:18px;">+</button>
    </div>
  </div>

  <div class="card">
    <div class="subtitulo">Período de contratação</div>
    <div style="display:flex;flex-direction:column;gap:8px;margin-top:10px;">
      <div class="periodo-opt sel" data-periodo="1" onclick="selPeriodo(1)">
        <div><strong>Mensal</strong><br><span style="font-size:12px;color:#64748b;">Sem compromisso</span></div>
        <div style="text-align:right;">
          <div style="font-weight:700;"><?php echo View::e(I18n::preco($preco)); ?>/mês</div>
          <div style="font-size:11px;color:#94a3b8;">Cobrado <?php echo View::e(I18n::preco($preco)); ?></div>
        </div>
      </div>
      <div class="periodo-opt" data-periodo="6" onclick="selPeriodo(6)">
        <div><strong>Semestral</strong> <span class="desc-badge"><?php echo $d6; ?>% OFF</span><br><span style="font-size:12px;color:#64748b;">Cobrado a cada 6 meses</span></div>
        <div style="text-align:right;">
          <div style="font-weight:700;" id="preco6m"></div>
          <div style="font-size:11px;color:#94a3b8;" id="cobrado6m"></div>
        </div>
      </div>
      <div class="periodo-opt" data-periodo="12" onclick="selPeriodo(12)">
        <div><strong>Anual</strong> <span class="desc-badge"><?php echo $d12; ?>% OFF</span><br><span style="font-size:12px;color:#64748b;">Cobrado anualmente</span></div>
        <div style="text-align:right;">
          <div style="font-weight:700;" id="preco12m"></div>
          <div style="font-size:11px;color:#94a3b8;" id="cobrado12m"></div>
        </div>
      </div>
    </div>
  </div>

  <?php if (!empty($addons)): ?>
  <div class="card">
    <div class="subtitulo">Serviços adicionais</div>
    <p class="texto" style="font-size:13px;">Opcionais para complementar sua VPS.</p>
    <div style="display:flex;flex-direction:column;gap:8px;margin-top:8px;">
      <?php
        $preAddons = is_array($pre_addons ?? null) ? $pre_addons : [];
      ?>
      <?php foreach ($addons as $a):
        $aId = (int)($a['id'] ?? 0);
        $aPreSel = in_array($aId, $preAddons, true);
      ?>
      <div class="addon-opt<?php echo $aPreSel ? ' sel' : ''; ?>" data-addon-id="<?php echo $aId; ?>" data-addon-price="<?php echo (float)($a['price'] ?? 0); ?>" onclick="toggleAddon(this)">
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
    <div class="subtitulo">Resumo</div>
    <div id="resumoConfig" style="font-size:14px;line-height:2;"></div>
    <div style="border-top:1px solid #e2e8f0;padding-top:10px;margin-top:8px;display:flex;justify-content:space-between;font-size:20px;font-weight:800;">
      <span>Total</span>
      <span id="resumoTotal" style="color:#4F46E5;"></span>
    </div>
  </div>

  <div class="linha" style="gap:8px;margin-top:16px;">
    <button class="botao ghost" onclick="irPasso(0)">← Voltar</button>
    <button class="botao" style="flex:1;justify-content:center;" onclick="avancarStep1()">Próximo</button>
  </div>
</div>

<!-- ═══ STEP 2: Dados pessoais ═══ -->
<div class="wz-panel" id="step2">
  <div class="card">
    <div class="titulo" style="font-size:18px;">Crie sua conta</div>
    <p class="texto">Preencha seus dados para criar sua conta e acessar o painel.</p>
    <div id="erroConta" class="erro-msg"></div>
    <div class="wz-field">
      <label class="wz-label">Nome completo *</label>
      <input class="input" type="text" id="cNome" required placeholder="Seu nome"/>
    </div>
    <div class="wz-field">
      <label class="wz-label">E-mail *</label>
      <input class="input" type="email" id="cEmail" required placeholder="seu@email.com"/>
    </div>
    <div class="wz-field">
      <label class="wz-label">CPF ou CNPJ *</label>
      <input class="input" type="text" id="cCpf" required placeholder="000.000.000-00" maxlength="18" inputmode="numeric"/>
    </div>
    <div class="wz-field">
      <label class="wz-label">Celular</label>
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
    <div class="wz-field">
      <label class="wz-label">Senha * <span style="font-size:11px;color:#94a3b8;">(mínimo 8 caracteres)</span></label>
      <input class="input" type="password" id="cSenha" required minlength="8" placeholder="••••••••"/>
    </div>
    <div class="wz-field">
      <label class="wz-label">Confirmar senha *</label>
      <input class="input" type="password" id="cSenha2" required minlength="8" placeholder="••••••••"/>
    </div>
  </div>
  <div class="linha" style="gap:8px;margin-top:16px;">
    <button class="botao ghost" onclick="irPasso(1)">← Voltar</button>
    <button class="botao" style="flex:1;justify-content:center;" onclick="avancarStep2()">Próximo</button>
  </div>
</div>

<!-- ═══ STEP 3: Pagamento ═══ -->
<div class="wz-panel" id="step3">
  <div class="card">
    <div class="titulo" style="font-size:18px;">Forma de pagamento</div>
    <p class="texto">Escolha como deseja pagar sua assinatura.</p>
    <div id="erroPayment" class="erro-msg"></div>
    <?php if ($isBrl): ?>
    <div class="linha" style="gap:8px;">
      <div class="gw-opt sel" data-gw="PIX" onclick="selGateway('PIX')">💠 PIX</div>
      <div class="gw-opt" data-gw="BOLETO" onclick="selGateway('BOLETO')">📄 Boleto</div>
      <div class="gw-opt" data-gw="CREDIT_CARD" onclick="selGateway('CREDIT_CARD')">💳 Cartão</div>
    </div>
    <!-- Campos cartão de crédito -->
    <div id="ccFields" style="display:none;margin-top:16px;">
      <div class="wz-field">
        <label class="wz-label">Nome no cartão *</label>
        <input class="input" type="text" id="ccNome" placeholder="Como está no cartão" autocomplete="cc-name"/>
      </div>
      <div class="wz-field">
        <label class="wz-label">Número do cartão *</label>
        <input class="input" type="text" id="ccNumero" placeholder="0000 0000 0000 0000" maxlength="19" inputmode="numeric" autocomplete="cc-number"/>
      </div>
      <div class="linha" style="gap:8px;">
        <div class="wz-field" style="flex:1;">
          <label class="wz-label">Validade *</label>
          <input class="input" type="text" id="ccValidade" placeholder="MM/AA" maxlength="5" inputmode="numeric" autocomplete="cc-exp"/>
        </div>
        <div class="wz-field" style="flex:1;">
          <label class="wz-label">CVV *</label>
          <input class="input" type="text" id="ccCvv" placeholder="000" maxlength="4" inputmode="numeric" autocomplete="cc-csc"/>
        </div>
      </div>
    </div>
    <?php else: ?>
    <div class="linha" style="gap:8px;">
      <div class="gw-opt sel" data-gw="stripe" onclick="selGateway('stripe')">💳 Card (Stripe)</div>
    </div>
    <?php endif; ?>
  </div>

  <div class="card" style="background:#f8fafc;">
    <div class="subtitulo">Resumo do pedido</div>
    <div id="resumoFinal" style="font-size:14px;line-height:2;"></div>
    <div style="border-top:1px solid #e2e8f0;padding-top:10px;margin-top:8px;display:flex;justify-content:space-between;font-size:20px;font-weight:800;">
      <span>Total</span>
      <span id="resumoFinalTotal" style="color:#4F46E5;"></span>
    </div>
  </div>

  <div class="linha" style="gap:8px;margin-top:16px;">
    <button class="botao ghost" onclick="irPasso(2)">← Voltar</button>
    <button class="botao sec" style="flex:1;justify-content:center;font-size:15px;padding:14px;" id="btnFinalizar" onclick="finalizar()">Finalizar contratação</button>
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
        <button class="botao ghost" onclick="fecharUpsell()">Não, obrigado</button>
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
  var upsell=<?php echo $upsell ? json_encode(['id'=>(int)$upsell['id'],'name'=>$upsell['name'],'price'=>(float)$upsell['price_monthly']]) : 'null'; ?>;
  var passo=0,periodo=1,gateway=<?php echo json_encode($isBrl?'PIX':'stripe'); ?>,addonsIds=<?php echo json_encode(array_values($preAddons)); ?>;

  function fmt(v){
    if(moeda==='BRL') return 'R$ '+v.toFixed(2).replace('.',',');
    return '$ '+v.toFixed(2);
  }
  function precoComDesconto(base,per){
    if(per===6) return base*(1-d6/100);
    if(per===12) return base*(1-d12/100);
    return base;
  }

  document.getElementById('preco6m').textContent=fmt(precoComDesconto(precoBase,6))+'/mês';
  document.getElementById('cobrado6m').textContent='Cobrado '+fmt(precoComDesconto(precoBase,6)*6);
  document.getElementById('preco12m').textContent=fmt(precoComDesconto(precoBase,12))+'/mês';
  document.getElementById('cobrado12m').textContent='Cobrado '+fmt(precoComDesconto(precoBase,12)*12);

  window.selPeriodo=function(p){
    periodo=p;
    document.querySelectorAll('.periodo-opt').forEach(function(el){el.classList.toggle('sel',parseInt(el.dataset.periodo)===p);});
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
    var precoUnit=precoComDesconto(precoBase,periodo);
    var addonsTotal=0;
    document.querySelectorAll('.addon-opt').forEach(function(el){if(el.classList.contains('sel'))addonsTotal+=parseFloat(el.dataset.addonPrice)||0;});
    // Addons também recebem desconto do período
    var addonsMes=addonsTotal*(periodo>1?(periodo===6?(1-d6/100):(1-d12/100)):1);
    var totalMes=(precoUnit+addonsMes)*qtd;
    var totalPeriodo=totalMes*periodo;
    var perSuffix=periodo===1?'/mês':periodo===6?'/sem':'/ano';
    var perLabel=periodo===1?'mês':periodo===6?'semestre':'ano';

    var planoPeriodo=precoUnit*qtd*periodo;
    var addonsPeriodo=addonsMes*qtd*periodo;

    var html='<div style="display:flex;justify-content:space-between;"><span>'+qtd+'x <?php echo View::e((string)($plano['name'] ?? '')); ?></span><span>'+fmt(planoPeriodo)+perSuffix+'</span></div>';
    if(addonsTotal>0){
      html+='<div style="display:flex;justify-content:space-between;color:#475569;"><span>Addons'+( periodo>1?' (c/ desconto)':'' )+'</span><span>+'+fmt(addonsPeriodo)+perSuffix+'</span></div>';
    }
    if(periodo>1){
      html+='<div style="display:flex;justify-content:space-between;color:#16a34a;"><span>Desconto '+(periodo===6?d6:d12)+'%</span><span>aplicado</span></div>';
    }
    html+='<div style="border-top:1px solid #e2e8f0;padding-top:6px;margin-top:6px;display:flex;justify-content:space-between;font-size:13px;color:#64748b;"><span><b>Equivalente mensal</b></span><span>'+fmt(totalMes)+'/mês</span></div>';
    html+='<div style="display:flex;justify-content:space-between;font-size:12px;color:#94a3b8;margin-top:2px;"><span>Cobrado por '+perLabel+'</span><span>'+fmt(totalPeriodo)+'</span></div>';

    document.getElementById('resumoConfig').innerHTML=html;
    document.getElementById('resumoTotal').textContent=fmt(totalPeriodo);
    document.getElementById('resumoFinal').innerHTML=html;
    document.getElementById('resumoFinalTotal').textContent=fmt(totalPeriodo);
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
    if(!upsellShown&&periodo===1){
      document.getElementById('upsellTitulo').textContent='Economize '+d12+'% no plano anual!';
      document.getElementById('upsellDesc').textContent='Contratando o período anual, você paga '+fmt(precoComDesconto(precoBase,12))+'/mês ao invés de '+fmt(precoBase)+'/mês.';
      document.getElementById('upsellAceitar').textContent='Quero o desconto anual';
      document.getElementById('upsellAceitar').onclick=function(){selPeriodo(12);fecharUpsell();irPasso(2);};
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
    if(!nome||!email||!cpf||!senha){errEl.textContent='Preencha todos os campos obrigatórios.';errEl.style.display='block';return;}
    if(senha.length<8){errEl.textContent='Senha mínima: 8 caracteres.';errEl.style.display='block';return;}
    if(senha!==senha2){errEl.textContent='As senhas não coincidem.';errEl.style.display='block';return;}
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
        errEl.textContent='Preencha todos os dados do cartão de crédito.';errEl.style.display='block';return;
      }
    }

    btn.disabled=true;btn.textContent='Processando...';
    var body=new FormData();
    body.append('_csrf',csrf);body.append('plan_id',planId);
    body.append('nome',document.getElementById('cNome').value.trim());
    body.append('email',document.getElementById('cEmail').value.trim());
    body.append('cpf_cnpj',document.getElementById('cCpf').value.trim());
    body.append('ddi',document.getElementById('cDdi').value);
    body.append('celular',document.getElementById('cCelular').value.trim());
    body.append('senha',document.getElementById('cSenha').value);
    body.append('gateway',gateway);body.append('periodo',periodo);
    body.append('quantidade',document.getElementById('qtdServidores').value);
    body.append('addons_ids',addonsIds.join(','));

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
          html+='<div class="titulo" style="font-size:18px;">Conta criada! Pague via PIX</div>';
          html+='<p class="texto">Escaneie o QR Code ou copie o código abaixo.</p>';
          if(data.pix_image) html+='<img src="data:image/png;base64,'+data.pix_image+'" style="max-width:220px;margin:16px auto;display:block;border-radius:12px;"/>';
          if(data.pix_payload){
            html+='<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:12px;margin:12px 0;word-break:break-all;font-size:12px;font-family:monospace;">'+data.pix_payload+'</div>';
            html+='<button class="botao ghost sm" onclick="navigator.clipboard.writeText(\''+data.pix_payload.replace(/'/g,"\\'")+'\');this.textContent=\'Copiado!\'">Copiar código PIX</button>';
          }
          html+='<div style="margin-top:16px;"><a href="'+data.redirect+'" class="botao">Ir para minhas assinaturas</a></div>';
          html+='</div>';
          document.getElementById('step3').innerHTML=html;
        } else if(data.ok&&data.payment_type==='boleto'){
          // Mostrar Boleto inline
          var html='<div class="card" style="text-align:center;padding:28px;">';
          html+='<div style="font-size:32px;margin-bottom:8px;">✅</div>';
          html+='<div class="titulo" style="font-size:18px;">Conta criada! Pague o boleto</div>';
          html+='<p class="texto">Copie a linha digitável ou baixe o boleto.</p>';
          if(data.boleto_linha){
            html+='<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:12px;margin:12px 0;word-break:break-all;font-size:13px;font-family:monospace;">'+data.boleto_linha+'</div>';
            html+='<button class="botao ghost sm" onclick="navigator.clipboard.writeText(\''+data.boleto_linha.replace(/'/g,"\\'")+'\');this.textContent=\'Copiado!\'">Copiar linha digitável</button>';
          }
          if(data.boleto_url) html+=' <a href="'+data.boleto_url+'" target="_blank" class="botao sm" style="margin-left:8px;">Baixar boleto</a>';
          html+='<div style="margin-top:16px;"><a href="'+data.redirect+'" class="botao">Ir para minhas assinaturas</a></div>';
          html+='</div>';
          document.getElementById('step3').innerHTML=html;
        } else if(data.ok&&data.redirect){
          window.location.href=data.redirect;
        } else{
          errEl.textContent=data.erro||'Erro ao processar. Tente novamente.';errEl.style.display='block';btn.disabled=false;btn.textContent='Finalizar contratação';
        }
      })
      .catch(function(){errEl.textContent='Erro de conexão. Tente novamente.';errEl.style.display='block';btn.disabled=false;btn.textContent='Finalizar contratação';});
  };
})();
</script>
</body>
</html>
