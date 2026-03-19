<?php

declare(strict_types=1);

use LRV\Core\View;
use LRV\Core\I18n;

function formatarGb(int $mb): string
{
    if ($mb <= 0) {
        return '0 GB';
    }
    return (string) ((int) round($mb / 1024)) . ' GB';
}

?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
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
        <?php require __DIR__ . '/../_partials/idioma.php'; ?>
        <a href="/equipe/painel">Painel</a>
        <a href="/equipe/planos">Planos</a>
        <a href="/equipe/vps">VPS</a>
        <a href="/equipe/monitoramento">Monitoramento</a>
        <a href="/equipe/jobs">Jobs</a>
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
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Usuário</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Chave</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Terminal</th>
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
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo View::e((string) ($s['ssh_user'] ?? '')); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><code><?php echo View::e((string) ($s['ssh_key_id'] ?? '')); ?></code></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;">
                  <?php
                    $tu = trim((string) ($s['terminal_ssh_user'] ?? ''));
                    $tk = trim((string) ($s['terminal_ssh_key_id'] ?? ''));
                    if ($tu !== '' && $tk !== '') {
                        echo View::e($tu) . '<div style="margin-top:4px;"><code>' . View::e($tk) . '</code></div>';
                    } else {
                        echo '<span class="badge" style="background:#f1f5f9;color:#334155;">Desativado</span>';
                    }
                  ?>
                </td>
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

                    if (array_key_exists('is_online', $s)) {
                        $online = (int) ($s['is_online'] ?? 0);
                        if ($online === 1) {
                            echo ' <span class="badge" style="background:#dcfce7;color:#166534;">Online</span>';
                        } else {
                            echo ' <span class="badge" style="background:#fee2e2;color:#991b1b;">Offline</span>';
                        }
                    }

                    if (array_key_exists('last_check_at', $s)) {
                        $lc = trim((string) ($s['last_check_at'] ?? ''));
                        if ($lc !== '') {
                            echo '<div style="margin-top:6px; font-size:12px; opacity:.85;"><code>' . View::e($lc) . '</code></div>';
                        }
                    }

                    if (array_key_exists('last_error', $s)) {
                        $err = trim((string) ($s['last_error'] ?? ''));
                        if ($err !== '') {
                            $errShort = (function_exists('mb_substr') ? mb_substr($err, 0, 80) : substr($err, 0, 80));
                            echo '<div style="margin-top:4px; font-size:12px; opacity:.85;"><code>' . View::e($errShort) . '</code></div>';
                        }
                    }
                  ?>
                </td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;">
                  <a href="/equipe/servidores/editar?id=<?php echo (int) ($s['id'] ?? 0); ?>">Editar</a>
                  <div style="margin-top:6px;">
                    <a href="/equipe/servidores/terminal-seguro?id=<?php echo (int) ($s['id'] ?? 0); ?>">Terminal seguro</a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($servidores)): ?>
              <tr>
                <td colspan="11" style="padding:12px;">Nenhum servidor cadastrado ainda.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</body>
</html>
