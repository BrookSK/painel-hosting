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
<title>Python App — <?php echo View::e($_nome); ?></title>
<?php require __DIR__ . '/../_partials/seo.php'; ?>
<?php require __DIR__ . '/../_partials/estilo.php'; ?>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:system-ui,-apple-system,'Segoe UI',Roboto,sans-serif;background:#fff;color:#0f172a}

/* ── Hero Python ── */
.py-hero{background:linear-gradient(135deg,#1a1a2e 0%,#0e2a30 30%,#0c3547 60%,#0891b2 85%,#06b6d4 100%);position:relative;overflow:hidden;padding:100px 0 80px}
.py-hero::before{content:'';position:absolute;inset:0;background-image:radial-gradient(circle,rgba(255,255,255,.08) 1px,transparent 1px);background-size:40px 40px;pointer-events:none}
.py-hero .glow{position:absolute;width:600px;height:600px;background:radial-gradient(circle,rgba(6,182,212,.35),transparent 65%);top:-200px;right:-100px;pointer-events:none}
.py-hero-inner{position:relative;z-index:1;max-width:1100px;margin:0 auto;padding:0 24px;display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:center}
.py-hero-badge{display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.15);padding:5px 14px;border-radius:99px;margin-bottom:20px}
.py-hero-badge span{font-size:.72rem;font-weight:600;color:rgba(255,255,255,.8);letter-spacing:.06em;text-transform:uppercase}
.py-hero h1{font-size:clamp(1.8rem,4.5vw,3.2rem);font-weight:800;color:#fff;line-height:1.12;margin-bottom:18px;letter-spacing:-.02em}
.py-hero h1 em{font-style:italic;color:#a5f3fc}
.py-hero p{font-size:1rem;color:rgba(255,255,255,.6);line-height:1.8;margin-bottom:28px;max-width:480px}
.py-hero-actions{display:flex;gap:12px;flex-wrap:wrap}
.py-btn-p{display:inline-flex;align-items:center;gap:8px;padding:14px 28px;border-radius:12px;font-size:.9rem;font-weight:700;background:#fff;color:#0891b2;transition:transform .15s;text-decoration:none}
.py-btn-p:hover{transform:translateY(-2px)}
.py-btn-s{display:inline-flex;align-items:center;gap:8px;padding:14px 28px;border-radius:12px;font-size:.9rem;font-weight:700;background:rgba(255,255,255,.1);color:#fff;border:1.5px solid rgba(255,255,255,.2);text-decoration:none;transition:background .15s}
.py-btn-s:hover{background:rgba(255,255,255,.18)}
.py-hero-visual{background:rgba(255,255,255,.06);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,.12);border-radius:20px;padding:28px;color:#fff;font-family:'Courier New',Courier,monospace;font-size:.85rem;line-height:1.7}
.py-mock-bar{display:flex;gap:6px;margin-bottom:16px}.py-mock-dot{width:10px;height:10px;border-radius:50%;background:rgba(255,255,255,.15)}.py-mock-dot:first-child{background:#ef4444}.py-mock-dot:nth-child(2){background:#f59e0b}.py-mock-dot:nth-child(3){background:#22c55e}
.py-term-line{display:flex;gap:8px;margin-bottom:6px}
.py-term-prompt{color:#06b6d4;font-weight:700;white-space:nowrap}
.py-term-cmd{color:rgba(255,255,255,.85)}
.py-term-out{color:rgba(255,255,255,.4);font-size:.8rem;margin-bottom:8px;padding-left:18px}
.py-term-success{color:#22c55e;font-weight:700;margin-top:8px;display:flex;align-items:center;gap:6px}
@media(max-width:860px){.py-hero-inner{grid-template-columns:1fr;text-align:center}.py-hero p{margin:0 auto 28px}.py-hero-actions{justify-content:center}.py-hero-visual{display:none}}

/* ── Stats ── */
.py-stats{background:#0f172a;padding:36px 0}
.py-stats-inner{max-width:1100px;margin:0 auto;display:grid;grid-template-columns:repeat(4,1fr);text-align:center;gap:0}
.py-stat{padding:24px 16px;border-right:1px solid rgba(255,255,255,.06)}.py-stat:last-child{border:none}
.py-stat h3{font-size:2rem;font-weight:900;color:#a5f3fc;margin-bottom:4px}.py-stat p{font-size:.8rem;color:rgba(255,255,255,.4)}
@media(max-width:640px){.py-stats-inner{grid-template-columns:1fr 1fr}.py-stat:nth-child(2){border-right:none}}

/* ── Sections ── */
.py-section{padding:80px 24px}.py-section.alt{background:#f8fafc}.py-section.dark{background:#0f172a;color:#fff}
.py-inner{max-width:1100px;margin:0 auto}
.py-label{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:#0891b2;margin-bottom:10px}
.py-title{font-size:clamp(22px,3.5vw,34px);font-weight:800;color:#0f172a;margin-bottom:10px;letter-spacing:-.02em;line-height:1.15}
.py-sub{font-size:15px;color:#64748b;line-height:1.75;max-width:560px}

/* ── Features Grid ── */
.py-features{display:grid;grid-template-columns:repeat(3,1fr);gap:2px;background:#e2e8f0;border:1px solid #e2e8f0;border-radius:18px;overflow:hidden;margin-top:40px}
.py-feat{background:#fff;padding:32px 24px;transition:background .2s}
.py-feat:hover{background:#ecfeff}
.py-feat-icon{width:48px;height:48px;background:#ecfeff;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:24px;margin-bottom:16px}
.py-feat h3{font-size:.95rem;font-weight:700;margin-bottom:6px}.py-feat p{font-size:.85rem;color:#64748b;line-height:1.6}
@media(max-width:860px){.py-features{grid-template-columns:1fr 1fr}}
@media(max-width:560px){.py-features{grid-template-columns:1fr}}

/* ── How it works ── */
.py-steps{display:flex;align-items:flex-start;justify-content:center;gap:16px;margin-top:40px}
.py-step{text-align:center;flex:1;max-width:260px}
.py-step-num{width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,#0891b2,#06b6d4);color:#fff;font-size:22px;font-weight:800;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;box-shadow:0 4px 20px rgba(8,145,178,.3)}
.py-step h3{font-size:15px;font-weight:700;margin-bottom:6px}.py-step p{font-size:13px;color:rgba(255,255,255,.5)}
.py-step-arrow{color:rgba(255,255,255,.15);font-size:28px;padding-top:14px;flex-shrink:0}
@media(max-width:768px){.py-steps{flex-direction:column;align-items:center}.py-step-arrow{transform:rotate(90deg);padding:0}}

/* ── Comparison ── */
.py-compare{display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-top:40px}
.py-compare-card{border-radius:16px;padding:28px 24px}
.py-compare-card.bad{background:#fef2f2;border:1px solid #fecaca}
.py-compare-card.good{background:#ecfeff;border:2px solid #0891b2}
.py-compare-card h3{font-size:16px;font-weight:700;margin-bottom:14px;display:flex;align-items:center;gap:8px}
.py-compare-card ul{list-style:none;padding:0}
.py-compare-card ul li{padding:8px 0;font-size:13px;display:flex;align-items:flex-start;gap:8px;border-bottom:1px solid rgba(0,0,0,.04)}
@media(max-width:640px){.py-compare{grid-template-columns:1fr}}

/* ── CTA ── */
.py-cta-section{padding:80px 24px;background:linear-gradient(135deg,#1a1a2e,#0c3547);text-align:center;color:#fff}
.py-cta-section h2{font-size:clamp(24px,4vw,36px);font-weight:800;margin-bottom:12px}
.py-cta-section p{font-size:16px;color:rgba(255,255,255,.55);max-width:500px;margin:0 auto 28px}

/* ── FAQ ── */
.py-faq{max-width:700px;margin:40px auto 0;display:flex;flex-direction:column;gap:10px}
.py-faq details{background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;transition:border-color .2s}
.py-faq details[open]{border-color:#0891b2}
.py-faq summary{padding:16px 20px;font-size:14px;font-weight:600;cursor:pointer;list-style:none;display:flex;justify-content:space-between;align-items:center}
.py-faq summary::after{content:'+';font-size:18px;color:#94a3b8;transition:transform .2s}
.py-faq details[open] summary::after{content:'−';color:#0891b2}
.py-faq summary::-webkit-details-marker{display:none}
.py-faq details p{padding:0 20px 16px;font-size:13px;color:#64748b;line-height:1.7}
</style>
</head>
<body>
<?php require __DIR__ . '/../_partials/navbar-publica.php'; ?>

<!-- HERO -->
<section class="py-hero">
  <div class="glow"></div>
  <div class="py-hero-inner">
    <div>
      <div class="py-hero-badge"><span>🐍 Python App</span></div>
      <h1>Deploy simples para suas aplicações <em>Python</em></h1>
      <p>Hospede Django, Flask, FastAPI e mais com banco de dados, pip, domínio customizado e git deploy.</p>
      <div class="py-hero-actions">
        <a href="#planos" class="py-btn-p">Ver planos Python</a>
        <a href="/contato" class="py-btn-s">Falar com a equipe</a>
      </div>
    </div>
    <div class="py-hero-visual">
      <div class="py-mock-bar"><div class="py-mock-dot"></div><div class="py-mock-dot"></div><div class="py-mock-dot"></div></div>
      <div class="py-term-line"><span class="py-term-prompt">$</span><span class="py-term-cmd">pip install -r requirements.txt</span></div>
      <div class="py-term-out">Installing collected packages: django, gunicorn, psycopg2...</div>
      <div class="py-term-out">Successfully installed 24 packages</div>
      <div class="py-term-line"><span class="py-term-prompt">$</span><span class="py-term-cmd">python manage.py migrate</span></div>
      <div class="py-term-out">Running migrations... OK</div>
      <div class="py-term-line"><span class="py-term-prompt">$</span><span class="py-term-cmd">gunicorn app:application --bind 0.0.0.0:8000</span></div>
      <div class="py-term-out">[INFO] Listening at: http://0.0.0.0:8000</div>
      <div class="py-term-success">✔ Gunicorn running</div>
    </div>
  </div>
</section>

<!-- STATS -->
<div class="py-stats">
  <div class="py-stats-inner">
    <div class="py-stat"><h3>pip/venv</h3><p>Gerenciamento de pacotes</p></div>
    <div class="py-stat"><h3>99.9%</h3><p>Uptime garantido</p></div>
    <div class="py-stat"><h3>WSGI/ASGI</h3><p>Gunicorn &amp; Uvicorn</p></div>
    <div class="py-stat"><h3>SSL</h3><p>Grátis em todos os planos</p></div>
  </div>
</div>

<!-- COMPARAÇÃO -->
<section class="py-section">
  <div class="py-inner">
    <div style="text-align:center;margin-bottom:8px;">
      <div class="py-label">Por que migrar?</div>
      <h2 class="py-title">Deploy Python manual vs Python App <?php echo View::e($_nome); ?></h2>
      <p class="py-sub" style="margin:0 auto;">Veja a diferença entre configurar um servidor manualmente e usar nossa plataforma otimizada para Python.</p>
    </div>
    <div class="py-compare">
      <div class="py-compare-card bad">
        <h3>❌ Deploy Python manual</h3>
        <ul>
          <li>❌ Configurar servidor, Nginx e Gunicorn na mão</li>
          <li>❌ Gerenciar SSL, firewall e atualizações</li>
          <li>❌ Sem backups automáticos confiáveis</li>
          <li>❌ Debug difícil sem logs centralizados</li>
          <li>❌ Downtime durante deploys manuais</li>
          <li>❌ Horas perdidas com infraestrutura</li>
        </ul>
      </div>
      <div class="py-compare-card good">
        <h3>✅ Python App <?php echo View::e($_nome); ?></h3>
        <ul>
          <li>✅ Container isolado com Gunicorn/Uvicorn pré-configurado</li>
          <li>✅ SSL automático e proteção DDoS inclusa</li>
          <li>✅ Backups diários automáticos</li>
          <li>✅ Logs em tempo real no painel</li>
          <li>✅ Deploy zero-downtime via Git push</li>
          <li>✅ Foque no código, não na infra</li>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section class="py-section alt">
  <div class="py-inner">
    <div style="text-align:center;">
      <div class="py-label">Tudo incluso</div>
      <h2 class="py-title">Tudo que sua aplicação Python precisa</h2>
    </div>
    <div class="py-features">
      <div class="py-feat"><div class="py-feat-icon">🔀</div><h3>Git Deploy</h3><p>Conecte seu repositório GitHub ou GitLab. Push na branch e o deploy acontece automaticamente.</p></div>
      <div class="py-feat"><div class="py-feat-icon">📦</div><h3>pip/venv integrado</h3><p>Ambientes virtuais e pip pré-configurados. Dependências instaladas automaticamente no deploy.</p></div>
      <div class="py-feat"><div class="py-feat-icon">🗄️</div><h3>MySQL/PostgreSQL</h3><p>Banco de dados dedicado com backups automáticos. Escolha MySQL ou PostgreSQL conforme seu projeto.</p></div>
      <div class="py-feat"><div class="py-feat-icon">🌐</div><h3>Domínio com SSL</h3><p>Conecte seu domínio próprio com SSL Let's Encrypt automático. HTTPS sem custo extra.</p></div>
      <div class="py-feat"><div class="py-feat-icon">🐳</div><h3>Container isolado</h3><p>Sua aplicação roda em container dedicado com recursos garantidos. Sem interferência de outros projetos.</p></div>
      <div class="py-feat"><div class="py-feat-icon">🔑</div><h3>Variáveis de ambiente</h3><p>Configure variáveis de ambiente pelo painel. Secrets seguros sem expor no código-fonte.</p></div>
      <div class="py-feat"><div class="py-feat-icon">📊</div><h3>Logs em tempo real</h3><p>Acompanhe stdout, stderr e logs da aplicação direto no painel. Debug sem SSH.</p></div>
      <div class="py-feat"><div class="py-feat-icon">🛡️</div><h3>Proteção DDoS</h3><p>Infraestrutura com proteção contra ataques DDoS nativa. Sua aplicação sempre no ar.</p></div>
      <div class="py-feat"><div class="py-feat-icon">💬</div><h3>Suporte técnico</h3><p>Equipe que entende Python e deploy de verdade. Chat, ticket e e-mail disponíveis.</p></div>
    </div>
  </div>
</section>

<!-- COMO FUNCIONA -->
<section class="py-section dark">
  <div class="py-inner">
    <div style="text-align:center;margin-bottom:8px;">
      <div class="py-label" style="color:#a5f3fc;">Como funciona</div>
      <h2 class="py-title" style="color:#fff;">Online em 3 passos</h2>
    </div>
    <div class="py-steps">
      <div class="py-step"><div class="py-step-num">1</div><h3 style="color:#fff;">Conecte repositório</h3><p>Vincule seu repositório GitHub ou GitLab ao painel.</p></div>
      <div class="py-step-arrow">→</div>
      <div class="py-step"><div class="py-step-num">2</div><h3 style="color:#fff;">Push código</h3><p>Dependências instaladas, migrations executadas e servidor configurado automaticamente.</p></div>
      <div class="py-step-arrow">→</div>
      <div class="py-step"><div class="py-step-num">3</div><h3 style="color:#fff;">App online</h3><p>Sua aplicação Django, Flask ou FastAPI rodando com SSL e domínio.</p></div>
    </div>
  </div>
</section>

<!-- PLANOS -->
<?php $_accent = '#0891b2'; $_plan_type = 'python'; $_cta_base = '/cliente/planos/checkout?plan_id='; require __DIR__ . '/_planos-section.php'; ?>

<!-- FAQ -->
<section class="py-section">
  <div class="py-inner">
    <div style="text-align:center;">
      <div class="py-label">Perguntas frequentes</div>
      <h2 class="py-title">Dúvidas sobre hospedagem Python</h2>
    </div>
    <div class="py-faq">
      <details><summary>Quais versões do Python são suportadas?</summary><p>Suportamos Python 3.9, 3.10, 3.11 e 3.12. Você pode escolher a versão no painel e trocar a qualquer momento sem perder dados.</p></details>
      <details><summary>Posso rodar Django, Flask e FastAPI?</summary><p>Sim. Nossa infraestrutura suporta qualquer framework Python. Django e Flask rodam com Gunicorn (WSGI), FastAPI e Starlette com Uvicorn (ASGI).</p></details>
      <details><summary>Como funciona a instalação de dependências?</summary><p>Basta incluir um requirements.txt no seu repositório. Durante o deploy, pip instala todas as dependências automaticamente em um ambiente virtual isolado.</p></details>
      <details><summary>Qual banco de dados posso usar?</summary><p>Oferecemos MySQL e PostgreSQL dedicados. Escolha o que melhor se adapta ao seu projeto e acesse via variáveis de ambiente pré-configuradas.</p></details>
      <details><summary>Qual a diferença entre WSGI e ASGI?</summary><p>WSGI (Gunicorn) é ideal para aplicações síncronas como Django e Flask. ASGI (Uvicorn) suporta async/await e é recomendado para FastAPI e Starlette. Ambos são suportados.</p></details>
      <details><summary>E se minha aplicação crescer?</summary><p>Faça upgrade de plano a qualquer momento sem downtime. Seus dados, bancos e configurações são preservados automaticamente.</p></details>
    </div>
  </div>
</section>

<!-- CTA FINAL -->
<section class="py-cta-section">
  <h2>Pronto para deployar sua aplicação Python?</h2>
  <p>Comece agora e tenha seu Django, Flask ou FastAPI no ar em minutos.</p>
  <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
    <a href="#planos" class="py-btn-p">Ver planos</a>
    <a href="/contato" class="py-btn-s">Falar com a equipe</a>
  </div>
</section>

<?php require __DIR__ . '/../_partials/footer.php'; ?>
<?php require __DIR__ . '/../_partials/chat-widget.php'; ?>
</body>
</html>