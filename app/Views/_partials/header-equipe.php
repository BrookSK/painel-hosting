<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\Auth;
use LRV\Core\I18n;
use LRV\Core\BancoDeDados;

$_notif_count = 0;
$_h_nome   = '';
$_h_role   = '';
$_h_email  = '';
$_h_avatar = '';
try {
    $uid = Auth::equipeId();
    if ($uid !== null) {
        $pdo = BancoDeDados::pdo();
        $s = $pdo->prepare('SELECT name, email, role, avatar_url FROM users WHERE id = :u');
        $s->execute([':u' => $uid]);
        $row = $s->fetch();
        if ($row) {
            $_h_nome   = (string) $row['name'];
            $_h_role   = (string) $row['role'];
            $_h_email  = (string) $row['email'];
            $_h_avatar = (string) ($row['avatar_url'] ?? '');
        }
        $s2 = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = :u AND read_at IS NULL');
        $s2->execute([':u' => $uid]);
        $_notif_count = (int) $s2->fetchColumn();
    }
} catch (\Throwable $e) {}

$_initials = '';
foreach (explode(' ', trim($_h_nome)) as $w) {
    $_initials .= strtoupper(substr($w, 0, 1));
    if (strlen($_initials) >= 2) break;
}
if ($_initials === '') $_initials = 'U';
?>
<header class="app-header">
  <div class="header-left">
    <button class="sidebar-expand-btn" id="sidebarExpandBtn" aria-label="<?php echo View::e(I18n::t('geral.expandir_menu')); ?>" title="<?php echo View::e(I18n::t('geral.expandir_menu')); ?>" onclick="(function(){var s=document.getElementById('appShell');if(s){s.classList.toggle('collapsed');localStorage.setItem('lrv_sidebar_collapsed',s.classList.contains('collapsed')?'1':'0');}})()">
      <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M7 4l6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </button>
    <button class="header-menu-btn" id="mobileMenuBtn" aria-label="Menu">
      <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M3 5h14M3 10h14M3 15h14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
    </button>
    <div class="header-search">
      <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="7" cy="7" r="4.5" stroke="#94a3b8" stroke-width="1.5"/><path d="M10.5 10.5l3 3" stroke="#94a3b8" stroke-width="1.5" stroke-linecap="round"/></svg>
      <input type="text" placeholder="<?php echo View::e(I18n::t('header.buscar')); ?>" class="header-search-input" />
    </div>
  </div>
  <div class="header-right">
    <?php require __DIR__ . '/idioma.php'; ?>
    <a href="/equipe/notificacoes" class="header-icon-btn" title="<?php echo View::e(I18n::t('header.notificacoes')); ?>">
      <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M10 2a6 6 0 00-6 6v3l-1.5 2.5h15L16 11V8a6 6 0 00-6-6z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><path d="M8.5 16a1.5 1.5 0 003 0" stroke="currentColor" stroke-width="1.6"/></svg>
      <?php if ($_notif_count > 0): ?>
        <span class="header-badge"><?php echo $_notif_count > 9 ? '9+' : $_notif_count; ?></span>
      <?php endif; ?>
    </a>
    <div class="header-avatar-wrap" id="avatarMenu">
      <div class="header-avatar">
        <?php if ($_h_avatar !== ''): ?>
          <img src="<?php echo View::e($_h_avatar); ?>" alt="avatar" style="width:100%;height:100%;object-fit:cover;border-radius:50%;" />
        <?php else: ?>
          <?php echo View::e($_initials); ?>
        <?php endif; ?>
      </div>
      <div class="header-avatar-info">
        <span class="header-avatar-name"><?php echo View::e($_h_nome); ?></span>
        <span class="header-avatar-role"><?php echo View::e($_h_role); ?></span>
      </div>
      <svg width="14" height="14" viewBox="0 0 14 14" fill="none" style="color:#94a3b8;flex-shrink:0;"><path d="M3 5l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
      <div class="avatar-dropdown" id="avatarDropdown">
        <div class="avatar-dropdown-info">
          <div class="avatar-dropdown-name"><?php echo View::e($_h_nome); ?></div>
          <div class="avatar-dropdown-email"><?php echo View::e($_h_email); ?></div>
        </div>
        <a href="/equipe/minha-conta" class="avatar-dropdown-item">
          <svg width="15" height="15" viewBox="0 0 15 15" fill="none"><circle cx="7.5" cy="4.5" r="2.5" stroke="currentColor" stroke-width="1.4"/><path d="M2 13c0-3.038 2.462-5.5 5.5-5.5S13 9.962 13 13" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
          <?php echo View::e(I18n::t('geral.minha_conta')); ?>
        </a>
        <a href="/equipe/2fa/configurar" class="avatar-dropdown-item">
          <svg width="15" height="15" viewBox="0 0 15 15" fill="none"><rect x="3" y="7" width="9" height="6" rx="1.5" stroke="currentColor" stroke-width="1.4"/><path d="M5 7V5a2.5 2.5 0 015 0v2" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
          <?php echo View::e(I18n::t('header.seguranca_2fa')); ?>
        </a>
        <div class="avatar-dropdown-divider"></div>
        <a href="/equipe/sair" class="avatar-dropdown-item avatar-dropdown-danger">
          <svg width="15" height="15" viewBox="0 0 15 15" fill="none"><path d="M10 7.5H3M10 7.5l-2.5-2.5M10 7.5l-2.5 2.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/><path d="M7 3H5a2 2 0 00-2 2v5a2 2 0 002 2h2" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
          <?php echo View::e(I18n::t('geral.sair')); ?>
        </a>
      </div>
    </div>
  </div>
</header>
