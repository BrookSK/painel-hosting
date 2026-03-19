<?php

declare(strict_types=1);

use LRV\Core\I18n;
use LRV\Core\View;

function fmtPctMon($v): string
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
        <div style="opacity:.9; font-size:13px;">Servidor <?php echo View::e((string) ($servidor['hostname'] ?? '')); ?></div>
      </div>
      <div class="linha">
        <?php require __DIR__ . '/../_partials/idioma.php'; ?>
        <a href="/equipe/monitoramento">Voltar</a>
        <a href="/equipe/servidores">Servidores</a>
        <a href="/equipe/sair">Sair</a>
      </div>
    </div>
  </div>

  <div class="conteudo">
    <div class="card" style="margin-bottom:14px;">
      <div class="grid">
        <div>
          <div class="texto" style="margin:0;"><strong>ID:</strong> #<?php echo (int) ($servidor['id'] ?? 0); ?></div>
          <div class="texto" style="margin:0;"><strong>IP:</strong> <?php echo View::e((string) ($servidor['ip_address'] ?? '')); ?></div>
        </div>
        <div>
          <div class="texto" style="margin:0;"><strong>Status:</strong> <?php echo View::e((string) ($servidor['status'] ?? '')); ?></div>
          <div class="texto" style="margin:0;"><strong>Coletas:</strong> <?php echo (int) count($metricas ?? []); ?></div>
        </div>
      </div>
    </div>

    <div class="card">
      <h2 class="titulo" style="font-size:16px; margin-bottom:12px;">Últimas métricas</h2>

      <div style="overflow:auto;">
        <table style="width:100%; border-collapse:collapse;">
          <thead>
            <tr>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Data</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">CPU</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">RAM</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Disco</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach (($metricas ?? []) as $m): ?>
              <tr>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo View::e((string) ($m['timestamp'] ?? '')); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><code><?php echo View::e(fmtPctMon($m['cpu_usage'] ?? null)); ?></code></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><code><?php echo View::e(fmtPctMon($m['ram_usage'] ?? null)); ?></code></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><code><?php echo View::e(fmtPctMon($m['disk_usage'] ?? null)); ?></code></td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($metricas)): ?>
              <tr>
                <td colspan="4" style="padding:12px;">Ainda não há métricas para este servidor.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</body>
</html>
