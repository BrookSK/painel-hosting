<?php
declare(strict_types=1);
use LRV\Core\View;

function gb(int $mb): string {
    if ($mb <= 0) return '0 GB';
    return round($mb / 1024) . ' GB';
}

function vpsStatusInfo(string $st): array {
    return match($st) {
        'running'              => ['dot' => 'dot-online',  'label' => 'Em execução'],
        'suspended_payment'    => ['dot' => 'dot-offline', 'label' => 'Suspensa'],
        'pending_payment'      => ['dot' => 'dot-pending', 'label' => 'Aguardando pagamento'],
        'pending_node'         => ['dot' => 'dot-pending', 'label' => 'Aguardando node'],
        'pending_provisioning' => ['dot' => 'dot-pending', 'label' => 'Provisionamento pendente'],
        'provisioning'         => ['dot' => 'dot-pending', 'label' => 'Provisionando'],
        'error'                => ['dot' => 'dot-offline', 'label' => 'Erro'],
        default                => ['dot' => 'dot-pending', 'label' => $st],
    };
}

$pageTitle    = 'Minhas VPS';
$clienteNome  = (string)($cliente['name'] ?? '');
$clienteEmail = (string)($cliente['email'] ?? '');
require __DIR__ . '/../_partials/layout-cliente-inicio.php';
?>

<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:24px;">
  <div>
    <div class="page-title">Minhas VPS</div>
    <div class="page-subtitle" style="margin-bottom:0;">Status e informações básicas</div>
  </div>
</div>

<?php if (empty($vps)): ?>
  <div class="card-new" style="text-align:center;padding:40px 24px;">
    <div style="font-size:36px;margin-bottom:12px;">🖥️</div>
    <div style="font-size:15px;font-weight:600;margin-bottom:8px;">Nenhuma VPS ainda</div>
    <div style="font-size:13px;color:#64748b;margin-bottom:16px;">Assine um plano para provisionar sua primeira VPS.</div>
    <a href="/cliente/planos" class="botao">Ver planos</a>
  </div>
<?php else: ?>
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px;">
    <?php foreach ($vps as $v):
      $st   = (string)($v['status'] ?? '');
      $info = vpsStatusInfo($st);
    ?>
      <div class="card-new">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
          <span style="font-weight:700;font-size:16px;color:#0B1C3D;">VPS #<?php echo (int)($v['id'] ?? 0); ?></span>
          <div style="display:flex;align-items:center;gap:6px;font-size:13px;">
            <span class="<?php echo View::e($info['dot']); ?>"></span>
            <span><?php echo View::e($info['label']); ?></span>
          </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;margin-bottom:14px;">
          <div style="font-size:13px;color:#64748b;"><strong style="color:#1e293b;display:block;font-size:14px;"><?php echo View::e((string)($v['cpu'] ?? '—')); ?></strong>CPU</div>
          <div style="font-size:13px;color:#64748b;"><strong style="color:#1e293b;display:block;font-size:14px;"><?php echo View::e(gb((int)($v['ram'] ?? 0))); ?></strong>RAM</div>
          <div style="font-size:13px;color:#64748b;"><strong style="color:#1e293b;display:block;font-size:14px;"><?php echo View::e(gb((int)($v['storage'] ?? 0))); ?></strong>Disco</div>
          <div style="font-size:13px;color:#64748b;"><strong style="color:#1e293b;display:block;font-size:14px;"><?php echo View::e((string)($v['server_id'] ?? '—')); ?></strong>Node</div>
        </div>

        <?php if (!empty($v['container_ip'])): ?>
          <div style="font-size:12px;color:#64748b;margin-bottom:10px;">IP: <?php echo View::e((string)$v['container_ip']); ?></div>
        <?php endif; ?>

        <div style="display:flex;gap:8px;flex-wrap:wrap;">
          <?php if ($st === 'running'): ?>
            <a href="/cliente/vps/terminal?id=<?php echo (int)($v['id'] ?? 0); ?>" class="botao sm">Terminal</a>
            <a href="/cliente/monitoramento/ver?vps_id=<?php echo (int)($v['id'] ?? 0); ?>" class="botao sm ghost">Monitor</a>
          <?php elseif (in_array($st, ['pending_payment', 'suspended_payment'], true)): ?>
            <a href="/cliente/planos" class="botao sm">Ver planos</a>
          <?php elseif (in_array($st, ['provisioning', 'pending_provisioning', 'pending_node'], true)): ?>
            <span style="font-size:12px;color:#64748b;display:flex;align-items:center;gap:6px;"><span class="dot-pending"></span> Aguardando...</span>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
