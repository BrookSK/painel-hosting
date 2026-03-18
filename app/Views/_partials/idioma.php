<?php

declare(strict_types=1);

use LRV\Core\I18n;
use LRV\Core\View;

$idiomaAtual = I18n::idioma();

$uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
$parts = parse_url($uri);
$parts = is_array($parts) ? $parts : [];
$path = (string) ($parts['path'] ?? '/');

$query = [];
if (isset($parts['query'])) {
    parse_str((string) $parts['query'], $query);
    if (!is_array($query)) {
        $query = [];
    }
}

unset($query['lang']);

$makeHref = static function (string $lang) use ($path, $query): string {
    $q = $query;
    $q['lang'] = $lang;
    $qs = http_build_query($q);
    return $qs !== '' ? ($path . '?' . $qs) : $path;
};

$opcoes = [
    'pt-BR' => 'PT',
    'en-US' => 'EN',
    'es-ES' => 'ES',
];

?>
<span style="display:inline-flex; gap:10px; align-items:center;">
  <?php foreach ($opcoes as $codigo => $label): ?>
    <?php $href = $makeHref($codigo); ?>
    <?php $ativo = $codigo === $idiomaAtual; ?>
    <a href="<?php echo View::e($href); ?>" style="<?php echo $ativo ? 'font-weight:700; text-decoration:underline;' : 'opacity:.9;'; ?>">
      <?php echo View::e($label); ?>
    </a>
  <?php endforeach; ?>
</span>
