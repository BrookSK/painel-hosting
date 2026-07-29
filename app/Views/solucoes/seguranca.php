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
<title><?php echo I18n::t('sol_sec.page_title'); ?> — <?php echo View::e($_nome); ?></title>
<?php require __DIR__ . '/../_partials/seo.php'; ?>
<?php require __DIR__ . '/../_partials/estilo.php'; ?>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:system-ui,-apple-system,'Segoe UI',Roboto,sans-serif;background:#fff;color:#0f172a}

/* ── Hero Segurança ── */
.sg-hero{background:linear-gradient(135deg,#020617 0%,#0f172a 30%,#1e293b 60%,#334155 85%,#475569 100%);position:relative;overflow:hidden;padding:100px 0 80px}
.sg-hero::before{content:'';position:absolute;inset:0;background-image:radial-gradient(circle,rgba(255,255,255,.04) 1px,transparent 1px);background-size:40px 40px;pointer-events:none}
.sg-hero .glow{position:absolute;width:600px;height:600px;background:radial-gradient(circle,rgba(51,65,85,.5),transparent 65%);top:-200px;right:-100px;pointer-events:none}
.sg-hero-inner{position:relative;z-index:1;max-width:1100px;margin:0 auto;padding:0 24px;display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:center}
.sg-hero-badge{display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12);padding:5px 14px;border-radius:99px;margin-bottom:20px}
.sg-hero-badge span{font-size:.72rem;font-weight:600;color:rgba(255,255,255,.7);letter-spacing:.06em;text-transform:uppercase}
.sg-hero h1{font-size:clamp(1.8rem,4.5vw,3.2rem);font-weight:800;color:#fff;line-height:1.12;margin-bottom:18px;letter-spacing:-.02em}
.sg-hero h1 em{font-style:italic;color:#94a3b8}
.sg-hero p{font-size:1rem;color:rgba(255,255,255,.5);line-height:1.8;margin-bottom:28px;max-width:480px}
.sg-hero-actions{display:flex;gap:12px;flex-wrap:wrap}
.sg-btn-p{display:inline-flex;align-items:center;gap:8px;padding:14px 28px;border-radius:12px;font-size:.9rem;font-weight:700;background:#fff;color:#334155;transition:transform .15s;text-decoration:none}
.sg-btn-p:hover{transform:translateY(-2px)}
.sg-btn-s{display:inline-flex;align-items:center;gap:8px;padding:14px 28px;border-radius:12px;font-size:.9rem;font-weight:700;background:rgba(255,255,255,.08);color:#fff;border:1.5px solid rgba(255,255,255,.15);text-decoration:none;transition:background .15s}
.sg-btn-s:hover{background:rgba(255,255,255,.14)}

/* Hero Visual — Security Shield */
.sg-hero-visual{background:rgba(255,255,255,.04);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,.08);border-radius:20px;padding:28px;color:#fff}
.sg-mock-bar{display:flex;gap:6px;margin-bottom:16px}.sg-mock-dot{width:10px;height:10px;border-radius:50%;background:rgba(255,255,255,.1)}.sg-mock-dot:first-child{background:#ef4444}.sg-mock-dot:nth-child(2){background:#f59e0b}.sg-mock-dot:nth-child(3){background:#22c55e}
.sg-shield{text-align:center;margin-bottom:16px}
.sg-shield-icon{font-size:3rem;margin-bottom:8px;display:block}
.sg-shield-status{font-size:.75rem;font-weight:700;color:#4ade80;text-transform:uppercase;letter-spacing:.08em}
.sg-checks{display:grid;grid-template-columns:1fr 1fr;gap:8px}
.sg-check-item{display:flex;align-items:center;gap:8px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.06);border-radius:8px;padding:10px 12px}
.sg-check-item .icon{font-size:.9rem}
.sg-check-item .txt{font-size:.7rem;color:rgba(255,255,255,.5)}
.sg-check-item .status{font-size:.65rem;font-weight:700;color:#4ade80;margin-left:auto}
.sg-threat-bar{margin-top:12px;background:rgba(0,0,0,.3);border-radius:8px;padding:10px 12px;display:flex;align-items:center;gap:8px}
.sg-threat-bar .dot{width:8px;height:8px;border-radius:50%;background:#4ade80;box-shadow:0 0 6px rgba(74,222,128,.4)}
.sg-threat-bar .txt{font-size:.68rem;color:rgba(255,255,255,.4)}
.sg-threat-bar .count{font-size:.68rem;font-weight:700;color:#4ade80;margin-left:auto}
@media(max-width:860px){.sg-hero-inner{grid-template-columns:1fr;text-align:center}.sg-hero p{margin:0 auto 28px}.sg-hero-actions{justify-content:center}.sg-hero-visual{display:none}}

/* ── Stats ── */
.sg-stats{background:#0f172a;padding:36px 0}
.sg-stats-inner{max-width:1100px;margin:0 auto;display:grid;grid-template-columns:repeat(4,1fr);text-align:center;gap:0}
.sg-stat{padding:24px 16px;border-right:1px solid rgba(255,255,255,.06)}.sg-stat:last-child{border:none}
.sg-stat h3{font-size:2rem;font-weight:900;color:#94a3b8;margin-bottom:4px}.sg-stat p{font-size:.8rem;color:rgba(255,255,255,.35)}
@media(max-width:640px){.sg-stats-inner{grid-template-columns:1fr 1fr}.sg-stat:nth-child(2){border-right:none}}

/* ── Sections ── */
.sg-section{padding:80px 24px}.sg-section.alt{background:#f8fafc}.sg-section.dark{background:#0f172a;color:#fff}
.sg-inner{max-width:1100px;margin:0 auto}
.sg-label{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:#475569;margin-bottom:10px}
.sg-title{font-size:clamp(22px,3.5vw,34px);font-weight:800;color:#0f172a;margin-bottom:10px;letter-spacing:-.02em;line-height:1.15}
.sg-sub{font-size:15px;color:#64748b;line-height:1.75;max-width:560px}

/* ── Features Grid ── */
.sg-features{display:grid;grid-template-columns:repeat(3,1fr);gap:2px;background:#e2e8f0;border:1px solid #e2e8f0;border-radius:18px;overflow:hidden;margin-top:40px}
.sg-feat{background:#fff;padding:32px 24px;transition:background .2s}
.sg-feat:hover{background:#f1f5f9}
.sg-feat-icon{width:48px;height:48px;background:#f1f5f9;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:24px;margin-bottom:16px}
.sg-feat h3{font-size:.95rem;font-weight:700;margin-bottom:6px}.sg-feat p{font-size:.85rem;color:#64748b;line-height:1.6}
@media(max-width:860px){.sg-features{grid-template-columns:1fr 1fr}}
@media(max-width:560px){.sg-features{grid-template-columns:1fr}}

/* ── How it works ── */
.sg-steps{display:flex;align-items:flex-start;justify-content:center;gap:16px;margin-top:40px}
.sg-step{text-align:center;flex:1;max-width:260px}
.sg-step-num{width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,#334155,#475569);color:#fff;font-size:22px;font-weight:800;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;box-shadow:0 4px 20px rgba(51,65,85,.3)}
.sg-step h3{font-size:15px;font-weight:700;margin-bottom:6px}.sg-step p{font-size:13px;color:rgba(255,255,255,.5)}
.sg-step-arrow{color:rgba(255,255,255,.15);font-size:28px;padding-top:14px;flex-shrink:0}
@media(max-width:768px){.sg-steps{flex-direction:column;align-items:center}.sg-step-arrow{transform:rotate(90deg);padding:0}}

/* ── Comparison ── */
.sg-compare{display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-top:40px}
.sg-compare-card{border-radius:16px;padding:28px 24px}
.sg-compare-card.bad{background:#fef2f2;border:1px solid #fecaca}
.sg-compare-card.good{background:#f1f5f9;border:2px solid #334155}
.sg-compare-card h3{font-size:16px;font-weight:700;margin-bottom:14px;display:flex;align-items:center;gap:8px}
.sg-compare-card ul{list-style:none;padding:0}
.sg-compare-card ul li{padding:8px 0;font-size:13px;display:flex;align-items:flex-start;gap:8px;border-bottom:1px solid rgba(0,0,0,.04)}
@media(max-width:640px){.sg-compare{grid-template-columns:1fr}}

/* ── CTA ── */
.sg-cta-section{padding:80px 24px;background:linear-gradient(135deg,#020617,#0f172a);text-align:center;color:#fff}
.sg-cta-section h2{font-size:clamp(24px,4vw,36px);font-weight:800;margin-bottom:12px}
.sg-cta-section p{font-size:16px;color:rgba(255,255,255,.5);max-width:500px;margin:0 auto 28px}

/* ── FAQ ── */
.sg-faq{max-width:700px;margin:40px auto 0;display:flex;flex-direction:column;gap:10px}
.sg-faq details{background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;transition:border-color .2s}
.sg-faq details[open]{border-color:#334155}
.sg-faq summary{padding:16px 20px;font-size:14px;font-weight:600;cursor:pointer;list-style:none;display:flex;justify-content:space-between;align-items:center}
.sg-faq summary::after{content:'+';font-size:18px;color:#94a3b8;transition:transform .2s}
.sg-faq details[open] summary::after{content:'−';color:#334155}
.sg-faq summary::-webkit-details-marker{display:none}
.sg-faq details p{padding:0 20px 16px;font-size:13px;color:#64748b;line-height:1.7}
</style>
</head>
<body>
<?php require __DIR__ . '/../_partials/navbar-publica.php'; ?>

<!-- HERO -->
<section class="sg-hero">
  <div class="glow"></div>
  <div class="sg-hero-inner">
    <div>
      <div class="sg-hero-badge"><span><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg> <?php echo I18n::t('sol_sec.hero_badge'); ?></span></div>
      <h1><?php echo I18n::t('sol_sec.hero_title_pre'); ?> <em><?php echo I18n::t('sol_sec.hero_title_em'); ?></em></h1>
      <p><?php echo I18n::t('sol_sec.hero_desc'); ?></p>
      <div class="sg-hero-actions">
        <a href="/cliente/criar-conta" class="sg-btn-p"><?php echo I18n::t('sol_sec.hero_btn_plans'); ?></a>
        <a href="/contato" class="sg-btn-s"><?php echo I18n::t('sol_sec.hero_btn_contact'); ?></a>
      </div>
    </div>
    <div class="sg-hero-visual">
      <div class="sg-mock-bar"><div class="sg-mock-dot"></div><div class="sg-mock-dot"></div><div class="sg-mock-dot"></div></div>
      <div class="sg-shield">
        <span class="sg-shield-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></span>
        <div class="sg-shield-status"><?php echo I18n::t('sol_sec.mock_status'); ?></div>
      </div>
      <div class="sg-checks">
        <div class="sg-check-item"><span class="icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></span><span class="txt">DDoS</span><span class="status">✓</span></div>
        <div class="sg-check-item"><span class="icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></span><span class="txt">SSL</span><span class="status">✓</span></div>
        <div class="sg-check-item"><span class="icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg></span><span class="txt">2FA</span><span class="status">✓</span></div>
        <div class="sg-check-item"><span class="icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/><line x1="2" y1="16" x2="22" y2="16"/><line x1="8" y1="4" x2="8" y2="10"/><line x1="16" y1="4" x2="16" y2="10"/><line x1="12" y1="10" x2="12" y2="16"/><line x1="6" y1="16" x2="6" y2="20"/><line x1="18" y1="16" x2="18" y2="20"/></svg></span><span class="txt">Firewall</span><span class="status">✓</span></div>
      </div>
      <div class="sg-threat-bar">
        <div class="dot"></div>
        <span class="txt"><?php echo I18n::t('sol_sec.mock_threats'); ?></span>
        <span class="count">1.247</span>
      </div>
    </div>
  </div>
</section>

<!-- STATS -->
<div class="sg-stats">
  <div class="sg-stats-inner">
    <div class="sg-stat"><h3>DDoS</h3><p><?php echo I18n::t('sol_sec.stats_ddos_label'); ?></p></div>
    <div class="sg-stat"><h3>SSL</h3><p><?php echo I18n::t('sol_sec.stats_ssl_label'); ?></p></div>
    <div class="sg-stat"><h3>2FA</h3><p><?php echo I18n::t('sol_sec.stats_2fa_label'); ?></p></div>
    <div class="sg-stat"><h3>100%</h3><p><?php echo I18n::t('sol_sec.stats_isolation_label'); ?></p></div>
  </div>
</div>

<!-- COMPARAÇÃO -->
<section class="sg-section">
  <div class="sg-inner">
    <div style="text-align:center;margin-bottom:8px;">
      <div class="sg-label"><?php echo I18n::t('sol_sec.why_badge'); ?></div>
      <h2 class="sg-title"><?php echo I18n::t('sol_sec.why_title'); ?> <?php echo View::e($_nome); ?></h2>
      <p class="sg-sub" style="margin:0 auto;"><?php echo I18n::t('sol_sec.why_desc'); ?></p>
    </div>
    <div class="sg-compare">
      <div class="sg-compare-card bad">
        <h3><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_sec.compare_bad_title'); ?></h3>
        <ul>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_sec.compare_bad1'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_sec.compare_bad2'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_sec.compare_bad3'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_sec.compare_bad4'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_sec.compare_bad5'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_sec.compare_bad6'); ?></li>
        </ul>
      </div>
      <div class="sg-compare-card good">
        <h3><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_sec.compare_good_title'); ?> <?php echo View::e($_nome); ?></h3>
        <ul>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_sec.compare_good1'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_sec.compare_good2'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_sec.compare_good3'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_sec.compare_good4'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_sec.compare_good5'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_sec.compare_good6'); ?></li>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section class="sg-section alt">
  <div class="sg-inner">
    <div style="text-align:center;">
      <div class="sg-label"><?php echo I18n::t('sol_sec.feat_label'); ?></div>
      <h2 class="sg-title"><?php echo I18n::t('sol_sec.feat_title'); ?></h2>
    </div>
    <div class="sg-features">
      <div class="sg-feat"><div class="sg-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div><h3><?php echo I18n::t('sol_sec.feat1_title'); ?></h3><p><?php echo I18n::t('sol_sec.feat1_desc'); ?></p></div>
      <div class="sg-feat"><div class="sg-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></div><h3><?php echo I18n::t('sol_sec.feat2_title'); ?></h3><p><?php echo I18n::t('sol_sec.feat2_desc'); ?></p></div>
      <div class="sg-feat"><div class="sg-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg></div><h3><?php echo I18n::t('sol_sec.feat3_title'); ?></h3><p><?php echo I18n::t('sol_sec.feat3_desc'); ?></p></div>
      <div class="sg-feat"><div class="sg-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="16.5" y1="9.4" x2="7.5" y2="4.21"/><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg></div><h3><?php echo I18n::t('sol_sec.feat4_title'); ?></h3><p><?php echo I18n::t('sol_sec.feat4_desc'); ?></p></div>
      <div class="sg-feat"><div class="sg-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/><line x1="2" y1="16" x2="22" y2="16"/><line x1="8" y1="4" x2="8" y2="10"/><line x1="16" y1="4" x2="16" y2="10"/><line x1="12" y1="10" x2="12" y2="16"/><line x1="6" y1="16" x2="6" y2="20"/><line x1="18" y1="16" x2="18" y2="20"/></svg></div><h3><?php echo I18n::t('sol_sec.feat5_title'); ?></h3><p><?php echo I18n::t('sol_sec.feat5_desc'); ?></p></div>
      <div class="sg-feat"><div class="sg-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1"/></svg></div><h3><?php echo I18n::t('sol_sec.feat6_title'); ?></h3><p><?php echo I18n::t('sol_sec.feat6_desc'); ?></p></div>
      <div class="sg-feat"><div class="sg-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg></div><h3><?php echo I18n::t('sol_sec.feat7_title'); ?></h3><p><?php echo I18n::t('sol_sec.feat7_desc'); ?></p></div>
      <div class="sg-feat"><div class="sg-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div><h3><?php echo I18n::t('sol_sec.feat8_title'); ?></h3><p><?php echo I18n::t('sol_sec.feat8_desc'); ?></p></div>
      <div class="sg-feat"><div class="sg-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></div><h3><?php echo I18n::t('sol_sec.feat9_title'); ?></h3><p><?php echo I18n::t('sol_sec.feat9_desc'); ?></p></div>
    </div>
  </div>
</section>

<!-- COMO FUNCIONA -->
<section class="sg-section dark">
  <div class="sg-inner">
    <div style="text-align:center;margin-bottom:8px;">
      <div class="sg-label" style="color:#94a3b8;"><?php echo I18n::t('sol_sec.steps_label'); ?></div>
      <h2 class="sg-title" style="color:#fff;"><?php echo I18n::t('sol_sec.steps_title'); ?></h2>
    </div>
    <div class="sg-steps">
      <div class="sg-step"><div class="sg-step-num">1</div><h3 style="color:#fff;"><?php echo I18n::t('sol_sec.step1_title'); ?></h3><p><?php echo I18n::t('sol_sec.step1_desc'); ?></p></div>
      <div class="sg-step-arrow">→</div>
      <div class="sg-step"><div class="sg-step-num">2</div><h3 style="color:#fff;"><?php echo I18n::t('sol_sec.step2_title'); ?></h3><p><?php echo I18n::t('sol_sec.step2_desc'); ?></p></div>
      <div class="sg-step-arrow">→</div>
      <div class="sg-step"><div class="sg-step-num">3</div><h3 style="color:#fff;"><?php echo I18n::t('sol_sec.step3_title'); ?></h3><p><?php echo I18n::t('sol_sec.step3_desc'); ?></p></div>
    </div>
  </div>
</section>

<!-- FAQ -->
<section class="sg-section">
  <div class="sg-inner">
    <div style="text-align:center;">
      <div class="sg-label"><?php echo I18n::t('sol_sec.faq_label'); ?></div>
      <h2 class="sg-title"><?php echo I18n::t('sol_sec.faq_title'); ?></h2>
    </div>
    <div class="sg-faq">
      <details><summary><?php echo I18n::t('sol_sec.faq1_q'); ?></summary><p><?php echo I18n::t('sol_sec.faq1_a'); ?></p></details>
      <details><summary><?php echo I18n::t('sol_sec.faq2_q'); ?></summary><p><?php echo I18n::t('sol_sec.faq2_a'); ?></p></details>
      <details><summary><?php echo I18n::t('sol_sec.faq3_q'); ?></summary><p><?php echo I18n::t('sol_sec.faq3_a'); ?></p></details>
      <details><summary><?php echo I18n::t('sol_sec.faq4_q'); ?></summary><p><?php echo I18n::t('sol_sec.faq4_a'); ?></p></details>
      <details><summary><?php echo I18n::t('sol_sec.faq5_q'); ?></summary><p><?php echo I18n::t('sol_sec.faq5_a'); ?></p></details>
      <details><summary><?php echo I18n::t('sol_sec.faq6_q'); ?></summary><p><?php echo I18n::t('sol_sec.faq6_a'); ?></p></details>
    </div>
  </div>
</section>

<!-- CTA FINAL -->
<section class="sg-cta-section">
  <h2><?php echo I18n::t('sol_sec.cta_title'); ?></h2>
  <p><?php echo I18n::t('sol_sec.cta_desc'); ?></p>
  <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
    <a href="/cliente/criar-conta" class="sg-btn-p"><?php echo I18n::t('sol_sec.cta_btn_plans'); ?></a>
    <a href="/contato" class="sg-btn-s"><?php echo I18n::t('sol_sec.cta_btn_contact'); ?></a>
  </div>
</section>

<?php require __DIR__ . '/../_partials/footer.php'; ?>
</body>
</html>
