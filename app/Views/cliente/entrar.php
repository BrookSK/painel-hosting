<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;
use LRV\Core\SistemaConfig;
$_topo_links = [
    ['href' => '/cliente/criar-conta', 'label' => 'Criar conta'],
    ['href' => '/equipe/entrar',        'label' => 'Equipe'],
];
?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Entrar — <?php echo View::e(SistemaConfig::nome()); ?></title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
</head>
<body>
  <?php require __DIR__ . '/../_partials/topo-publico.php'; ?>

  <div class="conteudo">
    <div class="card" style="max-width:480px;margin:40px auto 0;">
      <h1 class="titulo">Entrar</h1>
      <p class="texto">Acesse suas VPS, aplicações, deploy e suporte.</p>

      <?php if (!empty($erro)): ?>
        <div class="erro"><?php echo View::e((string) $erro); ?></div>
      <?php endif; ?>

      <form method="post" action="/cliente/entrar">
        <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
        <div style="margin-bottom:10px;">
          <label style="display:block;font-size:13px;margin-bottom:6px;">E-mail</label>
          <input class="input" type="email" name="email" value="<?php echo View::e((string) ($email ?? '')); ?>" autocomplete="email" />
        </div>
        <div style="margin-bottom:16px;">
          <label style="display:block;font-size:13px;margin-bottom:6px;">Senha</label>
          <input class="input" type="password" name="senha" autocomplete="current-password" />
        </div>
        <div class="linha" style="justify-content:space-between;align-items:center;">
          <button class="botao" type="submit">Entrar</button>
          <a href="/cliente/criar-conta" style="font-size:13px;">Criar conta</a>
        </div>
      </form>
    </div>
  </div>

  <?php require __DIR__ . '/../_partials/footer.php'; ?>
</body>
</html>
