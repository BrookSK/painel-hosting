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
  <title>Checkout cancelado</title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
</head>
<body>
  <div class="topo">
    <div class="conteudo linha" style="justify-content:space-between;">
      <div>
        <div style="font-size:18px;font-weight:700;">Assinatura</div>
        <div style="opacity:.9; font-size:13px;">Checkout cancelado</div>
      </div>
      <div class="linha">
        <?php require __DIR__ . '/../_partials/idioma.php'; ?>
        <a href="/cliente/planos">Voltar</a>
        <a href="/cliente/painel">Painel</a>
      </div>
    </div>
  </div>

  <div class="conteudo">
    <div class="card" style="max-width:920px; margin:0 auto;">
      <h1 class="titulo">Checkout cancelado</h1>
      <p class="texto">Você cancelou o checkout. Nenhuma cobrança foi gerada.</p>

      <div style="margin-top:14px;">
        <a class="botao" href="/cliente/planos">Escolher outro plano</a>
      </div>
    </div>
  </div>
</body>
</html>
