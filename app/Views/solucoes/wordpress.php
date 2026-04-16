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
<title>WordPress Gerenciado — <?php echo View::e($_nome); ?></title>
<?php require __DIR__ . '/../_partials/seo.php'; ?>
<?php require __DIR__ . '/../_partials/estilo.php'; ?>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:system-ui,-apple-system,'Segoe UI',Roboto,sans-serif;background:#fff;color:#0f172a}

/* ── Hero WordPress ── */
.wp-hero{background:linear-gradient(135deg,#1a1a2e 0%,#16213e 30%,#0f3460 60%,#1d4ed8 85%,#3b82f6 100%);position:relative;overflow:hidden;padding:100px 0 80px}
.wp-hero::before{content:'';position:absolute;inset:0;background-image:radial-gradient(circle,rgba(255,255,255,.08) 1px,transparent 1px);background-size:40px 40px;pointer-events:none}
.wp-hero .glow{position:absolute;width:600px;height:600px;background:radial-gradient(circle,rgba(59,130,246,.35),transparent 65%);top:-200px;right:-100px;pointer-events:none}
.wp-hero-inner{position:relative;z-index:1;max-width:1100px;margin:0 auto;padding:0 24px;display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:center}
.wp-hero-badge{display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.15);padding:5px 14px;border-radius:99px;margin-bottom:20px}
.wp-hero-badge span{font-size:.72rem;font-weight:600;color:rgba(255,255,255,.8);letter-spacing:.06em;text-transform:uppercase}
.wp-hero h1{font-size:clamp(1.8rem,4.5vw,3.2rem);font-weight:800;color:#fff;line-height:1.12;margin-bottom:18px;letter-spacing:-.02em}
.wp-hero h1 em{font-style:italic;color:#93c5fd}
.wp-hero p{font-size:1rem;color:rgba(255,255,255,.6);line-height:1.8;margin-bottom:28px;max-width:480px}
.wp-hero-actions{display:flex;gap:12px;flex-wrap:wrap}
.wp-btn-p{display:inline-flex;align-items:center;gap:8px;padding:14px 28px;border-radius:12px;font-size:.9rem;font-weight:700;background:#fff;color:#1d4ed8;transition:transform .15s;text-decoration:none}
.wp-btn-p:hover{transform:translateY(-2px)}
.wp-btn-s{display:inline-flex;align-items:center;gap:8px;padding:14px 28px;border-radius:12px;font-size:.9rem;font-weight:700;background:rgba(255,255,255,.1);color:#fff;border:1.5px solid rgba(255,255,255,.2);text-decoration:none;transition:background .15s}
.wp-btn-s:hover{background:rgba(255,255,255,.18)}
.wp-hero-visual{background:rgba(255,255,255,.06);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,.12);border-radius:20px;padding:28px;color:#fff}
.wp-mock-bar{display:flex;gap:6px;margin-bottom:16px}.wp-mock-dot{width:10px;height:10px;border-radius:50%;background:rgba(255,255,255,.15)}.wp-mock-dot:first-child{background:#ef4444}.wp-mock-dot:nth-child(2){background:#f59e0b}.wp-mock-dot:nth-child(3){background:#22c55e}
.wp-mock-content{display:flex;flex-direction:column;gap:10px}
.wp-mock-line{height:10px;border-radius:4px;background:rgba(255,255,255,.08)}
.wp-mock-line.w60{width:60%}.wp-mock-line.w80{width:80%}.wp-mock-line.w40{width:40%}.wp-mock-line.w100{width:100%}
.wp-mock-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:12px}
.wp-mock-card{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);border-radius:10px;padding:14px;text-align:center}
.wp-mock-card .num{font-size:1.4rem;font-weight:800;color:#93c5fd}.wp-mock-card .lbl{font-size:.7rem;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.05em;margin-top:4px}
@media(max-width:860px){.wp-hero-inner{grid-template-columns:1fr;text-align:center}.wp-hero p{margin:0 auto 28px}.wp-hero-actions{justify-content:center}.wp-hero-visual{display:none}}

/* ── Stats ── */
.wp-stats{background:#0f172a;padding:36px 0}
.wp-stats-inner{max-width:1100px;margin:0 auto;display:grid;grid-template-columns:repeat(4,1fr);text-align:center;gap:0}
.wp-stat{padding:24px 16px;border-right:1px solid rgba(255,255,255,.06)}.wp-stat:last-child{border:none}
.wp-stat h3{font-size:2rem;font-weight:900;color:#93c5fd;margin-bottom:4px}.wp-stat p{font-size:.8rem;color:rgba(255,255,255,.4)}
@media(max-width:640px){.wp-stats-inner{grid-template-columns:1fr 1fr}.wp-stat:nth-child(2){border-right:none}}

/* ── Sections ── */
.wp-section{padding:80px 24px}.wp-section.alt{background:#f8fafc}.wp-section.dark{background:#0f172a;color:#fff}
.wp-inner{max-width:1100px;margin:0 auto}
.wp-label{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:#1d4ed8;margin-bottom:10px}
.wp-title{font-size:clamp(22px,3.5vw,34px);font-weight:800;color:#0f172a;margin-bottom:10px;letter-spacing:-.02em;line-height:1.15}
.wp-sub{font-size:15px;color:#64748b;line-height:1.75;max-width:560px}

/* ── Features Grid ── */
.wp-features{display:grid;grid-template-columns:repeat(3,1fr);gap:2px;background:#e2e8f0;border:1px solid #e2e8f0;border-radius:18px;overflow:hidden;margin-top:40px}
.wp-feat{background:#fff;padding:32px 24px;transition:background .2s}
.wp-feat:hover{background:#eff6ff}
.wp-feat-icon{width:48px;height:48px;background:#eff6ff;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:24px;margin-bottom:16px}
.wp-feat h3{font-size:.95rem;font-weight:700;margin-bottom:6px}.wp-feat p{font-size:.85rem;color:#64748b;line-height:1.6}
@media(max-width:860px){.wp-features{grid-template-columns:1fr 1fr}}
@media(max-width:560px){.wp-features{grid-template-columns:1fr}}

/* ── How it works ── */
.wp-steps{display:flex;align-items:flex-start;justify-content:center;gap:16px;margin-top:40px}
.wp-step{text-align:center;flex:1;max-width:260px}
.wp-step-num{width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,#1d4ed8,#3b82f6);color:#fff;font-size:22px;font-weight:800;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;box-shadow:0 4px 20px rgba(29,78,216,.3)}
.wp-step h3{font-size:15px;font-weight:700;margin-bottom:6px}.wp-step p{font-size:13px;color:rgba(255,255,255,.5)}
.wp-step-arrow{color:rgba(255,255,255,.15);font-size:28px;padding-top:14px;flex-shrink:0}
@media(max-width:768px){.wp-steps{flex-direction:column;align-items:center}.wp-step-arrow{transform:rotate(90deg);padding:0}}

/* ── Comparison ── */
.wp-compare{display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-top:40px}
.wp-compare-card{border-radius:16px;padding:28px 24px}
.wp-compare-card.bad{background:#fef2f2;border:1px solid #fecaca}
.wp-compare-card.good{background:#eff6ff;border:2px solid #1d4ed8}
.wp-compare-card h3{font-size:16px;font-weight:700;margin-bottom:14px;display:flex;align-items:center;gap:8px}
.wp-compare-card ul{list-style:none;padding:0}
.wp-compare-card ul li{padding:8px 0;font-size:13px;display:flex;align-items:flex-start;gap:8px;border-bottom:1px solid rgba(0,0,0,.04)}
@media(max-width:640px){.wp-compare{grid-template-columns:1fr}}

/* ── CTA ── */
.wp-cta-section{padding:80px 24px;background:linear-gradient(135deg,#1a1a2e,#0f3460);text-align:center;color:#fff}
.wp-cta-section h2{font-size:clamp(24px,4vw,36px);font-weight:800;margin-bottom:12px}
.wp-cta-section p{font-size:16px;color:rgba(255,255,255,.55);max-width:500px;margin:0 auto 28px}

/* ── FAQ ── */
.wp-faq{max-width:700px;margin:40px auto 0;display:flex;flex-direction:column;gap:10px}
.wp-faq details{background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;transition:border-color .2s}
.wp-faq details[open]{border-color:#1d4ed8}
.wp-faq summary{padding:16px 20px;font-size:14px;font-weight:600;cursor:pointer;list-style:none;display:flex;justify-content:space-between;align-items:center}
.wp-faq summary::after{content:'+';font-size:18px;color:#94a3b8;transition:transform .2s}
.wp-faq details[open] summary::after{content:'−';color:#1d4ed8}
.wp-faq summary::-webkit-details-marker{display:none}
.wp-faq details p{padding:0 20px 16px;font-size:13px;color:#64748b;line-height:1.7}
</style>
</head>
<body>
<?php require __DIR__ . '/../_partials/navbar-publica.php'; ?>

<!-- HERO -->
<section class="wp-hero">
  <div class="glow"></div>
  <div class="wp-hero-inner">
    <div>
      <div class="wp-hero-badge"><span>📝 WordPress Gerenciado</span></div>
      <h1>Seu WordPress <em>rápido, seguro</em> e sem complicação</h1>
      <p>Hospedagem otimizada com instalação em 1 clique, backups automáticos, SSL grátis e painel simplificado. Foque no conteúdo, a gente cuida da infraestrutura.</p>
      <div class="wp-hero-actions">
        <a href="#planos" class="wp-btn-p">Ver planos WordPress</a>
        <a href="/contato" class="wp-btn-s">Falar com a equipe</a>
      </div>
    </div>
    <div class="wp-hero-visual">
      <div class="wp-mock-bar"><div class="wp-mock-dot"></div><div class="wp-mock-dot"></div><div class="wp-mock-dot"></div></div>
      <div class="wp-mock-content">
        <div class="wp-mock-line w80"></div>
        <div class="wp-mock-line w60"></div>
        <div class="wp-mock-line w100"></div>
        <div class="wp-mock-grid">
          <div class="wp-mock-card"><div class="num">99.9%</div><div class="lbl">Uptime</div></div>
          <div class="wp-mock-card"><div class="num">&lt;200ms</div><div class="lbl">TTFB</div></div>
          <div class="wp-mock-card"><div class="num">SSL</div><div class="lbl">Grátis</div></div>
          <div class="wp-mock-card"><div class="num">24/7</div><div class="lbl">Suporte</div></div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- STATS -->
<div class="wp-stats">
  <div class="wp-stats-inner">
    <div class="wp-stat"><h3>1 clique</h3><p>Instalação WordPress</p></div>
    <div class="wp-stat"><h3>99.9%</h3><p>Uptime garantido</p></div>
    <div class="wp-stat"><h3>SSL</h3><p>Grátis em todos os planos</p></div>
    <div class="wp-stat"><h3>24/7</h3><p>Suporte especializado</p></div>
  </div>
</div>

<!-- COMPARAÇÃO -->
<section class="wp-section">
  <div class="wp-inner">
    <div style="text-align:center;margin-bottom:8px;">
      <div class="wp-label">Por que migrar?</div>
      <h2 class="wp-title">Hospedagem comum vs WordPress Gerenciado</h2>
      <p class="wp-sub" style="margin:0 auto;">Veja a diferença entre uma hospedagem compartilhada e nossa infraestrutura dedicada para WordPress.</p>
    </div>
    <div class="wp-compare">
      <div class="wp-compare-card bad">
        <h3>❌ Hospedagem compartilhada</h3>
        <ul>
          <li>❌ Recursos divididos com centenas de sites</li>
          <li>❌ Lentidão em horários de pico</li>
          <li>❌ Sem backups automáticos confiáveis</li>
          <li>❌ Suporte genérico que não entende WordPress</li>
          <li>❌ SSL pago ou complicado de configurar</li>
          <li>❌ Sem isolamento — um site afeta todos</li>
        </ul>
      </div>
      <div class="wp-compare-card good">
        <h3>✅ <?php echo View::e($_nome); ?> WordPress</h3>
        <ul>
          <li>✅ Container isolado com recursos dedicados</li>
          <li>✅ Performance consistente 24/7</li>
          <li>✅ Backups diários automáticos</li>
          <li>✅ Suporte especializado em WordPress</li>
          <li>✅ SSL grátis e automático</li>
          <li>✅ Banco de dados MySQL dedicado</li>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section class="wp-section alt">
  <div class="wp-inner">
    <div style="text-align:center;">
      <div class="wp-label">Tudo incluso</div>
      <h2 class="wp-title">Tudo que seu WordPress precisa</h2>
    </div>
    <div class="wp-features">
      <div class="wp-feat"><div class="wp-feat-icon">🚀</div><h3>Instalação em 1 clique</h3><p>WordPress pré-configurado e otimizado, pronto para usar em segundos. Sem configuração manual.</p></div>
      <div class="wp-feat"><div class="wp-feat-icon">🗄️</div><h3>MySQL dedicado</h3><p>Banco de dados exclusivo com backups automáticos. Acesse pelo painel ou phpMyAdmin.</p></div>
      <div class="wp-feat"><div class="wp-feat-icon">🔒</div><h3>SSL grátis</h3><p>Certificado SSL Let's Encrypt automático para todos os seus domínios. HTTPS sem custo extra.</p></div>
      <div class="wp-feat"><div class="wp-feat-icon">💾</div><h3>Backups diários</h3><p>Seus dados protegidos com backups automáticos. Restaure com um clique a qualquer momento.</p></div>
      <div class="wp-feat"><div class="wp-feat-icon">📁</div><h3>Gerenciador de arquivos</h3><p>Edite temas, plugins e uploads direto pelo navegador. Sem FTP, sem complicação.</p></div>
      <div class="wp-feat"><div class="wp-feat-icon">🌐</div><h3>Domínio customizado</h3><p>Conecte seu domínio próprio com configuração DNS simplificada e verificação automática.</p></div>
      <div class="wp-feat"><div class="wp-feat-icon">📊</div><h3>Painel intuitivo</h3><p>Gerencie tudo em um painel moderno: sites, bancos, domínios, backups e suporte.</p></div>
      <div class="wp-feat"><div class="wp-feat-icon">🛡️</div><h3>Proteção DDoS</h3><p>Infraestrutura com proteção contra ataques DDoS nativa. Seu site sempre no ar.</p></div>
      <div class="wp-feat"><div class="wp-feat-icon">💬</div><h3>Suporte WordPress</h3><p>Equipe que entende WordPress de verdade. Chat, ticket e e-mail disponíveis.</p></div>
    </div>
  </div>
</section>

<!-- COMO FUNCIONA -->
<section class="wp-section dark">
  <div class="wp-inner">
    <div style="text-align:center;margin-bottom:8px;">
      <div class="wp-label" style="color:#93c5fd;">Como funciona</div>
      <h2 class="wp-title" style="color:#fff;">Online em 3 passos</h2>
    </div>
    <div class="wp-steps">
      <div class="wp-step"><div class="wp-step-num">1</div><h3 style="color:#fff;">Escolha o plano</h3><p>Selecione o plano ideal para o tamanho do seu projeto.</p></div>
      <div class="wp-step-arrow">→</div>
      <div class="wp-step"><div class="wp-step-num">2</div><h3 style="color:#fff;">WordPress instalado</h3><p>Em segundos seu WordPress está pronto com banco de dados e SSL.</p></div>
      <div class="wp-step-arrow">→</div>
      <div class="wp-step"><div class="wp-step-num">3</div><h3 style="color:#fff;">Publique conteúdo</h3><p>Acesse o wp-admin e comece a criar. Simples assim.</p></div>
    </div>
  </div>
</section>

<!-- PLANOS -->
<?php $_accent = '#1d4ed8'; $_plan_type = 'wordpress'; $_cta_base = '/cliente/planos/checkout?plan_id='; require __DIR__ . '/_planos-section.php'; ?>

<!-- FAQ -->
<section class="wp-section">
  <div class="wp-inner">
    <div style="text-align:center;">
      <div class="wp-label">Perguntas frequentes</div>
      <h2 class="wp-title">Dúvidas sobre WordPress Gerenciado</h2>
    </div>
    <div class="wp-faq">
      <details><summary>Posso migrar meu WordPress atual?</summary><p>Sim. Nossa equipe ajuda na migração completa sem downtime. Basta abrir um ticket após contratar.</p></details>
      <details><summary>Quantos sites posso ter?</summary><p>Depende do plano escolhido. Cada plano define o número máximo de sites WordPress que você pode criar.</p></details>
      <details><summary>Posso instalar plugins e temas?</summary><p>Sim, acesso total ao WordPress. Use o gerenciador de arquivos integrado ou o wp-admin normalmente.</p></details>
      <details><summary>E se meu site crescer?</summary><p>Faça upgrade a qualquer momento sem perder dados. Seus sites, bancos e configurações são preservados.</p></details>
      <details><summary>Tem acesso SSH/Terminal?</summary><p>O plano WordPress foca em simplicidade. Para acesso terminal completo, recomendamos o plano VPS.</p></details>
      <details><summary>Como funciona o suporte?</summary><p>Suporte especializado em WordPress via chat, ticket e e-mail. Resolvemos desde problemas de plugin até otimização de performance.</p></details>
    </div>
  </div>
</section>

<!-- CTA FINAL -->
<section class="wp-cta-section">
  <h2>Pronto para um WordPress mais rápido?</h2>
  <p>Comece agora e tenha seu site no ar em minutos. Sem complicação, sem surpresas.</p>
  <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
    <a href="#planos" class="wp-btn-p">Ver planos</a>
    <a href="/contato" class="wp-btn-s">Falar com a equipe</a>
  </div>
</section>

<?php require __DIR__ . '/../_partials/footer.php'; ?>
<?php require __DIR__ . '/../_partials/chat-widget.php'; ?>
</body>
</html>
