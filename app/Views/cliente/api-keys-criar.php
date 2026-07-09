<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\Csrf;
use LRV\Core\I18n;

$erro = (string)($erro ?? '');
$pageTitle = I18n::t('api_keys.criar_titulo');
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
?>

<style>
.form-card { background: var(--card-bg, #16213e); border-radius: 16px; padding: 32px; border: 1px solid var(--border, #1e293b); max-width: 640px; }
.form-row { margin-bottom: 20px; }
.form-row label { display: block; font-size: 12px; font-weight: 700; color: #94a3b8; margin-bottom: 6px; text-transform: uppercase; letter-spacing: .04em; }
.form-row input, .form-row select, .form-row textarea {
    width: 100%; background: #1e293b; border: 1.5px solid #334155; border-radius: 10px;
    padding: 12px 14px; color: #e2e8f0; font-size: 14px; outline: none; transition: border-color .2s, box-shadow .2s;
}
.form-row input:focus, .form-row select:focus, .form-row textarea:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.15); }
.form-row input::placeholder, .form-row textarea::placeholder { color: #475569; }
.form-row textarea { resize: vertical; min-height: 70px; }
.form-row select { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath d='M3 5l3 3 3-3' stroke='%2394a3b8' stroke-width='1.5' fill='none' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 14px center; padding-right: 36px; }
.form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
@media(max-width:600px) { .form-grid-2 { grid-template-columns: 1fr; } }
.scopes-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); gap: 8px; margin-top: 8px; }
.scope-item {
    display: flex; align-items: center; gap: 10px; font-size: 13px; color: #cbd5e1;
    padding: 10px 12px; background: #1e293b; border-radius: 10px;
    border: 1.5px solid #334155; cursor: pointer; transition: all .15s; user-select: none;
}
.scope-item:hover { border-color: #6366f1; }
.scope-item:has(input:checked) { border-color: #6366f1; background: rgba(99,102,241,.08); color: #e2e8f0; }
.scope-item input[type="checkbox"] { accent-color: #6366f1; width: 16px; height: 16px; cursor: pointer; flex-shrink: 0; }
.scope-actions { margin-bottom: 10px; display: flex; gap: 8px; }
.form-actions { display: flex; gap: 12px; margin-top: 28px; padding-top: 20px; border-top: 1px solid #1e293b; }
</style>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
    <div>
        <div class="page-title"><?= I18n::t('api_keys.criar_titulo') ?></div>
        <div class="page-subtitle"><?= I18n::t('api_keys.subtitulo') ?></div>
    </div>
    <a href="/cliente/api-keys" class="botao ghost"><?= I18n::t('geral.voltar') ?></a>
</div>

<?php if ($erro === 'nome_obrigatorio'): ?>
  <div class="erro"><?= I18n::t('api_keys.erro_nome') ?></div>
<?php elseif ($erro === 'escopos_obrigatorio'): ?>
  <div class="erro"><?= I18n::t('api_keys.erro_escopos') ?></div>
<?php endif; ?>

<div class="form-card">
    <form method="post" action="/cliente/api-keys/criar">
        <input type="hidden" name="_csrf" value="<?= View::e(Csrf::token()) ?>" />

        <div class="form-row">
            <label for="ak_nome"><?= I18n::t('api_keys.campo_nome') ?> *</label>
            <input type="text" id="ak_nome" name="nome" required maxlength="100" placeholder="<?= I18n::t('api_keys.campo_nome_placeholder') ?>" />
        </div>

        <div class="form-row">
            <label for="ak_descricao"><?= I18n::t('api_keys.campo_descricao') ?></label>
            <textarea id="ak_descricao" name="descricao" maxlength="255" placeholder="<?= I18n::t('api_keys.campo_descricao_placeholder') ?>"></textarea>
        </div>

        <div class="form-grid-2">
            <div class="form-row">
                <label for="ak_ambiente"><?= I18n::t('api_keys.campo_ambiente') ?></label>
                <select id="ak_ambiente" name="ambiente">
                    <option value="production"><?= I18n::t('api_keys.env_production') ?></option>
                    <option value="sandbox"><?= I18n::t('api_keys.env_sandbox') ?></option>
                </select>
            </div>
            <div class="form-row">
                <label for="ak_rate_limit"><?= I18n::t('api_keys.campo_rate_limit') ?></label>
                <input type="number" id="ak_rate_limit" name="rate_limit" value="60" min="1" max="1000" />
            </div>
        </div>

        <div class="form-row">
            <label for="ak_expira"><?= I18n::t('api_keys.campo_expiracao') ?></label>
            <input type="date" id="ak_expira" name="expira_em" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" />
        </div>

        <div class="form-row">
            <label>Scopes *</label>
            <div class="scope-actions">
                <button type="button" onclick="toggleAllScopes(true)" class="botao ghost sm" style="font-size:11px;"><?= I18n::t('api_keys.selecionar_todos') ?></button>
                <button type="button" onclick="toggleAllScopes(false)" class="botao ghost sm" style="font-size:11px;"><?= I18n::t('api_keys.desmarcar_todos') ?></button>
            </div>
            <div class="scopes-grid">
                <?php foreach ($escoposDisponiveis as $escopo => $label): ?>
                <label class="scope-item">
                    <input type="checkbox" name="escopos[]" value="<?= View::e($escopo) ?>" />
                    <span><?= View::e($label) ?></span>
                </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="form-actions">
            <a href="/cliente/api-keys" class="botao ghost"><?= I18n::t('geral.cancelar') ?></a>
            <button type="submit" class="botao"><?= I18n::t('api_keys.criar_btn') ?></button>
        </div>
    </form>
</div>

<script>
function toggleAllScopes(checked) {
    document.querySelectorAll('.scopes-grid input[type="checkbox"]').forEach(cb => cb.checked = checked);
}
</script>

<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
