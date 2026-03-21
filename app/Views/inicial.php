<?php
declare(strict_types=1);
use LRV\Core\I18n;
use LRV\Core\View;
use LRV\Core\SistemaConfig;

$_nome    = SistemaConfig::nome();
$_logo    = SistemaConfig::logoUrl();
$_empresa = SistemaConfig::empresaNome();
$_trial_ativo = !empty($trial_ativo);
$_trial_label = (string) ($trial_label ?? 'Contratar agora');
$_trial_desc  = (string) ($trial_desc ?? '');
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

/* ── Navbar ── */
.lp-nav { position: sticky; top: 0; z-index: 100; background: rgba(6,13,31,.96); backdrop-filter: blur(20px); border-bottom: 1px solid rgba(255,255,255,.07); padding: 0 24px; }
.lp-nav-inner { max-width: 1200px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; height: 62px; gap: 16px; }
.lp-nav-brand { display: flex; align-items: center; gap: 10px; text-decoration: none; color: #fff; flex-shrink: 0; }
.lp-nav-brand img { height: 32px; width: auto; }
.lp-nav-brand-name { font-size: 17px; font-weight: 800; letter-spacing: -.02em; }
.lp-nav-links { display: flex; align-items: center; gap: 4px; flex: 1; justify-content: center; }
.lp-nav-links a { color: rgba(255,255,255,.7); text-decoration: none; font-size: 14px; font-weight: 500; padding: 6px 13px; border-radius: 8px; transition: color .15s, background .15s; }
.lp-nav-links a:hover { color: #fff; background: rgba(255,255,255,.08); }
.lp-nav-actions { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
.lp-btn { display: inline-flex; align-items: center; gap: 7px; padding: 9px 20px; border-radius: 10px; font-size: 13.5px; font-weight: 700; text-decoration: none; transition: opacity .15s, transform .1s; white-space: nowrap; }
.lp-btn:hover { opacity: .88; transform: translateY(-1px); }
.lp-btn.ghost { color: rgba(255,255,255,.85); border: 1.5px solid rgba(255,255,255,.2); }
.lp-btn.solid { background: linear-gradient(135deg, #4F46E5, #7C3AED); color: #fff; box-shadow: 0 4px 16px rgba(79,70,229,.4); }
.lp-btn.solid:hover { box-shadow: 0 6px 24px rgba(79,70,229,.55); }
@media (max-width: 768px) { .lp-nav-links { display: none; } .lp-btn.ghost { display: none; } }

/* ── Hero split ── */
.lp-hero { background: linear-gradient(135deg, #060d1f 0%, #0b1c3d 45%, #1e1b4b 75%, #312e81 100%); color: #fff; padding: 80px 24px 90px; position: relative; overflow: hidden; }
.lp-hero::before { content: ''; position: absolute; inset: 0; background-image: linear-gradient(rgba(255,255,255,.03) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.03) 1px, transparent 1px); background-size: 56px 56px; pointer-events: none; }
.lp-hero-glow { position: absolute; top: -20%; right: -10%; width: 600px; height: 600px; border-radius: 50%; background: radial-gradient(circle, rgba(124,58,237,.35) 0%, transparent 70%); pointer-events: none; }
.lp-hero-inner { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1fr; gap: 64px; align-items: center; position: relative; }
@media (max-width: 900px) { .lp-hero-inner { grid-template-columns: 1fr; gap: 48px; } }
.lp-hero-badge { display: inline-flex; align-items: center; gap: 8px; background: rgba(79,70,229,.2); border: 1px solid rgba(165,180,252,.3); color: #a5b4fc; font-size: 12px; font-weight: 600; padding: 5px 14px; border-radius: 999px; margin-bottom: 22px; letter-spacing: .04em; text-transform: uppercase; }
.lp-hero-badge-dot { width: 7px; height: 7px; border-radius: 50%; background: #22c55e; animation: blink 2s infinite; }
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:.4} }
.lp-hero-title { font-size: clamp(30px, 4.5vw, 52px); font-weight: 900; line-height: 1.08; letter-spacing: -.03em; margin-bottom: 18px; }
.lp-hero-title em { font-style: italic; background: linear-gradient(135deg, #a5b4fc, #c4b5fd); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
.lp-hero-sub { font-size: 16px; color: rgba(255,255,255,.72); line-height: 1.75; margin-bottom: 36px; max-width: 480px; }
.lp-hero-ctas { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 28px; }
.lp-hero-trust { display: flex; gap: 16px; flex-wrap: wrap; }
.lp-hero-trust-item { display: flex; align-items: center; gap: 6px; font-size: 12px; color: rgba(255,255,255,.55); font-weight: 500; }
.lp-hero-trust-item svg { color: #22c55e; flex-shrink: 0; }

/* Server card */
.lp-server-card { background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.1); border-radius: 20px; padding: 28px; backdrop-filter: blur(12px); }
.lp-server-card-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
.lp-server-card-title { font-size: 13px; font-weight: 700; color: rgba(255,255,255,.6); text-transform: uppercase; letter-spacing: .08em; }
.lp-server-status { display: flex; align-items: center; gap: 6px; font-size: 12px; color: #22c55e; font-weight: 600; }
.lp-server-status::before { content: ''; width: 7px; height: 7px; border-radius: 50%; background: #22c55e; display: inline-block; animation: blink 2s infinite; }
.lp-server-name { font-size: 22px; font-weight: 800; color: #fff; margin-bottom: 6px; }
.lp-server-desc { font-size: 13px; color: rgba(255,255,255,.5); margin-bottom: 22px; }
.lp-server-specs { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 22px; }
.lp-spec { background: rgba(255,255,255,.06); border-radius: 12px; padding: 12px 14px; }
.lp-spec-val { font-size: 20px; font-weight: 800; color: #a5b4fc; line-height: 1; margin-bottom: 3px; }
.lp-spec-lbl { font-size: 11px; color: rgba(255,255,255,.45); font-weight: 500; }
.lp-server-price { display: flex; align-items: baseline; gap: 6px; margin-bottom: 16px; }
.lp-server-price-val { font-size: 32px; font-weight: 900; color: #fff; }
.lp-server-price-cycle { font-size: 14px; color: rgba(255,255,255,.45); }
.lp-server-cta { display: block; text-align: center; background: linear-gradient(135deg, #4F46E5, #7C3AED); color: #fff; text-decoration: none; padding: 13px; border-radius: 12px; font-size: 14px; font-weight: 700; transition: opacity .15s, transform .1s; }
.lp-server-cta:hover { opacity: .9; transform: translateY(-1px); }

/* ── Logos/clientes ── */
.lp-logos { background: #f8fafc; padding: 40px 24px; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0; }
.lp-logos-inner { max-width: 1100px; margin: 0 auto; text-align: center; }
.lp-logos-label { font-size: 12px; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: .1em; margin-bottom: 20px; }
.lp-logos-tags { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; }
.lp-logo-tag { background: #fff; border: 1.5px solid #e2e8f0; border-radius: 999px; padding: 8px 20px; font-size: 13px; font-weight: 600; color: #475569; transition: border-color .15s, color .15s; }
.lp-logo-tag:hover { border-color: #7C3AED; color: #7C3AED; }

/* ── Stats ── */
.lp-stats { background: #0f172a; padding: 36px 24px; }
.lp-stats-inner { max-width: 1000px; margin: 0 auto; display: grid; grid-template-columns: repeat(4, 1fr); }
.lp-stat { text-align: center; padding: 12px 16px; border-right: 1px solid rgba(255,255,255,.07); }
.lp-stat:last-child { border-right: none; }
.lp-stat-num { font-size: 32px; font-weight: 900; color: #a5b4fc; line-height: 1; margin-bottom: 5px; }
.lp-stat-lbl { font-size: 12px; color: rgba(255,255,255,.45); font-weight: 500; }
@media (max-width: 640px) { .lp-stats-inner { grid-template-columns: 1fr 1fr; } .lp-stat:nth-child(2) { border-right: none; } }

/* ── Section base ── */
.lp-section { padding: 88px 24px; }
.lp-section.alt { background: #f8fafc; }
.lp-section.dark { background: #060d1f; color: #fff; }
.lp-section-inner { max-width: 1160px; margin: 0 auto; }
.lp-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .12em; color: #7C3AED; margin-bottom: 10px; }
.lp-label.light { color: #a78bfa; }
.lp-title { font-size: clamp(22px, 3.5vw, 36px); font-weight: 900; color: #0f172a; margin-bottom: 12px; letter-spacing: -.025em; line-height: 1.15; }
.lp-title.light { color: #fff; }
.lp-sub { font-size: 15px; color: #64748b; line-height: 1.75; max-width: 520px; }
.lp-sub.light { color: rgba(255,255,255,.6); }

/* ── Por que nós — split ── */
.lp-why-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 64px; align-items: center; }
@media (max-width: 900px) { .lp-why-grid { grid-template-columns: 1fr; gap: 40px; } }
.lp-why-left { }
.lp-why-left .lp-title { margin-bottom: 16px; }
.lp-why-left .lp-sub { margin-bottom: 28px; }
.lp-why-features { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
@media (max-width: 560px) { .lp-why-features { grid-template-columns: 1fr; } }
.lp-why-feat { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 18px; transition: border-color .2s, box-shadow .2s; }
.lp-why-feat:hover { border-color: #c7d2fe; box-shadow: 0 4px 20px rgba(79,70,229,.08); }
.lp-why-feat-icon { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 10px; }
.lp-why-feat-name { font-size: 13px; font-weight: 700; color: #0f172a; margin-bottom: 4px; }
.lp-why-feat-desc { font-size: 12px; color: #64748b; line-height: 1.6; }

/* ── Diferenciais ── */
.lp-diff-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
@media (max-width: 900px) { .lp-diff-grid { grid-template-columns: 1fr 1fr; } }
@media (max-width: 560px) { .lp-diff-grid { grid-template-columns: 1fr; } }
.lp-diff-card { background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.08); border-radius: 18px; padding: 32px 24px; text-align: center; transition: background .2s, border-color .2s; }
.lp-diff-card:hover { background: rgba(255,255,255,.07); border-color: rgba(165,180,252,.25); }
.lp-diff-icon { width: 60px; height: 60px; border-radius: 18px; background: rgba(79,70,229,.2); display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; color: #a5b4fc; }
.lp-diff-name { font-size: 15px; font-weight: 700; color: #e2e8f0; margin-bottom: 8px; }
.lp-diff-desc { font-size: 13px; color: rgba(255,255,255,.45); line-height: 1.65; }

/* ── Planos carrossel ── */
.lp-plans-wrap { position: relative; }
.lp-plans-scroll { display: flex; gap: 20px; overflow-x: auto; scroll-snap-type: x mandatory; padding-bottom: 12px; scrollbar-width: none; }
.lp-plans-scroll::-webkit-scrollbar { display: none; }
.lp-plan-card { flex: 0 0 300px; scroll-snap-align: start; background: #fff; border: 1.5px solid #e2e8f0; border-radius: 20px; padding: 28px; display: flex; flex-direction: column; position: relative; transition: border-color .2s, box-shadow .2s; }
.lp-plan-card:hover { border-color: #7C3AED; box-shadow: 0 8px 32px rgba(124,58,237,.12); }
.lp-plan-card.destaque { border-color: #4F46E5; box-shadow: 0 8px 32px rgba(79,70,229,.18); }
.lp-plan-badge { position: absolute; top: -12px; left: 50%; transform: translateX(-50%); background: linear-gradient(135deg, #4F46E5, #7C3AED); color: #fff; font-size: 11px; font-weight: 700; padding: 4px 14px; border-radius: 999px; white-space: nowrap; }
.lp-plan-name { font-size: 18px; font-weight: 800; color: #0f172a; margin-bottom: 6px; }
.lp-plan-desc { font-size: 13px; color: #64748b; margin-bottom: 18px; line-height: 1.5; }
.lp-plan-price { font-size: 38px; font-weight: 900; color: #0f172a; line-height: 1; margin-bottom: 4px; }
.lp-plan-price span { font-size: 16px; font-weight: 500; color: #64748b; }
.lp-plan-cycle { font-size: 12px; color: #94a3b8; margin-bottom: 20px; }
.lp-plan-specs { list-style: none; padding: 0; margin: 0 0 22px; display: flex; flex-direction: column; gap: 9px; }
.lp-plan-specs li { display: flex; align-items: center; gap: 9px; font-size: 13px; color: #334155; }
.lp-plan-specs li svg { flex-shrink: 0; color: #7C3AED; }
.lp-plan-cta { margin-top: auto; }
.lp-plan-empty { text-align: center; padding: 48px 24px; color: #94a3b8; font-size: 14px; }
.lp-plans-nav { display: flex; gap: 8px; justify-content: center; margin-top: 20px; }
.lp-plans-nav button { width: 32px; height: 32px; border-radius: 50%; border: 1.5px solid #e2e8f0; background: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center; color: #475569; transition: border-color .15s, color .15s; }
.lp-plans-nav button:hover { border-color: #7C3AED; color: #7C3AED; }

/* ── CTA ajuda ── */
.lp-help-banner { background: linear-gradient(135deg, #4F46E5, #7C3AED); border-radius: 20px; padding: 40px 48px; display: flex; align-items: center; justify-content: space-between; gap: 24px; flex-wrap: wrap; }
.lp-help-title { font-size: 22px; font-weight: 800; color: #fff; margin-bottom: 6px; }
.lp-help-sub { font-size: 14px; color: rgba(255,255,255,.75); }
.lp-help-btn { background: #fff; color: #4F46E5; text-decoration: none; padding: 12px 28px; border-radius: 12px; font-size: 14px; font-weight: 700; white-space: nowrap; transition: opacity .15s; flex-shrink: 0; }
.lp-help-btn:hover { opacity: .9; }

/* ── Condições ── */
.lp-cond-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
@media (max-width: 640px) { .lp-cond-grid { grid-template-columns: 1fr; } }
.lp-cond-item { display: flex; gap: 14px; align-items: flex-start; padding: 18px; background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; }
.lp-cond-num { width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, #4F46E5, #7C3AED); color: #fff; font-size: 13px; font-weight: 800; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.lp-cond-text { font-size: 13px; color: #334155; line-height: 1.65; }
.lp-cond-text strong { color: #0f172a; font-weight: 700; display: block; margin-bottom: 2px; }

/* ── Depoimentos ── */
.lp-test-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
@media (max-width: 640px) { .lp-test-grid { grid-template-columns: 1fr; } }
.lp-test-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 18px; padding: 26px; }
.lp-test-stars { display: flex; gap: 3px; margin-bottom: 14px; color: #f59e0b; }
.lp-test-text { font-size: 14px; color: #334155; line-height: 1.7; margin-bottom: 18px; font-style: italic; }
.lp-test-author { display: flex; align-items: center; gap: 12px; }
.lp-test-avatar { width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #4F46E5, #7C3AED); color: #fff; font-size: 15px; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.lp-test-name { font-size: 14px; font-weight: 700; color: #0f172a; }
.lp-test-role { font-size: 12px; color: #94a3b8; }

/* ── CTA final ── */
.lp-cta-final { background: #060d1f; padding: 96px 24px; text-align: center; position: relative; overflow: hidden; }
.lp-cta-final::before { content: ''; position: absolute; inset: 0; background-image: linear-gradient(rgba(255,255,255,.025) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.025) 1px, transparent 1px); background-size: 56px 56px; pointer-events: none; }
.lp-cta-glow { position: absolute; top: 50%; left: 50%; transform: translate(-50%,-50%); width: 700px; height: 400px; background: radial-gradient(ellipse, rgba(79,70,229,.3) 0%, transparent 70%); pointer-events: none; }
.lp-cta-inner { max-width: 640px; margin: 0 auto; position: relative; }
.lp-cta-title { font-size: clamp(26px, 4.5vw, 46px); font-weight: 900; color: #fff; margin-bottom: 14px; letter-spacing: -.03em; font-style: italic; line-height: 1.1; }
.lp-cta-sub { font-size: 16px; color: rgba(255,255,255,.65); margin-bottom: 36px; line-height: 1.7; }
.lp-cta-btns { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
.lp-cta-btn { display: inline-flex; align-items: center; gap: 8px; padding: 15px 32px; border-radius: 14px; font-size: 15px; font-weight: 700; text-decoration: none; transition: transform .15s, box-shadow .15s; }
.lp-cta-btn:hover { transform: translateY(-2px); }
.lp-cta-btn.primary { background: linear-gradient(135deg, #4F46E5, #7C3AED); color: #fff; box-shadow: 0 4px 20px rgba(79,70,229,.5); }
.lp-cta-btn.primary:hover { box-shadow: 0 8px 32px rgba(79,70,229,.65); }
.lp-cta-btn.outline { background: rgba(255,255,255,.08); color: #fff; border: 1.5px solid rgba(255,255,255,.2); }
.lp-cta-btn.outline:hover { background: rgba(255,255,255,.14); }

/* ── Footer ── */
.lp-footer { background: #030712; color: rgba(255,255,255,.45); padding: 56px 24px 32px; }
.lp-footer-inner { max-width: 1160px; margin: 0 auto; }
.lp-footer-grid { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 40px; margin-bottom: 48px; }
@media (max-width: 768px) { .lp-footer-grid { grid-template-columns: 1fr 1fr; gap: 28px; } }
@media (max-width: 480px) { .lp-footer-grid { grid-template-columns: 1fr; } }
.lp-footer-brand-name { font-size: 16px; font-weight: 800; color: #fff; margin-bottom: 10px; }
.lp-footer-brand-desc { font-size: 13px; line-height: 1.7; max-width: 260px; }
.lp-footer-col-title { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: rgba(255,255,255,.3); margin-bottom: 14px; }
.lp-footer-links { list-style: none; display: flex; flex-direction: column; gap: 9px; }
.lp-footer-links a { color: rgba(255,255,255,.45); text-decoration: none; font-size: 13px; transition: color .15s; }
.lp-footer-links a:hover { color: #fff; }
.lp-footer-bottom { border-top: 1px solid rgba(255,255,255,.06); padding-top: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; font-size: 12px; }
.lp-footer-status { display: inline-flex; align-items: center; gap: 6px; }
.lp-footer-status::before { content: ''; width: 7px; height: 7px; border-radius: 50%; background: #22c55e; display: inline-block; }
</style>
</head>
<body>

<!-- Navbar -->
<nav class="lp-nav">
  <div class="lp-nav-inner">
    <a href="/" class="lp-nav-brand">
      <?php if ($_logo !== ''): ?>
        <img src="<?php echo View::e($_logo); ?>" alt="logo" />
      <?php else: ?>
        <svg width="30" height="30" viewBox="0 0 30 30" fill="none"><rect width="30" height="30" rx="9" fill="#4F46E5"/><path d="M8 15h14M15 8v14" stroke="#fff" stroke-width="2.5" stroke-linecap="round"/></svg>
      <?php endif; ?>
      <span class="lp-nav-brand-name"><?php echo View::e($_nome); ?></span>
    </a>
    <div class="lp-nav-links">
      <a href="#sobre">Sobre</a>
      <a href="#planos">Planos</a>
      <a href="#condicoes">Condições</a>
      <a href="/infraestrutura">Para devs</a>
    </div>
    <div class="lp-nav-actions">
      <?php require __DIR__ . '/_partials/idioma.php'; ?>
      <a href="/cliente/entrar" class="lp-btn ghost">Entrar</a>
      <a href="/cliente/criar-conta" class="lp-btn solid">Contratar agora</a>
    </div>
  </div>
</nav>

<!-- Hero -->
<section class="lp-hero">
  <div class="lp-hero-glow"></div>
  <div class="lp-hero-inner">
    <div class="lp-hero-left">
      <div class="lp-hero-badge">
        <span class="lp-hero-badge-dot"></span>
        Servidores disponíveis agora
      </div>
      <h1 class="lp-hero-title">Servidores dedicados<br><em>para quem não pode parar.</em></h1>
      <p class="lp-hero-sub">Infraestrutura cloud de alta performance com proteção DDoS, uptime garantido e suporte especializado. Ative seu servidor em minutos.</p>
      <div class="lp-hero-ctas">
        <a href="/cliente/criar-conta" class="lp-btn solid" style="font-size:15px;padding:13px 28px;">
          <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M8 2v12M2 8h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
          Contratar agora
        </a>
        <a href="/contato" class="lp-btn ghost" style="font-size:15px;padding:13px 28px;">Falar com especialista</a>
      </div>
      <div class="lp-hero-trust">
        <div class="lp-hero-trust-item"><svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M2 7l3 3 7-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>Proteção DDoS inclusa</div>
        <div class="lp-hero-trust-item"><svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M2 7l3 3 7-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>99,9% de uptime</div>
        <div class="lp-hero-trust-item"><svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M2 7l3 3 7-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>Suporte 24/7</div>
      </div>
    </div>
    <div class="lp-hero-right">
      <?php if (!empty($_planos)):
        $_fp = $_planos[0];
        $_fspecs = json_decode((string)($_fp['specs_json'] ?? ''), true) ?: [];
        $_fvcpu  = (int)($_fspecs['vcpu'] ?? $_fspecs['cpu'] ?? 0);
        $_fram   = (int)($_fspecs['ram_gb'] ?? 0);
        $_fdisco = (int)($_fspecs['disco_gb'] ?? $_fspecs['storage_gb'] ?? 0);
        $_fprice = (float)$_fp['price'];
      ?>
      <div class="lp-server-card">
        <div class="lp-server-card-header">
          <div class="lp-server-card-title">Servidor em destaque</div>
          <div class="lp-server-status">Online</div>
        </div>
        <div class="lp-server-name"><?php echo View::e((string)$_fp['name']); ?></div>
        <div class="lp-server-desc"><?php echo View::e((string)($_fp['description'] ?? 'Servidor cloud de alta performance')); ?></div>
        <div class="lp-server-specs">
          <?php if ($_fvcpu > 0): ?><div class="lp-spec"><div class="lp-spec-val"><?php echo $_fvcpu; ?></div><div class="lp-spec-lbl">vCPU</div></div><?php endif; ?>
          <?php if ($_fram > 0): ?><div class="lp-spec"><div class="lp-spec-val"><?php echo $_fram; ?>GB</div><div class="lp-spec-lbl">RAM</div></div><?php endif; ?>
          <?php if ($_fdisco > 0): ?><div class="lp-spec"><div class="lp-spec-val"><?php echo $_fdisco; ?>GB</div><div class="lp-spec-lbl">SSD NVMe</div></div><?php endif; ?>
          <div class="lp-spec"><div class="lp-spec-val">1Gbps</div><div class="lp-spec-lbl">Rede</div></div>
        </div>
        <div class="lp-server-price">
          <div class="lp-server-price-val">R$ <?php echo number_format($_fprice, 2, ',', '.'); ?></div>
          <div class="lp-server-price-cycle">/mês</div>
        </div>
        <a href="/cliente/criar-conta" class="lp-server-cta">Contratar este plano →</a>
      </div>
      <?php else: ?>
      <div class="lp-server-card">
        <div class="lp-server-card-header">
          <div class="lp-server-card-title">Infraestrutura cloud</div>
          <div class="lp-server-status">Online</div>
        </div>
        <div class="lp-server-name"><?php echo View::e($_nome); ?></div>
        <div class="lp-server-desc">Servidores de alta performance com ativação imediata.</div>
        <div class="lp-server-specs">
          <div class="lp-spec"><div class="lp-spec-val">NVMe</div><div class="lp-spec-lbl">Armazenamento</div></div>
          <div class="lp-spec"><div class="lp-spec-val">1Gbps</div><div class="lp-spec-lbl">Rede</div></div>
          <div class="lp-spec"><div class="lp-spec-val">DDoS</div><div class="lp-spec-lbl">Proteção</div></div>
          <div class="lp-spec"><div class="lp-spec-val">24/7</div><div class="lp-spec-lbl">Suporte</div></div>
        </div>
        <a href="/cliente/criar-conta" class="lp-server-cta">Ver planos disponíveis →</a>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- Logos/clientes -->
<div class="lp-logos">
  <div class="lp-logos-inner">
    <div class="lp-logos-label">Empresas que confiam na nossa infraestrutura</div>
    <div class="lp-logos-tags">
      <div class="lp-logo-tag">Startups</div>
      <div class="lp-logo-tag">E-commerce</div>
      <div class="lp-logo-tag">SaaS</div>
      <div class="lp-logo-tag">Agências</div>
      <div class="lp-logo-tag">Desenvolvedores</div>
      <div class="lp-logo-tag">Fintechs</div>
    </div>
  </div>
</div>

<!-- Stats -->
<div class="lp-stats">
  <div class="lp-stats-inner">
    <div class="lp-stat"><div class="lp-stat-num">+5 anos</div><div class="lp-stat-lbl">de experiência</div></div>
    <div class="lp-stat"><div class="lp-stat-num">+200</div><div class="lp-stat-lbl">clientes ativos</div></div>
    <div class="lp-stat"><div class="lp-stat-num">99,9%</div><div class="lp-stat-lbl">SLA garantido</div></div>
    <div class="lp-stat"><div class="lp-stat-num">6 dias</div><div class="lp-stat-lbl">ativação média</div></div>
  </div>
</div>

<!-- Por que nós -->
<section class="lp-section" id="sobre">
  <div class="lp-section-inner">
    <div class="lp-why-grid">
      <div class="lp-why-left">
        <div class="lp-label">Por que escolher a <?php echo View::e($_nome); ?></div>
        <h2 class="lp-title">Infraestrutura que acompanha o crescimento do seu negócio</h2>
        <p class="lp-sub">Combinamos hardware de ponta, rede de alta capacidade e suporte especializado para entregar a melhor experiência em cloud hosting.</p>
        <a href="/cliente/criar-conta" class="lp-btn solid" style="margin-top:24px;display:inline-flex;">Começar agora →</a>
      </div>
      <div class="lp-why-features">
        <div class="lp-why-feat">
          <div class="lp-why-feat-icon" style="background:#eef2ff;color:#4F46E5;"><svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M10 2l2 5h5l-4 3 1.5 5L10 12l-4.5 3L7 10 3 7h5L10 2z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg></div>
          <div class="lp-why-feat-name">Alto Desempenho</div>
          <div class="lp-why-feat-desc">Processadores de última geração e SSDs NVMe para máxima velocidade.</div>
        </div>
        <div class="lp-why-feat">
          <div class="lp-why-feat-icon" style="background:#fff1f2;color:#e11d48;"><svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M10 2l6 3v5c0 3.5-2.5 6.5-6 7.5C4.5 16.5 2 13.5 2 10V5l8-3z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg></div>
          <div class="lp-why-feat-name">Proteção DDoS</div>
          <div class="lp-why-feat-desc">Mitigação automática de ataques DDoS inclusa em todos os planos.</div>
        </div>
        <div class="lp-why-feat">
          <div class="lp-why-feat-icon" style="background:#f0fdf4;color:#16a34a;"><svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M2 10h16M10 2v16" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><circle cx="10" cy="10" r="8" stroke="currentColor" stroke-width="1.6"/></svg></div>
          <div class="lp-why-feat-name">Hiperconectividade</div>
          <div class="lp-why-feat-desc">Rede 1Gbps com múltiplos uplinks e baixa latência.</div>
        </div>
        <div class="lp-why-feat">
          <div class="lp-why-feat-icon" style="background:#fff7ed;color:#ea580c;"><svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M10 2v4M10 14v4M2 10h4M14 10h4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><circle cx="10" cy="10" r="3" stroke="currentColor" stroke-width="1.6"/></svg></div>
          <div class="lp-why-feat-name">Upgrades Fáceis</div>
          <div class="lp-why-feat-desc">Escale recursos sem downtime. Upgrade com um clique no painel.</div>
        </div>
        <div class="lp-why-feat">
          <div class="lp-why-feat-icon" style="background:#f5f3ff;color:#7C3AED;"><svg width="20" height="20" viewBox="0 0 20 20" fill="none"><rect x="3" y="6" width="14" height="5" rx="2" stroke="currentColor" stroke-width="1.6"/><rect x="3" y="13" width="14" height="4" rx="2" stroke="currentColor" stroke-width="1.6"/><circle cx="14" cy="8.5" r="1" fill="currentColor"/><circle cx="14" cy="15" r="1" fill="currentColor"/></svg></div>
          <div class="lp-why-feat-name">Hardware Dedicado</div>
          <div class="lp-why-feat-desc">Recursos garantidos sem compartilhamento excessivo de CPU e RAM.</div>
        </div>
        <div class="lp-why-feat">
          <div class="lp-why-feat-icon" style="background:#fefce8;color:#ca8a04;"><svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M10 2l1.8 5.5H18l-4.9 3.5 1.8 5.5L10 13l-4.9 3.5 1.8-5.5L2 7.5h6.2L10 2z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg></div>
          <div class="lp-why-feat-name">Custos Previsíveis</div>
          <div class="lp-why-feat-desc">Preços fixos mensais sem surpresas. Sem cobrança por tráfego.</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Diferenciais -->
<section class="lp-section dark">
  <div class="lp-section-inner">
    <div style="text-align:center;margin-bottom:48px;">
      <div class="lp-label light">Diferenciais</div>
      <h2 class="lp-title light" style="text-align:center;">O que nos torna diferentes</h2>
    </div>
    <div class="lp-diff-grid">
      <div class="lp-diff-card">
        <div class="lp-diff-icon"><svg width="28" height="28" viewBox="0 0 28 28" fill="none"><path d="M4 14h20M14 4v20" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="14" cy="14" r="10" stroke="currentColor" stroke-width="2"/></svg></div>
        <div class="lp-diff-name">Conectividade Global</div>
        <div class="lp-diff-desc">Rede de alta capacidade com múltiplos pontos de presença e baixa latência para seus usuários.</div>
      </div>
      <div class="lp-diff-card">
        <div class="lp-diff-icon"><svg width="28" height="28" viewBox="0 0 28 28" fill="none"><rect x="4" y="6" width="20" height="16" rx="3" stroke="currentColor" stroke-width="2"/><path d="M8 10l4 3-4 3M16 16h4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
        <div class="lp-diff-name">Console / KVM</div>
        <div class="lp-diff-desc">Acesso total ao servidor via terminal web integrado com auditoria completa de comandos.</div>
      </div>
      <div class="lp-diff-card">
        <div class="lp-diff-icon"><svg width="28" height="28" viewBox="0 0 28 28" fill="none"><path d="M14 3l7 3.5v6c0 4-2.8 7.5-7 8.5C9.8 20 7 16.5 7 12.5v-6L14 3z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg></div>
        <div class="lp-diff-name">Resiliência</div>
        <div class="lp-diff-desc">Infraestrutura redundante com backups automáticos e monitoramento 24/7 para máxima disponibilidade.</div>
      </div>
      <div class="lp-diff-card">
        <div class="lp-diff-icon"><svg width="28" height="28" viewBox="0 0 28 28" fill="none"><path d="M6 22V10l8-7 8 7v12" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><rect x="10" y="16" width="8" height="6" rx="1" stroke="currentColor" stroke-width="2"/></svg></div>
        <div class="lp-diff-name">Desempenho I/O</div>
        <div class="lp-diff-desc">SSDs NVMe de última geração com IOPS elevado para aplicações que exigem alta velocidade de disco.</div>
      </div>
      <div class="lp-diff-card">
        <div class="lp-diff-icon"><svg width="28" height="28" viewBox="0 0 28 28" fill="none"><rect x="4" y="12" width="20" height="12" rx="2" stroke="currentColor" stroke-width="2"/><path d="M9 12V8a5 5 0 0110 0v4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></div>
        <div class="lp-diff-name">Segurança Avançada</div>
        <div class="lp-diff-desc">Proteção DDoS, firewall gerenciado, 2FA e criptografia em repouso para seus dados.</div>
      </div>
      <div class="lp-diff-card">
        <div class="lp-diff-icon"><svg width="28" height="28" viewBox="0 0 28 28" fill="none"><path d="M14 4v8M14 16v8M4 14h8M16 14h8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="14" cy="14" r="4" stroke="currentColor" stroke-width="2"/></svg></div>
        <div class="lp-diff-name">Backup Automático</div>
        <div class="lp-diff-desc">Snapshots diários com retenção configurável. Restauração com um clique quando você precisar.</div>
      </div>
    </div>
  </div>
</section>

<!-- Planos -->
<section class="lp-section alt" id="planos">
  <div class="lp-section-inner">
    <div style="text-align:center;margin-bottom:40px;">
      <div class="lp-label">Planos</div>
      <h2 class="lp-title" style="text-align:center;">Escolha o plano ideal para você</h2>
      <p class="lp-sub" style="margin:10px auto 0;text-align:center;">Todos os planos incluem proteção DDoS, backups automáticos e suporte especializado.</p>
    </div>
    <?php if (!empty($_planos)): ?>
    <div class="lp-plans-wrap">
      <div class="lp-plans-scroll" id="plansScroll">
        <?php foreach ($_planos as $_i => $_p):
          $_specs = json_decode((string)($_p['specs_json'] ?? ''), true) ?: [];
          $_vcpu  = (int)($_specs['vcpu'] ?? $_specs['cpu'] ?? 0);
          $_ram   = (int)($_specs['ram_gb'] ?? 0);
          $_ramMb = (int)($_specs['ram_mb'] ?? 0);
          $_disco = (int)($_specs['disco_gb'] ?? $_specs['storage_gb'] ?? 0);
          $_bw    = (int)($_specs['bandwidth_gb'] ?? 0);
          $_price = (float)$_p['price'];
          $_cycle = (string)($_p['billing_cycle'] ?? 'monthly');
          $_destaque = $_i === 1;
        ?>
        <div class="lp-plan-card <?php echo $_destaque ? 'destaque' : ''; ?>">
          <?php if ($_destaque): ?><div class="lp-plan-badge">Mais popular</div><?php endif; ?>
          <div class="lp-plan-name"><?php echo View::e((string)$_p['name']); ?></div>
          <?php if (!empty($_p['description'])): ?><div class="lp-plan-desc"><?php echo View::e((string)$_p['description']); ?></div><?php endif; ?>
          <div class="lp-plan-price">R$ <?php echo number_format($_price, 2, ',', '.'); ?><span><?php echo $_cycle === 'yearly' ? '/ano' : '/mês'; ?></span></div>
          <div class="lp-plan-cycle"><?php echo $_cycle === 'yearly' ? 'Cobrado anualmente' : 'Cobrado mensalmente'; ?></div>
          <ul class="lp-plan-specs">
            <?php if ($_vcpu > 0): ?><li><svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M2 7l3 3 7-7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg><?php echo $_vcpu; ?> vCPU</li><?php endif; ?>
            <?php if ($_ram > 0): ?><li><svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M2 7l3 3 7-7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg><?php echo $_ram; ?> GB RAM</li><?php elseif ($_ramMb > 0): ?><li><svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M2 7l3 3 7-7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg><?php echo $_ramMb; ?> MB RAM</li><?php endif; ?>
            <?php if ($_disco > 0): ?><li><svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M2 7l3 3 7-7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg><?php echo $_disco; ?> GB SSD NVMe</li><?php endif; ?>
            <?php if ($_bw > 0): ?><li><svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M2 7l3 3 7-7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg><?php echo $_bw; ?> GB bandwidth</li><?php endif; ?>
            <li><svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M2 7l3 3 7-7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>Proteção DDoS</li>
            <li><svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M2 7l3 3 7-7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>Backups automáticos</li>
            <li><svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M2 7l3 3 7-7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>Suporte especializado</li>
          </ul>
          <div class="lp-plan-cta"><a href="/cliente/criar-conta" class="botao" style="width:100%;justify-content:center;">Contratar agora</a></div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php if (count($_planos) > 2): ?>
      <div class="lp-plans-nav">
        <button onclick="document.getElementById('plansScroll').scrollBy({left:-320,behavior:'smooth'})" aria-label="Anterior">
          <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M9 2L4 7l5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
        <button onclick="document.getElementById('plansScroll').scrollBy({left:320,behavior:'smooth'})" aria-label="Próximo">
          <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M5 2l5 5-5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
      </div>
      <?php endif; ?>
    </div>
    <?php else: ?>
    <div class="lp-plan-empty">Planos em breve. <a href="/contato">Entre em contato</a> para saber mais.</div>
    <?php endif; ?>
    <p style="text-align:center;margin-top:28px;font-size:13px;color:#94a3b8;">Precisa de algo personalizado? <a href="/contato" style="color:#7C3AED;">Fale com nossa equipe</a></p>
  </div>
</section>

<!-- CTA ajuda -->
<section class="lp-section" style="padding:48px 24px;">
  <div class="lp-section-inner">
    <div class="lp-help-banner">
      <div>
        <div class="lp-help-title">Precisando de ajuda para escolher?</div>
        <div class="lp-help-sub">Nossa equipe está pronta para indicar o melhor plano para o seu projeto.</div>
      </div>
      <a href="/contato" class="lp-help-btn">Falar com especialista →</a>
    </div>
  </div>
</section>

<!-- Condições -->
<section class="lp-section alt" id="condicoes">
  <div class="lp-section-inner">
    <div style="text-align:center;margin-bottom:40px;">
      <div class="lp-label">Condições</div>
      <h2 class="lp-title" style="text-align:center;">Transparência em tudo</h2>
      <p class="lp-sub" style="margin:10px auto 0;text-align:center;">Saiba exatamente o que está contratando. Sem letras miúdas.</p>
    </div>
    <div class="lp-cond-grid">
      <div class="lp-cond-item"><div class="lp-cond-num">1</div><div class="lp-cond-text"><strong>Ativação imediata</strong>Após confirmação do pagamento, seu servidor é provisionado automaticamente.</div></div>
      <div class="lp-cond-item"><div class="lp-cond-num">2</div><div class="lp-cond-text"><strong>Cobrança mensal</strong>Planos mensais com renovação automática. Cancele quando quiser.</div></div>
      <div class="lp-cond-item"><div class="lp-cond-num">3</div><div class="lp-cond-text"><strong>SLA 99,9%</strong>Garantia de disponibilidade com compensação em caso de indisponibilidade.</div></div>
      <div class="lp-cond-item"><div class="lp-cond-num">4</div><div class="lp-cond-text"><strong>Backups inclusos</strong>Backups automáticos diários com retenção de 7 dias em todos os planos.</div></div>
      <div class="lp-cond-item"><div class="lp-cond-num">5</div><div class="lp-cond-text"><strong>Proteção DDoS</strong>Mitigação automática de ataques sem custo adicional em todos os planos.</div></div>
      <div class="lp-cond-item"><div class="lp-cond-num">6</div><div class="lp-cond-text"><strong>Suporte incluso</strong>Suporte via chat e tickets incluído. Sem cobranças extras por atendimento.</div></div>
      <div class="lp-cond-item"><div class="lp-cond-num">7</div><div class="lp-cond-text"><strong>Upgrade sem downtime</strong>Aumente recursos do seu servidor sem interrupção do serviço.</div></div>
      <div class="lp-cond-item"><div class="lp-cond-num">8</div><div class="lp-cond-text"><strong>Acesso root completo</strong>Controle total do servidor via terminal web ou SSH direto.</div></div>
      <div class="lp-cond-item"><div class="lp-cond-num">9</div><div class="lp-cond-text"><strong>Sem fidelidade</strong>Não há contrato de fidelidade. Cancele a qualquer momento sem multa.</div></div>
      <div class="lp-cond-item"><div class="lp-cond-num">10</div><div class="lp-cond-text"><strong>Dados no Brasil</strong>Infraestrutura localizada no Brasil, em conformidade com a LGPD.</div></div>
    </div>
  </div>
</section>

<!-- Depoimentos -->
<section class="lp-section">
  <div class="lp-section-inner">
    <div style="text-align:center;margin-bottom:40px;">
      <div class="lp-label">Depoimentos</div>
      <h2 class="lp-title" style="text-align:center;">O que nossos clientes dizem</h2>
    </div>
    <div class="lp-test-grid">
      <div class="lp-test-card">
        <div class="lp-test-stars">★★★★★</div>
        <div class="lp-test-text">"Migramos nossa aplicação para a <?php echo View::e($_nome); ?> e a diferença de performance foi imediata. Uptime impecável e suporte sempre ágil."</div>
        <div class="lp-test-author"><div class="lp-test-avatar">RS</div><div><div class="lp-test-name">Rafael S.</div><div class="lp-test-role">CTO · Startup SaaS</div></div></div>
      </div>
      <div class="lp-test-card">
        <div class="lp-test-stars">★★★★★</div>
        <div class="lp-test-text">"O painel é muito intuitivo. Consigo gerenciar todos os servidores dos meus clientes em um único lugar. Recomendo para qualquer agência."</div>
        <div class="lp-test-author"><div class="lp-test-avatar">MC</div><div><div class="lp-test-name">Marina C.</div><div class="lp-test-role">Fundadora · Agência Digital</div></div></div>
      </div>
      <div class="lp-test-card">
        <div class="lp-test-stars">★★★★★</div>
        <div class="lp-test-text">"Proteção DDoS funcionando perfeitamente. Já sofremos ataques e o sistema mitigou tudo automaticamente sem afetar nossos clientes."</div>
        <div class="lp-test-author"><div class="lp-test-avatar">PL</div><div><div class="lp-test-name">Pedro L.</div><div class="lp-test-role">Dev Lead · E-commerce</div></div></div>
      </div>
      <div class="lp-test-card">
        <div class="lp-test-stars">★★★★★</div>
        <div class="lp-test-text">"Preço justo, infraestrutura sólida e suporte que realmente resolve. Já indiquei para vários colegas desenvolvedores."</div>
        <div class="lp-test-author"><div class="lp-test-avatar">AT</div><div><div class="lp-test-name">Ana T.</div><div class="lp-test-role">Desenvolvedora Freelancer</div></div></div>
      </div>
    </div>
  </div>
</section>

<!-- CTA final -->
<section class="lp-cta-final">
  <div class="lp-cta-glow"></div>
  <div class="lp-cta-inner">
    <h2 class="lp-cta-title"><em>Sua infraestrutura pronta para o próximo nível.</em></h2>
    <p class="lp-cta-sub">Ative seu servidor hoje e experimente a diferença de uma infraestrutura cloud de verdade.</p>
    <div class="lp-cta-btns">
      <a href="/cliente/criar-conta" class="lp-cta-btn primary">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M8 2v12M2 8h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
        Contratar agora
      </a>
      <a href="/contato" class="lp-cta-btn outline">Falar com especialista</a>
    </div>
  </div>
</section>

<!-- Footer -->
<footer class="lp-footer">
  <div class="lp-footer-inner">
    <div class="lp-footer-grid">
      <div>
        <div class="lp-footer-brand-name"><?php echo View::e($_nome); ?></div>
        <div class="lp-footer-brand-desc">Infraestrutura cloud de alta performance com proteção DDoS, backups automáticos e suporte especializado.</div>
      </div>
      <div>
        <div class="lp-footer-col-title">Produto</div>
        <ul class="lp-footer-links">
          <li><a href="#sobre">Sobre</a></li>
          <li><a href="#planos">Planos</a></li>
          <li><a href="#condicoes">Condições</a></li>
          <li><a href="/infraestrutura">Para devs</a></li>
          <li><a href="/changelog">Changelog</a></li>
        </ul>
      </div>
      <div>
        <div class="lp-footer-col-title">Suporte</div>
        <ul class="lp-footer-links">
          <li><a href="/contato">Contato</a></li>
          <li><a href="/status">Status</a></li>
          <li><a href="/cliente/tickets">Tickets</a></li>
          <li><a href="/cliente/ajuda">Central de ajuda</a></li>
        </ul>
      </div>
      <div>
        <div class="lp-footer-col-title">Legal</div>
        <ul class="lp-footer-links">
          <li><a href="/termos">Termos de uso</a></li>
          <li><a href="/privacidade">Privacidade</a></li>
        </ul>
      </div>
    </div>
    <div class="lp-footer-bottom">
      <span><?php echo View::e(SistemaConfig::copyrightText()); ?> · <?php echo View::e($_nome); ?> v<?php echo View::e(SistemaConfig::versao()); ?></span>
      <span class="lp-footer-status">Todos os sistemas operacionais</span>
    </div>
  </div>
</footer>

</body>
</html>
