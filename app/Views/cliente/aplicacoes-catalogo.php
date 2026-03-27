<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\Csrf;
use LRV\Core\I18n;

$pageTitle = I18n::t('apps.catalogo');
require __DIR__ . '/../_partials/layout-cliente-inicio.php';
$csrf = Csrf::token();
?>

<style>
.cat-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px;margin-top:16px;}
.cat-card{background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:20px;transition:border-color .15s,box-shadow .15s;display:flex;flex-direction:column;}
.cat-card:hover{border-color:#7C3AED;box-shadow:0 4px 20px rgba(124,58,237,.08);}
.cat-icon{font-size:32px;margin-bottom:8px;}
.cat-name{font-size:15px;font-weight:700;color:#1e293b;margin-bottom:4px;}
.cat-desc{font-size:13px;color:#64748b;line-height:1.5;flex:1;margin-bottom:12px;}
.cat-tag{display:inline-block;font-size:11px;padding:2px 8px;border-radius:999px;background:#f1f5f9;color:#475569;font-weight:600;margin-bottom:10px;}
.install-modal-bg{position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:9999;display:none;align-items:center;justify-content:center;}
.install-modal-bg.open{display:flex;}
.install-modal{background:#fff;border-radius:16px;padding:24px;width:420px;max-width:calc(100vw - 32px);max-height:80vh;overflow-y:auto;box-shadow:0 16px 48px rgba(0,0,0,.15);}
.install-modal h3{margin:0 0 16px;font-size:16px;font-weight:700;color:#1e293b;}
.install-field{margin-bottom:14px;}
.install-field label{display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:5px;}
.install-field input,.install-field select,.install-field textarea{width:100%;padding:8px 12px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:14px;font-family:inherit;outline:none;}
.install-field input:focus,.install-field select:focus,.install-field textarea:focus{border-color:#7C3AED;}
.install-actions{display:flex;gap:10px;justify-content:flex-end;margin-top:16px;}
.install-status{font-size:13px;color:#64748b;margin-top:10px;display:none;}
</style>

<div class="page-title"><?php echo View::e(I18n::t('apps.catalogo')); ?></div>
<div class="page-subtitle"><?php echo View::e(I18n::t('apps.catalogo_subtitulo')); ?></div>

<?php if (empty($vpsList)): ?>
  <div class="card-new" style="margin-top:16px;">
    <p class="texto"><?php echo View::e(I18n::t('apps.vps_necessaria')); ?> <a href="/cliente/painel"><?php echo View::e(I18n::t('apps.voltar_painel')); ?></a></p>
  </div>
<?php else: ?>

<?php
  $categorias = [];
  foreach ($templates as $t) {
      $cat = (string)($t['category'] ?? 'other');
      $categorias[$cat][] = $t;
  }
  $catLabels = ['cms'=>I18n::t('cat.cms'),'backend'=>I18n::t('cat.backend'),'database'=>I18n::t('cat.database'),'webserver'=>I18n::t('cat.webserver'),'dev'=>I18n::t('cat.dev'),'email'=>I18n::t('cat.email'),'other'=>I18n::t('cat.other')];
?>

<?php foreach ($categorias as $cat => $items): ?>
  <div style="margin-top:20px;">
    <div style="font-size:14px;font-weight:700;color:#475569;margin-bottom:8px;"><?php echo View::e($catLabels[$cat] ?? ucfirst($cat)); ?></div>
    <div class="cat-grid">
      <?php foreach ($items as $t): ?>
        <div class="cat-card">
          <div class="cat-icon"><?php echo (string)($t['icon'] ?? '📦'); ?></div>
          <div class="cat-name"><?php echo View::e((string)($t['name']??'')); ?></div>
          <div class="cat-desc"><?php echo View::e((string)($t['description']??'')); ?></div>
          <span class="cat-tag"><?php echo View::e((string)($t['docker_image']??'')); ?></span>
          <button class="botao sm" onclick="openInstall(<?php echo (int)$t['id']; ?>,<?php echo View::e(json_encode($t)); ?>)"><?php echo View::e(I18n::t('apps.instalar')); ?></button>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
<?php endforeach; ?>

<div class="install-modal-bg" id="installBg">
  <div class="install-modal">
    <h3 id="installTitle">Instalar aplicação</h3>
    <div id="installWarning" style="display:none;background:#fef3c7;border:1px solid #fde68a;color:#92400e;padding:10px 12px;border-radius:10px;font-size:13px;margin-bottom:14px;"></div>
    <form id="installForm" onsubmit="return doInstall(event)">
      <input type="hidden" name="_csrf" value="<?php echo View::e($csrf); ?>" />
      <input type="hidden" name="template_id" id="fTplId" />
      <div class="install-field">
        <label>VPS</label>
        <select name="vps_id" id="fVps" required>
          <?php foreach ($vpsList as $v): ?>
            <option value="<?php echo (int)$v['id']; ?>">VPS #<?php echo (int)$v['id']; ?> — <?php echo (int)$v['cpu']; ?>vCPU / <?php echo round(((int)($v['ram']??0))/1024/1024/1024,1); ?>GB</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="install-field" id="fDomainWrap" style="display:none;">
        <label>Subdomínio</label>
        <select name="domain" id="fDomain">
          <option value="">Selecione um subdomínio...</option>
          <?php foreach (($subdomains_disponiveis ?? []) as $sd): ?>
            <option value="<?php echo View::e((string)($sd['subdomain'] ?? '')); ?>"><?php echo View::e((string)($sd['subdomain'] ?? '')); ?></option>
          <?php endforeach; ?>
        </select>
        <?php if (empty($subdomains_disponiveis ?? [])): ?>
          <p style="font-size:11px;color:#f59e0b;margin-top:4px;">Nenhum subdomínio disponível. <a href="/cliente/dominios">Cadastre um</a>.</p>
        <?php endif; ?>
      </div>
      <div class="install-field" id="fRepoWrap" style="display:none;">
        <label>Repositório Git</label>
        <input type="text" name="repository" id="fRepo" placeholder="https://github.com/user/repo.git" />
      </div>
      <div class="install-field" id="fEnvWrap" style="display:none;">
        <label style="margin-bottom:8px;">Configurações</label>
        <div id="fEnvFields"></div>
        <input type="hidden" name="env_json" id="fEnv" />
      </div>
      <div class="install-actions">
        <button type="button" class="botao ghost sm" onclick="closeInstall()"><?php echo View::e(I18n::t('geral.cancelar')); ?></button>
        <button type="submit" class="botao sm" id="installBtn"><?php echo View::e(I18n::t('apps.instalar')); ?></button>
      </div>
      <div class="install-status" id="installStatus"></div>
    </form>
  </div>
</div>
<?php endif; ?>

<div style="margin-top:20px;">
  <a href="/cliente/aplicacoes" class="botao ghost sm"><?php echo I18n::t('apps.minhas_apps'); ?></a>
</div>

<script>
var currentTpl=null;
function openInstall(id,tpl){
  currentTpl=tpl;
  document.getElementById('fTplId').value=id;
  document.getElementById('installTitle').textContent='Instalar '+tpl.name;
  document.getElementById('fDomainWrap').style.display=parseInt(tpl.requires_domain)?'':'none';
  document.getElementById('fRepoWrap').style.display=parseInt(tpl.requires_repo)?'':'none';
  var warn=document.getElementById('installWarning');
  if(tpl.slug==='roundcube'){
    warn.innerHTML='⚠️ <?php echo View::e(I18n::t('apps.roundcube_aviso')); ?>';
    warn.style.display='';
  }else{warn.style.display='none';}
  var envVars=tpl.environment_variables;
  if(envVars&&typeof envVars==='string')try{envVars=JSON.parse(envVars);}catch(e){envVars=null;}
  var envFieldsEl=document.getElementById('fEnvFields');
  envFieldsEl.innerHTML='';
  if(envVars&&typeof envVars==='object'&&Object.keys(envVars).length>0){
    document.getElementById('fEnvWrap').style.display='';
    var friendlyNames={
      'WORDPRESS_SITE_TITLE':'Título do site','WORDPRESS_ADMIN_USER':'Usuário administrador','WORDPRESS_ADMIN_PASSWORD':'Senha do administrador',
      'WORDPRESS_ADMIN_EMAIL':'E-mail do administrador','WORDPRESS_TABLE_PREFIX':'Prefixo das tabelas',
      'NODE_PROJECT_NAME':'Nome do projeto','NODE_VERSION':'Versão do Node','APP_PORT':'Porta da aplicação',
      'LARAVEL_PROJECT_NAME':'Nome do projeto','PHP_VERSION':'Versão do PHP',
      'MYSQL_ROOT_PASSWORD':'Senha root do MySQL','MYSQL_DATABASE':'Nome do banco de dados',
      'SITE_TITLE':'Título do site',
      'ROUNDCUBEMAIL_DEFAULT_HOST':'Servidor IMAP','ROUNDCUBEMAIL_SMTP_SERVER':'Servidor SMTP',
      'ROUNDCUBEMAIL_DEFAULT_PORT':'Porta IMAP','ROUNDCUBEMAIL_SMTP_PORT':'Porta SMTP'
    };
    var placeholders={
      'WORDPRESS_SITE_TITLE':'Meu Site','WORDPRESS_ADMIN_USER':'admin','WORDPRESS_ADMIN_PASSWORD':'senha segura',
      'WORDPRESS_ADMIN_EMAIL':'seu@email.com','WORDPRESS_TABLE_PREFIX':'wp_',
      'NODE_PROJECT_NAME':'meu-app','APP_PORT':'3000',
      'LARAVEL_PROJECT_NAME':'meu-projeto',
      'MYSQL_ROOT_PASSWORD':'senha segura','MYSQL_DATABASE':'meu_banco',
      'SITE_TITLE':'Meu Site'
    };
    // Esconder variáveis internas do cliente
    var hiddenVars=['WORDPRESS_DB_HOST','WORDPRESS_DB_USER','WORDPRESS_DB_PASSWORD','WORDPRESS_DB_NAME','DB_DATABASE','DB_USERNAME','DB_PASSWORD','DB_HOST'];
    // Campos que são selects
    var selectFields={
      'NODE_VERSION':['18','20','22'],
      'PHP_VERSION':['8.1','8.2','8.3']
    };
    Object.keys(envVars).forEach(function(key){
      if(hiddenVars&&hiddenVars.indexOf(key)!==-1)return; // Pular variáveis internas
      var label=friendlyNames[key]||key.replace(/_/g,' ').toLowerCase();
      var ph=placeholders[key]||'';
      var isPassword=key.toLowerCase().indexOf('password')!==-1||key.toLowerCase().indexOf('secret')!==-1;
      var div=document.createElement('div');
      div.style.marginBottom='10px';
      if(selectFields&&selectFields[key]){
        var opts=selectFields[key].map(function(v){return '<option value="'+v+'"'+(envVars[key]===v?' selected':'')+'>'+v+'</option>';}).join('');
        div.innerHTML='<label style="display:block;font-size:12px;font-weight:500;color:#475569;margin-bottom:4px;">'+label+'</label>'
          +'<select class="input" data-env-key="'+key+'" style="font-size:13px;">'+opts+'</select>';
      } else {
        div.innerHTML='<label style="display:block;font-size:12px;font-weight:500;color:#475569;margin-bottom:4px;">'+label+'</label>'
          +'<input type="'+(isPassword?'password':'text')+'" class="input" data-env-key="'+key+'" placeholder="'+ph+'" value="'+envVars[key]+'" style="font-size:13px;"/>';
      }
      envFieldsEl.appendChild(div);
    });
  }else{document.getElementById('fEnvWrap').style.display='none';}
  document.getElementById('fEnv').value='';
  document.getElementById('installStatus').style.display='none';
  document.getElementById('installBtn').disabled=false;
  document.getElementById('installBg').classList.add('open');
}
function closeInstall(){document.getElementById('installBg').classList.remove('open');}
document.getElementById('installBg').addEventListener('click',function(e){if(e.target===this)closeInstall();});

function doInstall(e){
  e.preventDefault();
  var btn=document.getElementById('installBtn'),st=document.getElementById('installStatus');
  btn.disabled=true;st.style.display='block';st.textContent='Iniciando instalação...';st.style.color='#64748b';

  // Coletar variáveis de ambiente dos campos
  var envObj={};
  document.querySelectorAll('#fEnvFields input[data-env-key], #fEnvFields select[data-env-key]').forEach(function(inp){
    envObj[inp.dataset.envKey]=inp.value;
  });
  document.getElementById('fEnv').value=Object.keys(envObj).length>0?JSON.stringify(envObj):'';
  var fd=new FormData(document.getElementById('installForm'));
  var body=new URLSearchParams(fd).toString();
  fetch('/cliente/aplicacoes/instalar',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:body})
  .then(function(r){return r.json();}).then(function(d){
    if(d.ok){
      st.textContent='Instalação iniciada! Redirecionando...';st.style.color='#16a34a';
      setTimeout(function(){window.location.href='/cliente/aplicacoes';},1500);
    }else{st.textContent=d.erro||'Erro ao instalar.';st.style.color='#dc2626';btn.disabled=false;}
  }).catch(function(){st.textContent='Erro de rede.';st.style.color='#dc2626';btn.disabled=false;});
  return false;
}
</script>
<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
