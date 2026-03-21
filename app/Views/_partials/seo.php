<?php
/**
 * Partial SEO — inclua dentro do <head> de páginas públicas.
 * Variáveis opcionais que podem ser definidas antes do include:
 *   $seo_titulo    — título da página (sobrescreve o padrão do banco)
 *   $seo_descricao — descrição da página
 *   $seo_canonical — URL canônica completa
 *   $seo_noindex   — true para forçar noindex nesta página
 */
$_seo_nome           = \LRV\Core\SistemaConfig::nome();
$_seo_empresa        = \LRV\Core\SistemaConfig::empresaNome();
$_seo_titulo_cfg     = \LRV\Core\SistemaConfig::seoTitulo();
$_seo_desc_cfg       = \LRV\Core\SistemaConfig::seoDescricao();
$_seo_kw             = \LRV\Core\SistemaConfig::seoPalavrasChave();
$_seo_og_img         = \LRV\Core\SistemaConfig::seoOgImage();
$_seo_robots         = (!empty($seo_noindex)) ? 'noindex, nofollow' : \LRV\Core\SistemaConfig::seoRobots();
$_seo_ga_id          = \LRV\Core\SistemaConfig::seoGoogleAnalyticsId();
$_seo_canonical_base = \LRV\Core\SistemaConfig::seoCanonicalBase();
$_seo_schema_type    = \LRV\Core\SistemaConfig::seoSchemaType();
$_seo_favicon        = \LRV\Core\SistemaConfig::faviconUrl();

if (!defined('_SEO_PARTIAL_LOADED')) define('_SEO_PARTIAL_LOADED', true);

$_titulo_final = (!empty($seo_titulo))
    ? $seo_titulo
    : ($_seo_titulo_cfg !== '' ? $_seo_titulo_cfg : $_seo_nome . ' — Infraestrutura Cloud');

$_desc_final = (!empty($seo_descricao))
    ? $seo_descricao
    : ($_seo_desc_cfg !== '' ? $_seo_desc_cfg : '');

$_canonical = '';
if (!empty($seo_canonical)) {
    $_canonical = $seo_canonical;
} elseif ($_seo_canonical_base !== '') {
    $_seo_uri   = strtok((string)($_SERVER['REQUEST_URI'] ?? '/'), '?');
    $_canonical = $_seo_canonical_base . $_seo_uri;
}
?>
<title><?php echo \LRV\Core\View::e($_titulo_final); ?></title>
<meta name="robots" content="<?php echo \LRV\Core\View::e($_seo_robots); ?>" />
<?php if ($_desc_final !== ''): ?>
<meta name="description" content="<?php echo \LRV\Core\View::e($_desc_final); ?>" />
<?php endif; ?>
<?php if ($_seo_kw !== ''): ?>
<meta name="keywords" content="<?php echo \LRV\Core\View::e($_seo_kw); ?>" />
<?php endif; ?>
<?php if ($_canonical !== ''): ?>
<link rel="canonical" href="<?php echo \LRV\Core\View::e($_canonical); ?>" />
<?php endif; ?>
<?php if ($_seo_favicon !== ''): ?>
<link rel="icon" href="<?php echo \LRV\Core\View::e($_seo_favicon); ?>" />
<link rel="apple-touch-icon" href="<?php echo \LRV\Core\View::e($_seo_favicon); ?>" />
<?php endif; ?>
<meta property="og:type" content="website" />
<meta property="og:site_name" content="<?php echo \LRV\Core\View::e($_seo_empresa); ?>" />
<meta property="og:title" content="<?php echo \LRV\Core\View::e($_titulo_final); ?>" />
<?php if ($_desc_final !== ''): ?>
<meta property="og:description" content="<?php echo \LRV\Core\View::e($_desc_final); ?>" />
<?php endif; ?>
<?php if ($_canonical !== ''): ?>
<meta property="og:url" content="<?php echo \LRV\Core\View::e($_canonical); ?>" />
<?php endif; ?>
<?php if ($_seo_og_img !== ''): ?>
<meta property="og:image" content="<?php echo \LRV\Core\View::e($_seo_og_img); ?>" />
<meta property="og:image:width" content="1200" />
<meta property="og:image:height" content="630" />
<?php endif; ?>
<meta name="twitter:card" content="<?php echo $_seo_og_img !== '' ? 'summary_large_image' : 'summary'; ?>" />
<meta name="twitter:title" content="<?php echo \LRV\Core\View::e($_titulo_final); ?>" />
<?php if ($_desc_final !== ''): ?>
<meta name="twitter:description" content="<?php echo \LRV\Core\View::e($_desc_final); ?>" />
<?php endif; ?>
<?php if ($_seo_og_img !== ''): ?>
<meta name="twitter:image" content="<?php echo \LRV\Core\View::e($_seo_og_img); ?>" />
<?php endif; ?>
<?php if ($_seo_canonical_base !== ''): ?>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "<?php echo \LRV\Core\View::e($_seo_schema_type); ?>",
  "name": "<?php echo \LRV\Core\View::e($_seo_empresa); ?>",
  "url": "<?php echo \LRV\Core\View::e($_seo_canonical_base); ?>",
  "description": "<?php echo \LRV\Core\View::e($_desc_final); ?>"<?php if ($_seo_og_img !== ''): ?>,
  "logo": "<?php echo \LRV\Core\View::e($_seo_og_img); ?>"<?php endif; ?>
}
</script>
<?php endif; ?>
<?php if ($_seo_ga_id !== ''): ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo \LRV\Core\View::e($_seo_ga_id); ?>"></script>
<script>
  window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}
  gtag('js',new Date());gtag('config','<?php echo \LRV\Core\View::e($_seo_ga_id); ?>');
</script>
<?php endif; ?>
