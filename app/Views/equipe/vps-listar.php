<?php
declare(strict_types=1);
use LRV\Core\View;

function gb(int $mb): string {
    if ($mb <= 0) return '0 GB';
    return ((int)round($mb/1024)).' GB';
}

function badgeStatusVpsEquipe(string $st): string {
    $map = [
        'running'              => ['Em execução','badge-green'],
        'suspended_payment'    => ['Suspensa','badge-red'],
        'pending_payment'      => ['Aguard. pagamento','badge-yellow'],
        'pending_node'         => ['Aguard. node','badge-yellow'],
        'pending_provisioning' => ['Prov. pendente','badge-yellow'],
        'provisioning'         => ['Provisionando','badge-blue'],
        'error'                => ['Erro','badge-red'],
        'removed'              => ['Removida','badge-gray'],
    ];
    $d = $map[$st] ?? [View::e($st),'badge-gray'];
    return '<span class="badge-new '.$d[1].'">'.$d[0].'</span>';
}

$pageTitle = 'VPS';
require __DIR__ . '/../_partials/layout-equipe-inicio.php';
?>
<div class="page-title">VPS</div>
<div class="page-subtitle">Provisionamento, status e ações</div>

<div class="card-new">
  <div style="overflow:auto;">
    <table>
      <thead>
        <tr>
          <th>VPS</th><th>Cliente</th><th>CPU / RAM / Disco</th><th>Status</th><th>Node</th><th>Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach (($vps ?? []) as $v): ?>
          <?php $vid = (int)($v['id']??0); ?>
          <tr>
            <td>
              <strong>#<?php echo $vid; ?></strong>
              <?php if (trim((string)($v['name']??'')) !== ''): ?>
                <div style="font-size:12px;opacity:.8;"><?php echo View::e((string)($v['name']??'')); ?></div>
              <?php endif; ?>
            </td>
            <td>
              <div><strong><?php echo View::e((string)($v['client_name']??'')); ?></strong></div>
              <div style="font-size:12px;opacity:.8;"><?php echo View::e((string)($v['client_email']??'')); ?></div>
            </td>
            <td><?php echo View::e((string)($v['cpu']??'')); ?> vCPU / <?php echo View::e(gb((int)($v['ram']??0))); ?> / <?php echo View::e(gb((int)($v['storage']??0))); ?></td>
            <td><?php echo badgeStatusVpsEquipe((string)($v['status']??'')); ?></td>
            <td><?php echo View::e((string)($v['server_id']??'')); ?></td>
            <td>
              <div class="linha" style="gap:4px;flex-wrap:wrap;">
                <?php foreach ([['provisionar','Provisionar','btn-primary'],['reativar','Reativar','btn-outline'],['reiniciar','Reiniciar','btn-outline'],['suspender','Suspender','btn-outline'],['remover','Remover','btn-outline']] as [$acao,$label,$cls]): ?>
                  <form method="post" action="/equipe/vps/<?php echo $acao; ?>"<?php echo $acao==='remover' ? ' onsubmit="return confirm(\'Remover VPS #'.$vid.'?\')"' : ''; ?>>
                    <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
                    <input type="hidden" name="vps_id" value="<?php echo $vid; ?>" />
                    <button class="btn-sm <?php echo $cls; ?>" type="submit"><?php echo $label; ?></button>
                  </form>
                <?php endforeach; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($vps)): ?>
          <tr><td colspan="6">Nenhuma VPS encontrada.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<script>
document.querySelectorAll('form').forEach(function(f){f.addEventListener('submit',function(){var b=f.querySelector('button[type="submit"]');if(b&&!b.disabled){b.disabled=true;b.innerHTML='<span class="loading"></span>';}});});
</script>
<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
