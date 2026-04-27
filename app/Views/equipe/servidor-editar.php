<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;

$id       = $servidor['id'] ?? null;
$authType = (string)($servidor['ssh_auth_type'] ?? 'password');
$setupSt  = (string)($servidor['setup_status'] ?? 'pending');

$pageTitle = $id ? I18n::t('eq_srv_edit.titulo_editar') : I18n::t('eq_srv_edit.titulo_novo');
require __DIR__ . '/../_partials/layout-equipe-inicio.php';
?>

<div class="page-title"><?php echo $id ? View::e(I18n::t('eq_srv_edit.titulo_editar')) : View::e(I18n::t('eq_srv_edit.titulo_novo')); ?></div>
<div class="page-subtitle"><?php echo View::e(I18n::t('equipe.servidores')); ?> / <?php echo $id ? View::e(I18n::t('geral.editar')) : View::e(I18n::t('geral.novo')); ?></div>

<div class="card-new" style="max-width:920px;">

  <?php if (!empty($erro)): ?>
    <div class="erro" style="white-space:pre-wrap;"><?php echo View::e((string)$erro); ?></div>
  <?php endif; ?>
  <?php if (!empty($mensagem_ok)): ?>
    <div class="sucesso"><?php echo View::e((string)$mensagem_ok); ?></div>
  <?php endif; ?>

  <?php if (array_key_exists('is_online', (array)$servidor)): ?>
    <div class="card-new" style="margin:0 0 12px 0;">
      <div class="texto" style="margin:0 0 6px 0;"><strong><?php echo View::e(I18n::t('eq_srv_edit.conectividade')); ?></strong></div>
      <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
        <?php
          if ((int)($servidor['is_online'] ?? 0) === 1) echo '<span class="badge-new badge-green">Online</span>';
          else echo '<span class="badge-new badge-red">Offline</span>';
          if (!empty($servidor['last_check_at'])) echo '<code style="font-size:12px;">' . View::e((string)$servidor['last_check_at']) . '</code>';
        ?>
      </div>
      <?php if (!empty($servidor['last_error'])): ?>
        <pre style="white-space:pre-wrap;background:#0b1020;color:#e5e7eb;padding:10px;border-radius:12px;overflow:auto;margin-top:8px;font-size:12px;"><?php echo View::e((string)$servidor['last_error']); ?></pre>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <!-- Ações rápidas -->
  <div class="card-new" style="margin:0 0 12px 0;">
    <div class="texto" style="margin:0 0 10px 0;"><strong><?php echo View::e(I18n::t('eq_srv_edit.acoes')); ?></strong></div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
      <button class="botao" type="submit" form="form-servidor" formaction="/equipe/servidores/testar-conexao"><?php echo View::e(I18n::t('eq_srv_edit.testar_ssh')); ?></button>
      <?php if (!empty($servidor['id'])): ?>
        <?php
          $retomar   = $setupSt === 'error' ? 'true' : 'false';
          $btnLabel  = $setupSt === 'error' ? I18n::t('eq_srv_edit.continuar_setup') : ($setupSt === 'ready' ? I18n::t('eq_srv_edit.re_inicializar') : I18n::t('eq_srv_edit.inicializar'));
          $btnColor  = $setupSt === 'error' ? 'background:#f59e0b;' : '';
        ?>
        <button class="botao" type="button" style="<?php echo $btnColor; ?>"
          onclick="abrirSetupEditar(<?php echo (int)$servidor['id']; ?>,<?php echo View::e(json_encode((string)($servidor['hostname']??''))); ?>,<?php echo $retomar; ?>)">
          <?php echo View::e($btnLabel); ?>
        </button>
      <?php endif; ?>
    </div>
  </div>

  <form id="form-servidor" method="post" action="/equipe/servidores/salvar" enctype="multipart/form-data">
    <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
    <input type="hidden" name="id" value="<?php echo View::e((string)($servidor['id'] ?? '')); ?>" />

    <!-- Identificação -->
    <div class="grid">
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;"><?php echo View::e(I18n::t('eq_srv_edit.hostname')); ?></label>
        <input class="input" type="text" name="hostname" value="<?php echo View::e((string)($servidor['hostname'] ?? '')); ?>" placeholder="ex: node-01.lrvweb.com" />
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;"><?php echo View::e(I18n::t('eq_srv_edit.status')); ?></label>
        <select class="input" name="status">
          <option value="active"      <?php echo ($servidor['status'] ?? '') === 'active'      ? 'selected' : ''; ?>><?php echo View::e(I18n::t('eq_srv_edit.ativo')); ?></option>
          <option value="maintenance" <?php echo ($servidor['status'] ?? '') === 'maintenance' ? 'selected' : ''; ?>><?php echo View::e(I18n::t('eq_srv_edit.manutencao')); ?></option>
          <option value="inactive"    <?php echo ($servidor['status'] ?? '') === 'inactive'    ? 'selected' : ''; ?>><?php echo View::e(I18n::t('eq_srv_edit.inativo')); ?></option>
        </select>
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Função</label>
        <?php $srvRole = (string)($servidor['role'] ?? 'vps'); ?>
        <select class="input" name="role" id="srv-role" onchange="toggleEmailTutorial()">
          <option value="vps" <?php echo $srvRole === 'vps' ? 'selected' : ''; ?>>🖥️ VPS (clientes)</option>
          <option value="email" <?php echo $srvRole === 'email' ? 'selected' : ''; ?>>📧 E-mail (Mailcow)</option>
        </select>
        <p class="texto" style="font-size:12px;margin-top:4px;">Servidores de e-mail não recebem VPS de clientes.</p>
      </div>
    </div>

    <div style="margin-top:12px;">
      <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;">
        <input type="checkbox" name="is_test" value="1" <?php echo !empty($servidor['is_test']) ? 'checked' : ''; ?> style="accent-color:#f59e0b;width:16px;height:16px;" />
        <span>🧪 Servidor de teste</span>
      </label>
      <p class="texto" style="font-size:12px;margin-top:4px;">Servidores de teste só são usados por clientes marcados como "tester". Clientes normais nunca recebem VPS neste servidor.</p>
    </div>

    <div style="margin-top:8px;">
      <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;">
        <input type="checkbox" name="is_managed_server" value="1" <?php echo !empty($servidor['is_managed_server']) ? 'checked' : ''; ?> style="accent-color:#0ea5e9;width:16px;height:16px;" />
        <span>🔧 Servidor para clientes gerenciados</span>
      </label>
      <p class="texto" style="font-size:12px;margin-top:4px;">VPS de clientes gerenciados são provisionadas neste servidor sem limites de recursos (overselling). Os recursos do plano aparecem para o cliente, mas o container não tem caps.</p>
    </div>

    <div style="margin-top:14px;">
      <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;">📂 Caminho base dos volumes (storage)</label>
      <input class="input" type="text" name="volume_base_path" value="<?php echo View::e((string)($servidor['volume_base_path'] ?? '')); ?>" placeholder="/vps" />
      <p class="texto" style="font-size:12px;margin-top:4px;">Diretório onde os dados das VPS serão armazenados neste servidor. Deixe vazio para usar o padrão global (<code>infra.volume_base</code>, normalmente <code>/vps</code>). Útil quando o servidor tem discos/partições separados (ex: <code>/desenvolvimento</code>, <code>/mnt/ssd2</code>).</p>
    </div>

    <div style="margin-top:14px;">
      <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;">🌐 Caminho dos vhosts Nginx</label>
      <input class="input" type="text" name="nginx_vhost_path" value="<?php echo View::e((string)($servidor['nginx_vhost_path'] ?? '')); ?>" placeholder="/etc/nginx/sites-available/lrv" />
      <p class="texto" style="font-size:12px;margin-top:4px;">Diretório onde os vhosts Nginx são criados. Deixe vazio para o padrão (<code>/etc/nginx/sites-available/lrv</code>). Para servidores com aaPanel, use <code>/www/server/panel/vhost/nginx</code>.</p>
    </div>

    <div style="margin-top:14px;">
      <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;">🔄 Comando de reload do Nginx</label>
      <input class="input" type="text" name="nginx_reload_cmd" value="<?php echo View::e((string)($servidor['nginx_reload_cmd'] ?? '')); ?>" placeholder="systemctl reload nginx" />
      <p class="texto" style="font-size:12px;margin-top:4px;">Comando para recarregar o Nginx após criar/remover vhosts. Padrão: <code>systemctl reload nginx</code>. Para aaPanel: <code>/etc/init.d/nginx reload</code>.</p>
    </div>

    <div style="margin-top:14px;">
      <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;">🐬 URL do phpMyAdmin</label>
      <input class="input" type="text" name="phpmyadmin_url" value="<?php echo View::e((string)($servidor['phpmyadmin_url'] ?? '')); ?>" placeholder="http://ip:888/phpmyadmin_xxx/index.php" />
      <p class="texto" style="font-size:12px;margin-top:4px;">URL do phpMyAdmin deste servidor. Para aaPanel, copie a URL do phpMyAdmin do painel (ex: <code>http://ip:888/phpmyadmin_xxx/</code>). Quando preenchida, o botão "phpMyAdmin" aparece para os clientes na tela de bancos de dados.</p>
    </div>

    <div class="grid" style="margin-top:12px;">
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;"><?php echo View::e(I18n::t('eq_srv_edit.ip')); ?></label>
        <input class="input" type="text" name="ip_address" value="<?php echo View::e((string)($servidor['ip_address'] ?? '')); ?>" placeholder="ex: 192.168.1.10" />
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;"><?php echo View::e(I18n::t('eq_srv_edit.porta_ssh')); ?></label>
        <input class="input" type="number" name="ssh_port" value="<?php echo View::e((string)($servidor['ssh_port'] ?? '22')); ?>" min="1" max="65535" />
      </div>
    </div>

    <!-- Autenticação SSH -->
    <div class="card-new" style="margin:14px 0 0 0;">
      <div class="texto" style="margin:0 0 12px 0;"><strong><?php echo View::e(I18n::t('eq_srv_edit.auth_ssh')); ?></strong></div>

      <div style="display:flex;gap:16px;margin-bottom:14px;">
        <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:14px;">
          <input type="radio" name="ssh_auth_type" value="password"
            <?php echo $authType === 'password' ? 'checked' : ''; ?>
            onchange="toggleAuthType('password')" />
          <?php echo View::e(I18n::t('eq_srv_edit.usuario_senha')); ?>
        </label>
        <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:14px;">
          <input type="radio" name="ssh_auth_type" value="key"
            <?php echo $authType === 'key' ? 'checked' : ''; ?>
            onchange="toggleAuthType('key')" />
          <?php echo View::e(I18n::t('eq_srv_edit.chave_ssh')); ?>
        </label>
      </div>

      <div class="grid">
        <div>
          <label style="display:block;font-size:13px;margin-bottom:6px;"><?php echo View::e(I18n::t('eq_srv_edit.usuario_ssh')); ?></label>
          <input class="input" type="text" name="ssh_user" value="<?php echo View::e((string)($servidor['ssh_user'] ?? 'root')); ?>" />
        </div>

        <!-- Senha -->
        <div id="field-password" style="<?php echo $authType !== 'password' ? 'display:none;' : ''; ?>">
          <label style="display:block;font-size:13px;margin-bottom:6px;">
            <?php echo View::e(I18n::t('eq_srv_edit.senha_ssh')); ?><?php echo $id ? ' <span style="opacity:.6;font-weight:400;">' . View::e(I18n::t('eq_srv_edit.manter_branco')) . '</span>' : ''; ?>
          </label>
          <input class="input" type="password" name="ssh_password" value="" autocomplete="new-password" />
        </div>

        <!-- Chave -->
        <div id="field-key" style="<?php echo $authType !== 'key' ? 'display:none;' : ''; ?>">
          <label style="display:block;font-size:13px;margin-bottom:6px;"><?php echo View::e(I18n::t('eq_srv_edit.upload_chave')); ?></label>
          <?php if (!empty($servidor['ssh_key_id'])): ?>
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;padding:8px 12px;background:#f0f4ff;border-radius:8px;font-size:13px;">
              <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color:#4F46E5;flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
              <span><?php echo View::e(I18n::t('eq_srv_edit.chave_configurada')); ?>: <code><?php echo View::e((string)$servidor['ssh_key_id']); ?></code></span>
            </div>
          <?php endif; ?>
          <input class="input" type="file" name="ssh_key_file" accept=".pem,.key,.pub,.ppk" style="padding:8px;" />
          <p class="texto" style="font-size:12px;margin-top:6px;"><?php echo View::e(I18n::t('eq_srv_edit.hint_upload_chave')); ?></p>
          <?php if (!empty($servidor['ssh_key_id'])): ?>
            <p class="texto" style="font-size:12px;margin-top:4px;opacity:.7;"><?php echo View::e(I18n::t('eq_srv_edit.hint_substituir_chave')); ?></p>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Sudo -->
    <div class="card-new" style="margin:12px 0 0 0;">
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px;">
          <input type="checkbox" name="use_sudo" value="1" id="chk-sudo"
            <?php echo !empty($servidor['use_sudo']) ? 'checked' : ''; ?>
            onchange="toggleSudo(this.checked)" />
          <strong><?php echo View::e(I18n::t('eq_srv_edit.usar_sudo')); ?></strong>
        </label>
        <span class="texto" style="margin:0;font-size:13px;opacity:.8;"><?php echo View::e(I18n::t('eq_srv_edit.hint_sudo')); ?></span>
      </div>

      <div id="sudo-fields" style="<?php echo empty($servidor['use_sudo']) ? 'display:none;' : ''; ?>">
        <label style="display:block;font-size:13px;margin-bottom:6px;">
          <?php echo View::e(I18n::t('eq_srv_edit.senha_sudo')); ?><?php echo $id ? ' <span style="opacity:.6;font-weight:400;">' . View::e(I18n::t('eq_srv_edit.hint_sudo_branco')) . '</span>' : ''; ?>
        </label>
        <input class="input" type="password" name="sudo_password" value="" autocomplete="new-password"
          placeholder="" style="max-width:400px;" />
        <p class="texto" style="font-size:12px;margin-top:6px;opacity:.8;">
          <?php echo View::e(I18n::t('eq_srv_edit.hint_nopasswd')); ?>
        </p>
      </div>
    </div>
    <div class="card-new" style="margin:12px 0 0 0;">
      <div class="texto" style="margin:0 0 8px 0;"><strong><?php echo View::e(I18n::t('eq_srv_edit.terminal_seguro')); ?></strong></div>
      <p class="texto" style="margin:0 0 10px 0;font-size:13px;opacity:.9;"><?php echo View::e(I18n::t('eq_srv_edit.terminal_desc')); ?> <code>ForceCommand</code>.</p>
      <div class="grid">
        <div>
          <label style="display:block;font-size:13px;margin-bottom:6px;"><?php echo View::e(I18n::t('eq_srv_edit.usuario_terminal')); ?></label>
          <input class="input" type="text" name="terminal_ssh_user" value="<?php echo View::e((string)($servidor['terminal_ssh_user'] ?? 'lrv-terminal')); ?>" />
        </div>
        <div>
          <label style="display:block;font-size:13px;margin-bottom:6px;"><?php echo View::e(I18n::t('eq_srv_edit.upload_chave_terminal')); ?></label>
          <?php if (!empty($servidor['terminal_ssh_key_id'])): ?>
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;padding:8px 12px;background:#f0f4ff;border-radius:8px;font-size:13px;">
              <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color:#4F46E5;flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
              <span><?php echo View::e(I18n::t('eq_srv_edit.chave_configurada')); ?>: <code><?php echo View::e((string)$servidor['terminal_ssh_key_id']); ?></code></span>
            </div>
          <?php endif; ?>
          <input class="input" type="file" name="terminal_ssh_key_file" accept=".pem,.key,.pub,.ppk" style="padding:8px;" />
          <p class="texto" style="font-size:12px;margin-top:6px;"><?php echo View::e(I18n::t('eq_srv_edit.hint_upload_terminal')); ?></p>
        </div>
      </div>
      <?php if (!empty($servidor['id'])): ?>
        <div style="margin-top:10px;">
          <a class="botao" href="/equipe/servidores/terminal-seguro?id=<?php echo (int)$servidor['id']; ?>"><?php echo View::e(I18n::t('eq_srv_edit.passo_a_passo')); ?></a>
        </div>
      <?php endif; ?>
    </div>

    <!-- Capacidade -->
    <div class="grid" style="margin-top:12px;">
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;"><?php echo View::e(I18n::t('eq_srv_edit.cpu_total')); ?></label>
        <input class="input" type="number" name="cpu_total" value="<?php echo View::e((string)($servidor['cpu_total'] ?? '')); ?>" min="1" />
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;"><?php echo View::e(I18n::t('eq_srv_edit.ram_total')); ?></label>
        <input class="input" type="number" name="ram_total" value="<?php echo View::e((string)($servidor['ram_total'] ?? '')); ?>" min="256" />
        <p class="texto" style="font-size:12px;margin-top:6px;">Ex: 65536 = 64 GB</p>
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;"><?php echo View::e(I18n::t('eq_srv_edit.storage_total')); ?></label>
        <input class="input" type="number" name="storage_total" value="<?php echo View::e((string)($servidor['storage_total'] ?? '')); ?>" min="1024" />
        <p class="texto" style="font-size:12px;margin-top:6px;">Ex: 1024000 = 1 TB</p>
      </div>
    </div>

    <div style="margin-top:16px;">
      <button class="botao" type="submit"><?php echo View::e(I18n::t('eq_srv_edit.salvar')); ?></button>
    </div>
  </form>
</div>

<!-- Tutorial Mailcow (visível quando role=email) -->
<div id="email-tutorial" class="card-new" style="margin-top:16px;max-width:920px;display:<?php echo $srvRole === 'email' ? 'block' : 'none'; ?>;">
  <div class="card-new-title" style="margin-bottom:8px;">📧 Configuração do servidor de e-mail (Mailcow)</div>
  <p class="texto" style="margin:0 0 12px;font-size:13px;">Siga estes passos para instalar o Mailcow neste servidor:</p>
  <ol style="padding-left:18px;font-size:13px;color:#475569;line-height:2.2;">
    <li>Acesse o servidor via SSH como root</li>
    <li>Instale o Docker: <code>curl -fsSL https://get.docker.com | sh</code></li>
    <li>Clone o Mailcow: <code>cd /opt && git clone https://github.com/mailcow/mailcow-dockerized && cd mailcow-dockerized</code></li>
    <li>Gere a config: <code>cd /opt/mailcow-dockerized && ./generate_config.sh</code> — informe o hostname (ex: <code>correio.seudominio.com</code>), timezone <code>America/Sao_Paulo</code>, branch <code>1</code> (master)</li>
    <li>Suba os containers: <code>cd /opt/mailcow-dockerized && docker compose pull && docker compose up -d</code></li>
    <li>Acesse <code>https://correio.seudominio.com</code> — login padrão: <code>admin</code> / <code>moohoo</code></li>
    <li>Troque a senha do admin imediatamente</li>
    <li>Vá em Sistema → Configuração → Acesso → API: ative a API Read-Write e copie a chave</li>
    <li>No painel LRV em <a href="/equipe/configuracoes">Configurações</a>, preencha URL do Mailcow, API Key e URL do Webmail</li>
  </ol>
  <div style="background:#fef3c7;border:1px solid #fde68a;color:#92400e;padding:10px 12px;border-radius:8px;font-size:12px;margin-top:10px;">
    Portas necessárias: 25, 80, 110, 143, 443, 465, 587, 993, 995. Certifique-se de que não há outros serviços usando essas portas.
  </div>
</div>

<?php if (!empty($servidor['id']) && !empty($passos_detalhados)): ?>
<!-- Inicialização parcial (passo a passo) -->
<div class="card-new" style="margin-top:16px;max-width:920px;">
  <div class="card-new-title" style="margin-bottom:6px;">Inicialização parcial</div>
  <p class="texto" style="margin:0 0 14px;font-size:13px;">Execute cada passo individualmente. Ideal para servidores que já têm serviços rodando.</p>

  <div style="display:flex;flex-direction:column;gap:8px;" id="passos-parciais">
    <?php foreach ($passos_detalhados as $i => $p):
      $pNome = (string)($p['name'] ?? '');
      $pStatus = (string)($p['status'] ?? 'pending');
      $pEssencial = !empty($p['essencial']);
      $pRisco = (string)($p['risco'] ?? 'nenhum');
      $pDesc = (string)($p['descricao'] ?? '');
      $riscoColor = $pRisco === 'alto' ? '#ef4444' : ($pRisco === 'baixo' ? '#f59e0b' : '#10b981');
      $riscoBg = $pRisco === 'alto' ? '#fef2f2' : ($pRisco === 'baixo' ? '#fffbeb' : '#f0fdf4');
      $riscoLabel = $pRisco === 'alto' ? 'Risco alto' : ($pRisco === 'baixo' ? 'Risco baixo' : 'Sem risco');
      $statusIcon = $pStatus === 'done' ? '✔' : ($pStatus === 'error' ? '✘' : '○');
      $statusColor = $pStatus === 'done' ? '#10b981' : ($pStatus === 'error' ? '#ef4444' : '#94a3b8');
    ?>
    <div style="border:1px solid #e2e8f0;border-radius:10px;padding:12px 14px;display:flex;align-items:center;gap:12px;flex-wrap:wrap;" id="passo-<?php echo $i; ?>">
      <span style="font-size:16px;color:<?php echo $statusColor; ?>;flex-shrink:0;width:20px;text-align:center;" id="passo-icon-<?php echo $i; ?>"><?php echo $statusIcon; ?></span>
      <div style="flex:1;min-width:200px;">
        <div style="font-size:13px;font-weight:600;color:#1e293b;">
          <?php echo View::e($pNome); ?>
          <?php if ($pEssencial): ?><span style="font-size:10px;padding:1px 6px;border-radius:99px;background:#e0e7ff;color:#3730a3;margin-left:6px;">Essencial</span><?php endif; ?>
          <span style="font-size:10px;padding:1px 6px;border-radius:99px;background:<?php echo $riscoBg; ?>;color:<?php echo $riscoColor; ?>;margin-left:4px;"><?php echo $riscoLabel; ?></span>
        </div>
        <div style="font-size:12px;color:#64748b;margin-top:2px;"><?php echo $pDesc; ?></div>
      </div>
      <span id="passo-status-<?php echo $i; ?>" style="font-size:12px;color:<?php echo $statusColor; ?>;min-width:60px;text-align:center;">
        <?php echo $pStatus === 'done' ? 'Concluído' : ($pStatus === 'error' ? 'Erro' : 'Pendente'); ?>
      </span>
      <button class="botao sm" type="button" id="passo-btn-<?php echo $i; ?>"
              onclick="executarPassoParcial(<?php echo (int)$servidor['id']; ?>,<?php echo View::e(json_encode($pNome)); ?>,<?php echo $i; ?>)"
              <?php echo $pStatus === 'done' ? 'disabled style="opacity:.5;"' : ''; ?>>
        <?php echo $pStatus === 'done' ? '✓' : 'Executar'; ?>
      </button>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<!-- Modal de inicialização -->
<div id="modal-setup" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:9000;align-items:center;justify-content:center;">
  <div style="background:#fff;border-radius:16px;padding:28px;width:min(700px,96vw);max-height:92vh;display:flex;flex-direction:column;gap:14px;box-shadow:0 20px 60px rgba(0,0,0,.3);">
    <div style="display:flex;justify-content:space-between;align-items:center;">
      <strong id="modal-setup-titulo" style="font-size:16px;">Inicializar servidor</strong>
      <button id="btn-fechar-x" onclick="fecharSetup()" style="background:none;border:none;font-size:20px;cursor:pointer;color:#64748b;line-height:1;">✕</button>
    </div>
    <p class="texto" style="margin:0;font-size:13px;">Conecta via SSH e prepara o servidor: Docker, rede <code>lrv-net</code> e usuário de terminal.</p>
    <div style="background:#e2e8f0;border-radius:99px;height:8px;overflow:hidden;">
      <div id="setup-progress-bar" style="height:100%;width:0%;background:#4F46E5;border-radius:99px;transition:width .4s ease;"></div>
    </div>
    <div id="setup-progress-txt" style="font-size:12px;color:#64748b;margin-top:-8px;">Aguardando início…</div>
    <div id="setup-log" style="background:#0b1020;color:#e2e8f0;border-radius:12px;padding:14px;font-size:12px;font-family:monospace;min-height:200px;max-height:320px;overflow-y:auto;white-space:pre-wrap;flex:1;"></div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
      <button id="btn-iniciar-setup" class="botao" onclick="executarSetup(false)">Inicializar agora</button>
      <button id="btn-continuar-setup" class="botao" onclick="executarSetup(true)" style="display:none;background:#f59e0b;">Continuar de onde parou</button>
      <button class="botao" onclick="fecharSetup()" style="background:#64748b;">Fechar</button>
    </div>
  </div>
</div>

<script>
function toggleAuthType(tipo) {
    document.getElementById('field-password').style.display = tipo === 'password' ? '' : 'none';
    document.getElementById('field-key').style.display      = tipo === 'key'      ? '' : 'none';
}

function toggleSudo(checked) {
    document.getElementById('sudo-fields').style.display = checked ? '' : 'none';
}

var _setupId = 0, _setupRunning = false;

function abrirSetupEditar(id, hostname, retomar) {
    _setupId = id; _setupRunning = false;
    document.getElementById('modal-setup-titulo').textContent = 'Inicializar: ' + hostname;
    document.getElementById('setup-log').textContent = retomar ? 'Modo: continuar de onde parou.\n' : 'Clique para iniciar.\n';
    document.getElementById('setup-progress-bar').style.width = '0%';
    document.getElementById('setup-progress-txt').textContent = 'Aguardando início…';
    document.getElementById('btn-iniciar-setup').style.display  = retomar ? 'none' : '';
    document.getElementById('btn-continuar-setup').style.display = retomar ? '' : 'none';
    document.getElementById('btn-fechar-x').style.display = '';
    document.getElementById('modal-setup').style.display = 'flex';
}

function fecharSetup() {
    if (_setupRunning) return;
    document.getElementById('modal-setup').style.display = 'none';
}

function appendLog(msg) {
    var el = document.getElementById('setup-log');
    el.textContent += msg + '\n';
    el.scrollTop = el.scrollHeight;
}

function executarSetup(retomar) {
    if (_setupRunning || !_setupId) return;
    _setupRunning = true;
    document.getElementById('btn-iniciar-setup').disabled = true;
    document.getElementById('btn-continuar-setup').disabled = true;
    document.getElementById('btn-fechar-x').style.display = 'none';
    document.getElementById('setup-log').textContent = '';
    appendLog('▶ ' + (retomar ? 'Retomando' : 'Iniciando') + ' setup #' + _setupId + '…\n');

    var csrfVal = (document.querySelector('meta[name=csrf-token]') || {}).content || '';
    var fd = new FormData();
    fd.append('id', _setupId);
    fd.append('_csrf', csrfVal);
    if (retomar) fd.append('retomar', '1');

    fetch('/equipe/servidores/inicializar', { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data.ok) {
                appendLog('✘ ' + (data.erro || 'Erro ao iniciar setup.'));
                _setupRunning = false;
                document.getElementById('btn-fechar-x').style.display = '';
                document.getElementById('btn-iniciar-setup').disabled = false;
                return;
            }

            var steps = data.steps || [];
            var total = steps.length;
            var idx = 0, concluidos = 0, temErroFatal = false;

            function setP(c, t) {
                var pct = t > 0 ? Math.round(c / t * 100) : 0;
                document.getElementById('setup-progress-bar').style.width = pct + '%';
                document.getElementById('setup-progress-txt').textContent = c + ' / ' + t + ' etapas (' + pct + '%)';
            }
            setP(0, total);

            function proximo() {
                if (idx >= steps.length || temErroFatal) {
                    var fd3 = new FormData();
                    fd3.append('id', _setupId);
                    fd3.append('_csrf', csrfVal);
                    fetch('/equipe/servidores/inicializar-finalizar', { method: 'POST', body: fd3 })
                        .then(function(r) { return r.json(); })
                        .then(function(fin) {
                            _setupRunning = false;
                            document.getElementById('btn-fechar-x').style.display = '';
                            setP(fin.concluidos || concluidos, fin.total || total);
                            if (fin.ok) {
                                appendLog('\n✔ Servidor pronto. Recarregue para ver o status.');
                                document.getElementById('btn-iniciar-setup').style.display = 'none';
                                document.getElementById('btn-continuar-setup').style.display = 'none';
                            } else {
                                appendLog('\n✘ Erros encontrados. Use "Continuar de onde parou".');
                                document.getElementById('btn-iniciar-setup').style.display = 'none';
                                document.getElementById('btn-continuar-setup').style.display = '';
                                document.getElementById('btn-continuar-setup').disabled = false;
                            }
                        })
                        .catch(function(e) {
                            _setupRunning = false;
                            document.getElementById('btn-fechar-x').style.display = '';
                            appendLog('✘ Erro ao finalizar: ' + e.message);
                        });
                    return;
                }

                var s = steps[idx]; idx++;
                if (s.status === 'ok' && retomar) {
                    appendLog('⏭ ' + s.name + ' (já concluído)');
                    concluidos++; setP(concluidos, total); proximo(); return;
                }
                appendLog('⏳ ' + s.name + '…');
                var fd2 = new FormData();
                fd2.append('id', _setupId); fd2.append('step', s.name); fd2.append('_csrf', csrfVal);
                fetch('/equipe/servidores/inicializar-passo', { method: 'POST', body: fd2 })
                    .then(function(r2) { return r2.json(); })
                    .then(function(r) {
                        appendLog((r.status === 'ok' ? '✔' : '✘') + ' ' + s.name);
                        if (r.output && r.output.trim() && r.status !== 'ok') appendLog('    ' + r.output.trim().replace(/\n/g, '\n    '));
                        if (r.skipped || r.ok) concluidos++;
                        else if (r.fatal) temErroFatal = true;
                        setP(concluidos, total); proximo();
                    })
                    .catch(function(e) {
                        appendLog('✘ ' + s.name + ' — ' + e.message);
                        temErroFatal = true; setP(concluidos, total); proximo();
                    });
            }
            proximo();
        })
        .catch(function(e) {
            _setupRunning = false;
            document.getElementById('btn-fechar-x').style.display = '';
            document.getElementById('btn-iniciar-setup').disabled = false;
            document.getElementById('btn-continuar-setup').disabled = false;
            appendLog('✘ Erro: ' + e.message);
        });
}
</script>

<script>
function toggleEmailTutorial() {
  var role = document.getElementById('srv-role');
  var tut = document.getElementById('email-tutorial');
  if (role && tut) tut.style.display = role.value === 'email' ? 'block' : 'none';
}

function executarPassoParcial(serverId, stepName, idx) {
  var btn = document.getElementById('passo-btn-' + idx);
  var icon = document.getElementById('passo-icon-' + idx);
  var status = document.getElementById('passo-status-' + idx);
  btn.disabled = true; btn.textContent = '⏳';
  icon.textContent = '⏳'; icon.style.color = '#f59e0b';
  status.textContent = 'Executando...'; status.style.color = '#f59e0b';

  var csrf = (document.querySelector('meta[name=csrf-token]') || {}).content || '';
  var fd = new FormData();
  fd.append('id', serverId);
  fd.append('step', stepName);

  fetch('/equipe/servidores/inicializar-passo', {
    method: 'POST',
    headers: { 'x-csrf-token': csrf },
    body: fd
  }).then(function(r) { return r.json(); }).then(function(d) {
    if (d.ok) {
      icon.textContent = '✔'; icon.style.color = '#10b981';
      status.textContent = 'Concluído'; status.style.color = '#10b981';
      btn.textContent = '✓'; btn.style.opacity = '.5';
    } else {
      icon.textContent = '✘'; icon.style.color = '#ef4444';
      status.textContent = d.erro ? d.erro.substring(0, 50) : 'Erro'; status.style.color = '#ef4444';
      btn.textContent = 'Tentar'; btn.disabled = false;
    }
  }).catch(function() {
    icon.textContent = '✘'; icon.style.color = '#ef4444';
    status.textContent = 'Erro de rede'; status.style.color = '#ef4444';
    btn.textContent = 'Tentar'; btn.disabled = false;
  });
}
</script>

<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
