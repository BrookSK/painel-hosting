<?php

declare(strict_types=1);

use LRV\Core\View;
use LRV\Core\I18n;

$nome = (string) ($cliente['name'] ?? '');
$email = (string) ($cliente['email'] ?? '');

?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Painel do cliente</title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
</head>
<body>
  <div class="topo">
    <div class="conteudo linha" style="justify-content:space-between;">
      <div>
        <div style="font-size:18px;font-weight:700;">Painel do cliente</div>
        <div style="opacity:.9; font-size:13px;">Bem-vindo, <?php echo View::e($nome); ?></div>
      </div>
      <div class="linha">
        <?php require __DIR__ . '/../_partials/idioma.php'; ?>
        <a href="/">Início</a>
        <a href="/cliente/planos">Planos</a>
        <a href="/cliente/vps">VPS</a>
        <a href="/cliente/aplicacoes">Aplicações</a>
        <a href="/cliente/monitoramento">Monitoramento</a>
        <a href="/cliente/tickets">Tickets</a>
        <a href="/cliente/sair">Sair</a>
      </div>
    </div>
  </div>

  <div class="conteudo">
    <div class="grid">
      <div class="card">
        <h2 class="titulo">Sua conta</h2>
        <p class="texto"><strong>Nome:</strong> <?php echo View::e($nome); ?></p>
        <p class="texto"><strong>E-mail:</strong> <?php echo View::e($email); ?></p>
      </div>

      <div class="card">
        <h2 class="titulo">O que vai aparecer aqui</h2>
        <p class="texto">VPS, aplicações, deploy automático, backups, monitoramento e suporte (tickets).</p>
        <div class="linha" style="gap:8px;">
          <span class="badge">VPS</span>
          <span class="badge">Deploy</span>
          <span class="badge">Backups</span>
          <span class="badge">Monitoramento</span>
          <span class="badge">Tickets</span>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
