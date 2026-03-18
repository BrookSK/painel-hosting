<?php

declare(strict_types=1);

use LRV\Core\View;

function badgeStatusEquipe(string $st): string
{
    if ($st === 'closed') {
        return '<span class="badge" style="background:#f1f5f9;color:#334155;">Fechado</span>';
    }
    return '<span class="badge">Aberto</span>';
}

function badgePrioridadeEquipe(string $p): string
{
    if ($p === 'high') {
        return '<span class="badge" style="background:#fee2e2;color:#991b1b;">Alta</span>';
    }
    if ($p === 'low') {
        return '<span class="badge" style="background:#dcfce7;color:#166534;">Baixa</span>';
    }
    return '<span class="badge" style="background:#fef3c7;color:#92400e;">Média</span>';
}

?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Tickets</title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
</head>
<body>
  <div class="topo">
    <div class="conteudo linha" style="justify-content:space-between;">
      <div>
        <div style="font-size:18px;font-weight:700;">Tickets</div>
        <div style="opacity:.9; font-size:13px;">Atendimento e histórico</div>
      </div>
      <div class="linha">
        <a href="/equipe/painel">Painel</a>
        <a href="/equipe/planos">Planos</a>
        <a href="/equipe/servidores">Servidores</a>
        <a href="/equipe/configuracoes">Configurações</a>
        <a href="/equipe/sair">Sair</a>
      </div>
    </div>
  </div>

  <div class="conteudo">
    <div class="card">
      <div style="overflow:auto;">
        <table style="width:100%; border-collapse:collapse;">
          <thead>
            <tr>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Cliente</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Assunto</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Departamento</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Prioridade</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Status</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Atribuído</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Atualizado</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach (($tickets ?? []) as $t): ?>
              <tr>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;">
                  <div><strong><?php echo View::e((string) ($t['client_name'] ?? '')); ?></strong></div>
                  <div style="font-size:12px; opacity:.8;"><?php echo View::e((string) ($t['client_email'] ?? '')); ?></div>
                </td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo View::e((string) ($t['subject'] ?? '')); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo View::e((string) ($t['department'] ?? '')); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo badgePrioridadeEquipe((string) ($t['priority'] ?? 'medium')); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo badgeStatusEquipe((string) ($t['status'] ?? 'open')); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo View::e((string) (($t['assigned_to'] ?? '') === null ? '' : (string) ($t['assigned_to'] ?? ''))); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo View::e((string) ($t['updated_at'] ?? '')); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><a href="/equipe/tickets/ver?id=<?php echo (int) ($t['id'] ?? 0); ?>">Abrir</a></td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($tickets)): ?>
              <tr>
                <td colspan="8" style="padding:12px;">Nenhum ticket encontrado.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</body>
</html>
