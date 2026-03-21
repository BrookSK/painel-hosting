<?php
declare(strict_types=1);
use LRV\Core\View;

function badgeStatus(string $st): string {
    if ($st === 'closed') return '<span class="badge-new" style="background:#f1f5f9;color:#334155;">Fechado</span>';
    return '<span class="badge-new badge-green">Aberto</span>';
}

function badgePrioridade(string $p): string {
    if ($p === 'high') return '<span class="badge-new badge-red">Alta</span>';
    if ($p === 'low')  return '<span class="badge-new badge-green">Baixa</span>';
    return '<span class="badge-new badge-yellow">Média</span>';
}

$pageTitle    = 'Tickets';
$clienteNome  = (string)($cliente['name'] ?? '');
$clienteEmail = (string)($cliente['email'] ?? '');
require __DIR__ . '/../_partials/layout-cliente-inicio.php';
?>

<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:24px;">
  <div>
    <div class="page-title">Tickets</div>
    <div class="page-subtitle" style="margin-bottom:0;">Suporte e acompanhamento</div>
  </div>
  <a class="botao" href="/cliente/tickets/novo">Novo ticket</a>
</div>

<div class="card-new">
  <div style="overflow:auto;">
    <table style="width:100%;border-collapse:collapse;">
      <thead>
        <tr>
          <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;">Assunto</th>
          <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;">Departamento</th>
          <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;">Prioridade</th>
          <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;">Status</th>
          <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;">Atualizado</th>
          <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;">Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach (($tickets ?? []) as $t): ?>
          <tr>
            <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><strong><?php echo View::e((string)($t['subject'] ?? '')); ?></strong></td>
            <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><?php echo View::e((string)($t['department'] ?? '')); ?></td>
            <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><?php echo badgePrioridade((string)($t['priority'] ?? 'medium')); ?></td>
            <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><?php echo badgeStatus((string)($t['status'] ?? 'open')); ?></td>
            <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><?php echo View::e((string)($t['updated_at'] ?? '')); ?></td>
            <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><a href="/cliente/tickets/ver?id=<?php echo (int)($t['id'] ?? 0); ?>">Abrir</a></td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($tickets)): ?>
          <tr><td colspan="6" style="padding:12px;color:#94a3b8;">Você ainda não tem tickets.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
