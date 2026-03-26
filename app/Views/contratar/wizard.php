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
$isBrl   = I18n::idioma() === 'pt-BR';
$moeda   = $isBrl ? 'BRL' : 'USD';
?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title><?php echo View::e((string)($plano['name'] ?? '')); ?> — <?php echo View::e(SistemaConfig::nome()); ?></title>
  <style>
    *{margin:0;padding:0;box-sizing:border-box}
    body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:#f8fafc;color:#1e293b;min-height:100vh}
    .wz-header{background:#fff;border-bottom:1px solid #e2e8f0;padding:16px 24px;display:flex;align-items:center;justify-content:space-between}
    .wz-logo{font-weight:700;font-size:18px;color:#4F46E5}
    .wz-steps{display:flex;gap:0;align-items:center;justify-content:center;padding:24px 16px 0}
    .wz-step{display:flex;align-items:center;gap:8px;font-size:13px;color:#94a3b8}
    .wz-step.active{color:#4F46E5;font-weight:600}
    .wz-step.done{color:#16a34a}
    .wz-step-dot{width:28px;height:28px;border-radius:50%;border:2px solid #e2e8f0;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;background:#fff}
    .wz-step.active .wz-step-dot{border-color:#4F46E5;background:#4F46E5;color:#fff}
    .wz-step.done .wz-step-dot{border-color:#16a34a;background:#16a34a;color:#fff}
    .wz-line{width:40px;height:2px;background:#e2e8f0;margin:0 4px}
    .wz-line.done{background:#16a34a}
    .wz-body{max-width:900px;margin:0 auto;padding:24px 16px 60px}
    .wz-panel{display:none}
    .wz-panel.active{display:block}
    .card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;margin-bottom:16px}
    .card-title{font-size:16px;font-weight:700;margin-bottom:8px}
    .badge{display:inline-block;padding:3px 10px;border-radius:6px;font-size:12px;font-weight:600;background:#f1f5f9;color:#475569}
    .btn{display:inline-flex;align-items:center;justify-content:center;padding:12px 24px;border-radius:10px;font-size:14px;font-weight:600;border:none;cursor:pointer;transition:all .15s}
    .btn-primary{background:#4F46E5;color:#fff}.btn-primary:hover{background:#4338CA}
    .btn-ghost{background:transparent;color:#475569;border:1px solid #e2e8f0}.btn-ghost:hover{background:#f8fafc}
    .btn-success{background:#16a34a;color:#fff}.btn-success:hover{background:#15803d}
    .input{width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:14px;outline:none;transition:border .15s}
    .input:focus{border-color:#4F46E5}
    .label{display:block;font-size:13px;font-weight:500;margin-bottom:5px;color:#334155}
    .field{margin-bottom:14px}
    .upsell-card{border:2px solid #4F46E5;background:#f5f3ff;position:relative}
    .upsell-badge{position:absolute;top:-10px;left:50%;transform:translateX(-50%);background:#4F46E5;color:#fff;font-size:11px;font-weight:700;padding:2px 14px;border-radius:99px;white-space:nowrap}
    .overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:100;align-items:center;justify-content:center}
    .overlay.show{display:flex}
    .modal{background:#fff;border-radius:14px;padding:24px;max-width:460px;width:90%;position:relative}
    .periodo-opt{padding:12px 16px;border:1.5px solid #e2e8f0;border-radius:10px;cursor:pointer;transition:all .15s;display:flex;justify-content:space-between;align-items:center}
    .periodo-opt.sel{border-color:#4F46E5;background:#f5f3ff}
    .periodo-opt:hover{border-color:#4F46E5}
    .desc-badge{background:#dcfce7;color:#166534;font-size:11px;font-weight:700;padding:2px 8px;border-radius:6px}
    .addon-opt{padding:12px 14px;border:1.5px solid #e2e8f0;border-radius:10px;cursor:pointer;display:flex;align-items:center;gap:12px;transition:all .15s}
    .addon-opt.sel{border-color:#4F46E5;background:#f5f3ff}
    .gw-opt{padding:12px 16px;border:1.5px solid #e2e8f0;border-radius:10px;cursor:pointer;text-align:center;font-size:13px;font-weight:600;transition:all .15s;flex:1}
    .gw-opt.sel{border-color:#4F46E5;background:#f5f3ff}
    .gw-opt:hover{border-color:#4F46E5}
    .erro-msg{background:#fee2e2;color:#991b1b;padding:10px 14px;border-radius:10px;font-size:13px;margin-bottom:12px;display:none}
    @media(max-width:600px){.wz-step span{display:none}.wz-line{width:20px}}
  </style>
</head>
<body>
<div class="wz-header">
  <div class="wz-logo"><?php echo View::e(SistemaConfig::nome()); ?></div>
  <a href="/" style="font-size:13px;color:#64748b;text-decoration:none;">← Voltar ao site</a>
</div>

<!-- Steps indicator -->
<div class="wz-steps" id="stepsBar">
  <div class="wz-step active" data-s="0"><div class="wz-step-dot">1</div><span>Plano</span></div>
  <div class="wz-line"></div>
  <div class="wz-step" data-s="1"><div class="wz-step-dot">2</div><span>Configuração</span></div>
  <div class="wz-line"></div>
  <div class="wz-step" data-s="2"><div class="wz-step-dot">3</div><span>Dados pessoais</span></div>
  <div class="wz-line"></div>
  <div class="wz-step" data-s="3"><div class="wz-step-dot">4</div><span>Pagamento</span></div>
</div>

<div class="wz-body">

<!-- ═══ STEP 0: Landing do plano ═══ -->
<div class="wz-panel active" id="step0">
  <div class="card">
    <div class="card-title" style="font-size:22px;margin-bottom:4px;"><?php echo View::e((string)($plano['name'] ?? '')); ?></div>
    <p style="color:#64748b;font-size:14px;margin-bottom:16px;"><?php echo View::e((string)($plano['description'] ?? '')); ?></p>
    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;">
      <span class="badge"><?php echo $cpu; ?> vCPU</span>
      <span class="badge"><?php echo $ramGb; ?> GB RAM</span>
      <span class="badge"><?php echo $discoGb; ?> GB SSD</span>
    </div>
    <div style="font-size:28px;font-weight:800;color:#1e293b;margin-bottom:6px;">
      <?php echo View::e(I18n::preco($preco)); ?><span style="font-size:14px;font-weight:400;color:#64748b;">/mês</span>
    </div>
    <ul style="font-size:13px;color:#475569;line-height:2;padding-left:18px;margin:16px 0;">
      <li>Servidor VPS dedicado com recursos garantidos</li>
      <li>Painel de controle completo</li>
      <li>Backups automáticos</li>
      <li>Monitoramento 24/7</li>
      <li>Suporte técnico incluso</li>
    </ul>
    <button class="btn btn-primary" style="width:100%;font-size:16px;padding:16px;" onclick="irPasso(1)">Continuar com este plano</button>
  </div>

  <?php if ($upsell): ?>
  <div class="card upsell-card" style="margin-top:8px;">
    <div class="upsell-badge">⭐ Recomendado</div>
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-top:6px;">
      <div>
        <div class="card-title"><?php echo View::e((string)($upsell['name'] ?? '')); ?></div>
        <p style="font-size:13px;color:#64748b;margin-bottom:8px;"><?php echo View::e((string)($upsell['description'] ?? '')); ?></p>
        <div style="display:flex;gap:6px;flex-wrap:wrap;">
          <span class="badge"><?php echo (int)($upsell['cpu'] ?? 0); ?> vCPU</span>
          <span class="badge"><?php echo round((int)($upsell['ram'] ?? 0) / 1024); ?> GB RAM</span>
          <span class="badge"><?php echo round((int)($upsell['storage'] ?? 0) / 1024); ?> GB SSD</span>
        </div>
      </div>
      <div style="text-align:right;">
        <div style="font-size:22px;font-weight:800;color:#4F46E5;"><?php echo View::e(I18n::preco((float)($upsell['price_monthly'] ?? 0))); ?><span style="font-size:12px;font-weight:400;color:#64748b;">/mês</span></div>
        <a href="/contratar?plan_id=<?php echo (int)($upsell['id'] ?? 0); ?>" class="btn btn-primary" style="margin-top:8px;font-size:13px;padding:10px 20px;">Escolher este</a>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>

<!-- ═══ STEP 1: Configuração ═══ -->
<div class="wz-panel" id="step1">
  <div class="card">
    <div class="card-title">Configuração da VPS</div>
    <p style="font-size:13px;color:#64748b;margin-bottom:16px;">Seu plano <strong><?php echo View::e((string)($plano['name'] ?? '')); ?></strong> inclui:</p>
    <div style="display:flex;gap:16px;flex-wrap:wrap;margin-bottom:20px;">
      <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:12px 16px;flex:1;min-width:120px;text-align:center;">
        <div style="font-size:22px;font-weight:800;color:#4F46E5;"><?php echo $cpu; ?></div>
        <div style="font-size:12px;color:#64748b;">vCPU</div>
      </div>
      <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:12px 16px;flex:1;min-width:120px;text-align:center;">
        <div style="font-size:22px;font-weight:800;color:#4F46E5;"><?php echo $ramGb; ?> GB</div>
        <div style="font-size:12px;color:#64748b;">RAM</div>
      </div>
      <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:12px 16px;flex:1;min-width:120px;text-align:center;">
        <div style="font-size:22px;font-weight:800;color:#4F46E5;"><?php echo $discoGb; ?> GB</div>
        <div style="font-size:12px;color:#64748b;">SSD</div>
      </div>
    </div>
  </div>

  <!-- Quantidade de servidores -->
  <div class="card">
    <div class="card-title">Quantidade de servidores</div>
    <p style="font-size:13px;color:#64748b;margin-bottom:12px;">Cada servidor é uma VPS independente com os recursos do plano.</p>
    <div style="display:flex;align-items:center;gap:12px;">
      <button class="btn btn-ghost" onclick="alterarQtd(-1)" style="width:40px;height:40px;padding:0;font-size:18px;">−</button>
      <input type="number" id="qtdServidores" value="1" min="1" max="20" class="input" style="width:70px;text-align:center;font-size:18px;font-weight:700;" onchange="atualizarResumo()"/>
      <button class="btn btn-ghost" onclick="alterarQtd(1)" style="width:40px;height:40px;padding:0;font-size:18px;">+</button>
    </div>
  </div>

  <!-- Período -->
  <div class="card">
    <div class="card-title">Período de contratação</div>
    <div style="display:flex;flex-direction:column;gap:8px;margin-top:12px;">
      <div class="periodo-opt sel" data-periodo="1" onclick="selPeriodo(1)">
        <div><strong>Mensal</strong><br><span style="font-size:12px;color:#64748b;">Sem compromisso</span></div>
        <div style="font-weight:700;" id="preco1m"><?php echo View::e(I18n::preco($preco)); ?>/mês</div>
      </div>
      <div class="periodo-opt" data-periodo="6" onclick="selPeriodo(6)">
        <div><strong>Semestral</strong> <span class="desc-badge"><?php echo $d6; ?>% OFF</span><br><span style="font-size:12px;color:#64748b;">Cobrado a cada 6 meses</span></div>
        <div style="font-weight:700;" id="preco6m"></div>
      </div>
      <div class="periodo-opt" data-periodo="12" onclick="selPeriodo(12)">
        <div><strong>Anual</strong> <span class="desc-badge"><?php echo $d12; ?>% OFF</span><br><span style="font-size:12px;color:#64748b;">Cobrado anualmente</span></div>
        <div style="font-weight:700;" id="preco12m"></div>
      </div>
    </div>
  </div>

  <?php if (!empty($addons)): ?>
  <div class="card">
    <div class="card-title">Serviços adicionais</div>
    <p style="font-size:13px;color:#64748b;margin-bottom:12px;">Opcionais para complementar sua VPS.</p>
    <div style="display:flex;flex-direction:column;gap:8px;">
      <?php foreach ($addons as $a): ?>
      <div class="addon-opt" data-addon-id="<?php echo (int)($a['id'] ?? 0); ?>" data-addon-price="<?php echo (float)($a['price'] ?? 0); ?>" onclick="toggleAddon(this)">
        <input type="checkbox" style="accent-color:#4F46E5;width:18px;height:18px;pointer-events:none;"/>
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

  <!-- Resumo -->
  <div class="card" style="background:#f8fafc;">
    <div class="card-title">Resumo</div>
    <div id="resumoConfig" style="font-size:14px;line-height:2;"></div>
    <div style="border-top:1px solid #e2e8f0;padding-top:10px;margin-top:8px;display:flex;justify-content:space-between;font-size:20px;font-weight:800;">
      <span>Total</span>
      <span id="resumoTotal" style="color:#4F46E5;"></span>
    </div>
  </div>

  <div style="display:flex;gap:8px;margin-top:16px;">
    <button class="btn btn-ghost" onclick="irPasso(0)">← Voltar</button>
    <button class="btn btn-primary" style="flex:1;" onclick="avancarStep1()">Próximo</button>
  </div>
</div>

<!-- ═══ STEP 2: Dados pessoais ═══ -->
<div class="wz-panel" id="step2">
  <div class="card">
    <div class="card-title">Crie sua conta</div>
    <p style="font-size:13px;color:#64748b;margin-bottom:16px;">Preencha seus dados para criar sua conta e acessar o painel.</p>
    <div id="erroConta" class="erro-msg"></div>
    <div class="field">
      <label class="label">Nome completo *</label>
      <input class="input" type="text" id="cNome" required placeholder="Seu nome"/>
    </div>
    <div class="field">
      <label class="label">E-mail *</label>
      <input class="input" type="email" id="cEmail" required placeholder="seu@email.com"/>
    </div>
    <div class="field">
      <label class="label">CPF ou CNPJ *</label>
      <input class="input" type="text" id="cCpf" required placeholder="000.000.000-00" maxlength="18" inputmode="numeric"/>
    </div>
    <div class="field">
      <label class="label">Celular</label>
      <div style="display:flex;gap:8px;">
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
    <div class="field">
      <label class="label">Senha * <span style="font-size:11px;color:#94a3b8;">(mínimo 8 caracteres)</span></label>
      <input class="input" type="password" id="cSenha" required minlength="8" placeholder="••••••••"/>
    </div>
    <div class="field">
      <label class="label">Confirmar senha *</label>
      <input class="input" type="password" id="cSenha2" required minlength="8" placeholder="••••••••"/>
    </div>
  </div>
  <div style="display:flex;gap:8px;margin-top:16px;">
    <button class="btn btn-ghost" onclick="irPasso(1)">← Voltar</button>
    <button class="btn btn-primary" style="flex:1;" onclick="avancarStep2()">Próximo</button>
  </div>
</div>

<!-- ═══ STEP 3: Pagamento ═══ -->
<div class="wz-panel" id="step3">
  <div class="card">
    <div class="card-title">Forma de pagamento</div>
    <p style="font-size:13px;color:#64748b;margin-bottom:16px;">Escolha como deseja pagar sua assinatura.</p>
    <div id="erroPayment" class="erro-msg"></div>
    <?php if ($isBrl): ?>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
      <div class="gw-opt sel" data-gw="PIX" onclick="selGateway('PIX')">💠 PIX</div>
      <div class="gw-opt" data-gw="BOLETO" onclick="selGateway('BOLETO')">📄 Boleto</div>
      <div class="gw-opt" data-gw="CREDIT_CARD" onclick="selGateway('CREDIT_CARD')">💳 Cartão</div>
    </div>
    <?php else: ?>
    <div style="display:flex;gap:8px;">
      <div class="gw-opt sel" data-gw="stripe" onclick="selGateway('stripe')">💳 Card (Stripe)</div>
    </div>
    <?php endif; ?>
  </div>

  <!-- Resumo final -->
  <div class="card" style="background:#f8fafc;">
    <div class="card-title">Resumo do pedido</div>
    <div id="resumoFinal" style="font-size:14px;line-height:2;"></div>
    <div style="border-top:1px solid #e2e8f0;padding-top:10px;margin-top:8px;display:flex;justify-content:space-between;font-size:20px;font-weight:800;">
      <span>Total</span>
      <span id="resumoFinalTotal" style="color:#4F46E5;"></span>
    </div>
  </div>

  <div style="display:flex;gap:8px;margin-top:16px;">
    <button class="btn btn-ghost" onclick="irPasso(2)">← Voltar</button>
    <button class="btn btn-success" style="flex:1;font-size:16px;padding:16px;" id="btnFinalizar" onclick="finalizar()">Finalizar contratação</button>
  </div>
</div>

</div><!-- /wz-body -->

<!-- Modal upsell entre step 1 e 2 -->
<div class="overlay" id="modalUpsell">
  <div class="modal" style="text-align:center;">
    <div style="font-size:32px;margin-bottom:8px;">🚀</div>
    <div style="font-size:18px;font-weight:700;margin-bottom:6px;" id="upsellTitulo"></div>
    <div style="font-size:13px;color:#64748b;margin-bottom:16px;" id="upsellDesc"></div>
    <div style="display:flex;gap:8px;justify-content:center;">
      <button class="btn btn-ghost" onclick="fecharUpsell()">Não, obrigado</button>
      <button class="btn btn-primary" id="upsellAceitar" onclick="aceitarUpsell()">Aceitar oferta</button>
    </div>
  </div>
</div>

<script>
(function(){
  var planId=<?php echo $planId; ?>;
  var precoBase=<?php echo $preco; ?>;
  var d6=<?php echo $d6; ?>;
  var d12=<?php echo $d12; ?>;
  var moeda=<?php echo json_encode($moeda); ?>;
  var csrf=<?php echo json_encode(Csrf::token()); ?>;
  var upsell=<?php echo $upsell ? json_encode(['id'=>(int)$upsell['id'],'name'=>$upsell['name'],'price'=>(float)$upsell['price_monthly']]) : 'null'; ?>;

  var passo=0;
  var periodo=1;
  var gateway=<?php echo json_encode($isBrl ? 'PIX' : 'stripe'); ?>;
  var addonsIds=[];

  function fmt(v){
    if(moeda==='BRL') return 'R$ '+v.toFixed(2).replace('.',',');
    return '$ '+v.toFixed(2);
  }

  function precoComDesconto(base,per){
    if(per===6) return base*(1-d6/100);
    if(per===12) return base*(1-d12/100);
    return base;
  }

  // Init preços período
  document.getElementById('preco6m').textContent=fmt(precoComDesconto(precoBase,6))+'/mês';
  document.getElementById('preco12m').textContent=fmt(precoComDesconto(precoBase,12))+'/mês';

  window.selPeriodo=function(p){
    periodo=p;
    document.querySelectorAll('.periodo-opt').forEach(function(el){
      el.classList.toggle('sel',parseInt(el.dataset.periodo)===p);
    });
    atualizarResumo();
  };

  window.alterarQtd=function(d){
    var el=document.getElementById('qtdServidores');
    var v=Math.max(1,Math.min(20,parseInt(el.value||'1')+d));
    el.value=v;
    atualizarResumo();
  };

  window.toggleAddon=function(el){
    var cb=el.querySelector('input[type=checkbox]');
    cb.checked=!cb.checked;
    el.classList.toggle('sel',cb.checked);
    var id=parseInt(el.dataset.addonId);
    if(cb.checked){if(addonsIds.indexOf(id)===-1)addonsIds.push(id);}
    else{addonsIds=addonsIds.filter(function(x){return x!==id;});}
    atualizarResumo();
  };

  window.selGateway=function(gw){
    gateway=gw;
    document.querySelectorAll('.gw-opt').forEach(function(el){
      el.classList.toggle('sel',el.dataset.gw===gw);
    });
  };

  window.atualizarResumo=function(){
    var qtd=Math.max(1,parseInt(document.getElementById('qtdServidores').value||'1'));
    var precoUnit=precoComDesconto(precoBase,periodo);
    var addonsTotal=0;
    document.querySelectorAll('.addon-opt').forEach(function(el){
      if(el.classList.contains('sel')) addonsTotal+=parseFloat(el.dataset.addonPrice)||0;
    });
    var totalMes=(precoUnit+addonsTotal)*qtd;
    var totalPeriodo=totalMes*periodo;
    var perLabel=periodo===1?'mês':periodo===6?'semestre':'ano';

    var html='';
    html+='<div style="display:flex;justify-content:space-between;"><span>'+qtd+'x <?php echo View::e((string)($plano['name'] ?? '')); ?></span><span>'+fmt(precoUnit*qtd)+'/mês</span></div>';
    if(addonsTotal>0) html+='<div style="display:flex;justify-content:space-between;color:#475569;"><span>Addons</span><span>+'+fmt(addonsTotal*qtd)+'/mês</span></div>';
    if(periodo>1) html+='<div style="display:flex;justify-content:space-between;color:#16a34a;"><span>Desconto '+(periodo===6?d6:d12)+'%</span><span>aplicado</span></div>';
    html+='<div style="font-size:12px;color:#94a3b8;margin-top:4px;">Cobrado: '+fmt(totalPeriodo)+' por '+perLabel+'</div>';

    document.getElementById('resumoConfig').innerHTML=html;
    document.getElementById('resumoTotal').textContent=fmt(totalMes)+'/mês';

    // Atualizar resumo final também
    document.getElementById('resumoFinal').innerHTML=html;
    document.getElementById('resumoFinalTotal').textContent=fmt(totalMes)+'/mês';
  };
  atualizarResumo();

  window.irPasso=function(p){
    passo=p;
    for(var i=0;i<4;i++){
      var panel=document.getElementById('step'+i);
      if(panel) panel.classList.toggle('active',i===p);
    }
    // Atualizar steps bar
    var steps=document.querySelectorAll('.wz-step');
    var lines=document.querySelectorAll('.wz-line');
    steps.forEach(function(s,idx){
      s.classList.remove('active','done');
      if(idx<p) s.classList.add('done');
      else if(idx===p) s.classList.add('active');
    });
    lines.forEach(function(l,idx){
      l.classList.toggle('done',idx<p);
    });
    window.scrollTo(0,0);
  };

  // Upsell logic
  var upsellShown=false;
  window.avancarStep1=function(){
    if(!upsellShown && periodo===1 && upsell){
      // Oferecer desconto por período maior
      document.getElementById('upsellTitulo').textContent='Economize '+d12+'% no plano anual!';
      document.getElementById('upsellDesc').textContent='Contratando o período anual, você paga '+fmt(precoComDesconto(precoBase,12))+'/mês ao invés de '+fmt(precoBase)+'/mês.';
      document.getElementById('upsellAceitar').textContent='Quero o desconto anual';
      document.getElementById('upsellAceitar').onclick=function(){
        selPeriodo(12);
        fecharUpsell();
        irPasso(2);
      };
      document.getElementById('modalUpsell').classList.add('show');
      upsellShown=true;
      return;
    }
    if(!upsellShown && upsell && periodo<12){
      document.getElementById('upsellTitulo').textContent='Que tal o '+upsell.name+'?';
      document.getElementById('upsellDesc').textContent='Por apenas '+fmt(upsell.price)+'/mês você tem mais recursos para seu projeto.';
      document.getElementById('upsellAceitar').textContent='Ver plano '+upsell.name;
      document.getElementById('upsellAceitar').onclick=function(){
        window.location.href='/contratar?plan_id='+upsell.id;
      };
      document.getElementById('modalUpsell').classList.add('show');
      upsellShown=true;
      return;
    }
    irPasso(2);
  };

  window.fecharUpsell=function(){
    document.getElementById('modalUpsell').classList.remove('show');
    irPasso(2);
  };
  window.aceitarUpsell=function(){};

  window.avancarStep2=function(){
    var nome=document.getElementById('cNome').value.trim();
    var email=document.getElementById('cEmail').value.trim();
    var cpf=document.getElementById('cCpf').value.trim();
    var senha=document.getElementById('cSenha').value;
    var senha2=document.getElementById('cSenha2').value;
    var errEl=document.getElementById('erroConta');

    if(!nome||!email||!cpf||!senha){errEl.textContent='Preencha todos os campos obrigatórios.';errEl.style.display='block';return;}
    if(senha.length<8){errEl.textContent='Senha mínima: 8 caracteres.';errEl.style.display='block';return;}
    if(senha!==senha2){errEl.textContent='As senhas não coincidem.';errEl.style.display='block';return;}
    errEl.style.display='none';
    atualizarResumo();
    irPasso(3);
  };

  window.finalizar=function(){
    var btn=document.getElementById('btnFinalizar');
    btn.disabled=true;btn.textContent='Processando...';
    var errEl=document.getElementById('erroPayment');
    errEl.style.display='none';

    var body=new FormData();
    body.append('_csrf',csrf);
    body.append('plan_id',planId);
    body.append('nome',document.getElementById('cNome').value.trim());
    body.append('email',document.getElementById('cEmail').value.trim());
    body.append('cpf_cnpj',document.getElementById('cCpf').value.trim());
    body.append('ddi',document.getElementById('cDdi').value);
    body.append('celular',document.getElementById('cCelular').value.trim());
    body.append('senha',document.getElementById('cSenha').value);
    body.append('gateway',gateway);
    body.append('periodo',periodo);
    body.append('quantidade',document.getElementById('qtdServidores').value);
    body.append('addons_ids',addonsIds.join(','));

    fetch('/contratar/finalizar',{method:'POST',body:body,credentials:'same-origin'})
      .then(function(r){return r.json();})
      .then(function(data){
        if(data.ok&&data.redirect){
          window.location.href=data.redirect;
        }else{
          errEl.textContent=data.erro||'Erro ao processar. Tente novamente.';
          errEl.style.display='block';
          btn.disabled=false;btn.textContent='Finalizar contratação';
        }
      })
      .catch(function(){
        errEl.textContent='Erro de conexão. Tente novamente.';
        errEl.style.display='block';
        btn.disabled=false;btn.textContent='Finalizar contratação';
      });
  };
})();
</script>
</body>
</html>
