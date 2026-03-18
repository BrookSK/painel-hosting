<?php

declare(strict_types=1);

use LRV\Core\View;

$assinatura = is_array($resultado ?? null) ? ($resultado['assinatura'] ?? null) : null;
$cobrancas = is_array($resultado ?? null) ? ($resultado['cobrancas'] ?? null) : null;

$primeira = null;
if (is_array($cobrancas) && isset($cobrancas['data']) && is_array($cobrancas['data']) && count($cobrancas['data']) > 0) {
    $primeira = $cobrancas['data'][0];
}

$link = '';
if (is_array($primeira)) {
    $link = (string) ($primeira['invoiceUrl'] ?? '');
    if ($link === '') {
        $link = (string) ($primeira['bankSlipUrl'] ?? '');
    }
}

?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Assinatura criada</title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
</head>
<body>
  <div class="topo">
    <div class="conteudo linha" style="justify-content:space-between;">
      <div>
        <div style="font-size:18px;font-weight:700;">Assinatura</div>
        <div style="opacity:.9; font-size:13px;">Cobrança gerada</div>
      </div>
      <div class="linha">
        <a href="/cliente/planos">Voltar</a>
        <a href="/cliente/painel">Painel</a>
        <a href="/cliente/vps">VPS</a>
      </div>
    </div>
  </div>

  <div class="conteudo">
    <div class="card" style="max-width:920px; margin:0 auto;">
      <h1 class="titulo">Pronto</h1>

      <?php if (!empty($erro)): ?>
        <div class="erro"><?php echo View::e((string) $erro); ?></div>
      <?php else: ?>
        <p class="texto">Sua assinatura foi criada. Assim que o pagamento for confirmado, sua VPS será provisionada automaticamente.</p>

        <?php if ($link !== ''): ?>
          <p class="texto"><a class="botao" href="<?php echo View::e($link); ?>" target="_blank" rel="noreferrer">Abrir cobrança</a></p>
        <?php endif; ?>

        <div style="margin-top:14px;">
          <details>
            <summary style="cursor:pointer;">Ver detalhes (técnico)</summary>
            <pre style="white-space:pre-wrap; background:#0b1220; color:#e2e8f0; padding:12px; border-radius:12px; overflow:auto;"><?php echo View::e(json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)); ?></pre>
          </details>
        </div>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
