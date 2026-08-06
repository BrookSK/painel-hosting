<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;
use LRV\Core\Csrf;

$pageTitle = 'VPS da Equipe';
require __DIR__ . '/../_partials/layout-equipe-inicio.php';

function gbDevVps(int $mb): string {
    if ($mb <= 0) return '0 GB';
    return ((int)round($mb / 1024)) . ' GB';
}
?>
<div class="page-title">VPS da Equipe</div>
<div class="page-subtitle">Crie e gerencie VPS internas da empresa para desenvolvimento e ambientes de teste.</div>

<?php if (!empty($erro)): ?>
<div class="alerta alerta-erro" style="margin-bottom:16px;"><?php echo View::e($erro); ?></div>
<?php endif; ?>
<?php if (!empty($sucesso)): ?>
<div class="alerta alerta-sucesso" style="margin-bottom:16px;"><?php echo View::e($sucesso); ?></div>
<?php endif; ?>

<!-- VPS existentes -->
<?php if (!empty($vpsList)): ?>
<div class="card-new" style="margin-bottom:24px;">
  <div style="padding:16px 20px;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;">
    <h3 style="margin:0;font-size:15px;font-weight:700;color:#0f172a;">VPS ativas (<?php echo count($vpsList); ?>)</h3>
  </div>
  <div style="overflow:auto;">
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
        ?>
          <tr>
            <td><span style="font-weight:600;color:#6366f1;">#<?php echo $vid; ?></span></td>
            <td><strong><?php echo View::e($nome); ?></strong></td>
            <td>
              <?php echo View::e($hostname); ?>
              <?php if ($ip !== ''): ?>
                <br><span style="font-size:11px;color:#64748b;"><?php echo View::e($ip); ?></span>
              <?php endif; ?>
            </td>
            <td style="white-space:nowrap;">
              <span style="font-size:13px;"><?php echo (int)($v['cpu'] ?? 0); ?> vCPU</span> ·
              <span style="font-size:13px;"><?php echo gbDevVps((int)($v['ram'] ?? 0)); ?></span> ·
              <span style="font-size:13px;"><?php echo gbDevVps((int)($v['storage'] ?? 0)); ?></span>
            </td>
            <td>
              <?php
                $stMap = [
                    'running' => ['Em execução', 'badge-green'],
                    'pending_provisioning' => ['Aguardando', 'badge-yellow'],
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

<!-- Formulário para criar VPS -->
<div class="card-new" style="padding:28px;">
  <h3 style="margin:0 0 6px;font-size:17px;font-weight:700;color:#0f172a;">Criar nova VPS</h3>
  <p style="margin:0 0 20px;font-size:13px;color:#64748b;">Preencha as configurações da VPS interna. Ela ficará disponível para vincular aos projetos do Dev Workflow.</p>

  <form method="post" action="/equipe/vps-equipe/salvar">
    <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>" />

    <!-- Nome -->
    <div class="form-group" style="margin-bottom:16px;">
      <label class="form-label">Nome *</label>
      <input type="text" name="name" class="form-input" required maxlength="150" placeholder="Ex: VPS Dev 1, Staging, QA..." style="max-width:400px;" />
      <small style="color:#64748b;font-size:11px;display:block;margin-top:4px;">Um nome amigável para identificar esta VPS internamente.</small>
    </div>

    <!-- Servidor -->
    <div class="form-group" style="margin-bottom:16px;">
      <label class="form-label">Servidor *</label>
      <select name="server_id" class="form-input" required style="max-width:500px;">
        <option value="">— Selecione o servidor —</option>
        <?php foreach (($servidores ?? []) as $s): ?>
          <option value="<?php echo (int)$s['id']; ?>">
            <?php echo View::e((string)($s['hostname'] ?? '')); ?> — <?php echo View::e((string)($s['ip_address'] ?? '')); ?> (<?php echo (int)($s['cpu_total'] ?? 0); ?> CPU · <?php echo gbDevVps((int)($s['ram_total'] ?? 0)); ?> RAM · <?php echo gbDevVps((int)($s['storage_total'] ?? 0)); ?> Disco)
          </option>
        <?php endforeach; ?>
      </select>
      <small style="color:#64748b;font-size:11px;display:block;margin-top:4px;">Servidor físico onde a VPS será provisionada.</small>
    </div>

    <!-- Recursos -->
    <div style="display:grid;grid-template-columns:repeat(3, 1fr);gap:16px;margin-bottom:20px;max-width:600px;">
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
      <div class="form-group">
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

    <button type="submit" class="botao">
      <svg width="14" height="14" viewBox="0 0 20 20" fill="none" style="vertical-align:middle;"><path d="M10 4v12M4 10h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
      Criar VPS da Equipe
    </button>
  </form>
</div>

<?php if (empty($vpsList)): ?>
<div style="margin-top:24px;padding:32px;text-align:center;color:#94a3b8;">
  <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" style="margin:0 auto 10px;display:block;opacity:.5;"><rect x="2" y="5" width="20" height="5" rx="2"/><rect x="2" y="13" width="20" height="5" rx="2"/><circle cx="18" cy="7.5" r="1"/><circle cx="18" cy="15.5" r="1"/></svg>
  <p style="margin:0;font-size:13px;">Nenhuma VPS da equipe criada ainda. Crie a primeira usando o formulário acima.</p>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
