<?php

declare(strict_types=1);

use LRV\Core\View;
use LRV\Core\I18n;

function gb(int $mb): string
{
    if ($mb <= 0) return '0 GB';
    return round($mb / 1024) . ' GB';
}

function vpsStatusInfo(string $st): array
{
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

?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Minhas VPS</title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
  <style>
    .vps-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:14px; }
    .vps-card { background:#fff; border:1px solid #e5e7eb; border-radius:14px; padding:18px 16px; }
    .vps-card-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; }
    .vps-id { font-weight:700; font-size:16px; color:#0B1C3D; }
    .vps-specs { display:grid; grid-template-columns:1fr 1fr; gap:6px; margin-bottom:14px; }
    .vps-spec { font-size:13px; color:#64748b; }
    .vps-spec strong { color:#1e293b; display:block; font-size:14px; }
    .vps-status-row { display:flex; align-items:center; gap:6px; font-size:13px; margin-bottom:12px; }
    .vps-actions { display:flex; gap:8px; flex-wrap:wrap; }
  </style>
</head>
<body>
  <div class="topo">
    <div class="conteudo linha" style="justify-content:space-between;">
      <div>
        <div style="font-size:18px;font-weight:700;">Minhas VPS</div>
        <div style="opacity:.9; font-size:13px;">Status e informações básicas</div>
      </div>
      <div class="linha">
        <?php require __DIR__ . '/../_partials/idioma.php'; ?>
        <a href="/cliente/painel">Painel</a>
        <a href="/cliente/planos">Planos</a>
        <a href="/cliente/monitoramento">Monitoramento</a>
        <a href="/cliente/tickets">Tickets</a>
        <a href="/cliente/sair">Sair</a>
      </div>
    </div>
  </div>

  <div class="conteudo">
    <?php if (empty($vps)): ?>
      <div class="card">
        <p class="texto">Você ainda não tem VPS.</p>
        <a href="/cliente/planos" class="botao">Ver planos</a>
      </div>
    <?php else: ?>
      <div class="vps-grid">
        <?php foreach (($vps ?? []) as $v):
          $st = (string) ($v['status'] ?? '');
          $info = vpsStatusInfo($st);
        ?>
          <div class="vps-card">
            <div class="vps-card-header">
              <span class="vps-id">VPS #<?php echo (int) ($v['id'] ?? 0); ?></span>
              <div class="vps-status-row">
                <span class="<?php echo View::e($info['dot']); ?>"></span>
                <span><?php echo View::e($info['label']); ?></span>
              </div>
            </div>

            <div class="vps-specs">
              <div class="vps-spec">
                <strong><?php echo View::e((string) ($v['cpu'] ?? '—')); ?></strong>
                CPU
              </div>
              <div class="vps-spec">
                <strong><?php echo View::e(gb((int) ($v['ram'] ?? 0))); ?></strong>
                RAM
              </div>
              <div class="vps-spec">
                <strong><?php echo View::e(gb((int) ($v['storage'] ?? 0))); ?></strong>
                Disco
              </div>
              <div class="vps-spec">
                <strong><?php echo View::e((string) ($v['server_id'] ?? '—')); ?></strong>
                Node
              </div>
            </div>

            <?php if (!empty($v['container_ip'])): ?>
              <div style="font-size:12px; color:#64748b; margin-bottom:10px;">
                IP: <?php echo View::e((string) $v['container_ip']); ?>
              </div>
            <?php endif; ?>

            <div class="vps-actions">
              <?php if ($st === 'running'): ?>
                <a href="/cliente/vps/terminal?id=<?php echo (int) ($v['id'] ?? 0); ?>" class="botao sm">Terminal</a>
                <a href="/cliente/monitoramento/ver?vps_id=<?php echo (int) ($v['id'] ?? 0); ?>" class="botao sm ghost">Monitor</a>
              <?php elseif (in_array($st, ['pending_payment', 'suspended_payment'], true)): ?>
                <a href="/cliente/planos" class="botao sm">Ver planos</a>
              <?php elseif (in_array($st, ['provisioning', 'pending_provisioning', 'pending_node'], true)): ?>
                <span style="font-size:12px;color:#64748b;display:flex;align-items:center;gap:6px;">
                  <span class="dot-pending"></span> Aguardando...
                </span>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
  <?php require __DIR__ . '/../_partials/footer.php'; ?>
</body>
</html>
<script>
// Proteção double-submit em qualquer form da página
document.querySelectorAll('form').forEach(function(form) {
  form.addEventListener('submit', function() {
    var btn = form.querySelector('button[type="submit"]');
    if (btn && !btn.disabled) {
      btn.disabled = true;
      btn.dataset.original = btn.textContent;
      btn.innerHTML = '<span class="loading"></span> Processando...';
    }
  });
});
</script>
