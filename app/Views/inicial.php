<?php
declare(strict_types=1);
use LRV\Core\I18n;
use LRV\Core\View;
use LRV\Core\SistemaConfig;

$_nome   = SistemaConfig::nome();
$_logo   = SistemaConfig::logoUrl();
$_planos = is_array($planos ?? null) ? $planos : [];

// Hero card: último plano (maior) ou fallback
$_hero_plano = !empty($_planos) ? $_planos[count($_planos) - 1] : null;
if ($_hero_plano) {
    $_hs = json_decode((string)($_hero_plano['specs_json'] ?? ''), true) ?: [];
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
<style>
*{margin:0;padding:0;box-sizing:border-box}
:root{
  --az:#4F46E5;--azd:#4338CA;--azs:#6366F1;
  --bg:#F7F9FC;--bd:#DDE5F0;--tx:#0C1A2E;--ts:#2E4057;
  --wh:#fff;--li:#EEF2FF;--red:#7C3AED;--redhov:#6D28D9;--surf:#F0F4FB
}
html{scroll-behavior:smooth}
body{font-family:system-ui,-apple-system,'Segoe UI',Roboto,Ubuntu,sans-serif;color:var(--tx);background:var(--bg);font-size:15px;line-height:1.6;-webkit-font-smoothing:antialiased}
a{text-decoration:none;color:inherit}
.wrap{max-width:1140px;margin:0 auto;padding:0 28px}
.btn{display:inline-block;padding:12px 26px;border-radius:10px;font-weight:700;font-size:14px;cursor:pointer;border:none;transition:.2s}
.btn-p{background:var(--red);color:var(--wh);box-shadow:0 6px 20px rgba(124,58,237,.4)}
.btn-p:hover{background:var(--redhov);transform:translateY(-2px)}
.btn-s{background:transparent;color:var(--wh);border:2px solid rgba(255,255,255,.35)}
.btn-s:hover{background:rgba(255,255,255,.1)}
.btn-b{background:var(--az);color:var(--wh)}
.btn-b:hover{background:var(--azd);transform:translateY(-2px)}

/* Header */
.header{position:sticky;top:0;z-index:200;background:var(--az);border-bottom:1px solid rgba(255,255,255,.1)}
.header__inner{display:flex;align-items:center;justify-content:space-between;height:62px;max-width:1140px;margin:0 auto;padding:0 28px}
.header__logo{display:flex;align-items:center}
.header__logo img{height:36px;width:auto}
.header__logo-fallback{font-size:1.35rem;font-weight:900;color:var(--wh);letter-spacing:-.03em}
.header__logo-fallback span{color:#c7d2fe}
.header__nav{display:flex;align-items:center;gap:28px}
.header__nav a{font-size:.85rem;font-weight:500;color:rgba(255,255,255,.7);transition:color .15s}
.header__nav a:hover{color:var(--wh)}
.header__cta{background:var(--red);color:var(--wh)!important;padding:9px 20px;border-radius:10px;font-weight:700!important;font-size:.83rem!important;transition:background .15s,transform .15s!important}
.header__cta:hover{background:var(--redhov)!important;transform:translateY(-1px)}

/* Hero */
.hero{background:var(--az);position:relative;overflow:hidden;padding:80px 0}
.hero__particles{position:absolute;inset:0;pointer-events:none;background-image:radial-gradient(circle,rgba(255,255,255,.15) 1px,transparent 1px),radial-gradient(circle,rgba(255,255,255,.08) 1px,transparent 1px);background-size:48px 48px,96px 96px;background-position:0 0,24px 24px}
.hero__glow{position:absolute;width:700px;height:700px;background:radial-gradient(circle,rgba(99,102,241,.22) 0%,transparent 65%);top:-200px;right:-100px;pointer-events:none}
.hero__glow2{position:absolute;width:400px;height:400px;background:radial-gradient(circle,rgba(124,58,237,.15) 0%,transparent 65%);bottom:0;left:10%;pointer-events:none}
.hero__inner{position:relative;z-index:1;display:grid;grid-template-columns:1fr 1fr;gap:64px;align-items:center;max-width:1140px;margin:0 auto;padding:0 28px 80px}
.hero__badge{display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.18);padding:5px 14px;border-radius:99px;margin-bottom:24px}
.hero__badge-dot{width:7px;height:7px;border-radius:50%;background:#4ADE80;box-shadow:0 0 0 3px rgba(74,222,128,.25);animation:pulse 2s ease infinite}
@keyframes pulse{0%,100%{box-shadow:0 0 0 3px rgba(74,222,128,.25)}50%{box-shadow:0 0 0 6px rgba(74,222,128,.1)}}
.hero__badge span{font-size:.73rem;font-weight:600;color:rgba(255,255,255,.8);letter-spacing:.06em;text-transform:uppercase}
.hero__title{font-size:clamp(2.4rem,4vw,3.8rem);font-weight:400;line-height:1.08;letter-spacing:-.02em;color:var(--wh);margin-bottom:22px}
.hero__title em{font-style:italic;color:#c7d2fe}
.hero__subtitle{font-size:1rem;font-weight:300;color:rgba(255,255,255,.62);line-height:1.8;margin-bottom:36px;max-width:480px}
.hero__actions{display:flex;gap:12px;flex-wrap:wrap}
.hero__visual{position:relative}
.hero__server-card{background:rgba(255,255,255,.06);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,.14);border-radius:18px;padding:28px;color:var(--wh)}
.server-card__header{display:flex;align-items:center;gap:12px;margin-bottom:22px}
.server-card__icon{width:42px;height:42px;border-radius:10px;background:rgba(255,255,255,.12);display:flex;align-items:center;justify-content:center}
.server-card__label{font-size:.7rem;color:rgba(255,255,255,.5);margin-bottom:2px;letter-spacing:.06em;text-transform:uppercase}
.server-card__name{font-weight:700;font-size:.95rem}
.server-specs{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:20px}
.spec-item{background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.1);border-radius:10px;padding:12px 14px}
.spec-item__val{font-size:1.05rem;font-weight:800;color:var(--wh);line-height:1;margin-bottom:4px}
.spec-item__key{font-size:.7rem;color:rgba(255,255,255,.45);text-transform:uppercase;letter-spacing:.05em}
.server-price{display:flex;align-items:center;justify-content:space-between;padding-top:18px;border-top:1px solid rgba(255,255,255,.12)}
.server-price__label{font-size:.8rem;color:rgba(255,255,255,.5)}
.server-price__value{font-size:2rem;color:var(--wh)}
.server-price__value span{font-size:.8rem;color:rgba(255,255,255,.45)}
.hero__floater{position:absolute;background:var(--wh);border-radius:10px;padding:10px 16px;box-shadow:0 20px 60px rgba(79,70,229,.18);display:flex;align-items:center;gap:10px;animation:float 3s ease-in-out infinite}
.hero__floater--1{bottom:-20px;left:-24px;animation-delay:0s}
.hero__floater--2{top:20px;right:-20px;animation-delay:1.5s}
@keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-8px)}}
.floater-icon{width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center}
.floater-icon--green{background:#DCFCE7}
.floater-icon--blue{background:var(--li)}
.floater__text strong{display:block;font-size:.8rem;color:var(--tx);font-weight:700}
.floater__text span{font-size:.7rem;color:var(--ts)}
.hero__clients{max-width:1140px;margin:0 auto;padding:32px 28px 40px;border-top:1px solid rgba(255,255,255,.1);position:relative;z-index:1}
.hero__clients p{font-size:.72rem;font-weight:600;letter-spacing:.12em;text-transform:uppercase;color:rgba(255,255,255,.35);text-align:center;margin-bottom:20px}
.clients-logos{display:flex;align-items:center;justify-content:center;gap:40px;flex-wrap:wrap}
.clients-logos span{font-size:.82rem;font-weight:700;color:rgba(255,255,255,.3);letter-spacing:.04em;transition:color .2s;cursor:default}
.clients-logos span:hover{color:rgba(255,255,255,.6)}

/* Stats */
.statsbar{background:var(--az);padding:40px 0}
.stats{display:grid;grid-template-columns:repeat(4,1fr);text-align:center;max-width:1140px;margin:0 auto}
.stat{padding:36px 28px;border-right:1px solid rgba(255,255,255,.1);transition:background .18s}
.stat:last-child{border-right:none}
.stat:hover{background:rgba(255,255,255,.05)}
.stat h3{font-size:2.4rem;font-weight:900;color:var(--wh);line-height:1;margin-bottom:8px}
.stat p{font-size:.82rem;color:rgba(255,255,255,.6);margin:0}

section{padding:80px 0}
.sec-alt{background:var(--surf)}
h2{font-size:clamp(2rem,3.5vw,3rem);font-weight:600;line-height:1.12;margin-bottom:16px;letter-spacing:-.02em}
h3{font-size:1.05rem;font-weight:700;margin-bottom:8px}
p{color:var(--ts);margin-bottom:10px}
.eyebrow{display:inline-block;font-size:.72rem;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:var(--azs);margin-bottom:14px}
.lead{font-size:1.1rem;font-weight:300;color:var(--ts);line-height:1.75;max-width:600px}

/* Features */
.features-header{display:grid;grid-template-columns:1fr 1fr;gap:64px;align-items:center;margin-bottom:64px}
.features-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:2px;background:var(--bd);border:1px solid var(--bd);border-radius:18px;overflow:hidden}
.feat-item{background:var(--wh);padding:32px 28px;transition:background .2s}
.feat-item:hover{background:var(--li)}
.feat-item__icon{width:46px;height:46px;background:var(--li);border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:18px}
.feat-item__icon svg{width:24px;height:24px;stroke:var(--azs);stroke-width:2;fill:none}
.feat-item__title{font-size:.95rem;font-weight:700;color:var(--tx);margin-bottom:8px}
.feat-item__desc{font-size:.85rem;color:var(--ts);line-height:1.65}

/* Plans Carousel */
.plans-sec{background:var(--surf);padding:80px 0;overflow:hidden}
.compare-btn-container{text-align:center;margin-bottom:32px}
.compare-btn{display:inline-flex;align-items:center;gap:8px;padding:12px 28px;background:var(--wh);border:2px solid var(--az);color:var(--az);border-radius:10px;font-weight:700;font-size:14px;cursor:pointer;transition:all .3s;text-transform:uppercase;letter-spacing:.05em}
.compare-btn:hover{background:var(--az);color:var(--wh);transform:translateY(-2px);box-shadow:0 6px 20px rgba(79,70,229,.2)}
.compare-btn svg{width:18px;height:18px;stroke:currentColor;stroke-width:2;fill:none}
.carousel-container{position:relative;max-width:1200px;margin:0 auto;padding:0 80px}
.carousel-wrapper{overflow:hidden;width:100%;padding-top:20px;margin-top:-20px}
.carousel-track{display:flex;transition:transform .5s cubic-bezier(.4,0,.2,1);gap:24px}
.plan-card{background:var(--wh);border:2px solid var(--bd);border-radius:16px;padding:36px 28px;flex:0 0 calc(50% - 12px);min-width:0;position:relative;transition:all .3s;display:flex;flex-direction:column;box-shadow:0 4px 12px rgba(0,0,0,.06);overflow:visible}
.plan-card:hover{transform:translateY(-8px);box-shadow:0 12px 32px rgba(79,70,229,.15);border-color:var(--azs)}
.plan-badge{position:absolute;top:-10px;right:20px;background:var(--az);color:var(--wh);padding:6px 14px;border-radius:20px;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;z-index:10;box-shadow:0 2px 8px rgba(79,70,229,.3)}
.plan-name{font-size:26px;font-weight:900;color:var(--tx);margin-bottom:10px;line-height:1.1}
.plan-desc{font-size:13px;color:var(--ts);margin-bottom:24px;line-height:1.5;min-height:40px}
.plan-price{font-size:52px;font-weight:900;color:var(--az);line-height:1;margin-bottom:8px}
.plan-price small{font-size:22px;font-weight:900}
.plan-period{font-size:13px;color:var(--ts);margin-bottom:28px;padding-bottom:28px;border-bottom:2px solid var(--bd)}
.plan-features{list-style:none;margin:0;flex-grow:1}
.plan-features li{padding:12px 0;font-size:14px;display:flex;align-items:flex-start;gap:10px;color:var(--ts)}
.plan-features li::before{content:'✓';color:var(--az);font-weight:900;flex-shrink:0;font-size:16px}
.plan-cta{width:100%;padding:16px;background:var(--az);color:var(--wh);border:none;border-radius:10px;font-weight:800;font-size:15px;cursor:pointer;transition:all .3s;margin-top:24px;text-transform:uppercase;letter-spacing:.05em;text-align:center;display:block;text-decoration:none}
.plan-cta:hover{background:var(--azd);transform:translateY(-2px);box-shadow:0 8px 20px rgba(79,70,229,.3)}
.carousel-btn{position:absolute;top:50%;transform:translateY(-50%);width:48px;height:48px;background:var(--wh);border:2px solid var(--bd);border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all .3s;z-index:10;box-shadow:0 4px 12px rgba(0,0,0,.1)}
.carousel-btn:hover{background:var(--az);border-color:var(--az);box-shadow:0 6px 20px rgba(79,70,229,.3)}
.carousel-btn:hover svg{stroke:var(--wh)}
.carousel-btn svg{width:24px;height:24px;stroke:var(--az);stroke-width:3;transition:.3s}
.carousel-btn--prev{left:0}
.carousel-btn--next{right:0}
.carousel-dots{display:flex;justify-content:center;gap:10px;margin-top:40px}
.carousel-dot{width:12px;height:12px;border-radius:50%;background:var(--bd);cursor:pointer;transition:all .3s}
.carousel-dot.active{background:var(--az);width:32px;border-radius:6px}
.carousel-dot:hover{background:var(--azs)}

/* Compare Modal */
.compare-modal{display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,.85);z-index:1000;overflow-y:auto;padding:40px 20px}
.compare-modal.active{display:flex;align-items:flex-start;justify-content:center}
.compare-modal__content{background:var(--wh);border-radius:20px;max-width:1400px;width:100%;position:relative;padding:40px;margin:auto}
.compare-modal__header{display:flex;justify-content:space-between;align-items:center;margin-bottom:32px;padding-bottom:20px;border-bottom:2px solid var(--bd)}
.compare-modal__title{font-size:2rem;font-weight:900;color:var(--tx)}
.compare-modal__close{width:40px;height:40px;border-radius:50%;background:var(--surf);border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:.3s}
.compare-modal__close:hover{background:var(--az);transform:rotate(90deg)}
.compare-modal__close svg{width:24px;height:24px;stroke:var(--tx);stroke-width:2}
.compare-modal__close:hover svg{stroke:var(--wh)}
.compare-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:20px}
.compare-card{background:var(--surf);border:2px solid var(--bd);border-radius:12px;padding:24px 20px;position:relative}
.compare-card.featured{border-color:var(--azs);background:var(--li)}
.compare-card__badge{position:absolute;top:-10px;right:12px;background:var(--az);color:var(--wh);padding:4px 10px;border-radius:12px;font-size:9px;font-weight:800;text-transform:uppercase}
.compare-card__name{font-size:18px;font-weight:900;color:var(--tx);margin-bottom:8px}
.compare-card__price{font-size:32px;font-weight:900;color:var(--az);margin-bottom:4px}
.compare-card__price small{font-size:16px}
.compare-card__period{font-size:11px;color:var(--ts);margin-bottom:20px;padding-bottom:20px;border-bottom:1px solid var(--bd)}
.compare-card__features{list-style:none;margin:0}
.compare-card__features li{padding:8px 0;font-size:12px;color:var(--ts);display:flex;align-items:flex-start;gap:6px}
.compare-card__features li::before{content:'✓';color:var(--az);font-weight:900;flex-shrink:0;font-size:14px}

/* Help Card */
.help-card{background:linear-gradient(135deg,var(--az),var(--azd));border-radius:16px;padding:40px;margin-top:48px;display:flex;align-items:center;justify-content:space-between;gap:32px;box-shadow:0 12px 40px rgba(79,70,229,.2);position:relative;overflow:hidden}
.help-card::before{content:'';position:absolute;top:-50%;right:-10%;width:300px;height:300px;background:radial-gradient(circle,rgba(255,255,255,.1),transparent);border-radius:50%}
.help-content{flex:1;position:relative;z-index:1}
.help-title{font-size:1.5rem;font-weight:700;color:var(--wh);margin-bottom:8px}
.help-subtitle{font-size:1rem;color:rgba(255,255,255,.8);font-weight:400}
.help-action{position:relative;z-index:1}
.help-btn{background:var(--red);color:var(--wh);padding:16px 32px;border-radius:10px;font-weight:700;font-size:.95rem;border:none;cursor:pointer;transition:all .3s;box-shadow:0 6px 20px rgba(124,58,237,.4);white-space:nowrap;text-decoration:none;display:inline-block}
.help-btn:hover{background:var(--redhov);transform:translateY(-2px)}

/* Addons */
.addons-sec{margin-top:24px;padding-top:24px;border-top:2px solid var(--bd)}
.addons-sec h3{font-size:16px;font-weight:800;color:var(--tx);margin-bottom:16px;line-height:1.2}
.addon-item{background:var(--surf);border:2px solid var(--bd);border-radius:8px;padding:14px;margin-bottom:10px;display:flex;align-items:center;gap:12px;cursor:pointer;transition:.2s}
.addon-item:hover{border-color:var(--az)}
.addon-item.selected{border-color:var(--az);background:var(--li)}
.addon-check{width:20px;height:20px;border:2px solid var(--bd);border-radius:4px;flex-shrink:0;display:flex;align-items:center;justify-content:center;transition:.2s;font-size:12px}
.addon-item.selected .addon-check{background:var(--az);border-color:var(--az);color:var(--wh)}
.addon-info{flex:1}
.addon-name{font-weight:700;font-size:14px;color:var(--tx)}
.addon-desc{font-size:12px;color:var(--ts);margin-top:2px}
.addon-price{font-weight:700;font-size:16px;color:var(--az);flex-shrink:0}
.total-calc{background:var(--az);color:var(--wh);padding:18px;border-radius:10px;margin-top:20px;text-align:center}
.total-label{font-size:14px;opacity:.9;margin-bottom:6px}
.total-value{font-size:36px;font-weight:900;line-height:1}

/* Conditions */
.conds-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:16px}
.cond{display:flex;gap:14px;padding:16px 18px;border:1px solid var(--bd);border-radius:10px;background:var(--wh);transition:border-color .18s,box-shadow .18s}
.cond:hover{border-color:#c7d2fe;box-shadow:0 3px 12px rgba(79,70,229,.09)}
.cond__num{flex-shrink:0;width:24px;height:24px;border-radius:50%;background:var(--li);display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:800;color:var(--az);margin-top:1px}
.cond p{font-size:.875rem;color:var(--ts);line-height:1.6;margin:0}
.cond strong{color:var(--tx)}

/* Testimonials */
.test-grid{display:grid;grid-template-columns:1fr 1fr;gap:22px}
.test-card{background:var(--wh);border:1px solid var(--bd);border-radius:18px;padding:32px;box-shadow:0 1px 3px rgba(79,70,229,.07);position:relative;overflow:hidden;transition:box-shadow .2s}
.test-card:hover{box-shadow:0 8px 32px rgba(79,70,229,.13)}
.test-card::before{content:'\201C';font-family:Georgia,serif;font-size:6rem;line-height:0;color:var(--li);position:absolute;top:28px;left:24px;z-index:0}
.test-card__text{font-size:.92rem;color:var(--ts);line-height:1.78;margin-bottom:24px;position:relative;z-index:1;padding-left:8px}
.test-card__author{display:flex;align-items:center;gap:12px}
.test-card__av{width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,var(--az),var(--azs));display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.85rem;color:var(--wh)}
.test-card__name{font-size:.88rem;font-weight:700;color:var(--tx)}
.test-card__role{font-size:.75rem;color:var(--ts)}

/* CTA */
.cta-sec{background:var(--tx);padding:80px 0;text-align:center;position:relative;overflow:hidden}
.cta-sec::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse 800px 600px at 100% 100%,rgba(79,70,229,.5),transparent 60%),radial-gradient(ellipse 400px 400px at 0% 0%,rgba(99,102,241,.2),transparent 50%)}
.cta-sec__inner{position:relative;z-index:1}
.cta-sec h2{color:var(--wh);font-size:clamp(2.2rem,4vw,3.4rem);margin-bottom:20px}
.cta-sec h2 em{font-style:italic;color:#c7d2fe}
.cta-sec p{color:rgba(255,255,255,.5);font-size:16px;max-width:560px;margin:0 auto 28px}
.cta-btns{display:flex;gap:14px;justify-content:center;flex-wrap:wrap;margin-top:40px}

/* Footer */
.footer{background:var(--az);padding:24px 0}
.footer__inner{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;max-width:1140px;margin:0 auto;padding:0 28px}
.footer__brand{display:flex;align-items:center;gap:10px}
.footer__brand img{height:28px}
.footer__brand-name{font-weight:800;font-size:.95rem;color:var(--wh);letter-spacing:-.01em}
.footer__copy{font-size:.75rem;color:rgba(255,255,255,.35)}

/* Diferenciais */
.diff-sec{background:var(--wh);padding:80px 0;position:relative;overflow:hidden}
.diff-sec::before{content:'';position:absolute;bottom:0;left:0;right:0;height:300px;background:var(--az);z-index:0;clip-path:polygon(0 60%,100% 30%,100% 100%,0 100%)}
.diff-header{text-align:center;margin-bottom:60px;position:relative;z-index:1}
.diff-header h2{margin-bottom:8px}
.diff-header::after{content:'';display:block;width:80px;height:3px;background:var(--azs);margin:20px auto 0}
.diff-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px;position:relative;z-index:1;max-width:1140px;margin:0 auto;padding:0 28px}
.diff-card{background:var(--wh);border-radius:16px;padding:32px 24px;text-align:center;box-shadow:0 4px 20px rgba(0,0,0,.08);transition:all .3s cubic-bezier(.4,0,.2,1);position:relative;overflow:hidden}
.diff-card::before{content:'';position:absolute;top:0;left:0;right:0;height:4px;background:var(--azs);transform:scaleX(0);transform-origin:left;transition:transform .3s ease}
.diff-card:hover{transform:translateY(-8px);box-shadow:0 12px 40px rgba(79,70,229,.15)}
.diff-card:hover::before{transform:scaleX(1)}
.diff-icon{width:80px;height:80px;margin:0 auto 20px;background:linear-gradient(135deg,var(--li),#c7d2fe);border-radius:50%;display:flex;align-items:center;justify-content:center;transition:all .3s ease}
.diff-icon svg{width:40px;height:40px;stroke:var(--azs);stroke-width:2;fill:none}
.diff-card:hover .diff-icon{transform:scale(1.1) rotate(5deg);background:linear-gradient(135deg,var(--azs),var(--az))}
.diff-card:hover .diff-icon svg{stroke:var(--wh)}
.diff-title{font-size:1.15rem;font-weight:700;color:var(--tx);margin-bottom:12px}
.diff-desc{font-size:.88rem;color:var(--ts);line-height:1.7;margin:0}
.diff-card.animate{animation:fadeInUp .6s ease forwards;opacity:0}
@keyframes fadeInUp{from{opacity:0;transform:translateY(30px)}to{opacity:1;transform:translateY(0)}}
.diff-card:nth-child(1){animation-delay:.1s}.diff-card:nth-child(2){animation-delay:.2s}.diff-card:nth-child(3){animation-delay:.3s}
.diff-card:nth-child(4){animation-delay:.4s}.diff-card:nth-child(5){animation-delay:.5s}.diff-card:nth-child(6){animation-delay:.6s}

@media(max-width:860px){
  .diff-grid{grid-template-columns:1fr 1fr}
  .hero__inner{grid-template-columns:1fr;padding-bottom:60px}
  .hero__visual{display:none}
  .features-header{grid-template-columns:1fr;gap:32px}
  .features-grid{grid-template-columns:1fr 1fr}
  .stats{grid-template-columns:1fr 1fr}
  .test-grid{grid-template-columns:1fr}
  .conds-grid{grid-template-columns:1fr}
  .carousel-container{padding:0 50px}
  .plan-card{width:100%;min-width:280px}
  .help-card{flex-direction:column;text-align:center;padding:32px 24px}
  .compare-grid{grid-template-columns:repeat(3,1fr)}
}
@media(max-width:640px){
  .features-grid{grid-template-columns:1fr}
  .header__nav{display:none}
  .hero{padding:56px 0 0}
  section{padding:64px 0}
  .diff-grid{grid-template-columns:1fr}
  .stats{grid-template-columns:1fr}
  .clients-logos{gap:20px}
  .carousel-container{padding:0 20px}
  .carousel-btn{width:40px;height:40px}
  .plan-card{width:100%;min-width:100%}
  .compare-grid{grid-template-columns:1fr}
  .compare-modal__content{padding:24px}
}
</style>
</head>
<body>

<!-- HEADER -->
<header class="header">
  <div class="header__inner">
    <a href="/" class="header__logo">
      <?php if ($_logo !== ''): ?>
        <img src="<?php echo View::e($_logo); ?>" alt="<?php echo View::e($_nome); ?>">
      <?php else: ?>
        <div class="header__logo-fallback"><?php echo View::e($_nome); ?></div>
      <?php endif; ?>
    </a>
    <nav class="header__nav">
      <a href="#sobre">Sobre</a>
      <a href="#planos">Planos</a>
      <a href="#condicoes">Condições</a>
      <a href="/cliente/criar-conta" class="header__cta">Contratar Agora</a>
    </nav>
  </div>
</header>

<!-- HERO -->
<section class="hero">
  <div class="hero__particles"></div>
  <div class="hero__glow"></div>
  <div class="hero__glow2"></div>
  <div class="hero__inner">
    <div class="hero__text">
      <div class="hero__badge">
        <div class="hero__badge-dot"></div>
        <span>Servidores disponíveis agora</span>
      </div>
      <h1 class="hero__title">Infraestrutura VPS para<br><em>escalar seu negócio</em></h1>
      <p class="hero__subtitle">Servidores VPS com recursos dedicados, proteção DDoS nativa, uptime 99,9% e suporte técnico — ativação em até 6 dias úteis.</p>
      <div class="hero__actions">
        <a href="#planos" class="btn btn-p" style="padding:14px 26px;font-size:.9rem">Ver Planos e Preços</a>
        <a href="#sobre" class="btn btn-s" style="padding:14px 26px;font-size:.9rem">Saiba mais</a>
      </div>
    </div>
    <div class="hero__visual">
      <div class="hero__server-card">
        <div class="server-card__header">
          <div class="server-card__icon">
            <svg viewBox="0 0 24 24" style="width:24px;height:24px;stroke:currentColor;stroke-width:2;fill:none"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
          </div>
          <div>
            <div class="server-card__label">Servidor em destaque</div>
            <div class="server-card__name"><?php echo $_hero_plano ? View::e((string)$_hero_plano['name']) : 'VPS Enterprise'; ?></div>
          </div>
        </div>
        <div class="server-specs">
          <?php if ($_hero_plano && $_hram > 0): ?>
          <div class="spec-item"><div class="spec-item__val"><?php echo $_hram; ?> GB</div><div class="spec-item__key">RAM</div></div>
          <?php else: ?><div class="spec-item"><div class="spec-item__val">32 GB</div><div class="spec-item__key">RAM</div></div><?php endif; ?>
          <?php if ($_hero_plano && $_hvcpu > 0): ?>
          <div class="spec-item"><div class="spec-item__val"><?php echo $_hvcpu; ?> vCPU</div><div class="spec-item__key">Processamento</div></div>
          <?php else: ?><div class="spec-item"><div class="spec-item__val">16 vCPU</div><div class="spec-item__key">Processamento</div></div><?php endif; ?>
          <?php if ($_hero_plano && $_hdisco > 0): ?>
          <div class="spec-item"><div class="spec-item__val"><?php echo $_hdisco; ?> GB</div><div class="spec-item__key">SSD</div></div>
          <?php else: ?><div class="spec-item"><div class="spec-item__val">300 GB</div><div class="spec-item__key">SSD</div></div><?php endif; ?>
          <div class="spec-item"><div class="spec-item__val">Infra</div><div class="spec-item__key">Dedicada</div></div>
        </div>
        <div class="server-price">
          <div class="server-price__label">Total mensal (servidor + backup)</div>
          <div class="server-price__value">R$ <?php echo $_hero_plano ? number_format($_hprice, 0, ',', '.') : '1.497'; ?>+<span>/mês</span></div>
        </div>
      </div>
      <div class="hero__floater hero__floater--1">
        <div class="floater-icon floater-icon--green">
          <svg viewBox="0 0 24 24" style="width:18px;height:18px;stroke:#15803D;stroke-width:2;fill:none"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        </div>
        <div class="floater__text"><strong>DDoS Protection</strong><span>Ativada por padrão</span></div>
      </div>
      <div class="hero__floater hero__floater--2">
        <div class="floater-icon floater-icon--blue">
          <svg viewBox="0 0 24 24" style="width:18px;height:18px;stroke:var(--azs);stroke-width:2;fill:none"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
        </div>
        <div class="floater__text"><strong>99,9% Uptime</strong><span>SLA garantido</span></div>
      </div>
    </div>
  </div>
  <div class="hero__clients">
    <p>Empresas que confiam na <?php echo View::e($_nome); ?></p>
    <div class="clients-logos">
      <span>Startups</span><span>E-commerce</span><span>SaaS</span><span>Agências</span><span>Desenvolvedores</span>
    </div>
  </div>
</section>

<!-- STATS -->
<div class="statsbar">
  <div class="stats">
    <div class="stat"><h3>+5 anos</h3><p>Experiência no mercado de infraestrutura</p></div>
    <div class="stat"><h3>+200</h3><p>Clientes ativos em todo o Brasil</p></div>
    <div class="stat"><h3>99,9%</h3><p>SLA de uptime garantido em contrato</p></div>
    <div class="stat"><h3>6 dias</h3><p>Ativação rápida dos servidores</p></div>
  </div>
</div>

<!-- SOBRE -->
<section id="sobre">
  <div class="wrap">
    <div class="features-header">
      <div>
        <span class="eyebrow">Por que a <?php echo View::e($_nome); ?>?</span>
        <h2 style="margin-bottom:16px">A melhor infraestrutura<br>do Brasil</h2>
        <p class="lead">A <strong><?php echo View::e($_nome); ?></strong> é especializada em servidores dedicados, hospedagem web e infraestrutura digital. Tecnologia de ponta com suporte humanizado, custos transparentes e escalabilidade real.</p>
      </div>
      <div>
        <p class="lead" style="color:var(--ts);font-size:.95rem">Nossa infraestrutura é base para sistemas empresariais, bancos de dados, armazenamento de arquivos e soluções de continuidade de negócios em todo o Brasil.</p>
        <p style="font-size:.88rem;color:#637080;margin-top:14px;line-height:1.7">Reajuste previsível baseado no IGPM · Ativação em até 6 dias úteis · Tráfego ilimitado incluído</p>
      </div>
    </div>
    <div class="features-grid">
      <div class="feat-item"><div class="feat-item__icon"><svg viewBox="0 0 24 24"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg></div><div class="feat-item__title">Desempenho Superior</div><p class="feat-item__desc">Processadores Intel Xeon de última geração para máximo throughput e mínima latência.</p></div>
      <div class="feat-item"><div class="feat-item__icon"><svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div><div class="feat-item__title">Proteção DDoS Nativa</div><p class="feat-item__desc">Infraestrutura blindada contra ataques volumétricos. Incluída em todos os planos.</p></div>
      <div class="feat-item"><div class="feat-item__icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg></div><div class="feat-item__title">Hiperconectividade</div><p class="feat-item__desc">Portas de 1 a 10 Gbps com tráfego ilimitado. Dentro do hub de conectividade dos DCs.</p></div>
      <div class="feat-item"><div class="feat-item__icon"><svg viewBox="0 0 24 24"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg></div><div class="feat-item__title">Upgrades sem Burocracia</div><p class="feat-item__desc">Escale recursos com rapidez. Ajuste CPU, RAM e armazenamento sob demanda.</p></div>
      <div class="feat-item"><div class="feat-item__icon"><svg viewBox="0 0 24 24"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg></div><div class="feat-item__title">Hardware Exclusivo</div><p class="feat-item__desc">Controle total do servidor dedicado. Sem compartilhamento, sem vizinhos barulhentos.</p></div>
      <div class="feat-item"><div class="feat-item__icon"><svg viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div><div class="feat-item__title">Custos Transparentes</div><p class="feat-item__desc">Preços claros, sem taxas ocultas. Reajuste anual pelo IGPM, sempre previsível.</p></div>
    </div>
  </div>
</section>

<!-- DIFERENCIAIS -->
<section class="diff-sec">
  <div class="diff-header"><h2>Diferenciais dos Servidores Dedicados <?php echo View::e($_nome); ?></h2></div>
  <div class="diff-grid">
    <div class="diff-card animate">
      <div class="diff-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M12 2v4m0 12v4M4.93 4.93l2.83 2.83m8.48 8.48l2.83 2.83M2 12h4m12 0h4M4.93 19.07l2.83-2.83m8.48-8.48l2.83-2.83"/></svg></div>
      <h3 class="diff-title">Conectividade</h3>
      <p class="diff-desc">Conexão direta com grandes redes como Akamai, CloudFlare, Microsoft, Google, AWS e UPX. Capilaridade através dos maiores peerings nacionais do IX.br. Trânsito por múltiplas operadoras atuantes no Brasil e no mundo.</p>
    </div>
    <div class="diff-card animate">
      <div class="diff-icon"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg></div>
      <h3 class="diff-title">Console/KVM</h3>
      <p class="diff-desc">Painel intuitivo que fornece acesso externo ao servidor (display, teclado e mouse), possibilita a montagem de ISOs em drive virtual, permite ligar, desligar e reiniciar o servidor, entre outras opções.</p>
    </div>
    <div class="diff-card animate">
      <div class="diff-icon"><svg viewBox="0 0 24 24"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg></div>
      <h3 class="diff-title">Resiliência</h3>
      <p class="diff-desc">Servidores com grande quantidade de recursos para altas demandas de utilização sustentada ou durante picos inesperados, sem alterações na velocidade de resposta.</p>
    </div>
    <div class="diff-card animate">
      <div class="diff-icon"><svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg></div>
      <h3 class="diff-title">Desempenho I/O</h3>
      <p class="diff-desc">Baixíssima latência em operações de leitura e gravação, mesmo durante picos em cargas como bancos de dados complexos ou servidores de e-mail com grande fluxo de mensagens.</p>
    </div>
    <div class="diff-card animate">
      <div class="diff-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg></div>
      <h3 class="diff-title">Segurança a nível de rede</h3>
      <p class="diff-desc">VLAN privada para tráfego interno, VPN para integração segura com seu escritório, firewall dedicado para regras de segurança adequadas e link ponto a ponto para interligar escritórios (serviços adicionais).</p>
    </div>
    <div class="diff-card animate">
      <div class="diff-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 4.6a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 .33 1.65 1.65 0 0 0 10 1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg></div>
      <h3 class="diff-title">Backup diário</h3>
      <p class="diff-desc">Seus dados armazenados em ambiente externo e à prova de falhas. Segurança total em casos de remoção indevida, corrompimento de banco de dados, ransomware, entre outros (serviço adicional).</p>
    </div>
  </div>
</section>

<!-- PLANOS -->
<section class="plans-sec" id="planos">
  <div class="wrap">
    <h2 style="text-align:center;margin-bottom:12px">Escolha o ideal para seu projeto</h2>
    <p style="text-align:center;color:var(--ts);font-size:16px;max-width:640px;margin:0 auto 24px">Servidores VPS com recursos dedicados e escaláveis. Todos os planos incluem proteção DDoS e SSL gratuito.</p>
    <div class="compare-btn-container">
      <button class="compare-btn" onclick="openCompareModal()">
        <svg viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
        Comparar todos os planos
      </button>
    </div>
    <div class="carousel-container">
      <div class="carousel-wrapper">
        <div class="carousel-track" id="carouselTrack">
          <?php if (!empty($_planos)): ?>
          <?php foreach ($_planos as $_i => $_p):
            $_specs = json_decode((string)($_p['specs_json'] ?? ''), true) ?: [];
            $_vcpu  = (int)($_specs['vcpu'] ?? $_specs['cpu'] ?? 0);
            $_ram   = (int)($_specs['ram_gb'] ?? 0);
            $_disco = (int)($_specs['disco_gb'] ?? $_specs['storage_gb'] ?? 0);
            $_price = (float)$_p['price'];
            $_pid   = (int)$_p['id'];
            $_addons = is_array($_p['addons'] ?? null) ? $_p['addons'] : [];
            $_badge = (string)($_p['badge'] ?? '');
          ?>
          <div class="plan-card" id="pcard-<?php echo $_pid; ?>">
            <?php if ($_badge !== ''): ?><div class="plan-badge"><?php echo View::e($_badge); ?></div><?php endif; ?>
            <div class="plan-name"><?php echo View::e((string)$_p['name']); ?></div>
            <div class="plan-desc"><?php echo View::e((string)($_p['description'] ?? '')); ?></div>
            <div class="plan-price"><small>R$</small> <?php echo number_format($_price, 0, ',', '.'); ?></div>
            <div class="plan-period">por mês</div>
            <ul class="plan-features">
              <?php if ($_vcpu > 0): ?><li><?php echo $_vcpu; ?> vCPU</li><?php endif; ?>
              <?php if ($_ram > 0): ?><li><?php echo $_ram; ?> GB RAM</li><?php endif; ?>
              <?php if ($_disco > 0): ?><li><?php echo $_disco; ?> GB SSD</li><?php endif; ?>
              <li>Proteção DDoS</li>
              <li>SSL gratuito</li>
              <li>Suporte especializado</li>
            </ul>
            <?php if (!empty($_addons)): ?>
            <div class="addons-sec">
              <h3>Serviços adicionais</h3>
              <?php foreach ($_addons as $_ai => $_a):
                $_aprice = (float)$_a['price'];
              ?>
              <div class="addon-item" onclick="toggleAddon(this,<?php echo $_pid; ?>,<?php echo $_aprice; ?>)">
                <div class="addon-check"></div>
                <div class="addon-info">
                  <div class="addon-name"><?php echo View::e((string)$_a['name']); ?></div>
                  <?php if (!empty($_a['description'])): ?><div class="addon-desc"><?php echo View::e((string)$_a['description']); ?></div><?php endif; ?>
                </div>
                <div class="addon-price">+ R$ <?php echo number_format($_aprice, 0, ',', '.'); ?></div>
              </div>
              <?php endforeach; ?>
            </div>
            <div class="total-calc">
              <div class="total-label">Valor total</div>
              <div class="total-value">R$ <span id="total-<?php echo $_pid; ?>"><?php echo number_format($_price, 0, ',', '.'); ?></span></div>
            </div>
            <?php endif; ?>
            <a href="/cliente/criar-conta?plano=<?php echo $_pid; ?>" class="plan-cta">Contratar agora</a>
          </div>
          <?php endforeach; ?>
          <?php else: ?>
          <div style="padding:48px;text-align:center;color:var(--ts);font-size:14px;width:100%">Planos em breve. <a href="/contato" style="color:var(--az)">Entre em contato</a> para saber mais.</div>
          <?php endif; ?>
        </div>
      </div>
      <div class="carousel-btn carousel-btn--prev" onclick="moveCarousel(-1)">
        <svg viewBox="0 0 24 24" fill="none"><path d="M15 18l-6-6 6-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </div>
      <div class="carousel-btn carousel-btn--next" onclick="moveCarousel(1)">
        <svg viewBox="0 0 24 24" fill="none"><path d="M9 18l6-6-6-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </div>
      <div class="carousel-dots" id="carouselDots"></div>
    </div>
    <div class="help-card">
      <div class="help-content">
        <h3 class="help-title">Precisando de ajuda para decidir ou mais recursos?</h3>
        <p class="help-subtitle">Fale conosco para um plano personalizado</p>
      </div>
      <div class="help-action">
        <a href="/contato" class="help-btn">Falar com Especialista</a>
      </div>
    </div>
  </div>
</section>

<!-- CONDIÇÕES -->
<section id="condicoes">
  <div class="wrap">
    <div style="margin-bottom:40px">
      <span class="eyebrow">Transparência total</span>
      <h2 style="margin-bottom:14px">Condições Gerais dos Serviços</h2>
      <p class="lead">Tudo o que você precisa saber antes de contratar. Sem letras miúdas, sem surpresas na fatura.</p>
    </div>
    <div class="conds-grid">
      <div class="cond"><div class="cond__num">01</div><p>Todos os valores incluem os devidos <strong>impostos conforme a legislação vigente</strong>.</p></div>
      <div class="cond"><div class="cond__num">02</div><p>Serviços <strong>pré-pagos</strong>. Cobrança inicia após a ativação. Primeira fatura proporcional com vencimento em <strong>5 dias</strong>.</p></div>
      <div class="cond"><div class="cond__num">03</div><p>Faturas mensais enviadas com <strong>15 dias de antecedência</strong>, pagamento exclusivamente por boleto bancário.</p></div>
      <div class="cond"><div class="cond__num">04</div><p>Reajuste anual baseado no <strong>IGPM</strong> ao final de cada período de 12 meses de vigência.</p></div>
      <div class="cond"><div class="cond__num">05</div><p><strong>Renovação automática</strong> por períodos iguais se não houver contestação com <strong>60 dias</strong> de antecedência.</p></div>
      <div class="cond"><div class="cond__num">06</div><p>Rescisão antecipada: multa de <strong>50% do valor mensal × meses restantes</strong> acordados na proposta.</p></div>
      <div class="cond"><div class="cond__num">07</div><p><strong>Prazo de ativação SP1</strong>: 1 servidor = 6 dias úteis. Regiões PR1/CE1: +5 dias úteis adicionais.</p></div>
      <div class="cond"><div class="cond__num">08</div><p><strong>Backup BaaS</strong> ativado em até 4 dias úteis adicionais ao prazo do servidor contratado.</p></div>
      <div class="cond"><div class="cond__num">09</div><p>Cancelamento ou downgrade deve ser <strong>comunicado com 60 dias</strong> de antecedência da suspensão.</p></div>
      <div class="cond"><div class="cond__num">10</div><p>É estritamente proibida a <strong>hospedagem de jogos</strong> para garantia de performance de conectividade.</p></div>
    </div>
  </div>
</section>

<!-- DEPOIMENTOS -->
<section class="sec-alt">
  <div class="wrap">
    <div style="text-align:center;margin-bottom:52px">
      <span class="eyebrow">Depoimentos</span>
      <h2>O que nossos clientes dizem</h2>
    </div>
    <div class="test-grid">
      <div class="test-card">
        <p class="test-card__text">A <?php echo View::e($_nome); ?> nos deu todo o suporte quando enfrentamos um incidente crítico. Na época tínhamos todo o data center on-premise interno, e agora toda nossa infraestrutura está com eles. Nossa parceria já tem mais de 2 anos e a ideia é continuar por muito tempo. Estamos muito felizes com o atendimento prestado.</p>
        <div class="test-card__author"><div class="test-card__av">ES</div><div><div class="test-card__name">Edson Souza</div><div class="test-card__role">Gerente de Tecnologia — Ipanema Queijos</div></div></div>
      </div>
      <div class="test-card">
        <p class="test-card__text">A <?php echo View::e($_nome); ?> é a principal fornecedora de tecnologia da Akahosting. Fundamentamos uma parceria e em menos de um ano conseguimos ver o extremo sucesso em nossa operação. Graças a eles nossa empresa conseguiu alavancar muito os negócios. Somos gratos pelo suporte em todas as ocasiões.</p>
        <div class="test-card__author"><div class="test-card__av">MP</div><div><div class="test-card__name">Marcio Polonio</div><div class="test-card__role">CEO — Akahosting</div></div></div>
      </div>
      <div class="test-card">
        <p class="test-card__text">Trabalhávamos com provedores de servidor fora do Brasil, conhecemos a <?php echo View::e($_nome); ?> e trouxemos toda nossa infraestrutura para cá. Estamos com eles há alguns anos e nossa evolução conjunta é constante. Estamos muito satisfeitos e planejamos estender essa parceria cada vez mais.</p>
        <div class="test-card__author"><div class="test-card__av">LB</div><div><div class="test-card__name">Leonardo Barros</div><div class="test-card__role">Diretor Executivo — Reposit</div></div></div>
      </div>
      <div class="test-card">
        <p class="test-card__text">A migração para a <?php echo View::e($_nome); ?> foi decisiva para nossa operação. O processo foi transparente, o time técnico sempre disponível e o desempenho superou nossas expectativas. Com o servidor dedicado reduzimos latência e aumentamos capacidade sem aumentar os custos.</p>
        <div class="test-card__author"><div class="test-card__av">RN</div><div><div class="test-card__name">Rafael Neves</div><div class="test-card__role">CTO — TechStream</div></div></div>
      </div>
    </div>
  </div>
</section>

<!-- CTA FINAL -->
<section class="cta-sec" id="contato">
  <div class="wrap">
    <div class="cta-sec__inner">
      <span class="eyebrow" style="color:#c7d2fe">Pronto para começar?</span>
      <h2>Ative seu servidor em<br><em>até 6 dias úteis</em></h2>
      <p>Entre em contato agora e receba uma proposta personalizada. Nossa equipe técnica está pronta para atender você.</p>
      <div class="cta-btns">
        <a href="/contato" class="btn btn-p" style="padding:14px 26px;font-size:.9rem">✉️ Solicitar Proposta por E-mail</a>
        <a href="/contato" class="btn btn-s" style="padding:14px 26px;font-size:.9rem">💬 Falar com Consultor</a>
      </div>
    </div>
  </div>
</section>

<!-- MODAL DE COMPARAÇÃO -->
<div class="compare-modal" id="compareModal">
  <div class="compare-modal__content">
    <div class="compare-modal__header">
      <h2 class="compare-modal__title">Comparar todos os planos</h2>
      <button class="compare-modal__close" onclick="closeCompareModal()">
        <svg viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </button>
    </div>
    <div class="compare-grid">
      <?php foreach ($_planos as $_i => $_p):
        $_specs = json_decode((string)($_p['specs_json'] ?? ''), true) ?: [];
        $_vcpu  = (int)($_specs['vcpu'] ?? $_specs['cpu'] ?? 0);
        $_ram   = (int)($_specs['ram_gb'] ?? 0);
        $_disco = (int)($_specs['disco_gb'] ?? $_specs['storage_gb'] ?? 0);
        $_price = (float)$_p['price'];
        $_badge = (string)($_p['badge'] ?? '');
        $_featured = $_badge !== '';
      ?>
      <div class="compare-card <?php echo $_featured ? 'featured' : ''; ?>">
        <?php if ($_badge !== ''): ?><div class="compare-card__badge"><?php echo View::e($_badge); ?></div><?php endif; ?>
        <div class="compare-card__name"><?php echo View::e((string)$_p['name']); ?></div>
        <div class="compare-card__price"><small>R$</small> <?php echo number_format($_price, 0, ',', '.'); ?></div>
        <div class="compare-card__period">por mês</div>
        <ul class="compare-card__features">
          <?php if ($_vcpu > 0): ?><li><?php echo $_vcpu; ?> vCPU</li><?php endif; ?>
          <?php if ($_ram > 0): ?><li><?php echo $_ram; ?> GB RAM</li><?php endif; ?>
          <?php if ($_disco > 0): ?><li><?php echo $_disco; ?> GB SSD</li><?php endif; ?>
          <li>Proteção DDoS</li>
          <li>SSL gratuito</li>
          <li>Suporte especializado</li>
        </ul>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- FOOTER -->
<footer class="footer">
  <div class="footer__inner">
    <div class="footer__brand">
      <?php if ($_logo !== ''): ?>
        <img src="<?php echo View::e($_logo); ?>" alt="<?php echo View::e($_nome); ?>">
      <?php else: ?>
        <span class="footer__brand-name"><?php echo View::e($_nome); ?></span>
      <?php endif; ?>
    </div>
    <div class="footer__copy"><?php echo View::e(SistemaConfig::copyrightText()); ?> · <?php echo View::e($_nome); ?> v<?php echo View::e(SistemaConfig::versao()); ?></div>
  </div>
</footer>

<script>
// Smooth scroll
document.querySelectorAll('a[href^="#"]').forEach(function(a){
  a.addEventListener('click',function(e){
    var t=document.querySelector(this.getAttribute('href'));
    if(t){e.preventDefault();window.scrollTo({top:t.getBoundingClientRect().top+scrollY-68,behavior:'smooth'});}
  });
});

// Animate diff cards on scroll
var observerDiff=new IntersectionObserver(function(entries){
  entries.forEach(function(entry){if(entry.isIntersecting){entry.target.classList.add('animate');observerDiff.unobserve(entry.target);}});
},{threshold:.1,rootMargin:'0px 0px -50px 0px'});
document.querySelectorAll('.diff-card').forEach(function(c){observerDiff.observe(c);});

// Carousel
var currentSlide=0;
var track=document.getElementById('carouselTrack');
var originalCards=Array.from(document.querySelectorAll('.plan-card'));
var totalSlides=originalCards.length;
var dotsContainer=document.getElementById('carouselDots');

originalCards.forEach(function(card){track.appendChild(card.cloneNode(true));});

var totalDots=Math.ceil(totalSlides/2);
for(var i=0;i<totalDots;i++){
  var dot=document.createElement('div');
  dot.className='carousel-dot'+(i===0?' active':'');
  dot.onclick=(function(idx){return function(){goToSlide(idx*2);};})(i);
  dotsContainer.appendChild(dot);
}

function updateCarousel(smooth){
  var wrapper=document.querySelector('.carousel-wrapper');
  var ww=wrapper.offsetWidth;
  track.style.transition=smooth?'transform .5s cubic-bezier(.4,0,.2,1)':'none';
  track.style.transform='translateX(-'+(currentSlide*(ww+24))+'px)';
  var activeDot=Math.floor((currentSlide%totalSlides)/2);
  document.querySelectorAll('.carousel-dot').forEach(function(d,idx){d.classList.toggle('active',idx===activeDot);});
}
function moveCarousel(dir){
  currentSlide+=dir;
  updateCarousel(true);
  setTimeout(function(){
    if(currentSlide>=totalSlides){currentSlide=0;updateCarousel(false);}
    else if(currentSlide<0){currentSlide=totalSlides-1;updateCarousel(false);}
  },500);
}
function goToSlide(idx){currentSlide=idx;updateCarousel(true);}
window.addEventListener('resize',function(){updateCarousel(false);});
setTimeout(function(){updateCarousel(false);},100);

// Addons
var planBase={<?php foreach($_planos as $_p): ?><?php echo (int)$_p['id']; ?>:<?php echo (float)$_p['price']; ?>,<?php endforeach; ?>};
var planAddons={<?php foreach($_planos as $_p): ?><?php echo (int)$_p['id']; ?>:[],<?php endforeach; ?>};

function toggleAddon(el,planId,addonPrice){
  el.classList.toggle('selected');
  var sel=el.classList.contains('selected');
  el.querySelector('.addon-check').innerHTML=sel?'✓':'';
  if(sel){planAddons[planId].push(addonPrice);}
  else{var idx=planAddons[planId].indexOf(addonPrice);if(idx>-1)planAddons[planId].splice(idx,1);}
  var total=(planBase[planId]||0)+planAddons[planId].reduce(function(s,p){return s+p;},0);
  var el2=document.getElementById('total-'+planId);
  if(el2)el2.textContent=total.toLocaleString('pt-BR');
}

// Modal
function openCompareModal(){document.getElementById('compareModal').classList.add('active');document.body.style.overflow='hidden';}
function closeCompareModal(){document.getElementById('compareModal').classList.remove('active');document.body.style.overflow='';}
document.getElementById('compareModal').addEventListener('click',function(e){if(e.target===this)closeCompareModal();});
document.addEventListener('keydown',function(e){if(e.key==='Escape')closeCompareModal();});
</script>
</body>
</html>
