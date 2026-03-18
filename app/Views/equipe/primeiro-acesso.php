<?php

declare(strict_types=1);

use LRV\Core\View;

?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Equipe - Primeiro acesso</title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
</head>
<body>
  <div class="topo">
    <div class="conteudo linha" style="justify-content:space-between;">
      <div>
        <div style="font-size:18px;font-weight:700;">LRV Cloud Manager</div>
        <div style="opacity:.9; font-size:13px;">Primeiro acesso</div>
      </div>
      <div class="linha">
        <a href="/">Início</a>
      </div>
    </div>
  </div>

  <div class="conteudo">
    <div class="card" style="max-width:620px; margin:0 auto;">
      <h1 class="titulo">Criar SuperAdmin</h1>
      <p class="texto">Como ainda não existe nenhum usuário interno, crie agora o primeiro acesso (SuperAdmin).</p>

      <?php if (!empty($erro)): ?>
        <div class="erro"><?php echo View::e((string) $erro); ?></div>
      <?php endif; ?>

      <form method="post" action="/equipe/primeiro-acesso">
        <div class="grid">
          <div>
            <label style="display:block; font-size:13px; margin-bottom:6px;">Nome</label>
            <input class="input" type="text" name="nome" value="<?php echo View::e((string) ($nome ?? '')); ?>" autocomplete="name" />
          </div>
          <div>
            <label style="display:block; font-size:13px; margin-bottom:6px;">E-mail</label>
            <input class="input" type="email" name="email" value="<?php echo View::e((string) ($email ?? '')); ?>" autocomplete="email" />
          </div>
        </div>

        <div style="margin-top:12px;">
          <label style="display:block; font-size:13px; margin-bottom:6px;">Senha</label>
          <input class="input" type="password" name="senha" autocomplete="new-password" />
        </div>

        <div style="margin-top:14px;">
          <button class="botao" type="submit">Criar e entrar</button>
        </div>
      </form>

      <p class="texto" style="margin-top:14px; font-size:13px;">Depois disso você vai conseguir gerenciar permissões, planos, nodes, cobranças e tickets.</p>
    </div>
  </div>
</body>
</html>
