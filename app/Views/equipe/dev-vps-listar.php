<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;
use LRV\Core\Csrf;

$pageTitle = 'VPS da Equipe';
require __DIR__ . '/../_partials/layout-equipe-inicio.php';

function gbTeamVps(int $mb): string {
    if ($mb <= 0) return '0 GB';
    return ((int)round($mb / 1024)) . ' GB';
}
?>
<div class="page-title">VPS da Equipe</div>
<div class="page-subtitle">Crie e gerencie VPS internas da empresa para desenvolvimento e ambientes de teste.</div>

<?php if (!empty($erro)): ?>
  <div class="erro"><?php echo View::e($erro); ?></div>
<?php endif; ?>
<?php if (!empty($sucesso)): ?>
  <div class="sucesso"><?php echo View::e($sucesso); ?></div>
<?php endif; ?>

<!-- VPS existentes -->
<?php if (!empty($vpsList)): ?>
<div class="card-new" style="margin-bottom:24px;">
  <div class="texto" style="margin-bottom:0;"><strong>VPS ativas (<?php echo count($vpsList); ?>)</strong></div>
  <div style="overflow:auto;margin-top:12px;">
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Nome</th>
          <th>Servidor</th>
          <th>Recursos</th>
          <th>Status</th>
          <th>Criada em</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($vpsList as $v):
          $vid = (int)($v['id'] ?? 0);
          $nome = (string)($v['team_vps_name'] ?? $v['name'] ?? '');
          $hostname = (string)($v['hostname'] ?? '—');
          $ip = (string)($v['ip_address'] ?? '');
          $st = (string)($v['status'] ?? '');
          $createdAt = (string)($v['created_at'] ?? '');
          $stMap = [
              'running' => ['Em execução', 'badge-green'],
              'pending_provisioning' => ['Aguardando', 'badge-yellow'],
              'provisioning' => ['Provisionando', 'badge-blue'],
              'error' => ['Erro', 'badge-red'],
              'suspended_payment' => ['Suspensa', 'badge-red'],
          ];
          $badge = $stMap[$st] ?? [ucfirst($st), 'badge-gray'];
        ?>
          <tr>
            <td><strong>#<?php echo $vid; ?></strong></td>
            <td><?php echo View::e($nome); ?></td>
            <td>
              <?php echo View::e($hostname); ?>
              <?php if ($ip !== ''): ?>
                <br><code style="font-size:11px;"><?php echo View::e($ip); ?></code>
              <?php endif; ?>
            </td>
            <td><?php echo (int)($v['cpu'] ?? 0); ?> vCPU · <?php echo gbTeamVps((int)($v['ram'] ?? 0)); ?> · <?php echo gbTeamVps((int)($v['storage'] ?? 0)); ?></td>
            <td><span class="badge-new <?php echo $badge[1]; ?>"><?php echo View::e($badge[0]); ?></span></td>
            <td><?php echo $createdAt !== '' ? date('d/m/Y H:i', strtotime($createdAt)) : '—'; ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<!-- Criar nova VPS -->
<div class="card-new">
  <div class="texto" style="margin-bottom:16px;"><strong>Criar nova VPS da Equipe</strong></div>

  <form method="post" action="/equipe/vps-equipe/salvar">
    <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>" />

    <div class="form-group">
      <label class="form-label">Nome</label>
      <input type="text" name="name" class="form-input" required maxlength="150" placeholder="Ex: VPS Dev 1, Staging, QA..." />
    </div>

    <div class="form-group">
      <label class="form-label">Servidor</label>
      <select name="server_id" class="form-input" required>
        <option value="">— Selecione o servidor —</option>
        <?php foreach (($servidores ?? []) as $s): ?>
          <option value="<?php echo (int)$s['id']; ?>">
            <?php echo View::e((string)($s['hostname'] ?? '')); ?> (<?php echo View::e((string)($s['ip_address'] ?? '')); ?>) — <?php echo (int)($s['cpu_total'] ?? 0); ?> CPU / <?php echo gbTeamVps((int)($s['ram_total'] ?? 0)); ?> RAM / <?php echo gbTeamVps((int)($s['storage_total'] ?? 0)); ?> Disco
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div style="display:flex;gap:16px;flex-wrap:wrap;">
      <div class="form-group" style="flex:1;min-width:140px;">
        <label class="form-label">vCPU</label>
        <select name="cpu" class="form-input">
          <option value="1">1 vCPU</option>
          <option value="2" selected>2 vCPU</option>
          <option value="4">4 vCPU</option>
          <option value="8">8 vCPU</option>
          <option value="16">16 vCPU</option>
        </select>
      </div>
      <div class="form-group" style="flex:1;min-width:140px;">
        <label class="form-label">Memória RAM</label>
        <select name="ram" class="form-input">
          <option value="1024">1 GB</option>
          <option value="2048" selected>2 GB</option>
          <option value="4096">4 GB</option>
          <option value="8192">8 GB</option>
          <option value="16384">16 GB</option>
          <option value="32768">32 GB</option>
        </select>
      </div>
      <div class="form-group" style="flex:1;min-width:140px;">
        <label class="form-label">Disco</label>
        <select name="storage" class="form-input">
          <option value="10240">10 GB</option>
          <option value="20480" selected>20 GB</option>
          <option value="51200">50 GB</option>
          <option value="102400">100 GB</option>
          <option value="204800">200 GB</option>
        </select>
      </div>
    </div>

    <div style="margin-top:16px;">
      <button type="submit" class="botao">Criar VPS</button>
    </div>
  </form>
</div>

<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
