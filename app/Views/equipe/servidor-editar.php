<?php

declare(strict_types=1);

use LRV\Core\View;

$id = $servidor['id'] ?? null;
$pageTitle = $id ? 'Editar servidor' : 'Novo servidor';
require __DIR__ . '/../_partials/layout-equipe-inicio.php';
?>

<div class="page-title"><?php echo $id ? 'Editar servidor' : 'Novo servidor'; ?></div>
<div class="page-subtitle">Servidores / <?php echo $id ? 'Editar' : 'Novo'; ?></div>

<div class="card-new" style="max-width:920px;">

  <?php if (!empty($erro)): ?>
    <div class="erro"><?php echo View::e((string)$erro); ?></div>
  <?php endif; ?>

  <?php if (!empty($mensagem_ok)): ?>
    <div class="sucesso"><?php echo View::e((string)$mensagem_ok); ?></div>
  <?php endif; ?>

  <?php if (array_key_exists('is_online', (array)$servidor) || array_key_exists('last_check_at', (array)$servidor) || array_key_exists('last_error', (array)$servidor)): ?>
    <div class="card-new" style="margin:0 0 12px 0;">
      <div class="texto" style="margin:0 0 6px 0;"><strong>Status de conectividade</strong></div>
      <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(220px,1fr));">
        <div>
          <div class="texto" style="margin:0;font-size:13px;opacity:.9;">Online</div>
          <div style="margin-top:6px;">
            <?php
              if (!array_key_exists('is_online', (array)$servidor)) {
                  echo '<span class="badge-new badge-neutral">N/A</span>';
              } elseif ((int)($servidor['is_online'] ?? 0) === 1) {
                  echo '<span class="badge-new badge-success">Online</span>';
              } else {
                  echo '<span class="badge-new badge-danger">Offline</span>';
              }
            ?>
          </div>
        </div>
        <div>
          <div class="texto" style="margin:0;font-size:13px;opacity:.9;">Última checagem</div>
          <div style="margin-top:6px;"><code><?php echo View::e((string)($servidor['last_check_at'] ?? '')); ?></code></div>
        </div>
      </div>
      <?php if (!empty($servidor['last_error'])): ?>
        <div style="margin-top:10px;">
          <div class="texto" style="margin:0;font-size:13px;opacity:.9;">Último erro</div>
          <pre style="white-space:pre-wrap;background:#0b1020;color:#e5e7eb;padding:10px;border-radius:12px;overflow:auto;margin-top:6px;"><?php echo View::e((string)($servidor['last_error'] ?? '')); ?></pre>
        </div>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <div class="card-new" style="margin:0 0 12px 0;">
    <div class="texto" style="margin:0 0 10px 0;"><strong>Testar conexão</strong></div>
    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
      <button class="botao" type="submit" form="form-servidor" formaction="/equipe/servidores/testar-conexao">Testar SSH/Docker</button>
      <?php if (!empty($servidor['id'])): ?>
        <?php
          $setupSt = (string)($servidor['setup_status'] ?? 'pending');
          $retomar = $setupSt === 'error' ? 'true' : 'false';
          $btnLabel = $setupSt === 'error' ? 'Continuar setup' : ($setupSt === 'ready' ? 'Re-inicializar' : 'Inicializar servidor');
          $btnStyle = $setupSt === 'error' ? 'background:#f59e0b;' : '';
        ?>
        <button class="botao" type="button" style="<?php echo $btnStyle; ?>"
          onclick="abrirSetupEditar(<?php echo (int)$servidor['id']; ?>,<?php echo View::e(json_encode((string)($servidor['hostname']??''))); ?>,<?php echo $retomar; ?>)">
          <?php echo View::e($btnLabel); ?>
        </button>
      <?php endif; ?>
      <span class="texto" style="margin:0;opacity:.85;">Executa <code>docker version</code> no node.</span>
    </div>
  </div>

  <form id="form-servidor" method="post" action="/equipe/servidores/salvar">
    <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
    <input type="hidden" name="id" value="<?php echo View::e((string)($servidor['id'] ?? '')); ?>" />

    <div class="grid">
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Hostname</label>
        <input class="input" type="text" name="hostname" value="<?php echo View::e((string)($servidor['hostname'] ?? '')); ?>" />
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Status</label>
        <select class="input" name="status">
          <option value="active" <?php echo ($servidor['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Ativo</option>
          <option value="maintenance" <?php echo ($servidor['status'] ?? '') === 'maintenance' ? 'selected' : ''; ?>>Manutenção</option>
          <option value="inactive" <?php echo ($servidor['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inativo</option>
        </select>
      </div>
    </div>

    <div class="grid" style="margin-top:12px;">
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">IP</label>
        <input class="input" type="text" name="ip_address" value="<?php echo View::e((string)($servidor['ip_address'] ?? '')); ?>" />
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Porta SSH</label>
        <input class="input" type="number" name="ssh_port" value="<?php echo View::e((string)($servidor['ssh_port'] ?? '22')); ?>" min="1" max="65535" />
      </div>
    </div>

    <div class="grid" style="margin-top:12px;">
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Usuário SSH</label>
        <input class="input" type="text" name="ssh_user" value="<?php echo View::e((string)($servidor['ssh_user'] ?? '')); ?>" />
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Identificador da chave SSH</label>
        <input class="input" type="text" name="ssh_key_id" value="<?php echo View::e((string)($servidor['ssh_key_id'] ?? '')); ?>" />
        <p class="texto" style="font-size:13px;margin-top:8px;">Arquivo dentro do diretório base configurado em <strong>/equipe/configuracoes</strong>.</p>
      </div>
    </div>

    <div class="card-new" style="margin:12px 0 0 0;">
      <div class="texto" style="margin:0 0 10px 0;"><strong>Terminal seguro (clientes)</strong></div>
      <div class="texto" style="margin:0;opacity:.9;font-size:13px;">
        Usado para abrir sessões de terminal do cliente dentro do contêiner via <code>ForceCommand</code>.
      </div>
      <div class="grid" style="margin-top:12px;">
        <div>
          <label style="display:block;font-size:13px;margin-bottom:6px;">Usuário SSH do terminal</label>
          <input class="input" type="text" name="terminal_ssh_user" value="<?php echo View::e((string)($servidor['terminal_ssh_user'] ?? 'lrv-terminal')); ?>" />
        </div>
        <div>
          <label style="display:block;font-size:13px;margin-bottom:6px;">Identificador da chave do terminal</label>
          <input class="input" type="text" name="terminal_ssh_key_id" value="<?php echo View::e((string)($servidor['terminal_ssh_key_id'] ?? '')); ?>" />
          <p class="texto" style="font-size:13px;margin-top:8px;">Deixe em branco para desativar terminal seguro neste node.</p>
        </div>
      </div>
      <?php if (!empty($servidor['id'])): ?>
        <div style="margin-top:12px;">
          <a class="botao" href="/equipe/servidores/terminal-seguro?id=<?php echo (int)($servidor['id'] ?? 0); ?>">Passo-a-passo de configuração no node</a>
        </div>
      <?php endif; ?>
    </div>

    <div class="grid" style="margin-top:12px;">
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">CPU total</label>
        <input class="input" type="number" name="cpu_total" value="<?php echo View::e((string)($servidor['cpu_total'] ?? '')); ?>" min="1" />
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Memória total (MB)</label>
        <input class="input" type="number" name="ram_total" value="<?php echo View::e((string)($servidor['ram_total'] ?? '')); ?>" min="256" />
        <p class="texto" style="font-size:13px;margin-top:8px;">Exemplo: 65536 = 64 GB</p>
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Armazenamento total (MB)</label>
        <input class="input" type="number" name="storage_total" value="<?php echo View::e((string)($servidor['storage_total'] ?? '')); ?>" min="1024" />
        <p class="texto" style="font-size:13px;margin-top:8px;">Exemplo: 1024000 = 1000 GB</p>
      </div>
    </div>

    <div style="margin-top:14px;">
      <button class="botao" type="submit">Salvar</button>
    </div>
  </form>
</div>

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
var _setupId = 0, _setupRunning = false;

function abrirSetupEditar(id, hostname, retomar) {
    _setupId = id; _setupRunning = false;
    document.getElementById('modal-setup-titulo').textContent = 'Inicializar: ' + hostname;
    document.getElementById('setup-log').textContent = retomar ? 'Modo: continuar de onde parou.\n' : 'Clique para iniciar.\n';
    document.getElementById('setup-progress-bar').style.width = '0%';
    document.getElementById('setup-progress-txt').textContent = 'Aguardando início…';
    document.getElementById('btn-iniciar-setup').style.display = retomar ? 'none' : '';
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

    var fd = new FormData();
    fd.append('id', _setupId);
    fd.append('_csrf', (document.querySelector('meta[name=csrf-token]') || {}).content || '');
    if (retomar) fd.append('retomar', '1');

    fetch('/equipe/servidores/inicializar', { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            _setupRunning = false;
            document.getElementById('btn-fechar-x').style.display = '';
            var steps = data.steps || [], total = data.total || steps.length || 8, concluidos = data.concluidos || 0;
            steps.forEach(function(s) {
                appendLog((s.status === 'ok' ? '✔' : (s.status === 'error' ? '✘' : '…')) + ' ' + s.step);
                if (s.output && s.output.trim()) appendLog('    ' + s.output.trim().replace(/\n/g, '\n    '));
            });
            var pct = total > 0 ? Math.round(concluidos / total * 100) : 0;
            document.getElementById('setup-progress-bar').style.width = pct + '%';
            document.getElementById('setup-progress-txt').textContent = concluidos + ' / ' + total + ' etapas (' + pct + '%)';
            if (data.ok) {
                appendLog('\n✔ Servidor pronto. Recarregue para ver o status atualizado.');
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
            document.getElementById('btn-iniciar-setup').disabled = false;
            document.getElementById('btn-continuar-setup').disabled = false;
            appendLog('✘ Erro: ' + e.message);
        });
}
</script>

<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
