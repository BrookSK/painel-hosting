<?php

declare(strict_types=1);

use LRV\Core\View;
use LRV\Core\I18n;

?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Cliente - Entrar</title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
</head>
<body>
  <div class="topo">
    <div class="conteudo linha" style="justify-content:space-between;">
      <div>
        <div style="font-size:18px;font-weight:700;">LRV Cloud Manager</div>
        <div style="opacity:.9; font-size:13px;">Acesso do cliente</div>
      </div>
      <div class="linha">
        <?php require __DIR__ . '/../_partials/idioma.php'; ?>
        <a href="/">Início</a>
      </div>
    </div>
  </div>

  <div class="conteudo">
    <div class="card" style="max-width:520px; margin:0 auto;">
      <h1 class="titulo">Entrar (Cliente)</h1>
      <p class="texto">Acesse suas VPS, aplicações, deploy e suporte.</p>

      <?php if (!empty($erro)): ?>
        <div class="erro"><?php echo View::e((string) $erro); ?></div>
      <?php endif; ?>

      <form method="post" action="/cliente/entrar">
        <div style="margin-bottom:10px;">
          <label style="display:block; font-size:13px; margin-bottom:6px;">E-mail</label>
          <input class="input" type="email" name="email" value="<?php echo View::e((string) ($email ?? '')); ?>" autocomplete="email" />
        </div>
        <div style="margin-bottom:14px;">
          <label style="display:block; font-size:13px; margin-bottom:6px;">Senha</label>
          <input class="input" type="password" name="senha" autocomplete="current-password" />
        </div>
        <div class="linha" style="justify-content:space-between;">
          <button class="botao" type="submit">Entrar</button>
          <a href="/cliente/criar-conta">Criar conta</a>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
