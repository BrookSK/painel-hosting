<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;

function badgeStatusAssinatura(string $st): string {
    $st = strtoupper(trim($st));
    $map = ['ACTIVE'=>['ATIVA','badge-green'],'PENDING'=>['PENDENTE','badge-yellow'],'OVERDUE'=>['OVERDUE','badge-red'],'SUSPENDED'=>['SUSPENSA','badge-red'],'CANCELED'=>['CANCELADA','badge-gray']];
    $d = $map[$st] ?? [View::e($st),'badge-gray'];
    return '<span class="badge-new '.$d[1].'">'.$d[0].'</span>';
}

$pageTitle = 'Assinaturas';
require __DIR__ . '/../_partials/layout-equipe-inicio.php';
?>
<div class="page-title">Assinaturas</div>
<div class="page-subtitle">Status de cobranca</div>

<?php if (!empty($erro)): ?><div class="erro" style="margin-bottom:12px;"><?php echo View::e((string)$erro); ?></div><?php endif; ?>

<div class="card-new">
  <div style="overflow:auto;">
    <table>
      <thead>
        <tr><th>ID</th><th>Cliente</th><th>Plano</th><th>VPS</th><th>Status</th><th>Prox. venc.</th><th>Asaas ID</th><th>Stripe Sub</th><th>Criado</th></tr>
      </thead>
      <tbody>
        <?php foreach (($assinaturas??[]) as $s): ?>
          <tr>
            <td><strong>#<?php echo (int)($s['id']??0); ?></strong></td>
            <td>
              <div><strong><?php echo View::e((string)($s['client_name']??'')); ?></strong></div>
              <div style="font-size:12px;opacity:.8;"><?php echo View::e((string)($s['client_email']??'')); ?></div>
            </td>
            <td>
              <div><strong><?php echo View::e((string)($s['plan_name']??'')); ?></strong></div>
              <div style="font-size:12px;opacity:.8;"><?php echo View::e(I18n::preco((float)($s['plan_price'] ?? 0))); ?>/<?php echo View::e(I18n::t('assinaturas.mes')); ?></div>
            </td>
            <td>#<?php echo (int)($s['vps_id']??0); ?></td>
            <td><?php echo badgeStatusAssinatura((string)($s['status']??'')); ?></td>
            <td><?php echo View::e((string)($s['next_due_date']??'')); ?></td>
            <td><code><?php echo View::e((string)($s['asaas_subscription_id']??'')); ?></code></td>
            <td><code><?php echo View::e((string)($s['stripe_subscription_id']??'')); ?></code></td>
            <td><?php echo View::e((string)($s['created_at']??'')); ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($assinaturas)): ?>
          <tr><td colspan="9">Nenhuma assinatura encontrada.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
