<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;

function gb(int $mb): string {
    if ($mb <= 0) return '0 GB';
    return ((int)round($mb/1024)).' GB';
}

function badgeStatusVpsEquipe(string $st): string {
    $map = [
        'running'              => [I18n::t('eq_vps.em_execucao'),'badge-green'],
        'suspended_payment'    => [I18n::t('eq_vps.suspensa'),'badge-red'],
        'pending_payment'      => [I18n::t('eq_vps.aguard_pagamento'),'badge-yellow'],
        'pending_node'         => [I18n::t('eq_vps.aguard_node'),'badge-yellow'],
        'pending_provisioning' => [I18n::t('eq_vps.prov_pendente'),'badge-yellow'],
        'provisioning'         => [I18n::t('eq_vps.provisionando'),'badge-blue'],
        'error'                => [I18n::t('eq_vps.erro'),'badge-red'],
        'removed'              => [I18n::t('eq_vps.removida'),'badge-gray'],
    ];
    $d = $map[$st] ?? [View::e($st),'badge-gray'];
    return '<span class="badge-new '.$d[1].'">'.$d[0].'</span>';
}

$pageTitle = 'VPS';
require __DIR__ . '/../_partials/layout-equipe-inicio.php';
?>
<div class="page-title"><?php echo View::e(I18n::t('eq_vps.titulo')); ?></div>
<div class="page-subtitle"><?php echo View::e(I18n::t('eq_vps.subtitulo')); ?></div>

<div class="card-new">
  <div style="overflow:auto;">
    <table>
      <thead>
        <tr>
          <th>VPS</th><th><?php echo View::e(I18n::t('eq_vps.cliente')); ?></th><th><?php echo View::e(I18n::t('eq_vps.cpu_ram_disco')); ?></th><th><?php echo View::e(I18n::t('eq_vps.status')); ?></th><th><?php echo View::e(I18n::t('eq_vps.node')); ?></th><th><?php echo View::e(I18n::t('eq_vps.acoes')); ?></th>
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
                <?php foreach ([['provisionar',I18n::t('eq_vps.provisionar'),'btn-primary'],['reativar',I18n::t('eq_vps.reativar'),'btn-outline'],['reiniciar',I18n::t('eq_vps.reiniciar'),'btn-outline'],['suspender',I18n::t('eq_vps.suspender'),'btn-outline'],['remover',I18n::t('eq_vps.remover'),'btn-outline']] as [$acao,$label,$cls]): ?>
                  <form method="post" action="/equipe/vps/<?php echo $acao; ?>"<?php echo $acao==='remover' ? ' data-confirm="Remover VPS #'.$vid.'?"' : ''; ?>>
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
          <tr><td colspan="6"><?php echo View::e(I18n::t('eq_vps.nenhuma')); ?></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<script>
document.querySelectorAll('form').forEach(function(f){
  f.addEventListener('submit',function(e){
    var msg=f.getAttribute('data-confirm');
    if(msg&&!confirm(msg)){e.preventDefault();return;}
    var b=f.querySelector('button[type="submit"]');
    if(b&&!b.disabled){
      setTimeout(function(){b.disabled=true;b.innerHTML='<span class="loading"></span>';},0);
    }
  });
});
</script>
<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
