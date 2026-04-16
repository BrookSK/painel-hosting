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
$_planos      = is_array($planos ?? null) ? $planos : [];
$_topo_hide_inicio = true;
?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <?php require __DIR__ . '/_partials/seo.php'; ?>
  <?php require __DIR__ . '/_partials/estilo.php'; ?>
  <style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html { scroll-behavior: smooth; overflow-x: hidden; }
body { font-family: system-ui, -apple-system, 'Segoe UI', Roboto, Ubuntu, sans-serif; background: #fff; color: #0f172a; }
</style>
</head>
<body>
<?php require __DIR__ . '/_partials/navbar-publica.php'; ?>

<style>
.hero { position: relative; overflow: hidden; background: linear-gradient(135deg, #060d1f 0%, #0B1C3D 30%, #1e3a8a 60%, #4F46E5 85%, #7C3AED 100%); color: #fff; padding: 100px 24px 110px; text-align: center; }
.hero-grid-bg { position: absolute; inset: 0; pointer-events: none; background-image: linear-gradient(rgba(255,255,255,.04) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.04) 1px, transparent 1px); background-size: 48px 48px; mask-image: radial-gradient(ellipse 80% 80% at 50% 50%, black 40%, transparent 100%); }
.hero-glow { position: absolute; inset: 0; pointer-events: none; background: radial-gradient(ellipse 70% 60% at 60% 40%, rgba(124,58,237,.4) 0%, transparent 70%); }
.hero-inner { max-width: 760px; margin: 0 auto; position: relative; }
.hero-eyebrow { display: inline-flex; align-items: center; gap: 8px; background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.15); color: #c4b5fd; font-size: 12px; font-weight: 600; padding: 5px 14px; border-radius: 999px; margin-bottom: 24px; backdrop-filter: blur(8px); letter-spacing: .04em; text-transform: uppercase; }
.hero-eyebrow-dot { width: 6px; height: 6px; border-radius: 50%; background: #a78bfa; animation: pulse-dot 2s infinite; }
@keyframes pulse-dot { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.5;transform:scale(.8)} }
.hero-title { font-size: clamp(32px, 6vw, 58px); font-weight: 900; line-height: 1.1; letter-spacing: -.03em; margin-bottom: 20px; }
.hero-title .grad { background: linear-gradient(135deg, #a5b4fc, #c4b5fd, #f0abfc); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
.hero-sub { font-size: 17px; opacity: .8; line-height: 1.7; margin-bottom: 40px; max-width: 580px; margin-left: auto; margin-right: auto; }
.hero-ctas { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; margin-bottom: 20px; }
.hero-btn { display: inline-flex; align-items: center; gap: 8px; padding: 14px 28px; border-radius: 14px; font-size: 15px; font-weight: 700; text-decoration: none; transition: transform .15s, box-shadow .15s; }
.hero-btn:hover { transform: translateY(-2px); }
.hero-btn.primary { background: #fff; color: #4F46E5; box-shadow: 0 4px 20px rgba(255,255,255,.2); }
.hero-btn.primary:hover { box-shadow: 0 8px 32px rgba(255,255,255,.3); }
.hero-btn.outline { background: rgba(255,255,255,.1); color: #fff; border: 1.5px solid rgba(255,255,255,.25); backdrop-filter: blur(8px); }
.hero-btn.outline:hover { background: rgba(255,255,255,.18); }
.hero-trial-note { font-size: 13px; opacity: .6; margin-bottom: 40px; }
.hero-badges { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; }
.hero-badge { display: inline-flex; align-items: center; gap: 6px; background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.12); color: #e0e7ff; font-size: 12px; font-weight: 500; padding: 6px 14px; border-radius: 999px; backdrop-filter: blur(4px); }
.stats-bar { background: #0f172a; padding: 28px 24px; }
.stats-bar-inner { max-width: 1000px; margin: 0 auto; display: grid; grid-template-columns: repeat(4, 1fr); gap: 0; }
.stat-item { text-align: center; padding: 8px 16px; border-right: 1px solid rgba(255,255,255,.08); }
.stat-item:last-child { border-right: none; }
.stat-num { font-size: 28px; font-weight: 800; color: #a5b4fc; line-height: 1; margin-bottom: 4px; }
.stat-lbl { font-size: 12px; color: rgba(255,255,255,.5); font-weight: 500; }
@media (max-width: 640px) { .stats-bar-inner { grid-template-columns: 1fr 1fr; } .stat-item:nth-child(2) { border-right: none; } }
.section { padding: 88px 24px; }
.section.alt { background: #f8fafc; }
.section.dark { background: #060d1f; color: #fff; }
.section-inner { max-width: 1100px; margin: 0 auto; }
.section-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .12em; color: #7C3AED; margin-bottom: 10px; text-align: center; }
.section-label.light { color: #a78bfa; }
.section-title { font-size: clamp(22px, 3.5vw, 34px); font-weight: 800; color: #0f172a; text-align: center; margin-bottom: 10px; letter-spacing: -.02em; }
.section-title.light { color: #fff; }
.section-sub { font-size: 15px; color: #64748b; text-align: center; max-width: 540px; margin: 0 auto 56px; line-height: 1.7; }
.section-sub.light { color: rgba(255,255,255,.6); }
.feat-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
@media (max-width: 900px) { .feat-grid { grid-template-columns: 1fr 1fr; } }
@media (max-width: 560px) { .feat-grid { grid-template-columns: 1fr; } }
.feat-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 18px; padding: 26px; transition: box-shadow .2s, transform .2s, border-color .2s; }
.feat-card:hover { box-shadow: 0 10px 40px rgba(79,70,229,.1); transform: translateY(-3px); border-color: #c7d2fe; }
.feat-icon { width: 46px; height: 46px; border-radius: 13px; display: flex; align-items: center; justify-content: center; margin-bottom: 16px; }
.feat-icon.v { background: #f5f3ff; color: #7C3AED; } .feat-icon.b { background: #eff6ff; color: #3b82f6; } .feat-icon.g { background: #f0fdf4; color: #16a34a; } .feat-icon.o { background: #fff7ed; color: #ea580c; } .feat-icon.i { background: #eef2ff; color: #4F46E5; } .feat-icon.r { background: #fff1f2; color: #e11d48; } .feat-icon.c { background: #ecfdf5; color: #059669; } .feat-icon.y { background: #fefce8; color: #ca8a04; } .feat-icon.s { background: #f0f9ff; color: #0284c7; }
.feat-name { font-size: 15px; font-weight: 700; color: #0f172a; margin-bottom: 7px; }
.feat-desc { font-size: 13px; color: #64748b; line-height: 1.65; }
.feat-tag { display: inline-block; margin-top: 12px; font-size: 11px; font-weight: 600; padding: 3px 9px; border-radius: 999px; background: #f1f5f9; color: #475569; }
.steps { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; }
@media (max-width: 768px) { .steps { grid-template-columns: 1fr 1fr; } }
@media (max-width: 480px) { .steps { grid-template-columns: 1fr; } }
.step { text-align: center; padding: 8px; }
.step-num { width: 52px; height: 52px; border-radius: 50%; background: linear-gradient(135deg, #4F46E5, #7C3AED); color: #fff; font-size: 18px; font-weight: 800; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; box-shadow: 0 4px 16px rgba(79,70,229,.35); }
.step-title { font-size: 15px; font-weight: 700; color: #0f172a; margin-bottom: 8px; }
.step-desc { font-size: 13px; color: #64748b; line-height: 1.6; }
.plans-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px; }
.plan-card { background: #fff; border: 1.5px solid #e2e8f0; border-radius: 20px; padding: 28px; display: flex; flex-direction: column; transition: border-color .2s, box-shadow .2s, transform .2s; position: relative; }
.plan-card:hover { border-color: #7C3AED; box-shadow: 0 8px 32px rgba(124,58,237,.12); transform: translateY(-3px); }
.plan-card.destaque { border-color: #4F46E5; box-shadow: 0 8px 32px rgba(79,70,229,.18); }
.plan-badge { position: absolute; top: -12px; left: 50%; transform: translateX(-50%); background: linear-gradient(135deg, #4F46E5, #7C3AED); color: #fff; font-size: 11px; font-weight: 700; padding: 4px 14px; border-radius: 999px; white-space: nowrap; }
.plan-name { font-size: 17px; font-weight: 800; color: #0f172a; margin-bottom: 6px; }
.plan-desc { font-size: 13px; color: #64748b; margin-bottom: 20px; line-height: 1.5; }
.plan-price { font-size: 36px; font-weight: 900; color: #0f172a; line-height: 1; margin-bottom: 4px; }
.plan-price span { font-size: 16px; font-weight: 500; color: #64748b; }
.plan-cycle { font-size: 12px; color: #94a3b8; margin-bottom: 22px; }
.plan-specs { list-style: none; padding: 0; margin: 0 0 24px; display: flex; flex-direction: column; gap: 9px; }
.plan-specs li { display: flex; align-items: center; gap: 9px; font-size: 13px; color: #334155; }
.plan-specs li svg { flex-shrink: 0; color: #7C3AED; }
.plan-cta { margin-top: auto; }
.plan-empty { text-align: center; padding: 48px 24px; color: #94a3b8; font-size: 14px; grid-column: 1/-1; }
.tech-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
@media (max-width: 768px) { .tech-grid { grid-template-columns: 1fr 1fr; } }
@media (max-width: 480px) { .tech-grid { grid-template-columns: 1fr; } }
.tech-card { background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.08); border-radius: 16px; padding: 22px; transition: background .2s, border-color .2s; }
.tech-card:hover { background: rgba(255,255,255,.07); border-color: rgba(165,180,252,.3); }
.tech-card-icon { font-size: 28px; margin-bottom: 12px; }
.tech-card-name { font-size: 14px; font-weight: 700; color: #e2e8f0; margin-bottom: 6px; }
.tech-card-desc { font-size: 12px; color: rgba(255,255,255,.45); line-height: 1.6; }
.security-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
@media (max-width: 640px) { .security-grid { grid-template-columns: 1fr; } }
.sec-item { display: flex; gap: 14px; align-items: flex-start; }
.sec-icon { width: 40px; height: 40px; border-radius: 11px; background: rgba(124,58,237,.15); color: #a78bfa; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.sec-title { font-size: 14px; font-weight: 700; color: #e2e8f0; margin-bottom: 4px; }
.sec-desc { font-size: 12px; color: rgba(255,255,255,.45); line-height: 1.6; }
.faq-list { max-width: 720px; margin: 0 auto; display: flex; flex-direction: column; gap: 10px; }
.faq-item { border: 1px solid #e2e8f0; border-radius: 14px; overflow: hidden; }
.faq-q { width: 100%; background: #fff; border: none; cursor: pointer; padding: 16px 20px; text-align: left; font-size: 14px; font-weight: 600; color: #0f172a; display: flex; justify-content: space-between; align-items: center; gap: 12px; transition: background .15s; }
.faq-q:hover { background: #f8fafc; }
.faq-q svg { flex-shrink: 0; transition: transform .2s; color: #7C3AED; }
.faq-q.open svg { transform: rotate(180deg); }
.faq-a { display: none; padding: 0 20px 16px; font-size: 13px; color: #64748b; line-height: 1.7; }
.faq-a.open { display: block; }
.access-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px; max-width: 900px; margin: 0 auto; }
.access-card { border: 1.5px solid #e2e8f0; border-radius: 20px; padding: 28px; display: flex; flex-direction: column; gap: 14px; transition: border-color .2s, box-shadow .2s, transform .2s; background: #fff; }
.access-card:hover { border-color: #7C3AED; box-shadow: 0 6px 28px rgba(124,58,237,.1); transform: translateY(-2px); }
.access-icon { width: 50px; height: 50px; border-radius: 15px; display: flex; align-items: center; justify-content: center; }
.access-icon.v { background: #f5f3ff; color: #7C3AED; } .access-icon.i { background: #eef2ff; color: #4F46E5; } .access-icon.s { background: #f1f5f9; color: #475569; }
.access-title { font-size: 17px; font-weight: 700; color: #0f172a; }
.access-desc { font-size: 13px; color: #64748b; line-height: 1.6; flex: 1; }
.access-actions { display: flex; gap: 8px; flex-wrap: wrap; }
.cta-final { padding: 96px 24px; background: linear-gradient(135deg, #060d1f 0%, #0B1C3D 40%, #4F46E5 80%, #7C3AED 100%); text-align: center; color: #fff; position: relative; overflow: hidden; }
.cta-final::before { content: ''; position: absolute; inset: 0; pointer-events: none; background-image: linear-gradient(rgba(255,255,255,.03) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.03) 1px, transparent 1px); background-size: 48px 48px; }
.cta-final-inner { max-width: 620px; margin: 0 auto; position: relative; }
.cta-title { font-size: clamp(24px, 4vw, 38px); font-weight: 900; margin-bottom: 14px; letter-spacing: -.02em; }
.cta-sub { font-size: 16px; opacity: .75; margin-bottom: 36px; line-height: 1.7; }
.cta-btns { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
</style>

<section class="hero">
  <div class="hero-grid-bg"></div>
  <div class="hero-glow"></div>
  <div class="hero-inner">
    <div class="hero-eyebrow"><span class="hero-eyebrow-dot"></span><?php echo View::e(I18n::t('infra.hero_eyebrow')); ?></div>
    <h1 class="hero-title"><?php echo View::e(I18n::t('infra.hero_titulo')); ?><br><span class="grad"><?php echo View::e(I18n::t('infra.hero_titulo_grad')); ?></span></h1>
    <p class="hero-sub"><?php echo View::e(I18n::t('infra.hero_sub')); ?></p>
    <div class="hero-ctas">
      <a href="/cliente/criar-conta" class="hero-btn primary">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M8 2v12M2 8h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
        <?php echo $_trial_ativo ? View::e($_trial_label) : View::e(I18n::t('infra.hero_criar_conta')); ?>
      </a>
      <a href="#planos" class="hero-btn outline"><?php echo View::e(I18n::t('infra.hero_ver_planos')); ?></a>
    </div>
    <?php if ($_trial_ativo && $_trial_desc !== ''): ?>
    <p class="hero-trial-note"><?php echo View::e($_trial_desc); ?></p>
    <?php endif; ?>
    <div class="hero-badges">
      <span class="hero-badge"><svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M2 6l3 3 5-5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg><?php echo View::e(I18n::t('infra.badge_deploy')); ?></span>
      <span class="hero-badge"><svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M2 6l3 3 5-5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg><?php echo View::e(I18n::t('infra.badge_backups')); ?></span>
      <span class="hero-badge"><svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M2 6l3 3 5-5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg><?php echo View::e(I18n::t('infra.badge_suporte')); ?></span>
      <span class="hero-badge"><svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M2 6l3 3 5-5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg><?php echo View::e(I18n::t('infra.badge_monitoramento')); ?></span>
      <span class="hero-badge"><svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M2 6l3 3 5-5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg><?php echo View::e(I18n::t('infra.badge_terminal')); ?></span>
    </div>
  </div>
</section>

<div class="stats-bar">
  <div class="stats-bar-inner">
    <div class="stat-item"><div class="stat-num">99.9%</div><div class="stat-lbl"><?php echo View::e(I18n::t('infra.stat_uptime')); ?></div></div>
    <div class="stat-item"><div class="stat-num">&lt;60s</div><div class="stat-lbl"><?php echo View::e(I18n::t('infra.stat_provisioning')); ?></div></div>
    <div class="stat-item"><div class="stat-num">24/7</div><div class="stat-lbl"><?php echo View::e(I18n::t('infra.stat_monitoring')); ?></div></div>
    <div class="stat-item"><div class="stat-num">AES-256</div><div class="stat-lbl"><?php echo View::e(I18n::t('infra.stat_encryption')); ?></div></div>
  </div>
</div>

<section class="section alt" id="funcionalidades">
  <div class="section-inner">
    <div class="section-label"><?php echo View::e(I18n::t('infra.feat_label')); ?></div>
    <h2 class="section-title"><?php echo View::e(I18n::t('infra.feat_titulo')); ?></h2>
    <p class="section-sub"><?php echo View::e(I18n::t('infra.feat_sub')); ?></p>
    <div class="feat-grid">
      <div class="feat-card"><div class="feat-icon v"><svg width="22" height="22" viewBox="0 0 22 22" fill="none"><rect x="2" y="6" width="18" height="5" rx="2" stroke="currentColor" stroke-width="1.7"/><rect x="2" y="13" width="18" height="5" rx="2" stroke="currentColor" stroke-width="1.7"/><circle cx="17" cy="8.5" r="1.2" fill="currentColor"/><circle cx="17" cy="15.5" r="1.2" fill="currentColor"/></svg></div><div class="feat-name"><?php echo View::e(I18n::t('infra.feat_vps')); ?></div><div class="feat-desc"><?php echo View::e(I18n::t('infra.feat_vps_desc')); ?></div><span class="feat-tag">KVM / Docker</span></div>
      <div class="feat-card"><div class="feat-icon b"><svg width="22" height="22" viewBox="0 0 22 22" fill="none"><rect x="2" y="4" width="18" height="14" rx="2" stroke="currentColor" stroke-width="1.7"/><path d="M6 8l3 2-3 2M12 12h4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></div><div class="feat-name"><?php echo View::e(I18n::t('infra.feat_terminal')); ?></div><div class="feat-desc"><?php echo View::e(I18n::t('infra.feat_terminal_desc')); ?></div><span class="feat-tag">SSH / WebSocket</span></div>
      <div class="feat-card"><div class="feat-icon i"><svg width="22" height="22" viewBox="0 0 22 22" fill="none"><path d="M4 4l5 5-5 5M11 18h7" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></div><div class="feat-name"><?php echo View::e(I18n::t('infra.feat_deploy')); ?></div><div class="feat-desc"><?php echo View::e(I18n::t('infra.feat_deploy_desc')); ?></div><span class="feat-tag">Docker</span></div>
      <div class="feat-card"><div class="feat-icon g"><svg width="22" height="22" viewBox="0 0 22 22" fill="none"><path d="M11 3v12M7 11l4 4 4-4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><path d="M4 17h14" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></div><div class="feat-name"><?php echo View::e(I18n::t('infra.feat_backups')); ?></div><div class="feat-desc"><?php echo View::e(I18n::t('infra.feat_backups_desc')); ?></div><span class="feat-tag">Agendado</span></div>
      <div class="feat-card"><div class="feat-icon r"><svg width="22" height="22" viewBox="0 0 22 22" fill="none"><path d="M2 14l5-5 3 3 5-6 4 4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><rect x="2" y="3" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.7"/></svg></div><div class="feat-name"><?php echo View::e(I18n::t('infra.feat_monitor')); ?></div><div class="feat-desc"><?php echo View::e(I18n::t('infra.feat_monitor_desc')); ?></div><span class="feat-tag">Tempo real</span></div>
      <div class="feat-card"><div class="feat-icon o"><svg width="22" height="22" viewBox="0 0 22 22" fill="none"><rect x="2" y="6" width="18" height="13" rx="2" stroke="currentColor" stroke-width="1.7"/><path d="M2 9h18" stroke="currentColor" stroke-width="1.7"/><path d="M7 14h4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></div><div class="feat-name"><?php echo View::e(I18n::t('infra.feat_email')); ?></div><div class="feat-desc"><?php echo View::e(I18n::t('infra.feat_email_desc')); ?></div><span class="feat-tag">Mailcow</span></div>
      <div class="feat-card"><div class="feat-icon c"><svg width="22" height="22" viewBox="0 0 22 22" fill="none"><path d="M4 5h14a2 2 0 012 2v8a2 2 0 01-2 2H7l-5 3V7a2 2 0 012-2z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg></div><div class="feat-name"><?php echo View::e(I18n::t('infra.feat_suporte')); ?></div><div class="feat-desc"><?php echo View::e(I18n::t('infra.feat_suporte_desc')); ?></div><span class="feat-tag">Chat + Tickets</span></div>
      <div class="feat-card"><div class="feat-icon y"><svg width="22" height="22" viewBox="0 0 22 22" fill="none"><path d="M11 2l2.4 5 5.6.8-4 3.9.9 5.5L11 14.5l-4.9 2.7.9-5.5L3 7.8l5.6-.8L11 2z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg></div><div class="feat-name"><?php echo View::e(I18n::t('infra.feat_planos')); ?></div><div class="feat-desc"><?php echo View::e(I18n::t('infra.feat_planos_desc')); ?></div><span class="feat-tag">Asaas / Stripe</span></div>
      <div class="feat-card"><div class="feat-icon s"><svg width="22" height="22" viewBox="0 0 22 22" fill="none"><path d="M11 3C7.686 3 5 5.686 5 9c0 2.21 1.197 4.14 2.97 5.19L8 18h6l.03-3.81C15.803 13.14 17 11.21 17 9c0-3.314-2.686-6-6-6z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg></div><div class="feat-name"><?php echo View::e(I18n::t('infra.feat_2fa')); ?></div><div class="feat-desc"><?php echo View::e(I18n::t('infra.feat_2fa_desc')); ?></div><span class="feat-tag">TOTP / RBAC</span></div>
    </div>
  </div>
</section>

<section class="section">
  <div class="section-inner">
    <div class="section-label"><?php echo View::e(I18n::t('infra.steps_label')); ?></div>
    <h2 class="section-title"><?php echo View::e(I18n::t('infra.steps_titulo')); ?></h2>
    <p class="section-sub"><?php echo View::e(I18n::t('infra.steps_sub')); ?></p>
    <div class="steps">
      <div class="step"><div class="step-num">1</div><div class="step-title"><?php echo View::e(I18n::t('infra.step1_titulo')); ?></div><div class="step-desc"><?php echo View::e(I18n::t('infra.step1_desc')); ?></div></div>
      <div class="step"><div class="step-num">2</div><div class="step-title"><?php echo View::e(I18n::t('infra.step2_titulo')); ?></div><div class="step-desc"><?php echo View::e(I18n::t('infra.step2_desc')); ?></div></div>
      <div class="step"><div class="step-num">3</div><div class="step-title"><?php echo View::e(I18n::t('infra.step3_titulo')); ?></div><div class="step-desc"><?php echo View::e(I18n::t('infra.step3_desc')); ?></div></div>
      <div class="step"><div class="step-num">4</div><div class="step-title"><?php echo View::e(I18n::t('infra.step4_titulo')); ?></div><div class="step-desc"><?php echo View::e(I18n::t('infra.step4_desc')); ?></div></div>
    </div>
  </div>
</section>

<section class="section alt" id="planos">
  <div class="section-inner">
    <?php
      // Reusar o partial de planos com carousel
      $planos = $_planos;
      $_accent = '#4F46E5';
      $_plan_type = 'vps';
      $_cta_base = '/contratar?plan_id=';
      require __DIR__ . '/solucoes/_planos-section.php';
    ?>
  </div>
</section>

<!-- TODOS OS PRODUTOS -->
<section class="section" id="produtos">
  <div class="section-inner">
    <div class="section-label">Nossos Produtos</div>
    <h2 class="section-title">Soluções para cada necessidade</h2>
    <p class="section-sub">Do WordPress ao C/C++, temos o produto certo para o seu projeto.</p>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:20px;margin-top:40px;">
      <?php
        $produtos = [
          ['/solucoes/vps',       '🖥️', 'VPS Gerenciada',       'Servidor virtual completo com acesso total, terminal web e monitoramento.',       '#4F46E5'],
          ['/solucoes/wordpress', '📝', 'WordPress Gerenciado', 'Hospedagem otimizada para WordPress com instalação em 1 clique.',                  '#1d4ed8'],
          ['/solucoes/webhosting','🌐', 'Web Hosting',          'Hospedagem com catálogo de apps, git deploy e gerenciador de arquivos.',            '#16a34a'],
          ['/solucoes/nodejs',    '⬢',  'Node.js App',          'Deploy de aplicações Node.js com banco de dados e git deploy.',                     '#d97706'],
          ['/solucoes/php',       '🐘', 'PHP / Laravel',        'Hospedagem PHP otimizada com Composer, MySQL e git deploy.',                        '#ea580c'],
          ['/solucoes/python',    '🐍', 'Python App',           'Deploy de Django, Flask e FastAPI com banco de dados e pip.',                        '#0891b2'],
          ['/solucoes/cpp',       '⚙️', 'C/C++ App',            'Aplicações compiladas de alta performance com GCC, CMake e git deploy.',             '#db2777'],
          ['/solucoes/aplicacoes','🚀', 'Deploy Automático',    'Pipeline de deploy via Git com catálogo de 50+ apps e zero downtime.',               '#8b5cf6'],
          ['/solucoes/email',     '💬', 'Comunicação',          'E-mail profissional, chat em tempo real e sistema de tickets.',                      '#e11d48'],
          ['/solucoes/devops',    '⚡', 'DevOps & Ferramentas', 'Terminal web, monitoramento, backups automáticos e logs centralizados.',              '#059669'],
          ['/solucoes/seguranca', '🔐', 'Segurança',            'Proteção DDoS, SSL automático, 2FA e isolamento de containers.',                    '#334155'],
        ];
        foreach ($produtos as [$href, $icon, $name, $desc, $color]):
      ?>
      <a href="<?php echo $href; ?>" style="background:#fff;border:2px solid #e2e8f0;border-radius:16px;padding:24px;text-decoration:none;color:inherit;display:flex;flex-direction:column;gap:10px;transition:all .2s;"
         onmouseover="this.style.borderColor='<?php echo $color; ?>';this.style.boxShadow='0 8px 30px <?php echo $color; ?>18';this.style.transform='translateY(-4px)'"
         onmouseout="this.style.borderColor='#e2e8f0';this.style.boxShadow='none';this.style.transform='none'">
        <div style="width:48px;height:48px;border-radius:12px;background:<?php echo $color; ?>12;display:flex;align-items:center;justify-content:center;font-size:24px;"><?php echo $icon; ?></div>
        <div style="font-weight:700;font-size:15px;color:#0f172a;"><?php echo View::e($name); ?></div>
        <div style="font-size:13px;color:#64748b;line-height:1.5;"><?php echo View::e($desc); ?></div>
        <div style="font-size:12px;font-weight:600;color:<?php echo $color; ?>;margin-top:auto;">Saiba mais →</div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section dark" id="tecnologia">
  <div class="section-inner">
    <div class="section-label light"><?php echo View::e(I18n::t('infra.tech_label')); ?></div>
    <h2 class="section-title light"><?php echo View::e(I18n::t('infra.tech_titulo')); ?></h2>
    <p class="section-sub light"><?php echo View::e(I18n::t('infra.tech_sub')); ?></p>
    <div class="tech-grid">
      <div class="tech-card"><div class="tech-card-icon">🐘</div><div class="tech-card-name"><?php echo View::e(I18n::t('infra.tech_php')); ?></div><div class="tech-card-desc"><?php echo View::e(I18n::t('infra.tech_php_desc')); ?></div></div>
      <div class="tech-card"><div class="tech-card-icon">🐬</div><div class="tech-card-name"><?php echo View::e(I18n::t('infra.tech_mysql')); ?></div><div class="tech-card-desc"><?php echo View::e(I18n::t('infra.tech_mysql_desc')); ?></div></div>
      <div class="tech-card"><div class="tech-card-icon">🐳</div><div class="tech-card-name"><?php echo View::e(I18n::t('infra.tech_docker')); ?></div><div class="tech-card-desc"><?php echo View::e(I18n::t('infra.tech_docker_desc')); ?></div></div>
      <div class="tech-card"><div class="tech-card-icon">⚡</div><div class="tech-card-name"><?php echo View::e(I18n::t('infra.tech_ws')); ?></div><div class="tech-card-desc"><?php echo View::e(I18n::t('infra.tech_ws_desc')); ?></div></div>
      <div class="tech-card"><div class="tech-card-icon">🔒</div><div class="tech-card-name"><?php echo View::e(I18n::t('infra.tech_sec')); ?></div><div class="tech-card-desc"><?php echo View::e(I18n::t('infra.tech_sec_desc')); ?></div></div>
      <div class="tech-card"><div class="tech-card-icon">📧</div><div class="tech-card-name"><?php echo View::e(I18n::t('infra.tech_mail')); ?></div><div class="tech-card-desc"><?php echo View::e(I18n::t('infra.tech_mail_desc')); ?></div></div>
    </div>
  </div>
</section>

<section class="section dark" style="padding-top:0;">
  <div class="section-inner">
    <div class="section-label light" style="margin-bottom:10px;"><?php echo View::e(I18n::t('infra.sec_label')); ?></div>
    <h2 class="section-title light" style="margin-bottom:10px;"><?php echo View::e(I18n::t('infra.sec_titulo')); ?></h2>
    <p class="section-sub light"><?php echo View::e(I18n::t('infra.sec_sub')); ?></p>
    <div class="security-grid">
      <div class="sec-item"><div class="sec-icon"><svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M10 2l6 3v5c0 3.5-2.5 6.5-6 7.5C4.5 16.5 2 13.5 2 10V5l8-3z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg></div><div><div class="sec-title"><?php echo View::e(I18n::t('infra.sec_2fa')); ?></div><div class="sec-desc"><?php echo View::e(I18n::t('infra.sec_2fa_desc')); ?></div></div></div>
      <div class="sec-item"><div class="sec-icon"><svg width="20" height="20" viewBox="0 0 20 20" fill="none"><rect x="3" y="9" width="14" height="9" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="M7 9V6a3 3 0 016 0v3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg></div><div><div class="sec-title"><?php echo View::e(I18n::t('infra.sec_rbac')); ?></div><div class="sec-desc"><?php echo View::e(I18n::t('infra.sec_rbac_desc')); ?></div></div></div>
      <div class="sec-item"><div class="sec-icon"><svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M10 2v4M10 14v4M2 10h4M14 10h4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><circle cx="10" cy="10" r="3" stroke="currentColor" stroke-width="1.6"/></svg></div><div><div class="sec-title"><?php echo View::e(I18n::t('infra.sec_csrf')); ?></div><div class="sec-desc"><?php echo View::e(I18n::t('infra.sec_csrf_desc')); ?></div></div></div>
      <div class="sec-item"><div class="sec-icon"><svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M4 6h12M4 10h12M4 14h7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg></div><div><div class="sec-title"><?php echo View::e(I18n::t('infra.sec_audit')); ?></div><div class="sec-desc"><?php echo View::e(I18n::t('infra.sec_audit_desc')); ?></div></div></div>
    </div>
  </div>
</section>

<section class="section alt">
  <div class="section-inner">
    <div class="section-label"><?php echo View::e(I18n::t('infra.faq_label')); ?></div>
    <h2 class="section-title"><?php echo View::e(I18n::t('infra.faq_titulo')); ?></h2>
    <p class="section-sub"><?php echo View::e(I18n::t('infra.faq_sub')); ?></p>
    <div class="faq-list">
      <div class="faq-item"><button class="faq-q" onclick="toggleFaq(this)"><?php echo View::e(I18n::t('infra.faq1_q')); ?><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg></button><div class="faq-a"><?php echo View::e(I18n::t('infra.faq1_a')); ?></div></div>
      <div class="faq-item"><button class="faq-q" onclick="toggleFaq(this)"><?php echo View::e(I18n::t('infra.faq2_q')); ?><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg></button><div class="faq-a"><?php echo View::e(I18n::t('infra.faq2_a')); ?></div></div>
      <div class="faq-item"><button class="faq-q" onclick="toggleFaq(this)"><?php echo View::e(I18n::t('infra.faq3_q')); ?><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg></button><div class="faq-a"><?php echo View::e(I18n::t('infra.faq3_a')); ?></div></div>
      <div class="faq-item"><button class="faq-q" onclick="toggleFaq(this)"><?php echo View::e(I18n::t('infra.faq4_q')); ?><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg></button><div class="faq-a"><?php echo View::e(I18n::t('infra.faq4_a')); ?></div></div>
      <div class="faq-item"><button class="faq-q" onclick="toggleFaq(this)"><?php echo View::e(I18n::t('infra.faq5_q')); ?><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg></button><div class="faq-a"><?php echo View::e(I18n::t('infra.faq5_a')); ?></div></div>
    </div>
  </div>
</section>

<section class="section">
  <div class="section-inner">
    <div class="section-label"><?php echo View::e(I18n::t('infra.access_label')); ?></div>
    <h2 class="section-title"><?php echo View::e(I18n::t('infra.access_titulo')); ?></h2>
    <p class="section-sub"><?php echo View::e(I18n::t('infra.access_sub')); ?></p>
    <div class="access-grid">
      <div class="access-card">
        <div class="access-icon v"><svg width="24" height="24" viewBox="0 0 24 24" fill="none"><circle cx="9" cy="7" r="3.5" stroke="currentColor" stroke-width="1.7"/><path d="M3 20c0-3.866 2.686-7 6-7s6 3.134 6 7" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><path d="M16 10c1.657 0 3 1.343 3 3v6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><circle cx="16" cy="6.5" r="2.5" stroke="currentColor" stroke-width="1.7"/></svg></div>
        <div class="access-title"><?php echo View::e(I18n::t('infra.access_cliente')); ?></div>
        <div class="access-desc"><?php echo View::e(I18n::t('infra.access_cliente_desc')); ?></div>
        <div class="access-actions"><a href="/cliente/entrar" class="botao sm"><?php echo View::e(I18n::t('infra.access_entrar')); ?></a><a href="/cliente/criar-conta" class="botao ghost sm"><?php echo $_trial_ativo ? View::e($_trial_label) : View::e(I18n::t('infra.nav_criar_conta')); ?></a></div>
      </div>
      <div class="access-card">
        <div class="access-icon i"><svg width="24" height="24" viewBox="0 0 24 24" fill="none"><rect x="3" y="11" width="18" height="10" rx="2" stroke="currentColor" stroke-width="1.7"/><path d="M7 11V7a5 5 0 0110 0v4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></div>
        <div class="access-title"><?php echo View::e(I18n::t('infra.access_admin')); ?></div>
        <div class="access-desc"><?php echo View::e(I18n::t('infra.access_admin_desc')); ?></div>
        <div class="access-actions"><a href="/equipe/entrar" class="botao sm sec"><?php echo View::e(I18n::t('infra.access_entrar_equipe')); ?></a></div>
      </div>
    </div>
  </div>
</section>

<section class="cta-final">
  <div class="cta-final-inner">
    <h2 class="cta-title"><?php echo $_trial_ativo ? View::e(I18n::t('infra.cta_trial')) : View::e(I18n::t('infra.cta_pronto')); ?></h2>
    <p class="cta-sub"><?php echo $_trial_ativo && $_trial_desc !== '' ? View::e($_trial_desc) : View::e(I18n::t('infra.cta_sub')); ?></p>
    <div class="cta-btns">
      <a href="/cliente/criar-conta" class="hero-btn primary"><?php echo $_trial_ativo ? View::e($_trial_label) : View::e(I18n::t('infra.cta_criar_conta')); ?></a>
      <a href="/contato" class="hero-btn outline"><?php echo View::e(I18n::t('infra.cta_falar')); ?></a>
    </div>
  </div>
</section>

<script>
function toggleFaq(btn) {
  const a = btn.nextElementSibling;
  const open = a.classList.contains('open');
  document.querySelectorAll('.faq-a.open').forEach(el => el.classList.remove('open'));
  document.querySelectorAll('.faq-q.open').forEach(el => el.classList.remove('open'));
  if (!open) { a.classList.add('open'); btn.classList.add('open'); }
}
</script>

<?php require __DIR__ . '/_partials/footer.php'; ?>
</body>
</html>
