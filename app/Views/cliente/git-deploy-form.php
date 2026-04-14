<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\Csrf;
use LRV\Core\I18n;

$dep = is_array($deployment ?? null) ? $deployment : [];
$isEdit = !empty($dep['id']);
$pageTitle = $isEdit ? 'Editar repositório' : 'Novo repositório Git';
$clienteNome = (string)($cliente['name'] ?? '');
$clienteEmail = (string)($cliente['email'] ?? '');
require __DIR__ . '/../_partials/layout-cliente-inicio.php';
?>

<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:24px;">
  <div>
    <div class="page-title"><?php echo $isEdit ? 'Editar repositório' : 'Novo repositório Git'; ?></div>
    <div class="page-subtitle" style="margin-bottom:0;">Configure o deploy automático via Git</div>
  </div>
  <a href="/cliente/git-deploy" class="botao ghost sm">← Voltar</a>
</div>

<?php if (!empty($erro)): ?>
  <div class="erro"><?php echo View::e((string)$erro); ?></div>
<?php endif; ?>

<div class="card-new" style="max-width:680px;">
  <form method="post" action="/cliente/git-deploy/salvar">
    <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>" />
    <input type="hidden" name="id" value="<?php echo (int)($dep['id'] ?? 0); ?>" />

    <div class="grid" style="margin-bottom:14px;">
      <div>
        <label style="display:block;font-size:13px;margin-bottom:5px;">Nome da integração</label>
        <input class="input" type="text" name="name" id="deployName" value="<?php echo View::e((string)($dep['name'] ?? '')); ?>" placeholder="Meu site" required <?php if (!$isEdit): ?>oninput="var s=this.value.toLowerCase().replace(/[^a-z0-9]/g,'-').replace(/-+/g,'-').replace(/^-|-$/g,'');var p='/var/www/'+(s||'app');document.getElementById('deployPathInput').value=p;document.getElementById('deployPathDisplay').textContent=p;var f=document.getElementById('deployPathField');if(f)f.value=p;"<?php endif; ?> />
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:5px;">VPS</label>
        <select class="input" name="vps_id" required>
          <option value="">Selecione...</option>
          <?php foreach (($vpsList ?? []) as $v): ?>
            <option value="<?php echo (int)$v['id']; ?>" <?php echo ((int)($dep['vps_id'] ?? 0)) === (int)$v['id'] ? 'selected' : ''; ?>>
              VPS #<?php echo (int)$v['id']; ?> — <?php echo (int)$v['cpu']; ?>vCPU / <?php echo round((int)$v['ram']/1024); ?>GB
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div style="margin-bottom:14px;">
      <label style="display:block;font-size:13px;margin-bottom:5px;">URL do repositório</label>
      <input class="input" type="text" name="repo_url" value="<?php echo View::e((string)($dep['repo_url'] ?? '')); ?>" placeholder="https://github.com/usuario/repositorio" required />
      <p style="font-size:12px;color:#64748b;margin-top:4px;">URL HTTPS ou SSH do repositório (público ou privado).</p>
    </div>

    <?php if ($isEdit && !empty($dep['deploy_key_public'])): ?>
    <div style="margin-bottom:14px;background:#f0f9ff;border:1px solid #bae6fd;border-radius:10px;padding:14px;">
      <div style="font-size:13px;font-weight:600;color:#0369a1;margin-bottom:6px;">🔑 Deploy Key (para repositórios privados)</div>
      <p style="font-size:12px;color:#475569;margin-bottom:10px;">Para repositórios privados, copie a chave abaixo e adicione no seu repositório:</p>

      <div style="position:relative;margin-bottom:12px;">
        <textarea id="deployKeyPub" readonly style="width:100%;height:70px;font-family:monospace;font-size:11px;padding:8px;border:1px solid #bae6fd;border-radius:6px;background:#f8fafc;resize:none;box-sizing:border-box;"><?php echo View::e((string)$dep['deploy_key_public']); ?></textarea>
        <button type="button" onclick="document.getElementById('deployKeyPub').select();navigator.clipboard.writeText(document.getElementById('deployKeyPub').value).then(function(){this.textContent='✓ Copiada'}.bind(this))" style="position:absolute;top:6px;right:6px;background:#fff;border:1px solid #bae6fd;border-radius:4px;padding:2px 8px;font-size:11px;cursor:pointer;">Copiar</button>
      </div>

      <div style="background:#fff;border:1px solid #e0f2fe;border-radius:8px;padding:12px;font-size:12px;color:#475569;">
        <div style="font-weight:600;color:#0369a1;margin-bottom:8px;">Como adicionar no GitHub:</div>
        <ol style="margin:0;padding-left:18px;display:flex;flex-direction:column;gap:4px;">
          <li>Acesse seu repositório no GitHub</li>
          <li>Vá em <strong>Settings</strong> (aba no topo do repositório)</li>
          <li>No menu lateral, clique em <strong>Deploy keys</strong></li>
          <li>Clique em <strong>Add deploy key</strong></li>
          <li>Em "Title", coloque um nome (ex: <code>LRV Cloud</code>)</li>
          <li>Em "Key", cole a chave pública copiada acima</li>
          <li>Clique em <strong>Add key</strong></li>
        </ol>

        <div style="font-weight:600;color:#0369a1;margin-top:12px;margin-bottom:8px;">Como adicionar no GitLab:</div>
        <ol style="margin:0;padding-left:18px;display:flex;flex-direction:column;gap:4px;">
          <li>Acesse seu repositório no GitLab</li>
          <li>Vá em <strong>Settings → Repository</strong></li>
          <li>Expanda a seção <strong>Deploy keys</strong></li>
          <li>Cole a chave pública, dê um título e clique em <strong>Add key</strong></li>
        </ol>
      </div>

      <p style="font-size:11px;color:#94a3b8;margin-top:8px;">Repositórios públicos não precisam de deploy key. A URL será convertida automaticamente para SSH durante o deploy.</p>
    </div>
    <?php elseif (!$isEdit): ?>
    <div style="margin-bottom:14px;background:#f0f9ff;border:1px solid #bae6fd;border-radius:10px;padding:14px;">
      <div style="font-size:13px;font-weight:600;color:#0369a1;">🔑 Deploy Key</div>
      <p style="font-size:12px;color:#475569;">Uma deploy key SSH será gerada automaticamente ao criar. Você poderá copiá-la e adicionar no repositório depois.</p>
    </div>
    <?php endif; ?>

    <div class="grid" style="margin-bottom:14px;">
      <div>
        <label style="display:block;font-size:13px;margin-bottom:5px;">Branch</label>
        <input class="input" type="text" name="branch" value="<?php echo View::e((string)($dep['branch'] ?? 'main')); ?>" placeholder="main" />
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:5px;">Subdomínio <span style="font-weight:400;color:#94a3b8;">(opcional)</span></label>
        <select class="input" name="subdomain">
          <option value="">Nenhum (usar domínio temporário)</option>
          <?php
            $subSvc = new \LRV\App\Services\Infra\SubdomainVerificationService();
            $subDisp = $subSvc->listarAtivosDisponiveis(LRV\Core\Auth::clienteId() ?? 0);
            // Incluir o subdomínio atual do deploy (mesmo que esteja em uso por este deploy)
            $currentSub = (string)($dep['subdomain'] ?? '');
            $currentSubFound = false;
            foreach ($subDisp as $sd):
              if ((string)($sd['subdomain'] ?? '') === $currentSub) $currentSubFound = true;
          ?>
            <option value="<?php echo View::e((string)($sd['subdomain'] ?? '')); ?>" <?php echo $currentSub === (string)($sd['subdomain'] ?? '') ? 'selected' : ''; ?>>
              <?php echo View::e((string)($sd['subdomain'] ?? '')); ?>
            </option>
          <?php endforeach; ?>
          <?php if ($currentSub !== '' && !$currentSubFound): ?>
            <option value="<?php echo View::e($currentSub); ?>" selected><?php echo View::e($currentSub); ?> (atual)</option>
          <?php endif; ?>
        </select>
        <?php if (empty($subDisp)): ?>
          <p style="font-size:12px;color:#f59e0b;margin-top:4px;">Nenhum subdomínio disponível. <a href="/cliente/dominios">Cadastre um</a>.</p>
        <?php endif; ?>
      </div>
    </div>

    <?php
      $tempBase = trim((string)\LRV\Core\Settings::obter('infra.temp_domain_base', ''));
      $existingTemp = (string)($dep['temp_domain'] ?? '');
    ?>
    <?php if ($tempBase !== '' || $existingTemp !== ''): ?>
    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:14px;margin-bottom:14px;">
      <div style="font-size:13px;font-weight:600;color:#166534;margin-bottom:6px;">🌐 Domínio temporário</div>
      <?php if ($existingTemp !== ''): ?>
        <div style="font-size:13px;color:#475569;">Seu domínio temporário: <a href="http://<?php echo View::e($existingTemp); ?>" target="_blank" rel="noopener" style="color:#4F46E5;font-weight:600;"><?php echo View::e($existingTemp); ?></a></div>
      <?php else: ?>
        <div style="font-size:13px;color:#475569;margin-bottom:8px;">Não tem domínio próprio? Gere um domínio temporário gratuito (ex: <code>abc123.<?php echo View::e($tempBase); ?></code>).</div>
        <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;">
          <input type="checkbox" name="gerar_temp_domain" value="1" style="accent-color:#4F46E5;" />
          Gerar domínio temporário para este projeto
        </label>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <div style="margin-bottom:14px;">
      <?php
        $defaultPath = (string)($dep['deploy_path'] ?? '');
        if ($defaultPath === '' || $defaultPath === '/var/www/html') {
            $slugName = strtolower(preg_replace('/[^a-z0-9]/', '-', strtolower((string)($dep['name'] ?? ''))));
            $slugName = trim($slugName, '-');
            $defaultPath = '/var/www/' . ($slugName !== '' ? $slugName : 'app-' . time());
        }
      ?>
      <input type="hidden" name="deploy_path" id="deployPathInput" value="<?php echo View::e($defaultPath); ?>" />
      <div style="display:flex;align-items:center;gap:8px;">
        <span style="font-size:12px;color:#64748b;">📂 Deploy em: <code id="deployPathDisplay"><?php echo View::e($defaultPath); ?></code></span>
        <button type="button" onclick="document.getElementById('deployPathAdvanced').style.display=document.getElementById('deployPathAdvanced').style.display==='none'?'block':'none'" style="background:none;border:none;color:#4F46E5;font-size:11px;cursor:pointer;text-decoration:underline;">Alterar</button>
      </div>
      <div id="deployPathAdvanced" style="display:<?php echo $isEdit ? 'block' : 'none'; ?>;margin-top:8px;">
        <label style="display:block;font-size:13px;margin-bottom:5px;">Caminho de deploy no servidor</label>
        <input class="input" type="text" id="deployPathField" value="<?php echo View::e($defaultPath); ?>" placeholder="/var/www/meu-projeto" onchange="document.getElementById('deployPathInput').value=this.value;document.getElementById('deployPathDisplay').textContent=this.value;" />
        <p style="font-size:12px;color:#64748b;margin-top:4px;">Cada projeto deve ter seu próprio diretório.</p>
      </div>
    </div>

    <div style="margin-bottom:20px;border:1px solid #e2e8f0;border-radius:10px;padding:14px;">
      <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;">
        <input type="checkbox" name="force_overwrite" value="1" <?php echo ((int)($dep['force_overwrite'] ?? 1)) === 1 ? 'checked' : ''; ?> style="margin-top:2px;accent-color:#4F46E5;width:16px;height:16px;flex-shrink:0;" />
        <div>
          <div style="font-size:13px;font-weight:600;color:#1e293b;">Substituir tudo (force overwrite)</div>
          <div style="font-size:12px;color:#64748b;margin-top:2px;">Quando ativado, qualquer alteração feita diretamente no servidor será descartada e substituída pelo conteúdo do repositório. Recomendado para manter o servidor sempre sincronizado com o Git.<br><br>Quando desativado, o sistema faz <code>git stash</code> antes de puxar, preservando alterações locais.</div>
        </div>
      </label>
    </div>

    <div class="grid" style="margin-bottom:14px;">
      <div>
        <label style="display:block;font-size:13px;margin-bottom:5px;">Tipo de aplicação</label>
        <?php $appType = (string)($dep['app_type'] ?? 'php'); ?>
        <select class="input" name="app_type" id="appTypeSelect" onchange="toggleAppTypeFields()">
          <option value="php" <?php echo $appType === 'php' ? 'selected' : ''; ?>>🐘 PHP / Laravel / WordPress</option>
          <option value="static" <?php echo $appType === 'static' ? 'selected' : ''; ?>>📄 Site estático (HTML/CSS/JS)</option>
          <option value="nodejs" <?php echo $appType === 'nodejs' ? 'selected' : ''; ?>>🟢 Node.js</option>
          <option value="python" <?php echo $appType === 'python' ? 'selected' : ''; ?>>🐍 Python</option>
        </select>
      </div>
      <div id="appPortField" style="<?php echo in_array($appType, ['nodejs', 'python']) ? '' : 'display:none;'; ?>">
        <label style="display:block;font-size:13px;margin-bottom:5px;">Porta da aplicação</label>
        <input class="input" type="number" name="app_port" value="<?php echo (int)($dep['app_port'] ?? 3000); ?>" placeholder="3000" min="1024" max="65535" />
        <p style="font-size:12px;color:#64748b;margin-top:4px;">Porta onde a aplicação roda (ex: 3000 para Node.js, 8000 para Python)</p>
      </div>
    </div>

    <div style="margin-bottom:20px;">
      <label style="display:block;font-size:13px;margin-bottom:5px;">Comando pós-deploy <span style="font-weight:400;color:#94a3b8;">(opcional)</span></label>
      <input class="input" type="text" name="post_deploy_cmd" id="postDeployCmd" value="<?php echo View::e((string)($dep['post_deploy_cmd'] ?? '')); ?>" placeholder="npm install && npm run build" />
      <p style="font-size:12px;color:#64748b;margin-top:4px;">Executado automaticamente após cada deploy. Exemplos: <code>npm install && npm run build</code>, <code>composer install</code>, <code>pip install -r requirements.txt</code></p>
    </div>

    <!-- Configurações PHP (só para tipo PHP) -->
    <div id="phpConfigSection" style="<?php echo in_array($appType, ['nodejs', 'python', 'static']) ? 'display:none;' : ''; ?>margin-bottom:20px;border:1px solid #e2e8f0;border-radius:10px;padding:14px;">
      <div style="font-size:13px;font-weight:600;color:#1e293b;margin-bottom:10px;">🐘 Configurações PHP</div>
      <?php
        $phpSettings = [];
        if (!empty($dep['php_settings'])) {
            $phpSettings = is_string($dep['php_settings']) ? (json_decode($dep['php_settings'], true) ?: []) : (array)$dep['php_settings'];
        }
      ?>
      <div class="grid" style="margin-bottom:10px;">
        <div>
          <label style="display:block;font-size:12px;margin-bottom:4px;">Versão do PHP</label>
          <select class="input" name="php_version" style="font-size:13px;">
            <option value="8.3" <?php echo ((string)($dep['php_version'] ?? '8.3')) === '8.3' ? 'selected' : ''; ?>>PHP 8.3</option>
            <option value="8.2" <?php echo ((string)($dep['php_version'] ?? '')) === '8.2' ? 'selected' : ''; ?>>PHP 8.2</option>
            <option value="8.1" <?php echo ((string)($dep['php_version'] ?? '')) === '8.1' ? 'selected' : ''; ?>>PHP 8.1</option>
          </select>
        </div>
        <div>
          <label style="display:block;font-size:12px;margin-bottom:4px;">memory_limit</label>
          <input class="input" type="text" name="php_memory_limit" value="<?php echo View::e((string)($phpSettings['memory_limit'] ?? '256M')); ?>" style="font-size:13px;" />
        </div>
      </div>
      <div class="grid" style="margin-bottom:10px;">
        <div>
          <label style="display:block;font-size:12px;margin-bottom:4px;">upload_max_filesize</label>
          <input class="input" type="text" name="php_upload_max" value="<?php echo View::e((string)($phpSettings['upload_max_filesize'] ?? '64M')); ?>" style="font-size:13px;" />
        </div>
        <div>
          <label style="display:block;font-size:12px;margin-bottom:4px;">post_max_size</label>
          <input class="input" type="text" name="php_post_max" value="<?php echo View::e((string)($phpSettings['post_max_size'] ?? '64M')); ?>" style="font-size:13px;" />
        </div>
      </div>
      <div class="grid">
        <div>
          <label style="display:block;font-size:12px;margin-bottom:4px;">max_execution_time</label>
          <input class="input" type="text" name="php_max_exec" value="<?php echo View::e((string)($phpSettings['max_execution_time'] ?? '300')); ?>" style="font-size:13px;" />
        </div>
        <div>
          <label style="display:block;font-size:12px;margin-bottom:4px;">max_input_vars</label>
          <input class="input" type="text" name="php_max_input_vars" value="<?php echo View::e((string)($phpSettings['max_input_vars'] ?? '3000')); ?>" style="font-size:13px;" />
        </div>
      </div>
      <p style="font-size:11px;color:#94a3b8;margin-top:8px;">As configurações PHP são aplicadas ao fazer deploy. A versão do PHP precisa estar instalada no servidor.</p>
    </div>

    <!-- Dica Node.js/Python -->
    <div id="nodejsHint" style="<?php echo $appType === 'nodejs' ? '' : 'display:none;'; ?>margin-bottom:20px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:14px;">
      <div style="font-size:13px;font-weight:600;color:#166534;margin-bottom:6px;">🟢 Deploy Node.js</div>
      <p style="font-size:12px;color:#475569;margin:0;">O sistema vai usar PM2 para gerenciar o processo. Configure o comando pós-deploy com <code>npm install && npm run build</code> e o sistema vai iniciar automaticamente com <code>pm2 start</code>. O Nginx será configurado como reverse proxy para a porta informada.</p>
    </div>
    <div id="pythonHint" style="<?php echo $appType === 'python' ? '' : 'display:none;'; ?>margin-bottom:20px;background:#fefce8;border:1px solid #fef08a;border-radius:10px;padding:14px;">
      <div style="font-size:13px;font-weight:600;color:#854d0e;margin-bottom:6px;">🐍 Deploy Python</div>
      <p style="font-size:12px;color:#475569;margin:0;">O Nginx será configurado como reverse proxy para a porta informada. Configure o comando pós-deploy com <code>pip install -r requirements.txt</code> e inicie o servidor (ex: <code>gunicorn</code>, <code>uvicorn</code>) via PM2 ou systemd.</p>
    </div>

    <button class="botao" type="submit"><?php echo $isEdit ? 'Salvar alterações' : 'Conectar repositório'; ?></button>
  </form>
</div>

<script>
function toggleAppTypeFields() {
  var type = document.getElementById('appTypeSelect').value;
  var portField = document.getElementById('appPortField');
  var phpSection = document.getElementById('phpConfigSection');
  var nodejsHint = document.getElementById('nodejsHint');
  var pythonHint = document.getElementById('pythonHint');
  var postCmd = document.getElementById('postDeployCmd');

  portField.style.display = (type === 'nodejs' || type === 'python') ? '' : 'none';
  phpSection.style.display = (type === 'php') ? '' : 'none';
  nodejsHint.style.display = (type === 'nodejs') ? '' : 'none';
  pythonHint.style.display = (type === 'python') ? '' : 'none';

  // Sugerir comando pós-deploy se vazio
  if (postCmd.value === '') {
    if (type === 'nodejs') postCmd.placeholder = 'npm install && npm run build';
    else if (type === 'python') postCmd.placeholder = 'pip install -r requirements.txt';
    else if (type === 'php') postCmd.placeholder = 'composer install --no-dev';
    else postCmd.placeholder = '';
  }
}
</script>

<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
