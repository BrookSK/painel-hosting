<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\Csrf;
use LRV\Core\I18n;

$vpsList = is_array($vpsList ?? null) ? $vpsList : [];
$backups = is_array($backups ?? null) ? $backups : [];
$sucesso = (string)($sucesso ?? '');
$erro    = (string)($erro ?? '');
$erroParam = (string)($_GET['erro'] ?? '');

$pageTitle = 'Backups';
require __DIR__ . '/../_partials/layout-cliente-inicio.php';

function _bkStatus(string $s): string {
    return match($s) {
        'completed' => '<span class="badge-new badge-green">Concluído</span>',
        'running'   => '<span class="badge-new badge-yellow">Em andamento</span>',
        'queued'    => '<span class="badge-new badge-gray">Na fila</span>',
        'failed'    => '<span class="badge-new badge-red">Falhou</span>',
        default     => '<span class="badge-new badge-gray">' . View::e($s) . '</span>',
    };
}
function _bkSize(int $bytes): string {
    if ($bytes <= 0) return '—';
    if ($bytes < 1048576) return round($bytes / 1024) . ' KB';
    if ($bytes < 1073741824) return round($bytes / 1048576, 1) . ' MB';
    return round($bytes / 1073741824, 2) . ' GB';
}
?>

<div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;margin-bottom:24px;">
  <div>
    <div class="page-title">Backups</div>
    <div class="page-subtitle" style="margin-bottom:0;">Gerencie os backups da sua VPS</div>
  </div>
</div>

<?php if ($sucesso === 'criado'): ?>
  <div class="sucesso">Backup solicitado. Ele será processado em breve.</div>
<?php elseif ($sucesso === 'restaurando'): ?>
  <div class="sucesso">Restauração iniciada. Sua VPS será atualizada em breve.</div>
<?php endif; ?>
<?php if ($erroParam === 'sem_slots'): ?>
  <div class="erro">Seu plano não inclui backups. Faça upgrade para ter acesso.</div>
<?php elseif ($erroParam === 'em_andamento'): ?>
  <div class="erro">Já existe um backup em andamento para esta VPS.</div>
<?php endif; ?>

<!-- Criar backup -->
<?php foreach ($vpsList as $v):
  $vId = (int)($v['id'] ?? 0);
  $slots = (int)($v['backup_slots'] ?? 0);
  $vpsBackups = array_filter($backups, fn($b) => (int)($b['vps_id'] ?? 0) === $vId);
  $completedCount = count(array_filter($vpsBackups, fn($b) => ($b['status'] ?? '') === 'completed'));
?>
<div class="card-new" style="margin-bottom:16px;">
  <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:12px;">
    <div>
      <div style="font-weight:700;font-size:15px;">VPS #<?php echo $vId; ?></div>
      <div style="font-size:12px;color:#64748b;">
        <?php echo (int)($v['cpu'] ?? 0); ?> vCPU · <?php echo round((int)($v['ram'] ?? 0) / 1024); ?> GB RAM ·
        Backups: <?php echo $completedCount; ?>/<?php echo $slots; ?> slots
      </div>
    </div>
    <?php if ($slots > 0): ?>
    <form method="post" action="/cliente/backups/criar" onsubmit="return confirm('Criar um novo backup? Se o limite for atingido, o mais antigo será removido.')">
      <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>" />
      <input type="hidden" name="vps_id" value="<?php echo $vId; ?>" />
      <button class="botao sm" type="submit">💾 Criar backup</button>
    </form>
    <?php else: ?>
    <span style="font-size:12px;color:#94a3b8;">Plano sem backup</span>
    <?php endif; ?>
  </div>

  <?php if (empty($vpsBackups)): ?>
    <div style="text-align:center;padding:20px 0;color:#94a3b8;font-size:13px;">Nenhum backup ainda.</div>
  <?php else: ?>
    <div style="overflow:auto;">
      <table style="width:100%;border-collapse:collapse;font-size:13px;">
        <thead>
          <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
            <th style="padding:8px 12px;text-align:left;">#</th>
            <th style="padding:8px 12px;text-align:left;">Status</th>
            <th style="padding:8px 12px;text-align:left;">Tamanho</th>
            <th style="padding:8px 12px;text-align:left;">Criado</th>
            <th style="padding:8px 12px;text-align:left;">Concluído</th>
            <th style="padding:8px 12px;text-align:left;">Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($vpsBackups as $b): $bId = (int)($b['id'] ?? 0); ?>
          <tr style="border-bottom:1px solid #f1f5f9;">
            <td style="padding:8px 12px;color:#94a3b8;">#<?php echo $bId; ?></td>
            <td style="padding:8px 12px;"><?php echo _bkStatus((string)($b['status'] ?? '')); ?></td>
            <td style="padding:8px 12px;"><?php echo _bkSize((int)($b['file_size'] ?? 0)); ?></td>
            <td style="padding:8px 12px;"><?php echo View::e(date('d/m/Y H:i', strtotime((string)($b['created_at'] ?? 'now')))); ?></td>
            <td style="padding:8px 12px;"><?php echo ($b['completed_at'] ?? null) ? View::e(date('d/m/Y H:i', strtotime((string)$b['completed_at']))) : '—'; ?></td>
            <td style="padding:8px 12px;">
              <?php if (($b['status'] ?? '') === 'completed'): ?>
                <a href="/cliente/backups/baixar?id=<?php echo $bId; ?>" class="botao ghost sm" style="font-size:11px;">⬇ Baixar</a>
                <form method="post" action="/cliente/backups/restaurar" style="display:inline;" onsubmit="return confirm('Restaurar este backup? Os dados atuais da VPS serão substituídos.')">
                  <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>" />
                  <input type="hidden" name="id" value="<?php echo $bId; ?>" />
                  <button class="botao sm" type="submit" style="font-size:11px;background:#f59e0b;">🔄 Restaurar</button>
                </form>
              <?php elseif (($b['status'] ?? '') === 'failed'): ?>
                <span style="font-size:11px;color:#ef4444;" title="<?php echo View::e((string)($b['error'] ?? '')); ?>">Erro</span>
              <?php else: ?>
                <span style="font-size:11px;color:#64748b;">Aguardando...</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
<?php endforeach; ?>

<?php if (empty($vpsList)): ?>
<div class="card-new" style="text-align:center;padding:32px;">
  <div style="font-size:28px;margin-bottom:8px;">💾</div>
  <div style="font-size:14px;font-weight:600;margin-bottom:4px;">Nenhuma VPS ativa</div>
  <div style="font-size:13px;color:#64748b;">Assine um plano para ter acesso a backups.</div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
