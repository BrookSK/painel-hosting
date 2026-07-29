<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;
use LRV\Core\Csrf;

$pageTitle = I18n::t('migracao_wp_cli.nova_migracao');
require __DIR__ . '/../_partials/layout-cliente-inicio.php';
?>

<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:24px;">
  <div>
    <div class="page-title"><?php echo View::e(I18n::t('migracao_wp_cli.nova_migracao')); ?></div>
    <div class="page-subtitle" style="margin-bottom:0;"><?php echo View::e(I18n::t('migracao_wp_cli.form_subtitulo')); ?></div>
  </div>
  <a href="/cliente/migracoes-wp" class="botao ghost sm">&larr; <?php echo View::e(I18n::t('migracao_wp_cli.voltar')); ?></a>
</div>

<?php if (!empty($erro)): ?>
  <div class="erro"><?php echo View::e($erro); ?></div>
<?php endif; ?>

<div class="card-new" style="max-width:720px;">
  <form method="POST" action="/cliente/migracoes-wp/salvar">
    <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>" />

    <!-- Destino -->
    <h3 style="margin:0 0 14px;font-size:15px;font-weight:700;">
      <svg width="18" height="18" viewBox="0 0 20 20" fill="none" style="vertical-align:middle;margin-right:6px;"><rect x="2" y="5" width="16" height="4" rx="1.5" stroke="currentColor" stroke-width="1.6"/><rect x="2" y="11" width="16" height="4" rx="1.5" stroke="currentColor" stroke-width="1.6"/><circle cx="15" cy="7" r="1" fill="currentColor"/><circle cx="15" cy="13" r="1" fill="currentColor"/></svg>
      <?php echo View::e(I18n::t('migracao_wp_cli.secao_destino')); ?>
    </h3>
    <div class="grid" style="margin-bottom:14px;">
      <div>
        <label style="display:block;font-size:13px;margin-bottom:5px;"><?php echo View::e(I18n::t('migracao_wp_cli.vps_destino')); ?> *</label>
        <select name="vps_id" required class="input">
          <option value=""><?php echo View::e(I18n::t('migracao_wp_cli.selecione')); ?>...</option>
          <?php foreach (($vpsList??[]) as $v): ?>
          <option value="<?php echo (int)$v['id']; ?>">VPS #<?php echo (int)$v['id']; ?> — <?php echo View::e($v['server_name']??''); ?> (<?php echo (int)$v['cpu']; ?> vCPU / <?php echo (int)$v['ram']; ?>MB)</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:5px;"><?php echo View::e(I18n::t('migracao_wp_cli.dominio_destino')); ?></label>
        <input class="input" type="text" name="dest_domain" placeholder="meusite.com.br">
        <p class="texto" style="font-size:12px;margin-top:4px;"><?php echo View::e(I18n::t('migracao_wp_cli.dominio_hint')); ?></p>
      </div>
    </div>

    <!-- Origem SSH -->
    <h3 style="margin:20px 0 14px;font-size:15px;font-weight:700;">
      <svg width="18" height="18" viewBox="0 0 20 20" fill="none" style="vertical-align:middle;margin-right:6px;"><path d="M4 4l4 4-4 4M10 16h6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
      <?php echo View::e(I18n::t('migracao_wp_cli.secao_ssh')); ?>
    </h3>
    <div class="grid" style="margin-bottom:12px;">
      <div>
        <label style="display:block;font-size:13px;margin-bottom:5px;"><?php echo View::e(I18n::t('migracao_wp_cli.host')); ?> *</label>
        <input class="input" type="text" name="source_host" required placeholder="192.168.1.100">
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:5px;"><?php echo View::e(I18n::t('migracao_wp_cli.porta_ssh')); ?></label>
        <input class="input" type="number" name="source_port" value="22" min="1" max="65535">
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:5px;"><?php echo View::e(I18n::t('migracao_wp_cli.usuario')); ?></label>
        <input class="input" type="text" name="source_user" value="root">
      </div>
    </div>
    <div class="grid" style="margin-bottom:12px;">
      <div>
        <label style="display:block;font-size:13px;margin-bottom:5px;"><?php echo View::e(I18n::t('migracao_wp_cli.senha_ssh')); ?> *</label>
        <input class="input" type="password" name="source_password" required autocomplete="new-password">
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:5px;"><?php echo View::e(I18n::t('migracao_wp_cli.caminho_wp')); ?> *</label>
        <input class="input" type="text" name="source_wp_path" required placeholder="/www/wwwroot/meusite.com.br">
        <p class="texto" style="font-size:12px;margin-top:4px;"><?php echo View::e(I18n::t('migracao_wp_cli.caminho_hint')); ?></p>
      </div>
    </div>
    <div style="margin-bottom:4px;">
      <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;">
        <input type="checkbox" name="source_use_sudo" value="1" style="accent-color:#4F46E5;width:16px;height:16px;" onchange="document.getElementById('sudoPassCli').style.display=this.checked?'block':'none';" />
        <span><?php echo View::e(I18n::t('migracao_wp_cli.usar_sudo')); ?></span>
      </label>
      <p class="texto" style="font-size:12px;margin-top:3px;margin-left:24px;"><?php echo View::e(I18n::t('migracao_wp_cli.usar_sudo_hint')); ?></p>
    </div>
    <div id="sudoPassCli" style="display:none;margin-top:8px;max-width:300px;margin-bottom:12px;">
      <label style="display:block;font-size:13px;margin-bottom:5px;"><?php echo View::e(I18n::t('migracao_wp_cli.senha_sudo')); ?></label>
      <input class="input" type="password" name="source_sudo_password" autocomplete="new-password">
      <p class="texto" style="font-size:12px;margin-top:4px;"><?php echo View::e(I18n::t('migracao_wp_cli.senha_sudo_hint')); ?></p>
    </div>

    <!-- Origem Banco de Dados -->
    <h3 style="margin:20px 0 14px;font-size:15px;font-weight:700;">
      <svg width="18" height="18" viewBox="0 0 20 20" fill="none" style="vertical-align:middle;margin-right:6px;"><ellipse cx="10" cy="5" rx="7" ry="3" stroke="currentColor" stroke-width="1.5"/><path d="M3 5v10c0 1.66 3.13 3 7 3s7-1.34 7-3V5" stroke="currentColor" stroke-width="1.5"/><path d="M3 10c0 1.66 3.13 3 7 3s7-1.34 7-3" stroke="currentColor" stroke-width="1.5"/></svg>
      <?php echo View::e(I18n::t('migracao_wp_cli.secao_db')); ?>
    </h3>
    <div class="grid" style="margin-bottom:12px;">
      <div>
        <label style="display:block;font-size:13px;margin-bottom:5px;"><?php echo View::e(I18n::t('migracao_wp_cli.db_nome')); ?> *</label>
        <input class="input" type="text" name="source_db_name" required placeholder="wordpress_db">
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:5px;"><?php echo View::e(I18n::t('migracao_wp_cli.db_usuario')); ?></label>
        <input class="input" type="text" name="source_db_user" value="root">
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:5px;"><?php echo View::e(I18n::t('migracao_wp_cli.db_senha')); ?></label>
        <input class="input" type="password" name="source_db_password" autocomplete="new-password">
      </div>
    </div>
    <div class="grid" style="margin-bottom:20px;">
      <div>
        <label style="display:block;font-size:13px;margin-bottom:5px;"><?php echo View::e(I18n::t('migracao_wp_cli.db_host')); ?></label>
        <input class="input" type="text" name="source_db_host" value="localhost">
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:5px;"><?php echo View::e(I18n::t('migracao_wp_cli.db_porta')); ?></label>
        <input class="input" type="number" name="source_db_port" value="3306" min="1" max="65535">
      </div>
    </div>

    <div style="display:flex;gap:12px;">
      <button type="submit" class="botao"><?php echo View::e(I18n::t('migracao_wp_cli.iniciar')); ?></button>
      <a href="/cliente/migracoes-wp" class="botao sec"><?php echo View::e(I18n::t('geral.cancelar')); ?></a>
    </div>
  </form>
</div>

<div class="card-new" style="margin-top:16px;max-width:720px;border-left:3px solid var(--accent, #4F46E5);">
  <h4 style="margin:0 0 8px;font-size:14px;"><?php echo View::e(I18n::t('migracao_wp_cli.dica_titulo')); ?></h4>
  <ul style="margin:0;padding-left:20px;font-size:13px;color:var(--text-muted, #64748b);line-height:1.8;">
    <li><?php echo View::e(I18n::t('migracao_wp_cli.dica_1')); ?></li>
    <li><?php echo View::e(I18n::t('migracao_wp_cli.dica_2')); ?></li>
    <li><?php echo View::e(I18n::t('migracao_wp_cli.dica_3')); ?></li>
    <li><?php echo View::e(I18n::t('migracao_wp_cli.dica_4')); ?></li>
  </ul>
</div>
<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
