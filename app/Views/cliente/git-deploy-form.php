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
        <input class="input" type="text" name="name" value="<?php echo View::e((string)($dep['name'] ?? '')); ?>" placeholder="Meu site" required />
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
            foreach ($subDisp as $sd):
          ?>
            <option value="<?php echo View::e((string)($sd['subdomain'] ?? '')); ?>" <?php echo ((string)($dep['subdomain'] ?? '')) === (string)($sd['subdomain'] ?? '') ? 'selected' : ''; ?>>
              <?php echo View::e((string)($sd['subdomain'] ?? '')); ?>
            </option>
          <?php endforeach; ?>
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
      <label style="display:block;font-size:13px;margin-bottom:5px;">Caminho de deploy no servidor</label>
      <input class="input" type="text" name="deploy_path" value="<?php echo View::e((string)($dep['deploy_path'] ?? '/var/www/html')); ?>" placeholder="/var/www/html" />
      <p style="font-size:12px;color:#64748b;margin-top:4px;">Diretório onde os arquivos serão colocados na VPS.</p>
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

    <div style="margin-bottom:20px;">
      <label style="display:block;font-size:13px;margin-bottom:5px;">Comando pós-deploy <span style="font-weight:400;color:#94a3b8;">(opcional)</span></label>
      <input class="input" type="text" name="post_deploy_cmd" value="<?php echo View::e((string)($dep['post_deploy_cmd'] ?? '')); ?>" placeholder="npm install && npm run build" />
      <p style="font-size:12px;color:#64748b;margin-top:4px;">Executado automaticamente após cada deploy. Exemplos: <code>npm install && npm run build</code>, <code>composer install</code>, <code>pip install -r requirements.txt</code></p>
    </div>

    <button class="botao" type="submit"><?php echo $isEdit ? 'Salvar alterações' : 'Conectar repositório'; ?></button>
  </form>
</div>

<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
