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
        default     => '<span class="badge-new badge-gray">' . htmlspecialchars($s) . '</span>',
    };
};

$vpsBadge = function(string $s): string {
    return match($s) {
        'running'    => '<span class="badge-new badge-green">Ativo</span>',
        'stopped'    => '<span class="badge-new badge-yellow">Parado</span>',
        'suspended'  => '<span class="badge-new badge-red">Suspenso</span>',
        default      => '<span class="badge-new badge-gray">' . htmlspecialchars($s) . '</span>',
    };
};
?>
<div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
  <a href="/equipe/clientes" style="color:#94a3b8;font-size:13px;">← Clientes</a>
  <span style="color:#e2e8f0;">/</span>
  <span style="font-size:13px;color:#475569;"><?php echo View::e((string)($cliente['name'] ?? '')); ?></span>
</div>

<?php if ($ok === 'assinatura_criada'): ?>
  <div class="sucesso" style="margin-bottom:16px;">Assinatura criada com sucesso.</div>
<?php endif; ?>
<?php if ($erro !== ''): ?><div class="erro" style="margin-bottom:16px;"><?php echo View::e($erro); ?></div><?php endif; ?>

<!-- Cabeçalho do cliente -->
<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
  <div>
    <div class="page-title"><?php echo View::e((string)($cliente['name'] ?? '')); ?></div>
    <div class="page-subtitle"><?php echo View::e((string)($cliente['email'] ?? '')); ?></div>
  </div>
  <a href="/equipe/clientes/editar?id=<?php echo (int)$cliente['id']; ?>" class="botao sm sec">Editar dados</a>
</div>

<!-- Dados do cliente -->
<div class="card-new" style="margin-bottom:16px;">
  <div class="card-new-title">Dados cadastrais</div>
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px;margin-top:12px;">
    <div>
      <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;">Telefone</div>
      <div style="font-size:14px;color:#0f172a;"><?php echo View::e((string)($cliente['phone'] ?? '—')); ?></div>
    </div>
    <div>
      <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;">CPF / CNPJ</div>
      <div style="font-size:14px;color:#0f172a;"><?php echo View::e((string)($cliente['cpf_cnpj'] ?? '—')); ?></div>
    </div>
    <div>
      <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;">Cadastro</div>
      <div style="font-size:14px;color:#0f172a;"><?php echo View::e(date('d/m/Y H:i', strtotime((string)($cliente['created_at'] ?? 'now')))); ?></div>
    </div>
    <div>
      <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;">ID</div>
      <div style="font-size:14px;color:#94a3b8;">#<?php echo (int)$cliente['id']; ?></div>
    </div>
  </div>
</div>

<!-- VPS -->
<div class="card-new" style="margin-bottom:16px;padding:0;overflow:auto;">
  <div style="padding:16px 16px 0;display:flex;justify-content:space-between;align-items:center;">
    <div class="card-new-title" style="margin:0;">VPS (<?php echo count($vps); ?>)</div>
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
        <tr><td colspan="5" style="padding:30px;text-align:center;color:#94a3b8;">Nenhum VPS.</td></tr>
      <?php else: ?>
        <?php foreach ($vps as $v): ?>
          <tr style="border-bottom:1px solid #f1f5f9;">
            <td style="padding:10px 16px;color:#94a3b8;font-size:12px;">#<?php echo (int)$v['id']; ?></td>
            <td style="padding:10px 16px;font-weight:500;color:#0f172a;font-family:monospace;"><?php echo View::e((string)($v['container_id'] ?? '—')); ?></td>
            <td style="padding:10px 16px;color:#475569;font-size:13px;"><?php echo (int)($v['cpu']??0); ?> vCPU / <?php echo round((int)($v['ram']??0)/1024,1); ?> GB / <?php echo round((int)($v['storage']??0)/1024,1); ?> GB</td>
            <td style="padding:10px 16px;color:#475569;"><?php echo View::e((string)($v['plan_name'] ?? '—')); ?></td>
            <td style="padding:10px 16px;"><?php echo $vpsBadge((string)($v['status'] ?? '')); ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Assinaturas -->
<div class="card-new" style="margin-bottom:16px;padding:0;overflow:auto;">
  <div style="padding:16px 16px 0;display:flex;justify-content:space-between;align-items:center;">
    <div class="card-new-title" style="margin:0;">Assinaturas (<?php echo count($assinaturas); ?>)</div>
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

<!-- Assinar plano manualmente -->
<?php if (!empty($planos)): ?>
<div class="card-new" style="max-width:480px;">
  <div class="card-new-title">Assinar plano manualmente</div>
  <form method="POST" action="/equipe/clientes/assinar-plano" style="margin-top:12px;display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;">
    <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
    <input type="hidden" name="client_id" value="<?php echo (int)$cliente['id']; ?>" />
    <div style="flex:1;min-width:200px;">
      <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px;">Plano</label>
      <select name="plan_id" class="input" required>
        <option value="">Selecione...</option>
        <?php foreach ($planos as $pl): ?>
          <option value="<?php echo (int)$pl['id']; ?>">
            <?php echo View::e((string)$pl['name']); ?> — R$ <?php echo number_format((float)$pl['price_monthly'], 2, ',', '.'); ?>/mês
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <button type="submit" class="botao">Assinar</button>
  </form>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
