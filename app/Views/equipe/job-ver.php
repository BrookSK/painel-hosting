<?php

declare(strict_types=1);

use LRV\Core\View;
use LRV\Core\I18n;

$payloadJson = json_encode($payload ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Job</title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
</head>
<body>
  <div class="topo">
    <div class="conteudo linha" style="justify-content:space-between;">
      <div>
        <div style="font-size:18px;font-weight:700;">Job #<?php echo (int) ($job['id'] ?? 0); ?></div>
        <div style="opacity:.9; font-size:13px;"><?php echo View::e((string) ($job['type'] ?? '')); ?></div>
      </div>
      <div class="linha">
        <?php require __DIR__ . '/../_partials/idioma.php'; ?>
        <a href="/equipe/jobs">Voltar</a>
        <a href="/equipe/sair">Sair</a>
      </div>
    </div>
  </div>

  <div class="conteudo">
    <div class="grid">
      <div class="card">
        <h2 class="titulo" style="font-size:16px;">Dados</h2>
        <p class="texto"><strong>Status:</strong> <?php echo View::e((string) ($job['status'] ?? '')); ?></p>
        <p class="texto"><strong>Criado:</strong> <?php echo View::e((string) ($job['created_at'] ?? '')); ?></p>
        <p class="texto"><strong>Atualizado:</strong> <?php echo View::e((string) ($job['updated_at'] ?? '')); ?></p>
        <div style="margin-top:10px;">
          <div style="font-weight:700; font-size:13px; margin-bottom:6px;">Payload</div>
          <pre style="white-space:pre-wrap; background:#0b1220; color:#e2e8f0; padding:12px; border-radius:12px; overflow:auto;"><?php echo View::e((string) $payloadJson); ?></pre>
        </div>
      </div>

      <div class="card">
        <h2 class="titulo" style="font-size:16px;">Log</h2>
        <pre style="white-space:pre-wrap; background:#0b1220; color:#e2e8f0; padding:12px; border-radius:12px; overflow:auto; min-height:240px;"><?php echo View::e((string) ($job['log'] ?? '')); ?></pre>
      </div>
    </div>
  </div>
</body>
</html>
