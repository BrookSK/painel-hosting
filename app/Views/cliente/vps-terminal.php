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
          <div id="status" style="opacity:.9;">Desconectado</div>
        </div>
        <pre id="out" style="margin:0; padding:12px; color:#e2e8f0; font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace; font-size:13px; height:420px; overflow:auto;"></pre>
        <div style="border-top:1px solid rgba(148,163,184,.18); padding:10px 12px;">
          <input class="input" id="in" type="text" autocomplete="off" placeholder="Digite um comando e pressione Enter" style="background:#0b1220; border-color:#1f2937; color:#e2e8f0;" />
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
    })();
  </script>
</body>
</html>
