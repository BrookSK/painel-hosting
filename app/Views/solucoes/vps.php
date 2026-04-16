<?php
declare(strict_types=1);
use LRV\Core\I18n;
use LRV\Core\View;
use LRV\Core\SistemaConfig;
$_nome = SistemaConfig::nome();
$_planos = is_array($planos ?? null) ? $planos : [];
?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>VPS Gerenciada — <?php echo View::e($_nome); ?></title>
<?php require __DIR__ . '/../_partials/seo.php'; ?>
<?php require __DIR__ . '/../_partials/estilo.php'; ?>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:system-ui,-apple-system,'Segoe UI',Roboto,sans-serif;background:#fff;color:#0f172a}

/* ── Hero VPS ── */
.vp-hero{background:linear-gradient(135deg,#1e1b4b 0%,#312e81 30%,#3730a3 60%,#4F46E5 85%,#7C3AED 100%);position:relative;overflow:hidden;padding:100px 0 80px}
.vp-hero::before{content:'';position:absolute;inset:0;background-image:radial-gradient(circle,rgba(255,255,255,.06) 1px,transparent 1px);background-size:40px 40px;pointer-events:none}
.vp-hero .glow{position:absolute;width:600px;height:600px;background:radial-gradient(circle,rgba(79,70,229,.4),transparent 65%);top:-200px;right:-100px;pointer-events:none}
.vp-hero-inner{position:relative;z-index:1;max-width:1100px;margin:0 auto;padding:0 24px;display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:center}
.vp-hero-badge{display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.15);padding:5px 14px;border-radius:99px;margin-bottom:20px}
.vp-hero-badge span{font-size:.72rem;font-weight:600;color:rgba(255,255,255,.8);letter-spacing:.06em;text-transform:uppercase}
.vp-hero h1{font-size:clamp(1.8rem,4.5vw,3.2rem);font-weight:800;color:#fff;line-height:1.12;margin-bottom:18px;letter-spacing:-.02em}
.vp-hero h1 em{font-style:italic;color:#c4b5fd}
.vp-hero p{font-size:1rem;color:rgba(255,255,255,.6);line-height:1.8;margin-bottom:28px;max-width:480px}
.vp-hero-actions{display:flex;gap:12px;flex-wrap:wrap}
.vp-btn-p{display:inline-flex;align-items:center;gap:8px;padding:14px 28px;border-radius:12px;font-size:.9rem;font-weight:700;background:#fff;color:#4F46E5;transition:transform .15s;text-decoration:none}
.vp-btn-p:hover{transform:translateY(-2px)}
.vp-btn-s{display:inline-flex;align-items:center;gap:8px;padding:14px 28px;border-radius:12px;font-size:.9rem;font-weight:700;background:rgba(255,255,255,.1);color:#fff;border:1.5px solid rgba(255,255,255,.2);text-decoration:none;transition:background .15s}
.vp-btn-s:hover{background:rgba(255,255,255,.18)}

/* Hero Visual — Server Dashboard */
.vp-hero-visual{background:rgba(255,255,255,.06);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,.12);border-radius:20px;padding:28px;color:#fff}
.vp-mock-bar{display:flex;gap:6px;margin-bottom:16px}.vp-mock-dot{width:10px;height:10px;border-radius:50%;background:rgba(255,255,255,.15)}.vp-mock-dot:first-child{background:#ef4444}.vp-mock-dot:nth-child(2){background:#f59e0b}.vp-mock-dot:nth-child(3){background:#22c55e}
.vp-dash-title{font-size:.7rem;text-transform:uppercase;letter-spacing:.08em;color:rgba(255,255,255,.4);margin-bottom:14px}
.vp-gauges{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:14px}
.vp-gauge{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);border-radius:12px;padding:14px 10px;text-align:center}
.vp-gauge-bar{height:6px;border-radius:3px;background:rgba(255,255,255,.1);margin:8px auto 6px;width:80%;overflow:hidden}
.vp-gauge-fill{height:100%;border-radius:3px;background:linear-gradient(90deg,#4F46E5,#7C3AED)}
.vp-gauge .lbl{font-size:.65rem;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.05em}
.vp-gauge .val{font-size:1.1rem;font-weight:800;color:#c4b5fd}
.vp-dash-status{display:flex;align-items:center;gap:8px;font-size:.75rem;color:rgba(255,255,255,.4)}
.vp-dash-status .dot{width:8px;height:8px;border-radius:50%;background:#22c55e;box-shadow:0 0 6px rgba(34,197,94,.5)}
@media(max-width:860px){.vp-hero-inner{grid-template-columns:1fr;text-align:center}.vp-hero p{margin:0 auto 28px}.vp-hero-actions{justify-content:center}.vp-hero-visual{display:none}}

/* ── Stats ── */
.vp-stats{background:#1e1b4b;padding:36px 0}
.vp-stats-inner{max-width:1100px;margin:0 auto;display:grid;grid-template-columns:repeat(4,1fr);text-align:center;gap:0}
.vp-stat{padding:24px 16px;border-right:1px solid rgba(255,255,255,.06)}.vp-stat:last-child{border:none}
.vp-stat h3{font-size:2rem;font-weight:900;color:#c4b5fd;margin-bottom:4px}.vp-stat p{font-size:.8rem;color:rgba(255,255,255,.4)}
@media(max-width:640px){.vp-stats-inner{grid-template-columns:1fr 1fr}.vp-stat:nth-child(2){border-right:none}}

/* ── Sections ── */
.vp-section{padding:80px 24px}.vp-section.alt{background:#f8fafc}.vp-section.dark{background:#1e1b4b;color:#fff}
.vp-inner{max-width:1100px;margin:0 auto}
.vp-label{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:#4F46E5;margin-bottom:10px}
.vp-title{font-size:clamp(22px,3.5vw,34px);font-weight:800;color:#0f172a;margin-bottom:10px;letter-spacing:-.02em;line-height:1.15}
.vp-sub{font-size:15px;color:#64748b;line-height:1.75;max-width:560px}

/* ── Features Grid ── */
.vp-features{display:grid;grid-template-columns:repeat(3,1fr);gap:2px;background:#e2e8f0;border:1px solid #e2e8f0;border-radius:18px;overflow:hidden;margin-top:40px}
.vp-feat{background:#fff;padding:32px 24px;transition:background .2s}
.vp-feat:hover{background:#eef2ff}
.vp-feat-icon{width:48px;height:48px;background:#eef2ff;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:24px;margin-bottom:16px}
.vp-feat h3{font-size:.95rem;font-weight:700;margin-bottom:6px}.vp-feat p{font-size:.85rem;color:#64748b;line-height:1.6}
@media(max-width:860px){.vp-features{grid-template-columns:1fr 1fr}}
@media(max-width:560px){.vp-features{grid-template-columns:1fr}}

/* ── How it works ── */
.vp-steps{display:flex;align-items:flex-start;justify-content:center;gap:16px;margin-top:40px}
.vp-step{text-align:center;flex:1;max-width:260px}
.vp-step-num{width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,#4F46E5,#7C3AED);color:#fff;font-size:22px;font-weight:800;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;box-shadow:0 4px 20px rgba(79,70,229,.3)}
.vp-step h3{font-size:15px;font-weight:700;margin-bottom:6px}.vp-step p{font-size:13px;color:rgba(255,255,255,.5)}
.vp-step-arrow{color:rgba(255,255,255,.15);font-size:28px;padding-top:14px;flex-shrink:0}
@media(max-width:768px){.vp-steps{flex-direction:column;align-items:center}.vp-step-arrow{transform:rotate(90deg);padding:0}}

/* ── Comparison ── */
.vp-compare{display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-top:40px}
.vp-compare-card{border-radius:16px;padding:28px 24px}
.vp-compare-card.bad{background:#fef2f2;border:1px solid #fecaca}
.vp-compare-card.good{background:#eef2ff;border:2px solid #4F46E5}
.vp-compare-card h3{font-size:16px;font-weight:700;margin-bottom:14px;display:flex;align-items:center;gap:8px}
.vp-compare-card ul{list-style:none;padding:0}
.vp-compare-card ul li{padding:8px 0;font-size:13px;display:flex;align-items:flex-start;gap:8px;border-bottom:1px solid rgba(0,0,0,.04)}
@media(max-width:640px){.vp-compare{grid-template-columns:1fr}}

/* ── CTA ── */
.vp-cta-section{padding:80px 24px;background:linear-gradient(135deg,#1e1b4b,#312e81);text-align:center;color:#fff}
.vp-cta-section h2{font-size:clamp(24px,4vw,36px);font-weight:800;margin-bottom:12px}
.vp-cta-section p{font-size:16px;color:rgba(255,255,255,.55);max-width:500px;margin:0 auto 28px}

/* ── FAQ ── */
.vp-faq{max-width:700px;margin:40px auto 0;display:flex;flex-direction:column;gap:10px}
.vp-faq details{background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;transition:border-color .2s}
.vp-faq details[open]{border-color:#4F46E5}
.vp-faq summary{padding:16px 20px;font-size:14px;font-weight:600;cursor:pointer;list-style:none;display:flex;justify-content:space-between;align-items:center}
.vp-faq summary::after{content:'+';font-size:18px;color:#94a3b8;transition:transform .2s}
.vp-faq details[open] summary::after{content:'−';color:#4F46E5}
.vp-faq summary::-webkit-details-marker{display:none}
.vp-faq details p{padding:0 20px 16px;font-size:13px;color:#64748b;line-height:1.7}
</style>
</head>
<body>
<?php require __DIR__ . '/../_partials/navbar-publica.php'; ?>

<!-- HERO -->
<section class="vp-hero">
  <div class="glow"></div>
  <div class="vp-hero-inner">
    <div>
      <div class="vp-hero-badge"><span>🖥️ VPS Gerenciada</span></div>
      <h1>Servidores virtuais com <em>poder dedicado</em></h1>
      <p>VPS com recursos dedicados, SSD NVMe, proteção DDoS, terminal web e painel completo. Performance de servidor dedicado com a praticidade da nuvem.</p>
      <div class="vp-hero-actions">
        <a href="#planos" class="vp-btn-p">Ver planos VPS</a>
        <a href="/contato" class="vp-btn-s">Falar com a equipe</a>
      </div>
    </div>
    <div class="vp-hero-visual">
      <div class="vp-mock-bar"><div class="vp-mock-dot"></div><div class="vp-mock-dot"></div><div class="vp-mock-dot"></div></div>
      <div class="vp-dash-title">Server Dashboard — VPS-01</div>
      <div class="vp-gauges">
        <div class="vp-gauge">
          <div class="lbl">CPU</div>
          <div class="val">23%</div>
          <div class="vp-gauge-bar"><div class="vp-gauge-fill" style="width:23%"></div></div>
        </div>
        <div class="vp-gauge">
          <div class="lbl">RAM</div>
          <div class="val">1.2 GB</div>
          <div class="vp-gauge-bar"><div class="vp-gauge-fill" style="width:45%"></div></div>
        </div>
        <div class="vp-gauge">
          <div class="lbl">Disco</div>
          <div class="val">18 GB</div>
          <div class="vp-gauge-bar"><div class="vp-gauge-fill" style="width:36%"></div></div>
        </div>
      </div>
      <div class="vp-dash-status"><div class="dot"></div> Online — Uptime 99.98% — São Paulo, BR</div>
    </div>
  </div>
</section>

<!-- STATS -->
<div class="vp-stats">
  <div class="vp-stats-inner">
    <div class="vp-stat"><h3>99.9%</h3><p>Uptime garantido</p></div>
    <div class="vp-stat"><h3>NVMe</h3><p>SSD de alta performance</p></div>
    <div class="vp-stat"><h3>DDoS</h3><p>Proteção inclusa</p></div>
    <div class="vp-stat"><h3>24/7</h3><p>Suporte especializado</p></div>
  </div>
</div>

<!-- COMPARAÇÃO -->
<section class="vp-section">
  <div class="vp-inner">
    <div style="text-align:center;margin-bottom:8px;">
      <div class="vp-label">Por que escolher?</div>
      <h2 class="vp-title">VPS genérica vs VPS <?php echo View::e($_nome); ?></h2>
      <p class="vp-sub" style="margin:0 auto;">Veja a diferença entre uma VPS comum e nossa infraestrutura gerenciada com painel completo.</p>
    </div>
    <div class="vp-compare">
      <div class="vp-compare-card bad">
        <h3>❌ VPS genérica</h3>
        <ul>
          <li>❌ Configuração manual de tudo via terminal</li>
          <li>❌ Sem painel de controle intuitivo</li>
          <li>❌ Backups manuais e propensos a falha</li>
          <li>❌ Sem monitoramento integrado</li>
          <li>❌ SSL precisa ser configurado manualmente</li>
          <li>❌ Suporte genérico sem conhecimento do ambiente</li>
        </ul>
      </div>
      <div class="vp-compare-card good">
        <h3>✅ VPS <?php echo View::e($_nome); ?></h3>
        <ul>
          <li>✅ Painel completo com terminal web integrado</li>
          <li>✅ Monitoramento de CPU, RAM e disco em tempo real</li>
          <li>✅ Backups automáticos diários com restauração</li>
          <li>✅ Deploy automático via Git</li>
          <li>✅ SSL grátis e automático para todos os domínios</li>
          <li>✅ Suporte especializado que conhece seu servidor</li>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section class="vp-section alt">
  <div class="vp-inner">
    <div style="text-align:center;">
      <div class="vp-label">Recursos completos</div>
      <h2 class="vp-title">Tudo que sua VPS precisa</h2>
    </div>
    <div class="vp-features">
      <div class="vp-feat"><div class="vp-feat-icon">⚡</div><h3>Recursos dedicados</h3><p>CPU, RAM e disco exclusivos para sua VPS. Sem compartilhamento, sem vizinhos barulhentos.</p></div>
      <div class="vp-feat"><div class="vp-feat-icon">💾</div><h3>SSD NVMe</h3><p>Armazenamento NVMe de alta velocidade. Leitura e escrita até 10x mais rápidas que SSD convencional.</p></div>
      <div class="vp-feat"><div class="vp-feat-icon">🖥️</div><h3>Terminal web</h3><p>Acesse seu servidor direto pelo navegador. SSH integrado sem precisar de cliente externo.</p></div>
      <div class="vp-feat"><div class="vp-feat-icon">📊</div><h3>Monitoramento</h3><p>Acompanhe CPU, RAM, disco e rede em tempo real com gráficos e alertas automáticos.</p></div>
      <div class="vp-feat"><div class="vp-feat-icon">🚀</div><h3>Deploy automático</h3><p>Conecte seu repositório Git e faça deploy com um push. CI/CD simplificado.</p></div>
      <div class="vp-feat"><div class="vp-feat-icon">🔄</div><h3>Backups</h3><p>Backups automáticos diários com retenção configurável. Restaure com um clique.</p></div>
      <div class="vp-feat"><div class="vp-feat-icon">🔒</div><h3>SSL grátis</h3><p>Certificado SSL Let's Encrypt automático para todos os seus domínios. HTTPS sem custo.</p></div>
      <div class="vp-feat"><div class="vp-feat-icon">🛡️</div><h3>Proteção DDoS</h3><p>Mitigação de ataques DDoS nativa na infraestrutura. Seu servidor sempre disponível.</p></div>
      <div class="vp-feat"><div class="vp-feat-icon">💬</div><h3>Suporte 24/7</h3><p>Equipe técnica disponível via chat, ticket e e-mail. Resolvemos desde configuração até otimização.</p></div>
    </div>
  </div>
</section>

<!-- COMO FUNCIONA -->
<section class="vp-section dark">
  <div class="vp-inner">
    <div style="text-align:center;margin-bottom:8px;">
      <div class="vp-label" style="color:#c4b5fd;">Como funciona</div>
      <h2 class="vp-title" style="color:#fff;">Sua VPS em 3 passos</h2>
    </div>
    <div class="vp-steps">
      <div class="vp-step"><div class="vp-step-num">1</div><h3 style="color:#fff;">Escolha o plano</h3><p>Selecione a configuração ideal de CPU, RAM e disco para seu projeto.</p></div>
      <div class="vp-step-arrow">→</div>
      <div class="vp-step"><div class="vp-step-num">2</div><h3 style="color:#fff;">VPS provisionada</h3><p>Em minutos seu servidor está pronto com sistema operacional e painel configurados.</p></div>
      <div class="vp-step-arrow">→</div>
      <div class="vp-step"><div class="vp-step-num">3</div><h3 style="color:#fff;">Acesse o painel</h3><p>Gerencie tudo pelo painel: terminal, deploy, domínios, backups e monitoramento.</p></div>
    </div>
  </div>
</section>

<!-- PLANOS -->
<?php $_accent = '#4F46E5'; $_plan_type = 'vps'; $_cta_base = '/cliente/planos/checkout?plan_id='; require __DIR__ . '/_planos-section.php'; ?>

<!-- FAQ -->
<section class="vp-section">
  <div class="vp-inner">
    <div style="text-align:center;">
      <div class="vp-label">Perguntas frequentes</div>
      <h2 class="vp-title">Dúvidas sobre VPS Gerenciada</h2>
    </div>
    <div class="vp-faq">
      <details><summary>Os recursos da VPS são realmente dedicados?</summary><p>Sim. CPU, RAM e disco são exclusivos da sua VPS. Não há compartilhamento com outros clientes, garantindo performance consistente 24/7.</p></details>
      <details><summary>Tenho acesso SSH/root ao servidor?</summary><p>Sim. Você tem acesso root completo via terminal web integrado no painel ou via SSH com seu cliente preferido. Controle total do servidor.</p></details>
      <details><summary>Posso fazer upgrade de recursos?</summary><p>Sim. Faça upgrade de CPU, RAM ou disco a qualquer momento sem perder dados. A migração é feita automaticamente com mínimo downtime.</p></details>
      <details><summary>Como funcionam os backups?</summary><p>Backups automáticos diários com retenção configurável. Você pode restaurar qualquer backup com um clique pelo painel ou criar backups manuais a qualquer momento.</p></details>
      <details><summary>Que tipo de monitoramento está disponível?</summary><p>Monitoramento em tempo real de CPU, RAM, disco e rede com gráficos históricos. Alertas automáticos por e-mail quando recursos atingem limites configurados.</p></details>
      <details><summary>Qual é o SLA de uptime?</summary><p>Garantimos 99.9% de uptime com infraestrutura redundante. Em caso de falha, a migração automática mantém seu servidor disponível. Créditos são aplicados se o SLA não for cumprido.</p></details>
    </div>
  </div>
</section>

<!-- CTA FINAL -->
<section class="vp-cta-section">
  <h2>Pronto para sua VPS dedicada?</h2>
  <p>Comece agora com recursos dedicados, SSD NVMe e painel completo. Sem surpresas.</p>
  <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
    <a href="#planos" class="vp-btn-p">Ver planos VPS</a>
    <a href="/contato" class="vp-btn-s">Falar com a equipe</a>
  </div>
</section>

<?php require __DIR__ . '/../_partials/footer.php'; ?>
<?php require __DIR__ . '/../_partials/chat-widget.php'; ?>
</body>
</html>
