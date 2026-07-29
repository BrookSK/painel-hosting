<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;

$pageTitle = I18n::t('migracao_wp_cli.nova_migracao');
require __DIR__ . '/../_partials/layout-cliente-inicio.php';
?>
<div class="page-title"><?php echo View::e(I18n::t('migracao_wp_cli.nova_migracao')); ?></div>
<div class="page-subtitle"><?php echo View::e(I18n::t('migracao_wp_cli.form_subtitulo')); ?></div>

<?php if (!empty($erro)): ?>
<div class="alert alert-danger" style="margin-bottom:16px;padding:12px;border-radius:8px;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#ef4444;">
  <?php echo View::e($erro); ?>
</div>
<?php endif; ?>

<form method="POST" action="/cliente/migracoes-wp/salvar" class="card-new" style="max-width:750px;">
  <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />

  <!-- VPS de destino -->
  <h3 style="margin:0 0 16px;font-size:16px;color:var(--text);">
    <svg width="18" height="18" viewBox="0 0 20 20" fill="none" style="vertical-align:middle;margin-right:6px;"><rect x="2" y="5" width="16" height="4" rx="1.5" stroke="currentColor" stroke-width="1.6"/><rect x="2" y="11" width="16" height="4" rx="1.5" stroke="currentColor" stroke-width="1.6"/><circle cx="15" cy="7" r="1" fill="currentColor"/><circle cx="15" cy="13" r="1" fill="currentColor"/></svg>
    <?php echo View::e(I18n::t('migracao_wp_cli.secao_destino')); ?>
  </h3>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:20px;">
    <div>
      <label class="form-label"><?php echo View::e(I18n::t('migracao_wp_cli.vps_destino')); ?> *</label>
      <select name="vps_id" required class="form-control">
        <option value="">— <?php echo View::e(I18n::t('migracao_wp_cli.selecione')); ?> —</option>
        <?php foreach (($vpsList??[]) as $v): ?>
        <option value="<?php echo (int)$v['id']; ?>">
          VPS #<?php echo (int)$v['id']; ?> — <?php echo View::e($v['server_name']??''); ?> (<?php echo (int)$v['cpu']; ?> vCPU / <?php echo (int)$v['ram']; ?>MB)
        </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="form-label"><?php echo View::e(I18n::t('migracao_wp_cli.dominio_destino')); ?></label>
      <input type="text" name="dest_domain" class="form-control" placeholder="meusite.com.br">
      <small class="form-text"><?php echo View::e(I18n::t('migracao_wp_cli.dominio_hint')); ?></small>
    </div>
  </div>

  <hr style="border:none;border-top:1px solid var(--border);margin:20px 0;">

  <!-- Servidor de origem — SSH -->
  <h3 style="margin:0 0 16px;font-size:16px;color:var(--text);">
    <svg width="18" height="18" viewBox="0 0 20 20" fill="none" style="vertical-align:middle;margin-right:6px;"><path d="M4 4l4 4-4 4M10 16h6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
    <?php echo View::e(I18n::t('migracao_wp_cli.secao_ssh')); ?>
  </h3>

  <div style="display:grid;grid-template-columns:2fr 1fr 1fr;gap:12px;margin-bottom:12px;">
    <div>
      <label class="form-label"><?php echo View::e(I18n::t('migracao_wp_cli.host')); ?> *</label>
      <input type="text" name="source_host" required class="form-control" placeholder="192.168.1.100">
    </div>
    <div>
      <label class="form-label"><?php echo View::e(I18n::t('migracao_wp_cli.porta_ssh')); ?></label>
      <input type="number" name="source_port" class="form-control" value="22" min="1" max="65535">
    </div>
    <div>
      <label class="form-label"><?php echo View::e(I18n::t('migracao_wp_cli.usuario')); ?></label>
      <input type="text" name="source_user" class="form-control" value="root">
    </div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
    <div>
      <label class="form-label"><?php echo View::e(I18n::t('migracao_wp_cli.senha_ssh')); ?> *</label>
      <input type="password" name="source_password" required class="form-control" autocomplete="new-password">
    </div>
    <div>
      <label class="form-label"><?php echo View::e(I18n::t('migracao_wp_cli.caminho_wp')); ?> *</label>
      <input type="text" name="source_wp_path" required class="form-control" placeholder="/www/wwwroot/meusite.com.br">
      <small class="form-text"><?php echo View::e(I18n::t('migracao_wp_cli.caminho_hint')); ?></small>
    </div>
  </div>

  <div style="margin-top:12px;margin-bottom:4px;">
    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:var(--text);">
      <input type="checkbox" name="source_use_sudo" value="1" style="accent-color:var(--accent);width:16px;height:16px;" onchange="document.getElementById('sudoPassCli').style.display=this.checked?'block':'none';" />
      <span><?php echo View::e(I18n::t('migracao_wp_cli.usar_sudo')); ?></span>
    </label>
    <small class="form-text" style="margin-left:24px;"><?php echo View::e(I18n::t('migracao_wp_cli.usar_sudo_hint')); ?></small>
  </div>
  <div id="sudoPassCli" style="display:none;margin-top:8px;max-width:300px;">
    <label class="form-label"><?php echo View::e(I18n::t('migracao_wp_cli.senha_sudo')); ?></label>
    <input type="password" name="source_sudo_password" class="form-control" autocomplete="new-password">
    <small class="form-text"><?php echo View::e(I18n::t('migracao_wp_cli.senha_sudo_hint')); ?></small>
  </div>

  <hr style="border:none;border-top:1px solid var(--border);margin:20px 0;">

  <!-- Banco de dados de origem -->
  <h3 style="margin:0 0 16px;font-size:16px;color:var(--text);">
    <svg width="18" height="18" viewBox="0 0 20 20" fill="none" style="vertical-align:middle;margin-right:6px;"><ellipse cx="10" cy="5" rx="7" ry="3" stroke="currentColor" stroke-width="1.5"/><path d="M3 5v10c0 1.66 3.13 3 7 3s7-1.34 7-3V5" stroke="currentColor" stroke-width="1.5"/><path d="M3 10c0 1.66 3.13 3 7 3s7-1.34 7-3" stroke="currentColor" stroke-width="1.5"/></svg>
    <?php echo View::e(I18n::t('migracao_wp_cli.secao_db')); ?>
  </h3>

  <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:12px;">
    <div>
      <label class="form-label"><?php echo View::e(I18n::t('migracao_wp_cli.db_nome')); ?> *</label>
      <input type="text" name="source_db_name" required class="form-control" placeholder="wordpress_db">
    </div>
    <div>
      <label class="form-label"><?php echo View::e(I18n::t('migracao_wp_cli.db_usuario')); ?></label>
      <input type="text" name="source_db_user" class="form-control" value="root">
    </div>
    <div>
      <label class="form-label"><?php echo View::e(I18n::t('migracao_wp_cli.db_senha')); ?></label>
      <input type="password" name="source_db_password" class="form-control" autocomplete="new-password">
    </div>
  </div>

  <div style="display:grid;grid-template-columns:2fr 1fr;gap:12px;margin-bottom:20px;">
    <div>
      <label class="form-label"><?php echo View::e(I18n::t('migracao_wp_cli.db_host')); ?></label>
      <input type="text" name="source_db_host" class="form-control" value="localhost">
    </div>
    <div>
      <label class="form-label"><?php echo View::e(I18n::t('migracao_wp_cli.db_porta')); ?></label>
      <input type="number" name="source_db_port" class="form-control" value="3306" min="1" max="65535">
    </div>
  </div>

  <div style="display:flex;gap:12px;">
    <button type="submit" class="btn btn-primary"><?php echo View::e(I18n::t('migracao_wp_cli.iniciar')); ?></button>
    <a href="/cliente/migracoes-wp" class="btn btn-secondary"><?php echo View::e(I18n::t('geral.cancelar')); ?></a>
  </div>
</form>

<div class="card-new" style="margin-top:16px;max-width:750px;border-left:3px solid var(--accent);">
  <h4 style="margin:0 0 8px;font-size:14px;"><?php echo View::e(I18n::t('migracao_wp_cli.dica_titulo')); ?></h4>
  <ul style="margin:0;padding-left:20px;font-size:13px;color:var(--text-muted);line-height:1.8;">
    <li><?php echo View::e(I18n::t('migracao_wp_cli.dica_1')); ?></li>
    <li><?php echo View::e(I18n::t('migracao_wp_cli.dica_2')); ?></li>
    <li><?php echo View::e(I18n::t('migracao_wp_cli.dica_3')); ?></li>
    <li><?php echo View::e(I18n::t('migracao_wp_cli.dica_4')); ?></li>
  </ul>
</div>
<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
