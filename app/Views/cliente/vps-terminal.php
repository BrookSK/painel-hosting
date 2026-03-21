<?php

declare(strict_types=1);

use LRV\Core\View;
use LRV\Core\I18n;
use LRV\Core\ConfiguracoesSistema;

$vps = (array) ($vps ?? []);
$vpsId = (int) ($vps['id'] ?? 0);
$status = (string) ($vps['status'] ?? '');
$porta = (int) ConfiguracoesSistema::terminalWsInternalPort();

?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Terminal VPS #<?php echo (int) $vpsId; ?></title>
  <meta name="csrf-token" content="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
</head>
<body>
  <div class="topo">
    <div class="conteudo linha" style="justify-content:space-between;">
      <div>
        <div style="font-size:18px;font-weight:700;">Terminal da VPS #<?php echo (int) $vpsId; ?></div>
        <div style="opacity:.9; font-size:13px;">Sessão isolada dentro do contêiner</div>
      </div>
      <div class="linha">
        <?php require __DIR__ . '/../_partials/idioma.php'; ?>
        <a href="/cliente/painel">Painel</a>
        <a href="/cliente/vps">VPS</a>
        <a href="/cliente/sair">Sair</a>
      </div>
    </div>
  </div>

  <div class="conteudo">
    <div class="card">
      <div class="linha" style="justify-content:space-between; align-items:flex-end;">
        <div>
          <h1 class="titulo" style="margin-bottom:6px;">Abrir sessão</h1>
          <div class="texto" style="margin:0;">Tudo é auditado. O acesso é restrito ao contêiner da sua VPS.</div>
        </div>
        <div style="text-align:right;">
          <div class="badge">WS interno: 127.0.0.1:<?php echo (int) $porta; ?></div>
        </div>
      </div>

      <?php if ($status !== 'running'): ?>
        <div class="erro" style="margin-top:12px;">VPS não está em execução (status=<?php echo View::e($status); ?>). O terminal ficará disponível quando estiver rodando.</div>
      <?php endif; ?>

      <div style="margin-top:12px;" class="grid">
        <div>
          <button class="botao" id="btnConectar" type="button" style="width:100%;" <?php echo ($status !== 'running') ? 'disabled' : ''; ?>>Conectar</button>
        </div>
      </div>

      <div id="alerta" class="erro" style="display:none; margin-top:12px;"></div>

      <div style="margin-top:12px; border:1px solid #0b1220; border-radius:14px; overflow:hidden; background:#020617;">
        <div style="padding:10px 12px; border-bottom:1px solid rgba(148,163,184,.18); color:#e2e8f0; font-size:13px; display:flex; justify-content:space-between;">
          <div>Terminal</div>
          <div style="display:flex; gap:10px; align-items:center;">
            <button id="btnUpload" class="botao sec" type="button" style="padding:4px 10px; font-size:12px;" title="Enviar arquivo para a VPS">↑ Upload</button>
            <button id="btnDownload" class="botao sec" type="button" style="padding:4px 10px; font-size:12px;" title="Baixar arquivo da VPS">↓ Download</button>
            <div id="status" style="opacity:.9;">Desconectado</div>
          </div>
        </div>
        <pre id="out" style="margin:0; padding:12px; color:#e2e8f0; font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace; font-size:13px; height:420px; overflow:auto;"></pre>
        <div style="border-top:1px solid rgba(148,163,184,.18); padding:10px 12px;">
          <input class="input" id="in" type="text" autocomplete="off" placeholder="Digite um comando e pressione Enter" style="background:#0b1220; border-color:#1f2937; color:#e2e8f0;" />
        </div>
      </div>

      <!-- Modal upload -->
      <div id="modalUpload" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.6); z-index:999; align-items:center; justify-content:center;">
        <div class="card" style="max-width:420px; width:90%;">
          <h2 class="titulo" style="font-size:15px; margin-bottom:12px;">Enviar arquivo para a VPS</h2>
          <div style="margin-bottom:10px;">
            <label style="font-size:13px; display:block; margin-bottom:4px;">Arquivo</label>
            <input type="file" id="uploadFile" class="input" />
          </div>
          <div style="margin-bottom:12px;">
            <label style="font-size:13px; display:block; margin-bottom:4px;">Caminho remoto (ex: /home/user/arquivo.txt)</label>
            <input type="text" id="uploadPath" class="input" placeholder="/home/user/arquivo.txt" />
          </div>
          <div id="uploadStatus" style="font-size:13px; margin-bottom:10px; display:none;"></div>
          <div class="linha" style="gap:8px;">
            <button class="botao" id="btnUploadEnviar" type="button">Enviar</button>
            <button class="botao sec" type="button" onclick="document.getElementById('modalUpload').style.display='none'">Cancelar</button>
          </div>
        </div>
      </div>

      <!-- Modal download -->
      <div id="modalDownload" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.6); z-index:999; align-items:center; justify-content:center;">
        <div class="card" style="max-width:420px; width:90%;">
          <h2 class="titulo" style="font-size:15px; margin-bottom:12px;">Baixar arquivo da VPS</h2>
          <div style="margin-bottom:12px;">
            <label style="font-size:13px; display:block; margin-bottom:4px;">Caminho remoto (ex: /home/user/arquivo.txt)</label>
            <input type="text" id="downloadPath" class="input" placeholder="/home/user/arquivo.txt" />
          </div>
          <div class="linha" style="gap:8px;">
            <button class="botao" id="btnDownloadIniciar" type="button">Baixar</button>
            <button class="botao sec" type="button" onclick="document.getElementById('modalDownload').style.display='none'">Cancelar</button>
          </div>
        </div>
      </div>

      <div class="texto" style="margin-top:10px; font-size:13px; opacity:.9;">
        Conexão via proxy reverso: <strong>/ws/terminal</strong> (WSS).
      </div>
    </div>
  </div>

  <script>
    (function(){
      const VPS_ID = <?php echo (int) $vpsId; ?>;
      const elOut = document.getElementById('out');
      const elIn = document.getElementById('in');
      const elStatus = document.getElementById('status');
      const elAlerta = document.getElementById('alerta');
      const btn = document.getElementById('btnConectar');

      let ws = null;
      let conectado = false;

      function sendResize(){
        if(!ws || ws.readyState !== 1) return;
        const cols = Math.max(40, Math.floor(elOut.offsetWidth / 8));
        const rows = Math.max(10, Math.floor(elOut.offsetHeight / 16));
        ws.send(JSON.stringify({type:'resize', cols, rows}));
      }

      const ro = new ResizeObserver(function(){ sendResize(); });
      ro.observe(elOut);

      function setErro(msg){
        elAlerta.style.display = 'block';
        elAlerta.textContent = msg;
      }

      function clearErro(){
        elAlerta.style.display = 'none';
        elAlerta.textContent = '';
      }

      function append(text){
        elOut.textContent += text;
        elOut.scrollTop = elOut.scrollHeight;
      }

      async function emitirToken(){
        const csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
        const body = new URLSearchParams();
        body.set('vps_id', String(VPS_ID));
        const resp = await fetch('/cliente/vps/terminal/token', {
          method: 'POST',
          headers: {'Content-Type':'application/x-www-form-urlencoded', 'x-csrf-token': csrf, 'Accept':'application/json'},
          body
        });
        const json = await resp.json();
        if(!json || !json.ok){
          throw new Error((json && json.erro) ? json.erro : 'Falha ao emitir token.');
        }
        return json.token;
      }

      function wsUrl(token){
        const proto = (location.protocol === 'https:') ? 'wss://' : 'ws://';
        return proto + location.host + '/ws/terminal?token=' + encodeURIComponent(token);
      }

      async function conectar(){
        clearErro();
        if(ws){
          try{ ws.close(); }catch(e){}
        }

        elOut.textContent = '';
        append('Abrindo sessão...\n');
        elStatus.textContent = 'Conectando...';

        const token = await emitirToken();

        ws = new WebSocket(wsUrl(token));
        ws.onopen = function(){
          conectado = true;
          elStatus.textContent = 'Conectado';
          append('Conectado.\n');
          elIn.focus();
          // Enviar tamanho inicial
          sendResize();
        };
        ws.onmessage = function(ev){
          const data = String(ev.data || '');
          append(data);
        };
        ws.onclose = function(){
          conectado = false;
          elStatus.textContent = 'Desconectado';
          append('\n[conexão encerrada]\n');
        };
        ws.onerror = function(){
          setErro('Falha no WebSocket. Verifique proxy /ws/terminal e o daemon interno.');
        };
      }

      btn.addEventListener('click', function(){
        conectar().catch(e => setErro(e.message || 'Erro ao conectar.'));
      });

      elIn.addEventListener('keydown', function(ev){
        if(ev.key !== 'Enter') return;
        ev.preventDefault();

        const v = elIn.value;
        elIn.value = '';

        if(!conectado || !ws || ws.readyState !== 1){
          setErro('Não conectado.');
          return;
        }

        ws.send(v + "\n");
      });

      // Upload
      document.getElementById('btnUpload').addEventListener('click', function(){
        document.getElementById('modalUpload').style.display = 'flex';
      });

      document.getElementById('btnUploadEnviar').addEventListener('click', async function(){
        const file = document.getElementById('uploadFile').files[0];
        const path = document.getElementById('uploadPath').value.trim();
        const statusEl = document.getElementById('uploadStatus');
        if(!file || !path){ statusEl.style.display='block'; statusEl.textContent='Selecione um arquivo e informe o caminho.'; return; }
        statusEl.style.display='block'; statusEl.textContent='Enviando...';
        const csrf = (document.querySelector('meta[name="csrf-token"]')||{}).content||'';
        const fd = new FormData();
        fd.append('file', file);
        fd.append('vps_id', String(VPS_ID));
        fd.append('remote_path', path);
        try {
          const resp = await fetch('/cliente/vps/terminal/upload', {method:'POST', headers:{'x-csrf-token':csrf}, body:fd});
          const json = await resp.json();
          statusEl.textContent = json.ok ? '✓ ' + (json.mensagem||'Enviado.') : '✗ ' + (json.erro||'Erro.');
        } catch(e){ statusEl.textContent = '✗ Erro de rede.'; }
      });

      // Download
      document.getElementById('btnDownload').addEventListener('click', function(){
        document.getElementById('modalDownload').style.display = 'flex';
      });

      document.getElementById('btnDownloadIniciar').addEventListener('click', function(){
        const path = document.getElementById('downloadPath').value.trim();
        if(!path){ return; }
        const url = '/cliente/vps/terminal/download?vps_id=' + VPS_ID + '&remote_path=' + encodeURIComponent(path);
        window.location.href = url;
        document.getElementById('modalDownload').style.display = 'none';
      });
    })();
  </script>
  <?php require __DIR__ . '/../_partials/chat-widget.php'; ?>
</body>
</html>
