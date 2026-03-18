<?php

declare(strict_types=1);

use LRV\Core\View;

function badgeStatusAssinatura(string $st): string
{
    $st = strtoupper(trim($st));

    if ($st === 'ACTIVE') {
        return '<span class="badge" style="background:#dcfce7;color:#166534;">ATIVA</span>';
    }

    if ($st === 'PENDING') {
        return '<span class="badge" style="background:#fef3c7;color:#92400e;">PENDENTE</span>';
    }

    if ($st === 'OVERDUE') {
        return '<span class="badge" style="background:#fee2e2;color:#991b1b;">OVERDUE</span>';
    }

    if ($st === 'SUSPENDED') {
        return '<span class="badge" style="background:#fee2e2;color:#991b1b;">SUSPENSA</span>';
    }

    if ($st === 'CANCELED') {
        return '<span class="badge" style="background:#f1f5f9;color:#334155;">CANCELADA</span>';
    }

    return '<span class="badge" style="background:#f1f5f9;color:#334155;">' . View::e($st) . '</span>';
}

?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Assinaturas</title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
</head>
<body>
  <div class="topo">
    <div class="conteudo linha" style="justify-content:space-between;">
      <div>
        <div style="font-size:18px;font-weight:700;">Assinaturas</div>
        <div style="opacity:.9; font-size:13px;">Status de cobrança (Asaas)</div>
      </div>
      <div class="linha">
        <a href="/equipe/painel">Painel</a>
        <a href="/equipe/planos">Planos</a>
        <a href="/equipe/vps">VPS</a>
        <a href="/equipe/jobs">Jobs</a>
        <a href="/equipe/configuracoes">Configurações</a>
        <a href="/equipe/sair">Sair</a>
      </div>
    </div>
  </div>

  <div class="conteudo">
    <?php if (!empty($erro)): ?>
      <div class="card" style="border:1px solid #fecaca; background:#fff1f2;">
        <div style="font-weight:700;">Atenção</div>
        <div class="texto" style="margin:6px 0 0 0;"><?php echo View::e((string) $erro); ?></div>
      </div>
      <div style="height:12px;"></div>
    <?php endif; ?>

    <div class="card">
      <div style="overflow:auto;">
        <table style="width:100%; border-collapse:collapse;">
          <thead>
            <tr>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">ID</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Cliente</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Plano</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">VPS</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Status</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Próximo venc.</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Asaas ID</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Criado</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach (($assinaturas ?? []) as $s): ?>
              <tr>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><strong>#<?php echo (int) ($s['id'] ?? 0); ?></strong></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;">
                  <div><strong><?php echo View::e((string) ($s['client_name'] ?? '')); ?></strong></div>
                  <div style="font-size:12px; opacity:.8;"><?php echo View::e((string) ($s['client_email'] ?? '')); ?></div>
                </td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;">
                  <div><strong><?php echo View::e((string) ($s['plan_name'] ?? '')); ?></strong></div>
                  <div style="font-size:12px; opacity:.8;">R$ <?php echo View::e((string) ($s['plan_price'] ?? '')); ?>/mês</div>
                </td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;">#<?php echo (int) ($s['vps_id'] ?? 0); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo badgeStatusAssinatura((string) ($s['status'] ?? '')); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo View::e((string) ($s['next_due_date'] ?? '')); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><code><?php echo View::e((string) ($s['asaas_subscription_id'] ?? '')); ?></code></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo View::e((string) ($s['created_at'] ?? '')); ?></td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($assinaturas)): ?>
              <tr>
                <td colspan="8" style="padding:12px;">Nenhuma assinatura encontrada.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</body>
</html>
