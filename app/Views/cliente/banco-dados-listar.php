<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\Csrf;

$pageTitle = 'Bancos de Dados';
$clienteNome = (string)($cliente['name'] ?? '');
$clienteEmail = (string)($cliente['email'] ?? '');
require __DIR__ . '/../_partials/layout-cliente-inicio.php';
?>

<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:24px;">
  <div>
    <div class="page-title">Bancos de Dados</div>
    <div class="page-subtitle" style="margin-bottom:0;">Crie e gerencie bancos MySQL nas suas VPS</div>
  </div>
  <a href="/cliente/banco-dados/criar" class="botao">+ Novo banco</a>
</div>

<?php if (empty($bancos)): ?>
<div class="card-new" style="text-align:center;padding:48px 24px;">
  <div style="font-size:40px;margin-bottom:12px;">🗄️</div>
  <div style="font-size:16px;font-weight:600;margin-bottom:8px;">Nenhum banco de dados</div>
  <div style="font-size:13px;color:#64748b;margin-bottom:20px;">Crie um banco MySQL para usar nas suas aplicações.</div>
  <a href="/cliente/banco-dados/criar" class="botao">Criar banco de dados</a>
</div>
<?php else: ?>
<div style="display:flex;flex-direction:column;gap:12px;">
  <?php foreach ($bancos as $b):
    $bid = (int)($b['id'] ?? 0);
    $status = (string)($b['status'] ?? 'active');
    $statusColor = $status === 'active' ? '#10b981' : ($status === 'error' ? '#ef4444' : '#f59e0b');
    $statusLabel = $status === 'active' ? 'Ativo' : ($status === 'error' ? 'Erro' : 'Criando...');
    $dbName = (string)($b['db_name'] ?? '');
    $dbUser = (string)($b['db_user'] ?? '');
    $dbPassEnc = (string)($b['db_password_enc_raw'] ?? '');
    $dbHost = (string)($b['db_host'] ?? '127.0.0.1');
    $dbPort = (string)($b['db_port'] ?? '3306');
  ?>
  <div class="card-new">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:12px;">
      <div>
        <div style="font-size:15px;font-weight:700;color:#1e293b;"><?php echo View::e((string)($b['name'] ?? '')); ?></div>
        <div style="font-size:12px;color:#64748b;font-family:monospace;margin-top:2px;"><?php echo View::e($dbName); ?></div>
      </div>
      <span style="font-size:11px;padding:3px 10px;border-radius:99px;background:<?php echo $statusColor; ?>20;color:<?php echo $statusColor; ?>;font-weight:600;"><?php echo $statusLabel; ?></span>
    </div>

    <!-- Credenciais inline -->
    <div style="display:flex;flex-direction:column;gap:6px;margin-bottom:12px;">
      <div style="display:flex;align-items:center;gap:8px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:6px 10px;">
        <span style="font-size:11px;color:#64748b;width:55px;flex-shrink:0;">Banco</span>
        <code id="db-name-<?php echo $bid; ?>" style="flex:1;font-size:12px;color:#1e293b;"><?php echo View::e($dbName); ?></code>
        <button type="button" onclick="copiarDb('db-name-<?php echo $bid; ?>')" style="background:none;border:1px solid #e2e8f0;border-radius:4px;padding:1px 6px;font-size:10px;cursor:pointer;">Copiar</button>
      </div>
      <div style="display:flex;align-items:center;gap:8px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:6px 10px;">
        <span style="font-size:11px;color:#64748b;width:55px;flex-shrink:0;">Usuário</span>
        <code id="db-user-<?php echo $bid; ?>" style="flex:1;font-size:12px;color:#1e293b;"><?php echo View::e($dbUser); ?></code>
        <button type="button" onclick="copiarDb('db-user-<?php echo $bid; ?>')" style="background:none;border:1px solid #e2e8f0;border-radius:4px;padding:1px 6px;font-size:10px;cursor:pointer;">Copiar</button>
      </div>
      <div style="display:flex;align-items:center;gap:8px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:6px 10px;">
        <span style="font-size:11px;color:#64748b;width:55px;flex-shrink:0;">Senha</span>
        <code id="db-pass-<?php echo $bid; ?>" style="flex:1;font-size:12px;color:#1e293b;" data-enc="<?php echo View::e($dbPassEnc); ?>" data-revealed="0">••••••••••••</code>
        <button type="button" onclick="toggleSenha(<?php echo $bid; ?>)" id="btn-eye-<?php echo $bid; ?>" style="background:none;border:1px solid #e2e8f0;border-radius:4px;padding:1px 6px;font-size:10px;cursor:pointer;" title="Mostrar/esconder">👁</button>
        <button type="button" onclick="copiarDb('db-pass-<?php echo $bid; ?>')" style="background:none;border:1px solid #e2e8f0;border-radius:4px;padding:1px 6px;font-size:10px;cursor:pointer;">Copiar</button>
      </div>
    </div>

    <div style="display:flex;gap:8px;flex-wrap:wrap;">
      <a href="/cliente/banco-dados/ver?id=<?php echo $bid; ?>" class="botao sm">Ver detalhes / SQL</a>
      <?php
        // Verificar se o servidor desta VPS tem phpMyAdmin
        $pmaCheck = \LRV\Core\BancoDeDados::pdo()->prepare('SELECT s.phpmyadmin_url FROM vps v JOIN servers s ON s.id = v.server_id WHERE v.id = :v LIMIT 1');
        $pmaCheck->execute([':v' => (int)($b['vps_id'] ?? 0)]);
        $pmaRow = $pmaCheck->fetch();
        $hasPma = !empty($pmaRow['phpmyadmin_url']) || trim((string)\LRV\Core\Settings::obter('infra.phpmyadmin_url', '')) !== '';
      ?>
      <?php if ($hasPma): ?>
        <a href="/cliente/banco-dados/phpmyadmin?id=<?php echo $bid; ?>" target="_blank" class="botao ghost sm" title="Abrir phpMyAdmin">🐬 phpMyAdmin</a>
      <?php endif; ?>
      <form method="post" action="/cliente/banco-dados/excluir" style="display:inline;" onsubmit="return confirm('Remover banco <?php echo View::e((string)($b['name'] ?? '')); ?>? Os dados serão perdidos.')">
        <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>" />
        <input type="hidden" name="id" value="<?php echo $bid; ?>" />
        <button class="botao danger sm" type="submit">Remover</button>
      </form>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<script>
function copiarDb(id) {
  var el = document.getElementById(id);
  if (!el) return;
  var text = el.getAttribute('data-plain') || el.textContent.trim();
  if (text === '••••••••••••') return;
  navigator.clipboard.writeText(text).then(function() {
    var btn = el.parentElement.querySelector('button:last-child');
    if (btn) { var t = btn.textContent; btn.textContent = '✓'; setTimeout(function(){ btn.textContent = t; }, 1500); }
  });
}

function toggleSenha(bid) {
  var el = document.getElementById('db-pass-' + bid);
  if (!el) return;
  var revealed = el.getAttribute('data-revealed') === '1';
  if (revealed) {
    el.textContent = '••••••••••••';
    el.setAttribute('data-revealed', '0');
    return;
  }
  // Fetch decrypted password via AJAX
  var plain = el.getAttribute('data-plain');
  if (plain) {
    el.textContent = plain;
    el.setAttribute('data-revealed', '1');
    return;
  }
  el.textContent = '...';
  fetch('/cliente/banco-dados/senha?id=' + bid)
    .then(function(r) { return r.json(); })
    .then(function(d) {
      if (d.ok && d.senha) {
        el.setAttribute('data-plain', d.senha);
        el.textContent = d.senha;
        el.setAttribute('data-revealed', '1');
      } else {
        el.textContent = '(erro)';
      }
    })
    .catch(function() { el.textContent = '(erro)'; });
}
</script>
<?php endif; ?>

<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
