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
html { scroll-behavior: smooth; }
body { font-family: system-ui, -apple-system, 'Segoe UI', Roboto, Ubuntu, sans-serif; background: #fff; color: #0f172a; overflow-x: hidden; }
.navbar { position: sticky; top: 0; z-index: 100; background: rgba(11,28,61,.92); backdrop-filter: blur(16px) saturate(180%); -webkit-backdrop-filter: blur(16px) saturate(180%); border-bottom: 1px solid rgba(255,255,255,.08); padding: 0 24px; }
.navbar-inner { max-width: 1160px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; height: 60px; gap: 16px; }
.navbar-brand { display: flex; align-items: center; gap: 10px; text-decoration: none; color: #fff; flex-shrink: 0; }
.navbar-brand img { height: 30px; width: auto; }
.navbar-brand-name { font-size: 16px; font-weight: 700; letter-spacing: -.01em; }
.navbar-links { display: flex; align-items: center; gap: 2px; flex: 1; justify-content: center; }
.navbar-links a { color: rgba(255,255,255,.75); text-decoration: none; font-size: 13.5px; font-weight: 500; padding: 6px 12px; border-radius: 8px; transition: color .15s, background .15s; }
.navbar-links a:hover { color: #fff; background: rgba(255,255,255,.1); }
.navbar-links a.ativo { color: #a5b4fc; }
.navbar-actions { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
.navbar-btn { display: inline-flex; align-items: center; gap: 6px; padding: 7px 16px; border-radius: 10px; font-size: 13px; font-weight: 600; text-decoration: none; transition: opacity .15s, transform .1s; }
.navbar-btn:hover { opacity: .88; transform: translateY(-1px); }
.navbar-btn.ghost { color: rgba(255,255,255,.85); border: 1.5px solid rgba(255,255,255,.2); }
.navbar-btn.solid { background: linear-gradient(135deg,#4F46E5,#7C3AED); color: #fff; }
.navbar-hamburger{display:none;flex-direction:column;justify-content:center;gap:5px;width:36px;height:36px;background:none;border:none;cursor:pointer;padding:4px;flex-shrink:0}
.navbar-hamburger span{display:block;height:2px;background:#fff;border-radius:2px;transition:transform .25s,opacity .25s}
.navbar-hamburger.open span:nth-child(1){transform:translateY(7px) rotate(45deg)}
.navbar-hamburger.open span:nth-child(2){opacity:0}
.navbar-hamburger.open span:nth-child(3){transform:translateY(-7px) rotate(-45deg)}
.navbar-drawer{display:none;position:fixed;top:60px;left:0;right:0;bottom:0;background:rgba(6,13,31,.97);z-index:99;padding:24px 20px;flex-direction:column;gap:4px;overflow-y:auto}
.navbar-drawer.open{display:flex}
.navbar-drawer a{color:rgba(255,255,255,.8);font-size:16px;font-weight:500;padding:14px 16px;border-radius:10px;border-bottom:1px solid rgba(255,255,255,.06);transition:background .15s,color .15s}
.navbar-drawer a:hover{background:rgba(255,255,255,.08);color:#fff}
.navbar-drawer .drawer-actions{display:flex;flex-direction:column;gap:10px;margin-top:20px;padding-top:20px;border-top:1px solid rgba(255,255,255,.1)}
.navbar-drawer .drawer-actions a{border-bottom:none;text-align:center;font-weight:700}
.navbar-drawer .drawer-actions .ghost{border:1.5px solid rgba(255,255,255,.25)}
.navbar-drawer .drawer-actions .solid{background:linear-gradient(135deg,#4F46E5,#7C3AED);color:#fff}
@media (max-width: 768px) { .navbar-links { display: none; } .navbar-btn.ghost { display: none; } .navbar-btn.solid{display:none} .navbar-hamburger{display:flex} .navbar-actions .lang-dropdown{display:none} }
</style>
</head>
<body>
<nav class="navbar">
  <div class="navbar-inner">
    <a href="/" class="navbar-brand">
      <?php if ($_logo !== ''): ?>
        <img src="<?php echo View::e($_logo); ?>" alt="logo" />
      <?php else: ?>
        <svg width="28" height="28" viewBox="0 0 28 28" fill="none"><rect width="28" height="28" rx="8" fill="#4F46E5"/><path d="M7 14h14M14 7v14" stroke="#fff" stroke-width="2.5" stroke-linecap="round"/></svg>
      <?php endif; ?>
      <span class="navbar-brand-name"><?php echo View::e($_nome); ?></span>
    </a>
    <div class="navbar-links">
      <a href="#funcionalidades"><?php echo View::e(I18n::t('infra.nav_funcionalidades')); ?></a>
      <a href="#planos"><?php echo View::e(I18n::t('infra.nav_planos')); ?></a>
      <a href="#tecnologia"><?php echo View::e(I18n::t('infra.nav_tecnologia')); ?></a>
      <a href="/status"><?php echo View::e(I18n::t('infra.nav_status')); ?></a>
      <a href="/contato"><?php echo View::e(I18n::t('infra.nav_contato')); ?></a>
    </div>
    <div class="navbar-actions">
      <?php require __DIR__ . '/_partials/idioma.php'; ?>
      <a href="/cliente/entrar" class="navbar-btn ghost"><?php echo View::e(I18n::t('infra.nav_entrar')); ?></a>
      <a href="/cliente/criar-conta" class="navbar-btn solid">
        <?php echo $_trial_ativo ? View::e($_trial_label) : View::e(I18n::t('infra.nav_criar_conta')); ?>
      </a>
      <button class="navbar-hamburger" id="navHamburger" aria-label="Menu" onclick="toggleDrawer()">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>
</nav>

<!-- DRAWER MOBILE -->
<div class="navbar-drawer" id="navDrawer">
  <a href="#funcionalidades" onclick="closeDrawer()"><?php echo View::e(I18n::t('infra.nav_funcionalidades')); ?></a>
  <a href="#planos" onclick="closeDrawer()"><?php echo View::e(I18n::t('infra.nav_planos')); ?></a>
  <a href="#tecnologia" onclick="closeDrawer()"><?php echo View::e(I18n::t('infra.nav_tecnologia')); ?></a>
  <a href="/status" onclick="closeDrawer()"><?php echo View::e(I18n::t('infra.nav_status')); ?></a>
  <a href="/contato" onclick="closeDrawer()"><?php echo View::e(I18n::t('infra.nav_contato')); ?></a>
  <div class="drawer-actions">
    <a href="/cliente/entrar" class="ghost navbar-btn" onclick="closeDrawer()"><?php echo View::e(I18n::t('infra.nav_entrar')); ?></a>
    <a href="/cliente/criar-conta" class="solid navbar-btn" onclick="closeDrawer()"><?php echo $_trial_ativo ? View::e($_trial_label) : View::e(I18n::t('infra.nav_criar_conta')); ?></a>
  </div>
</div>

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
.footer { background: #060d1f; color: rgba(255,255,255,.5); padding: 56px 24px 32px; }
.footer-inner { max-width: 1100px; margin: 0 auto; }
.footer-grid { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 40px; margin-bottom: 48px; }
@media (max-width: 768px) { .footer-grid { grid-template-columns: 1fr 1fr; gap: 28px; } }
@media (max-width: 480px) { .footer-grid { grid-template-columns: 1fr; } }
.footer-brand-name { font-size: 16px; font-weight: 700; color: #fff; margin-bottom: 10px; }
.footer-brand-desc { font-size: 13px; line-height: 1.7; max-width: 260px; }
.footer-col-title { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: rgba(255,255,255,.35); margin-bottom: 14px; }
.footer-links { list-style: none; display: flex; flex-direction: column; gap: 9px; }
.footer-links a { color: rgba(255,255,255,.5); text-decoration: none; font-size: 13px; transition: color .15s; }
.footer-links a:hover { color: #fff; }
.footer-bottom { border-top: 1px solid rgba(255,255,255,.07); padding-top: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; font-size: 12px; }
.footer-status-dot { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; color: rgba(255,255,255,.4); }
.footer-status-dot::before { content: ''; width: 7px; height: 7px; border-radius: 50%; background: #22c55e; display: inline-block; }
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
    <div class="section-label"><?php echo View::e(I18n::t('infra.plans_label')); ?></div>
    <h2 class="section-title"><?php echo View::e(I18n::t('infra.plans_titulo')); ?></h2>
    <p class="section-sub"><?php echo View::e(I18n::t('infra.plans_sub')); ?></p>
    <?php if (!empty($_planos)): ?>
    <div class="plans-grid">
      <?php foreach ($_planos as $_i => $_p):
        $_specs = [];
        if (!empty($_p['specs_json'])) { $_specs = json_decode((string)$_p['specs_json'], true) ?: []; }
        $_vcpu  = (int)($_specs['vcpu'] ?? $_specs['cpu'] ?? 0);
        $_ram   = (int)($_specs['ram_gb'] ?? 0);
        $_ramMb = (int)($_specs['ram_mb'] ?? 0);
        $_disco = (int)($_specs['disco_gb'] ?? $_specs['storage_gb'] ?? 0);
        $_bw    = (int)($_specs['bandwidth_gb'] ?? 0);
        $_price = (float)$_p['price'];
        $_cycle = (string)($_p['billing_cycle'] ?? 'monthly');
        $_cycleLabel = $_cycle === 'yearly' ? I18n::t('infra.plans_ano') : I18n::t('infra.plans_mes');
        $_cycleBillingLabel = $_cycle === 'yearly' ? I18n::t('infra.plans_anual') : I18n::t('infra.plans_mensal');
        $_destaque = $_i === 1;
      ?>
      <div class="plan-card <?php echo $_destaque ? 'destaque' : ''; ?>">
        <?php if ($_destaque): ?><div class="plan-badge"><?php echo View::e(I18n::t('infra.plans_popular')); ?></div><?php endif; ?>
        <div class="plan-name"><?php echo View::e((string)$_p['name']); ?></div>
        <?php if (!empty($_p['description'])): ?><div class="plan-desc"><?php echo View::e((string)$_p['description']); ?></div><?php endif; ?>
        <div class="plan-price"><?php echo View::e(I18n::preco($_price)); ?><span><?php echo View::e($_cycleLabel); ?></span></div>
        <div class="plan-cycle"><?php echo View::e($_cycleBillingLabel); ?></div>
        <ul class="plan-specs">
          <?php if ($_vcpu > 0): ?><li><svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M2 7l3 3 7-7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg><?php echo $_vcpu; ?> vCPU</li><?php endif; ?>
          <?php if ($_ram > 0): ?><li><svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M2 7l3 3 7-7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg><?php echo $_ram; ?> GB RAM</li><?php elseif ($_ramMb > 0): ?><li><svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M2 7l3 3 7-7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg><?php echo $_ramMb; ?> MB RAM</li><?php endif; ?>
          <?php if ($_disco > 0): ?><li><svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M2 7l3 3 7-7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg><?php echo $_disco; ?> GB SSD</li><?php endif; ?>
          <?php if ($_bw > 0): ?><li><svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M2 7l3 3 7-7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg><?php echo $_bw; ?> GB bandwidth</li><?php endif; ?>
          <li><svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M2 7l3 3 7-7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg><?php echo View::e(I18n::t('infra.plans_backups')); ?></li>
          <li><svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M2 7l3 3 7-7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg><?php echo View::e(I18n::t('infra.plans_monitor')); ?></li>
          <li><svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M2 7l3 3 7-7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg><?php echo View::e(I18n::t('infra.plans_suporte')); ?></li>
        </ul>
        <div class="plan-cta"><a href="/cliente/criar-conta" class="botao" style="width:100%;justify-content:center;"><?php echo $_trial_ativo ? View::e($_trial_label) : View::e(I18n::t('infra.plans_comecar')); ?></a></div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="plans-grid"><div class="plan-empty"><?php echo View::e(I18n::t('infra.plans_empty')); ?> <a href="/contato"><?php echo View::e(I18n::t('infra.plans_contato')); ?></a> <?php echo View::e(I18n::t('infra.plans_saber_mais')); ?></div></div>
    <?php endif; ?>
    <p style="text-align:center;margin-top:24px;font-size:13px;color:#94a3b8;"><?php echo View::e(I18n::t('infra.plans_custom')); ?> <a href="/contato"><?php echo View::e(I18n::t('infra.plans_fale')); ?></a></p>
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

<footer class="footer">
  <div class="footer-inner">
    <div class="footer-grid">
      <div><div class="footer-brand-name"><?php echo View::e($_nome); ?></div><div class="footer-brand-desc"><?php echo View::e(I18n::t('infra.footer_desc')); ?></div></div>
      <div><div class="footer-col-title"><?php echo View::e(I18n::t('infra.footer_col_produto')); ?></div><ul class="footer-links"><li><a href="#funcionalidades"><?php echo View::e(I18n::t('infra.footer_funcionalidades')); ?></a></li><li><a href="#planos"><?php echo View::e(I18n::t('infra.footer_planos')); ?></a></li><li><a href="#tecnologia"><?php echo View::e(I18n::t('infra.footer_tecnologia')); ?></a></li><li><a href="/changelog">Changelog</a></li></ul></div>
      <div><div class="footer-col-title"><?php echo View::e(I18n::t('infra.footer_col_suporte')); ?></div><ul class="footer-links"><li><a href="/contato"><?php echo View::e(I18n::t('infra.footer_contato')); ?></a></li><li><a href="/status"><?php echo View::e(I18n::t('infra.footer_status')); ?></a></li><li><a href="/cliente/tickets"><?php echo View::e(I18n::t('infra.footer_tickets')); ?></a></li></ul></div>
      <div><div class="footer-col-title"><?php echo View::e(I18n::t('infra.footer_col_legal')); ?></div><ul class="footer-links"><li><a href="/termos"><?php echo View::e(I18n::t('infra.footer_termos')); ?></a></li><li><a href="/privacidade"><?php echo View::e(I18n::t('infra.footer_privacidade')); ?></a></li></ul></div>
    </div>
    <div class="footer-bottom">
      <div><?php echo View::e(SistemaConfig::copyrightText()); ?> · <?php echo View::e($_nome); ?> v<?php echo View::e(SistemaConfig::versao()); ?></div>
      <div class="footer-status-dot"><?php echo View::e(I18n::t('infra.footer_sistema_op')); ?></div>
    </div>
  </div>
</footer>

<script>
function toggleFaq(btn) {
  const a = btn.nextElementSibling;
  const open = a.classList.contains('open');
  document.querySelectorAll('.faq-a.open').forEach(el => el.classList.remove('open'));
  document.querySelectorAll('.faq-q.open').forEach(el => el.classList.remove('open'));
  if (!open) { a.classList.add('open'); btn.classList.add('open'); }
}
function toggleDrawer(){
  var h=document.getElementById('navHamburger');
  var d=document.getElementById('navDrawer');
  var open=d.classList.toggle('open');
  h.classList.toggle('open',open);
  document.body.style.overflow=open?'hidden':'';
}
function closeDrawer(){
  document.getElementById('navDrawer').classList.remove('open');
  document.getElementById('navHamburger').classList.remove('open');
  document.body.style.overflow='';
}
(function() {
  const sections = document.querySelectorAll('section[id]');
  const links = document.querySelectorAll('.navbar-links a[href^="#"]');
  if (!links.length) return;
  const obs = new IntersectionObserver(entries => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        links.forEach(l => l.classList.remove('ativo'));
        const match = document.querySelector('.navbar-links a[href="#' + e.target.id + '"]');
        if (match) match.classList.add('ativo');
      }
    });
  }, { threshold: 0.4 });
  sections.forEach(s => obs.observe(s));
})();
</script>
<?php require __DIR__ . '/_partials/chat-widget.php'; ?>
</body>
</html>
