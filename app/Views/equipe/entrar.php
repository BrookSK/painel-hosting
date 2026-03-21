<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;
use LRV\Core\SistemaConfig;
$_topo_links = [
    ['href' => '/cliente/entrar', 'label' => 'Área do cliente'],
];
?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Equipe — <?php echo View::e(SistemaConfig::nome()); ?></title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
</head>
<body>
  <?php require __DIR__ . '/../_partials/topo-publico.php'; ?>

  <div class="conteudo">
    <div class="card" style="max-width:480px;margin:40px auto 0;">
      <h1 class="titulo">Acesso da equipe</h1>
      <p class="texto">Use seu e-mail e senha para acessar o painel administrativo.</p>

      <?php if (!empty($erro)): ?>
        <div class="erro"><?php echo View::e((string) $erro); ?></div>
      <?php endif; ?>

      <?php if (isset($_GET['sessao']) && $_GET['sessao'] === 'expirada'): ?>
        <div class="erro">Sua sessão expirou por inatividade. Faça login novamente.</div>
      <?php endif; ?>

      <form method="post" action="/equipe/entrar">
        <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
        <div style="margin-bottom:10px;">
          <label style="display:block;font-size:13px;margin-bottom:6px;">E-mail</label>
          <input class="input" type="email" name="email" value="<?php echo View::e((string) ($email ?? '')); ?>" autocomplete="email" />
        </div>
        <div style="margin-bottom:16px;">
          <label style="display:block;font-size:13px;margin-bottom:6px;">Senha</label>
          <input class="input" type="password" name="senha" autocomplete="current-password" />
        </div>
        <button class="botao" type="submit">Entrar</button>
      </form>
    </div>
  </div>

  <?php require __DIR__ . '/../_partials/footer.php'; ?>
</body>
</html>
