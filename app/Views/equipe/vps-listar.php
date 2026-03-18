<?php

declare(strict_types=1);

use LRV\Core\View;
use LRV\Core\I18n;

function gb(int $mb): string
{
    if ($mb <= 0) {
        return '0 GB';
    }
    return (string) ((int) round($mb / 1024)) . ' GB';
}

function badgeStatusVpsEquipe(string $st): string
{
    if ($st === 'running') {
        return '<span class="badge">Em execução</span>';
    }

    if ($st === 'suspended_payment') {
        return '<span class="badge" style="background:#fee2e2;color:#991b1b;">Suspensa</span>';
    }

    if ($st === 'pending_payment') {
        return '<span class="badge" style="background:#fef3c7;color:#92400e;">Aguardando pagamento</span>';
    }

    if ($st === 'pending_node') {
        return '<span class="badge" style="background:#fef3c7;color:#92400e;">Aguardando node</span>';
    }

    if ($st === 'pending_provisioning') {
        return '<span class="badge" style="background:#fef3c7;color:#92400e;">Provisionamento pendente</span>';
    }

    if ($st === 'provisioning') {
        return '<span class="badge" style="background:#e0e7ff;color:#1e3a8a;">Provisionando</span>';
    }

    if ($st === 'error') {
        return '<span class="badge" style="background:#fee2e2;color:#991b1b;">Erro</span>';
    }

    return '<span class="badge" style="background:#f1f5f9;color:#334155;">' . View::e($st) . '</span>';
}

?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>VPS</title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
</head>
<body>
  <div class="topo">
    <div class="conteudo linha" style="justify-content:space-between;">
      <div>
        <div style="font-size:18px;font-weight:700;">VPS</div>
        <div style="opacity:.9; font-size:13px;">Provisionamento, status e ações</div>
      </div>
      <div class="linha">
        <?php require __DIR__ . '/../_partials/idioma.php'; ?>
        <a href="/equipe/painel">Painel</a>
        <a href="/equipe/planos">Planos</a>
        <a href="/equipe/servidores">Servidores</a>
        <a href="/equipe/tickets">Tickets</a>
        <a href="/equipe/jobs">Jobs</a>
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
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">VPS</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Cliente</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">CPU</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Memória</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Armazenamento</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Status</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Node</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach (($vps ?? []) as $v): ?>
              <tr>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><strong>#<?php echo (int) ($v['id'] ?? 0); ?></strong></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;">
                  <div><strong><?php echo View::e((string) ($v['client_name'] ?? '')); ?></strong></div>
                  <div style="font-size:12px; opacity:.8;"><?php echo View::e((string) ($v['client_email'] ?? '')); ?></div>
                </td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo View::e((string) ($v['cpu'] ?? '')); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo View::e(gb((int) ($v['ram'] ?? 0))); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo View::e(gb((int) ($v['storage'] ?? 0))); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo badgeStatusVpsEquipe((string) ($v['status'] ?? '')); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo View::e((string) ($v['server_id'] ?? '')); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;">
                  <form method="post" action="/equipe/vps/provisionar" style="display:inline-block;">
                    <input type="hidden" name="vps_id" value="<?php echo (int) ($v['id'] ?? 0); ?>" />
                    <button class="botao" type="submit" style="padding:8px 10px;">Provisionar</button>
                  </form>

                  <form method="post" action="/equipe/vps/reativar" style="display:inline-block; margin-left:6px;">
                    <input type="hidden" name="vps_id" value="<?php echo (int) ($v['id'] ?? 0); ?>" />
                    <button class="botao" type="submit" style="padding:8px 10px; background:#0B1C3D;">Reativar</button>
                  </form>

                  <form method="post" action="/equipe/vps/suspender" style="display:inline-block; margin-left:6px;">
                    <input type="hidden" name="vps_id" value="<?php echo (int) ($v['id'] ?? 0); ?>" />
                    <button class="botao" type="submit" style="padding:8px 10px; background:#991b1b;">Suspender</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($vps)): ?>
              <tr>
                <td colspan="8" style="padding:12px;">Nenhuma VPS encontrada.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</body>
</html>
