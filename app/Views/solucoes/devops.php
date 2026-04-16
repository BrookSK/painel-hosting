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
<title>DevOps &amp; Ferramentas — <?php echo View::e($_nome); ?></title>
<?php require __DIR__ . '/../_partials/seo.php'; ?>
<?php require __DIR__ . '/../_partials/estilo.php'; ?>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:system-ui,-apple-system,'Segoe UI',Roboto,sans-serif;background:#fff;color:#0f172a}

/* ── Hero DevOps ── */
.dv-hero{background:linear-gradient(135deg,#022c22 0%,#064e3b 30%,#065f46 60%,#059669 85%,#10b981 100%);position:relative;overflow:hidden;padding:100px 0 80px}
.dv-hero::before{content:'';position:absolute;inset:0;background-image:radial-gradient(circle,rgba(255,255,255,.06) 1px,transparent 1px);background-size:40px 40px;pointer-events:none}
.dv-hero .glow{position:absolute;width:600px;height:600px;background:radial-gradient(circle,rgba(5,150,105,.4),transparent 65%);top:-200px;right:-100px;pointer-events:none}
.dv-hero-inner{position:relative;z-index:1;max-width:1100px;margin:0 auto;padding:0 24px;display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:center}
.dv-hero-badge{display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.15);padding:5px 14px;border-radius:99px;margin-bottom:20px}
.dv-hero-badge span{font-size:.72rem;font-weight:600;color:rgba(255,255,255,.8);letter-spacing:.06em;text-transform:uppercase}
.dv-hero h1{font-size:clamp(1.8rem,4.5vw,3.2rem);font-weight:800;color:#fff;line-height:1.12;margin-bottom:18px;letter-spacing:-.02em}
.dv-hero h1 em{font-style:italic;color:#6ee7b7}
.dv-hero p{font-size:1rem;color:rgba(255,255,255,.6);line-height:1.8;margin-bottom:28px;max-width:480px}
.dv-hero-actions{display:flex;gap:12px;flex-wrap:wrap}
.dv-btn-p{display:inline-flex;align-items:center;gap:8px;padding:14px 28px;border-radius:12px;font-size:.9rem;font-weight:700;background:#fff;color:#059669;transition:transform .15s;text-decoration:none}
.dv-btn-p:hover{transform:translateY(-2px)}
.dv-btn-s{display:inline-flex;align-items:center;gap:8px;padding:14px 28px;border-radius:12px;font-size:.9rem;font-weight:700;background:rgba(255,255,255,.1);color:#fff;border:1.5px solid rgba(255,255,255,.2);text-decoration:none;transition:background .15s}
.dv-btn-s:hover{background:rgba(255,255,255,.18)}

/* Hero Visual — Monitoring Dashboard */
.dv-hero-visual{background:rgba(255,255,255,.06);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,.12);border-radius:20px;padding:28px;color:#fff}
.dv-mock-bar{display:flex;gap:6px;margin-bottom:16px}.dv-mock-dot{width:10px;height:10px;border-radius:50%;background:rgba(255,255,255,.15)}.dv-mock-dot:first-child{background:#ef4444}.dv-mock-dot:nth-child(2){background:#f59e0b}.dv-mock-dot:nth-child(3){background:#22c55e}
.dv-dash-title{font-size:.7rem;text-transform:uppercase;letter-spacing:.08em;color:rgba(255,255,255,.4);margin-bottom:14px}
.dv-chart-row{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:12px}
.dv-mini-chart{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);border-radius:10px;padding:12px}
.dv-mini-chart .lbl{font-size:.6rem;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px}
.dv-mini-chart .val{font-size:1rem;font-weight:800;color:#6ee7b7;margin-bottom:6px}
.dv-chart-bars{display:flex;align-items:flex-end;gap:3px;height:30px}
.dv-chart-bars span{display:block;width:6px;border-radius:2px;background:linear-gradient(to top,#059669,#6ee7b7)}
.dv-terminal-mock{background:rgba(0,0,0,.3);border-radius:8px;padding:10px 12px;font-family:'Courier New',monospace;font-size:.68rem;color:rgba(255,255,255,.5);line-height:1.8}
.dv-terminal-mock .green{color:#4ade80}.dv-terminal-mock .emerald{color:#6ee7b7}
@media(max-width:860px){.dv-hero-inner{grid-template-columns:1fr;text-align:center}.dv-hero p{margin:0 auto 28px}.dv-hero-actions{justify-content:center}.dv-hero-visual{display:none}}

/* ── Stats ── */
.dv-stats{background:#064e3b;padding:36px 0}
.dv-stats-inner{max-width:1100px;margin:0 auto;display:grid;grid-template-columns:repeat(4,1fr);text-align:center;gap:0}
.dv-stat{padding:24px 16px;border-right:1px solid rgba(255,255,255,.06)}.dv-stat:last-child{border:none}
.dv-stat h3{font-size:2rem;font-weight:900;color:#6ee7b7;margin-bottom:4px}.dv-stat p{font-size:.8rem;color:rgba(255,255,255,.4)}
@media(max-width:640px){.dv-stats-inner{grid-template-columns:1fr 1fr}.dv-stat:nth-child(2){border-right:none}}

/* ── Sections ── */
.dv-section{padding:80px 24px}.dv-section.alt{background:#f0fdf4}.dv-section.dark{background:#064e3b;color:#fff}
.dv-inner{max-width:1100px;margin:0 auto}
.dv-label{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:#059669;margin-bottom:10px}
.dv-title{font-size:clamp(22px,3.5vw,34px);font-weight:800;color:#0f172a;margin-bottom:10px;letter-spacing:-.02em;line-height:1.15}
.dv-sub{font-size:15px;color:#64748b;line-height:1.75;max-width:560px}

/* ── Features Grid ── */
.dv-features{display:grid;grid-template-columns:repeat(3,1fr);gap:2px;background:#e2e8f0;border:1px solid #e2e8f0;border-radius:18px;overflow:hidden;margin-top:40px}
.dv-feat{background:#fff;padding:32px 24px;transition:background .2s}
.dv-feat:hover{background:#ecfdf5}
.dv-feat-icon{width:48px;height:48px;background:#ecfdf5;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:24px;margin-bottom:16px}
.dv-feat h3{font-size:.95rem;font-weight:700;margin-bottom:6px}.dv-feat p{font-size:.85rem;color:#64748b;line-height:1.6}
@media(max-width:860px){.dv-features{grid-template-columns:1fr 1fr}}
@media(max-width:560px){.dv-features{grid-template-columns:1fr}}

/* ── How it works ── */
.dv-steps{display:flex;align-items:flex-start;justify-content:center;gap:16px;margin-top:40px}
.dv-step{text-align:center;flex:1;max-width:260px}
.dv-step-num{width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,#059669,#10b981);color:#fff;font-size:22px;font-weight:800;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;box-shadow:0 4px 20px rgba(5,150,105,.3)}
.dv-step h3{font-size:15px;font-weight:700;margin-bottom:6px}.dv-step p{font-size:13px;color:rgba(255,255,255,.5)}
.dv-step-arrow{color:rgba(255,255,255,.15);font-size:28px;padding-top:14px;flex-shrink:0}
@media(max-width:768px){.dv-steps{flex-direction:column;align-items:center}.dv-step-arrow{transform:rotate(90deg);padding:0}}

/* ── Comparison ── */
.dv-compare{display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-top:40px}
.dv-compare-card{border-radius:16px;padding:28px 24px}
.dv-compare-card.bad{background:#fef2f2;border:1px solid #fecaca}
.dv-compare-card.good{background:#ecfdf5;border:2px solid #059669}
.dv-compare-card h3{font-size:16px;font-weight:700;margin-bottom:14px;display:flex;align-items:center;gap:8px}
.dv-compare-card ul{list-style:none;padding:0}
.dv-compare-card ul li{padding:8px 0;font-size:13px;display:flex;align-items:flex-start;gap:8px;border-bottom:1px solid rgba(0,0,0,.04)}
@media(max-width:640px){.dv-compare{grid-template-columns:1fr}}

/* ── CTA ── */
.dv-cta-section{padding:80px 24px;background:linear-gradient(135deg,#022c22,#064e3b);text-align:center;color:#fff}
.dv-cta-section h2{font-size:clamp(24px,4vw,36px);font-weight:800;margin-bottom:12px}
.dv-cta-section p{font-size:16px;color:rgba(255,255,255,.55);max-width:500px;margin:0 auto 28px}

/* ── FAQ ── */
.dv-faq{max-width:700px;margin:40px auto 0;display:flex;flex-direction:column;gap:10px}
.dv-faq details{background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;transition:border-color .2s}
.dv-faq details[open]{border-color:#059669}
.dv-faq summary{padding:16px 20px;font-size:14px;font-weight:600;cursor:pointer;list-style:none;display:flex;justify-content:space-between;align-items:center}
.dv-faq summary::after{content:'+';font-size:18px;color:#94a3b8;transition:transform .2s}
.dv-faq details[open] summary::after{content:'−';color:#059669}
.dv-faq summary::-webkit-details-marker{display:none}
.dv-faq details p{padding:0 20px 16px;font-size:13px;color:#64748b;line-height:1.7}
</style>
</head>
<body>
<?php require __DIR__ . '/../_partials/navbar-publica.php'; ?>

<!-- HERO -->
<section class="dv-hero">
  <div class="glow"></div>
  <div class="dv-hero-inner">
    <div>
      <div class="dv-hero-badge"><span>⚡ DevOps &amp; Ferramentas</span></div>
      <h1>Terminal, monitoramento e <em>controle total</em></h1>
      <p>Terminal web, monitoramento em tempo real, backups automáticos, logs e métricas. Todas as ferramentas que você precisa para gerenciar sua infraestrutura.</p>
      <div class="dv-hero-actions">
        <a href="/cliente/criar-conta" class="dv-btn-p">Começar agora</a>
        <a href="/contato" class="dv-btn-s">Falar com a equipe</a>
      </div>
    </div>
    <div class="dv-hero-visual">
      <div class="dv-mock-bar"><div class="dv-mock-dot"></div><div class="dv-mock-dot"></div><div class="dv-mock-dot"></div></div>
      <div class="dv-dash-title">Monitoring Dashboard</div>
      <div class="dv-chart-row">
        <div class="dv-mini-chart">
          <div class="lbl">CPU Usage</div>
          <div class="val">18%</div>
          <div class="dv-chart-bars">
            <span style="height:40%"></span><span style="height:25%"></span><span style="height:55%"></span><span style="height:30%"></span><span style="height:45%"></span><span style="height:20%"></span><span style="height:35%"></span><span style="height:50%"></span><span style="height:18%"></span><span style="height:28%"></span>
          </div>
        </div>
        <div class="dv-mini-chart">
          <div class="lbl">Memory</div>
          <div class="val">1.4 GB</div>
          <div class="dv-chart-bars">
            <span style="height:60%"></span><span style="height:55%"></span><span style="height:65%"></span><span style="height:58%"></span><span style="height:62%"></span><span style="height:50%"></span><span style="height:68%"></span><span style="height:55%"></span><span style="height:60%"></span><span style="height:57%"></span>
          </div>
        </div>
      </div>
      <div class="dv-terminal-mock">
        <div><span class="emerald">user@vps-01</span>:<span class="green">~$</span> htop</div>
        <div>Tasks: 42 total, 1 running</div>
        <div>Mem: 1.4G/4.0G &nbsp; Swap: 0/512M</div>
        <div><span class="emerald">user@vps-01</span>:<span class="green">~$</span> _</div>
      </div>
    </div>
  </div>
</section>

<!-- STATS -->
<div class="dv-stats">
  <div class="dv-stats-inner">
    <div class="dv-stat"><h3>SSH</h3><p>Terminal web integrado</p></div>
    <div class="dv-stat"><h3>24/7</h3><p>Métricas em tempo real</p></div>
    <div class="dv-stat"><h3>Auto</h3><p>Backup automático</p></div>
    <div class="dv-stat"><h3>Live</h3><p>Logs em tempo real</p></div>
  </div>
</div>

<!-- COMPARAÇÃO -->
<section class="dv-section">
  <div class="dv-inner">
    <div style="text-align:center;margin-bottom:8px;">
      <div class="dv-label">Por que usar?</div>
      <h2 class="dv-title">Gerenciamento manual vs DevOps <?php echo View::e($_nome); ?></h2>
      <p class="dv-sub" style="margin:0 auto;">Pare de apagar incêndios e tenha visibilidade total da sua infraestrutura.</p>
    </div>
    <div class="dv-compare">
      <div class="dv-compare-card bad">
        <h3>❌ Gerenciamento manual</h3>
        <ul>
          <li>❌ SSH via terminal local sem histórico</li>
          <li>❌ Sem visibilidade de métricas do servidor</li>
          <li>❌ Backups manuais esquecidos com frequência</li>
          <li>❌ Logs espalhados em arquivos diferentes</li>
          <li>❌ Cron jobs configurados sem interface</li>
          <li>❌ Sem alertas — descobre problemas pelo cliente</li>
        </ul>
      </div>
      <div class="dv-compare-card good">
        <h3>✅ DevOps <?php echo View::e($_nome); ?></h3>
        <ul>
          <li>✅ Terminal web com histórico e sessões salvas</li>
          <li>✅ Dashboard com CPU, RAM, disco e rede</li>
          <li>✅ Backups automáticos com retenção configurável</li>
          <li>✅ Logs centralizados com busca e filtros</li>
          <li>✅ Cron jobs com interface visual e logs</li>
          <li>✅ Alertas automáticos por e-mail e painel</li>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section class="dv-section alt">
  <div class="dv-inner">
    <div style="text-align:center;">
      <div class="dv-label">Ferramentas</div>
      <h2 class="dv-title">Tudo para gerenciar sua infraestrutura</h2>
    </div>
    <div class="dv-features">
      <div class="dv-feat"><div class="dv-feat-icon">🖥️</div><h3>Terminal Web SSH</h3><p>Acesse qualquer servidor direto pelo navegador. Sem cliente SSH, sem configuração. Copie, cole e execute.</p></div>
      <div class="dv-feat"><div class="dv-feat-icon">📊</div><h3>Monitoramento CPU/RAM/Disco</h3><p>Gráficos em tempo real e históricos de uso. Identifique gargalos antes que virem problemas.</p></div>
      <div class="dv-feat"><div class="dv-feat-icon">💾</div><h3>Backups automáticos</h3><p>Backups diários com retenção configurável. Restaure arquivos, bancos ou o servidor inteiro.</p></div>
      <div class="dv-feat"><div class="dv-feat-icon">📋</div><h3>Logs centralizados</h3><p>Todos os logs em um só lugar com busca, filtros por data e nível. Nginx, app, sistema e deploy.</p></div>
      <div class="dv-feat"><div class="dv-feat-icon">📈</div><h3>Métricas em tempo real</h3><p>Latência, throughput, conexões ativas e uso de banda. Dashboards atualizados a cada segundo.</p></div>
      <div class="dv-feat"><div class="dv-feat-icon">⏰</div><h3>Cron Jobs</h3><p>Agende tarefas com interface visual. Veja histórico de execução, saída e erros de cada job.</p></div>
      <div class="dv-feat"><div class="dv-feat-icon">📁</div><h3>Gerenciador de arquivos</h3><p>Navegue, edite, faça upload e download de arquivos pelo navegador. Sem FTP, sem complicação.</p></div>
      <div class="dv-feat"><div class="dv-feat-icon">🔔</div><h3>Alertas automáticos</h3><p>Receba notificações quando CPU, RAM ou disco atingem limites. Configure thresholds personalizados.</p></div>
      <div class="dv-feat"><div class="dv-feat-icon">🔌</div><h3>API REST</h3><p>Automatize tudo via API. Crie scripts, integre com CI/CD ou construa suas próprias ferramentas.</p></div>
    </div>
  </div>
</section>

<!-- COMO FUNCIONA -->
<section class="dv-section dark">
  <div class="dv-inner">
    <div style="text-align:center;margin-bottom:8px;">
      <div class="dv-label" style="color:#6ee7b7;">Como funciona</div>
      <h2 class="dv-title" style="color:#fff;">Controle em 3 passos</h2>
    </div>
    <div class="dv-steps">
      <div class="dv-step"><div class="dv-step-num">1</div><h3 style="color:#fff;">Acesse o painel</h3><p>Faça login e veja todos os seus servidores, métricas e alertas em um dashboard unificado.</p></div>
      <div class="dv-step-arrow">→</div>
      <div class="dv-step"><div class="dv-step-num">2</div><h3 style="color:#fff;">Configure alertas</h3><p>Defina thresholds de CPU, RAM e disco. Receba notificações antes que problemas aconteçam.</p></div>
      <div class="dv-step-arrow">→</div>
      <div class="dv-step"><div class="dv-step-num">3</div><h3 style="color:#fff;">Monitore tudo</h3><p>Acompanhe métricas, logs e backups em tempo real. Terminal web sempre à mão.</p></div>
    </div>
  </div>
</section>

<!-- FAQ -->
<section class="dv-section">
  <div class="dv-inner">
    <div style="text-align:center;">
      <div class="dv-label">Perguntas frequentes</div>
      <h2 class="dv-title">Dúvidas sobre DevOps &amp; Ferramentas</h2>
    </div>
    <div class="dv-faq">
      <details><summary>O terminal web é seguro?</summary><p>Sim. A conexão é criptografada via WebSocket sobre HTTPS. Sessões são autenticadas com seu login e, opcionalmente, 2FA. Nenhum dado trafega em texto plano.</p></details>
      <details><summary>Que métricas de monitoramento estão disponíveis?</summary><p>CPU, RAM, disco, rede (entrada/saída), latência, conexões ativas e uso de swap. Gráficos em tempo real e históricos com retenção de 30 dias.</p></details>
      <details><summary>Como funcionam os backups automáticos?</summary><p>Backups diários automáticos com retenção configurável (7, 14 ou 30 dias). Inclui arquivos, bancos de dados e configurações. Restauração com um clique.</p></details>
      <details><summary>Posso agendar cron jobs pelo painel?</summary><p>Sim. Interface visual para criar, editar e excluir cron jobs. Cada execução gera log com saída, erros e duração. Notificações em caso de falha.</p></details>
      <details><summary>O gerenciador de arquivos suporta edição?</summary><p>Sim. Editor integrado com syntax highlighting para as principais linguagens. Faça upload, download, renomeie e edite arquivos direto pelo navegador.</p></details>
      <details><summary>A API REST tem documentação?</summary><p>Sim. Documentação completa em OpenAPI/Swagger com exemplos para cada endpoint. Autenticação via token de API gerado no painel.</p></details>
    </div>
  </div>
</section>

<!-- CTA FINAL -->
<section class="dv-cta-section">
  <h2>Controle total da sua infraestrutura</h2>
  <p>Terminal, monitoramento, backups e logs em um só lugar. Comece agora.</p>
  <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
    <a href="/cliente/criar-conta" class="dv-btn-p">Criar conta grátis</a>
    <a href="/contato" class="dv-btn-s">Falar com a equipe</a>
  </div>
</section>

<?php require __DIR__ . '/../_partials/footer.php'; ?>
<?php require __DIR__ . '/../_partials/chat-widget.php'; ?>
</body>
</html>
