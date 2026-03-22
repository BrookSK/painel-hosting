<?php
/**
 * Layout compartilhado para páginas de soluções.
 * Variáveis esperadas: $sol_prefix, $sol_icon, $sol_feats (int), $sol_cta_link
 */
declare(strict_types=1);
use LRV\Core\I18n;
use LRV\Core\View;
$_prefix = $sol_prefix ?? 'vps';
$_icon   = $sol_icon ?? '🖥️';
$_feats  = $sol_feats ?? 6;
$_cta    = $sol_cta_link ?? '/cliente/criar-conta';
?>
<!DOCTYPE html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title><?php echo View::e(I18n::t("sol.{$_prefix}_titulo")); ?></title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
html{overflow-x:hidden}
body{font-family:system-ui,-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:#060d1f;color:#fff;line-height:1.6}
a{text-decoration:none}
a:hover{text-decoration:none}

.sol-hero{padding:100px 20px 60px;text-align:center;background:linear-gradient(180deg,#0b1c3d 0%,#060d1f 100%)}
.sol-hero-icon{font-size:48px;margin-bottom:16px}
.sol-hero h1{font-size:clamp(28px,5vw,42px);font-weight:800;margin-bottom:14px;letter-spacing:-.02em}
.sol-hero p{font-size:clamp(15px,2.5vw,18px);color:rgba(255,255,255,.6);max-width:640px;margin:0 auto}

.sol-section{max-width:1100px;margin:0 auto;padding:60px 20px}
.sol-section-title{text-align:center;font-size:11.5px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#818cf8;margin-bottom:40px}

.sol-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px}
.sol-card{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:16px;padding:28px 24px;transition:border-color .2s,transform .2s}
.sol-card:hover{border-color:rgba(129,140,248,.3);transform:translateY(-2px)}
.sol-card h3{font-size:16px;font-weight:700;margin-bottom:6px;color:#fff}
.sol-card p{font-size:13.5px;color:rgba(255,255,255,.5);line-height:1.5}
</style>
</head>
<body>
<?php require __DIR__ . '/../_partials/navbar-publica.php'; ?>

<section class="sol-hero">
  <div class="sol-hero-icon"><?php echo $_icon; ?></div>
  <h1><?php echo View::e(I18n::t("sol.{$_prefix}_titulo")); ?></h1>
  <p><?php echo View::e(I18n::t("sol.{$_prefix}_subtitulo")); ?></p>
</section>

<section class="sol-section">
  <div class="sol-section-title"><?php echo View::e(I18n::t('sol.section_funcionalidades')); ?></div>
  <div class="sol-grid">
    <?php for ($i = 1; $i <= $_feats; $i++): ?>
    <div class="sol-card">
      <h3><?php echo View::e(I18n::t("sol.{$_prefix}_feat{$i}_titulo")); ?></h3>
      <p><?php echo View::e(I18n::t("sol.{$_prefix}_feat{$i}_desc")); ?></p>
    </div>
    <?php endfor; ?>
  </div>
</section>

<section class="sol-section" style="padding-top:0">
  <div class="sol-section-title"><?php echo View::e(I18n::t('sol.section_como_funciona')); ?></div>
  <div class="sol-grid" style="grid-template-columns:repeat(3,1fr)">
    <?php for ($s = 1; $s <= 3; $s++): ?>
    <div class="sol-card" style="text-align:center">
      <h3><?php echo View::e(I18n::t("sol.passo{$s}")); ?></h3>
      <p><?php echo View::e(I18n::t("sol.passo{$s}_desc")); ?></p>
    </div>
    <?php endfor; ?>
  </div>
</section>

<section style="text-align:center;padding:40px 20px 80px">
  <a href="<?php echo View::e($_cta); ?>" class="sol-cta-btn"><?php echo View::e(I18n::t("sol.{$_prefix}_cta")); ?></a>
</section>

<style>
.sol-cta-btn{display:inline-block;padding:14px 36px;background:linear-gradient(135deg,#4F46E5,#7C3AED);color:#fff;font-size:15px;font-weight:700;border-radius:12px;transition:opacity .15s,transform .1s}
.sol-cta-btn:hover{opacity:.9;transform:translateY(-2px)}
@media(max-width:768px){
  .sol-grid{grid-template-columns:1fr}
}
@media(min-width:769px) and (max-width:1024px){
  .sol-grid{grid-template-columns:repeat(2,1fr)}
}
</style>

<?php require __DIR__ . '/../_partials/footer.php'; ?>
</body>
</html>
