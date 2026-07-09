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
<title><?php echo I18n::t('sol_php.page_title'); ?> — <?php echo View::e($_nome); ?></title>
<?php require __DIR__ . '/../_partials/seo.php'; ?>
<?php require __DIR__ . '/../_partials/estilo.php'; ?>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:system-ui,-apple-system,'Segoe UI',Roboto,sans-serif;background:#fff;color:#0f172a}

/* ── Hero PHP/Laravel ── */
.pl-hero{background:linear-gradient(135deg,#1a1a2e 0%,#2e1a0e 30%,#4a2010 60%,#ea580c 85%,#f97316 100%);position:relative;overflow:hidden;padding:100px 0 80px}
.pl-hero::before{content:'';position:absolute;inset:0;background-image:radial-gradient(circle,rgba(255,255,255,.08) 1px,transparent 1px);background-size:40px 40px;pointer-events:none}
.pl-hero .glow{position:absolute;width:600px;height:600px;background:radial-gradient(circle,rgba(249,115,22,.35),transparent 65%);top:-200px;right:-100px;pointer-events:none}
.pl-hero-inner{position:relative;z-index:1;max-width:1100px;margin:0 auto;padding:0 24px;display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:center}
.pl-hero-badge{display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.15);padding:5px 14px;border-radius:99px;margin-bottom:20px}
.pl-hero-badge span{font-size:.72rem;font-weight:600;color:rgba(255,255,255,.8);letter-spacing:.06em;text-transform:uppercase}
.pl-hero h1{font-size:clamp(1.8rem,4.5vw,3.2rem);font-weight:800;color:#fff;line-height:1.12;margin-bottom:18px;letter-spacing:-.02em}
.pl-hero h1 em{font-style:italic;color:#fed7aa}
.pl-hero p{font-size:1rem;color:rgba(255,255,255,.6);line-height:1.8;margin-bottom:28px;max-width:480px}
.pl-hero-actions{display:flex;gap:12px;flex-wrap:wrap}
.pl-btn-p{display:inline-flex;align-items:center;gap:8px;padding:14px 28px;border-radius:12px;font-size:.9rem;font-weight:700;background:#fff;color:#ea580c;transition:transform .15s;text-decoration:none}
.pl-btn-p:hover{transform:translateY(-2px)}
.pl-btn-s{display:inline-flex;align-items:center;gap:8px;padding:14px 28px;border-radius:12px;font-size:.9rem;font-weight:700;background:rgba(255,255,255,.1);color:#fff;border:1.5px solid rgba(255,255,255,.2);text-decoration:none;transition:background .15s}
.pl-btn-s:hover{background:rgba(255,255,255,.18)}
.pl-hero-visual{background:rgba(255,255,255,.06);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,.12);border-radius:20px;padding:28px;color:#fff;font-family:'Courier New',Courier,monospace;font-size:.85rem;line-height:1.7}
.pl-mock-bar{display:flex;gap:6px;margin-bottom:16px}.pl-mock-dot{width:10px;height:10px;border-radius:50%;background:rgba(255,255,255,.15)}.pl-mock-dot:first-child{background:#ef4444}.pl-mock-dot:nth-child(2){background:#f59e0b}.pl-mock-dot:nth-child(3){background:#22c55e}
.pl-term-line{display:flex;gap:8px;margin-bottom:6px}
.pl-term-prompt{color:#f97316;font-weight:700;white-space:nowrap}
.pl-term-cmd{color:rgba(255,255,255,.85)}
.pl-term-out{color:rgba(255,255,255,.4);font-size:.8rem;margin-bottom:8px;padding-left:18px}
.pl-term-success{color:#22c55e;font-weight:700;margin-top:8px;display:flex;align-items:center;gap:6px}
@media(max-width:860px){.pl-hero-inner{grid-template-columns:1fr;text-align:center}.pl-hero p{margin:0 auto 28px}.pl-hero-actions{justify-content:center}.pl-hero-visual{display:none}}

/* ── Stats ── */
.pl-stats{background:#0f172a;padding:36px 0}
.pl-stats-inner{max-width:1100px;margin:0 auto;display:grid;grid-template-columns:repeat(4,1fr);text-align:center;gap:0}
.pl-stat{padding:24px 16px;border-right:1px solid rgba(255,255,255,.06)}.pl-stat:last-child{border:none}
.pl-stat h3{font-size:2rem;font-weight:900;color:#fed7aa;margin-bottom:4px}.pl-stat p{font-size:.8rem;color:rgba(255,255,255,.4)}
@media(max-width:640px){.pl-stats-inner{grid-template-columns:1fr 1fr}.pl-stat:nth-child(2){border-right:none}}

/* ── Sections ── */
.pl-section{padding:80px 24px}.pl-section.alt{background:#f8fafc}.pl-section.dark{background:#0f172a;color:#fff}
.pl-inner{max-width:1100px;margin:0 auto}
.pl-label{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:#ea580c;margin-bottom:10px}
.pl-title{font-size:clamp(22px,3.5vw,34px);font-weight:800;color:#0f172a;margin-bottom:10px;letter-spacing:-.02em;line-height:1.15}
.pl-sub{font-size:15px;color:#64748b;line-height:1.75;max-width:560px}

/* ── Features Grid ── */
.pl-features{display:grid;grid-template-columns:repeat(3,1fr);gap:2px;background:#e2e8f0;border:1px solid #e2e8f0;border-radius:18px;overflow:hidden;margin-top:40px}
.pl-feat{background:#fff;padding:32px 24px;transition:background .2s}
.pl-feat:hover{background:#fff7ed}
.pl-feat-icon{width:48px;height:48px;background:#fff7ed;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:24px;margin-bottom:16px}
.pl-feat h3{font-size:.95rem;font-weight:700;margin-bottom:6px}.pl-feat p{font-size:.85rem;color:#64748b;line-height:1.6}
@media(max-width:860px){.pl-features{grid-template-columns:1fr 1fr}}
@media(max-width:560px){.pl-features{grid-template-columns:1fr}}

/* ── How it works ── */
.pl-steps{display:flex;align-items:flex-start;justify-content:center;gap:16px;margin-top:40px}
.pl-step{text-align:center;flex:1;max-width:260px}
.pl-step-num{width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,#ea580c,#f97316);color:#fff;font-size:22px;font-weight:800;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;box-shadow:0 4px 20px rgba(234,88,12,.3)}
.pl-step h3{font-size:15px;font-weight:700;margin-bottom:6px}.pl-step p{font-size:13px;color:rgba(255,255,255,.5)}
.pl-step-arrow{color:rgba(255,255,255,.15);font-size:28px;padding-top:14px;flex-shrink:0}
@media(max-width:768px){.pl-steps{flex-direction:column;align-items:center}.pl-step-arrow{transform:rotate(90deg);padding:0}}

/* ── Comparison ── */
.pl-compare{display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-top:40px}
.pl-compare-card{border-radius:16px;padding:28px 24px}
.pl-compare-card.bad{background:#fef2f2;border:1px solid #fecaca}
.pl-compare-card.good{background:#fff7ed;border:2px solid #ea580c}
.pl-compare-card h3{font-size:16px;font-weight:700;margin-bottom:14px;display:flex;align-items:center;gap:8px}
.pl-compare-card ul{list-style:none;padding:0}
.pl-compare-card ul li{padding:8px 0;font-size:13px;display:flex;align-items:flex-start;gap:8px;border-bottom:1px solid rgba(0,0,0,.04)}
@media(max-width:640px){.pl-compare{grid-template-columns:1fr}}

/* ── CTA ── */
.pl-cta-section{padding:80px 24px;background:linear-gradient(135deg,#1a1a2e,#4a2010);text-align:center;color:#fff}
.pl-cta-section h2{font-size:clamp(24px,4vw,36px);font-weight:800;margin-bottom:12px}
.pl-cta-section p{font-size:16px;color:rgba(255,255,255,.55);max-width:500px;margin:0 auto 28px}

/* ── FAQ ── */
.pl-faq{max-width:700px;margin:40px auto 0;display:flex;flex-direction:column;gap:10px}
.pl-faq details{background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;transition:border-color .2s}
.pl-faq details[open]{border-color:#ea580c}
.pl-faq summary{padding:16px 20px;font-size:14px;font-weight:600;cursor:pointer;list-style:none;display:flex;justify-content:space-between;align-items:center}
.pl-faq summary::after{content:'+';font-size:18px;color:#94a3b8;transition:transform .2s}
.pl-faq details[open] summary::after{content:'−';color:#ea580c}
.pl-faq summary::-webkit-details-marker{display:none}
.pl-faq details p{padding:0 20px 16px;font-size:13px;color:#64748b;line-height:1.7}
</style>
</head>
<body>
<?php require __DIR__ . '/../_partials/navbar-publica.php'; ?>

<!-- HERO -->
<section class="pl-hero">
  <div class="glow"></div>
  <div class="pl-hero-inner">
    <div>
      <div class="pl-hero-badge"><span><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg> <?php echo I18n::t('sol_php.badge'); ?></span></div>
      <h1><?php echo I18n::t('sol_php.hero_title_pre'); ?> <em><?php echo I18n::t('sol_php.hero_title_em'); ?></em></h1>
      <p><?php echo I18n::t('sol_php.hero_desc'); ?></p>
      <div class="pl-hero-actions">
        <a href="#planos" class="pl-btn-p"><?php echo I18n::t('sol_php.btn_plans'); ?></a>
        <a href="/contato" class="pl-btn-s"><?php echo I18n::t('sol_php.btn_contact'); ?></a>
      </div>
    </div>
    <div class="pl-hero-visual">
      <div class="pl-mock-bar"><div class="pl-mock-dot"></div><div class="pl-mock-dot"></div><div class="pl-mock-dot"></div></div>
      <div class="pl-term-line"><span class="pl-term-prompt">$</span><span class="pl-term-cmd">composer install</span></div>
      <div class="pl-term-out">Installing dependencies from lock file...</div>
      <div class="pl-term-out">Package operations: 87 installs, 0 updates, 0 removals</div>
      <div class="pl-term-line"><span class="pl-term-prompt">$</span><span class="pl-term-cmd">php artisan migrate</span></div>
      <div class="pl-term-out">Migrating: 2024_01_01_create_users_table</div>
      <div class="pl-term-out">Migrated:  2024_01_01_create_users_table (45ms)</div>
      <div class="pl-term-success">✔ <?php echo I18n::t('sol_php.stat1_value'); ?></div>
    </div>
  </div>
</section>

<!-- STATS -->
<div class="pl-stats">
  <div class="pl-stats-inner">
    <div class="pl-stat"><h3>Composer</h3><p><?php echo I18n::t('sol_php.stat1_desc'); ?></p></div>
    <div class="pl-stat"><h3>99.9%</h3><p><?php echo I18n::t('sol_php.stat2_desc'); ?></p></div>
    <div class="pl-stat"><h3><?php echo I18n::t('sol_php.stat3_value'); ?></h3><p><?php echo I18n::t('sol_php.stat3_desc'); ?></p></div>
    <div class="pl-stat"><h3>SSL</h3><p><?php echo I18n::t('sol_php.stat4_desc'); ?></p></div>
  </div>
</div>

<!-- COMPARAÇÃO -->
<section class="pl-section">
  <div class="pl-inner">
    <div style="text-align:center;margin-bottom:8px;">
      <div class="pl-label"><?php echo I18n::t('sol_php.compare_label'); ?></div>
      <h2 class="pl-title"><?php echo I18n::t('sol_php.compare_title'); ?></h2>
      <p class="pl-sub" style="margin:0 auto;"><?php echo I18n::t('sol_php.compare_desc'); ?></p>
    </div>
    <div class="pl-compare">
      <div class="pl-compare-card bad">
        <h3><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_php.compare_bad_title'); ?></h3>
        <ul>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_php.compare_bad1'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_php.compare_bad2'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_php.compare_bad3'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_php.compare_bad4'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_php.compare_bad5'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_php.compare_bad6'); ?></li>
        </ul>
      </div>
      <div class="pl-compare-card good">
        <h3><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> PHP App <?php echo View::e($_nome); ?></h3>
        <ul>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_php.compare_good1'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_php.compare_good2'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_php.compare_good3'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_php.compare_good4'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_php.compare_good5'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_php.compare_good6'); ?></li>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section class="pl-section alt">
  <div class="pl-inner">
    <div style="text-align:center;">
      <div class="pl-label"><?php echo I18n::t('sol_php.feat_label'); ?></div>
      <h2 class="pl-title"><?php echo I18n::t('sol_php.feat_title'); ?></h2>
    </div>
    <div class="pl-features">
      <div class="pl-feat"><div class="pl-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="16.5" y1="9.4" x2="7.5" y2="4.21"/><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg></div><h3><?php echo I18n::t('sol_php.feat1_title'); ?></h3><p><?php echo I18n::t('sol_php.feat1_desc'); ?></p></div>
      <div class="pl-feat"><div class="pl-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg></div><h3><?php echo I18n::t('sol_php.feat2_title'); ?></h3><p><?php echo I18n::t('sol_php.feat2_desc'); ?></p></div>
      <div class="pl-feat"><div class="pl-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg></div><h3><?php echo I18n::t('sol_php.feat3_title'); ?></h3><p><?php echo I18n::t('sol_php.feat3_desc'); ?></p></div>
      <div class="pl-feat"><div class="pl-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg></div><h3><?php echo I18n::t('sol_php.feat4_title'); ?></h3><p><?php echo I18n::t('sol_php.feat4_desc'); ?></p></div>
      <div class="pl-feat"><div class="pl-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="18" r="3"/><circle cx="6" cy="6" r="3"/><circle cx="18" cy="6" r="3"/><path d="M6 9v6c0 2 2 3 6 3h3"/></svg></div><h3><?php echo I18n::t('sol_php.feat5_title'); ?></h3><p><?php echo I18n::t('sol_php.feat5_desc'); ?></p></div>
      <div class="pl-feat"><div class="pl-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg></div><h3><?php echo I18n::t('sol_php.feat6_title'); ?></h3><p><?php echo I18n::t('sol_php.feat6_desc'); ?></p></div>
      <div class="pl-feat"><div class="pl-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg></div><h3><?php echo I18n::t('sol_php.feat7_title'); ?></h3><p><?php echo I18n::t('sol_php.feat7_desc'); ?></p></div>
      <div class="pl-feat"><div class="pl-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg></div><h3><?php echo I18n::t('sol_php.feat8_title'); ?></h3><p><?php echo I18n::t('sol_php.feat8_desc'); ?></p></div>
      <div class="pl-feat"><div class="pl-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg></div><h3><?php echo I18n::t('sol_php.feat9_title'); ?></h3><p><?php echo I18n::t('sol_php.feat9_desc'); ?></p></div>
    </div>
  </div>
</section>

<!-- COMO FUNCIONA -->
<section class="pl-section dark">
  <div class="pl-inner">
    <div style="text-align:center;margin-bottom:8px;">
      <div class="pl-label" style="color:#fed7aa;"><?php echo I18n::t('sol_php.steps_label'); ?></div>
      <h2 class="pl-title" style="color:#fff;"><?php echo I18n::t('sol_php.steps_title'); ?></h2>
    </div>
    <div class="pl-steps">
      <div class="pl-step"><div class="pl-step-num">1</div><h3 style="color:#fff;"><?php echo I18n::t('sol_php.step1_title'); ?></h3><p><?php echo I18n::t('sol_php.step1_desc'); ?></p></div>
      <div class="pl-step-arrow">→</div>
      <div class="pl-step"><div class="pl-step-num">2</div><h3 style="color:#fff;"><?php echo I18n::t('sol_php.step2_title'); ?></h3><p><?php echo I18n::t('sol_php.step2_desc'); ?></p></div>
      <div class="pl-step-arrow">→</div>
      <div class="pl-step"><div class="pl-step-num">3</div><h3 style="color:#fff;"><?php echo I18n::t('sol_php.step3_title'); ?></h3><p><?php echo I18n::t('sol_php.step3_desc'); ?></p></div>
    </div>
  </div>
</section>

<!-- PLANOS -->
<?php $_accent = '#ea580c'; $_plan_type = 'php'; $_cta_base = '/cliente/planos/checkout?plan_id='; require __DIR__ . '/_planos-section.php'; ?>

<!-- FAQ -->
<section class="pl-section">
  <div class="pl-inner">
    <div style="text-align:center;">
      <div class="pl-label"><?php echo I18n::t('sol_php.faq_label'); ?></div>
      <h2 class="pl-title"><?php echo I18n::t('sol_php.faq_title'); ?></h2>
    </div>
    <div class="pl-faq">
      <details><summary><?php echo I18n::t('sol_php.faq1_q'); ?></summary><p><?php echo I18n::t('sol_php.faq1_a'); ?></p></details>
      <details><summary><?php echo I18n::t('sol_php.faq2_q'); ?></summary><p><?php echo I18n::t('sol_php.faq2_a'); ?></p></details>
      <details><summary><?php echo I18n::t('sol_php.faq3_q'); ?></summary><p><?php echo I18n::t('sol_php.faq3_a'); ?></p></details>
      <details><summary><?php echo I18n::t('sol_php.faq4_q'); ?></summary><p><?php echo I18n::t('sol_php.faq4_a'); ?></p></details>
      <details><summary><?php echo I18n::t('sol_php.faq5_q'); ?></summary><p><?php echo I18n::t('sol_php.faq5_a'); ?></p></details>
      <details><summary><?php echo I18n::t('sol_php.faq6_q'); ?></summary><p><?php echo I18n::t('sol_php.faq6_a'); ?></p></details>
    </div>
  </div>
</section>

<!-- CTA FINAL -->
<section class="pl-cta-section">
  <h2><?php echo I18n::t('sol_php.cta_title'); ?></h2>
  <p><?php echo I18n::t('sol_php.cta_desc'); ?></p>
  <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
    <a href="#planos" class="pl-btn-p"><?php echo I18n::t('sol_php.btn_plans'); ?></a>
    <a href="/contato" class="pl-btn-s"><?php echo I18n::t('sol_php.btn_contact'); ?></a>
  </div>
</section>

<?php require __DIR__ . '/../_partials/footer.php'; ?>
<?php require __DIR__ . '/../_partials/chat-widget.php'; ?>
</body>
</html>