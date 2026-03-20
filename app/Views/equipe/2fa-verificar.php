<?php declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;
$erro = (string) ($erro ?? '');
?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" /><meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Verificação 2FA</title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
</head>
<body>
  <div class="topo">
    <div class="conteudo"><div style="font-size:18px;font-weight:700;">Verificação em dois fatores</div></div>
  </div>
  <div class="conteudo">
    <div class="card" style="max-width:400px; margin:40px auto;">
      <?php if ($erro !== ''): ?>
        <div class="erro"><?php echo View::e($erro); ?></div>
      <?php endif; ?>
      <p class="texto">Insira o código do seu aplicativo autenticador:</p>
      <form method="post" action="/equipe/2fa/verificar">
        <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
        <input class="input" type="text" name="codigo" maxlength="6" pattern="\d{6}" autocomplete="one-time-code" placeholder="000000" required autofocus />
        <button class="botao" type="submit" style="margin-top:12px;width:100%;">Verificar</button>
      </form>
      <p style="margin-top:12px;font-size:13px;"><a href="/equipe/entrar">Voltar ao login</a></p>
    </div>
  </div>
</body>
</html>
