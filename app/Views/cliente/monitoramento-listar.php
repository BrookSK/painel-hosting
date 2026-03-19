<?php

declare(strict_types=1);

use LRV\Core\I18n;
use LRV\Core\View;

function fmtPctCli($v): string
{
    if ($v === null || $v === '') {
        return '';
    }
    return number_format((float) $v, 2, ',', '.') . '%';
}

?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Monitoramento</title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
</head>
<body>
  <div class="topo">
    <div class="conteudo linha" style="justify-content:space-between;">
      <div>
        <div style="font-size:18px;font-weight:700;">Monitoramento</div>
        <div style="opacity:.9; font-size:13px;">Última métrica por VPS</div>
      </div>
      <div class="linha">
        <?php require __DIR__ . '/../_partials/idioma.php'; ?>
        <a href="/cliente/painel">Painel</a>
        <a href="/cliente/vps">VPS</a>
        <a href="/cliente/aplicacoes">Aplicações</a>
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
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">VPS</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Servidor</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">CPU</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">RAM</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Disco</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Coleta</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach (($linhas ?? []) as $l): ?>
              <tr>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><strong>#<?php echo (int) ($l['vps_id'] ?? 0); ?></strong></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo View::e((string) ($l['hostname'] ?? '')); ?> <span style="opacity:.7; font-size:12px;">(<?php echo View::e((string) ($l['ip_address'] ?? '')); ?>)</span></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><code><?php echo View::e(fmtPctCli($l['cpu_usage'] ?? null)); ?></code></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><code><?php echo View::e(fmtPctCli($l['ram_usage'] ?? null)); ?></code></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><code><?php echo View::e(fmtPctCli($l['disk_usage'] ?? null)); ?></code></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo View::e((string) ($l['timestamp'] ?? '')); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><a href="/cliente/monitoramento/ver?vps_id=<?php echo (int) ($l['vps_id'] ?? 0); ?>">Ver</a></td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($linhas)): ?>
              <tr>
                <td colspan="7" style="padding:12px;">Nenhuma VPS encontrada.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</body>
</html>
