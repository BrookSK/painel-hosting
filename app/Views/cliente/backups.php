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
$pastasExtras = is_array($pastasExtras ?? null) ? $pastasExtras : [];
$pastasDeploy = is_array($pastasDeploy ?? null) ? $pastasDeploy : [];

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
<?php elseif ($sucesso === 'pastas'): ?>
  <div class="sucesso">Pastas do backup atualizadas.</div>
<?php endif; ?>
<?php if ($erroParam === 'sem_slots'): ?>
  <div class="erro">Seu plano não inclui backups. Faça upgrade para ter acesso.</div>
<?php elseif ($erroParam === 'em_andamento'): ?>
  <div class="erro">Já existe um backup em andamento para esta VPS.</div>
<?php elseif ($erro !== ''): ?>
  <div class="erro"><?php echo View::e($erro); ?></div>
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
      <button class="botao sm" type="submit"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg> Criar backup</button>
    </form>
    <?php else: ?>
    <span style="font-size:12px;color:#94a3b8;">Plano sem backup</span>
    <?php endif; ?>
  </div>

  <?php if ($slots > 0):
    $extrasV = $pastasExtras[$vId] ?? [];
    $atalhos = array_values(array_diff($pastasDeploy[$vId] ?? [], $extrasV));
  ?>
  <div style="border:1px solid #e2e8f0;border-radius:8px;padding:12px;margin-bottom:12px;font-size:13px;">
    <div style="font-weight:600;margin-bottom:6px;">Pastas extras no backup</div>
    <?php if (empty($extrasV)): ?>
      <div style="color:#94a3b8;margin-bottom:8px;">Nenhuma pasta extra.</div>
    <?php else: ?>
      <?php foreach ($extrasV as $p): ?>
      <form method="post" action="/cliente/backups/pastas" style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
        <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>" />
        <input type="hidden" name="vps_id" value="<?php echo $vId; ?>" />
        <input type="hidden" name="acao" value="remover" />
        <input type="hidden" name="caminho" value="<?php echo View::e($p); ?>" />
        <code><?php echo View::e($p); ?></code>
        <button class="botao ghost sm" type="submit" style="font-size:11px;">Remover</button>
      </form>
      <?php endforeach; ?>
    <?php endif; ?>
    <form method="post" action="/cliente/backups/pastas" style="display:flex;gap:8px;flex-wrap:wrap;margin-top:8px;">
      <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>" />
      <input type="hidden" name="vps_id" value="<?php echo $vId; ?>" />
      <input type="hidden" name="acao" value="adicionar" />
      <input type="text" name="caminho" placeholder="/var/www/meu-projeto/uploads" maxlength="300" style="flex:1;min-width:220px;" />
      <button class="botao sm" type="submit" style="font-size:11px;">Adicionar</button>
    </form>
    <?php if (!empty($atalhos)): ?>
    <div style="margin-top:8px;display:flex;gap:6px;flex-wrap:wrap;align-items:center;">
      <span style="color:#64748b;">Adicionar rápido:</span>
      <?php foreach ($atalhos as $p): ?>
      <form method="post" action="/cliente/backups/pastas" style="display:inline;">
        <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>" />
        <input type="hidden" name="vps_id" value="<?php echo $vId; ?>" />
        <input type="hidden" name="acao" value="adicionar" />
        <input type="hidden" name="caminho" value="<?php echo View::e($p); ?>" />
        <button class="botao ghost sm" type="submit" style="font-size:11px;">+ <?php echo View::e($p); ?></button>
      </form>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <div style="margin-top:8px;font-size:12px;color:#64748b;">Os bancos de dados desta VPS também entram no backup, mas não são restaurados automaticamente: a cópia fica dentro do arquivo baixado, na pasta <code>_lrv_db</code>.</div>
  </div>
  <?php endif; ?>

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
            <td style="padding:8px 12px;"><?php echo _bkStatus((string)($b['status'] ?? '')); ?>
              <?php if (trim((string)($b['warnings'] ?? '')) !== ''): ?>
                <div style="font-size:11px;color:#b45309;white-space:pre-line;margin-top:4px;">⚠ <?php echo View::e((string)$b['warnings']); ?></div>
              <?php endif; ?>
            </td>
            <td style="padding:8px 12px;"><?php echo _bkSize((int)($b['file_size'] ?? 0)); ?></td>
            <td style="padding:8px 12px;"><?php echo View::e(date('d/m/Y H:i', strtotime((string)($b['created_at'] ?? 'now')))); ?></td>
            <td style="padding:8px 12px;"><?php echo ($b['completed_at'] ?? null) ? View::e(date('d/m/Y H:i', strtotime((string)$b['completed_at']))) : '—'; ?></td>
            <td style="padding:8px 12px;">
              <?php if (($b['status'] ?? '') === 'completed'): ?>
                <a href="/cliente/backups/baixar?id=<?php echo $bId; ?>" class="botao ghost sm" style="font-size:11px;">⬇ Baixar</a>
                <form method="post" action="/cliente/backups/restaurar" style="display:inline;" onsubmit="return confirm('Restaurar este backup? Os dados atuais da VPS serão substituídos.')">
                  <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>" />
                  <input type="hidden" name="id" value="<?php echo $bId; ?>" />
                  <button class="botao sm" type="submit" style="font-size:11px;background:#f59e0b;"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg> Restaurar</button>
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
  <div style="font-size:28px;margin-bottom:8px;"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg></div>
  <div style="font-size:14px;font-weight:600;margin-bottom:4px;">Nenhuma VPS ativa</div>
  <div style="font-size:13px;color:#64748b;">Assine um plano para ter acesso a backups.</div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
