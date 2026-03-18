<?php

declare(strict_types=1);

use LRV\Core\View;

function formatarGb(int $mb): string
{
    if ($mb <= 0) {
        return '0 GB';
    }
    return (string) ((int) round($mb / 1024)) . ' GB';
}

?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Servidores</title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
</head>
<body>
  <div class="topo">
    <div class="conteudo linha" style="justify-content:space-between;">
      <div>
        <div style="font-size:18px;font-weight:700;">Servidores</div>
        <div style="opacity:.9; font-size:13px;">Nodes do cluster e capacidade disponível</div>
      </div>
      <div class="linha">
        <a href="/equipe/painel">Painel</a>
        <a href="/equipe/planos">Planos</a>
        <a href="/equipe/tickets">Tickets</a>
        <a href="/equipe/configuracoes">Configurações</a>
        <a href="/equipe/sair">Sair</a>
      </div>
    </div>
  </div>

  <div class="conteudo">
    <div class="linha" style="justify-content:space-between; margin-bottom:12px;">
      <div class="texto" style="margin:0;">Cadastre seus nodes e a capacidade total. O sistema usa esses dados para alocar VPS automaticamente.</div>
      <a class="botao" href="/equipe/servidores/novo">Novo servidor</a>
    </div>

    <div class="card">
      <div style="overflow:auto;">
        <table style="width:100%; border-collapse:collapse;">
          <thead>
            <tr>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Hostname</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">IP</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">SSH</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">CPU</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Memória</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Armazenamento</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Status</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach (($servidores ?? []) as $s): ?>
              <tr>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><strong><?php echo View::e((string) ($s['hostname'] ?? '')); ?></strong></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo View::e((string) ($s['ip_address'] ?? '')); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo View::e((string) ($s['ssh_port'] ?? '')); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;">
                  <?php echo View::e((string) ($s['cpu_used'] ?? 0)); ?> / <?php echo View::e((string) ($s['cpu_total'] ?? 0)); ?>
                </td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;">
                  <?php echo View::e(formatarGb((int) ($s['ram_used'] ?? 0))); ?> / <?php echo View::e(formatarGb((int) ($s['ram_total'] ?? 0))); ?>
                </td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;">
                  <?php echo View::e(formatarGb((int) ($s['storage_used'] ?? 0))); ?> / <?php echo View::e(formatarGb((int) ($s['storage_total'] ?? 0))); ?>
                </td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;">
                  <?php
                    $st = (string) ($s['status'] ?? '');
                    if ($st === 'active') {
                        echo '<span class="badge">Ativo</span>';
                    } elseif ($st === 'maintenance') {
                        echo '<span class="badge" style="background:#fef3c7;color:#92400e;">Manutenção</span>';
                    } else {
                        echo '<span class="badge" style="background:#f1f5f9;color:#334155;">Inativo</span>';
                    }
                  ?>
                </td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;">
                  <a href="/equipe/servidores/editar?id=<?php echo (int) ($s['id'] ?? 0); ?>">Editar</a>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($servidores)): ?>
              <tr>
                <td colspan="8" style="padding:12px;">Nenhum servidor cadastrado ainda.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</body>
</html>
