<?php
declare(strict_types=1);
use LRV\Core\View;

function badgeStatusAppCliente(string $st): string {
    if ($st === 'inactive')  return '<span class="badge-new" style="background:#f1f5f9;color:#334155;">Inativa</span>';
    if ($st === 'deploying') return '<span class="badge-new" style="background:#e0e7ff;color:#1e3a8a;">Deploy</span>';
    if ($st === 'error')     return '<span class="badge-new badge-red">Erro</span>';
    return '<span class="badge-new badge-green">Ativa</span>';
}

$pageTitle    = 'Minhas Aplicações';
$clienteNome  = (string)($cliente['name'] ?? '');
$clienteEmail = (string)($cliente['email'] ?? '');
require __DIR__ . '/../_partials/layout-cliente-inicio.php';
?>

<div style="margin-bottom:24px;">
  <div class="page-title">Minhas Aplicações</div>
  <div class="page-subtitle" style="margin-bottom:0;">Apps e portas reservadas</div>
</div>

<div class="card-new">
  <div style="overflow:auto;">
    <table style="width:100%;border-collapse:collapse;">
      <thead>
        <tr>
          <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;">Aplicação</th>
          <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;">VPS</th>
          <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;">Tipo</th>
          <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;">Domínio</th>
          <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;">Porta</th>
          <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;">Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach (($aplicacoes ?? []) as $a): ?>
          <tr>
            <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><strong>#<?php echo (int)($a['id'] ?? 0); ?></strong></td>
            <td style="padding:10px;border-bottom:1px solid #f1f5f9;">#<?php echo (int)($a['vps_id'] ?? 0); ?></td>
            <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><code><?php echo View::e((string)($a['type'] ?? '')); ?></code></td>
            <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><?php echo View::e((string)($a['domain'] ?? '')); ?></td>
            <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><code><?php echo View::e((string)($a['port'] ?? '')); ?></code></td>
            <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><?php echo badgeStatusAppCliente((string)($a['status'] ?? 'active')); ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($aplicacoes)): ?>
          <tr><td colspan="6" style="padding:12px;color:#94a3b8;">Você ainda não tem aplicações.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
