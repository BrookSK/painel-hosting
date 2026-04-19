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
          <?php
            $vid = (int)($v['id']??0);
            $st  = (string)($v['status']??'');
            // Ações permitidas por status
            $emTransicao = in_array($st, ['provisioning'], true);
            $acoes = [
                'provisionar' => [I18n::t('eq_vps.provisionar'), 'btn-primary',
                    in_array($st, ['pending_payment','pending_node','pending_provisioning','error'], true)],
                'reativar'    => [I18n::t('eq_vps.reativar'), 'btn-outline',
                    $st === 'suspended_payment'],
                'reiniciar'   => [I18n::t('eq_vps.reiniciar'), 'btn-outline',
                    $st === 'running'],
                'suspender'   => [I18n::t('eq_vps.suspender'), 'btn-outline',
                    $st === 'running'],
                'remover'     => [I18n::t('eq_vps.remover'), 'btn-outline',
                    !$emTransicao],
            ];
          ?>
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
            <td><?php echo badgeStatusVpsEquipe($st); ?></td>
            <td><?php echo View::e((string)($v['server_id']??'')); ?></td>
            <td>
              <div class="linha" style="gap:4px;flex-wrap:wrap;">
                <?php foreach ($acoes as $acao => [$label, $cls, $habilitado]): ?>
                  <form method="post" action="/equipe/vps/<?php echo $acao; ?>"<?php echo $acao==='remover' ? ' data-confirm="Remover VPS #'.$vid.'?"' : ''; ?> style="display:flex;align-items:center;gap:4px;">
                    <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
                    <input type="hidden" name="vps_id" value="<?php echo $vid; ?>" />
                    <?php if ($acao === 'provisionar' && $habilitado): ?>
                      <select name="server_id" style="font-size:11px;padding:2px 4px;border:1px solid #cbd5e1;border-radius:4px;max-width:120px;">
                        <option value="0">Auto</option>
                        <?php
                          static $_managedServers = null;
                          if ($_managedServers === null) {
                              try {
                                  $_managedServers = \LRV\Core\BancoDeDados::pdo()->query("SELECT id, hostname FROM servers WHERE status = 'active' AND is_managed_server = 1 ORDER BY hostname")->fetchAll() ?: [];
                              } catch (\Throwable) { $_managedServers = []; }
                          }
                          // Também listar servidores normais
                          static $_allActiveServers = null;
                          if ($_allActiveServers === null) {
                              try {
                                  $_allActiveServers = \LRV\Core\BancoDeDados::pdo()->query("SELECT id, hostname, is_managed_server FROM servers WHERE status = 'active' ORDER BY hostname")->fetchAll() ?: [];
                              } catch (\Throwable) { $_allActiveServers = []; }
                          }
                          foreach ($_allActiveServers as $_ms):
                        ?>
                          <option value="<?php echo (int)$_ms['id']; ?>"><?php echo View::e((string)$_ms['hostname']); ?><?php echo !empty($_ms['is_managed_server']) ? ' 🔧' : ''; ?></option>
                        <?php endforeach; ?>
                      </select>
                    <?php endif; ?>
                    <button class="btn-sm <?php echo $cls; ?>" type="submit"<?php echo $habilitado ? '' : ' disabled style="opacity:.4;cursor:not-allowed;"'; ?>><?php echo $label; ?></button>
                  </form>
                <?php endforeach; ?>
                <a href="/equipe/vps/logs?id=<?php echo $vid; ?>" class="btn-sm btn-outline" style="text-decoration:none;">📋 Logs</a>
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
