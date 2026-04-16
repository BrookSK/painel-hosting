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
<title>Comunicação — <?php echo View::e($_nome); ?></title>
<?php require __DIR__ . '/../_partials/seo.php'; ?>
<?php require __DIR__ . '/../_partials/estilo.php'; ?>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:system-ui,-apple-system,'Segoe UI',Roboto,sans-serif;background:#fff;color:#0f172a}

/* ── Hero Comunicação ── */
.em-hero{background:linear-gradient(135deg,#4c0519 0%,#881337 30%,#be123c 60%,#e11d48 85%,#fb7185 100%);position:relative;overflow:hidden;padding:100px 0 80px}
.em-hero::before{content:'';position:absolute;inset:0;background-image:radial-gradient(circle,rgba(255,255,255,.06) 1px,transparent 1px);background-size:40px 40px;pointer-events:none}
.em-hero .glow{position:absolute;width:600px;height:600px;background:radial-gradient(circle,rgba(225,29,72,.4),transparent 65%);top:-200px;right:-100px;pointer-events:none}
.em-hero-inner{position:relative;z-index:1;max-width:1100px;margin:0 auto;padding:0 24px;display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:center}
.em-hero-badge{display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.15);padding:5px 14px;border-radius:99px;margin-bottom:20px}
.em-hero-badge span{font-size:.72rem;font-weight:600;color:rgba(255,255,255,.8);letter-spacing:.06em;text-transform:uppercase}
.em-hero h1{font-size:clamp(1.8rem,4.5vw,3.2rem);font-weight:800;color:#fff;line-height:1.12;margin-bottom:18px;letter-spacing:-.02em}
.em-hero h1 em{font-style:italic;color:#fecdd3}
.em-hero p{font-size:1rem;color:rgba(255,255,255,.6);line-height:1.8;margin-bottom:28px;max-width:480px}
.em-hero-actions{display:flex;gap:12px;flex-wrap:wrap}
.em-btn-p{display:inline-flex;align-items:center;gap:8px;padding:14px 28px;border-radius:12px;font-size:.9rem;font-weight:700;background:#fff;color:#e11d48;transition:transform .15s;text-decoration:none}
.em-btn-p:hover{transform:translateY(-2px)}
.em-btn-s{display:inline-flex;align-items:center;gap:8px;padding:14px 28px;border-radius:12px;font-size:.9rem;font-weight:700;background:rgba(255,255,255,.1);color:#fff;border:1.5px solid rgba(255,255,255,.2);text-decoration:none;transition:background .15s}
.em-btn-s:hover{background:rgba(255,255,255,.18)}

/* Hero Visual — Email Inbox */
.em-hero-visual{background:rgba(255,255,255,.06);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,.12);border-radius:20px;padding:28px;color:#fff}
.em-mock-bar{display:flex;gap:6px;margin-bottom:16px}.em-mock-dot{width:10px;height:10px;border-radius:50%;background:rgba(255,255,255,.15)}.em-mock-dot:first-child{background:#ef4444}.em-mock-dot:nth-child(2){background:#f59e0b}.em-mock-dot:nth-child(3){background:#22c55e}
.em-inbox-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px}
.em-inbox-header .title{font-size:.8rem;font-weight:700;color:rgba(255,255,255,.7)}
.em-inbox-header .badge{background:rgba(225,29,72,.3);color:#fecdd3;font-size:.6rem;font-weight:700;padding:2px 8px;border-radius:10px}
.em-mail-item{display:flex;align-items:center;gap:10px;padding:10px 12px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.06);border-radius:8px;margin-bottom:6px}
.em-mail-item.unread{background:rgba(225,29,72,.08);border-color:rgba(225,29,72,.15)}
.em-mail-avatar{width:32px;height:32px;border-radius:50%;background:rgba(255,255,255,.1);display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:700;flex-shrink:0}
.em-mail-item.unread .em-mail-avatar{background:rgba(225,29,72,.2);color:#fecdd3}
.em-mail-body{flex:1;min-width:0}
.em-mail-body .from{font-size:.7rem;font-weight:600;color:rgba(255,255,255,.6)}
.em-mail-body .subject{font-size:.65rem;color:rgba(255,255,255,.35);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.em-mail-time{font-size:.6rem;color:rgba(255,255,255,.25);flex-shrink:0}
.em-chat-bubble{background:rgba(225,29,72,.12);border:1px solid rgba(225,29,72,.2);border-radius:12px 12px 12px 4px;padding:10px 14px;margin-top:10px;font-size:.7rem;color:rgba(255,255,255,.5);display:flex;align-items:center;gap:8px}
.em-chat-bubble .icon{font-size:1rem}
@media(max-width:860px){.em-hero-inner{grid-template-columns:1fr;text-align:center}.em-hero p{margin:0 auto 28px}.em-hero-actions{justify-content:center}.em-hero-visual{display:none}}

/* ── Stats ── */
.em-stats{background:#881337;padding:36px 0}
.em-stats-inner{max-width:1100px;margin:0 auto;display:grid;grid-template-columns:repeat(4,1fr);text-align:center;gap:0}
.em-stat{padding:24px 16px;border-right:1px solid rgba(255,255,255,.06)}.em-stat:last-child{border:none}
.em-stat h3{font-size:2rem;font-weight:900;color:#fecdd3;margin-bottom:4px}.em-stat p{font-size:.8rem;color:rgba(255,255,255,.4)}
@media(max-width:640px){.em-stats-inner{grid-template-columns:1fr 1fr}.em-stat:nth-child(2){border-right:none}}

/* ── Sections ── */
.em-section{padding:80px 24px}.em-section.alt{background:#fff1f2}.em-section.dark{background:#881337;color:#fff}
.em-inner{max-width:1100px;margin:0 auto}
.em-label{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:#e11d48;margin-bottom:10px}
.em-title{font-size:clamp(22px,3.5vw,34px);font-weight:800;color:#0f172a;margin-bottom:10px;letter-spacing:-.02em;line-height:1.15}
.em-sub{font-size:15px;color:#64748b;line-height:1.75;max-width:560px}

/* ── Features Grid ── */
.em-features{display:grid;grid-template-columns:repeat(3,1fr);gap:2px;background:#e2e8f0;border:1px solid #e2e8f0;border-radius:18px;overflow:hidden;margin-top:40px}
.em-feat{background:#fff;padding:32px 24px;transition:background .2s}
.em-feat:hover{background:#fff1f2}
.em-feat-icon{width:48px;height:48px;background:#fff1f2;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:24px;margin-bottom:16px}
.em-feat h3{font-size:.95rem;font-weight:700;margin-bottom:6px}.em-feat p{font-size:.85rem;color:#64748b;line-height:1.6}
@media(max-width:860px){.em-features{grid-template-columns:1fr 1fr}}
@media(max-width:560px){.em-features{grid-template-columns:1fr}}

/* ── How it works ── */
.em-steps{display:flex;align-items:flex-start;justify-content:center;gap:16px;margin-top:40px}
.em-step{text-align:center;flex:1;max-width:260px}
.em-step-num{width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,#e11d48,#fb7185);color:#fff;font-size:22px;font-weight:800;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;box-shadow:0 4px 20px rgba(225,29,72,.3)}
.em-step h3{font-size:15px;font-weight:700;margin-bottom:6px}.em-step p{font-size:13px;color:rgba(255,255,255,.5)}
.em-step-arrow{color:rgba(255,255,255,.15);font-size:28px;padding-top:14px;flex-shrink:0}
@media(max-width:768px){.em-steps{flex-direction:column;align-items:center}.em-step-arrow{transform:rotate(90deg);padding:0}}

/* ── Comparison ── */
.em-compare{display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-top:40px}
.em-compare-card{border-radius:16px;padding:28px 24px}
.em-compare-card.bad{background:#fef2f2;border:1px solid #fecaca}
.em-compare-card.good{background:#fff1f2;border:2px solid #e11d48}
.em-compare-card h3{font-size:16px;font-weight:700;margin-bottom:14px;display:flex;align-items:center;gap:8px}
.em-compare-card ul{list-style:none;padding:0}
.em-compare-card ul li{padding:8px 0;font-size:13px;display:flex;align-items:flex-start;gap:8px;border-bottom:1px solid rgba(0,0,0,.04)}
@media(max-width:640px){.em-compare{grid-template-columns:1fr}}

/* ── CTA ── */
.em-cta-section{padding:80px 24px;background:linear-gradient(135deg,#4c0519,#881337);text-align:center;color:#fff}
.em-cta-section h2{font-size:clamp(24px,4vw,36px);font-weight:800;margin-bottom:12px}
.em-cta-section p{font-size:16px;color:rgba(255,255,255,.55);max-width:500px;margin:0 auto 28px}

/* ── FAQ ── */
.em-faq{max-width:700px;margin:40px auto 0;display:flex;flex-direction:column;gap:10px}
.em-faq details{background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;transition:border-color .2s}
.em-faq details[open]{border-color:#e11d48}
.em-faq summary{padding:16px 20px;font-size:14px;font-weight:600;cursor:pointer;list-style:none;display:flex;justify-content:space-between;align-items:center}
.em-faq summary::after{content:'+';font-size:18px;color:#94a3b8;transition:transform .2s}
.em-faq details[open] summary::after{content:'−';color:#e11d48}
.em-faq summary::-webkit-details-marker{display:none}
.em-faq details p{padding:0 20px 16px;font-size:13px;color:#64748b;line-height:1.7}
</style>
</head>
<body>
<?php require __DIR__ . '/../_partials/navbar-publica.php'; ?>

<!-- HERO -->
<section class="em-hero">
  <div class="glow"></div>
  <div class="em-hero-inner">
    <div>
      <div class="em-hero-badge"><span>💬 Comunicação</span></div>
      <h1>E-mail profissional, chat e <em>suporte integrado</em></h1>
      <p>E-mail com domínio próprio, chat em tempo real, sistema de tickets e suporte técnico. Toda a comunicação da sua empresa em um só lugar.</p>
      <div class="em-hero-actions">
        <a href="/cliente/criar-conta" class="em-btn-p">Começar agora</a>
        <a href="/contato" class="em-btn-s">Falar com a equipe</a>
      </div>
    </div>
    <div class="em-hero-visual">
      <div class="em-mock-bar"><div class="em-mock-dot"></div><div class="em-mock-dot"></div><div class="em-mock-dot"></div></div>
      <div class="em-inbox-header">
        <div class="title">📬 Caixa de Entrada</div>
        <div class="badge">3 novas</div>
      </div>
      <div class="em-mail-item unread">
        <div class="em-mail-avatar">CL</div>
        <div class="em-mail-body">
          <div class="from">Cliente Premium</div>
          <div class="subject">Proposta comercial — Projeto Q4 2025</div>
        </div>
        <div class="em-mail-time">10:32</div>
      </div>
      <div class="em-mail-item unread">
        <div class="em-mail-avatar">EQ</div>
        <div class="em-mail-body">
          <div class="from">Equipe Dev</div>
          <div class="subject">Deploy v2.4 concluído com sucesso</div>
        </div>
        <div class="em-mail-time">09:15</div>
      </div>
      <div class="em-mail-item">
        <div class="em-mail-avatar">MK</div>
        <div class="em-mail-body">
          <div class="from">Marketing</div>
          <div class="subject">Relatório mensal de campanhas</div>
        </div>
        <div class="em-mail-time">Ontem</div>
      </div>
      <div class="em-chat-bubble">
        <div class="icon">💬</div>
        <div>Nova mensagem no chat: "Oi, preciso de ajuda com o DNS..."</div>
      </div>
    </div>
  </div>
</section>

<!-- STATS -->
<div class="em-stats">
  <div class="em-stats-inner">
    <div class="em-stat"><h3>E-mail</h3><p>Domínio profissional</p></div>
    <div class="em-stat"><h3>Chat</h3><p>Tempo real integrado</p></div>
    <div class="em-stat"><h3>Tickets</h3><p>Sistema completo</p></div>
    <div class="em-stat"><h3>24/7</h3><p>Suporte disponível</p></div>
  </div>
</div>

<!-- COMPARAÇÃO -->
<section class="em-section">
  <div class="em-inner">
    <div style="text-align:center;margin-bottom:8px;">
      <div class="em-label">Por que centralizar?</div>
      <h2 class="em-title">Comunicação fragmentada vs Comunicação <?php echo View::e($_nome); ?></h2>
      <p class="em-sub" style="margin:0 auto;">Unifique e-mail, chat e suporte em uma plataforma integrada.</p>
    </div>
    <div class="em-compare">
      <div class="em-compare-card bad">
        <h3>❌ Comunicação fragmentada</h3>
        <ul>
          <li>❌ E-mail genérico (@gmail, @hotmail)</li>
          <li>❌ Chat em uma ferramenta, e-mail em outra</li>
          <li>❌ Sem sistema de tickets organizado</li>
          <li>❌ Spam sem controle eficiente</li>
          <li>❌ Sem criptografia garantida</li>
          <li>❌ Suporte por canais desconectados</li>
        </ul>
      </div>
      <div class="em-compare-card good">
        <h3>✅ Comunicação <?php echo View::e($_nome); ?></h3>
        <ul>
          <li>✅ E-mail com seu domínio (voce@suaempresa.com)</li>
          <li>✅ Chat e e-mail integrados no mesmo painel</li>
          <li>✅ Tickets com prioridade, status e histórico</li>
          <li>✅ Anti-spam inteligente com filtros avançados</li>
          <li>✅ SSL/TLS em todas as conexões</li>
          <li>✅ Suporte técnico unificado via chat e ticket</li>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section class="em-section alt">
  <div class="em-inner">
    <div style="text-align:center;">
      <div class="em-label">Recursos</div>
      <h2 class="em-title">Tudo para sua comunicação profissional</h2>
    </div>
    <div class="em-features">
      <div class="em-feat"><div class="em-feat-icon">📧</div><h3>E-mail com domínio próprio</h3><p>Crie contas como contato@suaempresa.com. Credibilidade profissional para sua marca.</p></div>
      <div class="em-feat"><div class="em-feat-icon">🌐</div><h3>Webmail integrado</h3><p>Acesse seus e-mails pelo navegador de qualquer lugar. Interface moderna e responsiva.</p></div>
      <div class="em-feat"><div class="em-feat-icon">💬</div><h3>Chat em tempo real</h3><p>Converse com sua equipe e clientes em tempo real. Histórico salvo e notificações.</p></div>
      <div class="em-feat"><div class="em-feat-icon">🎫</div><h3>Sistema de tickets</h3><p>Organize solicitações com prioridade, status, atribuição e histórico completo.</p></div>
      <div class="em-feat"><div class="em-feat-icon">🛠️</div><h3>Suporte técnico</h3><p>Equipe disponível via chat, ticket e e-mail. Respostas rápidas e especializadas.</p></div>
      <div class="em-feat"><div class="em-feat-icon">🛡️</div><h3>Anti-spam</h3><p>Filtros inteligentes que bloqueiam spam antes de chegar na sua caixa. Listas personalizáveis.</p></div>
      <div class="em-feat"><div class="em-feat-icon">🔒</div><h3>SSL/TLS</h3><p>Todas as conexões criptografadas. IMAP, SMTP e POP3 com TLS obrigatório.</p></div>
      <div class="em-feat"><div class="em-feat-icon">👥</div><h3>Múltiplas contas</h3><p>Crie quantas contas de e-mail precisar. Cada colaborador com sua caixa própria.</p></div>
      <div class="em-feat"><div class="em-feat-icon">↗️</div><h3>Encaminhamento</h3><p>Redirecione e-mails entre contas ou para endereços externos. Aliases e catch-all disponíveis.</p></div>
    </div>
  </div>
</section>

<!-- COMO FUNCIONA -->
<section class="em-section dark">
  <div class="em-inner">
    <div style="text-align:center;margin-bottom:8px;">
      <div class="em-label" style="color:#fecdd3;">Como funciona</div>
      <h2 class="em-title" style="color:#fff;">Comunicação em 3 passos</h2>
    </div>
    <div class="em-steps">
      <div class="em-step"><div class="em-step-num">1</div><h3 style="color:#fff;">Configure o domínio</h3><p>Aponte os registros DNS do seu domínio. Verificação automática em minutos.</p></div>
      <div class="em-step-arrow">→</div>
      <div class="em-step"><div class="em-step-num">2</div><h3 style="color:#fff;">Crie as contas</h3><p>Adicione contas de e-mail para cada colaborador pelo painel.</p></div>
      <div class="em-step-arrow">→</div>
      <div class="em-step"><div class="em-step-num">3</div><h3 style="color:#fff;">Comunique-se</h3><p>Use webmail, chat e tickets. Tudo integrado e pronto para usar.</p></div>
    </div>
  </div>
</section>

<!-- FAQ -->
<section class="em-section">
  <div class="em-inner">
    <div style="text-align:center;">
      <div class="em-label">Perguntas frequentes</div>
      <h2 class="em-title">Dúvidas sobre Comunicação</h2>
    </div>
    <div class="em-faq">
      <details><summary>Como configuro o e-mail com meu domínio?</summary><p>Basta adicionar os registros MX, SPF e DKIM no DNS do seu domínio. O painel mostra exatamente quais registros criar e verifica automaticamente quando estiverem propagados.</p></details>
      <details><summary>Posso acessar o e-mail pelo navegador?</summary><p>Sim. O webmail integrado funciona em qualquer navegador e dispositivo. Você também pode configurar clientes como Outlook, Thunderbird ou Apple Mail via IMAP/SMTP.</p></details>
      <details><summary>O chat funciona em tempo real?</summary><p>Sim. Chat via WebSocket com entrega instantânea de mensagens. Histórico completo salvo e pesquisável. Notificações no navegador e por e-mail.</p></details>
      <details><summary>Como funciona o sistema de tickets?</summary><p>Crie tickets com título, descrição e prioridade. Acompanhe o status (aberto, em andamento, resolvido), adicione comentários e receba notificações de atualizações.</p></details>
      <details><summary>Tem proteção contra spam?</summary><p>Sim. Filtros anti-spam com SPF, DKIM e DMARC configurados automaticamente. Listas de bloqueio e permissão personalizáveis. Quarentena para mensagens suspeitas.</p></details>
      <details><summary>Qual o limite de contas de e-mail?</summary><p>O número de contas depende do seu plano. Todos os planos incluem aliases ilimitados e encaminhamento. Entre em contato para planos com necessidades específicas.</p></details>
    </div>
  </div>
</section>

<!-- CTA FINAL -->
<section class="em-cta-section">
  <h2>Comunicação profissional para sua empresa</h2>
  <p>E-mail, chat e suporte integrados. Comece agora e profissionalize sua comunicação.</p>
  <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
    <a href="/cliente/criar-conta" class="em-btn-p">Criar conta grátis</a>
    <a href="/contato" class="em-btn-s">Falar com a equipe</a>
  </div>
</section>

<?php require __DIR__ . '/../_partials/footer.php'; ?>
<?php require __DIR__ . '/../_partials/chat-widget.php'; ?>
</body>
</html>
