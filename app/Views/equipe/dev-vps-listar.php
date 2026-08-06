<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;
use LRV\Core\Csrf;

$pageTitle = 'VPS da Equipe — Dev Workflow';
require __DIR__ . '/../_partials/layout-equipe-inicio.php';

function gbDev(int $mb): string {
    if ($mb <= 0) return '0 GB';
    return ((int)round($mb / 1024)) . ' GB';
}
?>
<div class="page-title">VPS da Equipe</div>
<div class="page-subtitle">Gerencie as VPS internas da empresa usadas para desenvolvimento e testes.</div>

<a href="/equipe/dev" style="font-size:13px;color:#4F46E5;text-decoration:none;display:inline-flex;align-items:center;gap:4px;margin-bottom:16px;">
  <svg width="14" height="14" viewBox="0 0 20 20" fill="none"><path d="M12 4L6 10l6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
  Voltar aos projetos
</a>

<?php if (!empty($erro)): ?>
<div class="alerta alerta-erro" style="margin-bottom:16px;"><?php echo View::e($erro); ?></div>
<?php endif; ?>
<?php if (!empty($sucesso)): ?>
<div class="alerta alerta-sucesso" style="margin-bottom:16px;"><?php echo View::e($sucesso); ?></div>
<?php endif; ?>

<!-- Formulário para criar VPS -->
<div class="card-new" style="padding:24px;margin-bottom:24px;">
  <h3 style="margin:0 0 16px;font-size:16px;font-weight:700;color:#0f172a;">
    <svg width="18" height="18" viewBox="0 0 20 20" fill="none" style="vertical-align:middle;margin-right:6px;"><rect x="2" y="5" width="16" height="4" rx="1.5" stroke="currentColor" stroke-width="1.6"/><rect x="2" y="11" width="16" height="4" rx="1.5" stroke="currentColor" stroke-width="1.6"/><circle cx="15" cy="7" r="1" fill="currentColor"/><circle cx="15" cy="13" r="1" fill="currentColor"/></svg>
    Criar nova VPS da Equipe
  </h3>
  <form method="post" action="/equipe/dev/vps/salvar">
    <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>" />

    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;">
      <div class="form-group">
        <label class="form-label">Nome da VPS *</label>
        <input type="text" name="name" class="form-input" required maxlength="150" placeholder="Ex: VPS Dev 1, Staging, QA..." />
      </div>

      <div class="form-group">
        <label class="form-label">Servidor *</label>
        <select name="server_id" class="form-input" required>
          <option value="">— Selecione —</option>
          <?php foreach (($servidores ?? []) as $s): ?>
            <option value="<?php echo (int)$s['id']; ?>">
              <?php echo View::e((string)($s['hostname'] ?? '')); ?> (<?php echo View::e((string)($s['ip_address'] ?? '')); ?>) — <?php echo (int)($s['cpu_total'] ?? 0); ?> CPU / <?php echo gbDev((int)($s['ram_total'] ?? 0)); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label">vCPU</label>
        <select name="cpu" class="form-input">
          <option value="1">1 vCPU</option>
          <option value="2" selected>2 vCPU</option>
          <option value="4">4 vCPU</option>
          <option value="8">8 vCPU</option>
          <option value="16">16 vCPU</option>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label">RAM</label>
        <select name="ram" class="form-input">
          <option value="1024">1 GB</option>
          <option value="2048" selected>2 GB</option>
          <option value="4096">4 GB</option>
          <option value="8192">8 GB</option>
          <option value="16384">16 GB</option>
          <option value="32768">32 GB</option>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label">Armazenamento</label>
        <select name="storage" class="form-input">
          <option value="10240">10 GB</option>
          <option value="20480" selected>20 GB</option>
          <option value="51200">50 GB</option>
          <option value="102400">100 GB</option>
          <option value="204800">200 GB</option>
        </select>
      </div>

      <div class="form-group" style="display:flex;align-items:flex-end;">
        <button type="submit" class="botao" style="width:100%;">
          <svg width="14" height="14" viewBox="0 0 20 20" fill="none" style="vertical-align:middle;"><path d="M10 4v12M4 10h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
          Criar VPS
        </button>
      </div>
    </div>
  </form>
</div>

<!-- Lista de VPS da equipe -->
<?php if (empty($vpsList)): ?>
<div class="card-new" style="padding:40px;text-align:center;">
  <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" style="margin:0 auto 12px;display:block;"><rect x="2" y="5" width="20" height="5" rx="2"/><rect x="2" y="13" width="20" height="5" rx="2"/><circle cx="18" cy="7.5" r="1" fill="#94a3b8"/><circle cx="18" cy="15.5" r="1" fill="#94a3b8"/></svg>
  <p style="color:#64748b;margin:0;">Nenhuma VPS da equipe criada ainda. Use o formulário acima para criar a primeira.</p>
</div>
<?php else: ?>
<div class="card-new">
  <div style="overflow:auto;">
    <table>
      <thead>
        <tr>
          <th>#</th>
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
        ?>
          <tr>
            <td><strong>#<?php echo $vid; ?></strong></td>
            <td><?php echo View::e($nome); ?></td>
            <td>
              <span style="font-size:13px;"><?php echo View::e($hostname); ?></span>
              <?php if ($ip !== ''): ?>
                <br><span style="font-size:11px;color:#64748b;"><?php echo View::e($ip); ?></span>
              <?php endif; ?>
            </td>
            <td>
              <?php echo (int)($v['cpu'] ?? 0); ?> vCPU / <?php echo gbDev((int)($v['ram'] ?? 0)); ?> / <?php echo gbDev((int)($v['storage'] ?? 0)); ?>
            </td>
            <td>
              <?php
                $stMap = [
                    'running' => ['Em execução', 'badge-green'],
                    'pending_provisioning' => ['Provisionando', 'badge-yellow'],
                    'provisioning' => ['Provisionando', 'badge-blue'],
                    'error' => ['Erro', 'badge-red'],
                    'suspended_payment' => ['Suspensa', 'badge-red'],
                ];
                $badge = $stMap[$st] ?? [ucfirst($st), 'badge-gray'];
              ?>
              <span class="badge-new <?php echo $badge[1]; ?>"><?php echo View::e($badge[0]); ?></span>
            </td>
            <td><span style="font-size:12px;color:#64748b;"><?php echo $createdAt !== '' ? date('d/m/Y H:i', strtotime($createdAt)) : '—'; ?></span></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
