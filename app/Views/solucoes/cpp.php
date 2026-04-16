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
<title>C/C++ App — <?php echo View::e($_nome); ?></title>
<?php require __DIR__ . '/../_partials/seo.php'; ?>
<?php require __DIR__ . '/../_partials/estilo.php'; ?>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:system-ui,-apple-system,'Segoe UI',Roboto,sans-serif;background:#fff;color:#0f172a}

/* ── Hero C/C++ ── */
.cp-hero{background:linear-gradient(135deg,#1a1a2e 0%,#2d1b3d 30%,#831843 60%,#db2777 85%,#ec4899 100%);position:relative;overflow:hidden;padding:100px 0 80px}
.cp-hero::before{content:'';position:absolute;inset:0;background-image:radial-gradient(circle,rgba(255,255,255,.08) 1px,transparent 1px);background-size:40px 40px;pointer-events:none}
.cp-hero .glow{position:absolute;width:600px;height:600px;background:radial-gradient(circle,rgba(236,72,153,.35),transparent 65%);top:-200px;right:-100px;pointer-events:none}
.cp-hero-inner{position:relative;z-index:1;max-width:1100px;margin:0 auto;padding:0 24px;display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:center}
.cp-hero-badge{display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.15);padding:5px 14px;border-radius:99px;margin-bottom:20px}
.cp-hero-badge span{font-size:.72rem;font-weight:600;color:rgba(255,255,255,.8);letter-spacing:.06em;text-transform:uppercase}
.cp-hero h1{font-size:clamp(1.8rem,4.5vw,3.2rem);font-weight:800;color:#fff;line-height:1.12;margin-bottom:18px;letter-spacing:-.02em}
.cp-hero h1 em{font-style:italic;color:#fbcfe8}
.cp-hero p{font-size:1rem;color:rgba(255,255,255,.6);line-height:1.8;margin-bottom:28px;max-width:480px}
.cp-hero-actions{display:flex;gap:12px;flex-wrap:wrap}
.cp-btn-p{display:inline-flex;align-items:center;gap:8px;padding:14px 28px;border-radius:12px;font-size:.9rem;font-weight:700;background:#fff;color:#db2777;transition:transform .15s;text-decoration:none}
.cp-btn-p:hover{transform:translateY(-2px)}
.cp-btn-s{display:inline-flex;align-items:center;gap:8px;padding:14px 28px;border-radius:12px;font-size:.9rem;font-weight:700;background:rgba(255,255,255,.1);color:#fff;border:1.5px solid rgba(255,255,255,.2);text-decoration:none;transition:background .15s}
.cp-btn-s:hover{background:rgba(255,255,255,.18)}
.cp-hero-visual{background:rgba(255,255,255,.06);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,.12);border-radius:20px;padding:28px;color:#fff;font-family:'Courier New',Courier,monospace}
.cp-mock-bar{display:flex;gap:6px;margin-bottom:16px}.cp-mock-dot{width:10px;height:10px;border-radius:50%;background:rgba(255,255,255,.15)}.cp-mock-dot:first-child{background:#ef4444}.cp-mock-dot:nth-child(2){background:#f59e0b}.cp-mock-dot:nth-child(3){background:#22c55e}
.cp-mock-terminal{display:flex;flex-direction:column;gap:6px}
.cp-mock-line{font-size:.72rem;line-height:1.6;color:rgba(255,255,255,.5)}
.cp-mock-line .prompt{color:#ec4899}
.cp-mock-line .cmd{color:#fbcfe8}
.cp-mock-line .ok{color:#22c55e}
.cp-mock-line .warn{color:#f59e0b}
.cp-mock-line .dim{color:rgba(255,255,255,.25)}
.cp-mock-line .url{color:#93c5fd;text-decoration:underline}
@media(max-width:860px){.cp-hero-inner{grid-template-columns:1fr;text-align:center}.cp-hero p{margin:0 auto 28px}.cp-hero-actions{justify-content:center}.cp-hero-visual{display:none}}

/* ── Stats ── */
.cp-stats{background:#1a1a2e;padding:36px 0}
.cp-stats-inner{max-width:1100px;margin:0 auto;display:grid;grid-template-columns:repeat(4,1fr);text-align:center;gap:0}
.cp-stat{padding:24px 16px;border-right:1px solid rgba(255,255,255,.06)}.cp-stat:last-child{border:none}
.cp-stat h3{font-size:2rem;font-weight:900;color:#fbcfe8;margin-bottom:4px}.cp-stat p{font-size:.8rem;color:rgba(255,255,255,.4)}
@media(max-width:640px){.cp-stats-inner{grid-template-columns:1fr 1fr}.cp-stat:nth-child(2){border-right:none}}

/* ── Sections ── */
.cp-section{padding:80px 24px}.cp-section.alt{background:#f8fafc}.cp-section.dark{background:#1a1a2e;color:#fff}
.cp-inner{max-width:1100px;margin:0 auto}
.cp-label{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:#db2777;margin-bottom:10px}
.cp-title{font-size:clamp(22px,3.5vw,34px);font-weight:800;color:#0f172a;margin-bottom:10px;letter-spacing:-.02em;line-height:1.15}
.cp-sub{font-size:15px;color:#64748b;line-height:1.75;max-width:560px}

/* ── Features Grid ── */
.cp-features{display:grid;grid-template-columns:repeat(3,1fr);gap:2px;background:#e2e8f0;border:1px solid #e2e8f0;border-radius:18px;overflow:hidden;margin-top:40px}
.cp-feat{background:#fff;padding:32px 24px;transition:background .2s}
.cp-feat:hover{background:#fce7f3}
.cp-feat-icon{width:48px;height:48px;background:#fce7f3;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:24px;margin-bottom:16px}
.cp-feat h3{font-size:.95rem;font-weight:700;margin-bottom:6px}.cp-feat p{font-size:.85rem;color:#64748b;line-height:1.6}
@media(max-width:860px){.cp-features{grid-template-columns:1fr 1fr}}
@media(max-width:560px){.cp-features{grid-template-columns:1fr}}

/* ── How it works ── */
.cp-steps{display:flex;align-items:flex-start;justify-content:center;gap:16px;margin-top:40px}
.cp-step{text-align:center;flex:1;max-width:260px}
.cp-step-num{width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,#db2777,#ec4899);color:#fff;font-size:22px;font-weight:800;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;box-shadow:0 4px 20px rgba(219,39,119,.3)}
.cp-step h3{font-size:15px;font-weight:700;margin-bottom:6px}.cp-step p{font-size:13px;color:rgba(255,255,255,.5)}
.cp-step-arrow{color:rgba(255,255,255,.15);font-size:28px;padding-top:14px;flex-shrink:0}
@media(max-width:768px){.cp-steps{flex-direction:column;align-items:center}.cp-step-arrow{transform:rotate(90deg);padding:0}}

/* ── Comparison ── */
.cp-compare{display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-top:40px}
.cp-compare-card{border-radius:16px;padding:28px 24px}
.cp-compare-card.bad{background:#fef2f2;border:1px solid #fecaca}
.cp-compare-card.good{background:#fce7f3;border:2px solid #db2777}
.cp-compare-card h3{font-size:16px;font-weight:700;margin-bottom:14px;display:flex;align-items:center;gap:8px}
.cp-compare-card ul{list-style:none;padding:0}
.cp-compare-card ul li{padding:8px 0;font-size:13px;display:flex;align-items:flex-start;gap:8px;border-bottom:1px solid rgba(0,0,0,.04)}
@media(max-width:640px){.cp-compare{grid-template-columns:1fr}}

/* ── CTA ── */
.cp-cta-section{padding:80px 24px;background:linear-gradient(135deg,#1a1a2e,#831843);text-align:center;color:#fff}
.cp-cta-section h2{font-size:clamp(24px,4vw,36px);font-weight:800;margin-bottom:12px}
.cp-cta-section p{font-size:16px;color:rgba(255,255,255,.55);max-width:500px;margin:0 auto 28px}

/* ── FAQ ── */
.cp-faq{max-width:700px;margin:40px auto 0;display:flex;flex-direction:column;gap:10px}
.cp-faq details{background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;transition:border-color .2s}
.cp-faq details[open]{border-color:#db2777}
.cp-faq summary{padding:16px 20px;font-size:14px;font-weight:600;cursor:pointer;list-style:none;display:flex;justify-content:space-between;align-items:center}
.cp-faq summary::after{content:'+';font-size:18px;color:#94a3b8;transition:transform .2s}
.cp-faq details[open] summary::after{content:'−';color:#db2777}
.cp-faq summary::-webkit-details-marker{display:none}
.cp-faq details p{padding:0 20px 16px;font-size:13px;color:#64748b;line-height:1.7}
</style>
</head>
<body>
<?php require __DIR__ . '/../_partials/navbar-publica.php'; ?>

<!-- HERO -->
<section class="cp-hero">
  <div class="glow"></div>
  <div class="cp-hero-inner">
    <div>
      <div class="cp-hero-badge"><span>⚙️ C/C++ App</span></div>
      <h1>Aplicações C/C++ de <em>alta performance</em> na nuvem</h1>
      <p>Hospede aplicações C/C++ compiladas com builds automatizados, Git Deploy, banco de dados e SSL grátis. Performance nativa sem complicação.</p>
      <div class="cp-hero-actions">
        <a href="#planos" class="cp-btn-p">Ver planos C/C++</a>
        <a href="/contato" class="cp-btn-s">Falar com a equipe</a>
      </div>
    </div>
    <div class="cp-hero-visual">
      <div class="cp-mock-bar"><div class="cp-mock-dot"></div><div class="cp-mock-dot"></div><div class="cp-mock-dot"></div></div>
      <div class="cp-mock-terminal">
        <div class="cp-mock-line"><span class="prompt">$</span> <span class="cmd">git push origin main</span></div>
        <div class="cp-mock-line"><span class="dim">remote: Detecting project type... C++</span></div>
        <div class="cp-mock-line"><span class="dim">remote: Running cmake -B build .</span></div>
        <div class="cp-mock-line"><span class="dim">remote: -- The CXX compiler: /usr/bin/g++-13</span></div>
        <div class="cp-mock-line"><span class="dim">remote: -- Found Boost 1.83.0</span></div>
        <div class="cp-mock-line"><span class="dim">remote: -- Found OpenSSL 3.0.11</span></div>
        <div class="cp-mock-line"><span class="warn">remote: Building with -O2 -std=c++20</span></div>
        <div class="cp-mock-line"><span class="dim">remote: [100%] Linking CXX executable app</span></div>
        <div class="cp-mock-line"><span class="ok">✓ Build + deploy concluído!</span></div>
        <div class="cp-mock-line"><span class="dim">→</span> <span class="url">https://meuapp.exemplo.com</span></div>
      </div>
    </div>
  </div>
</section>

<!-- STATS -->
<div class="cp-stats">
  <div class="cp-stats-inner">
    <div class="cp-stat"><h3>GCC/G++</h3><p>Compiladores atualizados</p></div>
    <div class="cp-stat"><h3>99.9%</h3><p>Uptime garantido</p></div>
    <div class="cp-stat"><h3>CMake</h3><p>Build system integrado</p></div>
    <div class="cp-stat"><h3>Nativa</h3><p>Performance nativa</p></div>
  </div>
</div>

<!-- COMPARAÇÃO -->
<section class="cp-section">
  <div class="cp-inner">
    <div style="text-align:center;margin-bottom:8px;">
      <div class="cp-label">Por que escolher?</div>
      <h2 class="cp-title">Servidor manual vs C/C++ App <?php echo View::e($_nome); ?></h2>
      <p class="cp-sub" style="margin:0 auto;">Compare configurar um servidor do zero com nossa plataforma otimizada para aplicações C/C++.</p>
    </div>
    <div class="cp-compare">
      <div class="cp-compare-card bad">
        <h3>❌ Servidor manual</h3>
        <ul>
          <li>❌ Instalar compiladores e dependências na mão</li>
          <li>❌ Configurar build system e scripts de deploy</li>
          <li>❌ Gerenciar Nginx, systemd e SSL manualmente</li>
          <li>❌ Sem rollback fácil em caso de falha</li>
          <li>❌ Monitoramento e logs por conta própria</li>
          <li>❌ Horas perdidas com infraestrutura</li>
        </ul>
      </div>
      <div class="cp-compare-card good">
        <h3>✅ C/C++ App <?php echo View::e($_nome); ?></h3>
        <ul>
          <li>✅ GCC/G++ e CMake pré-configurados</li>
          <li>✅ Build automático a cada git push</li>
          <li>✅ SSL grátis e automático em todos os domínios</li>
          <li>✅ Logs em tempo real no painel</li>
          <li>✅ Bibliotecas populares disponíveis (Boost, OpenSSL)</li>
          <li>✅ Foque no código, não na infra</li>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section class="cp-section alt">
  <div class="cp-inner">
    <div style="text-align:center;">
      <div class="cp-label">Tudo incluso</div>
      <h2 class="cp-title">Tudo que sua aplicação C/C++ precisa</h2>
    </div>
    <div class="cp-features">
      <div class="cp-feat"><div class="cp-feat-icon">🔨</div><h3>Build automatizado</h3><p>GCC/G++ e CMake pré-configurados. Faça push e o build acontece automaticamente com otimizações de produção.</p></div>
      <div class="cp-feat"><div class="cp-feat-icon">📦</div><h3>Container isolado</h3><p>Sua aplicação roda em container dedicado com recursos garantidos. Isolamento total de outros apps.</p></div>
      <div class="cp-feat"><div class="cp-feat-icon">🗄️</div><h3>Banco de dados</h3><p>MySQL dedicado incluso no plano. Ideal para aplicações que precisam de persistência de dados.</p></div>
      <div class="cp-feat"><div class="cp-feat-icon">🌐</div><h3>Domínio com SSL</h3><p>Conecte seu domínio próprio com SSL Let's Encrypt automático. HTTPS sem custo extra.</p></div>
      <div class="cp-feat"><div class="cp-feat-icon">🚀</div><h3>Git Deploy</h3><p>Conecte seu repositório e faça deploy a cada push. Build e restart automáticos.</p></div>
      <div class="cp-feat"><div class="cp-feat-icon">📚</div><h3>Bibliotecas populares</h3><p>Boost, OpenSSL, libcurl, zlib e outras bibliotecas populares disponíveis. Solicite novas pelo suporte.</p></div>
      <div class="cp-feat"><div class="cp-feat-icon">📋</div><h3>Logs em tempo real</h3><p>Acompanhe stdout, stderr e logs de build da sua aplicação em tempo real direto no painel.</p></div>
      <div class="cp-feat"><div class="cp-feat-icon">🛡️</div><h3>Proteção DDoS</h3><p>Infraestrutura com proteção contra ataques DDoS nativa. Sua aplicação sempre disponível.</p></div>
      <div class="cp-feat"><div class="cp-feat-icon">💬</div><h3>Suporte técnico</h3><p>Equipe pronta para ajudar com build, deploy e configuração. Chat, ticket e e-mail disponíveis.</p></div>
    </div>
  </div>
</section>

<!-- COMO FUNCIONA -->
<section class="cp-section dark">
  <div class="cp-inner">
    <div style="text-align:center;margin-bottom:8px;">
      <div class="cp-label" style="color:#fbcfe8;">Como funciona</div>
      <h2 class="cp-title" style="color:#fff;">Online em 3 passos</h2>
    </div>
    <div class="cp-steps">
      <div class="cp-step"><div class="cp-step-num">1</div><h3 style="color:#fff;">Push seu código</h3><p>Envie seu código C/C++ com CMakeLists.txt ou Makefile para o repositório.</p></div>
      <div class="cp-step-arrow">→</div>
      <div class="cp-step"><div class="cp-step-num">2</div><h3 style="color:#fff;">Build automático</h3><p>O compilador detecta o projeto, instala dependências e compila com otimizações.</p></div>
      <div class="cp-step-arrow">→</div>
      <div class="cp-step"><div class="cp-step-num">3</div><h3 style="color:#fff;">App online</h3><p>Seu binário é executado em container isolado com SSL e domínio configurados.</p></div>
    </div>
  </div>
</section>

<!-- PLANOS -->
<?php $_accent = '#db2777'; $_plan_type = 'cpp'; $_cta_base = '/cliente/planos/checkout?plan_id='; require __DIR__ . '/_planos-section.php'; ?>

<!-- FAQ -->
<section class="cp-section">
  <div class="cp-inner">
    <div style="text-align:center;">
      <div class="cp-label">Perguntas frequentes</div>
      <h2 class="cp-title">Dúvidas sobre C/C++ App</h2>
    </div>
    <div class="cp-faq">
      <details><summary>Quais compiladores estão disponíveis?</summary><p>Disponibilizamos GCC/G++ 13 com suporte a C++20 e C17. O ambiente inclui CMake, Make e ferramentas de build essenciais pré-configuradas.</p></details>
      <details><summary>Posso usar bibliotecas externas como Boost e OpenSSL?</summary><p>Sim. Boost, OpenSSL, libcurl, zlib, libpq e outras bibliotecas populares estão disponíveis. Precisa de uma biblioteca específica? Solicite pelo suporte.</p></details>
      <details><summary>Como funciona o deploy de uma aplicação C/C++?</summary><p>Faça push do código com um CMakeLists.txt ou Makefile. O sistema detecta o projeto, compila com otimizações de produção e inicia o binário automaticamente.</p></details>
      <details><summary>WebSocket é suportado?</summary><p>Sim. Sua aplicação pode abrir portas e aceitar conexões WebSocket. A infraestrutura faz proxy reverso automaticamente para seu binário.</p></details>
      <details><summary>Posso escalar minha aplicação?</summary><p>Sim. Faça upgrade de plano a qualquer momento para mais CPU, RAM e armazenamento. O binário é recompilado e reiniciado sem perda de dados.</p></details>
      <details><summary>Como faço debug de problemas de build?</summary><p>Os logs de build completos ficam disponíveis no painel em tempo real. Você pode ver cada etapa do cmake, compilação e linking para identificar erros.</p></details>
    </div>
  </div>
</section>

<!-- CTA FINAL -->
<section class="cp-cta-section">
  <h2>Pronto para deployar sua aplicação C/C++?</h2>
  <p>Performance nativa na nuvem. Build automatizado, SSL e domínio inclusos.</p>
  <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
    <a href="#planos" class="cp-btn-p">Ver planos</a>
    <a href="/contato" class="cp-btn-s">Falar com a equipe</a>
  </div>
</section>

<?php require __DIR__ . '/../_partials/footer.php'; ?>
<?php require __DIR__ . '/../_partials/chat-widget.php'; ?>
</body>
</html>
