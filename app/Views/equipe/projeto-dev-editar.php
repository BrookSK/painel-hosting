<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;
use LRV\Core\Csrf;

$pageTitle = ($projeto ? I18n::t('dev_workflow.editar_projeto') : I18n::t('dev_workflow.novo_projeto'));
require __DIR__ . '/../_partials/layout-equipe-inicio.php';

$p = $projeto ?? [];
$id = (int)($p['id'] ?? 0);
?>
<div class="page-title"><?php echo View::e($pageTitle); ?></div>
<div class="page-subtitle"><?php echo View::e(I18n::t('dev_workflow.projeto_desc')); ?></div>

<a href="/equipe/dev" style="font-size:13px;color:#4F46E5;text-decoration:none;display:inline-flex;align-items:center;gap:4px;margin-bottom:16px;">
  <svg width="14" height="14" viewBox="0 0 20 20" fill="none"><path d="M12 4L6 10l6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
  <?php echo View::e(I18n::t('dev_workflow.voltar_projetos')); ?>
</a>

<?php if (!empty($erro)): ?><div class="erro"><?php echo View::e($erro); ?></div><?php endif; ?>
<?php if (!empty($sucesso)): ?><div class="sucesso"><?php echo View::e($sucesso); ?></div><?php endif; ?>

<div class="card-new" style="max-width:920px;">
  <form method="post" action="/equipe/dev/projeto/salvar">
    <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>" />
    <input type="hidden" name="id" value="<?php echo $id; ?>" />

    <h2 class="titulo" style="font-size:16px;margin-bottom:12px;">Informações do projeto</h2>

    <div class="grid" style="margin-bottom:12px;">
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;"><?php echo View::e(I18n::t('dev_workflow.nome_projeto')); ?> *</label>
        <input class="input" type="text" name="name" required maxlength="150" value="<?php echo View::e((string)($p['name'] ?? '')); ?>" placeholder="Ex: Brooks, Painel Hosting..." />
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;"><?php echo View::e(I18n::t('dev_workflow.repo_url')); ?> *</label>
        <input class="input" type="text" name="repo_url" required maxlength="500" value="<?php echo View::e((string)($p['repo_url'] ?? '')); ?>" placeholder="git@github.com:org/repo.git" />
      </div>
    </div>

    <div class="grid" style="margin-bottom:12px;">
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;"><?php echo View::e(I18n::t('dev_workflow.branch_principal')); ?></label>
        <input class="input" type="text" name="default_branch" maxlength="100" value="<?php echo View::e((string)($p['default_branch'] ?? 'main')); ?>" placeholder="main" />
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;"><?php echo View::e(I18n::t('dev_workflow.vps_equipe')); ?></label>
        <select class="input" name="vps_id">
          <option value="">— Selecione —</option>
          <?php foreach (($vpsList ?? []) as $v): ?>
            <option value="<?php echo (int)$v['id']; ?>" <?php echo ((int)($p['vps_id'] ?? 0) === (int)$v['id']) ? 'selected' : ''; ?>>
              #<?php echo (int)$v['id']; ?> — <?php echo View::e((string)($v['name'] ?? $v['hostname'] ?? '')); ?> (<?php echo (int)$v['cpu']; ?>vCPU / <?php echo (int)(($v['ram'] ?? 0) / 1024); ?>GB)
            </option>
          <?php endforeach; ?>
        </select>
        <?php if (empty($vpsList)): ?>
          <span style="font-size:11px;color:#f59e0b;">Nenhuma VPS da equipe. <a href="/equipe/vps-equipe" style="color:#4F46E5;">Criar uma</a></span>
        <?php endif; ?>
      </div>
    </div>

    <h2 class="titulo" style="font-size:16px;margin-bottom:12px;margin-top:20px;">Deploy e domínio</h2>

    <div class="grid" style="margin-bottom:12px;">
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;"><?php echo View::e(I18n::t('dev_workflow.deploy_path')); ?></label>
        <input class="input" type="text" name="deploy_path" maxlength="500" value="<?php echo View::e((string)($p['deploy_path'] ?? '')); ?>" placeholder="/var/www/dev/meu-projeto" />
        <span style="font-size:11px;color:#64748b;">Deixe vazio para gerar automaticamente.</span>
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;"><?php echo View::e(I18n::t('dev_workflow.dominio_teste')); ?></label>
        <input class="input" type="text" name="temp_domain" maxlength="255" value="<?php echo View::e((string)($p['temp_domain'] ?? '')); ?>" placeholder="Gerado automaticamente" />
        <span style="font-size:11px;color:#64748b;">Domínio .lrvweb para testes.</span>
      </div>
    </div>

    <div class="grid" style="margin-bottom:12px;">
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;"><?php echo View::e(I18n::t('dev_workflow.tipo_app')); ?></label>
        <?php $appType = (string)($p['app_type'] ?? 'php'); ?>
        <select class="input" name="app_type" id="devAppType" onchange="togglePortField()">
          <option value="php" <?php echo $appType === 'php' ? 'selected' : ''; ?>>PHP</option>
          <option value="nodejs" <?php echo $appType === 'nodejs' ? 'selected' : ''; ?>>Node.js</option>
          <option value="python" <?php echo $appType === 'python' ? 'selected' : ''; ?>>Python</option>
          <option value="static" <?php echo $appType === 'static' ? 'selected' : ''; ?>>Estático</option>
        </select>
      </div>
      <div id="phpVersionField" style="<?php echo $appType === 'php' ? '' : 'display:none;'; ?>">
        <label style="display:block;font-size:13px;margin-bottom:6px;"><?php echo View::e(I18n::t('dev_workflow.versao_php')); ?></label>
        <select class="input" name="php_version">
          <?php $phpV = (string)($p['php_version'] ?? '8.3');
            foreach (['8.4','8.3','8.2','8.1','8.0','7.4'] as $v): ?>
            <option value="<?php echo $v; ?>" <?php echo $phpV === $v ? 'selected' : ''; ?>><?php echo $v; ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div id="portField" style="<?php echo in_array($appType, ['nodejs','python']) ? '' : 'display:none;'; ?>">
        <label style="display:block;font-size:13px;margin-bottom:6px;"><?php echo View::e(I18n::t('dev_workflow.porta_app')); ?></label>
        <input class="input" type="number" name="app_port" min="1024" max="65535" value="<?php echo (int)($p['app_port'] ?? 3000); ?>" />
      </div>
    </div>

    <div style="margin-bottom:12px;">
      <label style="display:block;font-size:13px;margin-bottom:6px;"><?php echo View::e(I18n::t('dev_workflow.post_deploy_cmd')); ?></label>
      <input class="input" type="text" name="post_deploy_cmd" value="<?php echo View::e((string)($p['post_deploy_cmd'] ?? '')); ?>" placeholder="Ex: composer install && php artisan migrate" />
      <span style="font-size:11px;color:#64748b;">Comando executado após cada deploy (opcional).</span>
    </div>

    <div style="margin-bottom:12px;">
      <label style="display:block;font-size:13px;margin-bottom:6px;"><?php echo View::e(I18n::t('dev_workflow.descricao')); ?></label>
      <textarea class="input" name="description" rows="3" placeholder="Descrição do projeto (opcional)"><?php echo View::e((string)($p['description'] ?? '')); ?></textarea>
    </div>

    <div style="margin-bottom:16px;">
      <label style="display:block;font-size:13px;margin-bottom:6px;"><?php echo View::e(I18n::t('dev_workflow.auth_token')); ?></label>
      <input class="input" type="password" name="auth_token" autocomplete="off" placeholder="<?php echo $id > 0 ? '(manter atual)' : 'Token para repos privados via HTTPS'; ?>" />
      <span style="font-size:11px;color:#64748b;">Necessário apenas para repositórios HTTPS privados. Para SSH, use a Deploy Key abaixo.</span>
    </div>

    <div style="display:flex;gap:8px;">
      <button type="submit" class="botao"><?php echo View::e(I18n::t('geral.salvar')); ?></button>
      <a href="/equipe/dev" class="botao botao-secundario"><?php echo View::e(I18n::t('geral.cancelar')); ?></a>
    </div>
  </form>
</div>

<?php if ($id > 0): ?>
<!-- Deploy Key -->
<div class="card-new" style="max-width:920px;margin-top:20px;">
  <h2 class="titulo" style="font-size:16px;margin-bottom:12px;">Deploy Key</h2>
  <p class="texto">Adicione esta chave pública como Deploy Key no repositório Git:</p>

  <?php $pubKey = (string)($p['deploy_key_public'] ?? ''); ?>
  <?php if ($pubKey !== ''): ?>
    <div style="position:relative;">
      <textarea id="deployKeyText" class="input" rows="3" readonly style="font-family:monospace;font-size:12px;background:#f8fafc;"><?php echo View::e($pubKey); ?></textarea>
      <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('deployKeyText').value)" class="botao botao-sm" style="position:absolute;top:8px;right:8px;font-size:11px;">Copiar</button>
    </div>
  <?php else: ?>
    <p style="color:#94a3b8;font-size:13px;">Nenhuma deploy key gerada.</p>
  <?php endif; ?>

  <div style="margin-top:12px;display:flex;gap:10px;">
    <button type="button" id="btnRegenKey" class="botao botao-sm botao-secundario" onclick="regenerarChave()">Regenerar Chave</button>
    <button type="button" id="btnClonar" class="botao botao-sm" onclick="clonarRepo()">Clonar/Atualizar Repositório</button>
  </div>
  <div id="cloneOutput" style="display:none;margin-top:12px;background:#0f172a;color:#e2e8f0;padding:12px;border-radius:8px;font-family:monospace;font-size:12px;white-space:pre-wrap;max-height:200px;overflow:auto;"></div>
</div>

<!-- Ações do projeto -->
<div class="card-new" style="max-width:920px;margin-top:20px;">
  <h2 class="titulo" style="font-size:16px;margin-bottom:12px;">Ações</h2>
  <div style="display:flex;gap:12px;flex-wrap:wrap;">
    <a href="/equipe/dev/demandas?projeto=<?php echo $id; ?>" class="botao">Ver Demandas</a>
    <form method="post" action="/equipe/dev/projeto/arquivar" style="display:inline;" onsubmit="return confirm('Deseja arquivar este projeto?');">
      <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>" />
      <input type="hidden" name="id" value="<?php echo $id; ?>" />
      <button type="submit" class="botao botao-danger botao-sm">Arquivar Projeto</button>
    </form>
  </div>
</div>
<?php endif; ?>

<script>
function togglePortField() {
  var type = document.getElementById('devAppType').value;
  document.getElementById('portField').style.display = (type === 'nodejs' || type === 'python') ? '' : 'none';
  document.getElementById('phpVersionField').style.display = (type === 'php') ? '' : 'none';
}

function regenerarChave() {
  if (!confirm('Regenerar a deploy key? A chave anterior será invalidada.')) return;
  var btn = document.getElementById('btnRegenKey');
  btn.disabled = true; btn.textContent = 'Gerando...';
  fetch('/equipe/dev/projeto/regenerar-chave', {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded','X-CSRF-Token': '<?php echo View::e(Csrf::token()); ?>'},
    body: 'id=<?php echo $id; ?>&_csrf=<?php echo View::e(Csrf::token()); ?>'
  }).then(r => r.json()).then(d => {
    btn.disabled = false; btn.textContent = 'Regenerar Chave';
    if (d.ok && d.public_key) {
      var ta = document.getElementById('deployKeyText');
      if (ta) ta.value = d.public_key;
      alert('Nova deploy key gerada! Atualize no repositório.');
    } else { alert('Erro ao regenerar chave.'); }
  }).catch(() => { btn.disabled = false; btn.textContent = 'Regenerar Chave'; alert('Erro de conexão.'); });
}

function clonarRepo() {
  var btn = document.getElementById('btnClonar');
  var out = document.getElementById('cloneOutput');
  btn.disabled = true; btn.textContent = 'Clonando...';
  out.style.display = 'block'; out.textContent = 'Executando...';
  fetch('/equipe/dev/projeto/clonar', {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded','X-CSRF-Token': '<?php echo View::e(Csrf::token()); ?>'},
    body: 'id=<?php echo $id; ?>&_csrf=<?php echo View::e(Csrf::token()); ?>'
  }).then(r => r.json()).then(d => {
    btn.disabled = false; btn.textContent = 'Clonar/Atualizar Repositório';
    out.textContent = d.ok ? (d.output || 'Concluído com sucesso.') : ('Erro: ' + (d.erro || 'Desconhecido'));
  }).catch(() => { btn.disabled = false; btn.textContent = 'Clonar/Atualizar Repositório'; out.textContent = 'Erro de conexão.'; });
}
</script>

<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
