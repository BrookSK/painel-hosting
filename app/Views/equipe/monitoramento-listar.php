<?php

declare(strict_types=1);

use LRV\Core\I18n;
use LRV\Core\View;

function fmtPct($v): string
{
    if ($v === null || $v === '') {
        return '';
    }
    return number_format((float) $v, 2, ',', '.') . '%';
}

function badgeStatusServidorMon(string $st): string
{
    if ($st === 'maintenance') {
        return '<span class="badge" style="background:#fef3c7;color:#92400e;">Manutenção</span>';
    }
    if ($st === 'inactive') {
        return '<span class="badge" style="background:#f1f5f9;color:#334155;">Inativo</span>';
    }
    return '<span class="badge">Ativo</span>';
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
        <div style="opacity:.9; font-size:13px;">Últimas métricas por servidor</div>
      </div>
      <div class="linha">
        <?php require __DIR__ . '/../_partials/idioma.php'; ?>
        <a href="/equipe/painel">Painel</a>
        <a href="/equipe/servidores">Servidores</a>
        <a href="/equipe/configuracoes">Configurações</a>
        <a href="/equipe/sair">Sair</a>
      </div>
    </div>
  </div>

  <div class="conteudo">
    <div class="card">
      <div class="texto" style="margin-bottom:12px;">
        Envio de métricas: <code>POST /api/metrics/servers</code> com header <code>x-monitoring-token</code>.
      </div>

      <div style="overflow:auto;">
        <table style="width:100%; border-collapse:collapse;">
          <thead>
            <tr>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Servidor</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">IP</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Status</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">CPU</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">RAM</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Disco</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Coleta</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach (($servidores ?? []) as $s): ?>
              <tr>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><strong><?php echo View::e((string) ($s['hostname'] ?? '')); ?></strong> <span style="opacity:.7; font-size:12px;">#<?php echo (int) ($s['id'] ?? 0); ?></span></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo View::e((string) ($s['ip_address'] ?? '')); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo badgeStatusServidorMon((string) ($s['status'] ?? '')); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><code><?php echo View::e(fmtPct($s['cpu_usage'] ?? null)); ?></code></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><code><?php echo View::e(fmtPct($s['ram_usage'] ?? null)); ?></code></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><code><?php echo View::e(fmtPct($s['disk_usage'] ?? null)); ?></code></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo View::e((string) ($s['timestamp'] ?? '')); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><a href="/equipe/monitoramento/ver?id=<?php echo (int) ($s['id'] ?? 0); ?>">Ver</a></td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($servidores)): ?>
              <tr>
                <td colspan="8" style="padding:12px;">Nenhum servidor encontrado.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</body>
</html>
