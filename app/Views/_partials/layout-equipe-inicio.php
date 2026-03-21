<?php
/**
 * Layout equipe — início
 * Uso: require com $pageTitle e $usuario definidos antes
 */
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;
use LRV\Core\SistemaConfig;
$_lt_title = isset($pageTitle) ? $pageTitle . ' — ' . SistemaConfig::nome() : SistemaConfig::nome();
?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?php echo View::e($_lt_title); ?></title>
  <?php require __DIR__ . '/estilo-equipe.php'; ?>
</head>
<body>
<div class="app-shell" id="appShell">
  <div class="sidebar-overlay" id="sidebarOverlay"></div>
  <?php require __DIR__ . '/sidebar-equipe.php'; ?>
  <div class="app-main">
    <?php require __DIR__ . '/header-equipe.php'; ?>
    <div class="page-content">
