<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;
use LRV\Core\SistemaConfig;
use LRV\Core\Auth;

// Dados do cliente logado para o header
$_cliId   = Auth::clienteId() ?? 0;
$_cliNome = (string)($clienteNome ?? $cliente['name'] ?? '');
$_cliEmail= (string)($clienteEmail ?? $cliente['email'] ?? '');

$_initials = '';
foreach (explode(' ', trim($_cliNome)) as $_w) {
    $_initials .= strtoupper(substr($_w, 0, 1));
    if (strlen($_initials) >= 2) break;
}
if ($_initials === '') $_initials = 'C';

$_pageTitle = (string)($pageTitle ?? SistemaConfig::nome());
?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?php echo View::e($_pageTitle); ?> — <?php echo View::e(SistemaConfig::nome()); ?></title>
  <?php require __DIR__ . '/estilo.php'; ?>
  <?php require __DIR__ . '/estilo-equipe.php'; ?>
</head>
<body>
<div class="sidebar-overlay" id="sidebarOverlay" onclick="fecharSidebarCli()"></div>
<div class="app-shell" id="appShell">
  <?php require __DIR__ . '/sidebar-cliente.php'; ?>
  <div class="app-main">
    <!-- Header -->
    <header class="app-header">
      <div class="header-left">
        <button class="header-menu-btn" onclick="abrirSidebarCli()" aria-label="Menu">
          <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M3 5h14M3 10h14M3 15h14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
        </button>
        <button class="sidebar-expand-btn" onclick="expandirSidebarCli()" aria-label="Expandir menu">
          <svg width="18" height="18" viewBox="0 0 18 18" fill="none"><path d="M7 4l5 5-5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
      </div>
      <div class="header-right">
        <?php require __DIR__ . '/idioma.php'; ?>
        <div class="header-avatar-wrap" onclick="toggleAvatarDropdownCli()" id="avatarWrapCli">
          <div class="header-avatar"><?php echo View::e($_initials); ?></div>
          <div class="header-avatar-info">
            <span class="header-avatar-name"><?php echo View::e($_cliNome ?: 'Cliente'); ?></span>
            <span class="header-avatar-role">Área do cliente</span>
          </div>
          <div class="avatar-dropdown" id="avatarDropdownCli">
            <div class="avatar-dropdown-info">
              <div class="avatar-dropdown-name"><?php echo View::e($_cliNome ?: 'Cliente'); ?></div>
              <div class="avatar-dropdown-email"><?php echo View::e($_cliEmail); ?></div>
            </div>
            <a href="/cliente/minha-conta" class="avatar-dropdown-item">
              <svg width="14" height="14" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="7" r="3" stroke="currentColor" stroke-width="1.6"/><path d="M4 17c0-3.314 2.686-6 6-6s6 2.686 6 6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
              Minha Conta
            </a>
            <div class="avatar-dropdown-divider"></div>
            <a href="/cliente/sair" class="avatar-dropdown-item avatar-dropdown-danger">
              <svg width="14" height="14" viewBox="0 0 20 20" fill="none"><path d="M13 10H3M13 10l-3-3M13 10l-3 3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
              Sair
            </a>
          </div>
        </div>
      </div>
    </header>
    <!-- Page Content -->
    <div class="page-content">
