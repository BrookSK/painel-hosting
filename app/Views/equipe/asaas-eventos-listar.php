<?php
declare(strict_types=1);
use LRV\Core\View;

$pageTitle = 'Eventos Asaas';
require __DIR__ . '/../_partials/layout-equipe-inicio.php';
?>
<div class="page-title">Eventos Asaas</div>
<div class="page-subtitle">Webhook recebido (ultimos 300)</div>

<?php if (!empty($erro)): ?><div class="erro" style="margin-bottom:12px;"><?php echo View::e((string)$erro); ?></div><?php endif; ?>

<div class="card-new">
  <div style="overflow:auto;">
    <table>
      <thead>
        <tr><th>ID</th><th>Event ID</th><th>Tipo</th><th>Criado</th></tr>
      </thead>
      <tbody>
        <?php foreach (($eventos??[]) as $e): ?>
          <tr>
            <td><strong>#<?php echo (int)($e['id']??0); ?></strong></td>
            <td><code><?php echo View::e((string)($e['event_id']??'')); ?></code></td>
            <td><?php echo View::e((string)($e['event_type']??'')); ?></td>
            <td><?php echo View::e((string)($e['created_at']??'')); ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($eventos)): ?>
          <tr><td colspan="4">Nenhum evento recebido ainda.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
