<?php
declare(strict_types=1);
use LRV\Core\I18n;
use LRV\Core\View;
use LRV\Core\SistemaConfig;

$_nome   = SistemaConfig::nome();
$_logo   = SistemaConfig::logoUrl();
$_planos = is_array($planos ?? null) ? $planos : [];

$_hero_plano = !empty($_planos) ? $_planos[count($_planos) - 1] : null;
if ($_hero_plano) {
    $_hs     = json_decode((string)($_hero_plano['specs_json'] ?? ''), true) ?: [];
    $_hram   = (int)($_hs['ram_gb'] ?? 0);
    $_hvcpu  = (int)($_hs['vcpu'] ?? $_hs['cpu'] ?? 0);
    $_hdisco = (int)($_hs['disco_gb'] ?? $_hs['storage_gb'] ?? 0);
    $_hprice = (float)$_hero_plano['price'];
}
?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<?php require __DIR__ . '/_partials/seo.php'; ?>
<?php require __DIR__ . '/_partials/estilo.php'; ?>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth;overflow-x:hidden}
body{font-family:system-ui,-apple-system,'Segoe UI',Roboto,Ubuntu,sans-serif;background:#fff;color:#0f172a;max-width:100vw}
a{text-decoration:none;color:inherit}

/* ── Navbar ── */
.navbar{position:sticky;top:0;z-index:100;background:rgba(11,28,61,.92);backdrop-filter:blur(16px) saturate(180%);-webkit-backdrop-filter:blur(16px) saturate(180%);border-bottom:1px solid rgba(255,255,255,.08);padding:0 16px}
.navbar-inner{max-width:1160px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;height:60px;gap:8px}
.navbar-brand{display:flex;align-items:center;gap:8px;text-decoration:none;color:#fff;flex-shrink:0;min-width:0}
.navbar-brand img{height:28px;width:auto;flex-shrink:0}
.navbar-brand-name{font-size:15px;font-weight:700;letter-spacing:-.01em;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.navbar-links{display:flex;align-items:center;gap:2px;flex:1;justify-content:center}
.navbar-links a{color:rgba(255,255,255,.75);text-decoration:none;font-size:13.5px;font-weight:500;padding:6px 12px;border-radius:8px;transition:color .15s,background .15s}
.navbar-links a:hover{color:#fff;background:rgba(255,255,255,.1)}
.navbar-actions{display:flex;align-items:center;gap:8px;flex-shrink:0}
.navbar-btn{display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:10px;font-size:13px;font-weight:600;text-decoration:none;transition:opacity .15s,transform .1s;white-space:nowrap}
.navbar-btn:hover{opacity:.88;transform:translateY(-1px)}
.navbar-btn.ghost{color:rgba(255,255,255,.85);border:1.5px solid rgba(255,255,255,.2)}
.navbar-btn.solid{background:linear-gradient(135deg,#4F46E5,#7C3AED);color:#fff}
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
.navbar-drawer .drawer-actions a{border-bottom:none;text-align:center;font-weight:700;padding:14px 16px;border-radius:10px}
.navbar-drawer .drawer-actions .ghost{border:1.5px solid rgba(255,255,255,.25);color:rgba(255,255,255,.85)}
.navbar-drawer .drawer-actions .solid{background:linear-gradient(135deg,#4F46E5,#7C3AED);color:#fff}
@media(max-width:768px){
  .navbar{padding:0 12px}
  .navbar-links{display:none}
  .navbar-btn.ghost{display:none}
  .navbar-btn.solid{display:none}
  .navbar-hamburger{display:flex}
  .navbar-actions .lang-dropdown{display:none}
}

/* ── Hero split ── */
.hero{background:linear-gradient(135deg,#060d1f 0%,#0B1C3D 30%,#1e3a8a 60%,#4F46E5 85%,#7C3AED 100%);position:relative;overflow:hidden;padding:80px 0}
.hero__particles{position:absolute;inset:0;pointer-events:none;background-image:radial-gradient(circle,rgba(255,255,255,.15) 1px,transparent 1px),radial-gradient(circle,rgba(255,255,255,.08) 1px,transparent 1px);background-size:48px 48px,96px 96px;background-position:0 0,24px 24px}
.hero__glow{position:absolute;width:min(700px,100vw);height:min(700px,100vw);background:radial-gradient(circle,rgba(124,58,237,.3) 0%,transparent 65%);top:-200px;right:-100px;pointer-events:none}
.hero__inner{position:relative;z-index:1;display:grid;grid-template-columns:1fr 1fr;gap:64px;align-items:center;max-width:1160px;margin:0 auto;padding:0 28px 80px}
.hero__badge{display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.18);padding:5px 14px;border-radius:99px;margin-bottom:24px}
.hero__badge-dot{width:7px;height:7px;border-radius:50%;background:#4ADE80;box-shadow:0 0 0 3px rgba(74,222,128,.25);animation:pulse 2s ease infinite}
@keyframes pulse{0%,100%{box-shadow:0 0 0 3px rgba(74,222,128,.25)}50%{box-shadow:0 0 0 6px rgba(74,222,128,.1)}}
.hero__badge span{font-size:.73rem;font-weight:600;color:rgba(255,255,255,.8);letter-spacing:.06em;text-transform:uppercase}
.hero__title{font-size:clamp(1.7rem,5vw,3.8rem);font-weight:400;line-height:1.1;letter-spacing:-.02em;color:#fff;margin-bottom:22px}
.hero__title em{font-style:italic;color:#c4b5fd}
.hero__subtitle{font-size:1rem;font-weight:300;color:rgba(255,255,255,.62);line-height:1.8;margin-bottom:36px;max-width:480px}
.hero__actions{display:flex;gap:12px;flex-wrap:wrap}
.hero-btn-p{display:inline-flex;align-items:center;gap:8px;padding:14px 26px;border-radius:12px;font-size:.9rem;font-weight:700;background:#fff;color:#4F46E5;box-shadow:0 4px 20px rgba(255,255,255,.2);transition:transform .15s,box-shadow .15s}
.hero-btn-p:hover{transform:translateY(-2px);box-shadow:0 8px 32px rgba(255,255,255,.3)}
.hero-btn-s{display:inline-flex;align-items:center;gap:8px;padding:14px 26px;border-radius:12px;font-size:.9rem;font-weight:700;background:rgba(255,255,255,.1);color:#fff;border:1.5px solid rgba(255,255,255,.25);backdrop-filter:blur(8px);transition:background .15s}
.hero-btn-s:hover{background:rgba(255,255,255,.18)}
.hero__visual{position:relative}
.hero__server-card{background:rgba(255,255,255,.06);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,.14);border-radius:18px;padding:28px;color:#fff}
.server-card__header{display:flex;align-items:center;gap:12px;margin-bottom:22px}
.server-card__icon{width:42px;height:42px;border-radius:10px;background:rgba(255,255,255,.12);display:flex;align-items:center;justify-content:center}
.server-card__label{font-size:.7rem;color:rgba(255,255,255,.5);margin-bottom:2px;letter-spacing:.06em;text-transform:uppercase}
.server-card__name{font-weight:700;font-size:.95rem}
.server-specs{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:20px}
.spec-item{background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.1);border-radius:10px;padding:12px 14px}
.spec-item__val{font-size:1.05rem;font-weight:800;color:#fff;line-height:1;margin-bottom:4px}
.spec-item__key{font-size:.7rem;color:rgba(255,255,255,.45);text-transform:uppercase;letter-spacing:.05em}
.server-price{display:flex;align-items:center;justify-content:space-between;padding-top:18px;border-top:1px solid rgba(255,255,255,.12)}
.server-price__label{font-size:.8rem;color:rgba(255,255,255,.5)}
.server-price__value{font-size:2rem;color:#fff}
.server-price__value span{font-size:.8rem;color:rgba(255,255,255,.45)}
.hero__floater{position:absolute;background:#fff;border-radius:10px;padding:10px 16px;box-shadow:0 20px 60px rgba(11,28,61,.3);display:flex;align-items:center;gap:10px;animation:float 3s ease-in-out infinite}
.hero__floater--1{bottom:-20px;left:-24px;animation-delay:0s}
.hero__floater--2{top:20px;right:-20px;animation-delay:1.5s}
@keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-8px)}}
.floater-icon{width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center}
.floater-icon--green{background:#DCFCE7}
.floater-icon--blue{background:#eef2ff}
.floater__text strong{display:block;font-size:.8rem;color:#0f172a;font-weight:700}
.floater__text span{font-size:.7rem;color:#475569}
.hero__clients{max-width:1160px;margin:0 auto;padding:32px 20px 40px;border-top:1px solid rgba(255,255,255,.1);position:relative;z-index:1}
.hero__clients p{font-size:.72rem;font-weight:600;letter-spacing:.12em;text-transform:uppercase;color:rgba(255,255,255,.35);text-align:center;margin-bottom:20px}
.clients-logos{display:flex;align-items:center;justify-content:center;gap:24px;flex-wrap:wrap}
.clients-logos span{font-size:.82rem;font-weight:700;color:rgba(255,255,255,.3);letter-spacing:.04em;transition:color .2s;cursor:default}
.clients-logos span:hover{color:rgba(255,255,255,.6)}
@media(max-width:900px){.hero__inner{grid-template-columns:1fr;padding:0 20px 60px}.hero__visual{display:none}}
@media(max-width:480px){.hero{padding:48px 0 0}.hero__title{font-size:clamp(1.5rem,7vw,1.9rem)}.hero__subtitle{font-size:.875rem}.hero__badge span{font-size:.62rem}.hero__actions{gap:8px}.hero-btn-p,.hero-btn-s{padding:11px 18px;font-size:.82rem}.hero__clients{padding:24px 16px 32px}.clients-logos{gap:16px}}

/* ── Stats ── */
.statsbar{background:#0f172a;padding:40px 0}
.stats{display:grid;grid-template-columns:repeat(4,1fr);text-align:center;max-width:1160px;margin:0 auto}
.stat{padding:36px 28px;border-right:1px solid rgba(255,255,255,.08);transition:background .18s}
.stat:last-child{border-right:none}
.stat:hover{background:rgba(255,255,255,.04)}
.stat h3{font-size:2.4rem;font-weight:900;color:#a5b4fc;line-height:1;margin-bottom:8px}
.stat p{font-size:.82rem;color:rgba(255,255,255,.5);margin:0}
@media(max-width:640px){.stats{grid-template-columns:1fr 1fr}.stat:nth-child(2){border-right:none}}

/* ── Sections ── */
.lp-section{padding:88px 24px}
.lp-section.alt{background:#f8fafc}
.lp-section.dark{background:#060d1f;color:#fff}
.lp-section-inner{max-width:1100px;margin:0 auto}
.lp-label{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:#7C3AED;margin-bottom:10px}
.lp-title{font-size:clamp(22px,3.5vw,34px);font-weight:800;color:#0f172a;margin-bottom:10px;letter-spacing:-.02em;line-height:1.15}
.lp-sub{font-size:15px;color:#64748b;line-height:1.75;max-width:520px}
.eyebrow{display:inline-block;font-size:.72rem;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:#7C3AED;margin-bottom:14px}
.lead{font-size:1.1rem;font-weight:300;color:#475569;line-height:1.75;max-width:600px}

/* ── Features ── */
.features-header{display:grid;grid-template-columns:1fr 1fr;gap:64px;align-items:center;margin-bottom:64px}
@media(max-width:860px){.features-header{grid-template-columns:1fr;gap:32px}}
.features-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:2px;background:#e2e8f0;border:1px solid #e2e8f0;border-radius:18px;overflow:hidden}
@media(max-width:860px){.features-grid{grid-template-columns:1fr 1fr}}
@media(max-width:560px){.features-grid{grid-template-columns:1fr}}
.feat-item{background:#fff;padding:32px 28px;transition:background .2s}
.feat-item:hover{background:#eef2ff}
.feat-item__icon{width:46px;height:46px;background:#eef2ff;border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:18px}
.feat-item__icon svg{width:24px;height:24px;stroke:#4F46E5;stroke-width:2;fill:none}
.feat-item__title{font-size:.95rem;font-weight:700;color:#0f172a;margin-bottom:8px}
.feat-item__desc{font-size:.85rem;color:#64748b;line-height:1.65}
</style>
</head>
<body>

<!-- NAVBAR (idêntico ao infraestrutura.php) -->
<nav class="navbar">
  <div class="navbar-inner">
    <a href="/" class="navbar-brand">
      <?php if ($_logo !== ''): ?>
        <img src="<?php echo View::e($_logo); ?>" alt="<?php echo View::e($_nome); ?>"/>
      <?php else: ?>
        <svg width="28" height="28" viewBox="0 0 28 28" fill="none"><rect width="28" height="28" rx="8" fill="#4F46E5"/><path d="M7 14h14M14 7v14" stroke="#fff" stroke-width="2.5" stroke-linecap="round"/></svg>
      <?php endif; ?>
      <span class="navbar-brand-name"><?php echo View::e($_nome); ?></span>
    </a>
    <div class="navbar-links">
      <a href="#sobre"><?php echo View::e(I18n::t('home.nav_sobre')); ?></a>
      <a href="#planos"><?php echo View::e(I18n::t('home.nav_planos')); ?></a>
      <a href="#condicoes"><?php echo View::e(I18n::t('home.nav_condicoes')); ?></a>
      <a href="/infraestrutura"><?php echo View::e(I18n::t('home.nav_devs')); ?></a>
    </div>
    <div class="navbar-actions">
      <?php require __DIR__ . '/_partials/idioma.php'; ?>
      <a href="/cliente/entrar" class="navbar-btn ghost"><?php echo View::e(I18n::t('home.nav_entrar')); ?></a>
      <a href="/cliente/criar-conta" class="navbar-btn solid"><?php echo View::e(I18n::t('home.nav_contratar')); ?></a>
      <button class="navbar-hamburger" id="navHamburger" aria-label="Menu" onclick="toggleDrawer()">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>
</nav>

<!-- DRAWER MOBILE -->
<div class="navbar-drawer" id="navDrawer">
  <a href="#sobre" onclick="closeDrawer()"><?php echo View::e(I18n::t('home.nav_sobre')); ?></a>
  <a href="#planos" onclick="closeDrawer()"><?php echo View::e(I18n::t('home.nav_planos')); ?></a>
  <a href="#condicoes" onclick="closeDrawer()"><?php echo View::e(I18n::t('home.nav_condicoes')); ?></a>
  <a href="/infraestrutura" onclick="closeDrawer()"><?php echo View::e(I18n::t('home.nav_devs')); ?></a>
  <div class="drawer-actions">
    <a href="/cliente/entrar" class="ghost navbar-btn" onclick="closeDrawer()"><?php echo View::e(I18n::t('home.nav_entrar')); ?></a>
    <a href="/cliente/criar-conta" class="solid navbar-btn" onclick="closeDrawer()"><?php echo View::e(I18n::t('home.nav_contratar')); ?></a>
  </div>
</div>
<!-- HERO -->
<?php
$_trial_ativo = !empty($trial_ativo);
$_trial_label = (string)($trial_label ?? 'Testar grátis');
$_trial_desc  = (string)($trial_desc ?? '');
$_trial_dias  = (int)($trial_dias ?? 7);
?>
<section class="hero">
  <div class="hero__particles"></div>
  <div class="hero__glow"></div>
  <div class="hero__inner">
    <div class="hero__text">
      <div class="hero__badge">
        <div class="hero__badge-dot"></div>
        <?php if ($_trial_ativo): ?>
          <span><?php echo View::e(I18n::tf('home.badge_trial', $_trial_dias)); ?></span>
        <?php else: ?>
          <span><?php echo View::e(I18n::t('home.badge_servidores')); ?></span>
        <?php endif; ?>
      </div>
      <h1 class="hero__title"><?php echo View::e(I18n::t('home.hero_titulo')); ?><br><em><?php echo View::e(I18n::t('home.hero_titulo_em')); ?></em></h1>
      <p class="hero__subtitle"><?php echo View::e(I18n::t('home.hero_subtitulo')); ?></p>
      <?php if ($_trial_ativo && $_trial_desc !== ''): ?>
        <p style="font-size:.85rem;color:#a5b4fc;margin-bottom:20px;margin-top:-8px;"><?php echo View::e($_trial_desc); ?></p>
      <?php endif; ?>
      <div class="hero__actions">
        <?php if ($_trial_ativo): ?>
          <a href="/cliente/criar-conta" class="hero-btn-p"><?php echo View::e($_trial_label); ?></a>
          <a href="#planos" class="hero-btn-s"><?php echo View::e(I18n::t('home.ver_planos')); ?></a>
        <?php else: ?>
          <a href="#planos" class="hero-btn-p"><?php echo View::e(I18n::t('home.ver_planos_precos')); ?></a>
          <a href="#sobre" class="hero-btn-s"><?php echo View::e(I18n::t('home.saiba_mais')); ?></a>
        <?php endif; ?>
      </div>
    </div>
    <div class="hero__visual">
      <div class="hero__server-card">
        <div class="server-card__header">
          <div class="server-card__icon">
            <svg viewBox="0 0 24 24" style="width:24px;height:24px;stroke:currentColor;stroke-width:2;fill:none"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
          </div>
          <div>
            <div class="server-card__label"><?php echo View::e(I18n::t('home.servidor_destaque')); ?></div>
            <div class="server-card__name"><?php echo $_hero_plano ? View::e((string)$_hero_plano['name']) : 'VPS Enterprise'; ?></div>
          </div>
        </div>
        <div class="server-specs">
          <div class="spec-item"><div class="spec-item__val"><?php echo ($_hero_plano && $_hram > 0) ? $_hram.' GB' : '32 GB'; ?></div><div class="spec-item__key">RAM</div></div>
          <div class="spec-item"><div class="spec-item__val"><?php echo ($_hero_plano && $_hvcpu > 0) ? $_hvcpu.' vCPU' : '16 vCPU'; ?></div><div class="spec-item__key"><?php echo View::e(I18n::t('home.processamento')); ?></div></div>
          <div class="spec-item"><div class="spec-item__val"><?php echo ($_hero_plano && $_hdisco > 0) ? $_hdisco.' GB' : '300 GB'; ?></div><div class="spec-item__key">SSD</div></div>
          <div class="spec-item"><div class="spec-item__val">Infra</div><div class="spec-item__key"><?php echo View::e(I18n::t('home.infra_dedicada')); ?></div></div>
        </div>
        <div class="server-price">
          <div class="server-price__label"><?php echo View::e(I18n::t('home.total_mensal')); ?></div>
          <div class="server-price__value"><?php echo View::e(I18n::moeda()); ?> <?php echo $_hero_plano ? View::e(I18n::numero(I18n::precoValor($_hprice), 0)) : View::e(I18n::numero(I18n::precoValor(1497), 0)); ?>+<span><?php echo View::e(I18n::t('home.hero_preco_mes')); ?></span></div>
        </div>
      </div>
      <div class="hero__floater hero__floater--1">
        <div class="floater-icon floater-icon--green">
          <svg viewBox="0 0 24 24" style="width:18px;height:18px;stroke:#15803D;stroke-width:2;fill:none"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        </div>
        <div class="floater__text"><strong><?php echo View::e(I18n::t('home.ddos_protection')); ?></strong><span><?php echo View::e(I18n::t('home.ddos_ativada')); ?></span></div>
      </div>
      <div class="hero__floater hero__floater--2">
        <div class="floater-icon floater-icon--blue">
          <svg viewBox="0 0 24 24" style="width:18px;height:18px;stroke:#4F46E5;stroke-width:2;fill:none"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
        </div>
        <div class="floater__text"><strong>99,9% Uptime</strong><span><?php echo View::e(I18n::t('home.uptime_sla')); ?></span></div>
      </div>
    </div>
  </div>
  <div class="hero__clients">
    <p><?php echo View::e(I18n::tf('home.empresas_confiam', $_nome)); ?></p>
    <div class="clients-logos">
      <?php foreach (explode(',', I18n::t('home.clients_logos')) as $_cl): ?>
      <span><?php echo View::e(trim($_cl)); ?></span>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- STATS -->
<div class="statsbar">
  <div class="stats">
    <div class="stat"><h3><?php echo View::e(I18n::t('home.stats_num_exp')); ?></h3><p><?php echo View::e(I18n::t('home.stats_experiencia')); ?></p></div>
    <div class="stat"><h3><?php echo View::e(I18n::t('home.stats_num_clientes')); ?></h3><p><?php echo View::e(I18n::t('home.stats_clientes')); ?></p></div>
    <div class="stat"><h3><?php echo View::e(I18n::t('home.stats_num_sla')); ?></h3><p><?php echo View::e(I18n::t('home.stats_sla')); ?></p></div>
    <div class="stat"><h3><?php echo View::e(I18n::t('home.stats_num_ativacao')); ?></h3><p><?php echo View::e(I18n::t('home.stats_ativacao')); ?></p></div>
  </div>
</div>

<!-- SOBRE -->
<section class="lp-section" id="sobre">
  <div class="lp-section-inner">
    <div class="features-header">
      <div>
        <span class="eyebrow"><?php echo View::e(I18n::tf('home.porque', $_nome)); ?></span>
        <h2 class="lp-title" style="font-size:clamp(2rem,3.5vw,3rem);font-weight:600;margin-bottom:16px"><?php echo View::e(I18n::t('home.melhor_infra')); ?></h2>
        <p class="lead"><?php echo View::e(I18n::tf('home.sobre_desc', $_nome)); ?></p>
      </div>
      <div>
        <p class="lead" style="font-size:.95rem"><?php echo View::e(I18n::t('home.sobre_desc2')); ?></p>
        <p style="font-size:.88rem;color:#94a3b8;margin-top:14px;line-height:1.7"><?php echo View::e(I18n::t('home.sobre_desc3')); ?></p>
      </div>
    </div>
    <div class="features-grid">
      <div class="feat-item"><div class="feat-item__icon"><svg viewBox="0 0 24 24"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg></div><div class="feat-item__title"><?php echo View::e(I18n::t('home.feat_desempenho')); ?></div><p class="feat-item__desc"><?php echo View::e(I18n::t('home.feat_desempenho_desc')); ?></p></div>
      <div class="feat-item"><div class="feat-item__icon"><svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div><div class="feat-item__title"><?php echo View::e(I18n::t('home.feat_ddos')); ?></div><p class="feat-item__desc"><?php echo View::e(I18n::t('home.feat_ddos_desc')); ?></p></div>
      <div class="feat-item"><div class="feat-item__icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg></div><div class="feat-item__title"><?php echo View::e(I18n::t('home.feat_conectividade')); ?></div><p class="feat-item__desc"><?php echo View::e(I18n::t('home.feat_conectividade_desc')); ?></p></div>
      <div class="feat-item"><div class="feat-item__icon"><svg viewBox="0 0 24 24"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg></div><div class="feat-item__title"><?php echo View::e(I18n::t('home.feat_upgrades')); ?></div><p class="feat-item__desc"><?php echo View::e(I18n::t('home.feat_upgrades_desc')); ?></p></div>
      <div class="feat-item"><div class="feat-item__icon"><svg viewBox="0 0 24 24"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg></div><div class="feat-item__title"><?php echo View::e(I18n::t('home.feat_hardware')); ?></div><p class="feat-item__desc"><?php echo View::e(I18n::t('home.feat_hardware_desc')); ?></p></div>
      <div class="feat-item"><div class="feat-item__icon"><svg viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div><div class="feat-item__title"><?php echo View::e(I18n::t('home.feat_custos')); ?></div><p class="feat-item__desc"><?php echo View::e(I18n::t('home.feat_custos_desc')); ?></p></div>
    </div>
  </div>
</section>

<style>
/* ── Diferenciais ── */
.diff-sec{background:#fff;padding:80px 0;position:relative;overflow:hidden}
.diff-sec::before{content:'';position:absolute;bottom:0;left:0;right:0;height:300px;background:linear-gradient(135deg,#060d1f,#0B1C3D);z-index:0;clip-path:polygon(0 60%,100% 30%,100% 100%,0 100%)}
.diff-header{text-align:center;margin-bottom:60px;position:relative;z-index:1}
.diff-header h2{font-size:clamp(1.8rem,3vw,2.6rem);font-weight:700;color:#0f172a;margin-bottom:8px;letter-spacing:-.02em}
.diff-header::after{content:'';display:block;width:80px;height:3px;background:linear-gradient(135deg,#4F46E5,#7C3AED);margin:20px auto 0}
.diff-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px;position:relative;z-index:1;max-width:1100px;margin:0 auto;padding:0 28px}
@media(max-width:860px){.diff-grid{grid-template-columns:1fr 1fr}}
@media(max-width:560px){.diff-grid{grid-template-columns:1fr}}
.diff-card{background:#fff;border-radius:16px;padding:32px 24px;text-align:center;box-shadow:0 4px 20px rgba(0,0,0,.08);transition:all .3s cubic-bezier(.4,0,.2,1);position:relative;overflow:hidden}
.diff-card::before{content:'';position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(135deg,#4F46E5,#7C3AED);transform:scaleX(0);transform-origin:left;transition:transform .3s ease}
.diff-card:hover{transform:translateY(-8px);box-shadow:0 12px 40px rgba(79,70,229,.15)}
.diff-card:hover::before{transform:scaleX(1)}
.diff-icon{width:80px;height:80px;margin:0 auto 20px;background:linear-gradient(135deg,#eef2ff,#e0e7ff);border-radius:50%;display:flex;align-items:center;justify-content:center;transition:all .3s ease}
.diff-icon svg{width:40px;height:40px;stroke:#4F46E5;stroke-width:2;fill:none}
.diff-card:hover .diff-icon{transform:scale(1.1) rotate(5deg);background:linear-gradient(135deg,#4F46E5,#7C3AED)}
.diff-card:hover .diff-icon svg{stroke:#fff}
.diff-title{font-size:1.1rem;font-weight:700;color:#0f172a;margin-bottom:12px}
.diff-desc{font-size:.88rem;color:#64748b;line-height:1.7;margin:0}
.diff-card.animate{animation:fadeInUp .6s ease forwards;opacity:0}
@keyframes fadeInUp{from{opacity:0;transform:translateY(30px)}to{opacity:1;transform:translateY(0)}}
.diff-card:nth-child(1){animation-delay:.1s}.diff-card:nth-child(2){animation-delay:.2s}.diff-card:nth-child(3){animation-delay:.3s}
.diff-card:nth-child(4){animation-delay:.4s}.diff-card:nth-child(5){animation-delay:.5s}.diff-card:nth-child(6){animation-delay:.6s}

/* ── Plans ── */
.plans-sec{background:#f8fafc;padding:80px 0;overflow:hidden}
.compare-btn-wrap{text-align:center;margin-bottom:32px}
.compare-btn{display:inline-flex;align-items:center;gap:8px;padding:12px 28px;background:#fff;border:2px solid #4F46E5;color:#4F46E5;border-radius:10px;font-weight:700;font-size:14px;cursor:pointer;transition:all .3s;text-transform:uppercase;letter-spacing:.05em}
.compare-btn:hover{background:#4F46E5;color:#fff;transform:translateY(-2px);box-shadow:0 6px 20px rgba(79,70,229,.2)}
.compare-btn svg{width:18px;height:18px;stroke:currentColor;stroke-width:2;fill:none}
.carousel-container{position:relative;max-width:1200px;margin:0 auto;padding:0 80px}
@media(max-width:640px){.carousel-container{padding:0 20px}}
.carousel-wrapper{overflow:hidden;width:100%;padding-top:20px;margin-top:-20px}
.carousel-track{display:flex;transition:transform .5s cubic-bezier(.4,0,.2,1);gap:24px}
.plan-card{background:#fff;border:2px solid #e2e8f0;border-radius:16px;padding:36px 28px;flex:0 0 calc(50% - 12px);min-width:0;position:relative;transition:all .3s;display:flex;flex-direction:column;box-shadow:0 4px 12px rgba(0,0,0,.06);overflow:visible}
@media(max-width:640px){.plan-card{flex:0 0 calc(100% - 0px);padding:28px 20px}}
.plan-card:hover{transform:translateY(-8px);box-shadow:0 12px 32px rgba(79,70,229,.15);border-color:#6366f1}
.plan-badge{position:absolute;top:-10px;right:20px;background:linear-gradient(135deg,#4F46E5,#7C3AED);color:#fff;padding:6px 14px;border-radius:20px;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;z-index:10;box-shadow:0 2px 8px rgba(79,70,229,.3)}
.plan-name{font-size:26px;font-weight:900;color:#0f172a;margin-bottom:10px;line-height:1.1}
.plan-desc{font-size:13px;color:#64748b;margin-bottom:24px;line-height:1.5;min-height:40px}
.plan-price{font-size:52px;font-weight:900;color:#4F46E5;line-height:1;margin-bottom:8px}
.plan-price small{font-size:22px;font-weight:900}
.plan-period{font-size:13px;color:#64748b;margin-bottom:28px;padding-bottom:28px;border-bottom:2px solid #e2e8f0}
.plan-features{list-style:none;margin:0;flex-grow:1}
.plan-features li{padding:12px 0;font-size:14px;display:flex;align-items:flex-start;gap:10px;color:#475569}
.plan-features li::before{content:'✓';color:#4F46E5;font-weight:900;flex-shrink:0;font-size:16px}
.plan-cta{width:100%;padding:16px;background:linear-gradient(135deg,#4F46E5,#7C3AED);color:#fff;border:none;border-radius:10px;font-weight:800;font-size:15px;cursor:pointer;transition:all .3s;margin-top:24px;text-transform:uppercase;letter-spacing:.05em;text-align:center;display:block;text-decoration:none}
.plan-cta:hover{opacity:.9;transform:translateY(-2px);box-shadow:0 8px 20px rgba(79,70,229,.3)}
.carousel-btn{position:absolute;top:50%;transform:translateY(-50%);width:48px;height:48px;background:#fff;border:2px solid #e2e8f0;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all .3s;z-index:10;box-shadow:0 4px 12px rgba(0,0,0,.1)}
.carousel-btn:hover{background:#4F46E5;border-color:#4F46E5;box-shadow:0 6px 20px rgba(79,70,229,.3)}
.carousel-btn:hover svg{stroke:#fff}
.carousel-btn svg{width:24px;height:24px;stroke:#4F46E5;stroke-width:3;transition:.3s}
.carousel-btn--prev{left:0}.carousel-btn--next{right:0}
.carousel-dots{display:flex;justify-content:center;gap:10px;margin-top:40px}
.carousel-dot{width:12px;height:12px;border-radius:50%;background:#e2e8f0;cursor:pointer;transition:all .3s}
.carousel-dot.active{background:#4F46E5;width:32px;border-radius:6px}
.carousel-dot:hover{background:#6366f1}
.addons-sec{margin-top:24px;padding-top:24px;border-top:2px solid #e2e8f0}
.addons-sec h3{font-size:16px;font-weight:800;color:#0f172a;margin-bottom:16px;line-height:1.2}
.addon-item{background:#f8fafc;border:2px solid #e2e8f0;border-radius:8px;padding:14px;margin-bottom:10px;display:flex;align-items:center;gap:12px;cursor:pointer;transition:.2s}
.addon-item:hover{border-color:#4F46E5}
.addon-item.selected{border-color:#4F46E5;background:#eef2ff}
.addon-check{width:20px;height:20px;border:2px solid #cbd5e1;border-radius:4px;flex-shrink:0;display:flex;align-items:center;justify-content:center;transition:.2s;font-size:12px}
.addon-item.selected .addon-check{background:#4F46E5;border-color:#4F46E5;color:#fff}
.addon-info{flex:1}
.addon-name{font-weight:700;font-size:14px;color:#0f172a}
.addon-desc{font-size:12px;color:#64748b;margin-top:2px}
.addon-price{font-weight:700;font-size:16px;color:#4F46E5;flex-shrink:0}
.total-calc{background:linear-gradient(135deg,#4F46E5,#7C3AED);color:#fff;padding:18px;border-radius:10px;margin-top:20px;text-align:center}
.total-label{font-size:14px;opacity:.9;margin-bottom:6px}
.total-value{font-size:36px;font-weight:900;line-height:1}

/* ── Compare Modal ── */
.compare-modal{display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,.85);z-index:1000;overflow-y:auto;padding:40px 20px}
.compare-modal.active{display:flex;align-items:flex-start;justify-content:center}
.compare-modal__content{background:#fff;border-radius:20px;max-width:1400px;width:100%;position:relative;padding:40px;margin:auto}
.compare-modal__header{display:flex;justify-content:space-between;align-items:center;margin-bottom:32px;padding-bottom:20px;border-bottom:2px solid #e2e8f0}
.compare-modal__title{font-size:2rem;font-weight:900;color:#0f172a}
.compare-modal__close{width:40px;height:40px;border-radius:50%;background:#f8fafc;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:.3s}
.compare-modal__close:hover{background:#4F46E5;transform:rotate(90deg)}
.compare-modal__close svg{width:24px;height:24px;stroke:#0f172a;stroke-width:2}
.compare-modal__close:hover svg{stroke:#fff}
.compare-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:20px}
@media(max-width:1200px){.compare-grid{grid-template-columns:repeat(3,1fr)}}
@media(max-width:860px){.compare-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:640px){.compare-grid{grid-template-columns:1fr}.compare-modal__content{padding:24px}}
.compare-card{background:#f8fafc;border:2px solid #e2e8f0;border-radius:12px;padding:24px 20px;position:relative}
.compare-card.featured{border-color:#6366f1;background:#eef2ff}
.compare-card__badge{position:absolute;top:-10px;right:12px;background:linear-gradient(135deg,#4F46E5,#7C3AED);color:#fff;padding:4px 10px;border-radius:12px;font-size:9px;font-weight:800;text-transform:uppercase}
.compare-card__name{font-size:18px;font-weight:900;color:#0f172a;margin-bottom:8px}
.compare-card__price{font-size:32px;font-weight:900;color:#4F46E5;margin-bottom:4px}
.compare-card__price small{font-size:16px}
.compare-card__period{font-size:11px;color:#64748b;margin-bottom:20px;padding-bottom:20px;border-bottom:1px solid #e2e8f0}
.compare-card__features{list-style:none;margin:0}
.compare-card__features li{padding:8px 0;font-size:12px;color:#475569;display:flex;align-items:flex-start;gap:6px}
.compare-card__features li::before{content:'✓';color:#4F46E5;font-weight:900;flex-shrink:0;font-size:14px}

/* ── Help Card ── */
.help-card{background:linear-gradient(135deg,#4F46E5,#7C3AED);border-radius:16px;padding:40px;margin-top:48px;display:flex;align-items:center;justify-content:space-between;gap:32px;box-shadow:0 12px 40px rgba(79,70,229,.25);position:relative;overflow:hidden}
.help-card::before{content:'';position:absolute;top:-50%;right:-10%;width:300px;height:300px;background:radial-gradient(circle,rgba(255,255,255,.1),transparent);border-radius:50%}
.help-content{flex:1;position:relative;z-index:1}
.help-title{font-size:1.5rem;font-weight:700;color:#fff;margin-bottom:8px}
.help-subtitle{font-size:1rem;color:rgba(255,255,255,.8);font-weight:400}
.help-action{position:relative;z-index:1}
.help-btn{background:#fff;color:#4F46E5;padding:16px 32px;border-radius:10px;font-weight:700;font-size:.95rem;border:none;cursor:pointer;transition:all .3s;white-space:nowrap;text-decoration:none;display:inline-block}
.help-btn:hover{opacity:.9;transform:translateY(-2px)}
@media(max-width:860px){.help-card{flex-direction:column;text-align:center;padding:32px 24px}}

/* ── Conditions ── */
.conds-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:16px}
@media(max-width:640px){.conds-grid{grid-template-columns:1fr}}
.cond{display:flex;gap:14px;padding:16px 18px;border:1px solid #e2e8f0;border-radius:10px;background:#fff;transition:border-color .18s,box-shadow .18s}
.cond:hover{border-color:#c7d2fe;box-shadow:0 3px 12px rgba(79,70,229,.09)}
.cond__num{flex-shrink:0;width:24px;height:24px;border-radius:50%;background:#eef2ff;display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:800;color:#4F46E5;margin-top:1px}
.cond p{font-size:.875rem;color:#475569;line-height:1.6;margin:0}
.cond strong{color:#0f172a}

/* ── Testimonials ── */
.test-grid{display:grid;grid-template-columns:1fr 1fr;gap:22px}
@media(max-width:640px){.test-grid{grid-template-columns:1fr}}
.test-card{background:#fff;border:1px solid #e2e8f0;border-radius:18px;padding:32px;box-shadow:0 1px 3px rgba(79,70,229,.07);position:relative;overflow:hidden;transition:box-shadow .2s}
.test-card:hover{box-shadow:0 8px 32px rgba(79,70,229,.13)}
.test-card::before{content:'\201C';font-family:Georgia,serif;font-size:6rem;line-height:0;color:#eef2ff;position:absolute;top:28px;left:24px;z-index:0}
.test-card__text{font-size:.92rem;color:#475569;line-height:1.78;margin-bottom:24px;position:relative;z-index:1;padding-left:8px}
.test-card__author{display:flex;align-items:center;gap:12px}
.test-card__av{width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,#4F46E5,#7C3AED);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.85rem;color:#fff}
.test-card__name{font-size:.88rem;font-weight:700;color:#0f172a}
.test-card__role{font-size:.75rem;color:#64748b}

/* ── CTA Final ── */
.cta-final{padding:96px 24px;background:linear-gradient(135deg,#060d1f 0%,#0B1C3D 40%,#4F46E5 80%,#7C3AED 100%);text-align:center;color:#fff;position:relative;overflow:hidden}
.cta-final::before{content:'';position:absolute;inset:0;pointer-events:none;background-image:radial-gradient(circle,rgba(255,255,255,.12) 1px,transparent 1px);background-size:48px 48px}
.cta-final-inner{max-width:620px;margin:0 auto;position:relative}
.cta-title{font-size:clamp(24px,4vw,38px);font-weight:900;margin-bottom:14px;letter-spacing:-.02em}
.cta-title em{font-style:italic;color:#c4b5fd}
.cta-sub{font-size:16px;opacity:.75;margin-bottom:36px;line-height:1.7}
.cta-btns{display:flex;gap:12px;justify-content:center;flex-wrap:wrap}
.cta-btn-p{display:inline-flex;align-items:center;gap:8px;padding:14px 26px;border-radius:12px;font-size:.9rem;font-weight:700;background:#fff;color:#4F46E5;box-shadow:0 4px 20px rgba(255,255,255,.2);transition:transform .15s,box-shadow .15s;text-decoration:none}
.cta-btn-p:hover{transform:translateY(-2px);box-shadow:0 8px 32px rgba(255,255,255,.3)}
.cta-btn-s{display:inline-flex;align-items:center;gap:8px;padding:14px 26px;border-radius:12px;font-size:.9rem;font-weight:700;background:rgba(255,255,255,.1);color:#fff;border:1.5px solid rgba(255,255,255,.25);backdrop-filter:blur(8px);transition:background .15s;text-decoration:none}
.cta-btn-s:hover{background:rgba(255,255,255,.18)}

/* ── Footer (idêntico ao infraestrutura.php) ── */
.footer{background:#060d1f;color:rgba(255,255,255,.5);padding:56px 24px 32px}
.footer-inner{max-width:1100px;margin:0 auto}
.footer-grid{display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:40px;margin-bottom:48px}
@media(max-width:768px){.footer-grid{grid-template-columns:1fr 1fr;gap:28px}}
@media(max-width:480px){.footer-grid{grid-template-columns:1fr}}
.footer-brand-name{font-size:16px;font-weight:700;color:#fff;margin-bottom:10px}
.footer-brand-desc{font-size:13px;line-height:1.7;max-width:260px}
.footer-col-title{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:rgba(255,255,255,.35);margin-bottom:14px}
.footer-links{list-style:none;display:flex;flex-direction:column;gap:9px}
.footer-links a{color:rgba(255,255,255,.5);text-decoration:none;font-size:13px;transition:color .15s}
.footer-links a:hover{color:#fff}
.footer-bottom{border-top:1px solid rgba(255,255,255,.07);padding-top:24px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;font-size:12px}
.footer-status-dot{display:inline-flex;align-items:center;gap:6px;font-size:12px;color:rgba(255,255,255,.4)}
.footer-status-dot::before{content:'';width:7px;height:7px;border-radius:50%;background:#22c55e;display:inline-block}
</style>

<!-- DIFERENCIAIS -->
<section class="diff-sec">
  <div class="diff-header"><h2><?php echo View::e(I18n::tf('home.diff_titulo', $_nome)); ?></h2></div>
  <div class="diff-grid">
    <div class="diff-card animate"><div class="diff-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M12 2v4m0 12v4M4.93 4.93l2.83 2.83m8.48 8.48l2.83 2.83M2 12h4m12 0h4M4.93 19.07l2.83-2.83m8.48-8.48l2.83-2.83"/></svg></div><h3 class="diff-title"><?php echo View::e(I18n::t('home.diff_conectividade')); ?></h3><p class="diff-desc"><?php echo View::e(I18n::t('home.diff_conectividade_desc')); ?></p></div>
    <div class="diff-card animate"><div class="diff-icon"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg></div><h3 class="diff-title"><?php echo View::e(I18n::t('home.diff_kvm')); ?></h3><p class="diff-desc"><?php echo View::e(I18n::t('home.diff_kvm_desc')); ?></p></div>
    <div class="diff-card animate"><div class="diff-icon"><svg viewBox="0 0 24 24"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg></div><h3 class="diff-title"><?php echo View::e(I18n::t('home.diff_resiliencia')); ?></h3><p class="diff-desc"><?php echo View::e(I18n::t('home.diff_resiliencia_desc')); ?></p></div>
    <div class="diff-card animate"><div class="diff-icon"><svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg></div><h3 class="diff-title"><?php echo View::e(I18n::t('home.diff_io')); ?></h3><p class="diff-desc"><?php echo View::e(I18n::t('home.diff_io_desc')); ?></p></div>
    <div class="diff-card animate"><div class="diff-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg></div><h3 class="diff-title"><?php echo View::e(I18n::t('home.diff_rede')); ?></h3><p class="diff-desc"><?php echo View::e(I18n::t('home.diff_rede_desc')); ?></p></div>
    <div class="diff-card animate"><div class="diff-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-2.82 1.18V21a2 2 0 0 1-4 0v-.09a1.65 1.65 0 0 0-2.82-1.18l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.6a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg></div><h3 class="diff-title"><?php echo View::e(I18n::t('home.diff_backup')); ?></h3><p class="diff-desc"><?php echo View::e(I18n::t('home.diff_backup_desc')); ?></p></div>
  </div>
</section>

<!-- PLANOS -->
<section class="plans-sec" id="planos">
  <div style="max-width:1100px;margin:0 auto;padding:0 28px">
    <h2 style="text-align:center;margin-bottom:12px;font-size:clamp(2rem,3.5vw,3rem);font-weight:600;letter-spacing:-.02em;color:#0f172a"><?php echo View::e(I18n::t('home.plans_titulo')); ?></h2>
    <p style="text-align:center;color:#64748b;font-size:16px;max-width:640px;margin:0 auto 24px"><?php echo View::e(I18n::t('home.plans_sub')); ?></p>
    <div class="compare-btn-wrap">
      <button class="compare-btn" onclick="openCompareModal()">
        <svg viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
        <?php echo View::e(I18n::t('home.plans_comparar')); ?>
      </button>
    </div>
    <div class="carousel-container">
      <div class="carousel-wrapper">
        <div class="carousel-track" id="carouselTrack">
          <?php if (!empty($_planos)): ?>
          <?php foreach ($_planos as $_i => $_p):
            $_specs  = json_decode((string)($_p['specs_json'] ?? ''), true) ?: [];
            $_vcpu   = (int)($_specs['vcpu'] ?? $_specs['cpu'] ?? 0);
            $_ram    = (int)($_specs['ram_gb'] ?? 0);
            $_disco  = (int)($_specs['disco_gb'] ?? $_specs['storage_gb'] ?? 0);
            $_price  = (float)$_p['price'];
            $_pid    = (int)$_p['id'];
            $_addons = is_array($_p['addons'] ?? null) ? $_p['addons'] : [];
            $_badge  = (string)($_p['badge'] ?? '');
          ?>
          <div class="plan-card" id="pcard-<?php echo $_pid; ?>">
            <?php if ($_badge !== ''): ?><div class="plan-badge"><?php echo View::e($_badge); ?></div><?php endif; ?>
            <div class="plan-name"><?php echo View::e((string)$_p['name']); ?></div>
            <div class="plan-desc"><?php echo View::e((string)($_p['description'] ?? '')); ?></div>
            <div class="plan-price"><small><?php echo View::e(I18n::moeda()); ?></small> <?php echo View::e(I18n::numero(I18n::precoValor($_price), 0)); ?></div>
            <div class="plan-period"><?php echo View::e(I18n::t('home.plans_por_mes')); ?></div>
            <ul class="plan-features">
              <?php if ($_vcpu > 0): ?><li><?php echo $_vcpu; ?> vCPU</li><?php endif; ?>
              <?php if ($_ram > 0): ?><li><?php echo $_ram; ?> GB RAM</li><?php endif; ?>
              <?php if ($_disco > 0): ?><li><?php echo $_disco; ?> GB SSD</li><?php endif; ?>
              <li><?php echo View::e(I18n::t('home.plans_ddos')); ?></li><li><?php echo View::e(I18n::t('home.plans_ssl')); ?></li><li><?php echo View::e(I18n::t('home.plans_suporte')); ?></li>
            </ul>
            <?php if (!empty($_addons)): ?>
            <div class="addons-sec">
              <h3><?php echo View::e(I18n::t('home.plans_addons')); ?></h3>
              <?php foreach ($_addons as $_a): $_aprice = (float)$_a['price']; ?>
              <div class="addon-item" onclick="toggleAddon(this,<?php echo $_pid; ?>,<?php echo $_aprice; ?>)">
                <div class="addon-check"></div>
                <div class="addon-info">
                  <div class="addon-name"><?php echo View::e((string)$_a['name']); ?></div>
                  <?php if (!empty($_a['description'])): ?><div class="addon-desc"><?php echo View::e((string)$_a['description']); ?></div><?php endif; ?>
                </div>
                <div class="addon-price">+ <?php echo View::e(I18n::preco($_aprice)); ?></div>
              </div>
              <?php endforeach; ?>
            </div>
            <div class="total-calc">
              <div class="total-label"><?php echo View::e(I18n::t('home.plans_total')); ?></div>
              <div class="total-value"><?php echo View::e(I18n::moeda()); ?> <span id="total-<?php echo $_pid; ?>"><?php echo View::e(I18n::numero(I18n::precoValor($_price), 0)); ?></span></div>
            </div>
            <?php endif; ?>
            <a href="/cliente/criar-conta?plano=<?php echo $_pid; ?>" class="plan-cta"><?php echo View::e(I18n::t('home.nav_contratar')); ?></a>
          </div>
          <?php endforeach; ?>
          <?php else: ?>
          <div style="padding:48px;text-align:center;color:#94a3b8;font-size:14px;width:100%"><?php echo View::e(I18n::t('home.plans_empty')); ?> <a href="/contato" style="color:#4F46E5"><?php echo View::e(I18n::t('home.plans_empty_contato')); ?></a> <?php echo View::e(I18n::t('home.plans_empty_saber')); ?></div>
          <?php endif; ?>
        </div>
      </div>
      <div class="carousel-btn carousel-btn--prev" onclick="moveCarousel(-1)"><svg viewBox="0 0 24 24" fill="none"><path d="M15 18l-6-6 6-6" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
      <div class="carousel-btn carousel-btn--next" onclick="moveCarousel(1)"><svg viewBox="0 0 24 24" fill="none"><path d="M9 18l6-6-6-6" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
      <div class="carousel-dots" id="carouselDots"></div>
    </div>
    <div class="help-card">
      <div class="help-content">
        <h3 class="help-title"><?php echo View::e(I18n::t('home.plans_help_titulo')); ?></h3>
        <p class="help-subtitle"><?php echo View::e(I18n::t('home.plans_help_sub')); ?></p>
      </div>
      <div class="help-action"><a href="/contato" class="help-btn"><?php echo View::e(I18n::t('home.plans_help_btn')); ?></a></div>
    </div>
  </div>
</section>

<!-- CONDIÇÕES -->
<section class="lp-section" id="condicoes">
  <div class="lp-section-inner">
    <div style="margin-bottom:40px">
      <span class="eyebrow"><?php echo View::e(I18n::t('home.cond_eyebrow')); ?></span>
      <h2 style="font-size:clamp(2rem,3.5vw,3rem);font-weight:600;letter-spacing:-.02em;margin-bottom:14px"><?php echo View::e(I18n::t('home.cond_titulo')); ?></h2>
      <p class="lead"><?php echo View::e(I18n::t('home.cond_sub')); ?></p>
    </div>
    <div class="conds-grid">
      <div class="cond"><div class="cond__num">01</div><p><?php echo I18n::t('home.cond_01'); ?></p></div>
      <div class="cond"><div class="cond__num">02</div><p><?php echo I18n::t('home.cond_02'); ?></p></div>
      <div class="cond"><div class="cond__num">03</div><p><?php echo I18n::t('home.cond_03'); ?></p></div>
      <div class="cond"><div class="cond__num">04</div><p><?php echo I18n::t('home.cond_04'); ?></p></div>
      <div class="cond"><div class="cond__num">05</div><p><?php echo I18n::t('home.cond_05'); ?></p></div>
      <div class="cond"><div class="cond__num">06</div><p><?php echo I18n::t('home.cond_06'); ?></p></div>
      <div class="cond"><div class="cond__num">07</div><p><?php echo I18n::t('home.cond_07'); ?></p></div>
      <div class="cond"><div class="cond__num">08</div><p><?php echo I18n::t('home.cond_08'); ?></p></div>
      <div class="cond"><div class="cond__num">09</div><p><?php echo I18n::t('home.cond_09'); ?></p></div>
      <div class="cond"><div class="cond__num">10</div><p><?php echo I18n::t('home.cond_10'); ?></p></div>
    </div>
  </div>
</section>

<!-- DEPOIMENTOS -->
<section class="lp-section alt">
  <div class="lp-section-inner">
    <div style="text-align:center;margin-bottom:52px">
      <span class="eyebrow"><?php echo View::e(I18n::t('home.dep_eyebrow')); ?></span>
      <h2 style="font-size:clamp(2rem,3.5vw,3rem);font-weight:600;letter-spacing:-.02em;color:#0f172a"><?php echo View::e(I18n::t('home.dep_titulo')); ?></h2>
    </div>
    <div class="test-grid">
      <div class="test-card"><p class="test-card__text"><?php echo View::e(I18n::tf('home.dep_1', $_nome)); ?></p><div class="test-card__author"><div class="test-card__av">ES</div><div><div class="test-card__name">Edson Souza</div><div class="test-card__role">CTO — Nexa Sistemas</div></div></div></div>
      <div class="test-card"><p class="test-card__text"><?php echo View::e(I18n::tf('home.dep_2', $_nome)); ?></p><div class="test-card__author"><div class="test-card__av">MP</div><div><div class="test-card__name">Marcio Polonio</div><div class="test-card__role">Diretor — Agência Pixel</div></div></div></div>
      <div class="test-card"><p class="test-card__text"><?php echo View::e(I18n::tf('home.dep_3', $_nome)); ?></p><div class="test-card__author"><div class="test-card__av">LB</div><div><div class="test-card__name">Leonardo Barros</div><div class="test-card__role">CEO — CoreTech Solutions</div></div></div></div>
      <div class="test-card"><p class="test-card__text"><?php echo View::e(I18n::tf('home.dep_4', $_nome)); ?></p><div class="test-card__author"><div class="test-card__av">RN</div><div><div class="test-card__name">Rafael Neves</div><div class="test-card__role">Gerente de TI — Grupo Vértice</div></div></div></div>
    </div>
  </div>
</section>

<!-- CTA FINAL -->
<section class="cta-final" id="contato">
  <div class="cta-final-inner">
    <span style="display:inline-block;font-size:.72rem;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:#c4b5fd;margin-bottom:14px"><?php echo View::e(I18n::t('home.cta_pronto')); ?></span>
    <?php if ($_trial_ativo): ?>
      <h2 class="cta-title"><?php echo View::e(I18n::t('home.cta_trial_titulo')); ?><br><em><?php echo View::e(I18n::t('home.cta_trial_titulo_em')); ?></em></h2>
      <p class="cta-sub"><?php echo $_trial_desc !== '' ? View::e($_trial_desc) : View::e(I18n::tf('home.cta_trial_sub', $_trial_dias)); ?></p>
      <div class="cta-btns">
        <a href="/cliente/criar-conta" class="cta-btn-p"><?php echo View::e($_trial_label); ?></a>
        <a href="/contato" class="cta-btn-s">💬 <?php echo View::e(I18n::t('home.cta_falar_equipe')); ?></a>
      </div>
    <?php else: ?>
      <h2 class="cta-title"><?php echo View::e(I18n::t('home.cta_ative_titulo')); ?><br><em><?php echo View::e(I18n::t('home.cta_ative_titulo_em')); ?></em></h2>
      <p class="cta-sub"><?php echo View::e(I18n::t('home.cta_ative_sub')); ?></p>
      <div class="cta-btns">
        <a href="/contato" class="cta-btn-p">✉️ <?php echo View::e(I18n::t('home.cta_proposta')); ?></a>
        <a href="/contato" class="cta-btn-s">💬 <?php echo View::e(I18n::t('home.cta_consultor')); ?></a>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- MODAL DE COMPARAÇÃO -->
<div class="compare-modal" id="compareModal">
  <div class="compare-modal__content">
    <div class="compare-modal__header">
      <h2 class="compare-modal__title"><?php echo View::e(I18n::t('home.plans_comparar')); ?></h2>
      <button class="compare-modal__close" onclick="closeCompareModal()"><svg viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
    </div>
    <div class="compare-grid">
      <?php foreach ($_planos as $_p):
        $_specs  = json_decode((string)($_p['specs_json'] ?? ''), true) ?: [];
        $_vcpu   = (int)($_specs['vcpu'] ?? $_specs['cpu'] ?? 0);
        $_ram    = (int)($_specs['ram_gb'] ?? 0);
        $_disco  = (int)($_specs['disco_gb'] ?? $_specs['storage_gb'] ?? 0);
        $_price  = (float)$_p['price'];
        $_badge  = (string)($_p['badge'] ?? '');
      ?>
      <div class="compare-card <?php echo $_badge !== '' ? 'featured' : ''; ?>">
        <?php if ($_badge !== ''): ?><div class="compare-card__badge"><?php echo View::e($_badge); ?></div><?php endif; ?>
        <div class="compare-card__name"><?php echo View::e((string)$_p['name']); ?></div>
        <div class="compare-card__price"><small><?php echo View::e(I18n::moeda()); ?></small> <?php echo View::e(I18n::numero(I18n::precoValor($_price), 0)); ?></div>
        <div class="compare-card__period"><?php echo View::e(I18n::t('home.plans_por_mes')); ?></div>
        <ul class="compare-card__features">
          <?php if ($_vcpu > 0): ?><li><?php echo $_vcpu; ?> vCPU</li><?php endif; ?>
          <?php if ($_ram > 0): ?><li><?php echo $_ram; ?> GB RAM</li><?php endif; ?>
          <?php if ($_disco > 0): ?><li><?php echo $_disco; ?> GB SSD</li><?php endif; ?>
          <li><?php echo View::e(I18n::t('home.plans_ddos')); ?></li><li><?php echo View::e(I18n::t('home.plans_ssl')); ?></li><li><?php echo View::e(I18n::t('home.plans_suporte')); ?></li>
        </ul>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- FOOTER (idêntico ao infraestrutura.php) -->
<footer class="footer">
  <div class="footer-inner">
    <div class="footer-grid">
      <div>
        <div class="footer-brand-name"><?php echo View::e($_nome); ?></div>
        <div class="footer-brand-desc"><?php echo View::e(I18n::t('home.footer_desc')); ?></div>
      </div>
      <div><div class="footer-col-title"><?php echo View::e(I18n::t('home.footer_produto')); ?></div><ul class="footer-links"><li><a href="#sobre"><?php echo View::e(I18n::t('home.footer_sobre')); ?></a></li><li><a href="#planos"><?php echo View::e(I18n::t('home.footer_planos')); ?></a></li><li><a href="#condicoes"><?php echo View::e(I18n::t('home.footer_condicoes')); ?></a></li><li><a href="/infraestrutura"><?php echo View::e(I18n::t('home.footer_devs')); ?></a></li><li><a href="/changelog">Changelog</a></li></ul></div>
      <div><div class="footer-col-title"><?php echo View::e(I18n::t('home.footer_suporte')); ?></div><ul class="footer-links"><li><a href="/contato"><?php echo View::e(I18n::t('home.footer_contato')); ?></a></li><li><a href="/status"><?php echo View::e(I18n::t('home.footer_status')); ?></a></li><li><a href="/cliente/tickets"><?php echo View::e(I18n::t('home.footer_tickets')); ?></a></li><li><a href="/cliente/ajuda"><?php echo View::e(I18n::t('home.footer_ajuda')); ?></a></li></ul></div>
      <div><div class="footer-col-title"><?php echo View::e(I18n::t('home.footer_legal')); ?></div><ul class="footer-links"><li><a href="/termos"><?php echo View::e(I18n::t('home.footer_termos')); ?></a></li><li><a href="/privacidade"><?php echo View::e(I18n::t('home.footer_privacidade')); ?></a></li></ul></div>
    </div>
    <div class="footer-bottom">
      <div><?php echo View::e(SistemaConfig::copyrightText()); ?> · <?php echo View::e($_nome); ?> v<?php echo View::e(SistemaConfig::versao()); ?></div>
      <div class="footer-status-dot"><?php echo View::e(I18n::t('home.footer_sistema_op')); ?></div>
    </div>
  </div>
</footer>

<script>
document.querySelectorAll('a[href^="#"]').forEach(function(a){
  a.addEventListener('click',function(e){
    var t=document.querySelector(this.getAttribute('href'));
    if(t){e.preventDefault();window.scrollTo({top:t.getBoundingClientRect().top+scrollY-68,behavior:'smooth'});}
  });
});

var observerDiff=new IntersectionObserver(function(entries){
  entries.forEach(function(e){if(e.isIntersecting){e.target.classList.add('animate');observerDiff.unobserve(e.target);}});
},{threshold:.1,rootMargin:'0px 0px -50px 0px'});
document.querySelectorAll('.diff-card').forEach(function(c){observerDiff.observe(c);});

var currentSlide=0;
var track=document.getElementById('carouselTrack');
var origCards=Array.from(document.querySelectorAll('.plan-card'));
var totalSlides=origCards.length;
var dotsEl=document.getElementById('carouselDots');
origCards.forEach(function(c){track.appendChild(c.cloneNode(true));});
var totalDots=Math.ceil(totalSlides/2);
for(var i=0;i<totalDots;i++){
  var d=document.createElement('div');
  d.className='carousel-dot'+(i===0?' active':'');
  d.onclick=(function(idx){return function(){goToSlide(idx*2);};})(i);
  dotsEl.appendChild(d);
}
function updateCarousel(smooth){
  var ww=document.querySelector('.carousel-wrapper').offsetWidth;
  track.style.transition=smooth?'transform .5s cubic-bezier(.4,0,.2,1)':'none';
  track.style.transform='translateX(-'+(currentSlide*(ww+24))+'px)';
  var ad=Math.floor((currentSlide%totalSlides)/2);
  document.querySelectorAll('.carousel-dot').forEach(function(d,i){d.classList.toggle('active',i===ad);});
}
function moveCarousel(dir){
  currentSlide+=dir;updateCarousel(true);
  setTimeout(function(){
    if(currentSlide>=totalSlides){currentSlide=0;updateCarousel(false);}
    else if(currentSlide<0){currentSlide=totalSlides-1;updateCarousel(false);}
  },500);
}
function goToSlide(idx){currentSlide=idx;updateCarousel(true);}
window.addEventListener('resize',function(){updateCarousel(false);});
setTimeout(function(){updateCarousel(false);},100);

var _convRate=<?php echo json_encode(I18n::idioma() === 'pt-BR' ? 1.0 : (1.0 / \LRV\Core\ConfiguracoesSistema::taxaConversaoUsd())); ?>;
var _locale=<?php echo json_encode(I18n::idioma() === 'pt-BR' ? 'pt-BR' : 'en-US'); ?>;
var planBase={<?php foreach($_planos as $_p): ?><?php echo (int)$_p['id']; ?>:<?php echo (float)$_p['price']; ?>,<?php endforeach; ?>};
var planAddons={<?php foreach($_planos as $_p): ?><?php echo (int)$_p['id']; ?>:[],<?php endforeach; ?>};
function toggleAddon(el,pid,price){
  el.classList.toggle('selected');
  var sel=el.classList.contains('selected');
  el.querySelector('.addon-check').innerHTML=sel?'✓':'';
  if(sel){planAddons[pid].push(price);}else{var i=planAddons[pid].indexOf(price);if(i>-1)planAddons[pid].splice(i,1);}
  var total=((planBase[pid]||0)+planAddons[pid].reduce(function(s,p){return s+p;},0))*_convRate;
  var el2=document.getElementById('total-'+pid);
  if(el2)el2.textContent=Math.round(total).toLocaleString(_locale);
}
function openCompareModal(){document.getElementById('compareModal').classList.add('active');document.body.style.overflow='hidden';}
function closeCompareModal(){document.getElementById('compareModal').classList.remove('active');document.body.style.overflow='';}
document.getElementById('compareModal').addEventListener('click',function(e){if(e.target===this)closeCompareModal();});
document.addEventListener('keydown',function(e){if(e.key==='Escape')closeCompareModal();});

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

</script>
<?php require __DIR__ . '/_partials/chat-widget.php'; ?>
</body>
</html>
