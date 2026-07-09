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
<title><?php echo I18n::t('sol_vps.page_title'); ?> — <?php echo View::e($_nome); ?></title>
<?php require __DIR__ . '/../_partials/seo.php'; ?>
<?php require __DIR__ . '/../_partials/estilo.php'; ?>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:system-ui,-apple-system,'Segoe UI',Roboto,sans-serif;background:#fff;color:#0f172a}

/* ── Hero VPS ── */
.vp-hero{background:linear-gradient(135deg,#1e1b4b 0%,#312e81 30%,#3730a3 60%,#4F46E5 85%,#7C3AED 100%);position:relative;overflow:hidden;padding:100px 0 80px}
.vp-hero::before{content:'';position:absolute;inset:0;background-image:radial-gradient(circle,rgba(255,255,255,.06) 1px,transparent 1px);background-size:40px 40px;pointer-events:none}
.vp-hero .glow{position:absolute;width:600px;height:600px;background:radial-gradient(circle,rgba(79,70,229,.4),transparent 65%);top:-200px;right:-100px;pointer-events:none}
.vp-hero-inner{position:relative;z-index:1;max-width:1100px;margin:0 auto;padding:0 24px;display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:center}
.vp-hero-badge{display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.15);padding:5px 14px;border-radius:99px;margin-bottom:20px}
.vp-hero-badge span{font-size:.72rem;font-weight:600;color:rgba(255,255,255,.8);letter-spacing:.06em;text-transform:uppercase}
.vp-hero h1{font-size:clamp(1.8rem,4.5vw,3.2rem);font-weight:800;color:#fff;line-height:1.12;margin-bottom:18px;letter-spacing:-.02em}
.vp-hero h1 em{font-style:italic;color:#c4b5fd}
.vp-hero p{font-size:1rem;color:rgba(255,255,255,.6);line-height:1.8;margin-bottom:28px;max-width:480px}
.vp-hero-actions{display:flex;gap:12px;flex-wrap:wrap}
.vp-btn-p{display:inline-flex;align-items:center;gap:8px;padding:14px 28px;border-radius:12px;font-size:.9rem;font-weight:700;background:#fff;color:#4F46E5;transition:transform .15s;text-decoration:none}
.vp-btn-p:hover{transform:translateY(-2px)}
.vp-btn-s{display:inline-flex;align-items:center;gap:8px;padding:14px 28px;border-radius:12px;font-size:.9rem;font-weight:700;background:rgba(255,255,255,.1);color:#fff;border:1.5px solid rgba(255,255,255,.2);text-decoration:none;transition:background .15s}
.vp-btn-s:hover{background:rgba(255,255,255,.18)}

/* Hero Visual — Server Dashboard */
.vp-hero-visual{background:rgba(255,255,255,.06);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,.12);border-radius:20px;padding:28px;color:#fff}
.vp-mock-bar{display:flex;gap:6px;margin-bottom:16px}.vp-mock-dot{width:10px;height:10px;border-radius:50%;background:rgba(255,255,255,.15)}.vp-mock-dot:first-child{background:#ef4444}.vp-mock-dot:nth-child(2){background:#f59e0b}.vp-mock-dot:nth-child(3){background:#22c55e}
.vp-dash-title{font-size:.7rem;text-transform:uppercase;letter-spacing:.08em;color:rgba(255,255,255,.4);margin-bottom:14px}
.vp-gauges{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:14px}
.vp-gauge{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);border-radius:12px;padding:14px 10px;text-align:center}
.vp-gauge-bar{height:6px;border-radius:3px;background:rgba(255,255,255,.1);margin:8px auto 6px;width:80%;overflow:hidden}
.vp-gauge-fill{height:100%;border-radius:3px;background:linear-gradient(90deg,#4F46E5,#7C3AED)}
.vp-gauge .lbl{font-size:.65rem;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.05em}
.vp-gauge .val{font-size:1.1rem;font-weight:800;color:#c4b5fd}
.vp-dash-status{display:flex;align-items:center;gap:8px;font-size:.75rem;color:rgba(255,255,255,.4)}
.vp-dash-status .dot{width:8px;height:8px;border-radius:50%;background:#22c55e;box-shadow:0 0 6px rgba(34,197,94,.5)}
@media(max-width:860px){.vp-hero-inner{grid-template-columns:1fr;text-align:center}.vp-hero p{margin:0 auto 28px}.vp-hero-actions{justify-content:center}.vp-hero-visual{display:none}}

/* ── Stats ── */
.vp-stats{background:#1e1b4b;padding:36px 0}
.vp-stats-inner{max-width:1100px;margin:0 auto;display:grid;grid-template-columns:repeat(4,1fr);text-align:center;gap:0}
.vp-stat{padding:24px 16px;border-right:1px solid rgba(255,255,255,.06)}.vp-stat:last-child{border:none}
.vp-stat h3{font-size:2rem;font-weight:900;color:#c4b5fd;margin-bottom:4px}.vp-stat p{font-size:.8rem;color:rgba(255,255,255,.4)}
@media(max-width:640px){.vp-stats-inner{grid-template-columns:1fr 1fr}.vp-stat:nth-child(2){border-right:none}}

/* ── Sections ── */
.vp-section{padding:80px 24px}.vp-section.alt{background:#f8fafc}.vp-section.dark{background:#1e1b4b;color:#fff}
.vp-inner{max-width:1100px;margin:0 auto}
.vp-label{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:#4F46E5;margin-bottom:10px}
.vp-title{font-size:clamp(22px,3.5vw,34px);font-weight:800;color:#0f172a;margin-bottom:10px;letter-spacing:-.02em;line-height:1.15}
.vp-sub{font-size:15px;color:#64748b;line-height:1.75;max-width:560px}

/* ── Features Grid ── */
.vp-features{display:grid;grid-template-columns:repeat(3,1fr);gap:2px;background:#e2e8f0;border:1px solid #e2e8f0;border-radius:18px;overflow:hidden;margin-top:40px}
.vp-feat{background:#fff;padding:32px 24px;transition:background .2s}
.vp-feat:hover{background:#eef2ff}
.vp-feat-icon{width:48px;height:48px;background:#eef2ff;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:24px;margin-bottom:16px}
.vp-feat h3{font-size:.95rem;font-weight:700;margin-bottom:6px}.vp-feat p{font-size:.85rem;color:#64748b;line-height:1.6}
@media(max-width:860px){.vp-features{grid-template-columns:1fr 1fr}}
@media(max-width:560px){.vp-features{grid-template-columns:1fr}}

/* ── How it works ── */
.vp-steps{display:flex;align-items:flex-start;justify-content:center;gap:16px;margin-top:40px}
.vp-step{text-align:center;flex:1;max-width:260px}
.vp-step-num{width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,#4F46E5,#7C3AED);color:#fff;font-size:22px;font-weight:800;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;box-shadow:0 4px 20px rgba(79,70,229,.3)}
.vp-step h3{font-size:15px;font-weight:700;margin-bottom:6px}.vp-step p{font-size:13px;color:rgba(255,255,255,.5)}
.vp-step-arrow{color:rgba(255,255,255,.15);font-size:28px;padding-top:14px;flex-shrink:0}
@media(max-width:768px){.vp-steps{flex-direction:column;align-items:center}.vp-step-arrow{transform:rotate(90deg);padding:0}}

/* ── Comparison ── */
.vp-compare{display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-top:40px}
.vp-compare-card{border-radius:16px;padding:28px 24px}
.vp-compare-card.bad{background:#fef2f2;border:1px solid #fecaca}
.vp-compare-card.good{background:#eef2ff;border:2px solid #4F46E5}
.vp-compare-card h3{font-size:16px;font-weight:700;margin-bottom:14px;display:flex;align-items:center;gap:8px}
.vp-compare-card ul{list-style:none;padding:0}
.vp-compare-card ul li{padding:8px 0;font-size:13px;display:flex;align-items:flex-start;gap:8px;border-bottom:1px solid rgba(0,0,0,.04)}
@media(max-width:640px){.vp-compare{grid-template-columns:1fr}}

/* ── CTA ── */
.vp-cta-section{padding:80px 24px;background:linear-gradient(135deg,#1e1b4b,#312e81);text-align:center;color:#fff}
.vp-cta-section h2{font-size:clamp(24px,4vw,36px);font-weight:800;margin-bottom:12px}
.vp-cta-section p{font-size:16px;color:rgba(255,255,255,.55);max-width:500px;margin:0 auto 28px}

/* ── FAQ ── */
.vp-faq{max-width:700px;margin:40px auto 0;display:flex;flex-direction:column;gap:10px}
.vp-faq details{background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;transition:border-color .2s}
.vp-faq details[open]{border-color:#4F46E5}
.vp-faq summary{padding:16px 20px;font-size:14px;font-weight:600;cursor:pointer;list-style:none;display:flex;justify-content:space-between;align-items:center}
.vp-faq summary::after{content:'+';font-size:18px;color:#94a3b8;transition:transform .2s}
.vp-faq details[open] summary::after{content:'−';color:#4F46E5}
.vp-faq summary::-webkit-details-marker{display:none}
.vp-faq details p{padding:0 20px 16px;font-size:13px;color:#64748b;line-height:1.7}
</style>
</head>
<body>
<?php require __DIR__ . '/../_partials/navbar-publica.php'; ?>

<!-- HERO -->
<section class="vp-hero">
  <div class="glow"></div>
  <div class="vp-hero-inner">
    <div>
      <div class="vp-hero-badge"><span><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg> <?php echo I18n::t('sol_vps.badge'); ?></span></div>
      <h1><?php echo I18n::t('sol_vps.hero_title_pre'); ?> <em><?php echo I18n::t('sol_vps.hero_title_em'); ?></em></h1>
      <p><?php echo I18n::t('sol_vps.hero_desc'); ?></p>
      <div class="vp-hero-actions">
        <a href="#planos" class="vp-btn-p"><?php echo I18n::t('sol_vps.btn_plans'); ?></a>
        <a href="/contato" class="vp-btn-s"><?php echo I18n::t('sol_vps.btn_contact'); ?></a>
      </div>
    </div>
    <div class="vp-hero-visual">
      <div class="vp-mock-bar"><div class="vp-mock-dot"></div><div class="vp-mock-dot"></div><div class="vp-mock-dot"></div></div>
      <div class="vp-dash-title">Server Dashboard — VPS-01</div>
      <div class="vp-gauges">
        <div class="vp-gauge">
          <div class="lbl">CPU</div>
          <div class="val">23%</div>
          <div class="vp-gauge-bar"><div class="vp-gauge-fill" style="width:23%"></div></div>
        </div>
        <div class="vp-gauge">
          <div class="lbl">RAM</div>
          <div class="val">1.2 GB</div>
          <div class="vp-gauge-bar"><div class="vp-gauge-fill" style="width:45%"></div></div>
        </div>
        <div class="vp-gauge">
          <div class="lbl"><?php echo I18n::t('sol_vps.gauge_disk'); ?></div>
          <div class="val">18 GB</div>
          <div class="vp-gauge-bar"><div class="vp-gauge-fill" style="width:36%"></div></div>
        </div>
      </div>
      <div class="vp-dash-status"><div class="dot"></div> <?php echo I18n::t('sol_vps.dash_status'); ?></div>
    </div>
  </div>
</section>

<!-- STATS -->
<div class="vp-stats">
  <div class="vp-stats-inner">
    <div class="vp-stat"><h3>99.9%</h3><p><?php echo I18n::t('sol_vps.stat_uptime'); ?></p></div>
    <div class="vp-stat"><h3>NVMe</h3><p><?php echo I18n::t('sol_vps.stat_nvme'); ?></p></div>
    <div class="vp-stat"><h3>DDoS</h3><p><?php echo I18n::t('sol_vps.stat_ddos'); ?></p></div>
    <div class="vp-stat"><h3>24/7</h3><p><?php echo I18n::t('sol_vps.stat_support'); ?></p></div>
  </div>
</div>

<!-- COMPARAÇÃO -->
<section class="vp-section">
  <div class="vp-inner">
    <div style="text-align:center;margin-bottom:8px;">
      <div class="vp-label"><?php echo I18n::t('sol_vps.compare_label'); ?></div>
      <h2 class="vp-title"><?php echo I18n::t('sol_vps.compare_title_pre'); ?> <?php echo View::e($_nome); ?></h2>
      <p class="vp-sub" style="margin:0 auto;"><?php echo I18n::t('sol_vps.compare_desc'); ?></p>
    </div>
    <div class="vp-compare">
      <div class="vp-compare-card bad">
        <h3><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_vps.compare_generic'); ?></h3>
        <ul>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_vps.compare_bad1'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_vps.compare_bad2'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_vps.compare_bad3'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_vps.compare_bad4'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_vps.compare_bad5'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_vps.compare_bad6'); ?></li>
        </ul>
      </div>
      <div class="vp-compare-card good">
        <h3><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> VPS <?php echo View::e($_nome); ?></h3>
        <ul>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_vps.compare_good1'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_vps.compare_good2'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_vps.compare_good3'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_vps.compare_good4'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_vps.compare_good5'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_vps.compare_good6'); ?></li>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section class="vp-section alt">
  <div class="vp-inner">
    <div style="text-align:center;">
      <div class="vp-label"><?php echo I18n::t('sol_vps.feat_label'); ?></div>
      <h2 class="vp-title"><?php echo I18n::t('sol_vps.feat_title'); ?></h2>
    </div>
    <div class="vp-features">
      <div class="vp-feat"><div class="vp-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg></div><h3><?php echo I18n::t('sol_vps.feat1_title'); ?></h3><p><?php echo I18n::t('sol_vps.feat1_desc'); ?></p></div>
      <div class="vp-feat"><div class="vp-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg></div><h3><?php echo I18n::t('sol_vps.feat2_title'); ?></h3><p><?php echo I18n::t('sol_vps.feat2_desc'); ?></p></div>
      <div class="vp-feat"><div class="vp-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg></div><h3><?php echo I18n::t('sol_vps.feat3_title'); ?></h3><p><?php echo I18n::t('sol_vps.feat3_desc'); ?></p></div>
      <div class="vp-feat"><div class="vp-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg></div><h3><?php echo I18n::t('sol_vps.feat4_title'); ?></h3><p><?php echo I18n::t('sol_vps.feat4_desc'); ?></p></div>
      <div class="vp-feat"><div class="vp-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"/><path d="M12 15l-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"/><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"/><path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"/></svg></div><h3><?php echo I18n::t('sol_vps.feat5_title'); ?></h3><p><?php echo I18n::t('sol_vps.feat5_desc'); ?></p></div>
      <div class="vp-feat"><div class="vp-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg></div><h3><?php echo I18n::t('sol_vps.feat6_title'); ?></h3><p><?php echo I18n::t('sol_vps.feat6_desc'); ?></p></div>
      <div class="vp-feat"><div class="vp-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></div><h3><?php echo I18n::t('sol_vps.feat7_title'); ?></h3><p><?php echo I18n::t('sol_vps.feat7_desc'); ?></p></div>
      <div class="vp-feat"><div class="vp-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div><h3><?php echo I18n::t('sol_vps.feat8_title'); ?></h3><p><?php echo I18n::t('sol_vps.feat8_desc'); ?></p></div>
      <div class="vp-feat"><div class="vp-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg></div><h3><?php echo I18n::t('sol_vps.feat9_title'); ?></h3><p><?php echo I18n::t('sol_vps.feat9_desc'); ?></p></div>
    </div>
  </div>
</section>

<!-- COMO FUNCIONA -->
<section class="vp-section dark">
  <div class="vp-inner">
    <div style="text-align:center;margin-bottom:8px;">
      <div class="vp-label" style="color:#c4b5fd;"><?php echo I18n::t('sol_vps.steps_label'); ?></div>
      <h2 class="vp-title" style="color:#fff;"><?php echo I18n::t('sol_vps.steps_title'); ?></h2>
    </div>
    <div class="vp-steps">
      <div class="vp-step"><div class="vp-step-num">1</div><h3 style="color:#fff;"><?php echo I18n::t('sol_vps.step1_title'); ?></h3><p><?php echo I18n::t('sol_vps.step1_desc'); ?></p></div>
      <div class="vp-step-arrow">→</div>
      <div class="vp-step"><div class="vp-step-num">2</div><h3 style="color:#fff;"><?php echo I18n::t('sol_vps.step2_title'); ?></h3><p><?php echo I18n::t('sol_vps.step2_desc'); ?></p></div>
      <div class="vp-step-arrow">→</div>
      <div class="vp-step"><div class="vp-step-num">3</div><h3 style="color:#fff;"><?php echo I18n::t('sol_vps.step3_title'); ?></h3><p><?php echo I18n::t('sol_vps.step3_desc'); ?></p></div>
    </div>
  </div>
</section>

<!-- PLANOS -->
<?php $_accent = '#4F46E5'; $_plan_type = 'vps'; $_cta_base = '/cliente/planos/checkout?plan_id='; require __DIR__ . '/_planos-section.php'; ?>

<!-- FAQ -->
<section class="vp-section">
  <div class="vp-inner">
    <div style="text-align:center;">
      <div class="vp-label"><?php echo I18n::t('sol_vps.faq_label'); ?></div>
      <h2 class="vp-title"><?php echo I18n::t('sol_vps.faq_title'); ?></h2>
    </div>
    <div class="vp-faq">
      <details><summary><?php echo I18n::t('sol_vps.faq1_q'); ?></summary><p><?php echo I18n::t('sol_vps.faq1_a'); ?></p></details>
      <details><summary><?php echo I18n::t('sol_vps.faq2_q'); ?></summary><p><?php echo I18n::t('sol_vps.faq2_a'); ?></p></details>
      <details><summary><?php echo I18n::t('sol_vps.faq3_q'); ?></summary><p><?php echo I18n::t('sol_vps.faq3_a'); ?></p></details>
      <details><summary><?php echo I18n::t('sol_vps.faq4_q'); ?></summary><p><?php echo I18n::t('sol_vps.faq4_a'); ?></p></details>
      <details><summary><?php echo I18n::t('sol_vps.faq5_q'); ?></summary><p><?php echo I18n::t('sol_vps.faq5_a'); ?></p></details>
      <details><summary><?php echo I18n::t('sol_vps.faq6_q'); ?></summary><p><?php echo I18n::t('sol_vps.faq6_a'); ?></p></details>
    </div>
  </div>
</section>

<!-- CTA FINAL -->
<section class="vp-cta-section">
  <h2><?php echo I18n::t('sol_vps.cta_title'); ?></h2>
  <p><?php echo I18n::t('sol_vps.cta_desc'); ?></p>
  <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
    <a href="#planos" class="vp-btn-p"><?php echo I18n::t('sol_vps.btn_plans'); ?></a>
    <a href="/contato" class="vp-btn-s"><?php echo I18n::t('sol_vps.btn_contact'); ?></a>
  </div>
</section>

<?php require __DIR__ . '/../_partials/footer.php'; ?>
<?php require __DIR__ . '/../_partials/chat-widget.php'; ?>
</body>
</html>
