<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\Csrf;

$b = is_array($banco ?? null) ? $banco : [];
$bid = (int)($b['id'] ?? 0);
$dbPass = (string)($b['db_password_plain'] ?? '');

$pageTitle = 'Banco — ' . (string)($b['name'] ?? '');
$clienteNome = (string)($cliente['name'] ?? '');
$clienteEmail = (string)($cliente['email'] ?? '');
require __DIR__ . '/../_partials/layout-cliente-inicio.php';
?>

<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:24px;">
  <div>
    <div class="page-title"><?php echo View::e((string)($b['name'] ?? '')); ?></div>
    <div class="page-subtitle" style="margin-bottom:0;">Banco de dados MySQL</div>
  </div>
  <a href="/cliente/banco-dados" class="botao ghost sm">← Voltar</a>
</div>

<div class="grid" style="grid-template-columns:1fr 1fr;gap:16px;align-items:start;">

  <!-- Dados de conexão -->
  <div class="card-new">
    <div class="card-new-title" style="margin-bottom:12px;">Dados de conexão</div>
    <p style="font-size:12px;color:#64748b;margin-bottom:14px;">Use esses dados para conectar sua aplicação ao banco de dados.</p>

    <?php
      $containerId = (string)($b['container_id'] ?? '');
      $dbEngine = (string)($b['engine'] ?? 'docker');
      // Bancos nativos: host é localhost
      // Docker: host é o nome do container (para phpMyAdmin) ou 127.0.0.1 (para app)
      $pmaHost = $dbEngine === 'native' ? 'localhost' : ($containerId !== '' ? $containerId : (string)($b['db_host'] ?? '127.0.0.1'));
      $campos = [
        'Host' => $pmaHost,
        'Banco' => (string)($b['db_name'] ?? ''),
        'Usuário' => (string)($b['db_user'] ?? ''),
        'Senha' => $dbPass,
      ];
    ?>
    <div style="display:flex;flex-direction:column;gap:8px;">
      <?php foreach ($campos as $label => $val): ?>
      <div style="display:flex;align-items:center;gap:10px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:8px 12px;">
        <span style="font-size:12px;color:#64748b;width:60px;flex-shrink:0;"><?php echo $label; ?></span>
        <code id="campo-<?php echo strtolower($label); ?>" style="flex:1;font-size:13px;color:#1e293b;word-break:break-all;"><?php echo View::e($val); ?></code>
        <button type="button" onclick="copiar('campo-<?php echo strtolower($label); ?>')" style="background:none;border:1px solid #e2e8f0;border-radius:6px;padding:2px 8px;font-size:11px;cursor:pointer;flex-shrink:0;">Copiar</button>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Conexão para aplicação (fora do Docker) -->
    <div style="margin-top:14px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:10px 12px;font-size:12px;color:#166534;">
      <strong>String de conexão (PHP PDO):</strong><br>
      <code id="campo-dsn" style="word-break:break-all;">mysql:host=<?php echo View::e($pmaHost); ?>;port=3306;dbname=<?php echo View::e((string)($b['db_name'] ?? '')); ?></code>
      <button type="button" onclick="copiar('campo-dsn')" style="margin-left:8px;background:none;border:1px solid #bbf7d0;border-radius:6px;padding:2px 8px;font-size:11px;cursor:pointer;">Copiar</button>
    </div>
    <?php if ($dbEngine === 'docker' && $containerId !== ''): ?>
    <div style="margin-top:8px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:10px 12px;font-size:12px;color:#1e40af;">
      <strong>Conexão externa (fora do Docker):</strong> <code>127.0.0.1:<?php echo View::e((string)($b['db_port'] ?? '3306')); ?></code>
      <button type="button" onclick="copiar('campo-ext')" style="margin-left:8px;background:none;border:1px solid #bfdbfe;border-radius:6px;padding:2px 8px;font-size:11px;cursor:pointer;">Copiar</button>
      <span id="campo-ext" style="display:none;">127.0.0.1:<?php echo View::e((string)($b['db_port'] ?? '3306')); ?></span>
    </div>
    <?php endif; ?>
  </div>

  <!-- SQL Runner -->
  <div class="card-new">
    <div class="card-new-title" style="margin-bottom:8px;">Executar SQL</div>
    <p style="font-size:12px;color:#64748b;margin-bottom:12px;">Execute queries, migrations ou scripts SQL diretamente no banco.</p>

    <textarea id="sqlInput" style="width:100%;height:140px;font-family:monospace;font-size:13px;padding:10px;border:1.5px solid #e2e8f0;border-radius:8px;resize:vertical;outline:none;box-sizing:border-box;" placeholder="CREATE TABLE users (id INT PRIMARY KEY AUTO_INCREMENT, name VARCHAR(100));&#10;-- ou cole suas migrations aqui"></textarea>

    <div style="display:flex;gap:8px;margin-top:8px;align-items:center;">
      <button class="botao sm" id="btnRunSql" onclick="executarSql()">▶ Executar</button>
      <button class="botao sm ghost" onclick="document.getElementById('sqlInput').value=''">Limpar</button>
      <span id="sqlStatus" style="font-size:12px;color:#64748b;"></span>
    </div>

    <div id="sqlOutput" style="display:none;margin-top:10px;background:#0b1020;color:#e2e8f0;border-radius:8px;padding:12px;font-size:12px;font-family:monospace;white-space:pre-wrap;max-height:200px;overflow-y:auto;"></div>
  </div>

</div>

<script>
var _csrf = '<?php echo View::e(Csrf::token()); ?>';
var _dbId = <?php echo $bid; ?>;

function copiar(id) {
  var el = document.getElementById(id);
  if (!el) return;
  navigator.clipboard.writeText(el.textContent.trim()).then(function() {
    var btn = el.nextElementSibling;
    if (btn) { var t = btn.textContent; btn.textContent = '✓'; setTimeout(function(){ btn.textContent = t; }, 1500); }
  });
}

function executarSql() {
  var sql = document.getElementById('sqlInput').value.trim();
  if (!sql) return;
  var btn = document.getElementById('btnRunSql');
  var st = document.getElementById('sqlStatus');
  var out = document.getElementById('sqlOutput');
  btn.disabled = true; st.textContent = 'Executando...'; st.style.color = '#64748b';
  out.style.display = 'none';

  var fd = new FormData();
  fd.append('_csrf', _csrf);
  fd.append('id', _dbId);
  fd.append('sql', sql);

  fetch('/cliente/banco-dados/sql', { method: 'POST', body: fd })
    .then(function(r) { return r.json(); })
    .then(function(d) {
      btn.disabled = false;
      if (d.ok) {
        st.textContent = '✓ Executado'; st.style.color = '#10b981';
        out.textContent = d.output || '(sem saída)';
        out.style.display = 'block';
      } else {
        st.textContent = '✘ Erro'; st.style.color = '#ef4444';
        out.textContent = d.erro || 'Erro desconhecido';
        out.style.color = '#ef4444';
        out.style.display = 'block';
      }
    })
    .catch(function() {
      btn.disabled = false;
      st.textContent = '✘ Erro de rede'; st.style.color = '#ef4444';
    });
}

document.getElementById('sqlInput').addEventListener('keydown', function(e) {
  if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') { e.preventDefault(); executarSql(); }
});
</script>

<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
