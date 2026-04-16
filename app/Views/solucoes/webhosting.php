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
<title>Web Hosting — <?php echo View::e($_nome); ?></title>
<?php require __DIR__ . '/../_partials/seo.php'; ?>
<?php require __DIR__ . '/../_partials/estilo.php'; ?>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:system-ui,-apple-system,'Segoe UI',Roboto,sans-serif;background:#fff;color:#0f172a}

/* ── Hero Web Hosting ── */
.wh-hero{background:linear-gradient(135deg,#052e16 0%,#14532d 30%,#166534 60%,#16a34a 85%,#22c55e 100%);position:relative;overflow:hidden;padding:100px 0 80px}
.wh-hero::before{content:'';position:absolute;inset:0;background-image:radial-gradient(circle,rgba(255,255,255,.08) 1px,transparent 1px);background-size:40px 40px;pointer-events:none}
.wh-hero .glow{position:absolute;width:600px;height:600px;background:radial-gradient(circle,rgba(34,197,94,.35),transparent 65%);top:-200px;right:-100px;pointer-events:none}
.wh-hero-inner{position:relative;z-index:1;max-width:1100px;margin:0 auto;padding:0 24px;display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:center}
.wh-hero-badge{display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.15);padding:5px 14px;border-radius:99px;margin-bottom:20px}
.wh-hero-badge span{font-size:.72rem;font-weight:600;color:rgba(255,255,255,.8);letter-spacing:.06em;text-transform:uppercase}
.wh-hero h1{font-size:clamp(1.8rem,4.5vw,3.2rem);font-weight:800;color:#fff;line-height:1.12;margin-bottom:18px;letter-spacing:-.02em}
.wh-hero h1 em{font-style:italic;color:#bbf7d0}
.wh-hero p{font-size:1rem;color:rgba(255,255,255,.6);line-height:1.8;margin-bottom:28px;max-width:480px}
.wh-hero-actions{display:flex;gap:12px;flex-wrap:wrap}
.wh-btn-p{display:inline-flex;align-items:center;gap:8px;padding:14px 28px;border-radius:12px;font-size:.9rem;font-weight:700;background:#fff;color:#16a34a;transition:transform .15s;text-decoration:none}
.wh-btn-p:hover{transform:translateY(-2px)}
.wh-btn-s{display:inline-flex;align-items:center;gap:8px;padding:14px 28px;border-radius:12px;font-size:.9rem;font-weight:700;background:rgba(255,255,255,.1);color:#fff;border:1.5px solid rgba(255,255,255,.2);text-decoration:none;transition:background .15s}
.wh-btn-s:hover{background:rgba(255,255,255,.18)}
.wh-hero-visual{background:rgba(255,255,255,.06);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,.12);border-radius:20px;padding:28px;color:#fff}
.wh-mock-bar{display:flex;gap:6px;margin-bottom:16px}.wh-mock-dot{width:10px;height:10px;border-radius:50%;background:rgba(255,255,255,.15)}.wh-mock-dot:first-child{background:#ef4444}.wh-mock-dot:nth-child(2){background:#f59e0b}.wh-mock-dot:nth-child(3){background:#22c55e}
.wh-mock-sidebar{display:flex;gap:14px}
.wh-mock-nav{width:30%;display:flex;flex-direction:column;gap:6px}
.wh-mock-nav-item{height:10px;border-radius:4px;background:rgba(255,255,255,.1)}
.wh-mock-nav-item.active{background:rgba(34,197,94,.4);border-left:3px solid #22c55e}
.wh-mock-main{flex:1;display:flex;flex-direction:column;gap:10px}
.wh-mock-file{display:flex;align-items:center;gap:8px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);border-radius:8px;padding:8px 12px}
.wh-mock-file-icon{font-size:14px}
.wh-mock-file-name{font-size:.7rem;color:rgba(255,255,255,.6);flex:1}
.wh-mock-file-size{font-size:.65rem;color:rgba(255,255,255,.3)}
.wh-mock-breadcrumb{font-size:.65rem;color:rgba(255,255,255,.35);margin-bottom:4px;display:flex;align-items:center;gap:4px}
@media(max-width:860px){.wh-hero-inner{grid-template-columns:1fr;text-align:center}.wh-hero p{margin:0 auto 28px}.wh-hero-actions{justify-content:center}.wh-hero-visual{display:none}}

/* ── Stats ── */
.wh-stats{background:#052e16;padding:36px 0}
.wh-stats-inner{max-width:1100px;margin:0 auto;display:grid;grid-template-columns:repeat(4,1fr);text-align:center;gap:0}
.wh-stat{padding:24px 16px;border-right:1px solid rgba(255,255,255,.06)}.wh-stat:last-child{border:none}
.wh-stat h3{font-size:2rem;font-weight:900;color:#bbf7d0;margin-bottom:4px}.wh-stat p{font-size:.8rem;color:rgba(255,255,255,.4)}
@media(max-width:640px){.wh-stats-inner{grid-template-columns:1fr 1fr}.wh-stat:nth-child(2){border-right:none}}

/* ── Sections ── */
.wh-section{padding:80px 24px}.wh-section.alt{background:#f8fafc}.wh-section.dark{background:#052e16;color:#fff}
.wh-inner{max-width:1100px;margin:0 auto}
.wh-label{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:#16a34a;margin-bottom:10px}
.wh-title{font-size:clamp(22px,3.5vw,34px);font-weight:800;color:#0f172a;margin-bottom:10px;letter-spacing:-.02em;line-height:1.15}
.wh-sub{font-size:15px;color:#64748b;line-height:1.75;max-width:560px}

/* ── Features Grid ── */
.wh-features{display:grid;grid-template-columns:repeat(3,1fr);gap:2px;background:#e2e8f0;border:1px solid #e2e8f0;border-radius:18px;overflow:hidden;margin-top:40px}
.wh-feat{background:#fff;padding:32px 24px;transition:background .2s}
.wh-feat:hover{background:#dcfce7}
.wh-feat-icon{width:48px;height:48px;background:#dcfce7;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:24px;margin-bottom:16px}
.wh-feat h3{font-size:.95rem;font-weight:700;margin-bottom:6px}.wh-feat p{font-size:.85rem;color:#64748b;line-height:1.6}
@media(max-width:860px){.wh-features{grid-template-columns:1fr 1fr}}
@media(max-width:560px){.wh-features{grid-template-columns:1fr}}

/* ── How it works ── */
.wh-steps{display:flex;align-items:flex-start;justify-content:center;gap:16px;margin-top:40px}
.wh-step{text-align:center;flex:1;max-width:260px}
.wh-step-num{width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,#16a34a,#22c55e);color:#fff;font-size:22px;font-weight:800;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;box-shadow:0 4px 20px rgba(22,163,74,.3)}
.wh-step h3{font-size:15px;font-weight:700;margin-bottom:6px}.wh-step p{font-size:13px;color:rgba(255,255,255,.5)}
.wh-step-arrow{color:rgba(255,255,255,.15);font-size:28px;padding-top:14px;flex-shrink:0}
@media(max-width:768px){.wh-steps{flex-direction:column;align-items:center}.wh-step-arrow{transform:rotate(90deg);padding:0}}

/* ── Comparison ── */
.wh-compare{display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-top:40px}
.wh-compare-card{border-radius:16px;padding:28px 24px}
.wh-compare-card.bad{background:#fef2f2;border:1px solid #fecaca}
.wh-compare-card.good{background:#dcfce7;border:2px solid #16a34a}
.wh-compare-card h3{font-size:16px;font-weight:700;margin-bottom:14px;display:flex;align-items:center;gap:8px}
.wh-compare-card ul{list-style:none;padding:0}
.wh-compare-card ul li{padding:8px 0;font-size:13px;display:flex;align-items:flex-start;gap:8px;border-bottom:1px solid rgba(0,0,0,.04)}
@media(max-width:640px){.wh-compare{grid-template-columns:1fr}}

/* ── CTA ── */
.wh-cta-section{padding:80px 24px;background:linear-gradient(135deg,#052e16,#166534);text-align:center;color:#fff}
.wh-cta-section h2{font-size:clamp(24px,4vw,36px);font-weight:800;margin-bottom:12px}
.wh-cta-section p{font-size:16px;color:rgba(255,255,255,.55);max-width:500px;margin:0 auto 28px}

/* ── FAQ ── */
.wh-faq{max-width:700px;margin:40px auto 0;display:flex;flex-direction:column;gap:10px}
.wh-faq details{background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;transition:border-color .2s}
.wh-faq details[open]{border-color:#16a34a}
.wh-faq summary{padding:16px 20px;font-size:14px;font-weight:600;cursor:pointer;list-style:none;display:flex;justify-content:space-between;align-items:center}
.wh-faq summary::after{content:'+';font-size:18px;color:#94a3b8;transition:transform .2s}
.wh-faq details[open] summary::after{content:'−';color:#16a34a}
.wh-faq summary::-webkit-details-marker{display:none}
.wh-faq details p{padding:0 20px 16px;font-size:13px;color:#64748b;line-height:1.7}
</style>
</head>
<body>
<?php require __DIR__ . '/../_partials/navbar-publica.php'; ?>

<!-- HERO -->
<section class="wh-hero">
  <div class="glow"></div>
  <div class="wh-hero-inner">
    <div>
      <div class="wh-hero-badge"><span>🌐 Web Hosting</span></div>
      <h1>Hospedagem completa para seus <em>projetos web</em></h1>
      <p>Hospede sites e aplicações com catálogo de apps, deploy via Git, bancos de dados e SSL grátis. Tudo gerenciado em um painel intuitivo.</p>
      <div class="wh-hero-actions">
        <a href="#planos" class="wh-btn-p">Ver planos de Hosting</a>
        <a href="/contato" class="wh-btn-s">Falar com a equipe</a>
      </div>
    </div>
    <div class="wh-hero-visual">
      <div class="wh-mock-bar"><div class="wh-mock-dot"></div><div class="wh-mock-dot"></div><div class="wh-mock-dot"></div></div>
      <div class="wh-mock-breadcrumb">📁 /home &gt; public_html</div>
      <div class="wh-mock-sidebar">
        <div class="wh-mock-nav">
          <div class="wh-mock-nav-item active"></div>
          <div class="wh-mock-nav-item"></div>
          <div class="wh-mock-nav-item"></div>
          <div class="wh-mock-nav-item"></div>
          <div class="wh-mock-nav-item"></div>
        </div>
        <div class="wh-mock-main">
          <div class="wh-mock-file"><span class="wh-mock-file-icon">📄</span><span class="wh-mock-file-name">index.php</span><span class="wh-mock-file-size">4.2 KB</span></div>
          <div class="wh-mock-file"><span class="wh-mock-file-icon">📁</span><span class="wh-mock-file-name">wp-content/</span><span class="wh-mock-file-size">—</span></div>
          <div class="wh-mock-file"><span class="wh-mock-file-icon">⚙️</span><span class="wh-mock-file-name">.htaccess</span><span class="wh-mock-file-size">1.1 KB</span></div>
          <div class="wh-mock-file"><span class="wh-mock-file-icon">🗄️</span><span class="wh-mock-file-name">database.sql</span><span class="wh-mock-file-size">12 MB</span></div>
          <div class="wh-mock-file"><span class="wh-mock-file-icon">🔒</span><span class="wh-mock-file-name">.env</span><span class="wh-mock-file-size">0.3 KB</span></div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- STATS -->
<div class="wh-stats">
  <div class="wh-stats-inner">
    <div class="wh-stat"><h3>50+</h3><p>Catálogo de apps</p></div>
    <div class="wh-stat"><h3>99.9%</h3><p>Uptime garantido</p></div>
    <div class="wh-stat"><h3>Git</h3><p>Deploy integrado</p></div>
    <div class="wh-stat"><h3>SSL</h3><p>Grátis em todos os planos</p></div>
  </div>
</div>

<!-- COMPARAÇÃO -->
<section class="wh-section">
  <div class="wh-inner">
    <div style="text-align:center;margin-bottom:8px;">
      <div class="wh-label">Por que escolher?</div>
      <h2 class="wh-title">Hospedagem limitada vs Web Hosting <?php echo View::e($_nome); ?></h2>
      <p class="wh-sub" style="margin:0 auto;">Compare uma hospedagem básica com nossa plataforma completa para projetos web.</p>
    </div>
    <div class="wh-compare">
      <div class="wh-compare-card bad">
        <h3>❌ Hospedagem limitada</h3>
        <ul>
          <li>❌ Apenas PHP e HTML estáticos</li>
          <li>❌ Sem suporte a Node.js ou Python</li>
          <li>❌ Deploy manual via FTP</li>
          <li>❌ Sem catálogo de aplicações</li>
          <li>❌ Backups manuais e pouco confiáveis</li>
          <li>❌ Painel antigo e confuso</li>
        </ul>
      </div>
      <div class="wh-compare-card good">
        <h3>✅ Web Hosting <?php echo View::e($_nome); ?></h3>
        <ul>
          <li>✅ Suporte a PHP, Node.js, Python e mais</li>
          <li>✅ Catálogo com WordPress, Laravel, Next.js</li>
          <li>✅ Deploy via Git com um push</li>
          <li>✅ Bancos de dados MySQL dedicados</li>
          <li>✅ Backups automáticos diários</li>
          <li>✅ Painel moderno e intuitivo</li>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section class="wh-section alt">
  <div class="wh-inner">
    <div style="text-align:center;">
      <div class="wh-label">Tudo incluso</div>
      <h2 class="wh-title">Tudo que sua hospedagem precisa</h2>
    </div>
    <div class="wh-features">
      <div class="wh-feat"><div class="wh-feat-icon">📦</div><h3>Catálogo de apps</h3><p>Instale WordPress, Laravel, Node.js e dezenas de outras aplicações com um clique. Sem configuração manual.</p></div>
      <div class="wh-feat"><div class="wh-feat-icon">🚀</div><h3>Git Deploy</h3><p>Conecte seu repositório Git e faça deploy automático a cada push. Fluxo profissional sem complicação.</p></div>
      <div class="wh-feat"><div class="wh-feat-icon">🗄️</div><h3>Bancos de dados MySQL</h3><p>Crie e gerencie bancos de dados MySQL dedicados. Acesse pelo painel ou phpMyAdmin integrado.</p></div>
      <div class="wh-feat"><div class="wh-feat-icon">📁</div><h3>Gerenciador de arquivos</h3><p>Navegue, edite e gerencie seus arquivos direto pelo navegador. Upload, download e edição inline.</p></div>
      <div class="wh-feat"><div class="wh-feat-icon">🌐</div><h3>Domínios com SSL</h3><p>Conecte domínios próprios com SSL Let's Encrypt automático. HTTPS sem custo extra em todos os planos.</p></div>
      <div class="wh-feat"><div class="wh-feat-icon">💾</div><h3>Backups automáticos</h3><p>Seus dados protegidos com backups diários automáticos. Restaure arquivos e bancos com um clique.</p></div>
      <div class="wh-feat"><div class="wh-feat-icon">📊</div><h3>Painel intuitivo</h3><p>Gerencie tudo em um painel moderno: sites, bancos, domínios, backups, Git e suporte em um só lugar.</p></div>
      <div class="wh-feat"><div class="wh-feat-icon">🛡️</div><h3>Proteção DDoS</h3><p>Infraestrutura com proteção contra ataques DDoS nativa. Seus projetos sempre disponíveis.</p></div>
      <div class="wh-feat"><div class="wh-feat-icon">💬</div><h3>Suporte técnico</h3><p>Equipe especializada pronta para ajudar. Chat, ticket e e-mail disponíveis para qualquer dúvida.</p></div>
    </div>
  </div>
</section>

<!-- COMO FUNCIONA -->
<section class="wh-section dark">
  <div class="wh-inner">
    <div style="text-align:center;margin-bottom:8px;">
      <div class="wh-label" style="color:#bbf7d0;">Como funciona</div>
      <h2 class="wh-title" style="color:#fff;">Online em 3 passos</h2>
    </div>
    <div class="wh-steps">
      <div class="wh-step"><div class="wh-step-num">1</div><h3 style="color:#fff;">Escolha o plano</h3><p>Selecione o plano ideal para o tamanho do seu projeto.</p></div>
      <div class="wh-step-arrow">→</div>
      <div class="wh-step"><div class="wh-step-num">2</div><h3 style="color:#fff;">Instale apps do catálogo</h3><p>Escolha entre WordPress, Laravel, Node.js e mais. Instalação em um clique.</p></div>
      <div class="wh-step-arrow">→</div>
      <div class="wh-step"><div class="wh-step-num">3</div><h3 style="color:#fff;">Publique</h3><p>Seu site ou app está no ar. Use Git Deploy para atualizações contínuas.</p></div>
    </div>
  </div>
</section>

<!-- PLANOS -->
<?php $_accent = '#16a34a'; $_plan_type = 'webhosting'; $_cta_base = '/cliente/planos/checkout?plan_id='; require __DIR__ . '/_planos-section.php'; ?>

<!-- FAQ -->
<section class="wh-section">
  <div class="wh-inner">
    <div style="text-align:center;">
      <div class="wh-label">Perguntas frequentes</div>
      <h2 class="wh-title">Dúvidas sobre Web Hosting</h2>
    </div>
    <div class="wh-faq">
      <details><summary>Quais linguagens e frameworks são suportados?</summary><p>Suportamos PHP, Node.js, Python e aplicações estáticas. O catálogo inclui WordPress, Laravel, Next.js, Express e muitos outros frameworks prontos para instalar.</p></details>
      <details><summary>Posso usar meu próprio domínio?</summary><p>Sim. Conecte quantos domínios quiser com configuração DNS simplificada. Todos recebem SSL grátis automaticamente.</p></details>
      <details><summary>Tenho acesso SSH?</summary><p>O Web Hosting foca em simplicidade com painel e Git Deploy. Para acesso SSH completo, recomendamos o plano VPS.</p></details>
      <details><summary>Posso migrar para um VPS depois?</summary><p>Sim. Faça upgrade a qualquer momento sem perder dados. Nossa equipe ajuda na migração completa.</p></details>
      <details><summary>Como funciona o Git Deploy?</summary><p>Conecte seu repositório GitHub, GitLab ou Bitbucket. A cada push na branch configurada, o deploy é feito automaticamente no seu hosting.</p></details>
      <details><summary>Quantos bancos de dados posso criar?</summary><p>Depende do plano escolhido. Cada plano define o número máximo de bancos MySQL que você pode criar e gerenciar.</p></details>
    </div>
  </div>
</section>

<!-- CTA FINAL -->
<section class="wh-cta-section">
  <h2>Pronto para hospedar seus projetos?</h2>
  <p>Comece agora e tenha seu site ou app no ar em minutos. Catálogo de apps, Git Deploy e SSL grátis.</p>
  <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
    <a href="#planos" class="wh-btn-p">Ver planos</a>
    <a href="/contato" class="wh-btn-s">Falar com a equipe</a>
  </div>
</section>

<?php require __DIR__ . '/../_partials/footer.php'; ?>
<?php require __DIR__ . '/../_partials/chat-widget.php'; ?>
</body>
</html>
