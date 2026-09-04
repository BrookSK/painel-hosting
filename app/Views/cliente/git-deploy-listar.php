<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\Csrf;
use LRV\Core\I18n;

$pageTitle = 'Git Deploy';
$clienteNome = (string)($cliente['name'] ?? '');
$clienteEmail = (string)($cliente['email'] ?? '');
require __DIR__ . '/../_partials/layout-cliente-inicio.php';
?>

<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:24px;">
  <div>
    <div class="page-title">Git Deploy</div>
    <div class="page-subtitle" style="margin-bottom:0;">Conecte repositórios GitHub/GitLab e faça deploy com 1 clique</div>
  </div>
  <a href="/cliente/git-deploy/novo" class="botao">+ Novo repositório</a>
</div>

<?php if (empty($deployments)): ?>
<div class="card-new" style="text-align:center;padding:48px 24px;">
  <div style="font-size:40px;margin-bottom:12px;"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"/><path d="M12 15l-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"/><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"/><path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"/></svg></div>
  <div style="font-size:16px;font-weight:600;margin-bottom:8px;">Nenhum repositório conectado</div>
  <div style="font-size:13px;color:#64748b;margin-bottom:20px;">Conecte um repositório Git para fazer deploy automático na sua VPS.</div>
  <a href="/cliente/git-deploy/novo" class="botao">Conectar repositório</a>
</div>
<?php else: ?>
<div style="display:flex;flex-direction:column;gap:14px;">
  <?php foreach ($deployments as $d):
    $did = (int)($d['id'] ?? 0);
    $status = (string)($d['status'] ?? 'active');
    $statusColor = $status === 'active' ? '#10b981' : ($status === 'error' ? '#ef4444' : '#94a3b8');
    $statusLabel = $status === 'active' ? 'Ativo' : ($status === 'error' ? 'Erro' : 'Inativo');
    $lastHash = (string)($d['last_commit_hash'] ?? '');
    $lastMsg = (string)($d['last_commit_message'] ?? '');
    $lastAt = (string)($d['last_deployed_at'] ?? '');
    $lastAuthor = (string)($d['last_commit_author'] ?? '');
    $appType = (string)($d['app_type'] ?? 'php');
    $appTypeIcon = match($appType) { 'nodejs' => '🟢', 'python' => '<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/><line x1="12" y1="2" x2="12" y2="22"/></svg>', 'static' => '<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>', default => '<svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>' };
    $appTypeLabel = match($appType) { 'nodejs' => 'Node.js', 'python' => 'Python', 'static' => 'Estático', default => 'PHP' };
  ?>
  <div class="card-new" id="dep-<?php echo $did; ?>">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:12px;">
      <div>
        <div style="font-size:15px;font-weight:700;color:#1e293b;margin-bottom:2px;"><?php echo $appTypeIcon; ?> <?php echo View::e((string)($d['name'] ?? '')); ?> <span style="font-size:11px;font-weight:500;color:#64748b;"><?php echo $appTypeLabel; ?></span></div>
        <div style="font-size:12px;color:#64748b;font-family:monospace;"><?php echo View::e((string)($d['repo_url'] ?? '')); ?> <span style="color:#4F46E5;">@<?php echo View::e((string)($d['branch'] ?? 'main')); ?></span>
          <?php if (in_array($appType, ['nodejs', 'python']) && !empty($d['app_port'])): ?>
            · <span style="color:#f59e0b;">:<?php echo (int)$d['app_port']; ?></span>
          <?php endif; ?>
          <?php if (!empty($d['subdomain'])): ?>
            · <a href="https://<?php echo View::e((string)$d['subdomain']); ?>" target="_blank" rel="noopener" style="color:#10b981;font-family:system-ui;"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg> <?php echo View::e((string)$d['subdomain']); ?></a>
          <?php endif; ?>
        </div>
      </div>
      <span style="font-size:11px;padding:3px 10px;border-radius:99px;background:<?php echo $statusColor; ?>20;color:<?php echo $statusColor; ?>;font-weight:600;"><?php echo $statusLabel; ?></span>
      <?php if (((int)($d['auto_deploy'] ?? 0)) === 1): ?>
        <span style="font-size:11px;padding:3px 10px;border-radius:99px;background:#dbeafe;color:#2563eb;font-weight:600;"><svg xmlns="http://www.w3.org/2000/svg" style="width:12px;height:12px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg> Auto Deploy</span>
      <?php endif; ?>
    </div>

    <?php if ($lastHash !== ''): ?>
    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;margin-bottom:12px;font-size:12px;">
      <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
        <code style="color:#4F46E5;"><?php echo View::e(substr($lastHash, 0, 8)); ?></code>
        <span style="color:#475569;flex:1;"><?php echo View::e($lastMsg); ?></span>
        <?php if ($lastAuthor !== ''): ?><span style="color:#94a3b8;">— <?php echo View::e($lastAuthor); ?></span><?php endif; ?>
        <?php if ($lastAt !== ''): ?><span style="color:#94a3b8;"><?php echo View::e($lastAt); ?></span><?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($d['error_message'])): ?>
    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:8px 12px;margin-bottom:12px;font-size:12px;color:#dc2626;"><?php echo View::e((string)$d['error_message']); ?></div>
    <?php endif; ?>

    <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
      <button class="botao sm" id="btn-deploy-<?php echo $did; ?>" onclick="executarDeploy(<?php echo $did; ?>)">▶ Deploy agora</button>
      <button class="botao sm ghost" onclick="verLogs(<?php echo $did; ?>)"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1"/></svg> Histórico</button>
      <a href="/cliente/arquivos?vps_id=<?php echo (int)($d['vps_id'] ?? 0); ?>&path=<?php echo urlencode((string)($d['deploy_path'] ?? '/var/www/html')); ?>&direct=1" class="botao sm ghost" title="Ver arquivos"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg> Arquivos</a>
      <button class="botao sm ghost" onclick="toggleConsole(<?php echo $did; ?>)" title="Executar comandos na pasta do projeto"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="4 17 10 11 4 5"/><line x1="12" y1="19" x2="20" y2="19"/></svg> Console</button>
      <?php if ($appType === 'nodejs'): ?>
      <button class="botao sm ghost" onclick="runQuickCmd(<?php echo $did; ?>,'pm2 restart deploy-<?php echo $did; ?> 2>&1 && pm2 status deploy-<?php echo $did; ?> 2>&1')" title="Reiniciar processo Node.js"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg> Reiniciar</button>
      <button class="botao sm ghost" onclick="runQuickCmd(<?php echo $did; ?>,'pm2 logs deploy-<?php echo $did; ?> --lines 30 --nostream 2>&1')" title="Ver logs PM2">📜 Logs PM2</button>
      <?php endif; ?>
      <a href="/cliente/git-deploy/editar?id=<?php echo $did; ?>" class="botao sm ghost">✏️ Editar</a>
      <button class="botao sm ghost" onclick="toggleServerLogs(<?php echo $did; ?>)" title="Ver logs do servidor"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1"/></svg> Logs servidor</button>
      <button class="botao sm ghost" id="btn-ssl-<?php echo $did; ?>" onclick="regerarSSL(<?php echo $did; ?>)" title="Reemite o certificado SSL e reativa o HTTPS caso o cadeado tenha caído"><svg xmlns="http://www.w3.org/2000/svg" style="width:16px;height:16px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg> Regerar SSL</button>
      <form method="post" action="/cliente/git-deploy/excluir" style="display:inline;" onsubmit="return confirmarExclusao(this)">
        <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>" />
        <input type="hidden" name="id" value="<?php echo $did; ?>" />
        <input type="hidden" name="apagar_arquivos" value="0" id="apagar-arquivos-<?php echo $did; ?>" />
        <button class="botao danger sm" type="submit"><svg xmlns="http://www.w3.org/2000/svg" style="width:14px;height:14px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg> Remover</button>
      </form>
      <span id="deploy-status-<?php echo $did; ?>" style="font-size:12px;color:#64748b;"></span>
    </div>

    <!-- Console inline -->
    <div id="console-<?php echo $did; ?>" style="display:none;margin-top:12px;background:#0b1020;border-radius:8px;padding:12px;font-family:monospace;font-size:12px;">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
        <span style="color:#64748b;">📂 <?php echo View::e((string)($d['deploy_path'] ?? '/var/www/html')); ?></span>
        <div style="display:flex;gap:4px;">
          <button onclick="runQuickCmd(<?php echo $did; ?>,'curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && apt-get install -y nodejs 2>&1 && node -v && npm -v && (test -f package.json && npm install 2>&1 || true)')" style="background:#1e293b;color:#94a3b8;border:1px solid #334155;border-radius:4px;padding:2px 6px;font-size:10px;cursor:pointer;" title="Instalar Node.js + npm install"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="16.5" y1="9.4" x2="7.5" y2="4.21"/><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg> Node.js</button>
          <button onclick="runQuickCmd(<?php echo $did; ?>,'(which composer >/dev/null 2>&1 || (curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer 2>&1)) && composer install --no-interaction --no-dev 2>&1')" style="background:#1e293b;color:#94a3b8;border:1px solid #334155;border-radius:4px;padding:2px 6px;font-size:10px;cursor:pointer;" title="Instalar Composer + dependências"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="16.5" y1="9.4" x2="7.5" y2="4.21"/><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg> Composer</button>
          <button onclick="runQuickCmd(<?php echo $did; ?>,'apt-get update -qq && apt-get install -y -qq python3 python3-pip 2>&1 && python3 --version && (test -f requirements.txt && pip3 install -r requirements.txt 2>&1 || true)')" style="background:#1e293b;color:#94a3b8;border:1px solid #334155;border-radius:4px;padding:2px 6px;font-size:10px;cursor:pointer;" title="Instalar Python + dependências"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="16.5" y1="9.4" x2="7.5" y2="4.21"/><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg> Python</button>
        </div>
      </div>
      <div id="console-output-<?php echo $did; ?>" style="color:#e2e8f0;white-space:pre-wrap;max-height:300px;overflow-y:auto;margin-bottom:8px;"></div>
      <div style="display:flex;gap:6px;">
        <span style="color:#10b981;flex-shrink:0;">$</span>
        <input type="text" id="console-input-<?php echo $did; ?>" style="flex:1;background:transparent;border:none;color:#e2e8f0;font-family:monospace;font-size:12px;outline:none;" placeholder="npm install, npm run build, ls -la..." onkeydown="if(event.key==='Enter')runConsoleCmd(<?php echo $did; ?>)" />
        <button onclick="runConsoleCmd(<?php echo $did; ?>)" style="background:#4F46E5;color:#fff;border:none;border-radius:4px;padding:2px 10px;font-size:11px;cursor:pointer;">▶</button>
      </div>
    </div>

    <!-- Logs accordion -->
    <div id="logs-<?php echo $did; ?>" style="display:none;margin-top:12px;"></div>

    <!-- Server logs viewer -->
    <div id="server-logs-<?php echo $did; ?>" style="display:none;margin-top:12px;background:#0b1020;border-radius:8px;padding:12px;font-family:monospace;font-size:12px;">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
        <div style="display:flex;gap:6px;">
          <span style="color:#64748b;font-size:11px;"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1"/></svg> Logs do servidor</span>
          <button onclick="carregarServerLogs(<?php echo $did; ?>,'all')" style="background:#1e293b;color:#e2e8f0;border:1px solid #334155;border-radius:4px;padding:2px 8px;font-size:10px;cursor:pointer;">Todos</button>
          <button onclick="carregarServerLogs(<?php echo $did; ?>,'nginx')" style="background:#1e293b;color:#94a3b8;border:1px solid #334155;border-radius:4px;padding:2px 8px;font-size:10px;cursor:pointer;">Nginx</button>
          <button onclick="carregarServerLogs(<?php echo $did; ?>,'php')" style="background:#1e293b;color:#94a3b8;border:1px solid #334155;border-radius:4px;padding:2px 8px;font-size:10px;cursor:pointer;">PHP</button>
          <button onclick="carregarServerLogs(<?php echo $did; ?>,'app')" style="background:#1e293b;color:#94a3b8;border:1px solid #334155;border-radius:4px;padding:2px 8px;font-size:10px;cursor:pointer;">App</button>
        </div>
        <button onclick="carregarServerLogs(<?php echo $did; ?>,'all')" style="background:none;border:none;color:#64748b;cursor:pointer;font-size:11px;"><svg xmlns="http://www.w3.org/2000/svg" style="width:18px;height:18px;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg></button>
      </div>
      <pre id="server-logs-output-<?php echo $did; ?>" style="color:#e2e8f0;white-space:pre-wrap;max-height:400px;overflow-y:auto;margin:0;">Carregando...</pre>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<script>
var _csrf = '<?php echo View::e(Csrf::token()); ?>';

function confirmarExclusao(form) {
  var id = form.querySelector('input[name="id"]').value;
  var msg = 'Remover esta integração?\n\nEscolha:\n• OK = remove apenas a integração do painel (os arquivos ficam no servidor)\n\nSe quiser TAMBÉM apagar os arquivos do servidor, marque a opção abaixo antes de clicar em Remover.';

  // Create a custom dialog
  var overlay = document.createElement('div');
  overlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;display:flex;align-items:center;justify-content:center;';
  var box = document.createElement('div');
  box.style.cssText = 'background:#fff;border-radius:14px;padding:24px;max-width:420px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,.3);';
  box.innerHTML = '<div style="font-size:16px;font-weight:700;color:#dc2626;margin-bottom:12px;">Remover integração</div>'
    + '<p style="font-size:14px;color:#334155;margin:0 0 16px;line-height:1.6;">Tem certeza que deseja remover esta integração do Git Deploy?</p>'
    + '<label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:12px;margin-bottom:16px;">'
    + '<input type="checkbox" id="chk-apagar-' + id + '" style="margin-top:2px;accent-color:#dc2626;width:16px;height:16px;flex-shrink:0;" />'
    + '<div><div style="font-size:13px;font-weight:600;color:#dc2626;">Também apagar os arquivos do servidor</div>'
    + '<div style="font-size:12px;color:#92400e;margin-top:2px;">Isso vai excluir permanentemente a pasta do projeto no servidor. Essa ação não pode ser desfeita.</div></div>'
    + '</label>'
    + '<div style="display:flex;gap:8px;justify-content:flex-end;">'
    + '<button id="cancel-del-' + id + '" style="padding:8px 16px;border-radius:8px;border:1px solid #e2e8f0;background:#fff;cursor:pointer;font-size:13px;">Cancelar</button>'
    + '<button id="confirm-del-' + id + '" style="padding:8px 16px;border-radius:8px;border:none;background:#dc2626;color:#fff;cursor:pointer;font-size:13px;font-weight:600;">Remover</button>'
    + '</div>';
  overlay.appendChild(box);
  document.body.appendChild(overlay);

  document.getElementById('cancel-del-' + id).onclick = function() { document.body.removeChild(overlay); };
  overlay.addEventListener('click', function(e) { if (e.target === overlay) document.body.removeChild(overlay); });

  document.getElementById('confirm-del-' + id).onclick = function() {
    var chk = document.getElementById('chk-apagar-' + id);
    form.querySelector('input[name="apagar_arquivos"]').value = chk.checked ? '1' : '0';
    document.body.removeChild(overlay);
    form.onsubmit = null;
    form.submit();
  };

  return false;
}

function executarDeploy(id) {
  var btn = document.getElementById('btn-deploy-' + id);
  var st = document.getElementById('deploy-status-' + id);
  btn.disabled = true; btn.textContent = '⏳ Fazendo deploy...';
  st.textContent = ''; st.style.color = '#64748b';

  var fd = new FormData();
  fd.append('_csrf', _csrf);
  fd.append('id', id);

  fetch('/cliente/git-deploy/deploy', { method: 'POST', body: fd })
    .then(function(r) { return r.json(); })
    .then(function(d) {
      if (d.ok) {
        st.textContent = '✓ Deploy concluído — ' + (d.commit ? d.commit.substring(0, 8) : '') + ' ' + (d.mensagem || '');
        st.style.color = '#10b981';
        setTimeout(function() { location.reload(); }, 2000);
      } else {
        st.textContent = '✘ ' + (d.erro || 'Erro');
        st.style.color = '#ef4444';
        btn.disabled = false; btn.textContent = '▶ Deploy agora';
      }
    })
    .catch(function() {
      st.textContent = '✘ Erro de rede';
      st.style.color = '#ef4444';
      btn.disabled = false; btn.textContent = '▶ Deploy agora';
    });
}

function verLogs(id) {
  var el = document.getElementById('logs-' + id);
  if (el.style.display !== 'none') { el.style.display = 'none'; return; }
  el.innerHTML = '<div style="font-size:12px;color:#64748b;padding:8px;">Carregando...</div>';
  el.style.display = 'block';

  fetch('/cliente/git-deploy/logs?id=' + id)
    .then(function(r) { return r.json(); })
    .then(function(d) {
      if (!d.ok || !d.logs.length) { el.innerHTML = '<div style="font-size:12px;color:#94a3b8;padding:8px;">Nenhum log encontrado.</div>'; return; }
      var html = '<div style="font-size:12px;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;">';
      d.logs.forEach(function(l) {
        var ok = l.status === 'success';
        html += '<div style="display:flex;gap:10px;padding:8px 12px;border-bottom:1px solid #f1f5f9;align-items:flex-start;">';
        html += '<span style="color:' + (ok ? '#10b981' : '#ef4444') + ';flex-shrink:0;">' + (ok ? '✓' : '✘') + '</span>';
        html += '<div style="flex:1;min-width:0;">';
        if (l.commit_hash) html += '<code style="color:#4F46E5;">' + l.commit_hash.substring(0, 8) + '</code> ';
        if (l.commit_message) html += '<span style="color:#475569;">' + escHtml(l.commit_message) + '</span>';
        if (l.commit_author) html += ' <span style="color:#94a3b8;">— ' + escHtml(l.commit_author) + '</span>';
        html += '<div style="color:#94a3b8;margin-top:2px;">' + escHtml(l.deployed_at) + '</div>';
        if (!ok && l.output) html += '<pre style="margin-top:4px;background:#fef2f2;color:#dc2626;padding:6px;border-radius:6px;font-size:11px;white-space:pre-wrap;max-height:120px;overflow:auto;">' + escHtml(l.output) + '</pre>';
        html += '</div></div>';
      });
      html += '</div>';
      el.innerHTML = html;
    });
}

function escHtml(s) {
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function toggleConsole(id) {
  var el = document.getElementById('console-' + id);
  el.style.display = el.style.display === 'none' ? 'block' : 'none';
  if (el.style.display !== 'none') document.getElementById('console-input-' + id).focus();
}

function runQuickCmd(id, cmd) {
  var output = document.getElementById('console-output-' + id);
  var el = document.getElementById('console-' + id);
  if (el.style.display === 'none') el.style.display = 'block';
  output.textContent += '$ ' + cmd.substring(0, 80) + '...\n⏳ Instalando (pode demorar)...\n';
  output.scrollTop = output.scrollHeight;

  var fd = new FormData();
  fd.append('_csrf', _csrf);
  fd.append('id', id);
  fd.append('command', cmd);

  fetch('/cliente/git-deploy/console', { method: 'POST', body: fd })
    .then(function(r) { return r.json(); })
    .then(function(d) {
      if (d.ok) {
        output.textContent += (d.output || '✓ Concluído') + '\n';
      } else {
        output.textContent += '✘ ' + (d.erro || 'Erro') + '\n';
      }
      output.scrollTop = output.scrollHeight;
    })
    .catch(function() { output.textContent += '✘ Erro de rede\n'; });
}

function runConsoleCmd(id) {
  var input = document.getElementById('console-input-' + id);
  var output = document.getElementById('console-output-' + id);
  var cmd = input.value.trim();
  if (!cmd) return;

  output.textContent += '$ ' + cmd + '\n';
  input.value = '';
  input.disabled = true;

  var fd = new FormData();
  fd.append('_csrf', _csrf);
  fd.append('id', id);
  fd.append('command', cmd);

  fetch('/cliente/git-deploy/console', { method: 'POST', body: fd })
    .then(function(r) { return r.json(); })
    .then(function(d) {
      input.disabled = false;
      input.focus();
      if (d.ok) {
        if (d.output) output.textContent += d.output + '\n';
      } else {
        output.textContent += '✘ ' + (d.erro || 'Erro') + '\n';
      }
      output.scrollTop = output.scrollHeight;
    })
    .catch(function() {
      input.disabled = false;
      output.textContent += '✘ Erro de rede\n';
    });
}
function toggleServerLogs(id) {
  var el = document.getElementById('server-logs-' + id);
  if (el.style.display === 'none') {
    el.style.display = 'block';
    carregarServerLogs(id, 'all');
  } else {
    el.style.display = 'none';
  }
}

function regerarSSL(id) {
  if (!confirm('Regerar o certificado SSL deste site?\n\nUse isto se o cadeado de segurança (https) parou de funcionar. O sistema reemite o certificado e reativa o HTTPS automaticamente. Pode levar até 1 minuto.')) return;
  var btn = document.getElementById('btn-ssl-' + id);
  var txt = btn ? btn.innerHTML : '';
  if (btn) { btn.disabled = true; btn.textContent = 'Regerando SSL...'; }
  var status = document.getElementById('deploy-status-' + id);
  if (status) { status.textContent = 'Regerando SSL, aguarde...'; status.style.color = '#64748b'; }

  var fd = new FormData();
  fd.append('_csrf', _csrf);
  fd.append('id', id);

  fetch('/cliente/git-deploy/regerar-ssl', { method: 'POST', body: fd })
    .then(function(r) { return r.json(); })
    .then(function(d) {
      if (btn) { btn.disabled = false; btn.innerHTML = txt; }
      if (d.ok) {
        if (status) { status.textContent = '\u2713 ' + (d.mensagem || 'SSL regerado com sucesso!'); status.style.color = '#16a34a'; }
      } else {
        if (status) { status.textContent = '\u2717 ' + (d.erro || 'Falha ao regerar SSL'); status.style.color = '#dc2626'; }
        alert('Não foi possível regerar o SSL:\n\n' + (d.erro || 'Erro desconhecido') + '\n\nSe persistir, abra um ticket de suporte.');
      }
    })
    .catch(function() {
      if (btn) { btn.disabled = false; btn.innerHTML = txt; }
      if (status) { status.textContent = '\u2717 Erro de conexão'; status.style.color = '#dc2626'; }
    });
}

function carregarServerLogs(id, tipo) {
  var output = document.getElementById('server-logs-output-' + id);
  output.textContent = '⏳ Carregando logs...';

  fetch('/cliente/git-deploy/server-logs?id=' + id + '&tipo=' + tipo + '&linhas=100')
    .then(function(r) { return r.json(); })
    .then(function(d) {
      if (d.ok) {
        output.textContent = d.logs || '(sem logs)';
        output.scrollTop = output.scrollHeight;
      } else {
        output.textContent = '✘ ' + (d.erro || 'Erro ao carregar logs');
      }
    })
    .catch(function() { output.textContent = '✘ Erro de rede'; });
}
</script>

<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
