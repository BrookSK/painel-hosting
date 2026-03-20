<?php

declare(strict_types=1);

use LRV\Core\View;
use LRV\Core\I18n;

function gb(int $mb): string
{
    if ($mb <= 0) { return '0 GB'; }
    return (string) ((int) round($mb / 1024)) . ' GB';
}

function badgeStatusVpsEquipe(string $st): string
{
    $map = [
        'running'              => ['Em execução', '#dcfce7', '#166534'],
        'suspended_payment'    => ['Suspensa', '#fee2e2', '#991b1b'],
        'pending_payment'      => ['Aguardando pagamento', '#fef3c7', '#92400e'],
        'pending_node'         => ['Aguardando node', '#fef3c7', '#92400e'],
        'pending_provisioning' => ['Provisionamento pendente', '#fef3c7', '#92400e'],
        'provisioning'         => ['Provisionando', '#e0e7ff', '#1e3a8a'],
        'error'                => ['Erro', '#fee2e2', '#991b1b'],
        'removed'              => ['Removida', '#f1f5f9', '#334155'],
    ];
    $d = $map[$st] ?? [View::e($st), '#f1f5f9', '#334155'];
    return '<span class="badge" style="background:' . $d[1] . ';color:' . $d[2] . ';">' . $d[0] . '</span>';
}

?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>VPS</title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
</head>
<body>
  <div class="topo">
    <div class="conteudo linha" style="justify-content:space-between;">
      <div>
        <div style="font-size:18px;font-weight:700;">VPS</div>
        <div style="opacity:.9; font-size:13px;">Provisionamento, status e ações</div>
      </div>
      <div class="linha">
        <?php require __DIR__ . '/../_partials/idioma.php'; ?>
        <a href="/equipe/painel">Painel</a>
        <a href="/equipe/planos">Planos</a>
        <a href="/equipe/servidores">Servidores</a>
        <a href="/equipe/aplicacoes">Aplicações</a>
        <a href="/equipe/backups">Backups</a>
        <a href="/equipe/tickets">Tickets</a>
        <a href="/equipe/jobs">Jobs</a>
        <a href="/equipe/configuracoes">Configurações</a>
        <a href="/equipe/sair">Sair</a>
      </div>
    </div>
  </div>

  <div class="conteudo">
    <div class="card">
      <div style="overflow:auto;">
        <table style="width:100%; border-collapse:collapse;">
          <thead>
            <tr>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">VPS</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Cliente</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">CPU / RAM / Disco</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Status</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Node</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach (($vps ?? []) as $v): ?>
              <?php $vid = (int) ($v['id'] ?? 0); ?>
              <tr>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;">
                  <strong>#<?php echo $vid; ?></strong>
                  <?php if (trim((string) ($v['name'] ?? '')) !== ''): ?>
                    <div style="font-size:12px; opacity:.8;"><?php echo View::e((string) ($v['name'] ?? '')); ?></div>
                  <?php endif; ?>
                </td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;">
                  <div><strong><?php echo View::e((string) ($v['client_name'] ?? '')); ?></strong></div>
                  <div style="font-size:12px; opacity:.8;"><?php echo View::e((string) ($v['client_email'] ?? '')); ?></div>
                </td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;">
                  <?php echo View::e((string) ($v['cpu'] ?? '')); ?> vCPU /
                  <?php echo View::e(gb((int) ($v['ram'] ?? 0))); ?> /
                  <?php echo View::e(gb((int) ($v['storage'] ?? 0))); ?>
                </td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo badgeStatusVpsEquipe((string) ($v['status'] ?? '')); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo View::e((string) ($v['server_id'] ?? '')); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;">
                  <div class="linha" style="gap:6px; flex-wrap:wrap;">
                    <form method="post" action="/equipe/vps/provisionar">
                      <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
                      <input type="hidden" name="vps_id" value="<?php echo $vid; ?>" />
                      <button class="botao" type="submit" style="padding:6px 10px; font-size:12px;">Provisionar</button>
                    </form>
                    <form method="post" action="/equipe/vps/reativar">
                      <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
                      <input type="hidden" name="vps_id" value="<?php echo $vid; ?>" />
                      <button class="botao" type="submit" style="padding:6px 10px; font-size:12px; background:#0B1C3D;">Reativar</button>
                    </form>
                    <form method="post" action="/equipe/vps/reiniciar">
                      <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
                      <input type="hidden" name="vps_id" value="<?php echo $vid; ?>" />
                      <button class="botao" type="submit" style="padding:6px 10px; font-size:12px; background:#7C3AED;">Reiniciar</button>
                    </form>
                    <form method="post" action="/equipe/vps/suspender">
                      <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
                      <input type="hidden" name="vps_id" value="<?php echo $vid; ?>" />
                      <button class="botao" type="submit" style="padding:6px 10px; font-size:12px; background:#b45309;">Suspender</button>
                    </form>
                    <form method="post" action="/equipe/vps/remover" onsubmit="return confirm('Remover definitivamente a VPS #<?php echo $vid; ?>? Esta ação não pode ser desfeita.');">
                      <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
                      <input type="hidden" name="vps_id" value="<?php echo $vid; ?>" />
                      <button class="botao" type="submit" style="padding:6px 10px; font-size:12px; background:#991b1b;">Remover</button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($vps)): ?>
              <tr>
                <td colspan="6" style="padding:12px;">Nenhuma VPS encontrada.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <?php require __DIR__ . '/../_partials/footer.php'; ?>
</body>
</html>
<script>
// Proteção double-submit: desabilita botão e mostra "Processando..." após clique
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
