<?php
declare(strict_types=1);
use LRV\Core\View;

$assinatura = is_array($resultado ?? null) ? ($resultado['assinatura'] ?? null) : null;
$cobrancas  = is_array($resultado ?? null) ? ($resultado['cobrancas']  ?? null) : null;

$primeira = null;
if (is_array($cobrancas) && isset($cobrancas['data']) && is_array($cobrancas['data']) && count($cobrancas['data']) > 0) {
    $primeira = $cobrancas['data'][0];
}

$link = '';
if (is_array($primeira)) {
    $link = (string)($primeira['invoiceUrl'] ?? '');
    if ($link === '') $link = (string)($primeira['bankSlipUrl'] ?? '');
}

$pageTitle    = 'Assinatura criada';
$clienteNome  = (string)($cliente['name'] ?? '');
$clienteEmail = (string)($cliente['email'] ?? '');
require __DIR__ . '/../_partials/layout-cliente-inicio.php';
?>

<div style="margin-bottom:24px;">
  <div class="page-title">Assinatura</div>
  <div class="page-subtitle" style="margin-bottom:0;">Cobrança gerada</div>
</div>

<div class="card-new" style="max-width:760px;">
  <?php if (!empty($erro)): ?>
    <div class="erro"><?php echo View::e((string)$erro); ?></div>
  <?php else: ?>
    <div style="text-align:center;padding:20px 0 24px;">
      <div style="font-size:48px;margin-bottom:12px;">🎉</div>
      <div style="font-size:18px;font-weight:700;margin-bottom:8px;">Pronto!</div>
      <p style="font-size:14px;color:#64748b;margin-bottom:20px;">Sua assinatura foi criada. Assim que o pagamento for confirmado, sua VPS será provisionada automaticamente.</p>
      <?php if ($link !== ''): ?>
        <a class="botao" href="<?php echo View::e($link); ?>" target="_blank" rel="noreferrer">Abrir cobrança</a>
      <?php endif; ?>
    </div>

    <div style="border-top:1px solid #f1f5f9;padding-top:14px;margin-top:4px;">
      <details>
        <summary style="cursor:pointer;font-size:13px;color:#64748b;">Ver detalhes técnicos</summary>
        <pre style="white-space:pre-wrap;background:#0b1220;color:#e2e8f0;padding:12px;border-radius:12px;overflow:auto;font-size:12px;margin-top:10px;"><?php echo View::e(json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)); ?></pre>
      </details>
    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
