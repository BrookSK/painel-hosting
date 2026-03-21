<?php
declare(strict_types=1);
use LRV\Core\View;

$cliente     = $cliente ?? [];
$vps         = $vps ?? [];
$assinaturas = $assinaturas ?? [];
$planos      = $planos ?? [];
$ok          = (string)($ok ?? ($_GET['ok'] ?? ''));
$erro        = (string)($erro ?? '');
$pageTitle   = 'Cliente: ' . ($cliente['name'] ?? '');
require __DIR__ . '/../_partials/layout-equipe-inicio.php';

$statusBadge = function(string $s): string {
    return match($s) {
        'active'    => '<span class="badge-new badge-green">Ativa</span>',
        'suspended' => '<span class="badge-new badge-yellow">Suspensa</span>',
        'cancelled' => '<span class="badge-new badge-red">Cancelada</span>',
        default     => '<span class="badge-new badge-gray">' . View::e($s) . '</span>',
    };
};

$vpsBadge = function(string $s): string {
    return match($s) {
        'running'   => '<span class="badge-new badge-green">Ativo</span>',
        'stopped'   => '<span class="badge-new badge-yellow">Parado</span>',
        'suspended' => '<span class="badge-new badge-red">Suspenso</span>',
        default     => '<span class="badge-new badge-gray">' . View::e($s) . '</span>',
    };
};

$iniciais = strtoupper(substr((string)($cliente['name'] ?? 'C'), 0, 1));
$nomePartes = explode(' ', trim((string)($cliente['name'] ?? '')));
if (count($nomePartes) >= 2) {
    $iniciais = strtoupper(substr($nomePartes[0], 0, 1) . substr(end($nomePartes), 0, 1));
}
?>
<!-- Breadcrumb -->
<div style="display:flex;align-items:center;gap:8px;margin-bottom:20px;font-size:13px;">
  <a href="/equipe/clientes" style="color:#94a3b8;">← Clientes</a>
  <span style="color:#e2e8f0;">/</span>
  <span style="color:#475569;"><?php echo View::e((string)($cliente['name'] ?? '')); ?></span>
</div>

<?php if ($ok === 'assinatura_criada'): ?>
  <div class="sucesso" style="margin-bottom:16px;">Assinatura criada com sucesso.</div>
<?php endif; ?>
<?php if ($erro !== ''): ?>
  <div class="erro" style="margin-bottom:16px;"><?php echo View::e($erro); ?></div>
<?php endif; ?>

<!-- Header do cliente -->
<div class="card-new" style="margin-bottom:16px;">
  <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
    <div style="display:flex;align-items:center;gap:14px;">
      <div style="width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#8b5cf6);display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:700;color:#fff;flex-shrink:0;">
        <?php echo View::e($iniciais); ?>
      </div>
      <div>
        <div style="font-size:18px;font-weight:700;color:#0f172a;line-height:1.2;"><?php echo View::e((string)($cliente['name'] ?? '')); ?></div>
        <div style="font-size:13px;color:#64748b;margin-top:2px;"><?php echo View::e((string)($cliente['email'] ?? '')); ?></div>
        <div style="display:flex;gap:6px;margin-top:6px;flex-wrap:wrap;">
          <span class="badge-new badge-blue"><?php echo count($vps); ?> VPS</span>
          <span class="badge-new badge-<?php echo count(array_filter($assinaturas, fn($s) => ($s['status']??'') === 'active')) > 0 ? 'green' : 'gray'; ?>">
            <?php echo count(array_filter($assinaturas, fn($s) => ($s['status']??'') === 'active')); ?> assinatura(s) ativa(s)
          </span>
        </div>
      </div>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
      <a href="/equipe/clientes/editar?id=<?php echo (int)$cliente['id']; ?>" class="botao sec sm">Editar dados</a>
    </div>
  </div>
</div>

<!-- Grid: dados cadastrais + ações -->
<div class="grid" style="margin-bottom:16px;">
  <div class="card-new">
    <div class="card-new-title">Dados cadastrais</div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:12px;">
      <div>
        <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin-bottom:3px;">Telefone</div>
        <div style="font-size:14px;color:#0f172a;"><?php echo View::e((string)($cliente['phone'] ?? '—')); ?></div>
      </div>
      <div>
        <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin-bottom:3px;">Celular</div>
        <div style="font-size:14px;color:#0f172a;"><?php echo View::e((string)($cliente['mobile_phone'] ?? '—')); ?></div>
      </div>
      <div>
        <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin-bottom:3px;">CPF / CNPJ</div>
        <div style="font-size:14px;color:#0f172a;"><?php echo View::e((string)($cliente['cpf_cnpj'] ?? '—')); ?></div>
      </div>
      <div>
        <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin-bottom:3px;">Cadastro</div>
        <div style="font-size:14px;color:#0f172a;"><?php echo View::e(date('d/m/Y H:i', strtotime((string)($cliente['created_at'] ?? 'now')))); ?></div>
      </div>
      <div>
        <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin-bottom:3px;">ID</div>
        <div style="font-size:14px;color:#94a3b8;">#<?php echo (int)$cliente['id']; ?></div>
      </div>
    </div>
    <?php
    $temEndereco = !empty($cliente['address_street']) || !empty($cliente['address_city']);
    if ($temEndereco):
      $endLine1 = trim(($cliente['address_street'] ?? '') . ($cliente['address_number'] ? ', ' . $cliente['address_number'] : '') . ($cliente['address_complement'] ? ' ' . $cliente['address_complement'] : ''));
      $endLine2 = trim(($cliente['address_district'] ? $cliente['address_district'] . ' — ' : '') . ($cliente['address_city'] ?? '') . ($cliente['address_state'] ? '/' . $cliente['address_state'] : ''));
    ?>
    <div style="margin-top:14px;padding-top:14px;border-top:1px solid #f1f5f9;">
      <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px;">Endereço</div>
      <div style="font-size:14px;color:#0f172a;line-height:1.6;">
        <?php if ($endLine1): ?><?php echo View::e($endLine1); ?><br><?php endif; ?>
        <?php if ($endLine2): ?><?php echo View::e($endLine2); ?><br><?php endif; ?>
        <?php if (!empty($cliente['address_zip'])): ?>CEP <?php echo View::e((string)$cliente['address_zip']); ?><?php endif; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <!-- Assinar plano -->
  <?php if (!empty($planos)): ?>
  <div class="card-new">
    <div class="card-new-title">Assinar plano manualmente</div>
    <form method="POST" action="/equipe/clientes/assinar-plano" style="margin-top:12px;">
      <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
      <input type="hidden" name="client_id" value="<?php echo (int)$cliente['id']; ?>" />
      <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px;">Plano</label>
      <select name="plan_id" class="input" required style="margin-bottom:10px;">
        <option value="">Selecione...</option>
        <?php foreach ($planos as $pl): ?>
          <option value="<?php echo (int)$pl['id']; ?>">
            <?php echo View::e((string)$pl['name']); ?> — R$ <?php echo number_format((float)$pl['price_monthly'], 2, ',', '.'); ?>/mês
          </option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="botao" style="width:100%;">Assinar plano</button>
    </form>
  </div>
  <?php endif; ?>
</div>

<!-- VPS -->
<div class="card-new" style="margin-bottom:16px;padding:0;overflow:auto;">
  <div style="padding:16px 16px 0;display:flex;justify-content:space-between;align-items:center;">
    <div class="card-new-title" style="margin:0;">VPS <span style="font-weight:400;color:#94a3b8;font-size:13px;">(<?php echo count($vps); ?>)</span></div>
  </div>
  <table style="width:100%;border-collapse:collapse;font-size:13px;margin-top:12px;">
    <thead>
      <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
        <th style="padding:10px 16px;text-align:left;font-weight:600;color:#475569;">#</th>
        <th style="padding:10px 16px;text-align:left;font-weight:600;color:#475569;">Container</th>
        <th style="padding:10px 16px;text-align:left;font-weight:600;color:#475569;">CPU / RAM / Disco</th>
        <th style="padding:10px 16px;text-align:left;font-weight:600;color:#475569;">Plano</th>
        <th style="padding:10px 16px;text-align:left;font-weight:600;color:#475569;">Status</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($vps)): ?>
        <tr><td colspan="5" style="padding:30px;text-align:center;color:#94a3b8;">Nenhum VPS cadastrado.</td></tr>
      <?php else: ?>
        <?php foreach ($vps as $v): ?>
          <tr style="border-bottom:1px solid #f1f5f9;">
            <td style="padding:10px 16px;color:#94a3b8;font-size:12px;">#<?php echo (int)$v['id']; ?></td>
            <td style="padding:10px 16px;font-weight:500;color:#0f172a;font-family:monospace;font-size:12px;"><?php echo View::e((string)($v['container_id'] ?? '—')); ?></td>
            <td style="padding:10px 16px;color:#475569;"><?php echo (int)($v['cpu']??0); ?> vCPU / <?php echo round((int)($v['ram']??0)/1024,1); ?> GB / <?php echo round((int)($v['storage']??0)/1024,1); ?> GB</td>
            <td style="padding:10px 16px;color:#475569;"><?php echo View::e((string)($v['plan_name'] ?? '—')); ?></td>
            <td style="padding:10px 16px;"><?php echo $vpsBadge((string)($v['status'] ?? '')); ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Assinaturas -->
<div class="card-new" style="padding:0;overflow:auto;">
  <div style="padding:16px 16px 0;display:flex;justify-content:space-between;align-items:center;">
    <div class="card-new-title" style="margin:0;">Assinaturas <span style="font-weight:400;color:#94a3b8;font-size:13px;">(<?php echo count($assinaturas); ?>)</span></div>
  </div>
  <table style="width:100%;border-collapse:collapse;font-size:13px;margin-top:12px;">
    <thead>
      <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
        <th style="padding:10px 16px;text-align:left;font-weight:600;color:#475569;">#</th>
        <th style="padding:10px 16px;text-align:left;font-weight:600;color:#475569;">Plano</th>
        <th style="padding:10px 16px;text-align:left;font-weight:600;color:#475569;">Valor/mês</th>
        <th style="padding:10px 16px;text-align:left;font-weight:600;color:#475569;">Próx. vencimento</th>
        <th style="padding:10px 16px;text-align:left;font-weight:600;color:#475569;">Status</th>
        <th style="padding:10px 16px;text-align:left;font-weight:600;color:#475569;">Criada</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($assinaturas)): ?>
        <tr><td colspan="6" style="padding:30px;text-align:center;color:#94a3b8;">Nenhuma assinatura.</td></tr>
      <?php else: ?>
        <?php foreach ($assinaturas as $sub): ?>
          <tr style="border-bottom:1px solid #f1f5f9;">
            <td style="padding:10px 16px;color:#94a3b8;font-size:12px;">#<?php echo (int)$sub['id']; ?></td>
            <td style="padding:10px 16px;font-weight:500;color:#0f172a;"><?php echo View::e((string)($sub['plan_name'] ?? '—')); ?></td>
            <td style="padding:10px 16px;color:#475569;">R$ <?php echo number_format((float)($sub['price_monthly'] ?? 0), 2, ',', '.'); ?></td>
            <td style="padding:10px 16px;color:#475569;">
              <?php echo $sub['next_due_date'] ? View::e(date('d/m/Y', strtotime((string)$sub['next_due_date']))) : '—'; ?>
            </td>
            <td style="padding:10px 16px;"><?php echo $statusBadge((string)($sub['status'] ?? '')); ?></td>
            <td style="padding:10px 16px;color:#94a3b8;font-size:12px;">
              <?php echo View::e(date('d/m/Y', strtotime((string)($sub['created_at'] ?? 'now')))); ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
