<?php
declare(strict_types=1);
use LRV\Core\View;

function badgeBackupStatus(string $st): string {
    if ($st==='completed') return '<span class="badge-new badge-green">Concluido</span>';
    if ($st==='failed')    return '<span class="badge-new badge-red">Falhou</span>';
    if ($st==='running')   return '<span class="badge-new badge-blue">Rodando</span>';
    return '<span class="badge-new badge-yellow">Na fila</span>';
}
function fmtBytes(int $b): string {
    if ($b<=0) return '0 B';
    $kb=1024;$mb=$kb*1024;$gb=$mb*1024;
    if ($b>=$gb) return number_format($b/$gb,2,',','.').' GB';
    if ($b>=$mb) return number_format($b/$mb,2,',','.').' MB';
    if ($b>=$kb) return number_format($b/$kb,2,',','.').' KB';
    return $b.' B';
}

$pageTitle = 'Backups';
require __DIR__ . '/../_partials/layout-equipe-inicio.php';
?>
<div class="page-title">Backups</div>
<div class="page-subtitle">VPS (ultimos 200)</div>

<div class="card-new" style="margin-bottom:14px;">
  <form method="post" action="/equipe/backups/criar" class="linha" style="justify-content:space-between;gap:12px;">
    <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
    <div style="flex:1;min-width:280px;">
      <label style="display:block;font-size:13px;margin-bottom:6px;">Criar backup para VPS</label>
      <select class="input" name="vps_id">
        <option value="">Selecione...</option>
        <?php foreach (($vps??[]) as $vv): ?>
          <option value="<?php echo (int)($vv['id']??0); ?>">#<?php echo (int)($vv['id']??0); ?> (<?php echo View::e((string)($vv['client_email']??'')); ?>)</option>
        <?php endforeach; ?>
      </select>
    </div>
    <div style="align-self:end;">
      <button class="botao" type="submit">Criar backup</button>
    </div>
  </form>
</div>

<div class="card-new">
  <div style="overflow:auto;">
    <table>
      <thead>
        <tr><th>ID</th><th>VPS</th><th>Cliente</th><th>Status</th><th>Tamanho</th><th>Criado</th><th>Acoes</th></tr>
      </thead>
      <tbody>
        <?php foreach (($backups??[]) as $b): ?>
          <tr>
            <td><strong>#<?php echo (int)($b['id']??0); ?></strong></td>
            <td>#<?php echo (int)($b['vps_id']??0); ?></td>
            <td><?php echo View::e((string)($b['client_email']??'')); ?></td>
            <td><?php echo badgeBackupStatus((string)($b['status']??'queued')); ?></td>
            <td><code><?php echo View::e(fmtBytes((int)($b['file_size']??0))); ?></code></td>
            <td><?php echo View::e((string)($b['created_at']??'')); ?></td>
            <td>
              <?php if (!empty($b['job_id'])): ?><a href="/equipe/jobs/ver?id=<?php echo (int)($b['job_id']??0); ?>">Job</a> | <?php endif; ?>
              <?php if (((string)($b['status']??'))==='completed'): ?><a href="/equipe/backups/baixar?id=<?php echo (int)($b['id']??0); ?>">Baixar</a> | <?php endif; ?>
              <form method="post" action="/equipe/backups/excluir" style="display:inline;" onsubmit="return confirm('Excluir este backup?');">
                <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
                <input type="hidden" name="id" value="<?php echo (int)($b['id']??0); ?>" />
                <button class="botao sec" type="submit" style="padding:4px 8px;font-size:12px;">Excluir</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($backups)): ?>
          <tr><td colspan="7">Nenhum backup ainda.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
