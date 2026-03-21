<?php
declare(strict_types=1);
use LRV\Core\View;

$pageTitle = 'Detalhe do Erro';
require __DIR__ . '/../_partials/layout-equipe-inicio.php';

$e = $erro ?? [];
$id = (int)($e['id'] ?? 0);
$httpCode = (int)($e['http_code'] ?? 0);

$corBadge = $httpCode >= 500
    ? 'background:#fee2e2;color:#b91c1c;'
    : ($httpCode >= 400 ? 'background:#fef3c7;color:#92400e;' : 'background:#e0f2fe;color:#0369a1;');

$contextData = null;
if (!empty($e['context_json'])) {
    $contextData = json_decode((string)$e['context_json'], true);
}
?>
<div style="display:flex;align-items:center;gap:12px;margin-bottom:4px;">
  <a href="/equipe/erros" style="color:#64748b;font-size:13px;">← Erros</a>
  <span style="color:#cbd5e1;">/</span>
  <span style="font-size:13px;color:#475569;">Erro #<?php echo $id; ?></span>
</div>

<div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;flex-wrap:wrap;">
  <div class="page-title" style="margin:0;">Erro #<?php echo $id; ?></div>
  <span style="font-size:13px;font-weight:700;padding:4px 12px;border-radius:8px;<?php echo $corBadge; ?>">
    <?php echo $httpCode; ?>
  </span>
  <?php if ((int)($e['resolved']??0) === 1): ?>
    <span style="font-size:12px;background:#dcfce7;color:#15803d;padding:3px 10px;border-radius:999px;">✓ Resolvido</span>
  <?php else: ?>
    <span style="font-size:12px;background:#fee2e2;color:#b91c1c;padding:3px 10px;border-radius:999px;">Pendente</span>
    <button class="botao sm" onclick="resolverErro(<?php echo $id; ?>)" id="btnResolver">Marcar como resolvido</button>
  <?php endif; ?>
  <button class="botao ghost sm" style="color:#b91c1c;" onclick="excluirErro(<?php echo $id; ?>)">Excluir</button>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">

  <!-- Informações gerais -->
  <div class="card-new">
    <h2 class="titulo" style="font-size:14px;margin-bottom:14px;">Informações gerais</h2>
    <table style="width:100%;font-size:13px;border-collapse:collapse;">
      <?php
      $rows = [
        'Tipo'       => (string)($e['error_type']??''),
        'Método'     => (string)($e['method']??''),
        'URL'        => (string)($e['url']??''),
        'IP'         => (string)($e['ip_address']??'—'),
        'Usuário'    => ($e['user_type']??'') !== '' ? ucfirst((string)$e['user_type']) . ' #' . (int)($e['user_id']??0) : 'Guest',
        'Notificado' => (int)($e['notified']??0) ? 'Sim' : 'Não',
        'Data'       => date('d/m/Y H:i:s', strtotime((string)($e['created_at']??'now'))),
      ];
      if ((int)($e['resolved']??0) === 1 && !empty($e['resolved_at'])) {
        $rows['Resolvido em'] = date('d/m/Y H:i:s', strtotime((string)$e['resolved_at']));
      }
      foreach ($rows as $label => $val): ?>
      <tr style="border-bottom:1px solid #f1f5f9;">
        <td style="padding:7px 0;color:#64748b;width:110px;vertical-align:top;"><?php echo View::e($label); ?></td>
        <td style="padding:7px 0;word-break:break-all;"><?php echo View::e($val); ?></td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>

  <!-- User Agent -->
  <div class="card-new">
    <h2 class="titulo" style="font-size:14px;margin-bottom:14px;">Mensagem do erro</h2>
    <pre style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px;font-size:13px;white-space:pre-wrap;word-break:break-all;margin:0;max-height:160px;overflow:auto;"><?php echo View::e((string)($e['message']??'')); ?></pre>

    <?php if (!empty($e['user_agent'])): ?>
    <h2 class="titulo" style="font-size:14px;margin:16px 0 8px;">User Agent</h2>
    <div style="font-size:12px;color:#64748b;word-break:break-all;"><?php echo View::e((string)$e['user_agent']); ?></div>
    <?php endif; ?>
  </div>

</div>

<?php if (!empty($e['file']) || !empty($e['line'])): ?>
<div class="card-new" style="margin-bottom:16px;">
  <h2 class="titulo" style="font-size:14px;margin-bottom:10px;">Localização</h2>
  <div style="font-size:13px;font-family:monospace;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:10px 14px;">
    <?php echo View::e((string)($e['file']??'')); ?>
    <?php if (!empty($e['line'])): ?>
      <span style="color:#7C3AED;font-weight:700;"> : linha <?php echo (int)$e['line']; ?></span>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>

<?php if (!empty($e['trace'])): ?>
<div class="card-new" style="margin-bottom:16px;">
  <h2 class="titulo" style="font-size:14px;margin-bottom:10px;">Stack Trace</h2>
  <pre style="background:#0f172a;color:#e2e8f0;border-radius:12px;padding:16px;font-size:12px;line-height:1.6;white-space:pre-wrap;word-break:break-all;margin:0;max-height:400px;overflow:auto;"><?php echo View::e((string)$e['trace']); ?></pre>
</div>
<?php endif; ?>

<?php if ($contextData !== null): ?>
<div class="card-new" style="margin-bottom:16px;">
  <h2 class="titulo" style="font-size:14px;margin-bottom:10px;">Contexto adicional</h2>
  <pre style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px;font-size:12px;white-space:pre-wrap;word-break:break-all;margin:0;max-height:300px;overflow:auto;"><?php echo View::e(json_encode($contextData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
</div>
<?php endif; ?>

<!-- Ações de reteste -->
<div class="card-new" style="margin-bottom:16px;">
  <h2 class="titulo" style="font-size:14px;margin-bottom:12px;">Reteste manual</h2>
  <p class="texto" style="font-size:13px;margin-bottom:12px;">
    Reproduza o erro acessando a URL abaixo com o mesmo método e contexto.
  </p>
  <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
    <code style="background:#f1f5f9;border:1px solid #e2e8f0;border-radius:8px;padding:8px 14px;font-size:13px;flex:1;word-break:break-all;">
      <?php echo View::e((string)($e['method']??'GET')); ?> <?php echo View::e((string)($e['url']??'')); ?>
    </code>
    <?php if (strtoupper((string)($e['method']??'GET')) === 'GET'): ?>
    <a href="<?php echo View::e((string)($e['url']??'')); ?>" target="_blank" class="botao sec sm">Abrir URL</a>
    <?php endif; ?>
    <button class="botao ghost sm" onclick="copiarUrl()">Copiar URL</button>
  </div>
</div>

<script>
const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

async function resolverErro(id) {
  const r = await fetch('/equipe/erros/resolver', {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded','x-csrf-token':csrf},
    body: 'id=' + id
  });
  const j = await r.json();
  if (j.ok) {
    document.getElementById('btnResolver')?.remove();
    alert('Marcado como resolvido.');
  }
}

async function excluirErro(id) {
  if (!confirm('Excluir este erro permanentemente?')) return;
  const r = await fetch('/equipe/erros/excluir', {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded','x-csrf-token':csrf},
    body: 'id=' + id
  });
  const j = await r.json();
  if (j.ok) window.location.href = '/equipe/erros';
}

function copiarUrl() {
  const url = <?php echo json_encode((string)($e['url']??'')); ?>;
  navigator.clipboard?.writeText(url).then(() => alert('URL copiada!'));
}
</script>

<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
