<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;

$pageTitle = I18n::t('migracao_wp.nova_migracao');
require __DIR__ . '/../_partials/layout-equipe-inicio.php';
?>
<div class="page-title"><?php echo View::e(I18n::t('migracao_wp.nova_migracao')); ?></div>
<div class="page-subtitle"><?php echo View::e(I18n::t('migracao_wp.form_subtitulo')); ?></div>

<?php if (!empty($erro)): ?>
<div class="erro"><?php echo View::e($erro); ?></div>
<?php endif; ?>

<div class="card-new" style="max-width:860px;">
  <form method="POST" action="/equipe/migracoes-wp/salvar">
    <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />

    <!-- Destino -->
    <h2 class="titulo" style="font-size:15px;margin:0 0 14px;">
      <svg width="18" height="18" viewBox="0 0 20 20" fill="none" style="vertical-align:middle;margin-right:6px;"><rect x="2" y="5" width="16" height="4" rx="1.5" stroke="currentColor" stroke-width="1.6"/><rect x="2" y="11" width="16" height="4" rx="1.5" stroke="currentColor" stroke-width="1.6"/><circle cx="15" cy="7" r="1" fill="currentColor"/><circle cx="15" cy="13" r="1" fill="currentColor"/></svg>
      <?php echo View::e(I18n::t('migracao_wp.secao_destino')); ?>
    </h2>
    <div class="grid">
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;"><?php echo View::e(I18n::t('migracao_wp.cliente')); ?> *</label>
        <select name="client_id" required class="input" id="selCliente">
          <option value="">— Selecione —</option>
          <?php foreach (($clientes??[]) as $c): ?>
          <option value="<?php echo (int)$c['id']; ?>"><?php echo View::e($c['name'] . ' (' . $c['email'] . ')'); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;"><?php echo View::e(I18n::t('migracao_wp.vps_destino')); ?> *</label>
        <select name="vps_id" required class="input" id="selVps">
          <option value="">— Selecione —</option>
          <?php foreach (($vpsList??[]) as $v): ?>
          <option value="<?php echo (int)$v['id']; ?>" data-client="<?php echo (int)$v['client_id']; ?>">VPS #<?php echo (int)$v['id']; ?> — <?php echo View::e($v['server_name']??''); ?> (<?php echo (int)$v['cpu']; ?> vCPU / <?php echo (int)$v['ram']; ?>MB)</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;"><?php echo View::e(I18n::t('migracao_wp.dominio_destino')); ?></label>
        <input class="input" type="text" name="dest_domain" placeholder="site.com.br">
        <p class="texto" style="font-size:12px;margin-top:4px;"><?php echo View::e(I18n::t('migracao_wp.dominio_destino_hint')); ?></p>
      </div>
    </div>

    <!-- Origem SSH -->
    <h2 class="titulo" style="font-size:15px;margin:20px 0 14px;">
      <svg width="18" height="18" viewBox="0 0 20 20" fill="none" style="vertical-align:middle;margin-right:6px;"><path d="M4 4l4 4-4 4M10 16h6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
      <?php echo View::e(I18n::t('migracao_wp.secao_origem_ssh')); ?>
    </h2>
    <div class="grid">
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;"><?php echo View::e(I18n::t('migracao_wp.host_origem')); ?> *</label>
        <input class="input" type="text" name="source_host" required placeholder="192.168.1.100 ou server.exemplo.com">
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;"><?php echo View::e(I18n::t('migracao_wp.porta_ssh')); ?></label>
        <input class="input" type="number" name="source_port" value="22" min="1" max="65535">
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;"><?php echo View::e(I18n::t('migracao_wp.usuario_ssh')); ?></label>
        <input class="input" type="text" name="source_user" value="root">
      </div>
    </div>
    <div class="grid" style="margin-top:12px;">
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;"><?php echo View::e(I18n::t('migracao_wp.senha_ssh')); ?> *</label>
        <input class="input" type="password" name="source_password" required autocomplete="new-password">
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;"><?php echo View::e(I18n::t('migracao_wp.caminho_wp')); ?> *</label>
        <input class="input" type="text" name="source_wp_path" required placeholder="/www/wwwroot/site.com">
        <p class="texto" style="font-size:12px;margin-top:4px;"><?php echo View::e(I18n::t('migracao_wp.caminho_wp_hint')); ?></p>
      </div>
    </div>
    <div style="margin-top:12px;">
      <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;">
        <input type="checkbox" name="source_use_sudo" value="1" style="accent-color:#4F46E5;width:16px;height:16px;" id="chkSudo" onchange="document.getElementById('sudoPassRow').style.display=this.checked?'':'none';" />
        <span>Usar sudo no servidor de origem</span>
      </label>
      <p class="texto" style="font-size:12px;margin-top:4px;">Marque se o usuário SSH não é root e precisa de sudo para acessar os arquivos do WordPress e MySQL.</p>
    </div>
    <div class="grid" style="margin-top:8px;display:none;" id="sudoPassRow">
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Senha do sudo</label>
        <input class="input" type="password" name="source_sudo_password" autocomplete="new-password">
        <p class="texto" style="font-size:12px;margin-top:4px;">Deixe em branco para usar a mesma senha SSH.</p>
      </div>
    </div>

    <!-- Origem Banco de Dados -->
    <h2 class="titulo" style="font-size:15px;margin:20px 0 14px;">
      <svg width="18" height="18" viewBox="0 0 20 20" fill="none" style="vertical-align:middle;margin-right:6px;"><ellipse cx="10" cy="5" rx="7" ry="3" stroke="currentColor" stroke-width="1.5"/><path d="M3 5v10c0 1.66 3.13 3 7 3s7-1.34 7-3V5" stroke="currentColor" stroke-width="1.5"/><path d="M3 10c0 1.66 3.13 3 7 3s7-1.34 7-3" stroke="currentColor" stroke-width="1.5"/></svg>
      <?php echo View::e(I18n::t('migracao_wp.secao_origem_db')); ?>
    </h2>
    <div class="grid">
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;"><?php echo View::e(I18n::t('migracao_wp.db_nome')); ?> *</label>
        <input class="input" type="text" name="source_db_name" required placeholder="wordpress_db">
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;"><?php echo View::e(I18n::t('migracao_wp.db_usuario')); ?></label>
        <input class="input" type="text" name="source_db_user" value="root">
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;"><?php echo View::e(I18n::t('migracao_wp.db_senha')); ?></label>
        <input class="input" type="password" name="source_db_password" autocomplete="new-password">
      </div>
    </div>
    <div class="grid" style="margin-top:12px;">
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;"><?php echo View::e(I18n::t('migracao_wp.db_host')); ?></label>
        <input class="input" type="text" name="source_db_host" value="localhost">
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;"><?php echo View::e(I18n::t('migracao_wp.db_porta')); ?></label>
        <input class="input" type="number" name="source_db_port" value="3306" min="1" max="65535">
      </div>
    </div>

    <div style="margin-top:20px;display:flex;gap:12px;">
      <button type="submit" class="botao"><?php echo View::e(I18n::t('migracao_wp.iniciar_migracao')); ?></button>
      <a href="/equipe/migracoes-wp" class="botao sec"><?php echo View::e(I18n::t('geral.cancelar')); ?></a>
    </div>
  </form>
</div>

<script>
document.getElementById('selCliente').addEventListener('change', function(){
  var cid = this.value;
  var opts = document.querySelectorAll('#selVps option[data-client]');
  opts.forEach(function(o){ o.style.display = (!cid || o.dataset.client === cid) ? '' : 'none'; });
  document.getElementById('selVps').value = '';
});
</script>
<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
