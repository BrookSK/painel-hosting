<?php
declare(strict_types=1);
use LRV\Core\View;

function formatarGb(int $mb): string {
    if ($mb<=0) return '0 GB';
    return ((int)round($mb/1024)).' GB';
}

$pageTitle = 'Servidores';
require __DIR__ . '/../_partials/layout-equipe-inicio.php';
?>
<div class="page-title">Servidores</div>
<div class="page-subtitle">Nodes do cluster e capacidade disponivel</div>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
  <span class="texto" style="margin:0;">Cadastre seus nodes e a capacidade total.</span>
  <a class="botao" href="/equipe/servidores/novo">Novo servidor</a>
</div>

<div class="card-new">
  <div style="overflow:auto;">
    <table>
      <thead>
        <tr><th>Hostname</th><th>IP</th><th>SSH</th><th>Usuario</th><th>Chave</th><th>CPU</th><th>Memoria</th><th>Armazenamento</th><th>Status</th><th>Acoes</th></tr>
      </thead>
      <tbody>
        <?php foreach (($servidores??[]) as $s): ?>
          <tr>
            <td><strong><?php echo View::e((string)($s['hostname']??'')); ?></strong></td>
            <td><?php echo View::e((string)($s['ip_address']??'')); ?></td>
            <td><?php echo View::e((string)($s['ssh_port']??'')); ?></td>
            <td><?php echo View::e((string)($s['ssh_user']??'')); ?></td>
            <td><code><?php echo View::e((string)($s['ssh_key_id']??'')); ?></code></td>
            <td><?php echo View::e((string)($s['cpu_used']??0)); ?>/<?php echo View::e((string)($s['cpu_total']??0)); ?></td>
            <td><?php echo View::e(formatarGb((int)($s['ram_used']??0))); ?>/<?php echo View::e(formatarGb((int)($s['ram_total']??0))); ?></td>
            <td><?php echo View::e(formatarGb((int)($s['storage_used']??0))); ?>/<?php echo View::e(formatarGb((int)($s['storage_total']??0))); ?></td>
            <td>
              <?php
                $st = (string)($s['status']??'');
                if ($st==='active') echo '<span class="badge-new badge-green">Ativo</span>';
                elseif ($st==='maintenance') echo '<span class="badge-new badge-yellow">Manutencao</span>';
                else echo '<span class="badge-new badge-gray">Inativo</span>';
                if (array_key_exists('is_online',$s)) {
                    echo (int)($s['is_online']??0)===1 ? ' <span class="badge-new badge-green">Online</span>' : ' <span class="badge-new badge-red">Offline</span>';
                }
              ?>
            </td>
            <td>
              <a href="/equipe/servidores/editar?id=<?php echo (int)($s['id']??0); ?>">Editar</a>
              <div style="margin-top:4px;"><a href="/equipe/servidores/terminal-seguro?id=<?php echo (int)($s['id']??0); ?>">Terminal seguro</a></div>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($servidores)): ?>
          <tr><td colspan="10">Nenhum servidor cadastrado ainda.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
