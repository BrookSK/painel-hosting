<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\Csrf;
use LRV\Core\I18n;

$keys = is_array($keys ?? null) ? $keys : [];
$novaChave = (string)($novaChave ?? '');
$sucesso = (string)($sucesso ?? '');
$erro = (string)($erro ?? '');

$pageTitle = I18n::t('api_keys.titulo');
require __DIR__ . '/../_partials/layout-cliente-inicio.php';

$escoposDisponiveis = [
    'clients.read' => I18n::t('api_keys.scope_clients_read'),
    'clients.write' => I18n::t('api_keys.scope_clients_write'),
    'hosting.read' => I18n::t('api_keys.scope_hosting_read'),
    'hosting.write' => I18n::t('api_keys.scope_hosting_write'),
    'tickets.read' => I18n::t('api_keys.scope_tickets_read'),
    'tickets.write' => I18n::t('api_keys.scope_tickets_write'),
    'domains.read' => I18n::t('api_keys.scope_domains_read'),
    'domains.write' => I18n::t('api_keys.scope_domains_write'),
    'billing.read' => I18n::t('api_keys.scope_billing_read'),
    'billing.write' => I18n::t('api_keys.scope_billing_write'),
    'backups.read' => I18n::t('api_keys.scope_backups_read'),
    'backups.write' => I18n::t('api_keys.scope_backups_write'),
    'monitoring.read' => I18n::t('api_keys.scope_monitoring_read'),
    'webhooks.read' => I18n::t('api_keys.scope_webhooks_read'),
    'webhooks.write' => I18n::t('api_keys.scope_webhooks_write'),
    'applications.read' => I18n::t('api_keys.scope_apps_read'),
    'applications.write' => I18n::t('api_keys.scope_apps_write'),
    'databases.read' => I18n::t('api_keys.scope_databases_read'),
    'databases.write' => I18n::t('api_keys.scope_databases_write'),
    'emails.read' => I18n::t('api_keys.scope_emails_read'),
    'emails.write' => I18n::t('api_keys.scope_emails_write'),
];

function _apiKeyStatus(string $s): string {
    return match($s) {
        'active' => '<span class="badge-new badge-green">' . I18n::t('api_keys.status_active') . '</span>',
        'revoked' => '<span class="badge-new badge-red">' . I18n::t('api_keys.status_revoked') . '</span>',
        'expired' => '<span class="badge-new badge-yellow">' . I18n::t('api_keys.status_expired') . '</span>',
        default => '<span class="badge-new badge-gray">' . View::e($s) . '</span>',
    };
}

function _apiKeyEnvBadge(string $env): string {
    return match($env) {
        'production' => '<span class="badge-new badge-blue">' . I18n::t('api_keys.env_production') . '</span>',
        'sandbox' => '<span class="badge-new badge-yellow">' . I18n::t('api_keys.env_sandbox') . '</span>',
        default => '<span class="badge-new badge-gray">' . View::e($env) . '</span>',
    };
}
?>

<style>
.api-keys-header { display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:12px; margin-bottom:24px; }
.api-key-revealed {
    background: #1e3a5f; border: 2px solid #3b82f6; border-radius: 10px; padding: 16px 20px;
    margin-bottom: 20px; position: relative;
}
.api-key-revealed code {
    font-family: 'JetBrains Mono', 'Fira Code', monospace; font-size: 13px; color: #93c5fd;
    word-break: break-all; display: block; margin: 8px 0;
}
.api-key-revealed .warning-text { color: #fbbf24; font-size: 12px; font-weight: 600; margin-top: 8px; }
.api-key-copy-btn {
    position: absolute; top: 12px; right: 12px; background: #3b82f6; color: #fff; border: none;
    border-radius: 6px; padding: 6px 12px; font-size: 12px; cursor: pointer; transition: background .2s;
}
.api-key-copy-btn:hover { background: #2563eb; }
.scopes-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); gap: 8px; }
.scope-item {
    display: flex; align-items: center; gap: 10px; font-size: 13px; color: #cbd5e1;
    padding: 10px 12px; background: #1e293b; border-radius: 10px;
    border: 1.5px solid #334155; cursor: pointer; transition: all .15s; user-select: none;
}
.scope-item:hover { border-color: #6366f1; }
.scope-item:has(input:checked) { border-color: #6366f1; background: rgba(99,102,241,.08); color: #e2e8f0; }
.scope-item input[type="checkbox"] { accent-color: #6366f1; width: 16px; height: 16px; cursor: pointer; flex-shrink: 0; }
.keys-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.keys-table th { padding: 10px 12px; text-align: left; color: #94a3b8; font-weight: 600; border-bottom: 2px solid #334155; font-size: 12px; text-transform: uppercase; letter-spacing: .5px; }
.keys-table td { padding: 10px 12px; border-bottom: 1px solid #1e293b; color: #cbd5e1; vertical-align: middle; }
.keys-table tr:hover td { background: rgba(59,130,246,.04); }
.key-hint { font-family: 'JetBrains Mono', monospace; color: #94a3b8; font-size: 12px; }
.key-actions { display: flex; gap: 6px; flex-wrap: wrap; }
.key-actions form { display: inline; }
@media (max-width: 900px) {
    .keys-table-wrap { overflow-x: auto; }
    .scopes-grid { grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); }
}
</style>

<div class="api-keys-header">
  <div>
    <div class="page-title"><?= I18n::t('api_keys.titulo') ?></div>
    <div class="page-subtitle" style="margin-bottom:0;"><?= I18n::t('api_keys.subtitulo') ?></div>
  </div>
  <button class="botao" onclick="window.location='/cliente/api-keys/novo'">
    <svg width="16" height="16" viewBox="0 0 20 20" fill="none" style="vertical-align:middle;margin-right:4px;"><path d="M10 4v12M4 10h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
    <?= I18n::t('api_keys.criar_btn') ?>
  </button>
</div>

<?php if ($sucesso === 'criada'): ?>
  <div class="sucesso"><?= I18n::t('api_keys.msg_criada') ?></div>
<?php elseif ($sucesso === 'revogada'): ?>
  <div class="sucesso"><?= I18n::t('api_keys.msg_revogada') ?></div>
<?php elseif ($sucesso === 'rotacionada'): ?>
  <div class="sucesso"><?= I18n::t('api_keys.msg_rotacionada') ?></div>
<?php endif; ?>

<?php if ($erro === 'csrf'): ?>
  <div class="erro"><?= I18n::t('api_keys.erro_csrf') ?></div>
<?php elseif ($erro === 'nome_obrigatorio'): ?>
  <div class="erro"><?= I18n::t('api_keys.erro_nome') ?></div>
<?php elseif ($erro === 'escopos_obrigatorio'): ?>
  <div class="erro"><?= I18n::t('api_keys.erro_escopos') ?></div>
<?php elseif ($erro === 'nao_encontrada'): ?>
  <div class="erro"><?= I18n::t('api_keys.erro_nao_encontrada') ?></div>
<?php elseif ($erro === 'invalida'): ?>
  <div class="erro"><?= I18n::t('api_keys.erro_invalida') ?></div>
<?php endif; ?>

<?php if ($novaChave !== ''): ?>
<div class="api-key-revealed">
  <div style="font-weight:700;color:#e2e8f0;font-size:14px;display:flex;align-items:center;gap:8px;"><svg viewBox="0 0 24 24" fill="none" stroke="#93c5fd" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg> <?= I18n::t('api_keys.sua_nova_chave') ?></div>
  <code id="novaChaveTexto"><?php echo View::e($novaChave); ?></code>
  <button class="api-key-copy-btn" onclick="copiarChave()"><?= I18n::t('api_keys.copiar') ?></button>
  <div class="warning-text"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="12" height="12" style="vertical-align:middle;margin-right:4px"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg> <?= I18n::t('api_keys.aviso_guardar') ?></div>
</div>
<?php endif; ?>

<!-- Keys listing -->
<div class="card-new">
<?php if (empty($keys)): ?>
  <div style="text-align:center;padding:40px 20px;">
    <div style="margin-bottom:10px;"><svg viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" width="40" height="40"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg></div>
    <div style="font-size:15px;font-weight:600;color:#e2e8f0;margin-bottom:4px;"><?= I18n::t('api_keys.nenhuma_chave') ?></div>
    <div style="font-size:13px;color:#94a3b8;"><?= I18n::t('api_keys.nenhuma_chave_desc') ?></div>
  </div>
<?php else: ?>
  <div class="keys-table-wrap">
    <table class="keys-table">
      <thead>
        <tr>
          <th><?= I18n::t('api_keys.col_nome') ?></th>
          <th><?= I18n::t('api_keys.col_ambiente') ?></th>
          <th><?= I18n::t('api_keys.col_chave') ?></th>
          <th><?= I18n::t('geral.status') ?></th>
          <th>Scopes</th>
          <th><?= I18n::t('api_keys.col_ultimo_uso') ?></th>
          <th><?= I18n::t('api_keys.col_requisicoes') ?></th>
          <th><?= I18n::t('api_keys.col_criado') ?></th>
          <th><?= I18n::t('geral.acoes') ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($keys as $k):
          $kId = (int)($k['id'] ?? 0);
          $kStatus = (string)($k['status'] ?? 'active');
          $kEscopos = json_decode((string)($k['scopes'] ?? '[]'), true);
          $kEscoposCount = is_array($kEscopos) ? count($kEscopos) : 0;
          $kLastUsed = ($k['last_used_at'] ?? null) ? date('d/m/Y H:i', strtotime((string)$k['last_used_at'])) : '—';
          $kRequests = number_format((int)($k['request_count'] ?? 0));
          $kCreated = date('d/m/Y', strtotime((string)($k['created_at'] ?? 'now')));
        ?>
        <tr>
          <td>
            <div style="font-weight:600;color:#e2e8f0;"><?php echo View::e((string)($k['name'] ?? '')); ?></div>
            <?php if (!empty($k['description'])): ?>
              <div style="font-size:11px;color:#64748b;margin-top:2px;"><?php echo View::e((string)$k['description']); ?></div>
            <?php endif; ?>
          </td>
          <td><?php echo _apiKeyEnvBadge((string)($k['environment'] ?? '')); ?></td>
          <td><span class="key-hint"><?php echo View::e((string)($k['prefix'] ?? '')); ?>····<?php echo View::e((string)($k['key_hint'] ?? '')); ?></span></td>
          <td><?php echo _apiKeyStatus($kStatus); ?></td>
          <td><span class="badge-new badge-gray"><?php echo $kEscoposCount; ?> scope<?php echo $kEscoposCount !== 1 ? 's' : ''; ?></span></td>
          <td style="font-size:12px;"><?php echo View::e($kLastUsed); ?></td>
          <td style="font-size:12px;"><?php echo $kRequests; ?></td>
          <td style="font-size:12px;"><?php echo View::e($kCreated); ?></td>
          <td>
            <?php if ($kStatus === 'active'): ?>
            <div class="key-actions">
              <form method="post" action="/cliente/api-keys/rotacionar" onsubmit="return confirm('Rotate this key? The current key will be revoked and a new one generated.')">
                <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>" />
                <input type="hidden" name="key_id" value="<?php echo $kId; ?>" />
                <button class="botao ghost sm" type="submit" style="font-size:11px;" title="Rotate"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="12" height="12" style="vertical-align:middle;margin-right:2px"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg> Rotate</button>
              </form>
              <form method="post" action="/cliente/api-keys/revogar" onsubmit="return confirm('Revoke this API Key? This action cannot be undone.')">
                <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>" />
                <input type="hidden" name="key_id" value="<?php echo $kId; ?>" />
                <button class="botao ghost sm" type="submit" style="font-size:11px;color:#ef4444;" title="Revoke"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="12" height="12" style="vertical-align:middle;margin-right:2px"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> Revoke</button>
              </form>
            </div>
            <?php else: ?>
              <span style="font-size:11px;color:#64748b;">—</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
</div>

<script>
function copiarChave() {
    const texto = document.getElementById('novaChaveTexto');
    if (!texto) return;
    navigator.clipboard.writeText(texto.textContent.trim()).then(() => {
        const btn = document.querySelector('.api-key-copy-btn');
        if (btn) { btn.textContent = '<?= I18n::t('api_keys.copiado') ?>'; setTimeout(() => btn.textContent = '<?= I18n::t('api_keys.copiar') ?>', 2000); }
    });
}
</script>

<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
