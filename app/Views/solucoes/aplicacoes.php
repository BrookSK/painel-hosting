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
<title><?php echo I18n::t('sol_apps.page_title'); ?> — <?php echo View::e($_nome); ?></title>
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
      <div class="da-hero-badge"><span><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"/><path d="M12 15l-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"/><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"/><path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"/></svg> <?php echo I18n::t('sol_apps.hero_badge'); ?></span></div>
      <h1><?php echo I18n::t('sol_apps.hero_title_pre'); ?> <em><?php echo I18n::t('sol_apps.hero_title_em'); ?></em></h1>
      <p><?php echo I18n::t('sol_apps.hero_desc'); ?></p>
      <div class="da-hero-actions">
        <a href="/cliente/criar-conta" class="da-btn-p"><?php echo I18n::t('sol_apps.hero_btn_plans'); ?></a>
        <a href="/contato" class="da-btn-s"><?php echo I18n::t('sol_apps.hero_btn_contact'); ?></a>
      </div>
    </div>
    <div class="da-hero-visual">
      <div class="da-mock-bar"><div class="da-mock-dot"></div><div class="da-mock-dot"></div><div class="da-mock-dot"></div></div>
      <div class="da-pipeline">
        <div class="da-pipe-step"><div class="icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg></div><div class="txt">Commit</div></div>
        <div class="da-pipe-arrow">→</div>
        <div class="da-pipe-step"><div class="icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg></div><div class="txt">Build</div></div>
        <div class="da-pipe-arrow">→</div>
        <div class="da-pipe-step"><div class="icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 2l6 0"/><path d="M12 11l0-9"/><path d="M6 18a6 6 0 0 0 12 0L12 8 6 18z"/></svg></div><div class="txt">Test</div></div>
        <div class="da-pipe-arrow">→</div>
        <div class="da-pipe-step active"><div class="icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"/><path d="M12 15l-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"/><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"/><path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"/></svg></div><div class="txt">Deploy</div></div>
        <div class="da-pipe-arrow">→</div>
        <div class="da-pipe-step"><div class="icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div><div class="txt">Live</div></div>
      </div>
      <div class="da-pipe-log">
        <div><span class="violet">$</span> git push origin main</div>
        <div><span class="green">✓</span> <?php echo I18n::t('sol_apps.pipe_build'); ?></div>
        <div><span class="green">✓</span> <?php echo I18n::t('sol_apps.pipe_tests'); ?></div>
        <div><span class="green">✓</span> <?php echo I18n::t('sol_apps.pipe_deploy'); ?> — <span class="violet">app.exemplo.com</span></div>
      </div>
    </div>
  </div>
</section>

<!-- STATS -->
<div class="da-stats">
  <div class="da-stats-inner">
    <div class="da-stat"><h3>Git</h3><p><?php echo I18n::t('sol_apps.stats_git_label'); ?></p></div>
    <div class="da-stat"><h3>50+</h3><p><?php echo I18n::t('sol_apps.stats_apps_label'); ?></p></div>
    <div class="da-stat"><h3>Zero</h3><p><?php echo I18n::t('sol_apps.stats_downtime_label'); ?></p></div>
    <div class="da-stat"><h3>1 clique</h3><p><?php echo I18n::t('sol_apps.stats_rollback_label'); ?></p></div>
  </div>
</div>

<!-- COMPARAÇÃO -->
<section class="da-section">
  <div class="da-inner">
    <div style="text-align:center;margin-bottom:8px;">
      <div class="da-label"><?php echo I18n::t('sol_apps.why_badge'); ?></div>
      <h2 class="da-title"><?php echo I18n::t('sol_apps.why_title'); ?> <?php echo View::e($_nome); ?></h2>
      <p class="da-sub" style="margin:0 auto;"><?php echo I18n::t('sol_apps.why_desc'); ?></p>
    </div>
    <div class="da-compare">
      <div class="da-compare-card bad">
        <h3><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_apps.compare_bad_title'); ?></h3>
        <ul>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_apps.compare_bad1'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_apps.compare_bad2'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_apps.compare_bad3'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_apps.compare_bad4'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_apps.compare_bad5'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_apps.compare_bad6'); ?></li>
        </ul>
      </div>
      <div class="da-compare-card good">
        <h3><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_apps.compare_good_title'); ?> <?php echo View::e($_nome); ?></h3>
        <ul>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_apps.compare_good1'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_apps.compare_good2'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_apps.compare_good3'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_apps.compare_good4'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_apps.compare_good5'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_apps.compare_good6'); ?></li>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section class="da-section alt">
  <div class="da-inner">
    <div style="text-align:center;">
      <div class="da-label"><?php echo I18n::t('sol_apps.feat_badge'); ?></div>
      <h2 class="da-title"><?php echo I18n::t('sol_apps.feat_title'); ?></h2>
    </div>
    <div class="da-features">
      <div class="da-feat"><div class="da-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg></div><h3><?php echo I18n::t('sol_apps.feat_1_title'); ?></h3><p><?php echo I18n::t('sol_apps.feat_1_desc'); ?></p></div>
      <div class="da-feat"><div class="da-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="16.5" y1="9.4" x2="7.5" y2="4.21"/><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg></div><h3><?php echo I18n::t('sol_apps.feat_2_title'); ?></h3><p><?php echo I18n::t('sol_apps.feat_2_desc'); ?></p></div>
      <div class="da-feat"><div class="da-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><rect x="5" y="10" width="4" height="4"/><rect x="11" y="10" width="4" height="4"/><line x1="2" y1="4" x2="7" y2="4"/></svg></div><h3><?php echo I18n::t('sol_apps.feat_3_title'); ?></h3><p><?php echo I18n::t('sol_apps.feat_3_desc'); ?></p></div>
      <div class="da-feat"><div class="da-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg></div><h3><?php echo I18n::t('sol_apps.feat_4_title'); ?></h3><p><?php echo I18n::t('sol_apps.feat_4_desc'); ?></p></div>
      <div class="da-feat"><div class="da-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="11 19 2 12 11 5 11 19"/><polygon points="22 19 13 12 22 5 22 19"/></svg></div><h3><?php echo I18n::t('sol_apps.feat_5_title'); ?></h3><p><?php echo I18n::t('sol_apps.feat_5_desc'); ?></p></div>
      <div class="da-feat"><div class="da-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></div><h3><?php echo I18n::t('sol_apps.feat_6_title'); ?></h3><p><?php echo I18n::t('sol_apps.feat_6_desc'); ?></p></div>
      <div class="da-feat"><div class="da-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1"/></svg></div><h3><?php echo I18n::t('sol_apps.feat_7_title'); ?></h3><p><?php echo I18n::t('sol_apps.feat_7_desc'); ?></p></div>
      <div class="da-feat"><div class="da-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg></div><h3><?php echo I18n::t('sol_apps.feat_8_title'); ?></h3><p><?php echo I18n::t('sol_apps.feat_8_desc'); ?></p></div>
      <div class="da-feat"><div class="da-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg></div><h3><?php echo I18n::t('sol_apps.feat_9_title'); ?></h3><p><?php echo I18n::t('sol_apps.feat_9_desc'); ?></p></div>
    </div>
  </div>
</section>

<!-- COMO FUNCIONA -->
<section class="da-section dark">
  <div class="da-inner">
    <div style="text-align:center;margin-bottom:8px;">
      <div class="da-label" style="color:#ddd6fe;"><?php echo I18n::t('sol_apps.steps_label'); ?></div>
      <h2 class="da-title" style="color:#fff;"><?php echo I18n::t('sol_apps.steps_title'); ?></h2>
    </div>
    <div class="da-steps">
      <div class="da-step"><div class="da-step-num">1</div><h3 style="color:#fff;"><?php echo I18n::t('sol_apps.step1_title'); ?></h3><p><?php echo I18n::t('sol_apps.step1_desc'); ?></p></div>
      <div class="da-step-arrow">→</div>
      <div class="da-step"><div class="da-step-num">2</div><h3 style="color:#fff;"><?php echo I18n::t('sol_apps.step2_title'); ?></h3><p><?php echo I18n::t('sol_apps.step2_desc'); ?></p></div>
      <div class="da-step-arrow">→</div>
      <div class="da-step"><div class="da-step-num">3</div><h3 style="color:#fff;"><?php echo I18n::t('sol_apps.step3_title'); ?></h3><p><?php echo I18n::t('sol_apps.step3_desc'); ?></p></div>
    </div>
  </div>
</section>

<!-- FAQ -->
<section class="da-section">
  <div class="da-inner">
    <div style="text-align:center;">
      <div class="da-label"><?php echo I18n::t('sol_apps.faq_label'); ?></div>
      <h2 class="da-title"><?php echo I18n::t('sol_apps.faq_title'); ?></h2>
    </div>
    <div class="da-faq">
      <details><summary><?php echo I18n::t('sol_apps.faq1_q'); ?></summary><p><?php echo I18n::t('sol_apps.faq1_a'); ?></p></details>
      <details><summary><?php echo I18n::t('sol_apps.faq2_q'); ?></summary><p><?php echo I18n::t('sol_apps.faq2_a'); ?></p></details>
      <details><summary><?php echo I18n::t('sol_apps.faq3_q'); ?></summary><p><?php echo I18n::t('sol_apps.faq3_a'); ?></p></details>
      <details><summary><?php echo I18n::t('sol_apps.faq4_q'); ?></summary><p><?php echo I18n::t('sol_apps.faq4_a'); ?></p></details>
      <details><summary><?php echo I18n::t('sol_apps.faq5_q'); ?></summary><p><?php echo I18n::t('sol_apps.faq5_a'); ?></p></details>
      <details><summary><?php echo I18n::t('sol_apps.faq6_q'); ?></summary><p><?php echo I18n::t('sol_apps.faq6_a'); ?></p></details>
    </div>
  </div>
</section>

<!-- CTA FINAL -->
<section class="da-cta-section">
  <h2><?php echo I18n::t('sol_apps.cta_title'); ?></h2>
  <p><?php echo I18n::t('sol_apps.cta_desc'); ?></p>
  <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
    <a href="/cliente/criar-conta" class="da-btn-p"><?php echo I18n::t('sol_apps.cta_btn_plans'); ?></a>
    <a href="/contato" class="da-btn-s"><?php echo I18n::t('sol_apps.cta_btn_contact'); ?></a>
  </div>
</section>

<?php require __DIR__ . '/../_partials/footer.php'; ?>
<?php require __DIR__ . '/../_partials/chat-widget.php'; ?>
</body>
</html>
