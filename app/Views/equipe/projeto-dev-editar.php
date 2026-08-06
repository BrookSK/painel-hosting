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

<?php if (!empty($erro)): ?>
<div class="alerta alerta-erro" style="margin-bottom:16px;"><?php echo View::e($erro); ?></div>
<?php endif; ?>
<?php if (!empty($sucesso)): ?>
<div class="alerta alerta-sucesso" style="margin-bottom:16px;"><?php echo View::e($sucesso); ?></div>
<?php endif; ?>

<form method="post" action="/equipe/dev/projeto/salvar" class="card-new" style="padding:24px;">
  <input type="hidden" name="_csrf" value="<?php echo View::e(Csrf::token()); ?>" />
  <input type="hidden" name="id" value="<?php echo $id; ?>" />

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
    <!-- Nome -->
    <div class="form-group">
      <label class="form-label"><?php echo View::e(I18n::t('dev_workflow.nome_projeto')); ?> *</label>
      <input type="text" name="name" class="form-input" required maxlength="150"
             value="<?php echo View::e((string)($p['name'] ?? '')); ?>"
             placeholder="Ex: Brooks, Painel Hosting..." />
    </div>

    <!-- Repositório -->
    <div class="form-group">
      <label class="form-label"><?php echo View::e(I18n::t('dev_workflow.repo_url')); ?> *</label>
      <input type="text" name="repo_url" class="form-input" required maxlength="500"
             value="<?php echo View::e((string)($p['repo_url'] ?? '')); ?>"
             placeholder="git@github.com:org/repo.git" />
    </div>

    <!-- Branch principal -->
    <div class="form-group">
      <label class="form-label"><?php echo View::e(I18n::t('dev_workflow.branch_principal')); ?></label>
      <input type="text" name="default_branch" class="form-input" maxlength="100"
             value="<?php echo View::e((string)($p['default_branch'] ?? 'main')); ?>"
             placeholder="main" />
    </div>

    <!-- VPS -->
    <div class="form-group">
      <label class="form-label"><?php echo View::e(I18n::t('dev_workflow.vps_equipe')); ?></label>
      <select name="vps_id" class="form-input">
        <option value="">— Selecione —</option>
        <?php foreach (($vpsList ?? []) as $v): ?>
          <option value="<?php echo (int)$v['id']; ?>" <?php echo ((int)($p['vps_id'] ?? 0) === (int)$v['id']) ? 'selected' : ''; ?>>
            #<?php echo (int)$v['id']; ?> — <?php echo View::e((string)($v['name'] ?? $v['hostname'] ?? '')); ?> (<?php echo (int)$v['cpu']; ?>vCPU / <?php echo (int)(($v['ram'] ?? 0) / 1024); ?>GB)
          </option>
        <?php endforeach; ?>
      </select>
      <?php if (empty($vpsList)): ?>
        <small style="color:#f59e0b;font-size:11px;">Nenhuma VPS da equipe encontrada. <a href="/equipe/dev/vps" style="color:#4F46E5;">Criar uma VPS da equipe</a></small>
      <?php endif; ?>
    </div>

    <!-- Caminho de deploy -->
    <div class="form-group">
      <label class="form-label"><?php echo View::e(I18n::t('dev_workflow.deploy_path')); ?></label>
      <input type="text" name="deploy_path" class="form-input" maxlength="500"
             value="<?php echo View::e((string)($p['deploy_path'] ?? '')); ?>"
             placeholder="/var/www/dev/meu-projeto" />
      <small style="color:#64748b;font-size:11px;">Deixe vazio para gerar automaticamente.</small>
    </div>

    <!-- Domínio temporário -->
    <div class="form-group">
      <label class="form-label"><?php echo View::e(I18n::t('dev_workflow.dominio_teste')); ?></label>
      <input type="text" name="temp_domain" class="form-input" maxlength="255"
             value="<?php echo View::e((string)($p['temp_domain'] ?? '')); ?>"
             placeholder="Gerado automaticamente" />
      <small style="color:#64748b;font-size:11px;">Domínio .lrvweb para testes. Gerado automaticamente se vazio.</small>
    </div>

    <!-- Tipo de aplicação -->
    <div class="form-group">
      <label class="form-label"><?php echo View::e(I18n::t('dev_workflow.tipo_app')); ?></label>
      <select name="app_type" class="form-input" id="devAppType" onchange="togglePortField()">
        <?php $appType = (string)($p['app_type'] ?? 'php'); ?>
        <option value="php" <?php echo $appType === 'php' ? 'selected' : ''; ?>>PHP</option>
        <option value="nodejs" <?php echo $appType === 'nodejs' ? 'selected' : ''; ?>>Node.js</option>
        <option value="python" <?php echo $appType === 'python' ? 'selected' : ''; ?>>Python</option>
        <option value="static" <?php echo $appType === 'static' ? 'selected' : ''; ?>>Estático</option>
      </select>
    </div>

    <!-- Porta (Node/Python) -->
    <div class="form-group" id="portField" style="<?php echo in_array($appType, ['nodejs','python']) ? '' : 'display:none;'; ?>">
      <label class="form-label"><?php echo View::e(I18n::t('dev_workflow.porta_app')); ?></label>
      <input type="number" name="app_port" class="form-input" min="1024" max="65535"
             value="<?php echo (int)($p['app_port'] ?? 3000); ?>" />
    </div>

    <!-- Versão PHP -->
    <div class="form-group" id="phpVersionField" style="<?php echo $appType === 'php' ? '' : 'display:none;'; ?>">
      <label class="form-label"><?php echo View::e(I18n::t('dev_workflow.versao_php')); ?></label>
      <select name="php_version" class="form-input">
        <?php $phpV = (string)($p['php_version'] ?? '8.3');
          foreach (['8.4','8.3','8.2','8.1','8.0','7.4'] as $v): ?>
          <option value="<?php echo $v; ?>" <?php echo $phpV === $v ? 'selected' : ''; ?>><?php echo $v; ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <!-- Comando pós-deploy -->
    <div class="form-group" style="grid-column:1/-1;">
      <label class="form-label"><?php echo View::e(I18n::t('dev_workflow.post_deploy_cmd')); ?></label>
      <input type="text" name="post_deploy_cmd" class="form-input"
             value="<?php echo View::e((string)($p['post_deploy_cmd'] ?? '')); ?>"
             placeholder="Ex: composer install && php artisan migrate" />
      <small style="color:#64748b;font-size:11px;">Comando executado após cada deploy (opcional).</small>
    </div>

    <!-- Descrição -->
    <div class="form-group" style="grid-column:1/-1;">
      <label class="form-label"><?php echo View::e(I18n::t('dev_workflow.descricao')); ?></label>
      <textarea name="description" class="form-input" rows="3" placeholder="Descrição do projeto (opcional)"><?php echo View::e((string)($p['description'] ?? '')); ?></textarea>
    </div>

    <!-- Token de autenticação -->
    <div class="form-group" style="grid-column:1/-1;">
      <label class="form-label"><?php echo View::e(I18n::t('dev_workflow.auth_token')); ?></label>
      <input type="password" name="auth_token" class="form-input" autocomplete="off"
             placeholder="<?php echo $id > 0 ? '(manter atual)' : 'Token para repos privados via HTTPS'; ?>" />
      <small style="color:#64748b;font-size:11px;">Necessário apenas para repositórios HTTPS privados. Para SSH, use a Deploy Key abaixo.</small>
    </div>
  </div>

  <div style="margin-top:20px;display:flex;gap:12px;">
    <button type="submit" class="botao"><?php echo View::e(I18n::t('geral.salvar')); ?></button>
    <a href="/equipe/dev" class="botao botao-secundario"><?php echo View::e(I18n::t('geral.cancelar')); ?></a>
  </div>
</form>

<?php if ($id > 0): ?>
<!-- Deploy Key -->
<div class="card-new" style="margin-top:20px;padding:24px;">
  <h3 style="margin:0 0 12px;font-size:16px;font-weight:700;color:#0f172a;">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:6px;"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 11-7.778 7.778 5.5 5.5 0 017.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>
    Deploy Key
  </h3>
  <p style="font-size:13px;color:#64748b;margin:0 0 12px;">Adicione esta chave pública como Deploy Key no repositório Git:</p>

  <?php $pubKey = (string)($p['deploy_key_public'] ?? ''); ?>
  <?php if ($pubKey !== ''): ?>
    <div style="position:relative;">
      <textarea id="deployKeyText" class="form-input" rows="3" readonly style="font-family:monospace;font-size:12px;background:#f8fafc;"><?php echo View::e($pubKey); ?></textarea>
      <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('deployKeyText').value)" class="botao botao-sm" style="position:absolute;top:8px;right:8px;font-size:11px;">Copiar</button>
    </div>
  <?php else: ?>
    <p style="color:#94a3b8;font-size:13px;">Nenhuma deploy key gerada.</p>
  <?php endif; ?>

  <div style="margin-top:12px;display:flex;gap:10px;">
    <button type="button" id="btnRegenKey" class="botao botao-sm botao-secundario" onclick="regenerarChave()">Regenerar Chave</button>
    <button type="button" id="btnClonar" class="botao botao-sm" onclick="clonarRepo()">
      <svg width="14" height="14" viewBox="0 0 20 20" fill="none" style="vertical-align:middle;"><path d="M10 3v10M6 9l4 4 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
      Clonar/Atualizar Repositório
    </button>
  </div>
  <div id="cloneOutput" style="display:none;margin-top:12px;background:#0f172a;color:#e2e8f0;padding:12px;border-radius:8px;font-family:monospace;font-size:12px;white-space:pre-wrap;max-height:200px;overflow:auto;"></div>
</div>

<!-- Ações do projeto -->
<div class="card-new" style="margin-top:20px;padding:24px;">
  <h3 style="margin:0 0 12px;font-size:16px;font-weight:700;color:#0f172a;">Ações</h3>
  <div style="display:flex;gap:12px;flex-wrap:wrap;">
    <a href="/equipe/dev/demandas?projeto=<?php echo $id; ?>" class="botao">
      <svg width="14" height="14" viewBox="0 0 20 20" fill="none" style="vertical-align:middle;"><path d="M4 4l4 4-4 4M10 16h6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
      Ver Demandas
    </a>
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
    } else {
      alert('Erro ao regenerar chave.');
    }
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
