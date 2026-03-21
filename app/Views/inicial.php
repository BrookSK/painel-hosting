<?php
declare(strict_types=1);
use LRV\Core\I18n;
use LRV\Core\View;
use LRV\Core\SistemaConfig;
$_topo_hide_inicio = true;
$_topo_links = [
    ['href' => '/status',   'label' => 'Status'],
    ['href' => '/contato',  'label' => 'Contato'],
    ['href' => '/changelog','label' => 'Changelog'],
];
?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?php echo View::e(SistemaConfig::nome()); ?></title>
  <?php require __DIR__ . '/_partials/estilo.php'; ?>
</head>
<body>
  <?php require __DIR__ . '/_partials/topo-publico.php'; ?>

  <div class="conteudo" style="padding-top:40px;">
    <div class="grid">
      <div class="card">
        <h2 class="titulo">Acesso da equipe</h2>
        <p class="texto">Administre nodes, cobranças, tickets e permissões.</p>
        <div class="linha" style="gap:8px;">
          <a class="botao sm" href="/equipe/entrar">Entrar como equipe</a>
          <a class="botao ghost sm" href="/equipe/primeiro-acesso">Primeiro acesso</a>
        </div>
      </div>

      <div class="card">
        <h2 class="titulo">Acesso do cliente</h2>
        <p class="texto">Gerencie VPS, aplicações, deploy, backups e tickets.</p>
        <div class="linha" style="gap:8px;">
          <a class="botao sm" href="/cliente/entrar">Entrar como cliente</a>
          <a class="botao ghost sm" href="/cliente/criar-conta">Criar conta</a>
        </div>
      </div>

      <div class="card">
        <h2 class="titulo">API interna</h2>
        <p class="texto">Verificação rápida de saúde do sistema.</p>
        <a href="/api/saude" class="botao ghost sm">/api/saude</a>
      </div>
    </div>
  </div>

  <?php require __DIR__ . '/_partials/footer.php'; ?>
</body>
</html>
