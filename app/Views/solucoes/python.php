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
<title><?php echo I18n::t('sol_py.page_title'); ?> — <?php echo View::e($_nome); ?></title>
<?php require __DIR__ . '/../_partials/seo.php'; ?>
<?php require __DIR__ . '/../_partials/estilo.php'; ?>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:system-ui,-apple-system,'Segoe UI',Roboto,sans-serif;background:#fff;color:#0f172a}

/* ── Hero Python ── */
.py-hero{background:linear-gradient(135deg,#1a1a2e 0%,#0e2a30 30%,#0c3547 60%,#0891b2 85%,#06b6d4 100%);position:relative;overflow:hidden;padding:100px 0 80px}
.py-hero::before{content:'';position:absolute;inset:0;background-image:radial-gradient(circle,rgba(255,255,255,.08) 1px,transparent 1px);background-size:40px 40px;pointer-events:none}
.py-hero .glow{position:absolute;width:600px;height:600px;background:radial-gradient(circle,rgba(6,182,212,.35),transparent 65%);top:-200px;right:-100px;pointer-events:none}
.py-hero-inner{position:relative;z-index:1;max-width:1100px;margin:0 auto;padding:0 24px;display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:center}
.py-hero-badge{display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.15);padding:5px 14px;border-radius:99px;margin-bottom:20px}
.py-hero-badge span{font-size:.72rem;font-weight:600;color:rgba(255,255,255,.8);letter-spacing:.06em;text-transform:uppercase}
.py-hero h1{font-size:clamp(1.8rem,4.5vw,3.2rem);font-weight:800;color:#fff;line-height:1.12;margin-bottom:18px;letter-spacing:-.02em}
.py-hero h1 em{font-style:italic;color:#a5f3fc}
.py-hero p{font-size:1rem;color:rgba(255,255,255,.6);line-height:1.8;margin-bottom:28px;max-width:480px}
.py-hero-actions{display:flex;gap:12px;flex-wrap:wrap}
.py-btn-p{display:inline-flex;align-items:center;gap:8px;padding:14px 28px;border-radius:12px;font-size:.9rem;font-weight:700;background:#fff;color:#0891b2;transition:transform .15s;text-decoration:none}
.py-btn-p:hover{transform:translateY(-2px)}
.py-btn-s{display:inline-flex;align-items:center;gap:8px;padding:14px 28px;border-radius:12px;font-size:.9rem;font-weight:700;background:rgba(255,255,255,.1);color:#fff;border:1.5px solid rgba(255,255,255,.2);text-decoration:none;transition:background .15s}
.py-btn-s:hover{background:rgba(255,255,255,.18)}
.py-hero-visual{background:rgba(255,255,255,.06);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,.12);border-radius:20px;padding:28px;color:#fff;font-family:'Courier New',Courier,monospace;font-size:.85rem;line-height:1.7}
.py-mock-bar{display:flex;gap:6px;margin-bottom:16px}.py-mock-dot{width:10px;height:10px;border-radius:50%;background:rgba(255,255,255,.15)}.py-mock-dot:first-child{background:#ef4444}.py-mock-dot:nth-child(2){background:#f59e0b}.py-mock-dot:nth-child(3){background:#22c55e}
.py-term-line{display:flex;gap:8px;margin-bottom:6px}
.py-term-prompt{color:#06b6d4;font-weight:700;white-space:nowrap}
.py-term-cmd{color:rgba(255,255,255,.85)}
.py-term-out{color:rgba(255,255,255,.4);font-size:.8rem;margin-bottom:8px;padding-left:18px}
.py-term-success{color:#22c55e;font-weight:700;margin-top:8px;display:flex;align-items:center;gap:6px}
@media(max-width:860px){.py-hero-inner{grid-template-columns:1fr;text-align:center}.py-hero p{margin:0 auto 28px}.py-hero-actions{justify-content:center}.py-hero-visual{display:none}}

/* ── Stats ── */
.py-stats{background:#0f172a;padding:36px 0}
.py-stats-inner{max-width:1100px;margin:0 auto;display:grid;grid-template-columns:repeat(4,1fr);text-align:center;gap:0}
.py-stat{padding:24px 16px;border-right:1px solid rgba(255,255,255,.06)}.py-stat:last-child{border:none}
.py-stat h3{font-size:2rem;font-weight:900;color:#a5f3fc;margin-bottom:4px}.py-stat p{font-size:.8rem;color:rgba(255,255,255,.4)}
@media(max-width:640px){.py-stats-inner{grid-template-columns:1fr 1fr}.py-stat:nth-child(2){border-right:none}}

/* ── Sections ── */
.py-section{padding:80px 24px}.py-section.alt{background:#f8fafc}.py-section.dark{background:#0f172a;color:#fff}
.py-inner{max-width:1100px;margin:0 auto}
.py-label{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:#0891b2;margin-bottom:10px}
.py-title{font-size:clamp(22px,3.5vw,34px);font-weight:800;color:#0f172a;margin-bottom:10px;letter-spacing:-.02em;line-height:1.15}
.py-sub{font-size:15px;color:#64748b;line-height:1.75;max-width:560px}

/* ── Features Grid ── */
.py-features{display:grid;grid-template-columns:repeat(3,1fr);gap:2px;background:#e2e8f0;border:1px solid #e2e8f0;border-radius:18px;overflow:hidden;margin-top:40px}
.py-feat{background:#fff;padding:32px 24px;transition:background .2s}
.py-feat:hover{background:#ecfeff}
.py-feat-icon{width:48px;height:48px;background:#ecfeff;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:24px;margin-bottom:16px}
.py-feat h3{font-size:.95rem;font-weight:700;margin-bottom:6px}.py-feat p{font-size:.85rem;color:#64748b;line-height:1.6}
@media(max-width:860px){.py-features{grid-template-columns:1fr 1fr}}
@media(max-width:560px){.py-features{grid-template-columns:1fr}}

/* ── How it works ── */
.py-steps{display:flex;align-items:flex-start;justify-content:center;gap:16px;margin-top:40px}
.py-step{text-align:center;flex:1;max-width:260px}
.py-step-num{width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,#0891b2,#06b6d4);color:#fff;font-size:22px;font-weight:800;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;box-shadow:0 4px 20px rgba(8,145,178,.3)}
.py-step h3{font-size:15px;font-weight:700;margin-bottom:6px}.py-step p{font-size:13px;color:rgba(255,255,255,.5)}
.py-step-arrow{color:rgba(255,255,255,.15);font-size:28px;padding-top:14px;flex-shrink:0}
@media(max-width:768px){.py-steps{flex-direction:column;align-items:center}.py-step-arrow{transform:rotate(90deg);padding:0}}

/* ── Comparison ── */
.py-compare{display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-top:40px}
.py-compare-card{border-radius:16px;padding:28px 24px}
.py-compare-card.bad{background:#fef2f2;border:1px solid #fecaca}
.py-compare-card.good{background:#ecfeff;border:2px solid #0891b2}
.py-compare-card h3{font-size:16px;font-weight:700;margin-bottom:14px;display:flex;align-items:center;gap:8px}
.py-compare-card ul{list-style:none;padding:0}
.py-compare-card ul li{padding:8px 0;font-size:13px;display:flex;align-items:flex-start;gap:8px;border-bottom:1px solid rgba(0,0,0,.04)}
@media(max-width:640px){.py-compare{grid-template-columns:1fr}}

/* ── CTA ── */
.py-cta-section{padding:80px 24px;background:linear-gradient(135deg,#1a1a2e,#0c3547);text-align:center;color:#fff}
.py-cta-section h2{font-size:clamp(24px,4vw,36px);font-weight:800;margin-bottom:12px}
.py-cta-section p{font-size:16px;color:rgba(255,255,255,.55);max-width:500px;margin:0 auto 28px}

/* ── FAQ ── */
.py-faq{max-width:700px;margin:40px auto 0;display:flex;flex-direction:column;gap:10px}
.py-faq details{background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;transition:border-color .2s}
.py-faq details[open]{border-color:#0891b2}
.py-faq summary{padding:16px 20px;font-size:14px;font-weight:600;cursor:pointer;list-style:none;display:flex;justify-content:space-between;align-items:center}
.py-faq summary::after{content:'+';font-size:18px;color:#94a3b8;transition:transform .2s}
.py-faq details[open] summary::after{content:'−';color:#0891b2}
.py-faq summary::-webkit-details-marker{display:none}
.py-faq details p{padding:0 20px 16px;font-size:13px;color:#64748b;line-height:1.7}
</style>
</head>
<body>
<?php require __DIR__ . '/../_partials/navbar-publica.php'; ?>

<!-- HERO -->
<section class="py-hero">
  <div class="glow"></div>
  <div class="py-hero-inner">
    <div>
      <div class="py-hero-badge"><span><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/><line x1="12" y1="2" x2="12" y2="22"/></svg> <?php echo I18n::t('sol_py.badge'); ?></span></div>
      <h1><?php echo I18n::t('sol_py.hero_title_pre'); ?> <em><?php echo I18n::t('sol_py.hero_title_em'); ?></em></h1>
      <p><?php echo I18n::t('sol_py.hero_desc'); ?></p>
      <div class="py-hero-actions">
        <a href="#planos" class="py-btn-p"><?php echo I18n::t('sol_py.btn_plans'); ?></a>
        <a href="/contato" class="py-btn-s"><?php echo I18n::t('sol_py.btn_contact'); ?></a>
      </div>
    </div>
    <div class="py-hero-visual">
      <div class="py-mock-bar"><div class="py-mock-dot"></div><div class="py-mock-dot"></div><div class="py-mock-dot"></div></div>
      <div class="py-term-line"><span class="py-term-prompt">$</span><span class="py-term-cmd">pip install -r requirements.txt</span></div>
      <div class="py-term-out">Installing collected packages: django, gunicorn, psycopg2...</div>
      <div class="py-term-out">Successfully installed 24 packages</div>
      <div class="py-term-line"><span class="py-term-prompt">$</span><span class="py-term-cmd">python manage.py migrate</span></div>
      <div class="py-term-out">Running migrations... OK</div>
      <div class="py-term-line"><span class="py-term-prompt">$</span><span class="py-term-cmd">gunicorn app:application --bind 0.0.0.0:8000</span></div>
      <div class="py-term-out">[INFO] Listening at: http://0.0.0.0:8000</div>
      <div class="py-term-success">✔ Gunicorn running</div>
    </div>
  </div>
</section>

<!-- STATS -->
<div class="py-stats">
  <div class="py-stats-inner">
    <div class="py-stat"><h3><?php echo I18n::t('sol_py.stat1_value'); ?></h3><p><?php echo I18n::t('sol_py.stat1_desc'); ?></p></div>
    <div class="py-stat"><h3>99.9%</h3><p><?php echo I18n::t('sol_py.stat2_desc'); ?></p></div>
    <div class="py-stat"><h3><?php echo I18n::t('sol_py.stat3_value'); ?></h3><p><?php echo I18n::t('sol_py.stat3_desc'); ?></p></div>
    <div class="py-stat"><h3>SSL</h3><p><?php echo I18n::t('sol_py.stat4_desc'); ?></p></div>
  </div>
</div>

<!-- COMPARAÇÃO -->
<section class="py-section">
  <div class="py-inner">
    <div style="text-align:center;margin-bottom:8px;">
      <div class="py-label"><?php echo I18n::t('sol_py.compare_label'); ?></div>
      <h2 class="py-title"><?php echo I18n::t('sol_py.compare_title'); ?></h2>
      <p class="py-sub" style="margin:0 auto;"><?php echo I18n::t('sol_py.compare_desc'); ?></p>
    </div>
    <div class="py-compare">
      <div class="py-compare-card bad">
        <h3><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_py.compare_bad_title'); ?></h3>
        <ul>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_py.compare_bad1'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_py.compare_bad2'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_py.compare_bad3'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_py.compare_bad4'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_py.compare_bad5'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#dc2626;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <?php echo I18n::t('sol_py.compare_bad6'); ?></li>
        </ul>
      </div>
      <div class="py-compare-card good">
        <h3><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> Python App <?php echo View::e($_nome); ?></h3>
        <ul>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_py.compare_good1'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_py.compare_good2'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_py.compare_good3'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_py.compare_good4'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_py.compare_good5'); ?></li>
          <li><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> <?php echo I18n::t('sol_py.compare_good6'); ?></li>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section class="py-section alt">
  <div class="py-inner">
    <div style="text-align:center;">
      <div class="py-label"><?php echo I18n::t('sol_py.feat_label'); ?></div>
      <h2 class="py-title"><?php echo I18n::t('sol_py.feat_title'); ?></h2>
    </div>
    <div class="py-features">
      <div class="py-feat"><div class="py-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="18" r="3"/><circle cx="6" cy="6" r="3"/><circle cx="18" cy="6" r="3"/><path d="M6 9v6c0 2 2 3 6 3h3"/></svg></div><h3><?php echo I18n::t('sol_py.feat1_title'); ?></h3><p><?php echo I18n::t('sol_py.feat1_desc'); ?></p></div>
      <div class="py-feat"><div class="py-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="16.5" y1="9.4" x2="7.5" y2="4.21"/><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg></div><h3><?php echo I18n::t('sol_py.feat2_title'); ?></h3><p><?php echo I18n::t('sol_py.feat2_desc'); ?></p></div>
      <div class="py-feat"><div class="py-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg></div><h3><?php echo I18n::t('sol_py.feat3_title'); ?></h3><p><?php echo I18n::t('sol_py.feat3_desc'); ?></p></div>
      <div class="py-feat"><div class="py-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg></div><h3><?php echo I18n::t('sol_py.feat4_title'); ?></h3><p><?php echo I18n::t('sol_py.feat4_desc'); ?></p></div>
      <div class="py-feat"><div class="py-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><rect x="5" y="10" width="4" height="4"/><rect x="11" y="10" width="4" height="4"/><line x1="2" y1="4" x2="7" y2="4"/></svg></div><h3><?php echo I18n::t('sol_py.feat5_title'); ?></h3><p><?php echo I18n::t('sol_py.feat5_desc'); ?></p></div>
      <div class="py-feat"><div class="py-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg></div><h3><?php echo I18n::t('sol_py.feat6_title'); ?></h3><p><?php echo I18n::t('sol_py.feat6_desc'); ?></p></div>
      <div class="py-feat"><div class="py-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg></div><h3><?php echo I18n::t('sol_py.feat7_title'); ?></h3><p><?php echo I18n::t('sol_py.feat7_desc'); ?></p></div>
      <div class="py-feat"><div class="py-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div><h3><?php echo I18n::t('sol_py.feat8_title'); ?></h3><p><?php echo I18n::t('sol_py.feat8_desc'); ?></p></div>
      <div class="py-feat"><div class="py-feat-icon"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg></div><h3><?php echo I18n::t('sol_py.feat9_title'); ?></h3><p><?php echo I18n::t('sol_py.feat9_desc'); ?></p></div>
    </div>
  </div>
</section>

<!-- COMO FUNCIONA -->
<section class="py-section dark">
  <div class="py-inner">
    <div style="text-align:center;margin-bottom:8px;">
      <div class="py-label" style="color:#a5f3fc;"><?php echo I18n::t('sol_py.steps_label'); ?></div>
      <h2 class="py-title" style="color:#fff;"><?php echo I18n::t('sol_py.steps_title'); ?></h2>
    </div>
    <div class="py-steps">
      <div class="py-step"><div class="py-step-num">1</div><h3 style="color:#fff;"><?php echo I18n::t('sol_py.step1_title'); ?></h3><p><?php echo I18n::t('sol_py.step1_desc'); ?></p></div>
      <div class="py-step-arrow">→</div>
      <div class="py-step"><div class="py-step-num">2</div><h3 style="color:#fff;"><?php echo I18n::t('sol_py.step2_title'); ?></h3><p><?php echo I18n::t('sol_py.step2_desc'); ?></p></div>
      <div class="py-step-arrow">→</div>
      <div class="py-step"><div class="py-step-num">3</div><h3 style="color:#fff;"><?php echo I18n::t('sol_py.step3_title'); ?></h3><p><?php echo I18n::t('sol_py.step3_desc'); ?></p></div>
    </div>
  </div>
</section>

<!-- PLANOS -->
<?php $_accent = '#0891b2'; $_plan_type = 'python'; $_cta_base = '/cliente/planos/checkout?plan_id='; require __DIR__ . '/_planos-section.php'; ?>

<!-- FAQ -->
<section class="py-section">
  <div class="py-inner">
    <div style="text-align:center;">
      <div class="py-label"><?php echo I18n::t('sol_py.faq_label'); ?></div>
      <h2 class="py-title"><?php echo I18n::t('sol_py.faq_title'); ?></h2>
    </div>
    <div class="py-faq">
      <details><summary><?php echo I18n::t('sol_py.faq1_q'); ?></summary><p><?php echo I18n::t('sol_py.faq1_a'); ?></p></details>
      <details><summary><?php echo I18n::t('sol_py.faq2_q'); ?></summary><p><?php echo I18n::t('sol_py.faq2_a'); ?></p></details>
      <details><summary><?php echo I18n::t('sol_py.faq3_q'); ?></summary><p><?php echo I18n::t('sol_py.faq3_a'); ?></p></details>
      <details><summary><?php echo I18n::t('sol_py.faq4_q'); ?></summary><p><?php echo I18n::t('sol_py.faq4_a'); ?></p></details>
      <details><summary><?php echo I18n::t('sol_py.faq5_q'); ?></summary><p><?php echo I18n::t('sol_py.faq5_a'); ?></p></details>
      <details><summary><?php echo I18n::t('sol_py.faq6_q'); ?></summary><p><?php echo I18n::t('sol_py.faq6_a'); ?></p></details>
    </div>
  </div>
</section>

<!-- CTA FINAL -->
<section class="py-cta-section">
  <h2><?php echo I18n::t('sol_py.cta_title'); ?></h2>
  <p><?php echo I18n::t('sol_py.cta_desc'); ?></p>
  <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
    <a href="#planos" class="py-btn-p"><?php echo I18n::t('sol_py.btn_plans'); ?></a>
    <a href="/contato" class="py-btn-s"><?php echo I18n::t('sol_py.btn_contact'); ?></a>
  </div>
</section>

<?php require __DIR__ . '/../_partials/footer.php'; ?>
</body>
</html>