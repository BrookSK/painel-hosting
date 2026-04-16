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
<title>Node.js App — <?php echo View::e($_nome); ?></title>
<?php require __DIR__ . '/../_partials/seo.php'; ?>
<?php require __DIR__ . '/../_partials/estilo.php'; ?>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:system-ui,-apple-system,'Segoe UI',Roboto,sans-serif;background:#fff;color:#0f172a}

/* ── Hero Node.js ── */
.nj-hero{background:linear-gradient(135deg,#1c1917 0%,#292524 30%,#78350f 60%,#b45309 85%,#f59e0b 100%);position:relative;overflow:hidden;padding:100px 0 80px}
.nj-hero::before{content:'';position:absolute;inset:0;background-image:radial-gradient(circle,rgba(255,255,255,.08) 1px,transparent 1px);background-size:40px 40px;pointer-events:none}
.nj-hero .glow{position:absolute;width:600px;height:600px;background:radial-gradient(circle,rgba(245,158,11,.35),transparent 65%);top:-200px;right:-100px;pointer-events:none}
.nj-hero-inner{position:relative;z-index:1;max-width:1100px;margin:0 auto;padding:0 24px;display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:center}
.nj-hero-badge{display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.15);padding:5px 14px;border-radius:99px;margin-bottom:20px}
.nj-hero-badge span{font-size:.72rem;font-weight:600;color:rgba(255,255,255,.8);letter-spacing:.06em;text-transform:uppercase}
.nj-hero h1{font-size:clamp(1.8rem,4.5vw,3.2rem);font-weight:800;color:#fff;line-height:1.12;margin-bottom:18px;letter-spacing:-.02em}
.nj-hero h1 em{font-style:italic;color:#fde68a}
.nj-hero p{font-size:1rem;color:rgba(255,255,255,.6);line-height:1.8;margin-bottom:28px;max-width:480px}
.nj-hero-actions{display:flex;gap:12px;flex-wrap:wrap}
.nj-btn-p{display:inline-flex;align-items:center;gap:8px;padding:14px 28px;border-radius:12px;font-size:.9rem;font-weight:700;background:#fff;color:#d97706;transition:transform .15s;text-decoration:none}
.nj-btn-p:hover{transform:translateY(-2px)}
.nj-btn-s{display:inline-flex;align-items:center;gap:8px;padding:14px 28px;border-radius:12px;font-size:.9rem;font-weight:700;background:rgba(255,255,255,.1);color:#fff;border:1.5px solid rgba(255,255,255,.2);text-decoration:none;transition:background .15s}
.nj-btn-s:hover{background:rgba(255,255,255,.18)}
.nj-hero-visual{background:rgba(255,255,255,.06);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,.12);border-radius:20px;padding:28px;color:#fff;font-family:'Courier New',Courier,monospace}
.nj-mock-bar{display:flex;gap:6px;margin-bottom:16px}.nj-mock-dot{width:10px;height:10px;border-radius:50%;background:rgba(255,255,255,.15)}.nj-mock-dot:first-child{background:#ef4444}.nj-mock-dot:nth-child(2){background:#f59e0b}.nj-mock-dot:nth-child(3){background:#22c55e}
.nj-mock-terminal{display:flex;flex-direction:column;gap:6px}
.nj-mock-line{font-size:.72rem;line-height:1.6;color:rgba(255,255,255,.5)}
.nj-mock-line .prompt{color:#f59e0b}
.nj-mock-line .cmd{color:#fde68a}
.nj-mock-line .ok{color:#22c55e}
.nj-mock-line .dim{color:rgba(255,255,255,.25)}
.nj-mock-line .url{color:#93c5fd;text-decoration:underline}
@media(max-width:860px){.nj-hero-inner{grid-template-columns:1fr;text-align:center}.nj-hero p{margin:0 auto 28px}.nj-hero-actions{justify-content:center}.nj-hero-visual{display:none}}

/* ── Stats ── */
.nj-stats{background:#1c1917;padding:36px 0}
.nj-stats-inner{max-width:1100px;margin:0 auto;display:grid;grid-template-columns:repeat(4,1fr);text-align:center;gap:0}
.nj-stat{padding:24px 16px;border-right:1px solid rgba(255,255,255,.06)}.nj-stat:last-child{border:none}
.nj-stat h3{font-size:2rem;font-weight:900;color:#fde68a;margin-bottom:4px}.nj-stat p{font-size:.8rem;color:rgba(255,255,255,.4)}
@media(max-width:640px){.nj-stats-inner{grid-template-columns:1fr 1fr}.nj-stat:nth-child(2){border-right:none}}

/* ── Sections ── */
.nj-section{padding:80px 24px}.nj-section.alt{background:#f8fafc}.nj-section.dark{background:#1c1917;color:#fff}
.nj-inner{max-width:1100px;margin:0 auto}
.nj-label{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:#d97706;margin-bottom:10px}
.nj-title{font-size:clamp(22px,3.5vw,34px);font-weight:800;color:#0f172a;margin-bottom:10px;letter-spacing:-.02em;line-height:1.15}
.nj-sub{font-size:15px;color:#64748b;line-height:1.75;max-width:560px}

/* ── Features Grid ── */
.nj-features{display:grid;grid-template-columns:repeat(3,1fr);gap:2px;background:#e2e8f0;border:1px solid #e2e8f0;border-radius:18px;overflow:hidden;margin-top:40px}
.nj-feat{background:#fff;padding:32px 24px;transition:background .2s}
.nj-feat:hover{background:#fef3c7}
.nj-feat-icon{width:48px;height:48px;background:#fef3c7;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:24px;margin-bottom:16px}
.nj-feat h3{font-size:.95rem;font-weight:700;margin-bottom:6px}.nj-feat p{font-size:.85rem;color:#64748b;line-height:1.6}
@media(max-width:860px){.nj-features{grid-template-columns:1fr 1fr}}
@media(max-width:560px){.nj-features{grid-template-columns:1fr}}

/* ── How it works ── */
.nj-steps{display:flex;align-items:flex-start;justify-content:center;gap:16px;margin-top:40px}
.nj-step{text-align:center;flex:1;max-width:260px}
.nj-step-num{width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,#d97706,#f59e0b);color:#fff;font-size:22px;font-weight:800;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;box-shadow:0 4px 20px rgba(217,119,6,.3)}
.nj-step h3{font-size:15px;font-weight:700;margin-bottom:6px}.nj-step p{font-size:13px;color:rgba(255,255,255,.5)}
.nj-step-arrow{color:rgba(255,255,255,.15);font-size:28px;padding-top:14px;flex-shrink:0}
@media(max-width:768px){.nj-steps{flex-direction:column;align-items:center}.nj-step-arrow{transform:rotate(90deg);padding:0}}

/* ── Comparison ── */
.nj-compare{display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-top:40px}
.nj-compare-card{border-radius:16px;padding:28px 24px}
.nj-compare-card.bad{background:#fef2f2;border:1px solid #fecaca}
.nj-compare-card.good{background:#fef3c7;border:2px solid #d97706}
.nj-compare-card h3{font-size:16px;font-weight:700;margin-bottom:14px;display:flex;align-items:center;gap:8px}
.nj-compare-card ul{list-style:none;padding:0}
.nj-compare-card ul li{padding:8px 0;font-size:13px;display:flex;align-items:flex-start;gap:8px;border-bottom:1px solid rgba(0,0,0,.04)}
@media(max-width:640px){.nj-compare{grid-template-columns:1fr}}

/* ── CTA ── */
.nj-cta-section{padding:80px 24px;background:linear-gradient(135deg,#1c1917,#78350f);text-align:center;color:#fff}
.nj-cta-section h2{font-size:clamp(24px,4vw,36px);font-weight:800;margin-bottom:12px}
.nj-cta-section p{font-size:16px;color:rgba(255,255,255,.55);max-width:500px;margin:0 auto 28px}

/* ── FAQ ── */
.nj-faq{max-width:700px;margin:40px auto 0;display:flex;flex-direction:column;gap:10px}
.nj-faq details{background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;transition:border-color .2s}
.nj-faq details[open]{border-color:#d97706}
.nj-faq summary{padding:16px 20px;font-size:14px;font-weight:600;cursor:pointer;list-style:none;display:flex;justify-content:space-between;align-items:center}
.nj-faq summary::after{content:'+';font-size:18px;color:#94a3b8;transition:transform .2s}
.nj-faq details[open] summary::after{content:'−';color:#d97706}
.nj-faq summary::-webkit-details-marker{display:none}
.nj-faq details p{padding:0 20px 16px;font-size:13px;color:#64748b;line-height:1.7}
</style>
</head>
<body>
<?php require __DIR__ . '/../_partials/navbar-publica.php'; ?>

<!-- HERO -->
<section class="nj-hero">
  <div class="glow"></div>
  <div class="nj-hero-inner">
    <div>
      <div class="nj-hero-badge"><span>⬢ Node.js App</span></div>
      <h1>Deploy simples para suas aplicações <em>Node.js</em></h1>
      <p>Hospede aplicações Node.js com banco de dados incluso, domínio customizado, Git Deploy e SSL grátis. Do push ao ar em segundos.</p>
      <div class="nj-hero-actions">
        <a href="#planos" class="nj-btn-p">Ver planos Node.js</a>
        <a href="/contato" class="nj-btn-s">Falar com a equipe</a>
      </div>
    </div>
    <div class="nj-hero-visual">
      <div class="nj-mock-bar"><div class="nj-mock-dot"></div><div class="nj-mock-dot"></div><div class="nj-mock-dot"></div></div>
      <div class="nj-mock-terminal">
        <div class="nj-mock-line"><span class="prompt">$</span> <span class="cmd">git push origin main</span></div>
        <div class="nj-mock-line"><span class="dim">Enumerating objects: 42, done.</span></div>
        <div class="nj-mock-line"><span class="dim">Compressing objects: 100% (38/38)</span></div>
        <div class="nj-mock-line"><span class="dim">remote: Installing dependencies...</span></div>
        <div class="nj-mock-line"><span class="dim">remote: npm install ✓</span></div>
        <div class="nj-mock-line"><span class="dim">remote: Building application...</span></div>
        <div class="nj-mock-line"><span class="ok">✓ Deploy concluído com sucesso!</span></div>
        <div class="nj-mock-line"><span class="dim">→</span> <span class="url">https://meuapp.exemplo.com</span></div>
        <div class="nj-mock-line" style="margin-top:6px"><span class="prompt">$</span> <span class="dim" style="animation:blink 1s infinite">_</span></div>
      </div>
    </div>
  </div>
</section>

<!-- STATS -->
<div class="nj-stats">
  <div class="nj-stats-inner">
    <div class="nj-stat"><h3>Git</h3><p>Deploy integrado</p></div>
    <div class="nj-stat"><h3>99.9%</h3><p>Uptime garantido</p></div>
    <div class="nj-stat"><h3>WS</h3><p>WebSocket nativo</p></div>
    <div class="nj-stat"><h3>SSL</h3><p>Grátis em todos os planos</p></div>
  </div>
</div>

<!-- COMPARAÇÃO -->
<section class="nj-section">
  <div class="nj-inner">
    <div style="text-align:center;margin-bottom:8px;">
      <div class="nj-label">Por que escolher?</div>
      <h2 class="nj-title">Deploy manual vs Node.js App <?php echo View::e($_nome); ?></h2>
      <p class="nj-sub" style="margin:0 auto;">Compare configurar um servidor manualmente com nossa plataforma otimizada para Node.js.</p>
    </div>
    <div class="nj-compare">
      <div class="nj-compare-card bad">
        <h3>❌ Deploy manual</h3>
        <ul>
          <li>❌ Configurar servidor, Nginx e PM2 na mão</li>
          <li>❌ Gerenciar SSL e renovações manualmente</li>
          <li>❌ Sem rollback fácil em caso de erro</li>
          <li>❌ Monitoramento e logs por conta própria</li>
          <li>❌ Banco de dados separado e pago</li>
          <li>❌ Horas perdidas com DevOps</li>
        </ul>
      </div>
      <div class="nj-compare-card good">
        <h3>✅ Node.js App <?php echo View::e($_nome); ?></h3>
        <ul>
          <li>✅ Git push e pronto — deploy automático</li>
          <li>✅ SSL grátis e automático em todos os domínios</li>
          <li>✅ Logs em tempo real no painel</li>
          <li>✅ Variáveis de ambiente seguras</li>
          <li>✅ Banco de dados incluso no plano</li>
          <li>✅ Foque no código, não na infra</li>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section class="nj-section alt">
  <div class="nj-inner">
    <div style="text-align:center;">
      <div class="nj-label">Tudo incluso</div>
      <h2 class="nj-title">Tudo que sua aplicação Node.js precisa</h2>
    </div>
    <div class="nj-features">
      <div class="nj-feat"><div class="nj-feat-icon">🚀</div><h3>Git Deploy</h3><p>Conecte seu repositório e faça deploy a cada push. Suporte a GitHub, GitLab e Bitbucket.</p></div>
      <div class="nj-feat"><div class="nj-feat-icon">🗄️</div><h3>Banco de dados incluso</h3><p>MySQL dedicado incluso no plano. Crie, gerencie e acesse pelo painel ou ferramentas externas.</p></div>
      <div class="nj-feat"><div class="nj-feat-icon">🌐</div><h3>Domínio com SSL</h3><p>Conecte seu domínio próprio com SSL Let's Encrypt automático. HTTPS sem custo extra.</p></div>
      <div class="nj-feat"><div class="nj-feat-icon">📦</div><h3>Container isolado</h3><p>Sua aplicação roda em container dedicado com recursos garantidos. Sem interferência de outros apps.</p></div>
      <div class="nj-feat"><div class="nj-feat-icon">🔑</div><h3>Variáveis de ambiente</h3><p>Gerencie variáveis de ambiente de forma segura pelo painel. Sem expor secrets no código.</p></div>
      <div class="nj-feat"><div class="nj-feat-icon">📋</div><h3>Logs em tempo real</h3><p>Acompanhe stdout e stderr da sua aplicação em tempo real direto no painel de controle.</p></div>
      <div class="nj-feat"><div class="nj-feat-icon">📊</div><h3>Painel intuitivo</h3><p>Gerencie deploy, domínios, banco de dados, variáveis e logs em um painel moderno e unificado.</p></div>
      <div class="nj-feat"><div class="nj-feat-icon">🛡️</div><h3>Proteção DDoS</h3><p>Infraestrutura com proteção contra ataques DDoS nativa. Sua aplicação sempre disponível.</p></div>
      <div class="nj-feat"><div class="nj-feat-icon">💬</div><h3>Suporte técnico</h3><p>Equipe que entende Node.js de verdade. Chat, ticket e e-mail disponíveis para qualquer dúvida.</p></div>
    </div>
  </div>
</section>

<!-- COMO FUNCIONA -->
<section class="nj-section dark">
  <div class="nj-inner">
    <div style="text-align:center;margin-bottom:8px;">
      <div class="nj-label" style="color:#fde68a;">Como funciona</div>
      <h2 class="nj-title" style="color:#fff;">Online em 3 passos</h2>
    </div>
    <div class="nj-steps">
      <div class="nj-step"><div class="nj-step-num">1</div><h3 style="color:#fff;">Conecte o repositório</h3><p>Vincule seu repositório Git ao painel com um clique.</p></div>
      <div class="nj-step-arrow">→</div>
      <div class="nj-step"><div class="nj-step-num">2</div><h3 style="color:#fff;">Push seu código</h3><p>Faça git push e o build + deploy acontecem automaticamente.</p></div>
      <div class="nj-step-arrow">→</div>
      <div class="nj-step"><div class="nj-step-num">3</div><h3 style="color:#fff;">App online</h3><p>Sua aplicação Node.js está no ar com SSL e domínio configurados.</p></div>
    </div>
  </div>
</section>

<!-- PLANOS -->
<?php $_accent = '#d97706'; $_plan_type = 'nodejs'; $_cta_base = '/cliente/planos/checkout?plan_id='; require __DIR__ . '/_planos-section.php'; ?>

<!-- FAQ -->
<section class="nj-section">
  <div class="nj-inner">
    <div style="text-align:center;">
      <div class="nj-label">Perguntas frequentes</div>
      <h2 class="nj-title">Dúvidas sobre Node.js App</h2>
    </div>
    <div class="nj-faq">
      <details><summary>Quais versões do Node.js são suportadas?</summary><p>Suportamos as versões LTS mais recentes do Node.js (18, 20, 22). Você pode especificar a versão desejada no package.json ou nas configurações do painel.</p></details>
      <details><summary>Posso usar Next.js, Express ou NestJS?</summary><p>Sim. Qualquer framework Node.js é suportado: Next.js, Express, Fastify, NestJS, Koa, Hapi e outros. Basta configurar o script start no package.json.</p></details>
      <details><summary>WebSocket é suportado?</summary><p>Sim. Nossa infraestrutura suporta WebSocket nativamente. Ideal para aplicações em tempo real como chats, dashboards e jogos.</p></details>
      <details><summary>Posso escalar minha aplicação?</summary><p>Sim. Faça upgrade de plano a qualquer momento para mais CPU, RAM e armazenamento. Seus dados e configurações são preservados.</p></details>
      <details><summary>Como configuro variáveis de ambiente?</summary><p>Pelo painel de controle, na seção de variáveis de ambiente. Adicione, edite e remova variáveis de forma segura sem expor no código.</p></details>
      <details><summary>Qual banco de dados está incluso?</summary><p>Cada plano inclui banco de dados MySQL dedicado. Você pode acessar pelo painel, phpMyAdmin ou conectar via string de conexão na sua aplicação.</p></details>
    </div>
  </div>
</section>

<!-- CTA FINAL -->
<section class="nj-cta-section">
  <h2>Pronto para deployar sua aplicação?</h2>
  <p>Do git push ao ar em segundos. Banco de dados, SSL e domínio inclusos.</p>
  <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
    <a href="#planos" class="nj-btn-p">Ver planos</a>
    <a href="/contato" class="nj-btn-s">Falar com a equipe</a>
  </div>
</section>

<?php require __DIR__ . '/../_partials/footer.php'; ?>
<?php require __DIR__ . '/../_partials/chat-widget.php'; ?>
</body>
</html>
