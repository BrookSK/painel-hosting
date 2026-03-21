<?php
declare(strict_types=1);
use LRV\Core\I18n;
use LRV\Core\View;

$idiomaAtual = I18n::idioma();

$uri   = (string) ($_SERVER['REQUEST_URI'] ?? '/');
$parts = parse_url($uri);
$parts = is_array($parts) ? $parts : [];
$path  = (string) ($parts['path'] ?? '/');

$query = [];
if (isset($parts['query'])) {
    parse_str((string) $parts['query'], $query);
    if (!is_array($query)) $query = [];
}
unset($query['lang']);

$makeHref = static function (string $lang) use ($path, $query): string {
    $q = $query;
    $q['lang'] = $lang;
    $qs = http_build_query($q);
    return $qs !== '' ? ($path . '?' . $qs) : $path;
};

$bandeiras = [
    'pt-BR' => '<svg width="20" height="14" viewBox="0 0 20 14" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><rect width="20" height="14" rx="2" fill="#009C3B"/><polygon points="10,1.5 18.5,7 10,12.5 1.5,7" fill="#FEDF00"/><circle cx="10" cy="7" r="3.2" fill="#002776"/><path d="M7 7.4 Q10 5.8 13 7.4" stroke="#fff" stroke-width=".7" fill="none"/></svg>',
    'en-US' => '<svg width="20" height="14" viewBox="0 0 20 14" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><rect width="20" height="14" rx="2" fill="#B22234"/><rect y="1.08" width="20" height="1.08" fill="#fff"/><rect y="3.23" width="20" height="1.08" fill="#fff"/><rect y="5.38" width="20" height="1.08" fill="#fff"/><rect y="7.54" width="20" height="1.08" fill="#fff"/><rect y="9.69" width="20" height="1.08" fill="#fff"/><rect y="11.85" width="20" height="1.08" fill="#fff"/><rect width="8" height="7.54" rx="0" fill="#3C3B6E"/></svg>',
    'es-ES' => '<svg width="20" height="14" viewBox="0 0 20 14" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><rect width="20" height="14" rx="2" fill="#AA151B"/><rect y="3.5" width="20" height="7" fill="#F1BF00"/></svg>',
];

$labels  = ['pt-BR' => 'Português', 'en-US' => 'English', 'es-ES' => 'Español'];
$codigos = ['pt-BR' => 'PT', 'en-US' => 'EN', 'es-ES' => 'ES'];

$uid = 'lang_' . substr(md5($path . $idiomaAtual . uniqid()), 0, 8);
?>
<div class="lang-dropdown" id="<?php echo $uid; ?>">
  <button class="lang-trigger" onclick="toggleLang('<?php echo $uid; ?>')" aria-haspopup="true" aria-expanded="false" type="button">
    <?php echo $bandeiras[$idiomaAtual] ?? ''; ?>
    <span class="lang-code"><?php echo View::e($codigos[$idiomaAtual] ?? 'PT'); ?></span>
    <svg class="lang-chevron" width="12" height="12" viewBox="0 0 12 12" fill="none" aria-hidden="true">
      <path d="M3 4.5l3 3 3-3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
  </button>
  <div class="lang-menu" role="menu">
    <?php foreach ($bandeiras as $codigo => $svg):
      $ativo = $codigo === $idiomaAtual;
    ?>
    <a href="<?php echo View::e($makeHref($codigo)); ?>"
       class="lang-option<?php echo $ativo ? ' lang-option-ativo' : ''; ?>"
       role="menuitem">
      <?php echo $svg; ?>
      <span><?php echo View::e($labels[$codigo]); ?></span>
      <?php if ($ativo): ?>
        <svg class="lang-check" width="12" height="12" viewBox="0 0 12 12" fill="none" aria-hidden="true">
          <path d="M2 6l3 3 5-5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      <?php endif; ?>
    </a>
    <?php endforeach; ?>
  </div>
</div>

<?php if (empty($GLOBALS['_lang_dropdown_assets_loaded'])): $GLOBALS['_lang_dropdown_assets_loaded'] = true; ?>
<style>
.lang-dropdown { position: relative; display: inline-block; }
.lang-trigger {
  display: inline-flex; align-items: center; gap: 6px;
  background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.18);
  color: #fff; border-radius: 8px; padding: 5px 10px;
  font-size: 12px; font-weight: 600; cursor: pointer;
  transition: background .15s, border-color .15s;
  white-space: nowrap; line-height: 1;
}
.lang-trigger:hover { background: rgba(255,255,255,.18); border-color: rgba(255,255,255,.3); }
.lang-trigger svg:first-child { border-radius: 2px; flex-shrink: 0; }
.lang-code { letter-spacing: .04em; }
.lang-chevron { opacity: .7; transition: transform .2s; flex-shrink: 0; }
.lang-dropdown.open .lang-chevron { transform: rotate(180deg); }
.lang-menu {
  display: none; position: absolute; top: calc(100% + 8px); right: 0;
  background: #1e293b; border: 1px solid rgba(255,255,255,.1);
  border-radius: 12px; padding: 6px; min-width: 156px;
  box-shadow: 0 12px 40px rgba(0,0,0,.45); z-index: 9999;
  animation: langFadeIn .15s ease;
}
@keyframes langFadeIn {
  from { opacity: 0; transform: translateY(-6px); }
  to   { opacity: 1; transform: translateY(0); }
}
.lang-dropdown.open .lang-menu { display: block; }
.lang-option {
  display: flex; align-items: center; gap: 10px;
  padding: 8px 10px; border-radius: 8px; text-decoration: none;
  color: rgba(255,255,255,.75); font-size: 13px; font-weight: 500;
  transition: background .12s, color .12s;
}
.lang-option:hover { background: rgba(255,255,255,.08); color: #fff; text-decoration: none; }
.lang-option-ativo { color: #a5b4fc; }
.lang-option-ativo:hover { background: rgba(165,180,252,.1); }
.lang-option svg:first-child { border-radius: 2px; flex-shrink: 0; }
.lang-check { margin-left: auto; color: #a5b4fc; flex-shrink: 0; }
</style>
<script>
(function() {
  function toggleLang(id) {
    var el = document.getElementById(id);
    if (!el) return;
    var isOpen = el.classList.contains('open');
    document.querySelectorAll('.lang-dropdown.open').forEach(function(d) { d.classList.remove('open'); });
    if (!isOpen) {
      el.classList.add('open');
      el.querySelector('.lang-trigger').setAttribute('aria-expanded', 'true');
    }
  }
  document.addEventListener('click', function(e) {
    if (!e.target.closest('.lang-dropdown')) {
      document.querySelectorAll('.lang-dropdown.open').forEach(function(d) {
        d.classList.remove('open');
        var btn = d.querySelector('.lang-trigger');
        if (btn) btn.setAttribute('aria-expanded', 'false');
      });
    }
  });
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      document.querySelectorAll('.lang-dropdown.open').forEach(function(d) {
        d.classList.remove('open');
        var btn = d.querySelector('.lang-trigger');
        if (btn) { btn.setAttribute('aria-expanded', 'false'); btn.focus(); }
      });
    }
  });
  window.toggleLang = toggleLang;
})();
</script>
<?php endif; ?>
