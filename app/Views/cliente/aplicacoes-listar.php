<?php

declare(strict_types=1);

use LRV\Core\I18n;
use LRV\Core\View;

function badgeStatusAppCliente(string $st): string
{
    if ($st === 'inactive') {
        return '<span class="badge" style="background:#f1f5f9;color:#334155;">Inativa</span>';
    }
    if ($st === 'deploying') {
        return '<span class="badge" style="background:#e0e7ff;color:#1e3a8a;">Deploy</span>';
    }
    if ($st === 'error') {
        return '<span class="badge" style="background:#fee2e2;color:#991b1b;">Erro</span>';
    }
    return '<span class="badge">Ativa</span>';
}

?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Minhas aplicações</title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
</head>
<body>
  <div class="topo">
    <div class="conteudo linha" style="justify-content:space-between;">
      <div>
        <div style="font-size:18px;font-weight:700;">Minhas aplicações</div>
        <div style="opacity:.9; font-size:13px;">Apps e portas reservadas</div>
      </div>
      <div class="linha">
        <?php require __DIR__ . '/../_partials/idioma.php'; ?>
        <a href="/cliente/painel">Painel</a>
        <a href="/cliente/planos">Planos</a>
        <a href="/cliente/vps">VPS</a>
        <a href="/cliente/tickets">Tickets</a>
        <a href="/cliente/sair">Sair</a>
      </div>
    </div>
  </div>

  <div class="conteudo">
    <div class="card">
      <div style="overflow:auto;">
        <table style="width:100%; border-collapse:collapse;">
          <thead>
            <tr>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Aplicação</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">VPS</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Tipo</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Domínio</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Porta</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach (($aplicacoes ?? []) as $a): ?>
              <tr>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><strong>#<?php echo (int) ($a['id'] ?? 0); ?></strong></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;">#<?php echo (int) ($a['vps_id'] ?? 0); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><code><?php echo View::e((string) ($a['type'] ?? '')); ?></code></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo View::e((string) ($a['domain'] ?? '')); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><code><?php echo View::e((string) ($a['port'] ?? '')); ?></code></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo badgeStatusAppCliente((string) ($a['status'] ?? 'active')); ?></td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($aplicacoes)): ?>
              <tr>
                <td colspan="6" style="padding:12px;">Você ainda não tem aplicações.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <?php require __DIR__ . '/../_partials/chat-widget.php'; ?>
</body>
</html>
