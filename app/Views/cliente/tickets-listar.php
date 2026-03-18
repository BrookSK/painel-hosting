<?php

declare(strict_types=1);

use LRV\Core\View;
use LRV\Core\I18n;

function badgeStatus(string $st): string
{
    if ($st === 'closed') {
        return '<span class="badge" style="background:#f1f5f9;color:#334155;">Fechado</span>';
    }
    return '<span class="badge">Aberto</span>';
}

function badgePrioridade(string $p): string
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
<html lang="<?php echo View::e(I18n::idioma()); ?>">
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
        <div style="opacity:.9; font-size:13px;">Suporte e acompanhamento</div>
      </div>
      <div class="linha">
        <?php require __DIR__ . '/../_partials/idioma.php'; ?>
        <a href="/cliente/painel">Painel</a>
        <a href="/cliente/planos">Planos</a>
        <a href="/cliente/vps">VPS</a>
        <a href="/cliente/sair">Sair</a>
      </div>
    </div>
  </div>

  <div class="conteudo">
    <div class="linha" style="justify-content:space-between; margin-bottom:12px;">
      <div class="texto" style="margin:0;">Abra um ticket para dúvidas, suporte técnico, financeiro ou solicitações.</div>
      <a class="botao" href="/cliente/tickets/novo">Novo ticket</a>
    </div>

    <div class="card">
      <div style="overflow:auto;">
        <table style="width:100%; border-collapse:collapse;">
          <thead>
            <tr>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Assunto</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Departamento</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Prioridade</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Status</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Atualizado</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach (($tickets ?? []) as $t): ?>
              <tr>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><strong><?php echo View::e((string) ($t['subject'] ?? '')); ?></strong></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo View::e((string) ($t['department'] ?? '')); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo badgePrioridade((string) ($t['priority'] ?? 'medium')); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo badgeStatus((string) ($t['status'] ?? 'open')); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo View::e((string) ($t['updated_at'] ?? '')); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><a href="/cliente/tickets/ver?id=<?php echo (int) ($t['id'] ?? 0); ?>">Abrir</a></td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($tickets)): ?>
              <tr>
                <td colspan="6" style="padding:12px;">Você ainda não tem tickets.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</body>
</html>
