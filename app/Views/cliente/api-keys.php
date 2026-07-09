<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\Csrf;
use LRV\Core\I18n;

$keys = is_array($keys ?? null) ? $keys : [];
$novaChave = (string)($novaChave ?? '');
$sucesso = (string)($sucesso ?? '');
$erro = (string)($erro ?? '');

$pageTitle = 'API Keys';
require __DIR__ . '/../_partials/layout-cliente-inicio.php';

$escoposDisponiveis = [
    'clients.read' => 'Clients (Read)',
    'clients.write' => 'Clients (Write)',
    'hosting.read' => 'Hosting (Read)',
    'hosting.write' => 'Hosting (Write)',
    'tickets.read' => 'Tickets (Read)',
    'tickets.write' => 'Tickets (Write)',
    'domains.read' => 'Domains (Read)',
    'domains.write' => 'Domains (Write)',
    'billing.read' => 'Billing (Read)',
    'billing.write' => 'Billing (Write)',
    'backups.read' => 'Backups (Read)',
    'backups.write' => 'Backups (Write)',
    'monitoring.read' => 'Monitoring (Read)',
    'webhooks.read' => 'Webhooks (Read)',
    'webhooks.write' => 'Webhooks (Write)',
    'applications.read' => 'Applications (Read)',
    'applications.write' => 'Applications (Write)',
    'databases.read' => 'Databases (Read)',
    'databases.write' => 'Databases (Write)',
    'emails.read' => 'Emails (Read)',
    'emails.write' => 'Emails (Write)',
];

function _apiKeyStatus(string $s): string {
    return match($s) {
        'active' => '<span class="badge-new badge-green">Active</span>',
        'revoked' => '<span class="badge-new badge-red">Revoked</span>',
        'expired' => '<span class="badge-new badge-yellow">Expired</span>',
        default => '<span class="badge-new badge-gray">' . View::e($s) . '</span>',
    };
}

function _apiKeyEnvBadge(string $env): string {
    return match($env) {
        'production' => '<span class="badge-new badge-blue">Production</span>',
        'sandbox' => '<span class="badge-new badge-yellow">Sandbox</span>',
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
.scopes-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 6px; }
.scope-item { display: flex; align-items: center; gap: 6px; font-size: 13px; color: #cbd5e1; }
.scope-item input[type="checkbox"] { accent-color: #3b82f6; }
.modal-overlay {
    display: none; position: fixed; inset: 0; background: rgba(0,0,0,.6); z-index: 1000;
    align-items: center; justify-content: center; padding: 20px;
}
.modal-overlay.active { display: flex; }
.modal-box {
    background: #16213e; border-radius: 12px; padding: 24px; width: 100%; max-width: 600px;
    max-height: 85vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,.5);
}
.modal-box h3 { margin: 0 0 16px; font-size: 18px; color: #e2e8f0; }
.form-group { margin-bottom: 14px; }
.form-group label { display: block; font-size: 13px; font-weight: 600; color: #94a3b8; margin-bottom: 4px; }
.form-group input, .form-group select, .form-group textarea {
    width: 100%; background: #1e293b; border: 1px solid #334155; border-radius: 8px;
    padding: 10px 12px; color: #e2e8f0; font-size: 14px; outline: none; transition: border-color .2s;
}
.form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color: #3b82f6; }
.form-group textarea { resize: vertical; min-height: 60px; }
.modal-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 18px; }
.btn-cancel { background: #334155; color: #e2e8f0; border: none; border-radius: 8px; padding: 10px 18px; cursor: pointer; font-size: 14px; }
.btn-cancel:hover { background: #475569; }
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
    <div class="page-title">API Keys</div>
    <div class="page-subtitle" style="margin-bottom:0;">Manage your Public API authentication keys</div>
  </div>
  <button class="botao" onclick="document.getElementById('modalCriar').classList.add('active')">
    <svg width="16" height="16" viewBox="0 0 20 20" fill="none" style="vertical-align:middle;margin-right:4px;"><path d="M10 4v12M4 10h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
    Create API Key
  </button>
</div>

<?php if ($sucesso === 'criada'): ?>
  <div class="sucesso">API Key created successfully. Copy it now — it won't be shown again.</div>
<?php elseif ($sucesso === 'revogada'): ?>
  <div class="sucesso">API Key revoked.</div>
<?php elseif ($sucesso === 'rotacionada'): ?>
  <div class="sucesso">API Key rotated. Copy the new key — it won't be shown again.</div>
<?php endif; ?>

<?php if ($erro === 'csrf'): ?>
  <div class="erro">Session expired. Please try again.</div>
<?php elseif ($erro === 'nome_obrigatorio'): ?>
  <div class="erro">The key name is required.</div>
<?php elseif ($erro === 'escopos_obrigatorio'): ?>
  <div class="erro">Select at least one scope.</div>
<?php elseif ($erro === 'nao_encontrada'): ?>
  <div class="erro">Key not found or already revoked.</div>
<?php elseif ($erro === 'invalida'): ?>
  <div class="erro">Invalid key.</div>
<?php endif; ?>

<?php if ($novaChave !== ''): ?>
<div class="api-key-revealed">
  <div style="font-weight:700;color:#e2e8f0;font-size:14px;display:flex;align-items:center;gap:8px;"><svg viewBox="0 0 24 24" fill="none" stroke="#93c5fd" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg> Your new API Key</div>
  <code id="novaChaveTexto"><?php echo View::e($novaChave); ?></code>
  <button class="api-key-copy-btn" onclick="copiarChave()">Copy</button>
  <div class="warning-text"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="12" height="12" style="vertical-align:middle;margin-right:4px"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg> Store this key securely. It won't be shown again.</div>
</div>
<?php endif; ?>

<!-- Keys listing -->
<div class="card-new">
<?php if (empty($keys)): ?>
  <div style="text-align:center;padding:40px 20px;">
    <div style="margin-bottom:10px;"><svg viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" width="40" height="40"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg></div>
    <div style="font-size:15px;font-weight:600;color:#e2e8f0;margin-bottom:4px;">No API Keys yet</div>
    <div style="font-size:13px;color:#94a3b8;">Create your first key to start using the Public API.</div>
  </div>
<?php else: ?>
  <div class="keys-table-wrap">
    <table class="keys-table">
      <thead>
        <tr>
          <th>Name</th>
          <th>Environment</th>
          <th>Key</th>
          <th>Status</th>
          <th>Scopes</th>
          <th>Last Used</th>
          <th>Requests</th>
          <th>Created</th>
          <th>Actions</th>
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

<!-- Create Modal -->
<div class="modal-overlay" id="modalCriar">
  <div class="modal-box">
    <h3>Create API Key</h3>
    <form method="post" action="/cliente/api-keys/criar">
      <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>" />

      <div class="form-group">
        <label for="ak_nome">Name *</label>
        <input type="text" id="ak_nome" name="nome" required maxlength="100" placeholder="e.g. My Integration" />
      </div>

      <div class="form-group">
        <label for="ak_descricao">Description</label>
        <textarea id="ak_descricao" name="descricao" maxlength="255" placeholder="Optional description for this key"></textarea>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div class="form-group">
          <label for="ak_ambiente">Environment</label>
          <select id="ak_ambiente" name="ambiente">
            <option value="production">Production</option>
            <option value="sandbox">Sandbox</option>
          </select>
        </div>
        <div class="form-group">
          <label for="ak_rate_limit">Rate Limit (req/min)</label>
          <input type="number" id="ak_rate_limit" name="rate_limit" value="60" min="1" max="1000" />
        </div>
      </div>

      <div class="form-group">
        <label for="ak_expira">Expiration (optional)</label>
        <input type="date" id="ak_expira" name="expira_em" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" />
      </div>

      <div class="form-group">
        <label>Scopes *</label>
        <div style="margin-bottom:8px;">
          <button type="button" onclick="toggleAllScopes(true)" class="botao ghost sm" style="font-size:11px;margin-right:4px;">Select All</button>
          <button type="button" onclick="toggleAllScopes(false)" class="botao ghost sm" style="font-size:11px;">Deselect All</button>
        </div>
        <div class="scopes-grid">
          <?php foreach ($escoposDisponiveis as $escopo => $label): ?>
          <label class="scope-item">
            <input type="checkbox" name="escopos[]" value="<?php echo View::e($escopo); ?>" />
            <span><?php echo View::e($label); ?></span>
          </label>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="modal-actions">
        <button type="button" class="btn-cancel" onclick="document.getElementById('modalCriar').classList.remove('active')">Cancel</button>
        <button type="submit" class="botao">Create Key</button>
      </div>
    </form>
  </div>
</div>

<script>
function copiarChave() {
    const texto = document.getElementById('novaChaveTexto');
    if (!texto) return;
    navigator.clipboard.writeText(texto.textContent.trim()).then(() => {
        const btn = document.querySelector('.api-key-copy-btn');
        if (btn) { btn.textContent = 'Copied!'; setTimeout(() => btn.textContent = 'Copy', 2000); }
    });
}

function toggleAllScopes(checked) {
    document.querySelectorAll('.scopes-grid input[type="checkbox"]').forEach(cb => cb.checked = checked);
}

// Close modal on overlay click
document.getElementById('modalCriar')?.addEventListener('click', function(e) {
    if (e.target === this) this.classList.remove('active');
});

// Close modal on Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.getElementById('modalCriar')?.classList.remove('active');
    }
});
</script>

<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
