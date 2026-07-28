<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;
use LRV\Core\Csrf;

$pageTitle = I18n::t('migracao_wp.nova_migracao');
require __DIR__ . '/../_partials/layout-equipe-inicio.php';
?>
<div class="page-title"><?php echo View::e(I18n::t('migracao_wp.nova_migracao')); ?></div>
<div class="page-subtitle"><?php echo View::e(I18n::t('migracao_wp.form_subtitulo')); ?></div>

<?php if (!empty($erro)): ?>
<div class="alert alert-danger" style="margin-bottom:16px;padding:12px;border-radius:8px;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#ef4444;">
  <?php echo View::e($erro); ?>
</div>
<?php endif; ?>

<form method="POST" action="/equipe/migracoes-wp/salvar" class="card-new" style="max-width:800px;">
  <?php echo Csrf::campo(); ?>

  <!-- Destino -->
  <h3 style="margin:0 0 16px;font-size:16px;color:var(--text);">
    <svg width="18" height="18" viewBox="0 0 20 20" fill="none" style="vertical-align:middle;margin-right:6px;"><rect x="2" y="5" width="16" height="4" rx="1.5" stroke="currentColor" stroke-width="1.6"/><rect x="2" y="11" width="16" height="4" rx="1.5" stroke="currentColor" stroke-width="1.6"/><circle cx="15" cy="7" r="1" fill="currentColor"/><circle cx="15" cy="13" r="1" fill="currentColor"/></svg>
    <?php echo View::e(I18n::t('migracao_wp.secao_destino')); ?>
  </h3>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:20px;">
    <div>
      <label class="form-label"><?php echo View::e(I18n::t('migracao_wp.cliente')); ?> *</label>
      <select name="client_id" required class="form-control" id="selCliente">
        <option value="">— Selecione —</option>
        <?php foreach (($clientes??[]) as $c): ?>
        <option value="<?php echo (int)$c['id']; ?>"><?php echo View::e($c['name'] . ' (' . $c['email'] . ')'); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="form-label"><?php echo View::e(I18n::t('migracao_wp.vps_destino')); ?> *</label>
      <select name="vps_id" required class="form-control" id="selVps">
        <option value="">— Selecione —</option>
        <?php foreach (($vpsList??[]) as $v): ?>
        <option value="<?php echo (int)$v['id']; ?>" data-client="<?php echo (int)$v['client_id']; ?>">
          VPS #<?php echo (int)$v['id']; ?> — <?php echo View::e($v['server_name']??''); ?> (<?php echo (int)$v['cpu']; ?> vCPU / <?php echo (int)$v['ram']; ?>MB)
        </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="form-label"><?php echo View::e(I18n::t('migracao_wp.dominio_destino')); ?></label>
      <input type="text" name="dest_domain" class="form-control" placeholder="site.com.br">
      <small class="form-text"><?php echo View::e(I18n::t('migracao_wp.dominio_destino_hint')); ?></small>
    </div>
  </div>

  <hr style="border:none;border-top:1px solid var(--border);margin:20px 0;">

  <!-- Origem SSH -->
  <h3 style="margin:0 0 16px;font-size:16px;color:var(--text);">
    <svg width="18" height="18" viewBox="0 0 20 20" fill="none" style="vertical-align:middle;margin-right:6px;"><path d="M4 4l4 4-4 4M10 16h6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
    <?php echo View::e(I18n::t('migracao_wp.secao_origem_ssh')); ?>
  </h3>

  <div style="display:grid;grid-template-columns:2fr 1fr 1fr;gap:12px;margin-bottom:12px;">
    <div>
      <label class="form-label"><?php echo View::e(I18n::t('migracao_wp.host_origem')); ?> *</label>
      <input type="text" name="source_host" required class="form-control" placeholder="192.168.1.100 ou server.exemplo.com">
    </div>
    <div>
      <label class="form-label"><?php echo View::e(I18n::t('migracao_wp.porta_ssh')); ?></label>
      <input type="number" name="source_port" class="form-control" value="22" min="1" max="65535">
    </div>
    <div>
      <label class="form-label"><?php echo View::e(I18n::t('migracao_wp.usuario_ssh')); ?></label>
      <input type="text" name="source_user" class="form-control" value="root">
    </div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
    <div>
      <label class="form-label"><?php echo View::e(I18n::t('migracao_wp.senha_ssh')); ?> *</label>
      <input type="password" name="source_password" required class="form-control" autocomplete="new-password">
    </div>
    <div>
      <label class="form-label"><?php echo View::e(I18n::t('migracao_wp.caminho_wp')); ?> *</label>
      <input type="text" name="source_wp_path" required class="form-control" placeholder="/www/wwwroot/site.com">
      <small class="form-text"><?php echo View::e(I18n::t('migracao_wp.caminho_wp_hint')); ?></small>
    </div>
  </div>

  <hr style="border:none;border-top:1px solid var(--border);margin:20px 0;">

  <!-- Origem Banco de Dados -->
  <h3 style="margin:0 0 16px;font-size:16px;color:var(--text);">
    <svg width="18" height="18" viewBox="0 0 20 20" fill="none" style="vertical-align:middle;margin-right:6px;"><ellipse cx="10" cy="5" rx="7" ry="3" stroke="currentColor" stroke-width="1.5"/><path d="M3 5v10c0 1.66 3.13 3 7 3s7-1.34 7-3V5" stroke="currentColor" stroke-width="1.5"/><path d="M3 10c0 1.66 3.13 3 7 3s7-1.34 7-3" stroke="currentColor" stroke-width="1.5"/></svg>
    <?php echo View::e(I18n::t('migracao_wp.secao_origem_db')); ?>
  </h3>

  <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:12px;">
    <div>
      <label class="form-label"><?php echo View::e(I18n::t('migracao_wp.db_nome')); ?> *</label>
      <input type="text" name="source_db_name" required class="form-control" placeholder="wordpress_db">
    </div>
    <div>
      <label class="form-label"><?php echo View::e(I18n::t('migracao_wp.db_usuario')); ?></label>
      <input type="text" name="source_db_user" class="form-control" value="root">
    </div>
    <div>
      <label class="form-label"><?php echo View::e(I18n::t('migracao_wp.db_senha')); ?></label>
      <input type="password" name="source_db_password" class="form-control" autocomplete="new-password">
    </div>
  </div>

  <div style="display:grid;grid-template-columns:2fr 1fr;gap:12px;margin-bottom:20px;">
    <div>
      <label class="form-label"><?php echo View::e(I18n::t('migracao_wp.db_host')); ?></label>
      <input type="text" name="source_db_host" class="form-control" value="localhost">
    </div>
    <div>
      <label class="form-label"><?php echo View::e(I18n::t('migracao_wp.db_porta')); ?></label>
      <input type="number" name="source_db_port" class="form-control" value="3306" min="1" max="65535">
    </div>
  </div>

  <div style="display:flex;gap:12px;">
    <button type="submit" class="btn btn-primary"><?php echo View::e(I18n::t('migracao_wp.iniciar_migracao')); ?></button>
    <a href="/equipe/migracoes-wp" class="btn btn-secondary"><?php echo View::e(I18n::t('geral.cancelar')); ?></a>
  </div>
</form>

<script>
// Filtrar VPS por cliente selecionado
document.getElementById('selCliente').addEventListener('change', function(){
  var cid = this.value;
  var opts = document.querySelectorAll('#selVps option[data-client]');
  opts.forEach(function(o){ o.style.display = (!cid || o.dataset.client === cid) ? '' : 'none'; });
  document.getElementById('selVps').value = '';
});
</script>
<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
