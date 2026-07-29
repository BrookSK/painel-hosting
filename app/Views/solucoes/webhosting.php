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
<title><?php echo I18n::t('sol_wh.page_title'); ?> — <?php echo View::e($_nome); ?></title>
<?php require __DIR__ . '/../_partials/seo.php'; ?>
<?php require __DIR__ . '/../_partials/estilo.php'; ?>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:system-ui,-apple-system,'Segoe UI',Roboto,sans-serif;background:#fff;color:#0f172a}

/* ── Hero Web Hosting ── */
.wh-hero{background:linear-gradient(135deg,#052e16 0%,#14532d 30%,#166534 60%,#16a34a 85%,#22c55e 100%);position:relative;overflow:hidden;padding:100px 0 80px}
.wh-hero::before{content:'';position:absolute;inset:0;background-image:radial-gradient(circle,rgba(255,255,255,.08) 1px,transparent 1px);background-size:40px 40px;pointer-events:none}
.wh-hero .glow{position:absolute;width:600px;height:600px;background:radial-gradient(circle,rgba(34,197,94,.35),transparent 65%);top:-200px;right:-100px;pointer-events:none}
.wh-hero-inner{position:relative;z-index:1;max-width:1100px;margin:0 auto;padding:0 24px;display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:center}
.wh-hero-badge{display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.15);padding:5px 14px;border-radius:99px;margin-bottom:20px}
.wh-hero-badge span{font-size:.72rem;font-weight:600;color:rgba(255,255,255,.8);letter-spacing:.06em;text-transform:uppercase}
.wh-hero h1{font-size:clamp(1.8rem,4.5vw,3.2rem);font-weight:800;color:#fff;line-height:1.12;margin-bottom:18px;letter-spacing:-.02em}
.wh-hero h1 em{font-style:italic;color:#bbf7d0}
.wh-hero p{font-size:1rem;color:rgba(255,255,255,.6);line-height:1.8;margin-bottom:28px;max-width:480px}
.wh-hero-actions{display:flex;gap:12px;flex-wrap:wrap}
.wh-btn-p{display:inline-flex;align-items:center;gap:8px;padding:14px 28px;border-radius:12px;font-size:.9rem;font-weight:700;background:#fff;color:#16a34a;transition:transform .15s;text-decoration:none}
.wh-btn-p:hover{transform:translateY(-2px)}
.wh-btn-s{display:inline-flex;align-items:center;gap:8px;padding:14px 28px;border-radius:12px;font-size:.9rem;font-weight:700;background:rgba(255,255,255,.1);color:#fff;border:1.5px solid rgba(255,255,255,.2);text-decoration:none;transition:background .15s}
.wh-btn-s:hover{background:rgba(255,255,255,.18)}
.wh-hero-visual{background:rgba(255,255,255,.06);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,.12);border-radius:20px;padding:28px;color:#fff}
.wh-mock-bar{display:flex;gap:6px;margin-bottom:16px}.wh-mock-dot{width:10px;height:10px;border-radius:50%;background:rgba(255,255,255,.15)}.wh-mock-dot:first-child{background:#ef4444}.wh-mock-dot:nth-child(2){background:#f59e0b}.wh-mock-dot:nth-child(3){background:#22c55e}
.wh-mock-sidebar{display:flex;gap:14px}
.wh-mock-nav{width:30%;display:flex;flex-direction:column;gap:6px}
.wh-mock-nav-item{height:10px;border-radius:4px;background:rgba(255,255,255,.1)}
.wh-mock-nav-item.active{background:rgba(34,197,94,.4);border-left:3px solid #22c55e}
.wh-mock-main{flex:1;display:flex;flex-direction:column;gap:10px}
.wh-mock-file{display:flex;align-items:center;gap:8px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);border-radius:8px;padding:8px 12px}
.wh-mock-file-icon{font-size:14px}
.wh-mock-file-name{font-size:.7rem;color:rgba(255,255,255,.6);flex:1}
.wh-mock-file-size{font-size:.65rem;color:rgba(255,255,255,.3)}
.wh-mock-breadcrumb{font-size:.65rem;color:rgba(255,255,255,.35);margin-bottom:4px;display:flex;align-items:center;gap:4px}
@media(max-width:860px){.wh-hero-inner{grid-template-columns:1fr;text-align:center}.wh-hero p{margin:0 auto 28px}.wh-hero-actions{justify-content:center}.wh-hero-visual{display:none}}

/* ── Stats ── */
.wh-stats{background:#052e16;padding:36px 0}
.wh-stats-inner{max-width:1100px;margin:0 auto;display:grid;grid-template-columns:repeat(4,1fr);text-align:center;gap:0}
.wh-stat{padding:24px 16px;border-right:1px solid rgba(255,255,255,.06)}.wh-stat:last-child{border:none}
.wh-stat h3{font-size:2rem;font-weight:900;color:#bbf7d0;margin-bottom:4px}.wh-stat p{font-size:.8rem;color:rgba(255,255,255,.4)}
@media(max-width:640px){.wh-stats-inner{grid-template-columns:1fr 1fr}.wh-stat:nth-child(2){border-right:none}}

/* ── Sections ── */
.wh-section{padding:80px 24px}.wh-section.alt{background:#f8fafc}.wh-section.dark{background:#052e16;color:#fff}
.wh-inner{max-width:1100px;margin:0 auto}
.wh-label{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:#16a34a;margin-bottom:10px}
.wh-title{font-size:clamp(22px,3.5vw,34px);font-weight:800;color:#0f172a;margin-bottom:10px;letter-spacing:-.02em;line-height:1.15}
.wh-sub{font-size:15px;color:#64748b;line-height:1.75;max-width:560px}

/* ── Features Grid ── */
.wh-features{display:grid;grid-template-columns:repeat(3,1fr);gap:2px;background:#e2e8f0;border:1px solid #e2e8f0;border-radius:18px;overflow:hidden;margin-top:40px}
.wh-feat{background:#fff;padding:32px 24px;transition:background .2s}
.wh-feat:hover{background:#dcfce7}
.wh-feat-icon{width:48px;height:48px;background:#dcfce7;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:24px;margin-bottom:16px}
.wh-feat h3{font-size:.95rem;font-weight:700;margin-bottom:6px}.wh-feat p{font-size:.85rem;color:#64748b;line-height:1.6}
@media(max-width:860px){.wh-features{grid-template-columns:1fr 1fr}}
@media(max-width:560px){.wh-features{grid-template-columns:1fr}}

/* ── How it works ── */
.wh-steps{display:flex;align-items:flex-start;justify-content:center;gap:16px;margin-top:40px}
.wh-step{text-align:center;flex:1;max-width:260px}
.wh-step-num{width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,#16a34a,#22c55e);color:#fff;font-size:22px;font-weight:800;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;box-shadow:0 4px 20px rgba(22,163,74,.3)}
.wh-step h3{font-size:15px;font-weight:700;margin-bottom:6px}.wh-step p{font-size:13px;color:rgba(255,255,255,.5)}
.wh-step-arrow{color:rgba(255,255,255,.15);font-size:28px;padding-top:14px;flex-shrink:0}
@media(max-width:768px){.wh-steps{flex-direction:column;align-items:center}.wh-step-arrow{transform:rotate(90deg);padding:0}}

/* ── Comparison ── */
.wh-compare{display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-top:40px}
.wh-compare-card{border-radius:16px;padding:28px 24px}
.wh-compare-card.bad{background:#fef2f2;border:1px solid #fecaca}
.wh-compare-card.good{background:#dcfce7;border:2px solid #16a34a}
.wh-compare-card h3{font-size:16px;font-weight:700;margin-bottom:14px;display:flex;align-items:center;gap:8px}
.wh-compare-card ul{list-style:none;padding:0}
.wh-compare-card ul li{padding:8px 0;font-size:13px;display:flex;align-items:flex-start;gap:8px;border-bottom:1px solid rgba(0,0,0,.04)}
@media(max-width:640px){.wh-compare{grid-template-columns:1fr}}

/* ── CTA ── */
.wh-cta-section{padding:80px 24px;background:linear-gradient(135deg,#052e16,#166534);text-align:center;color:#fff}
.wh-cta-section h2{font-size:clamp(24px,4vw,36px);font-weight:800;margin-bottom:12px}
.wh-cta-section p{font-size:16px;color:rgba(255,255,255,.55);max-width:500px;margin:0 auto 28px}

/* ── FAQ ── */
.wh-faq{max-width:700px;margin:40px auto 0;display:flex;flex-direction:column;gap:10px}
.wh-faq details{background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;transition:border-color .2s}
.wh-faq details[open]{border-color:#16a34a}
.wh-faq summary{padding:16px 20px;font-size:14px;font-weight:600;cursor:pointer;list-style:none;display:flex;justify-content:space-between;align-items:center}
.wh-faq summary::after{content:'+';font-size:18px;color:#94a3b8;transition:transform .2s}
.wh-faq details[open] summary::after{content:'−';color:#16a34a}
.wh-faq summary::-webkit-details-marker{display:none}
.wh-faq details p{padding:0 20px 16px;font-size:13px;color:#64748b;line-height:1.7}
</style>
</head>
<body>
<?php require __DIR__ . '/../_partials/navbar-publica.php'; ?>

<!-- HERO -->
<section class="wh-hero">
  <div class="glow"></div>
  <div class="wh-hero-inner">
    <div>
      <div class="wh-hero-badge"><span><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg> <?php echo I18n::t('sol_wh.badge'); ?></span></div>
      <h1><?php echo I18n::t('sol_wh.hero_title_pre'); ?> <em><?php echo I18n::t('sol_wh.hero_title_em'); ?></em></h1>
      <p><?php echo I18n::t('sol_wh.hero_desc'); ?></p>
      <div class="wh-hero-actions">
        <a href="#planos" class="wh-btn-p"><?php echo I18n::t('sol_wh.btn_plans'); ?></a>
        <a href="/contato" class="wh-btn-s"><?php echo I18n::t('sol_wh.btn_contact'); ?></a>
      </div>
    </div>
    <div class="wh-hero-visual">
      <div class="wh-mock-bar"><div class="wh-mock-dot"></div><div class="wh-mock-dot"></div><div class="wh-mock-dot"></div></div>
      <div class="wh-mock-breadcrumb"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg> /home &gt; public_html</div>
      <div class="wh-mock-sidebar">
        <div class="wh-mock-nav">
          <div class="wh-mock-nav-item active"></div>
          <div class="wh-mock-nav-item"></div>
          <div class="wh-mock-nav-item"></div>
          <div class="wh-mock-nav-item"></div>
          <div class="wh-mock-nav-item"></div>
        </div>
        <div class="wh-mock-main">
          <div class="wh-mock-file"><span class="wh-mock-file-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></span><span class="wh-mock-file-name">index.php</span><span class="wh-mock-file-size">4.2 KB</span></div>
          <div class="wh-mock-file"><span class="wh-mock-file-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg></span><span class="wh-mock-file-name">wp-content/</span><span class="wh-mock-file-size">—</span></div>
          <div class="wh-mock-file"><span class="wh-mock-file-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09a1.65 1.65 0 0 0-1.08-1.51 1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09a1.65 1.65 0 0 0 1.51-1.08 1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1.08 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1.08z"/></svg></span><span class="wh-mock-file-name">.htaccess</span><span class="wh-mock-file-size">1.1 KB</span></div>
          <div class="wh-mock-file"><span class="wh-mock-file-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg></span><span class="wh-mock-file-name">database.sql</span><span class="wh-mock-file-size">12 MB</span></div>
          <div class="wh-mock-file"><span class="wh-mock-file-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></span><span class="wh-mock-file-name">.env</span><span class="wh-mock-file-size">0.3 KB</span></div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- STATS -->
<div class="wh-stats">
  <div class="wh-stats-inner">
    <div class="wh-stat"><h3><?php echo I18n::t('sol_wh.stat1_value'); ?></h3><p><?php echo I18n::t('sol_wh.stat1_desc'); ?></p></div>
    <div class="wh-stat"><h3>99.9%</h3><p><?php echo I18n::t('sol_wh.stat2_desc'); ?></p></div>
    <div class="wh-stat"><h3><?php echo I18n::t('sol_wh.stat3_value'); ?></h3><p><?php echo I18n::t('sol_wh.stat3_desc'); ?></p></div>
    <div class="wh-stat"><h3>SSL</h3><p><?php echo I18n::t('sol_wh.stat4_desc'); ?></p></div>
  </div>
</div>

<!-- COMPARAÇÃO -->
<section class="wh-section">
  <div class="wh-inner">
    <div style="text-align:center;margin-bottom:8px;">
      <div class="wh-label"><?php echo I18n::t('sol_wh.compare_label'); ?></div>
      <h2 class="wh-title"><?php echo I18n::t('sol_wh.compare_title'); ?> <?php echo View::e($_nome); ?></h2>
      <p class="wh-sub" style="margin:0 auto;"><?php echo I18n::t('sol_wh.compare_desc'); ?></p>
    </div>
    <div class="wh-compare">
      <div class="wh-compare-card bad">
        <h3><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_wh.compare_bad_title'); ?></h3>
        <ul>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_wh.compare_bad1'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_wh.compare_bad2'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_wh.compare_bad3'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_wh.compare_bad4'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_wh.compare_bad5'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_wh.compare_bad6'); ?></li>
        </ul>
      </div>
      <div class="wh-compare-card good">
        <h3><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> Web Hosting <?php echo View::e($_nome); ?></h3>
        <ul>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_wh.compare_good1'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_wh.compare_good2'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_wh.compare_good3'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_wh.compare_good4'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_wh.compare_good5'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_wh.compare_good6'); ?></li>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section class="wh-section alt">
  <div class="wh-inner">
    <div style="text-align:center;">
      <div class="wh-label"><?php echo I18n::t('sol_wh.feat_label'); ?></div>
      <h2 class="wh-title"><?php echo I18n::t('sol_wh.feat_title'); ?></h2>
    </div>
    <div class="wh-features">
      <div class="wh-feat"><div class="wh-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="16.5" y1="9.4" x2="7.5" y2="4.21"/><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg></div><h3><?php echo I18n::t('sol_wh.feat1_title'); ?></h3><p><?php echo I18n::t('sol_wh.feat1_desc'); ?></p></div>
      <div class="wh-feat"><div class="wh-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"/><path d="M12 15l-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"/><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"/><path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"/></svg></div><h3><?php echo I18n::t('sol_wh.feat2_title'); ?></h3><p><?php echo I18n::t('sol_wh.feat2_desc'); ?></p></div>
      <div class="wh-feat"><div class="wh-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg></div><h3><?php echo I18n::t('sol_wh.feat3_title'); ?></h3><p><?php echo I18n::t('sol_wh.feat3_desc'); ?></p></div>
      <div class="wh-feat"><div class="wh-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg></div><h3><?php echo I18n::t('sol_wh.feat4_title'); ?></h3><p><?php echo I18n::t('sol_wh.feat4_desc'); ?></p></div>
      <div class="wh-feat"><div class="wh-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg></div><h3><?php echo I18n::t('sol_wh.feat5_title'); ?></h3><p><?php echo I18n::t('sol_wh.feat5_desc'); ?></p></div>
      <div class="wh-feat"><div class="wh-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg></div><h3><?php echo I18n::t('sol_wh.feat6_title'); ?></h3><p><?php echo I18n::t('sol_wh.feat6_desc'); ?></p></div>
      <div class="wh-feat"><div class="wh-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg></div><h3><?php echo I18n::t('sol_wh.feat7_title'); ?></h3><p><?php echo I18n::t('sol_wh.feat7_desc'); ?></p></div>
      <div class="wh-feat"><div class="wh-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div><h3><?php echo I18n::t('sol_wh.feat8_title'); ?></h3><p><?php echo I18n::t('sol_wh.feat8_desc'); ?></p></div>
      <div class="wh-feat"><div class="wh-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg></div><h3><?php echo I18n::t('sol_wh.feat9_title'); ?></h3><p><?php echo I18n::t('sol_wh.feat9_desc'); ?></p></div>
    </div>
  </div>
</section>

<!-- COMO FUNCIONA -->
<section class="wh-section dark">
  <div class="wh-inner">
    <div style="text-align:center;margin-bottom:8px;">
      <div class="wh-label" style="color:#bbf7d0;"><?php echo I18n::t('sol_wh.steps_label'); ?></div>
      <h2 class="wh-title" style="color:#fff;"><?php echo I18n::t('sol_wh.steps_title'); ?></h2>
    </div>
    <div class="wh-steps">
      <div class="wh-step"><div class="wh-step-num">1</div><h3 style="color:#fff;"><?php echo I18n::t('sol_wh.step1_title'); ?></h3><p><?php echo I18n::t('sol_wh.step1_desc'); ?></p></div>
      <div class="wh-step-arrow">→</div>
      <div class="wh-step"><div class="wh-step-num">2</div><h3 style="color:#fff;"><?php echo I18n::t('sol_wh.step2_title'); ?></h3><p><?php echo I18n::t('sol_wh.step2_desc'); ?></p></div>
      <div class="wh-step-arrow">→</div>
      <div class="wh-step"><div class="wh-step-num">3</div><h3 style="color:#fff;"><?php echo I18n::t('sol_wh.step3_title'); ?></h3><p><?php echo I18n::t('sol_wh.step3_desc'); ?></p></div>
    </div>
  </div>
</section>

<!-- PLANOS -->
<?php $_accent = '#16a34a'; $_plan_type = 'webhosting'; $_cta_base = '/cliente/planos/checkout?plan_id='; require __DIR__ . '/_planos-section.php'; ?>

<!-- FAQ -->
<section class="wh-section">
  <div class="wh-inner">
    <div style="text-align:center;">
      <div class="wh-label"><?php echo I18n::t('sol_wh.faq_label'); ?></div>
      <h2 class="wh-title"><?php echo I18n::t('sol_wh.faq_title'); ?></h2>
    </div>
    <div class="wh-faq">
      <details><summary><?php echo I18n::t('sol_wh.faq1_q'); ?></summary><p><?php echo I18n::t('sol_wh.faq1_a'); ?></p></details>
      <details><summary><?php echo I18n::t('sol_wh.faq2_q'); ?></summary><p><?php echo I18n::t('sol_wh.faq2_a'); ?></p></details>
      <details><summary><?php echo I18n::t('sol_wh.faq3_q'); ?></summary><p><?php echo I18n::t('sol_wh.faq3_a'); ?></p></details>
      <details><summary><?php echo I18n::t('sol_wh.faq4_q'); ?></summary><p><?php echo I18n::t('sol_wh.faq4_a'); ?></p></details>
      <details><summary><?php echo I18n::t('sol_wh.faq5_q'); ?></summary><p><?php echo I18n::t('sol_wh.faq5_a'); ?></p></details>
      <details><summary><?php echo I18n::t('sol_wh.faq6_q'); ?></summary><p><?php echo I18n::t('sol_wh.faq6_a'); ?></p></details>
    </div>
  </div>
</section>

<!-- CTA FINAL -->
<section class="wh-cta-section">
  <h2><?php echo I18n::t('sol_wh.cta_title'); ?></h2>
  <p><?php echo I18n::t('sol_wh.cta_desc'); ?></p>
  <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
    <a href="#planos" class="wh-btn-p"><?php echo I18n::t('sol_wh.btn_plans'); ?></a>
    <a href="/contato" class="wh-btn-s"><?php echo I18n::t('sol_wh.btn_contact'); ?></a>
  </div>
</section>

<?php require __DIR__ . '/../_partials/footer.php'; ?>
</body>
</html>
