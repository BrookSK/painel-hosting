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
<title><?php echo I18n::t('sol_wp.page_title'); ?> — <?php echo View::e($_nome); ?></title>
<?php require __DIR__ . '/../_partials/seo.php'; ?>
<?php require __DIR__ . '/../_partials/estilo.php'; ?>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:system-ui,-apple-system,'Segoe UI',Roboto,sans-serif;background:#fff;color:#0f172a}

/* ── Hero WordPress ── */
.wp-hero{background:linear-gradient(135deg,#1a1a2e 0%,#16213e 30%,#0f3460 60%,#1d4ed8 85%,#3b82f6 100%);position:relative;overflow:hidden;padding:100px 0 80px}
.wp-hero::before{content:'';position:absolute;inset:0;background-image:radial-gradient(circle,rgba(255,255,255,.08) 1px,transparent 1px);background-size:40px 40px;pointer-events:none}
.wp-hero .glow{position:absolute;width:600px;height:600px;background:radial-gradient(circle,rgba(59,130,246,.35),transparent 65%);top:-200px;right:-100px;pointer-events:none}
.wp-hero-inner{position:relative;z-index:1;max-width:1100px;margin:0 auto;padding:0 24px;display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:center}
.wp-hero-badge{display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.15);padding:5px 14px;border-radius:99px;margin-bottom:20px}
.wp-hero-badge span{font-size:.72rem;font-weight:600;color:rgba(255,255,255,.8);letter-spacing:.06em;text-transform:uppercase}
.wp-hero h1{font-size:clamp(1.8rem,4.5vw,3.2rem);font-weight:800;color:#fff;line-height:1.12;margin-bottom:18px;letter-spacing:-.02em}
.wp-hero h1 em{font-style:italic;color:#93c5fd}
.wp-hero p{font-size:1rem;color:rgba(255,255,255,.6);line-height:1.8;margin-bottom:28px;max-width:480px}
.wp-hero-actions{display:flex;gap:12px;flex-wrap:wrap}
.wp-btn-p{display:inline-flex;align-items:center;gap:8px;padding:14px 28px;border-radius:12px;font-size:.9rem;font-weight:700;background:#fff;color:#1d4ed8;transition:transform .15s;text-decoration:none}
.wp-btn-p:hover{transform:translateY(-2px)}
.wp-btn-s{display:inline-flex;align-items:center;gap:8px;padding:14px 28px;border-radius:12px;font-size:.9rem;font-weight:700;background:rgba(255,255,255,.1);color:#fff;border:1.5px solid rgba(255,255,255,.2);text-decoration:none;transition:background .15s}
.wp-btn-s:hover{background:rgba(255,255,255,.18)}
.wp-hero-visual{background:rgba(255,255,255,.06);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,.12);border-radius:20px;padding:28px;color:#fff}
.wp-mock-bar{display:flex;gap:6px;margin-bottom:16px}.wp-mock-dot{width:10px;height:10px;border-radius:50%;background:rgba(255,255,255,.15)}.wp-mock-dot:first-child{background:#ef4444}.wp-mock-dot:nth-child(2){background:#f59e0b}.wp-mock-dot:nth-child(3){background:#22c55e}
.wp-mock-content{display:flex;flex-direction:column;gap:10px}
.wp-mock-line{height:10px;border-radius:4px;background:rgba(255,255,255,.08)}
.wp-mock-line.w60{width:60%}.wp-mock-line.w80{width:80%}.wp-mock-line.w40{width:40%}.wp-mock-line.w100{width:100%}
.wp-mock-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:12px}
.wp-mock-card{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);border-radius:10px;padding:14px;text-align:center}
.wp-mock-card .num{font-size:1.4rem;font-weight:800;color:#93c5fd}.wp-mock-card .lbl{font-size:.7rem;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.05em;margin-top:4px}
@media(max-width:860px){.wp-hero-inner{grid-template-columns:1fr;text-align:center}.wp-hero p{margin:0 auto 28px}.wp-hero-actions{justify-content:center}.wp-hero-visual{display:none}}

/* ── Stats ── */
.wp-stats{background:#0f172a;padding:36px 0}
.wp-stats-inner{max-width:1100px;margin:0 auto;display:grid;grid-template-columns:repeat(4,1fr);text-align:center;gap:0}
.wp-stat{padding:24px 16px;border-right:1px solid rgba(255,255,255,.06)}.wp-stat:last-child{border:none}
.wp-stat h3{font-size:2rem;font-weight:900;color:#93c5fd;margin-bottom:4px}.wp-stat p{font-size:.8rem;color:rgba(255,255,255,.4)}
@media(max-width:640px){.wp-stats-inner{grid-template-columns:1fr 1fr}.wp-stat:nth-child(2){border-right:none}}

/* ── Sections ── */
.wp-section{padding:80px 24px}.wp-section.alt{background:#f8fafc}.wp-section.dark{background:#0f172a;color:#fff}
.wp-inner{max-width:1100px;margin:0 auto}
.wp-label{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:#1d4ed8;margin-bottom:10px}
.wp-title{font-size:clamp(22px,3.5vw,34px);font-weight:800;color:#0f172a;margin-bottom:10px;letter-spacing:-.02em;line-height:1.15}
.wp-sub{font-size:15px;color:#64748b;line-height:1.75;max-width:560px}

/* ── Features Grid ── */
.wp-features{display:grid;grid-template-columns:repeat(3,1fr);gap:2px;background:#e2e8f0;border:1px solid #e2e8f0;border-radius:18px;overflow:hidden;margin-top:40px}
.wp-feat{background:#fff;padding:32px 24px;transition:background .2s}
.wp-feat:hover{background:#eff6ff}
.wp-feat-icon{width:48px;height:48px;background:#eff6ff;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:24px;margin-bottom:16px}
.wp-feat h3{font-size:.95rem;font-weight:700;margin-bottom:6px}.wp-feat p{font-size:.85rem;color:#64748b;line-height:1.6}
@media(max-width:860px){.wp-features{grid-template-columns:1fr 1fr}}
@media(max-width:560px){.wp-features{grid-template-columns:1fr}}

/* ── How it works ── */
.wp-steps{display:flex;align-items:flex-start;justify-content:center;gap:16px;margin-top:40px}
.wp-step{text-align:center;flex:1;max-width:260px}
.wp-step-num{width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,#1d4ed8,#3b82f6);color:#fff;font-size:22px;font-weight:800;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;box-shadow:0 4px 20px rgba(29,78,216,.3)}
.wp-step h3{font-size:15px;font-weight:700;margin-bottom:6px}.wp-step p{font-size:13px;color:rgba(255,255,255,.5)}
.wp-step-arrow{color:rgba(255,255,255,.15);font-size:28px;padding-top:14px;flex-shrink:0}
@media(max-width:768px){.wp-steps{flex-direction:column;align-items:center}.wp-step-arrow{transform:rotate(90deg);padding:0}}

/* ── Comparison ── */
.wp-compare{display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-top:40px}
.wp-compare-card{border-radius:16px;padding:28px 24px}
.wp-compare-card.bad{background:#fef2f2;border:1px solid #fecaca}
.wp-compare-card.good{background:#eff6ff;border:2px solid #1d4ed8}
.wp-compare-card h3{font-size:16px;font-weight:700;margin-bottom:14px;display:flex;align-items:center;gap:8px}
.wp-compare-card ul{list-style:none;padding:0}
.wp-compare-card ul li{padding:8px 0;font-size:13px;display:flex;align-items:flex-start;gap:8px;border-bottom:1px solid rgba(0,0,0,.04)}
@media(max-width:640px){.wp-compare{grid-template-columns:1fr}}

/* ── CTA ── */
.wp-cta-section{padding:80px 24px;background:linear-gradient(135deg,#1a1a2e,#0f3460);text-align:center;color:#fff}
.wp-cta-section h2{font-size:clamp(24px,4vw,36px);font-weight:800;margin-bottom:12px}
.wp-cta-section p{font-size:16px;color:rgba(255,255,255,.55);max-width:500px;margin:0 auto 28px}

/* ── FAQ ── */
.wp-faq{max-width:700px;margin:40px auto 0;display:flex;flex-direction:column;gap:10px}
.wp-faq details{background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;transition:border-color .2s}
.wp-faq details[open]{border-color:#1d4ed8}
.wp-faq summary{padding:16px 20px;font-size:14px;font-weight:600;cursor:pointer;list-style:none;display:flex;justify-content:space-between;align-items:center}
.wp-faq summary::after{content:'+';font-size:18px;color:#94a3b8;transition:transform .2s}
.wp-faq details[open] summary::after{content:'−';color:#1d4ed8}
.wp-faq summary::-webkit-details-marker{display:none}
.wp-faq details p{padding:0 20px 16px;font-size:13px;color:#64748b;line-height:1.7}
</style>
</head>
<body>
<?php require __DIR__ . '/../_partials/navbar-publica.php'; ?>

<!-- HERO -->
<section class="wp-hero">
  <div class="glow"></div>
  <div class="wp-hero-inner">
    <div>
      <div class="wp-hero-badge"><span><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg> <?php echo I18n::t('sol_wp.badge'); ?></span></div>
      <h1><?php echo I18n::t('sol_wp.hero_title_pre'); ?> <em><?php echo I18n::t('sol_wp.hero_title_em'); ?></em> <?php echo I18n::t('sol_wp.hero_title_post'); ?></h1>
      <p><?php echo I18n::t('sol_wp.hero_desc'); ?></p>
      <div class="wp-hero-actions">
        <a href="#planos" class="wp-btn-p"><?php echo I18n::t('sol_wp.btn_plans'); ?></a>
        <a href="/contato" class="wp-btn-s"><?php echo I18n::t('sol_wp.btn_contact'); ?></a>
      </div>
    </div>
    <div class="wp-hero-visual">
      <div class="wp-mock-bar"><div class="wp-mock-dot"></div><div class="wp-mock-dot"></div><div class="wp-mock-dot"></div></div>
      <div class="wp-mock-content">
        <div class="wp-mock-line w80"></div>
        <div class="wp-mock-line w60"></div>
        <div class="wp-mock-line w100"></div>
        <div class="wp-mock-grid">
          <div class="wp-mock-card"><div class="num">99.9%</div><div class="lbl"><?php echo I18n::t('sol_wp.mock_uptime'); ?></div></div>
          <div class="wp-mock-card"><div class="num">&lt;200ms</div><div class="lbl">TTFB</div></div>
          <div class="wp-mock-card"><div class="num">SSL</div><div class="lbl"><?php echo I18n::t('sol_wp.mock_free'); ?></div></div>
          <div class="wp-mock-card"><div class="num">24/7</div><div class="lbl"><?php echo I18n::t('sol_wp.mock_support'); ?></div></div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- STATS -->
<div class="wp-stats">
  <div class="wp-stats-inner">
    <div class="wp-stat"><h3><?php echo I18n::t('sol_wp.stat_1click'); ?></h3><p><?php echo I18n::t('sol_wp.stat_1click_desc'); ?></p></div>
    <div class="wp-stat"><h3>99.9%</h3><p><?php echo I18n::t('sol_wp.stat_uptime'); ?></p></div>
    <div class="wp-stat"><h3>SSL</h3><p><?php echo I18n::t('sol_wp.stat_ssl'); ?></p></div>
    <div class="wp-stat"><h3>24/7</h3><p><?php echo I18n::t('sol_wp.stat_support'); ?></p></div>
  </div>
</div>

<!-- COMPARAÇÃO -->
<section class="wp-section">
  <div class="wp-inner">
    <div style="text-align:center;margin-bottom:8px;">
      <div class="wp-label"><?php echo I18n::t('sol_wp.compare_label'); ?></div>
      <h2 class="wp-title"><?php echo I18n::t('sol_wp.compare_title'); ?></h2>
      <p class="wp-sub" style="margin:0 auto;"><?php echo I18n::t('sol_wp.compare_desc'); ?></p>
    </div>
    <div class="wp-compare">
      <div class="wp-compare-card bad">
        <h3><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_wp.compare_bad_title'); ?></h3>
        <ul>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_wp.compare_bad1'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_wp.compare_bad2'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_wp.compare_bad3'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_wp.compare_bad4'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_wp.compare_bad5'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_wp.compare_bad6'); ?></li>
        </ul>
      </div>
      <div class="wp-compare-card good">
        <h3><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo View::e($_nome); ?> WordPress</h3>
        <ul>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_wp.compare_good1'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_wp.compare_good2'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_wp.compare_good3'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_wp.compare_good4'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_wp.compare_good5'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_wp.compare_good6'); ?></li>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section class="wp-section alt">
  <div class="wp-inner">
    <div style="text-align:center;">
      <div class="wp-label"><?php echo I18n::t('sol_wp.feat_label'); ?></div>
      <h2 class="wp-title"><?php echo I18n::t('sol_wp.feat_title'); ?></h2>
    </div>
    <div class="wp-features">
      <div class="wp-feat"><div class="wp-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"/><path d="M12 15l-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"/><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"/><path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"/></svg></div><h3><?php echo I18n::t('sol_wp.feat1_title'); ?></h3><p><?php echo I18n::t('sol_wp.feat1_desc'); ?></p></div>
      <div class="wp-feat"><div class="wp-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg></div><h3><?php echo I18n::t('sol_wp.feat2_title'); ?></h3><p><?php echo I18n::t('sol_wp.feat2_desc'); ?></p></div>
      <div class="wp-feat"><div class="wp-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></div><h3><?php echo I18n::t('sol_wp.feat3_title'); ?></h3><p><?php echo I18n::t('sol_wp.feat3_desc'); ?></p></div>
      <div class="wp-feat"><div class="wp-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg></div><h3><?php echo I18n::t('sol_wp.feat4_title'); ?></h3><p><?php echo I18n::t('sol_wp.feat4_desc'); ?></p></div>
      <div class="wp-feat"><div class="wp-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg></div><h3><?php echo I18n::t('sol_wp.feat5_title'); ?></h3><p><?php echo I18n::t('sol_wp.feat5_desc'); ?></p></div>
      <div class="wp-feat"><div class="wp-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg></div><h3><?php echo I18n::t('sol_wp.feat6_title'); ?></h3><p><?php echo I18n::t('sol_wp.feat6_desc'); ?></p></div>
      <div class="wp-feat"><div class="wp-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg></div><h3><?php echo I18n::t('sol_wp.feat7_title'); ?></h3><p><?php echo I18n::t('sol_wp.feat7_desc'); ?></p></div>
      <div class="wp-feat"><div class="wp-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div><h3><?php echo I18n::t('sol_wp.feat8_title'); ?></h3><p><?php echo I18n::t('sol_wp.feat8_desc'); ?></p></div>
      <div class="wp-feat"><div class="wp-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg></div><h3><?php echo I18n::t('sol_wp.feat9_title'); ?></h3><p><?php echo I18n::t('sol_wp.feat9_desc'); ?></p></div>
    </div>
  </div>
</section>

<!-- COMO FUNCIONA -->
<section class="wp-section dark">
  <div class="wp-inner">
    <div style="text-align:center;margin-bottom:8px;">
      <div class="wp-label" style="color:#93c5fd;"><?php echo I18n::t('sol_wp.steps_label'); ?></div>
      <h2 class="wp-title" style="color:#fff;"><?php echo I18n::t('sol_wp.steps_title'); ?></h2>
    </div>
    <div class="wp-steps">
      <div class="wp-step"><div class="wp-step-num">1</div><h3 style="color:#fff;"><?php echo I18n::t('sol_wp.step1_title'); ?></h3><p><?php echo I18n::t('sol_wp.step1_desc'); ?></p></div>
      <div class="wp-step-arrow">→</div>
      <div class="wp-step"><div class="wp-step-num">2</div><h3 style="color:#fff;"><?php echo I18n::t('sol_wp.step2_title'); ?></h3><p><?php echo I18n::t('sol_wp.step2_desc'); ?></p></div>
      <div class="wp-step-arrow">→</div>
      <div class="wp-step"><div class="wp-step-num">3</div><h3 style="color:#fff;"><?php echo I18n::t('sol_wp.step3_title'); ?></h3><p><?php echo I18n::t('sol_wp.step3_desc'); ?></p></div>
    </div>
  </div>
</section>

<!-- PLANOS -->
<?php $_accent = '#1d4ed8'; $_plan_type = 'wordpress'; $_cta_base = '/cliente/planos/checkout?plan_id='; require __DIR__ . '/_planos-section.php'; ?>

<!-- FAQ -->
<section class="wp-section">
  <div class="wp-inner">
    <div style="text-align:center;">
      <div class="wp-label"><?php echo I18n::t('sol_wp.faq_label'); ?></div>
      <h2 class="wp-title"><?php echo I18n::t('sol_wp.faq_title'); ?></h2>
    </div>
    <div class="wp-faq">
      <details><summary><?php echo I18n::t('sol_wp.faq1_q'); ?></summary><p><?php echo I18n::t('sol_wp.faq1_a'); ?></p></details>
      <details><summary><?php echo I18n::t('sol_wp.faq2_q'); ?></summary><p><?php echo I18n::t('sol_wp.faq2_a'); ?></p></details>
      <details><summary><?php echo I18n::t('sol_wp.faq3_q'); ?></summary><p><?php echo I18n::t('sol_wp.faq3_a'); ?></p></details>
      <details><summary><?php echo I18n::t('sol_wp.faq4_q'); ?></summary><p><?php echo I18n::t('sol_wp.faq4_a'); ?></p></details>
      <details><summary><?php echo I18n::t('sol_wp.faq5_q'); ?></summary><p><?php echo I18n::t('sol_wp.faq5_a'); ?></p></details>
      <details><summary><?php echo I18n::t('sol_wp.faq6_q'); ?></summary><p><?php echo I18n::t('sol_wp.faq6_a'); ?></p></details>
    </div>
  </div>
</section>

<!-- CTA FINAL -->
<section class="wp-cta-section">
  <h2><?php echo I18n::t('sol_wp.cta_title'); ?></h2>
  <p><?php echo I18n::t('sol_wp.cta_desc'); ?></p>
  <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
    <a href="#planos" class="wp-btn-p"><?php echo I18n::t('sol_wp.btn_plans'); ?></a>
    <a href="/contato" class="wp-btn-s"><?php echo I18n::t('sol_wp.btn_contact'); ?></a>
  </div>
</section>

<?php require __DIR__ . '/../_partials/footer.php'; ?>
<?php require __DIR__ . '/../_partials/chat-widget.php'; ?>
</body>
</html>
