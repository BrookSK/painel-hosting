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
<title><?php echo I18n::t('sol_devops.page_title'); ?> — <?php echo View::e($_nome); ?></title>
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
      <div class="dv-hero-badge"><span><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg> <?php echo I18n::t('sol_devops.hero_badge'); ?></span></div>
      <h1><?php echo I18n::t('sol_devops.hero_title_pre'); ?> <em><?php echo I18n::t('sol_devops.hero_title_em'); ?></em></h1>
      <p><?php echo I18n::t('sol_devops.hero_desc'); ?></p>
      <div class="dv-hero-actions">
        <a href="/cliente/criar-conta" class="dv-btn-p"><?php echo I18n::t('sol_devops.hero_btn_plans'); ?></a>
        <a href="/contato" class="dv-btn-s"><?php echo I18n::t('sol_devops.hero_btn_contact'); ?></a>
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
    <div class="dv-stat"><h3>SSH</h3><p><?php echo I18n::t('sol_devops.stats_ssh_label'); ?></p></div>
    <div class="dv-stat"><h3>24/7</h3><p><?php echo I18n::t('sol_devops.stats_metrics_label'); ?></p></div>
    <div class="dv-stat"><h3>Auto</h3><p><?php echo I18n::t('sol_devops.stats_backup_label'); ?></p></div>
    <div class="dv-stat"><h3>Live</h3><p><?php echo I18n::t('sol_devops.stats_logs_label'); ?></p></div>
  </div>
</div>

<!-- COMPARAÇÃO -->
<section class="dv-section">
  <div class="dv-inner">
    <div style="text-align:center;margin-bottom:8px;">
      <div class="dv-label"><?php echo I18n::t('sol_devops.why_badge'); ?></div>
      <h2 class="dv-title"><?php echo I18n::t('sol_devops.why_title'); ?> <?php echo View::e($_nome); ?></h2>
      <p class="dv-sub" style="margin:0 auto;"><?php echo I18n::t('sol_devops.why_desc'); ?></p>
    </div>
    <div class="dv-compare">
      <div class="dv-compare-card bad">
        <h3><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_devops.compare_bad_title'); ?></h3>
        <ul>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_devops.compare_bad1'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_devops.compare_bad2'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_devops.compare_bad3'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_devops.compare_bad4'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_devops.compare_bad5'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_devops.compare_bad6'); ?></li>
        </ul>
      </div>
      <div class="dv-compare-card good">
        <h3><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_devops.compare_good_title'); ?> <?php echo View::e($_nome); ?></h3>
        <ul>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_devops.compare_good1'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_devops.compare_good2'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_devops.compare_good3'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_devops.compare_good4'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_devops.compare_good5'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_devops.compare_good6'); ?></li>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section class="dv-section alt">
  <div class="dv-inner">
    <div style="text-align:center;">
      <div class="dv-label"><?php echo I18n::t('sol_devops.feat_badge'); ?></div>
      <h2 class="dv-title"><?php echo I18n::t('sol_devops.feat_title'); ?></h2>
    </div>
    <div class="dv-features">
      <div class="dv-feat"><div class="dv-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg></div><h3><?php echo I18n::t('sol_devops.feat1_title'); ?></h3><p><?php echo I18n::t('sol_devops.feat1_desc'); ?></p></div>
      <div class="dv-feat"><div class="dv-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg></div><h3><?php echo I18n::t('sol_devops.feat2_title'); ?></h3><p><?php echo I18n::t('sol_devops.feat2_desc'); ?></p></div>
      <div class="dv-feat"><div class="dv-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg></div><h3><?php echo I18n::t('sol_devops.feat3_title'); ?></h3><p><?php echo I18n::t('sol_devops.feat3_desc'); ?></p></div>
      <div class="dv-feat"><div class="dv-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1"/></svg></div><h3><?php echo I18n::t('sol_devops.feat4_title'); ?></h3><p><?php echo I18n::t('sol_devops.feat4_desc'); ?></p></div>
      <div class="dv-feat"><div class="dv-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg></div><h3><?php echo I18n::t('sol_devops.feat5_title'); ?></h3><p><?php echo I18n::t('sol_devops.feat5_desc'); ?></p></div>
      <div class="dv-feat"><div class="dv-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div><h3><?php echo I18n::t('sol_devops.feat6_title'); ?></h3><p><?php echo I18n::t('sol_devops.feat6_desc'); ?></p></div>
      <div class="dv-feat"><div class="dv-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg></div><h3><?php echo I18n::t('sol_devops.feat7_title'); ?></h3><p><?php echo I18n::t('sol_devops.feat7_desc'); ?></p></div>
      <div class="dv-feat"><div class="dv-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg></div><h3><?php echo I18n::t('sol_devops.feat8_title'); ?></h3><p><?php echo I18n::t('sol_devops.feat8_desc'); ?></p></div>
      <div class="dv-feat"><div class="dv-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v6"/><path d="M18.4 6.6L15.5 9.5"/><path d="M20 12h-6"/><path d="M18.4 17.4l-2.9-2.9"/><path d="M12 22v-6"/><path d="M5.6 17.4L8.5 14.5"/><path d="M4 12h6"/><path d="M5.6 6.6l2.9 2.9"/></svg></div><h3><?php echo I18n::t('sol_devops.feat9_title'); ?></h3><p><?php echo I18n::t('sol_devops.feat9_desc'); ?></p></div>
    </div>
  </div>
</section>

<!-- COMO FUNCIONA -->
<section class="dv-section dark">
  <div class="dv-inner">
    <div style="text-align:center;margin-bottom:8px;">
      <div class="dv-label" style="color:#6ee7b7;"><?php echo I18n::t('sol_devops.steps_label'); ?></div>
      <h2 class="dv-title" style="color:#fff;"><?php echo I18n::t('sol_devops.steps_title'); ?></h2>
    </div>
    <div class="dv-steps">
      <div class="dv-step"><div class="dv-step-num">1</div><h3 style="color:#fff;"><?php echo I18n::t('sol_devops.step1_title'); ?></h3><p><?php echo I18n::t('sol_devops.step1_desc'); ?></p></div>
      <div class="dv-step-arrow">→</div>
      <div class="dv-step"><div class="dv-step-num">2</div><h3 style="color:#fff;"><?php echo I18n::t('sol_devops.step2_title'); ?></h3><p><?php echo I18n::t('sol_devops.step2_desc'); ?></p></div>
      <div class="dv-step-arrow">→</div>
      <div class="dv-step"><div class="dv-step-num">3</div><h3 style="color:#fff;"><?php echo I18n::t('sol_devops.step3_title'); ?></h3><p><?php echo I18n::t('sol_devops.step3_desc'); ?></p></div>
    </div>
  </div>
</section>

<!-- FAQ -->
<section class="dv-section">
  <div class="dv-inner">
    <div style="text-align:center;">
      <div class="dv-label"><?php echo I18n::t('sol_devops.faq_label'); ?></div>
      <h2 class="dv-title"><?php echo I18n::t('sol_devops.faq_title'); ?></h2>
    </div>
    <div class="dv-faq">
      <details><summary><?php echo I18n::t('sol_devops.faq1_q'); ?></summary><p><?php echo I18n::t('sol_devops.faq1_a'); ?></p></details>
      <details><summary><?php echo I18n::t('sol_devops.faq2_q'); ?></summary><p><?php echo I18n::t('sol_devops.faq2_a'); ?></p></details>
      <details><summary><?php echo I18n::t('sol_devops.faq3_q'); ?></summary><p><?php echo I18n::t('sol_devops.faq3_a'); ?></p></details>
      <details><summary><?php echo I18n::t('sol_devops.faq4_q'); ?></summary><p><?php echo I18n::t('sol_devops.faq4_a'); ?></p></details>
      <details><summary><?php echo I18n::t('sol_devops.faq5_q'); ?></summary><p><?php echo I18n::t('sol_devops.faq5_a'); ?></p></details>
      <details><summary><?php echo I18n::t('sol_devops.faq6_q'); ?></summary><p><?php echo I18n::t('sol_devops.faq6_a'); ?></p></details>
    </div>
  </div>
</section>

<!-- CTA FINAL -->
<section class="dv-cta-section">
  <h2><?php echo I18n::t('sol_devops.cta_title'); ?></h2>
  <p><?php echo I18n::t('sol_devops.cta_desc'); ?></p>
  <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
    <a href="/cliente/criar-conta" class="dv-btn-p"><?php echo I18n::t('sol_devops.cta_btn_plans'); ?></a>
    <a href="/contato" class="dv-btn-s"><?php echo I18n::t('sol_devops.cta_btn_contact'); ?></a>
  </div>
</section>

<?php require __DIR__ . '/../_partials/footer.php'; ?>
</body>
</html>
