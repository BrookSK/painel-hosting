<?php
declare(strict_types=1);
use LRV\Core\I18n;
use LRV\Core\View;
use LRV\Core\SistemaConfig;
$_nome = SistemaConfig::nome();
?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Deploy Automático — <?php echo View::e($_nome); ?></title>
<?php require __DIR__ . '/../_partials/seo.php'; ?>
<?php require __DIR__ . '/../_partials/estilo.php'; ?>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:system-ui,-apple-system,'Segoe UI',Roboto,sans-serif;background:#fff;color:#0f172a}

/* ── Hero Deploy ── */
.da-hero{background:linear-gradient(135deg,#2e1065 0%,#5b21b6 30%,#7c3aed 60%,#8b5cf6 85%,#a78bfa 100%);position:relative;overflow:hidden;padding:100px 0 80px}
.da-hero::before{content:'';position:absolute;inset:0;background-image:radial-gradient(circle,rgba(255,255,255,.06) 1px,transparent 1px);background-size:40px 40px;pointer-events:none}
.da-hero .glow{position:absolute;width:600px;height:600px;background:radial-gradient(circle,rgba(139,92,246,.4),transparent 65%);top:-200px;right:-100px;pointer-events:none}
.da-hero-inner{position:relative;z-index:1;max-width:1100px;margin:0 auto;padding:0 24px;display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:center}
.da-hero-badge{display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.15);padding:5px 14px;border-radius:99px;margin-bottom:20px}
.da-hero-badge span{font-size:.72rem;font-weight:600;color:rgba(255,255,255,.8);letter-spacing:.06em;text-transform:uppercase}
.da-hero h1{font-size:clamp(1.8rem,4.5vw,3.2rem);font-weight:800;color:#fff;line-height:1.12;margin-bottom:18px;letter-spacing:-.02em}
.da-hero h1 em{font-style:italic;color:#ddd6fe}
.da-hero p{font-size:1rem;color:rgba(255,255,255,.6);line-height:1.8;margin-bottom:28px;max-width:480px}
.da-hero-actions{display:flex;gap:12px;flex-wrap:wrap}
.da-btn-p{display:inline-flex;align-items:center;gap:8px;padding:14px 28px;border-radius:12px;font-size:.9rem;font-weight:700;background:#fff;color:#7c3aed;transition:transform .15s;text-decoration:none}
.da-btn-p:hover{transform:translateY(-2px)}
.da-btn-s{display:inline-flex;align-items:center;gap:8px;padding:14px 28px;border-radius:12px;font-size:.9rem;font-weight:700;background:rgba(255,255,255,.1);color:#fff;border:1.5px solid rgba(255,255,255,.2);text-decoration:none;transition:background .15s}
.da-btn-s:hover{background:rgba(255,255,255,.18)}

/* Hero Visual — Pipeline */
.da-hero-visual{background:rgba(255,255,255,.06);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,.12);border-radius:20px;padding:28px;color:#fff}
.da-mock-bar{display:flex;gap:6px;margin-bottom:16px}.da-mock-dot{width:10px;height:10px;border-radius:50%;background:rgba(255,255,255,.15)}.da-mock-dot:first-child{background:#ef4444}.da-mock-dot:nth-child(2){background:#f59e0b}.da-mock-dot:nth-child(3){background:#22c55e}
.da-pipeline{display:flex;align-items:center;gap:0;margin-bottom:16px;flex-wrap:wrap;justify-content:center}
.da-pipe-step{background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12);border-radius:10px;padding:10px 14px;text-align:center;min-width:80px}
.da-pipe-step .icon{font-size:1.2rem;margin-bottom:4px}
.da-pipe-step .txt{font-size:.65rem;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.05em}
.da-pipe-step.active{border-color:#a78bfa;background:rgba(139,92,246,.15)}
.da-pipe-step.active .txt{color:#ddd6fe}
.da-pipe-arrow{color:rgba(255,255,255,.2);font-size:16px;padding:0 4px}
.da-pipe-log{background:rgba(0,0,0,.3);border-radius:8px;padding:12px 14px;font-family:'Courier New',monospace;font-size:.7rem;color:rgba(255,255,255,.5);line-height:1.8}
.da-pipe-log .green{color:#4ade80}.da-pipe-log .violet{color:#a78bfa}
@media(max-width:860px){.da-hero-inner{grid-template-columns:1fr;text-align:center}.da-hero p{margin:0 auto 28px}.da-hero-actions{justify-content:center}.da-hero-visual{display:none}}

/* ── Stats ── */
.da-stats{background:#2e1065;padding:36px 0}
.da-stats-inner{max-width:1100px;margin:0 auto;display:grid;grid-template-columns:repeat(4,1fr);text-align:center;gap:0}
.da-stat{padding:24px 16px;border-right:1px solid rgba(255,255,255,.06)}.da-stat:last-child{border:none}
.da-stat h3{font-size:2rem;font-weight:900;color:#ddd6fe;margin-bottom:4px}.da-stat p{font-size:.8rem;color:rgba(255,255,255,.4)}
@media(max-width:640px){.da-stats-inner{grid-template-columns:1fr 1fr}.da-stat:nth-child(2){border-right:none}}

/* ── Sections ── */
.da-section{padding:80px 24px}.da-section.alt{background:#faf5ff}.da-section.dark{background:#2e1065;color:#fff}
.da-inner{max-width:1100px;margin:0 auto}
.da-label{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:#8b5cf6;margin-bottom:10px}
.da-title{font-size:clamp(22px,3.5vw,34px);font-weight:800;color:#0f172a;margin-bottom:10px;letter-spacing:-.02em;line-height:1.15}
.da-sub{font-size:15px;color:#64748b;line-height:1.75;max-width:560px}

/* ── Features Grid ── */
.da-features{display:grid;grid-template-columns:repeat(3,1fr);gap:2px;background:#e2e8f0;border:1px solid #e2e8f0;border-radius:18px;overflow:hidden;margin-top:40px}
.da-feat{background:#fff;padding:32px 24px;transition:background .2s}
.da-feat:hover{background:#f5f3ff}
.da-feat-icon{width:48px;height:48px;background:#f5f3ff;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:24px;margin-bottom:16px}
.da-feat h3{font-size:.95rem;font-weight:700;margin-bottom:6px}.da-feat p{font-size:.85rem;color:#64748b;line-height:1.6}
@media(max-width:860px){.da-features{grid-template-columns:1fr 1fr}}
@media(max-width:560px){.da-features{grid-template-columns:1fr}}

/* ── How it works ── */
.da-steps{display:flex;align-items:flex-start;justify-content:center;gap:16px;margin-top:40px}
.da-step{text-align:center;flex:1;max-width:260px}
.da-step-num{width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,#7c3aed,#a78bfa);color:#fff;font-size:22px;font-weight:800;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;box-shadow:0 4px 20px rgba(124,58,237,.3)}
.da-step h3{font-size:15px;font-weight:700;margin-bottom:6px}.da-step p{font-size:13px;color:rgba(255,255,255,.5)}
.da-step-arrow{color:rgba(255,255,255,.15);font-size:28px;padding-top:14px;flex-shrink:0}
@media(max-width:768px){.da-steps{flex-direction:column;align-items:center}.da-step-arrow{transform:rotate(90deg);padding:0}}

/* ── Comparison ── */
.da-compare{display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-top:40px}
.da-compare-card{border-radius:16px;padding:28px 24px}
.da-compare-card.bad{background:#fef2f2;border:1px solid #fecaca}
.da-compare-card.good{background:#f5f3ff;border:2px solid #8b5cf6}
.da-compare-card h3{font-size:16px;font-weight:700;margin-bottom:14px;display:flex;align-items:center;gap:8px}
.da-compare-card ul{list-style:none;padding:0}
.da-compare-card ul li{padding:8px 0;font-size:13px;display:flex;align-items:flex-start;gap:8px;border-bottom:1px solid rgba(0,0,0,.04)}
@media(max-width:640px){.da-compare{grid-template-columns:1fr}}

/* ── CTA ── */
.da-cta-section{padding:80px 24px;background:linear-gradient(135deg,#2e1065,#5b21b6);text-align:center;color:#fff}
.da-cta-section h2{font-size:clamp(24px,4vw,36px);font-weight:800;margin-bottom:12px}
.da-cta-section p{font-size:16px;color:rgba(255,255,255,.55);max-width:500px;margin:0 auto 28px}

/* ── FAQ ── */
.da-faq{max-width:700px;margin:40px auto 0;display:flex;flex-direction:column;gap:10px}
.da-faq details{background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;transition:border-color .2s}
.da-faq details[open]{border-color:#8b5cf6}
.da-faq summary{padding:16px 20px;font-size:14px;font-weight:600;cursor:pointer;list-style:none;display:flex;justify-content:space-between;align-items:center}
.da-faq summary::after{content:'+';font-size:18px;color:#94a3b8;transition:transform .2s}
.da-faq details[open] summary::after{content:'−';color:#8b5cf6}
.da-faq summary::-webkit-details-marker{display:none}
.da-faq details p{padding:0 20px 16px;font-size:13px;color:#64748b;line-height:1.7}
</style>
</head>
<body>
<?php require __DIR__ . '/../_partials/navbar-publica.php'; ?>

<!-- HERO -->
<section class="da-hero">
  <div class="glow"></div>
  <div class="da-hero-inner">
    <div>
      <div class="da-hero-badge"><span>🚀 Deploy Automático</span></div>
      <h1>Do código ao ar em <em>segundos</em></h1>
      <p>Deploy automático via Git, catálogo com mais de 50 apps, containers isolados e rollback instantâneo. Foque no código, a infraestrutura é por nossa conta.</p>
      <div class="da-hero-actions">
        <a href="/cliente/criar-conta" class="da-btn-p">Começar agora</a>
        <a href="/contato" class="da-btn-s">Falar com a equipe</a>
      </div>
    </div>
    <div class="da-hero-visual">
      <div class="da-mock-bar"><div class="da-mock-dot"></div><div class="da-mock-dot"></div><div class="da-mock-dot"></div></div>
      <div class="da-pipeline">
        <div class="da-pipe-step"><div class="icon">📝</div><div class="txt">Commit</div></div>
        <div class="da-pipe-arrow">→</div>
        <div class="da-pipe-step"><div class="icon">🔨</div><div class="txt">Build</div></div>
        <div class="da-pipe-arrow">→</div>
        <div class="da-pipe-step"><div class="icon">🧪</div><div class="txt">Test</div></div>
        <div class="da-pipe-arrow">→</div>
        <div class="da-pipe-step active"><div class="icon">🚀</div><div class="txt">Deploy</div></div>
        <div class="da-pipe-arrow">→</div>
        <div class="da-pipe-step"><div class="icon">✅</div><div class="txt">Live</div></div>
      </div>
      <div class="da-pipe-log">
        <div><span class="violet">$</span> git push origin main</div>
        <div><span class="green">✓</span> Build concluído em 12s</div>
        <div><span class="green">✓</span> Testes passaram (47/47)</div>
        <div><span class="green">✓</span> Deploy realizado — <span class="violet">app.exemplo.com</span></div>
      </div>
    </div>
  </div>
</section>

<!-- STATS -->
<div class="da-stats">
  <div class="da-stats-inner">
    <div class="da-stat"><h3>Git</h3><p>Deploy via push</p></div>
    <div class="da-stat"><h3>50+</h3><p>Apps no catálogo</p></div>
    <div class="da-stat"><h3>Zero</h3><p>Downtime no deploy</p></div>
    <div class="da-stat"><h3>1 clique</h3><p>Rollback instantâneo</p></div>
  </div>
</div>

<!-- COMPARAÇÃO -->
<section class="da-section">
  <div class="da-inner">
    <div style="text-align:center;margin-bottom:8px;">
      <div class="da-label">Por que automatizar?</div>
      <h2 class="da-title">Deploy manual vs Deploy Automático <?php echo View::e($_nome); ?></h2>
      <p class="da-sub" style="margin:0 auto;">Pare de perder tempo com deploys manuais e foque no que importa: seu código.</p>
    </div>
    <div class="da-compare">
      <div class="da-compare-card bad">
        <h3>❌ Deploy manual</h3>
        <ul>
          <li>❌ Upload via FTP arquivo por arquivo</li>
          <li>❌ Downtime a cada atualização</li>
          <li>❌ Sem rollback — erro é dor de cabeça</li>
          <li>❌ Configuração de ambiente manual</li>
          <li>❌ Sem logs centralizados do deploy</li>
          <li>❌ Processo diferente para cada desenvolvedor</li>
        </ul>
      </div>
      <div class="da-compare-card good">
        <h3>✅ Deploy Automático <?php echo View::e($_nome); ?></h3>
        <ul>
          <li>✅ Push no Git e o deploy acontece sozinho</li>
          <li>✅ Zero downtime com troca atômica</li>
          <li>✅ Rollback instantâneo para qualquer versão</li>
          <li>✅ Variáveis de ambiente seguras no painel</li>
          <li>✅ Logs em tempo real de cada deploy</li>
          <li>✅ Pipeline padronizado para todo o time</li>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section class="da-section alt">
  <div class="da-inner">
    <div style="text-align:center;">
      <div class="da-label">Funcionalidades</div>
      <h2 class="da-title">Tudo para seu deploy perfeito</h2>
    </div>
    <div class="da-features">
      <div class="da-feat"><div class="da-feat-icon">🔗</div><h3>Git Deploy</h3><p>Conecte GitHub, GitLab ou Bitbucket. Cada push na branch configurada dispara o deploy automaticamente.</p></div>
      <div class="da-feat"><div class="da-feat-icon">📦</div><h3>Catálogo 50+ apps</h3><p>WordPress, Node.js, Python, Laravel, Next.js e dezenas de outros. Instale com um clique.</p></div>
      <div class="da-feat"><div class="da-feat-icon">🐳</div><h3>Containers Docker</h3><p>Cada aplicação roda em container isolado com recursos dedicados. Segurança e performance.</p></div>
      <div class="da-feat"><div class="da-feat-icon">🔄</div><h3>Zero downtime</h3><p>Deploy com troca atômica. A versão antiga só é removida quando a nova está 100% pronta.</p></div>
      <div class="da-feat"><div class="da-feat-icon">⏪</div><h3>Rollback</h3><p>Algo deu errado? Volte para qualquer versão anterior com um clique. Sem perda de dados.</p></div>
      <div class="da-feat"><div class="da-feat-icon">🔐</div><h3>Variáveis de ambiente</h3><p>Gerencie secrets e configurações pelo painel. Criptografadas e injetadas no build automaticamente.</p></div>
      <div class="da-feat"><div class="da-feat-icon">📋</div><h3>Logs em tempo real</h3><p>Acompanhe cada etapa do deploy em tempo real. Build, testes e publicação com timestamps.</p></div>
      <div class="da-feat"><div class="da-feat-icon">🌐</div><h3>Multi-linguagem</h3><p>Node.js, Python, PHP, Ruby, Go, Rust, Java e mais. Detecção automática de runtime.</p></div>
      <div class="da-feat"><div class="da-feat-icon">💬</div><h3>Suporte</h3><p>Equipe técnica que entende de deploy. Chat, ticket e e-mail para resolver qualquer dúvida.</p></div>
    </div>
  </div>
</section>

<!-- COMO FUNCIONA -->
<section class="da-section dark">
  <div class="da-inner">
    <div style="text-align:center;margin-bottom:8px;">
      <div class="da-label" style="color:#ddd6fe;">Como funciona</div>
      <h2 class="da-title" style="color:#fff;">Deploy em 3 passos</h2>
    </div>
    <div class="da-steps">
      <div class="da-step"><div class="da-step-num">1</div><h3 style="color:#fff;">Conecte o Git</h3><p>Vincule seu repositório GitHub, GitLab ou Bitbucket ao painel.</p></div>
      <div class="da-step-arrow">→</div>
      <div class="da-step"><div class="da-step-num">2</div><h3 style="color:#fff;">Push no código</h3><p>Faça push na branch configurada. O build e deploy iniciam automaticamente.</p></div>
      <div class="da-step-arrow">→</div>
      <div class="da-step"><div class="da-step-num">3</div><h3 style="color:#fff;">App online</h3><p>Sua aplicação está no ar com SSL, domínio e logs. Simples assim.</p></div>
    </div>
  </div>
</section>

<!-- FAQ -->
<section class="da-section">
  <div class="da-inner">
    <div style="text-align:center;">
      <div class="da-label">Perguntas frequentes</div>
      <h2 class="da-title">Dúvidas sobre Deploy Automático</h2>
    </div>
    <div class="da-faq">
      <details><summary>Quais linguagens e frameworks são suportados?</summary><p>Suportamos Node.js, Python, PHP, Ruby, Go, Rust, Java, .NET e mais. O runtime é detectado automaticamente pelo seu projeto ou você pode configurar manualmente via Dockerfile.</p></details>
      <details><summary>Quais provedores Git são compatíveis?</summary><p>GitHub, GitLab e Bitbucket são suportados nativamente. Basta autorizar o acesso e selecionar o repositório no painel.</p></details>
      <details><summary>Como funciona o rollback?</summary><p>Cada deploy gera uma versão imutável. Para fazer rollback, basta selecionar a versão desejada no painel e clicar em restaurar. A troca é instantânea e sem downtime.</p></details>
      <details><summary>Como gerencio variáveis de ambiente?</summary><p>Pelo painel, na seção de configuração da aplicação. As variáveis são criptografadas em repouso e injetadas automaticamente durante o build e runtime.</p></details>
      <details><summary>Posso ver os logs do deploy em tempo real?</summary><p>Sim. Cada deploy tem logs detalhados com timestamps de cada etapa: clone, build, testes e publicação. Logs de runtime também ficam disponíveis no painel.</p></details>
      <details><summary>Posso usar um Dockerfile customizado?</summary><p>Sim. Se o seu projeto contém um Dockerfile na raiz, ele será usado automaticamente. Você tem controle total sobre o ambiente de build e runtime.</p></details>
    </div>
  </div>
</section>

<!-- CTA FINAL -->
<section class="da-cta-section">
  <h2>Comece a deployar agora</h2>
  <p>Conecte seu repositório e tenha sua aplicação no ar em segundos. Sem complicação.</p>
  <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
    <a href="/cliente/criar-conta" class="da-btn-p">Criar conta grátis</a>
    <a href="/contato" class="da-btn-s">Falar com a equipe</a>
  </div>
</section>

<?php require __DIR__ . '/../_partials/footer.php'; ?>
<?php require __DIR__ . '/../_partials/chat-widget.php'; ?>
</body>
</html>
