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
  <title>Contato</title>
  <?php require __DIR__ . '/_partials/estilo.php'; ?>
</head>
<body>
  <div class="topo">
    <div class="conteudo linha" style="justify-content:space-between;">
      <div>
        <div style="font-size:18px;font-weight:700;">Contato</div>
        <div style="opacity:.9; font-size:13px;">Fale conosco</div>
      </div>
      <div class="linha">
        <?php require __DIR__ . '/_partials/idioma.php'; ?>
        <a href="/">Início</a>
        <a href="/status">Status</a>
        <a href="/cliente/entrar">Cliente</a>
      </div>
    </div>
  </div>

  <div class="conteudo">
    <div class="card" style="max-width:600px; margin:0 auto;">
      <h1 class="titulo">Enviar mensagem</h1>

      <?php if (!empty($ok)): ?>
        <div style="background:#dcfce7; border:1px solid #bbf7d0; color:#166534; padding:12px; border-radius:12px; margin-bottom:14px;">
          <?php echo View::e((string) $ok); ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($erro)): ?>
        <div class="erro"><?php echo View::e((string) $erro); ?></div>
      <?php endif; ?>

      <?php if (empty($ok)): ?>
        <form method="post" action="/contato">
          <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />

          <div style="margin-bottom:12px;">
            <label style="display:block; font-size:13px; margin-bottom:6px;">Nome</label>
            <input class="input" type="text" name="name" value="<?php echo View::e((string) ($form['name'] ?? '')); ?>" maxlength="120" required />
          </div>

          <div style="margin-bottom:12px;">
            <label style="display:block; font-size:13px; margin-bottom:6px;">E-mail</label>
            <input class="input" type="email" name="email" value="<?php echo View::e((string) ($form['email'] ?? '')); ?>" maxlength="190" required />
          </div>

          <div style="margin-bottom:12px;">
            <label style="display:block; font-size:13px; margin-bottom:6px;">Assunto</label>
            <input class="input" type="text" name="subject" value="<?php echo View::e((string) ($form['subject'] ?? '')); ?>" maxlength="190" required />
          </div>

          <div style="margin-bottom:14px;">
            <label style="display:block; font-size:13px; margin-bottom:6px;">Mensagem</label>
            <textarea class="input" name="message" rows="6" maxlength="3000" required><?php echo View::e((string) ($form['message'] ?? '')); ?></textarea>
          </div>

          <button class="botao" type="submit">Enviar</button>
        </form>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
