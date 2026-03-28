<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\Csrf;
use LRV\Core\I18n;

$vpsList = $vpsList ?? [];
$appInfo = $appInfo ?? null;
$isAppMode = $appInfo !== null;
$pageTitle = $isAppMode
    ? 'Arquivos — ' . ($appInfo['template_name'] ?? 'Aplicação')
    : 'Gerenciador de Arquivos';
require __DIR__ . '/../_partials/layout-cliente-inicio.php';
?>
<meta name="csrf-token" content="<?php echo View::e(Csrf::token()); ?>"/>

<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:16px;">
  <div>
    <div class="page-title"><?php
      if ($isAppMode) {
          echo View::e(($appInfo['template_icon'] ?? '📦') . ' ' . ($appInfo['template_name'] ?? 'Aplicação'));
          if (!empty($appInfo['domain'])) echo ' <span style="font-size:13px;color:#64748b;font-weight:400;">(' . View::e((string)$appInfo['domain']) . ')</span>';
      } else {
          echo 'Gerenciador de Arquivos';
      }
    ?></div>
    <div class="page-subtitle" style="margin-bottom:0;"><?php echo $isAppMode ? 'Navegue e edite arquivos da aplicação' : 'Navegue e edite arquivos dentro da sua VPS'; ?></div>
  </div>
  <?php if ($isAppMode): ?>
    <a href="/cliente/aplicacoes" class="botao ghost sm">← Voltar às aplicações</a>
  <?php else: ?>
    <select class="input" id="vpsSelect" style="width:auto;min-width:200px;">
      <?php foreach ($vpsList as $v): ?>
        <option value="<?php echo (int)$v['id']; ?>">VPS #<?php echo (int)$v['id']; ?> — <?php echo (int)$v['cpu']; ?>vCPU</option>
      <?php endforeach; ?>
    </select>
  <?php endif; ?>
</div>

<?php if (!$isAppMode && empty($vpsList)): ?>
  <div class="card-new" style="text-align:center;padding:40px;">
    <p style="color:#94a3b8;">Nenhuma VPS ativa. <a href="/cliente/vps">Ver VPS</a></p>
  </div>
<?php else: ?>

<div class="card-new" style="padding:0;">
  <!-- Toolbar -->
  <div style="padding:10px 14px;border-bottom:1px solid #e2e8f0;display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
    <span id="breadcrumb" style="font-size:13px;color:#475569;flex:1;font-family:monospace;">/</span>
    <button class="botao ghost sm" onclick="goUp()">⬆ Subir</button>
    <button class="botao ghost sm" onclick="promptNewFolder()">📁 Nova pasta</button>
    <button class="botao ghost sm" onclick="promptNewFile()">📄 Novo arquivo</button>
    <button class="botao ghost sm" onclick="refreshFiles()">🔄</button>
  </div>

  <!-- File list -->
  <div id="fileList" style="min-height:300px;padding:8px 14px;">
    <p style="color:#94a3b8;font-size:13px;">Carregando...</p>
  </div>
</div>

<!-- Editor modal -->
<div id="editorModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:200;align-items:center;justify-content:center;">
  <div class="card-new" style="max-width:800px;width:95%;max-height:85vh;display:flex;flex-direction:column;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
      <div class="card-new-title" id="editorTitle" style="margin:0;font-size:14px;"></div>
      <div style="display:flex;gap:6px;">
        <button class="botao sm" onclick="saveFile()">Salvar</button>
        <button class="botao ghost sm" onclick="closeEditor()">Fechar</button>
      </div>
    </div>
    <textarea id="editorContent" class="input" style="flex:1;min-height:400px;font-family:monospace;font-size:13px;resize:vertical;"></textarea>
  </div>
</div>

<script>
(function(){
  var urlParams = new URLSearchParams(window.location.search);
  var initialPath = urlParams.get('path') || '/';
  var initialVps = urlParams.get('vps_id') || '';
  var appIdParam = urlParams.get('app_id') || '';
  var currentPath=initialPath;
  var vpsSelect = document.getElementById('vpsSelect');

  // Pre-select VPS from URL if provided
  if (initialVps && vpsSelect) {
    for (var i = 0; i < vpsSelect.options.length; i++) {
      if (vpsSelect.options[i].value === initialVps) {
        vpsSelect.selectedIndex = i;
        break;
      }
    }
  }
  var currentVps = vpsSelect ? vpsSelect.value : '';
  var csrf=(document.querySelector('meta[name="csrf-token"]')||{}).content||'';

  // Build query string with either app_id or vps_id
  function qsRead() {
    if (appIdParam) return 'app_id=' + appIdParam;
    return 'vps_id=' + currentVps;
  }
  function qsPost(body) {
    if (appIdParam) body.set('app_id', appIdParam);
    else body.set('vps_id', currentVps);
  }

  if (vpsSelect) {
    vpsSelect.addEventListener('change',function(){
      currentVps=this.value;currentPath='/';appIdParam='';loadFiles();
    });
  }

  function loadFiles(){
    document.getElementById('breadcrumb').textContent=currentPath;
    document.getElementById('fileList').innerHTML='<p style="color:#94a3b8;font-size:13px;">Carregando...</p>';
    fetch('/cliente/arquivos/listar?'+qsRead()+'&path='+encodeURIComponent(currentPath))
      .then(function(r){return r.json();})
      .then(function(d){
        if(!d.ok){document.getElementById('fileList').innerHTML='<p class="erro">'+d.erro+'</p>';return;}
        renderFiles(d.files);
      })
      .catch(function(){document.getElementById('fileList').innerHTML='<p class="erro">Erro de conexão.</p>';});
  }

  function renderFiles(files){
    if(!files||files.length===0){
      document.getElementById('fileList').innerHTML='<p style="color:#94a3b8;font-size:13px;">Pasta vazia.</p>';
      return;
    }
    var html='<table style="width:100%;font-size:13px;"><thead><tr><th style="text-align:left;padding:6px 8px;">Nome</th><th style="text-align:right;padding:6px 8px;">Tamanho</th><th style="padding:6px 8px;">Data</th><th style="padding:6px 8px;">Ações</th></tr></thead><tbody>';
    files.forEach(function(f){
      var icon=f.type==='dir'?'📁':'📄';
      var size=f.type==='dir'?'—':formatSize(f.size);
      html+='<tr style="border-bottom:1px solid #f1f5f9;">';
      html+='<td style="padding:8px;"><span style="cursor:pointer;color:#4F46E5;" onclick="'+(f.type==='dir'?'navigateTo(\''+escHtml(f.name)+'\')':'openFile(\''+escHtml(f.name)+'\')')+'">'+icon+' '+escHtml(f.name)+'</span></td>';
      html+='<td style="padding:8px;text-align:right;color:#64748b;">'+size+'</td>';
      html+='<td style="padding:8px;color:#94a3b8;font-size:12px;">'+escHtml(f.date)+'</td>';
      html+='<td style="padding:8px;"><button class="botao danger sm" style="font-size:11px;padding:2px 8px;" onclick="deleteItem(\''+escHtml(f.name)+'\')">✕</button></td>';
      html+='</tr>';
    });
    html+='</tbody></table>';
    document.getElementById('fileList').innerHTML=html;
  }

  function formatSize(b){
    if(b<1024)return b+' B';
    if(b<1048576)return (b/1024).toFixed(1)+' KB';
    return (b/1048576).toFixed(1)+' MB';
  }
  function escHtml(s){return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');}

  window.navigateTo=function(name){
    currentPath=currentPath.replace(/\/$/,'') + '/' + name;
    loadFiles();
  };
  window.goUp=function(){
    if(currentPath==='/') return;
    var parts=currentPath.split('/').filter(Boolean);
    parts.pop();
    currentPath='/'+parts.join('/');
    if(currentPath!=='/')currentPath+='/';
    loadFiles();
  };
  window.openFile=function(name){
    var fullPath=currentPath.replace(/\/$/,'')+'/'+name;
    fetch('/cliente/arquivos/ler?'+qsRead()+'&path='+encodeURIComponent(fullPath))
      .then(function(r){return r.json();})
      .then(function(d){
        if(!d.ok){alert(d.erro||'Erro');return;}
        document.getElementById('editorTitle').textContent=fullPath;
        document.getElementById('editorContent').value=d.content;
        document.getElementById('editorModal').style.display='flex';
        document.getElementById('editorModal')._path=fullPath;
      });
  };
  window.closeEditor=function(){document.getElementById('editorModal').style.display='none';};
  window.saveFile=function(){
    var path=document.getElementById('editorModal')._path;
    var content=document.getElementById('editorContent').value;
    var body=new URLSearchParams();
    qsPost(body);body.set('path',path);body.set('content',content);
    fetch('/cliente/arquivos/salvar',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded','x-csrf-token':csrf},body:body})
      .then(function(r){return r.json();})
      .then(function(d){if(d.ok)closeEditor();else alert(d.erro||'Erro');});
  };
  window.promptNewFolder=function(){
    var name=prompt('Nome da pasta:');
    if(!name)return;
    var path=currentPath.replace(/\/$/,'')+'/'+name;
    var body=new URLSearchParams();qsPost(body);body.set('path',path);
    fetch('/cliente/arquivos/criar-pasta',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded','x-csrf-token':csrf},body:body})
      .then(function(r){return r.json();}).then(function(){loadFiles();});
  };
  window.promptNewFile=function(){
    var name=prompt('Nome do arquivo:');
    if(!name)return;
    var path=currentPath.replace(/\/$/,'')+'/'+name;
    var body=new URLSearchParams();qsPost(body);body.set('path',path);body.set('content','');
    fetch('/cliente/arquivos/salvar',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded','x-csrf-token':csrf},body:body})
      .then(function(r){return r.json();}).then(function(){loadFiles();});
  };
  window.deleteItem=function(name){
    if(!confirm('Deletar "'+name+'"?'))return;
    var path=currentPath.replace(/\/$/,'')+'/'+name;
    var body=new URLSearchParams();qsPost(body);body.set('path',path);
    fetch('/cliente/arquivos/deletar',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded','x-csrf-token':csrf},body:body})
      .then(function(r){return r.json();}).then(function(){loadFiles();});
  };
  window.refreshFiles=loadFiles;

  loadFiles();
})();
</script>

<?php endif; ?>
<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
