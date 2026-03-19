<?php

declare(strict_types=1);

use LRV\Core\I18n;
use LRV\Core\View;

function badgeBackupStatus(string $st): string
{
    if ($st === 'completed') {
        return '<span class="badge" style="background:#dcfce7;color:#166534;">Concluído</span>';
    }
    if ($st === 'failed') {
        return '<span class="badge" style="background:#fee2e2;color:#991b1b;">Falhou</span>';
    }
    if ($st === 'running') {
        return '<span class="badge" style="background:#e0e7ff;color:#1e3a8a;">Rodando</span>';
    }
    return '<span class="badge" style="background:#fef3c7;color:#92400e;">Na fila</span>';
}

function fmtBytes(int $b): string
{
    if ($b <= 0) {
        return '0 B';
    }
    $kb = 1024;
    $mb = $kb * 1024;
    $gb = $mb * 1024;

    if ($b >= $gb) {
        return number_format($b / $gb, 2, ',', '.') . ' GB';
    }
    if ($b >= $mb) {
        return number_format($b / $mb, 2, ',', '.') . ' MB';
    }
    if ($b >= $kb) {
        return number_format($b / $kb, 2, ',', '.') . ' KB';
    }
    return (string) $b . ' B';
}

?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Backups</title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
</head>
<body>
  <div class="topo">
    <div class="conteudo linha" style="justify-content:space-between;">
      <div>
        <div style="font-size:18px;font-weight:700;">Backups</div>
        <div style="opacity:.9; font-size:13px;">VPS (últimos 200)</div>
      </div>
      <div class="linha">
        <?php require __DIR__ . '/../_partials/idioma.php'; ?>
        <a href="/equipe/painel">Painel</a>
        <a href="/equipe/vps">VPS</a>
        <a href="/equipe/jobs">Jobs</a>
        <a href="/equipe/sair">Sair</a>
      </div>
    </div>
  </div>

  <div class="conteudo">
    <div class="card" style="margin-bottom:12px;">
      <form method="post" action="/equipe/backups/criar" class="linha" style="justify-content:space-between;">
        <div style="flex:1; min-width:280px;">
          <label style="display:block; font-size:13px; margin-bottom:6px;">Criar backup para VPS</label>
          <select class="input" name="vps_id">
            <option value="">Selecione...</option>
            <?php foreach (($vps ?? []) as $vv): ?>
              <option value="<?php echo (int) ($vv['id'] ?? 0); ?>">#<?php echo (int) ($vv['id'] ?? 0); ?> (<?php echo View::e((string) ($vv['client_email'] ?? '')); ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div style="align-self:end;">
          <button class="botao" type="submit">Criar backup</button>
        </div>
      </form>
    </div>

    <div class="card">
      <div style="overflow:auto;">
        <table style="width:100%; border-collapse:collapse;">
          <thead>
            <tr>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">ID</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">VPS</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Cliente</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Status</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Tamanho</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Criado</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach (($backups ?? []) as $b): ?>
              <tr>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><strong>#<?php echo (int) ($b['id'] ?? 0); ?></strong></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;">#<?php echo (int) ($b['vps_id'] ?? 0); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo View::e((string) ($b['client_email'] ?? '')); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo badgeBackupStatus((string) ($b['status'] ?? 'queued')); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><code><?php echo View::e(fmtBytes((int) ($b['file_size'] ?? 0))); ?></code></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo View::e((string) ($b['created_at'] ?? '')); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;">
                  <?php if (!empty($b['job_id'])): ?>
                    <a href="/equipe/jobs/ver?id=<?php echo (int) ($b['job_id'] ?? 0); ?>">Job</a>
                    <span style="opacity:.4; margin:0 6px;">|</span>
                  <?php endif; ?>

                  <?php if (((string) ($b['status'] ?? '')) === 'completed'): ?>
                    <a href="/equipe/backups/baixar?id=<?php echo (int) ($b['id'] ?? 0); ?>">Baixar</a>
                    <span style="opacity:.4; margin:0 6px;">|</span>
                  <?php endif; ?>

                  <form method="post" action="/equipe/backups/excluir" style="display:inline;" onsubmit="return confirm('Excluir este backup?');">
                    <input type="hidden" name="id" value="<?php echo (int) ($b['id'] ?? 0); ?>" />
                    <button class="botao sec" type="submit" style="padding:6px 10px; border-radius:10px;">Excluir</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($backups)): ?>
              <tr>
                <td colspan="7" style="padding:12px;">Nenhum backup ainda.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</body>
</html>
