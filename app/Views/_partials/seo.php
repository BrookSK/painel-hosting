<?php
/**
 * Partial SEO — inclua dentro do <head> de páginas públicas.
 * Variáveis opcionais que podem ser definidas antes do include:
 *   $seo_titulo      — título da página (sobrescreve o padrão)
 *   $seo_descricao   — descrição da página
 *   $seo_canonical   — URL canônica completa (ex: https://site.com/pagina)
 *   $seo_noindex     — true para forçar noindex nesta página
 */
declare(strict_types=1);
use LRV\Core\SistemaConfig;
use LRV\Core\View;

$_seo_nome      = SistemaConfig::nome();
$_seo_empresa   = SistemaConfig::empresaNome();
$_seo_titulo_cfg = SistemaConfig::seoTitulo();
$_seo_desc_cfg   = SistemaConfig::seoDescricao();
$_seo_kw         = SistemaConfig::seoPalavrasChave();
$_seo_og_img     = SistemaConfig::seoOgImage();
$_seo_robots     = isset($seo_noindex) && $seo_noindex ? 'noindex, nofollow' : SistemaConfig::seoRobots();
$_seo_ga_id      = SistemaConfig::seoGoogleAnalyticsId();
$_seo_canonical_base = SistemaConfig::seoCanonicalBase();
$_seo_schema_type    = SistemaConfig::seoSchemaType();
$_favicon        = SistemaConfig::faviconUrl();

// Título final: prioridade → variável local → config → nome do sistema
$_titulo_final = isset($seo_titulo) && $seo_titulo !== ''
    ? $seo_titulo
    : ($_seo_titulo_cfg !== '' ? $_seo_titulo_cfg : $_seo_nome . ' — Infraestrutura Cloud');

// Descrição final
$_desc_final = isset($seo_descricao) && $seo_descricao !== ''
    ? $seo_descricao
    : ($_seo_desc_cfg !== '' ? $_seo_desc_cfg : '');

// Canonical
$_canonical = '';
if (isset($seo_canonical) && $seo_canonical !== '') {
    $_canonical = $seo_canonical;
} elseif ($_seo_canonical_base !== '') {
    $_uri = strtok((string)($_SERVER['REQUEST_URI'] ?? '/'), '?');
    $_canonical = $_seo_canonical_base . $_uri;
}
?>
<title><?php echo View::e($_titulo_final); ?></title>
<meta name="robots" content="<?php echo View::e($_seo_robots); ?>" />
<?php if ($_desc_final !== ''): ?>
<meta name="description" content="<?php echo View::e($_desc_final); ?>" />
<?php endif; ?>
<?php if ($_seo_kw !== ''): ?>
<meta name="keywords" content="<?php echo View::e($_seo_kw); ?>" />
<?php endif; ?>
<?php if ($_canonical !== ''): ?>
<link rel="canonical" href="<?php echo View::e($_canonical); ?>" />
<?php endif; ?>
<?php if ($_favicon !== ''): ?>
<link rel="icon" href="<?php echo View::e($_favicon); ?>" />
<link rel="apple-touch-icon" href="<?php echo View::e($_favicon); ?>" />
<?php endif; ?>

<!-- Open Graph -->
<meta property="og:type" content="website" />
<meta property="og:site_name" content="<?php echo View::e($_seo_empresa); ?>" />
<meta property="og:title" content="<?php echo View::e($_titulo_final); ?>" />
<?php if ($_desc_final !== ''): ?>
<meta property="og:description" content="<?php echo View::e($_desc_final); ?>" />
<?php endif; ?>
<?php if ($_canonical !== ''): ?>
<meta property="og:url" content="<?php echo View::e($_canonical); ?>" />
<?php endif; ?>
<?php if ($_seo_og_img !== ''): ?>
<meta property="og:image" content="<?php echo View::e($_seo_og_img); ?>" />
<meta property="og:image:width" content="1200" />
<meta property="og:image:height" content="630" />
<?php endif; ?>

<!-- Twitter Card -->
<meta name="twitter:card" content="<?php echo $_seo_og_img !== '' ? 'summary_large_image' : 'summary'; ?>" />
<meta name="twitter:title" content="<?php echo View::e($_titulo_final); ?>" />
<?php if ($_desc_final !== ''): ?>
<meta name="twitter:description" content="<?php echo View::e($_desc_final); ?>" />
<?php endif; ?>
<?php if ($_seo_og_img !== ''): ?>
<meta name="twitter:image" content="<?php echo View::e($_seo_og_img); ?>" />
<?php endif; ?>

<!-- Schema.org JSON-LD -->
<?php if ($_seo_canonical_base !== ''): ?>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "<?php echo View::e($_seo_schema_type); ?>",
  "name": "<?php echo View::e($_seo_empresa); ?>",
  "url": "<?php echo View::e($_seo_canonical_base); ?>",
  <?php if ($_seo_og_img !== ''): ?>
  "logo": "<?php echo View::e($_seo_og_img); ?>",
  <?php endif; ?>
  "description": "<?php echo View::e($_desc_final); ?>"
}
</script>
<?php endif; ?>

<?php if ($_seo_ga_id !== ''): ?>
<!-- Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo View::e($_seo_ga_id); ?>"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '<?php echo View::e($_seo_ga_id); ?>');
</script>
<?php endif; ?>
