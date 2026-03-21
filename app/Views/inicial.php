<?php
declare(strict_types=1);
use LRV\Core\I18n;
use LRV\Core\View;
use LRV\Core\SistemaConfig;

$_nome    = SistemaConfig::nome();
$_logo    = SistemaConfig::logoUrl();
$_empresa = SistemaConfig::empresaNome();
$_trial_ativo = !empty($trial_ativo);
$_trial_label = (string) ($trial_label ?? 'Testar grátis');
$_trial_desc  = (string) ($trial_desc ?? '');
$_trial_dias  = (int) ($trial_dias ?? 7);
$_topo_hide_inicio = true;
$_topo_links = [
    ['href' => '/status',    'label' => 'Status'],
    ['href' => '/contato',   'label' => 'Contato'],
    ['href' => '/changelog', 'label' => 'Changelog'],
    ['href' => '/cliente/entrar', 'label' => 'Entrar'],
];
?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?php echo View::e($_nome); ?></title>
  <?php require __DIR__ . '/_partials/estilo.php'; ?>
  <style>
    /* Hero */
    .hero {
      background: linear-gradient(135deg, #0B1C3D 0%, #1e3a8a 40%, #4F46E5 75%, #7C3AED 100%);
      color: #fff;
      padding: 80px 18px 90px;
      text-align: center;
      position: relative;
      overflow: hidden;
    }
    .hero::before {
      content: '';
      position: absolute;
      inset: 0;
      background: radial-gradient(ellipse at 60% 50%, rgba(124,58,237,.35) 0%, transparent 70%);
      pointer-events: none;
    }
    .hero-inner { max-width: 720px; margin: 0 auto; position: relative; }
    .hero-logo { margin-bottom: 20px; }
    .hero-logo img { height: 52px; width: auto; }
    .hero-title {
      font-size: clamp(28px, 5vw, 48px);
      font-weight: 800;
      line-height: 1.15;
      margin-bottom: 16px;
      letter-spacing: -.02em;
    }
    .hero-title span { color: #a5b4fc; }
    .hero-sub {
      font-size: 17px;
      opacity: .85;
      line-height: 1.65;
      margin-bottom: 36px;
      max-width: 560px;
      margin-left: auto;
      margin-right: auto;
    }
    .hero-ctas { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
    .hero-btn {
      display: inline-flex; align-items: center; gap: 8px;
      padding: 13px 26px; border-radius: 12px;
      font-size: 15px; font-weight: 700;
      text-decoration: none; transition: transform .15s, opacity .15s;
    }
    .hero-btn:hover { transform: translateY(-2px); opacity: .92; }
    .hero-btn.primary { background: #fff; color: #4F46E5; }
    .hero-btn.outline { background: rgba(255,255,255,.12); color: #fff; border: 1.5px solid rgba(255,255,255,.3); backdrop-filter: blur(4px); }
    .hero-badges { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; margin-top: 36px; }
    .hero-badge {
      display: inline-flex; align-items: center; gap: 6px;
      background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.15);
      color: #e0e7ff; font-size: 12px; font-weight: 500;
      padding: 5px 12px; border-radius: 999px; backdrop-filter: blur(4px);
    }
    .hero-badge svg { opacity: .8; }

    /* Features */
    .features { padding: 72px 18px; background: #f8fafc; }
    .features-inner { max-width: 1060px; margin: 0 auto; }
    .features-label {
      text-align: center; font-size: 12px; font-weight: 700;
      text-transform: uppercase; letter-spacing: .1em;
      color: #7C3AED; margin-bottom: 10px;
    }
    .features-title {
      text-align: center; font-size: clamp(20px, 3vw, 30px);
      font-weight: 800; color: #0f172a; margin-bottom: 8px;
    }
    .features-sub {
      text-align: center; color: #64748b; font-size: 15px;
      max-width: 520px; margin: 0 auto 48px; line-height: 1.6;
    }
    .features-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 20px;
    }
    .feature-card {
      background: #fff; border: 1px solid #e2e8f0;
      border-radius: 16px; padding: 24px;
      transition: box-shadow .2s, transform .2s;
    }
    .feature-card:hover { box-shadow: 0 8px 30px rgba(15,23,42,.08); transform: translateY(-2px); }
    .feature-icon {
      width: 44px; height: 44px; border-radius: 12px;
      display: flex; align-items: center; justify-content: center;
      margin-bottom: 14px; flex-shrink: 0;
    }
    .feature-icon.purple { background: #f5f3ff; color: #7C3AED; }
    .feature-icon.blue   { background: #eff6ff; color: #3b82f6; }
    .feature-icon.green  { background: #f0fdf4; color: #16a34a; }
    .feature-icon.orange { background: #fff7ed; color: #ea580c; }
    .feature-icon.indigo { background: #eef2ff; color: #4F46E5; }
    .feature-icon.rose   { background: #fff1f2; color: #e11d48; }
    .feature-name { font-size: 15px; font-weight: 700; color: #0f172a; margin-bottom: 6px; }
    .feature-desc { font-size: 13px; color: #64748b; line-height: 1.6; }

    /* CTA section */
    .cta-section {
      padding: 72px 18px;
      background: linear-gradient(135deg, #0B1C3D, #4F46E5);
      text-align: center; color: #fff;
    }
    .cta-inner { max-width: 600px; margin: 0 auto; }
    .cta-title { font-size: clamp(22px, 3vw, 32px); font-weight: 800; margin-bottom: 12px; }
    .cta-sub { font-size: 16px; opacity: .85; margin-bottom: 32px; line-height: 1.6; }
    .cta-btns { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }

    /* Access cards */
    .access-section { padding: 64px 18px; background: #fff; }
    .access-inner { max-width: 860px; margin: 0 auto; }
    .access-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    @media (max-width: 640px) { .access-grid { grid-template-columns: 1fr; } }
    .access-card {
      border: 1.5px solid #e2e8f0; border-radius: 18px; padding: 28px;
      display: flex; flex-direction: column; gap: 14px;
      transition: border-color .2s, box-shadow .2s;
    }
    .access-card:hover { border-color: #7C3AED; box-shadow: 0 6px 24px rgba(124,58,237,.1); }
    .access-card-icon {
      width: 48px; height: 48px; border-radius: 14px;
      display: flex; align-items: center; justify-content: center;
    }
    .access-card-icon.purple { background: #f5f3ff; color: #7C3AED; }
    .access-card-icon.indigo { background: #eef2ff; color: #4F46E5; }
    .access-card-title { font-size: 17px; font-weight: 700; color: #0f172a; }
    .access-card-desc { font-size: 13px; color: #64748b; line-height: 1.6; flex: 1; }
    .access-card-actions { display: flex; gap: 8px; flex-wrap: wrap; }

    @media (max-width: 640px) {
      .hero { padding: 56px 18px 64px; }
      .features { padding: 48px 18px; }
      .cta-section { padding: 48px 18px; }
      .access-section { padding: 48px 18px; }
    }
  </style>
</head>
<body>
  <?php require __DIR__ . '/_partials/topo-publico.php'; ?>

  <!-- Hero -->
  <section class="hero">
    <div class="hero-inner">
      <?php if ($_logo !== ''): ?>
        <div class="hero-logo"><img src="<?php echo View::e($_logo); ?>" alt="logo" /></div>
      <?php endif; ?>
      <h1 class="hero-title">
        Infraestrutura cloud<br><span>simples e poderosa</span>
      </h1>
      <p class="hero-sub">
        Gerencie VPS, aplicações, e-mails, backups e suporte em um único painel.
        Tudo automatizado, seguro e pronto para escalar.
      </p>
      <div class="hero-ctas">
        <?php if ($_trial_ativo): ?>
        <a href="/cliente/criar-conta" class="hero-btn primary">
          <svg width="18" height="18" viewBox="0 0 18 18" fill="none"><path d="M9 2v14M2 9h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
          <?php echo View::e($_trial_label); ?>
        </a>
        <a href="/cliente/entrar" class="hero-btn outline">Já tenho conta →</a>
        <?php else: ?>
        <a href="/cliente/criar-conta" class="hero-btn primary">
          <svg width="18" height="18" viewBox="0 0 18 18" fill="none"><path d="M9 2v14M2 9h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
          Criar conta grátis
        </a>
        <a href="/cliente/entrar" class="hero-btn outline">Já tenho conta →</a>
        <?php endif; ?>
      </div>
      <?php if ($_trial_ativo && $_trial_desc !== ''): ?>
      <p style="margin-top:16px;font-size:13px;opacity:.75;"><?php echo View::e($_trial_desc); ?></p>
      <?php endif; ?>
      <div class="hero-badges">
        <span class="hero-badge">
          <svg width="13" height="13" viewBox="0 0 13 13" fill="none"><circle cx="6.5" cy="6.5" r="5" stroke="currentColor" stroke-width="1.4"/><path d="M4 6.5l2 2 3-3" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
          Deploy automatizado
        </span>
        <span class="hero-badge">
          <svg width="13" height="13" viewBox="0 0 13 13" fill="none"><circle cx="6.5" cy="6.5" r="5" stroke="currentColor" stroke-width="1.4"/><path d="M4 6.5l2 2 3-3" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
          Backups automáticos
        </span>
        <span class="hero-badge">
          <svg width="13" height="13" viewBox="0 0 13 13" fill="none"><circle cx="6.5" cy="6.5" r="5" stroke="currentColor" stroke-width="1.4"/><path d="M4 6.5l2 2 3-3" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
          Suporte via chat e tickets
        </span>
        <span class="hero-badge">
          <svg width="13" height="13" viewBox="0 0 13 13" fill="none"><circle cx="6.5" cy="6.5" r="5" stroke="currentColor" stroke-width="1.4"/><path d="M4 6.5l2 2 3-3" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
          Monitoramento 24/7
        </span>
      </div>
    </div>
  </section>

  <!-- Features -->
  <section class="features">
    <div class="features-inner">
      <div class="features-label">Funcionalidades</div>
      <h2 class="features-title">Tudo que você precisa em um lugar</h2>
      <p class="features-sub">Do provisionamento ao suporte, cada recurso foi pensado para simplificar sua operação.</p>
      <div class="features-grid">
        <div class="feature-card">
          <div class="feature-icon purple">
            <svg width="22" height="22" viewBox="0 0 22 22" fill="none"><rect x="2" y="6" width="18" height="5" rx="2" stroke="currentColor" stroke-width="1.7"/><rect x="2" y="13" width="18" height="5" rx="2" stroke="currentColor" stroke-width="1.7"/><circle cx="17" cy="8.5" r="1.2" fill="currentColor"/><circle cx="17" cy="15.5" r="1.2" fill="currentColor"/></svg>
          </div>
          <div class="feature-name">VPS Gerenciadas</div>
          <div class="feature-desc">Provisione, suspenda, reinicie e monitore suas VPS com poucos cliques. Terminal web integrado.</div>
        </div>
        <div class="feature-card">
          <div class="feature-icon blue">
            <svg width="22" height="22" viewBox="0 0 22 22" fill="none"><path d="M4 4l5 5-5 5M11 18h7" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </div>
          <div class="feature-name">Deploy de Aplicações</div>
          <div class="feature-desc">Faça deploy de aplicações Docker com um clique. Logs em tempo real e rollback fácil.</div>
        </div>
        <div class="feature-card">
          <div class="feature-icon green">
            <svg width="22" height="22" viewBox="0 0 22 22" fill="none"><path d="M11 3v12M7 11l4 4 4-4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><path d="M4 17h14" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
          </div>
          <div class="feature-name">Backups Automáticos</div>
          <div class="feature-desc">Backups agendados com download direto. Nunca perca dados importantes.</div>
        </div>
        <div class="feature-card">
          <div class="feature-icon orange">
            <svg width="22" height="22" viewBox="0 0 22 22" fill="none"><rect x="2" y="6" width="18" height="13" rx="2" stroke="currentColor" stroke-width="1.7"/><path d="M2 9h18" stroke="currentColor" stroke-width="1.7"/><path d="M7 14h4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
          </div>
          <div class="feature-name">E-mails Profissionais</div>
          <div class="feature-desc">Crie contas de e-mail no seu próprio domínio. Integração com Mailcow e webmail incluso.</div>
        </div>
        <div class="feature-card">
          <div class="feature-icon indigo">
            <svg width="22" height="22" viewBox="0 0 22 22" fill="none"><path d="M4 5h14a2 2 0 012 2v8a2 2 0 01-2 2H7l-5 3V7a2 2 0 012-2z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg>
          </div>
          <div class="feature-name">Suporte Integrado</div>
          <div class="feature-desc">Chat ao vivo e sistema de tickets. Histórico completo de atendimentos.</div>
        </div>
        <div class="feature-card">
          <div class="feature-icon rose">
            <svg width="22" height="22" viewBox="0 0 22 22" fill="none"><path d="M2 14l5-5 3 3 5-6 4 4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><rect x="2" y="3" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.7"/></svg>
          </div>
          <div class="feature-name">Monitoramento</div>
          <div class="feature-desc">Acompanhe CPU, memória e disco em tempo real. Alertas automáticos por e-mail e WhatsApp.</div>
        </div>
      </div>
    </div>
  </section>

  <!-- Access cards -->
  <section class="access-section">
    <div class="access-inner">
      <div style="text-align:center;margin-bottom:40px;">
        <div class="features-label">Acesso</div>
        <h2 class="features-title">Escolha seu perfil</h2>
      </div>
      <div class="access-grid">
        <div class="access-card">
          <div class="access-card-icon purple">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"><circle cx="9" cy="7" r="3.5" stroke="currentColor" stroke-width="1.7"/><path d="M3 20c0-3.866 2.686-7 6-7s6 3.134 6 7" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><path d="M16 10c1.657 0 3 1.343 3 3v6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><circle cx="16" cy="6.5" r="2.5" stroke="currentColor" stroke-width="1.7"/></svg>
          </div>
          <div class="access-card-title">Área do cliente</div>
          <div class="access-card-desc">Acesse suas VPS, aplicações, e-mails, backups, monitoramento e suporte.</div>
          <div class="access-card-actions">
            <a href="/cliente/entrar" class="botao sm">Entrar</a>
            <a href="/cliente/criar-conta" class="botao ghost sm"><?php echo $_trial_ativo ? View::e($_trial_label) : 'Criar conta'; ?></a>
          </div>
        </div>
        <div class="access-card">
          <div class="access-card-icon indigo">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"><rect x="3" y="11" width="18" height="10" rx="2" stroke="currentColor" stroke-width="1.7"/><path d="M7 11V7a5 5 0 0110 0v4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
          </div>
          <div class="access-card-title">Painel administrativo</div>
          <div class="access-card-desc">Gerencie clientes, servidores, cobranças, tickets e toda a infraestrutura.</div>
          <div class="access-card-actions">
            <a href="/equipe/entrar" class="botao sm sec">Entrar como equipe</a>
          </div>
        </div>
        <?php if (!empty($equipe_logada)): ?>
        <div class="access-card" style="border-color:#e2e8f0;background:#f8fafc;">
          <div class="access-card-icon" style="background:#f1f5f9;color:#475569;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M4 6h16M4 10h16M4 14h10" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><circle cx="18" cy="17" r="3" stroke="currentColor" stroke-width="1.7"/><path d="M18 15.5v1.5l1 1" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
          </div>
          <div class="access-card-title" style="color:#475569;">API interna</div>
          <div class="access-card-desc">Endpoints internos visíveis apenas para a equipe autenticada.</div>
          <div class="access-card-actions">
            <a href="/api/saude" class="botao ghost sm" target="_blank">/api/saude</a>
            <a href="/public/api/openapi.yaml" class="botao ghost sm" target="_blank">OpenAPI</a>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- CTA -->
  <section class="cta-section">
    <div class="cta-inner">
      <h2 class="cta-title"><?php echo $_trial_ativo ? 'Comece seu teste grátis agora' : 'Pronto para começar?'; ?></h2>
      <p class="cta-sub"><?php echo $_trial_ativo && $_trial_desc !== '' ? View::e($_trial_desc) : 'Crie sua conta agora e tenha sua infraestrutura funcionando em minutos.'; ?></p>
      <div class="cta-btns">
        <a href="/cliente/criar-conta" class="hero-btn primary"><?php echo $_trial_ativo ? View::e($_trial_label) : 'Criar conta grátis'; ?></a>
        <a href="/contato" class="hero-btn outline">Falar com a equipe</a>
      </div>
    </div>
  </section>

  <?php require __DIR__ . '/_partials/footer.php'; ?>
</body>
</html>
