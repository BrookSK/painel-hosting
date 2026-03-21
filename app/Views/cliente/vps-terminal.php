<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\ConfiguracoesSistema;

$vps    = (array)($vps ?? []);
$vpsId  = (int)($vps['id'] ?? 0);
$status = (string)($vps['status'] ?? '');
$porta  = (int)ConfiguracoesSistema::terminalWsInternalPort();

$pageTitle    = 'Terminal VPS #' . $vpsId;
$clienteNome  = (string)($cliente['name'] ?? '');
$clienteEmail = (string)($cliente['email'] ?? '');
require __DIR__ . '/../_partials/layout-cliente-inicio.php';
?>
<meta name="csrf-token" content="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />

<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:24px;">
  <div>
    <div class="page-title">Terminal VPS #<?php echo $vpsId; ?></div>
    <div class="page-subtitle" style="margin-bottom:0;">Sessão isolada dentro do contêiner</div>
  </div>
  <a href="/cliente/vps" class="botao ghost sm">← VPS</a>
</div>

<div class="card-new">
  <div style="display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:12px;flex-wrap:wrap;gap:8px;">
    <div>
      <div style="font-weight:600;font-size:14px;margin-bottom:4px;">Abrir sessão</div>
      <div style="font-size:13px;color:#64748b;">Tudo é auditado. O acesso é restrito ao contêiner da sua VPS.</div>
    </div>
    <span class="badge-new" style="background:#f1f5f9;color:#334155;">WS: 127.0.0.1:<?php echo $porta; ?></span>
  </div>

  <?php if ($status !== 'running'): ?>
    <div class="erro" style="margin-bottom:12px;">VPS não está em execução (status=<?php echo View::e($status); ?>). O terminal ficará disponível quando estiver rodando.</div>
  <?php endif; ?>

  <div style="margin-bottom:12px;">
    <button class="botao" id="btnConectar" type="button" style="width:100%;" <?php echo ($status !== 'running') ? 'disabled' : ''; ?>>Conectar</button>
  </div>

  <div id="alerta" class="erro" style="display:none;margin-bottom:12px;"></div>

  <div style="border:1px solid #0b1220;border-radius:14px;overflow:hidden;background:#020617;">
    <div style="padding:10px 12px;border-bottom:1px solid rgba(148,163,184,.18);color:#e2e8f0;font-size:13px;display:flex;justify-content:space-between;align-items:center;">
      <div>Terminal</div>
      <div style="display:flex;gap:10px;align-items:center;">
        <button id="btnUpload" class="botao ghost sm" type="button" style="padding:4px 10px;font-size:12px;">↑ Upload</button>
        <button id="btnDownload" class="botao ghost sm" type="button" style="padding:4px 10px;font-size:12px;">↓ Download</button>
        <div id="status" style="opacity:.9;font-size:13px;">Desconectado</div>
      </div>
    </div>
    <pre id="out" style="margin:0;padding:12px;color:#e2e8f0;font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;font-size:13px;height:420px;overflow:auto;"></pre>
    <div style="border-top:1px solid rgba(148,163,184,.18);padding:10px 12px;">
      <input class="input" id="in" type="text" autocomplete="off" placeholder="Digite um comando e pressione Enter"
             style="background:#0b1220;border-color:#1f2937;color:#e2e8f0;" />
    </div>
  </div>

  <div style="font-size:13px;color:#94a3b8;margin-top:10px;">Conexão via proxy reverso: <strong>/ws/terminal</strong> (WSS).</div>
</div>

<!-- Modal upload -->
<div id="modalUpload" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:999;align-items:center;justify-content:center;">
  <div class="card-new" style="max-width:420px;width:90%;">
    <div class="card-new-title" style="margin-bottom:12px;">Enviar arquivo para a VPS</div>
    <div style="margin-bottom:10px;">
      <label style="font-size:13px;display:block;margin-bottom:4px;">Arquivo</label>
      <input type="file" id="uploadFile" class="input" />
    </div>
    <div style="margin-bottom:12px;">
      <label style="font-size:13px;display:block;margin-bottom:4px;">Caminho remoto</label>
      <input type="text" id="uploadPath" class="input" placeholder="/home/user/arquivo.txt" />
    </div>
    <div id="uploadStatus" style="font-size:13px;margin-bottom:10px;display:none;"></div>
    <div style="display:flex;gap:8px;">
      <button class="botao" id="btnUploadEnviar" type="button">Enviar</button>
      <button class="botao ghost" type="button" onclick="document.getElementById('modalUpload').style.display='none'">Cancelar</button>
    </div>
  </div>
</div>

<!-- Modal download -->
<div id="modalDownload" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:999;align-items:center;justify-content:center;">
  <div class="card-new" style="max-width:420px;width:90%;">
    <div class="card-new-title" style="margin-bottom:12px;">Baixar arquivo da VPS</div>
    <div style="margin-bottom:12px;">
      <label style="font-size:13px;display:block;margin-bottom:4px;">Caminho remoto</label>
      <input type="text" id="downloadPath" class="input" placeholder="/home/user/arquivo.txt" />
    </div>
    <div style="display:flex;gap:8px;">
      <button class="botao" id="btnDownloadIniciar" type="button">Baixar</button>
      <button class="botao ghost" type="button" onclick="document.getElementById('modalDownload').style.display='none'">Cancelar</button>
    </div>
  </div>
</div>

<script>
(function(){
  const VPS_ID = <?php echo $vpsId; ?>;
  const elOut = document.getElementById('out');
  const elIn = document.getElementById('in');
  const elStatus = document.getElementById('status');
  const elAlerta = document.getElementById('alerta');
  const btn = document.getElementById('btnConectar');
  let ws = null, conectado = false;

  function sendResize(){
    if(!ws || ws.readyState !== 1) return;
    const cols = Math.max(40, Math.floor(elOut.offsetWidth / 8));
    const rows = Math.max(10, Math.floor(elOut.offsetHeight / 16));
    ws.send(JSON.stringify({type:'resize', cols, rows}));
  }
  new ResizeObserver(function(){ sendResize(); }).observe(elOut);

  function setErro(msg){ elAlerta.style.display='block'; elAlerta.textContent=msg; }
  function clearErro(){ elAlerta.style.display='none'; elAlerta.textContent=''; }
  function append(text){ elOut.textContent += text; elOut.scrollTop = elOut.scrollHeight; }

  async function emitirToken(){
    const csrf = (document.querySelector('meta[name="csrf-token"]')||{}).content||'';
    const body = new URLSearchParams();
    body.set('vps_id', String(VPS_ID));
    const resp = await fetch('/cliente/vps/terminal/token', {
      method:'POST',
      headers:{'Content-Type':'application/x-www-form-urlencoded','x-csrf-token':csrf,'Accept':'application/json'},
      body
    });
    const json = await resp.json();
    if(!json||!json.ok) throw new Error((json&&json.erro)?json.erro:'Falha ao emitir token.');
    return json.token;
  }

  function wsUrl(token){
    const proto = (location.protocol==='https:')?'wss://':'ws://';
    return proto + location.host + '/ws/terminal?token=' + encodeURIComponent(token);
  }

  async function conectar(){
    clearErro();
    if(ws){ try{ ws.close(); }catch(e){} }
    elOut.textContent = '';
    append('Abrindo sessao...\n');
    elStatus.textContent = 'Conectando...';
    const token = await emitirToken();
    ws = new WebSocket(wsUrl(token));
    ws.onopen = function(){ conectado=true; elStatus.textContent='Conectado'; append('Conectado.\n'); elIn.focus(); sendResize(); };
    ws.onmessage = function(ev){ append(String(ev.data||'')); };
    ws.onclose = function(){ conectado=false; elStatus.textContent='Desconectado'; append('\n[conexao encerrada]\n'); };
    ws.onerror = function(){ setErro('Falha no WebSocket. Verifique proxy /ws/terminal e o daemon interno.'); };
  }

  btn.addEventListener('click', function(){ conectar().catch(e => setErro(e.message||'Erro ao conectar.')); });

  elIn.addEventListener('keydown', function(ev){
    if(ev.key !== 'Enter') return;
    ev.preventDefault();
    const v = elIn.value; elIn.value = '';
    if(!conectado||!ws||ws.readyState!==1){ setErro('Nao conectado.'); return; }
    ws.send(v + "\n");
  });

  document.getElementById('btnUpload').addEventListener('click', function(){ document.getElementById('modalUpload').style.display='flex'; });
  document.getElementById('btnUploadEnviar').addEventListener('click', async function(){
    const file = document.getElementById('uploadFile').files[0];
    const path = document.getElementById('uploadPath').value.trim();
    const statusEl = document.getElementById('uploadStatus');
    if(!file||!path){ statusEl.style.display='block'; statusEl.textContent='Selecione um arquivo e informe o caminho.'; return; }
    statusEl.style.display='block'; statusEl.textContent='Enviando...';
    const csrf = (document.querySelector('meta[name="csrf-token"]')||{}).content||'';
    const fd = new FormData();
    fd.append('file', file); fd.append('vps_id', String(VPS_ID)); fd.append('remote_path', path);
    try {
      const resp = await fetch('/cliente/vps/terminal/upload', {method:'POST', headers:{'x-csrf-token':csrf}, body:fd});
      const json = await resp.json();
      statusEl.textContent = json.ok ? '✓ ' + (json.mensagem||'Enviado.') : '✗ ' + (json.erro||'Erro.');
    } catch(e){ statusEl.textContent = '✗ Erro de rede.'; }
  });

  document.getElementById('btnDownload').addEventListener('click', function(){ document.getElementById('modalDownload').style.display='flex'; });
  document.getElementById('btnDownloadIniciar').addEventListener('click', function(){
    const path = document.getElementById('downloadPath').value.trim();
    if(!path) return;
    window.location.href = '/cliente/vps/terminal/download?vps_id=' + VPS_ID + '&remote_path=' + encodeURIComponent(path);
    document.getElementById('modalDownload').style.display = 'none';
  });
})();
</script>

<?php require __DIR__ . '/../_partials/layout-cliente-fim.php'; ?>
