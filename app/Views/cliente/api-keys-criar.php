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

<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:24px;">
  <div>
    <div class="page-title"><?= I18n::t('api_keys.criar_titulo') ?></div>
    <div class="page-subtitle" style="margin-bottom:0;"><?= I18n::t('api_keys.subtitulo') ?></div>
  </div>
  <a href="/cliente/api-keys" class="botao ghost sm">&larr; <?= I18n::t('geral.voltar') ?></a>
</div>

<?php if ($erro === 'nome_obrigatorio'): ?>
  <div class="erro"><?= I18n::t('api_keys.erro_nome') ?></div>
<?php elseif ($erro === 'escopos_obrigatorio'): ?>
  <div class="erro"><?= I18n::t('api_keys.erro_escopos') ?></div>
<?php endif; ?>

<div class="card-new" style="max-width:680px;">
  <form method="post" action="/cliente/api-keys/criar">
    <input type="hidden" name="_csrf" value="<?= View::e(Csrf::token()) ?>" />

    <div style="margin-bottom:14px;">
      <label style="display:block;font-size:13px;margin-bottom:5px;"><?= I18n::t('api_keys.campo_nome') ?> *</label>
      <input class="input" type="text" name="nome" required maxlength="100" placeholder="<?= I18n::t('api_keys.campo_nome_placeholder') ?>" />
    </div>

    <div style="margin-bottom:14px;">
      <label style="display:block;font-size:13px;margin-bottom:5px;"><?= I18n::t('api_keys.campo_descricao') ?></label>
      <textarea class="input" name="descricao" maxlength="255" placeholder="<?= I18n::t('api_keys.campo_descricao_placeholder') ?>" style="min-height:60px;resize:vertical;"></textarea>
    </div>

    <div class="grid" style="margin-bottom:14px;">
      <div>
        <label style="display:block;font-size:13px;margin-bottom:5px;"><?= I18n::t('api_keys.campo_ambiente') ?></label>
        <select class="input" name="ambiente">
          <option value="production"><?= I18n::t('api_keys.env_production') ?></option>
          <option value="sandbox"><?= I18n::t('api_keys.env_sandbox') ?></option>
        </select>
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:5px;"><?= I18n::t('api_keys.campo_rate_limit') ?></label>
        <input class="input" type="number" name="rate_limit" value="60" min="1" max="1000" />
      </div>
    </div>

    <div style="margin-bottom:14px;">
      <label style="display:block;font-size:13px;margin-bottom:5px;"><?= I18n::t('api_keys.campo_expiracao') ?></label>
      <input class="input" type="date" name="expira_em" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" />
      <p style="font-size:12px;color:#64748b;margin-top:4px;">Deixe vazio para chave sem expiração.</p>
    </div>

    <div style="margin-bottom:14px;">
      <label style="display:block;font-size:13px;margin-bottom:5px;">Scopes *</label>
      <p style="font-size:12px;color:#64748b;margin-bottom:10px;">Selecione as permissões que esta chave terá acesso.</p>
      <div style="margin-bottom:10px;display:flex;gap:8px;">
        <button type="button" onclick="toggleAllScopes(true)" class="botao ghost sm"><?= I18n::t('api_keys.selecionar_todos') ?></button>
        <button type="button" onclick="toggleAllScopes(false)" class="botao ghost sm"><?= I18n::t('api_keys.desmarcar_todos') ?></button>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;">
        <?php foreach ($escoposDisponiveis as $escopo => $label): ?>
        <label style="display:flex;align-items:center;gap:8px;padding:8px 10px;background:rgba(255,255,255,.03);border:1px solid var(--border,#334155);border-radius:8px;cursor:pointer;font-size:13px;transition:border-color .15s;">
          <input type="checkbox" name="escopos[]" value="<?= View::e($escopo) ?>" style="accent-color:#6366f1;width:15px;height:15px;" />
          <span><?= View::e($label) ?></span>
        </label>
        <?php endforeach; ?>
      </div>
    </div>

    <div style="display:flex;gap:12px;margin-top:24px;padding-top:16px;border-top:1px solid var(--border,#1e293b);">
      <a href="/cliente/api-keys" class="botao ghost"><?= I18n::t('geral.cancelar') ?></a>
      <button type="submit" class="botao"><?= I18n::t('api_keys.criar_btn') ?></button>
    </div>
  </form>
</div>

<script>
function toggleAllScopes(checked) {
    document.querySelectorAll('input[name="escopos[]"]').forEach(cb => cb.checked = checked);
}
</script>

<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
