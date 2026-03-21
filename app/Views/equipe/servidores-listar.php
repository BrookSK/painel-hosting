<?php
declare(strict_types=1);
use LRV\Core\View;

function formatarGb(int $mb): string {
    if ($mb <= 0) return '0 GB';
    return ((int)round($mb / 1024)) . ' GB';
}

$pageTitle = 'Servidores';
require __DIR__ . '/../_partials/layout-equipe-inicio.php';
?>
<div class="page-title">Servidores</div>
<div class="page-subtitle">Nodes do cluster e capacidade disponível</div>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
  <span class="texto" style="margin:0;">Cadastre seus nodes e a capacidade total.</span>
  <a class="botao" href="/equipe/servidores/novo">Novo servidor</a>
</div>

<div class="card-new">
  <div style="overflow:auto;">
    <table>
      <thead>
        <tr>
          <th>Hostname</th><th>IP</th><th>CPU</th><th>Memória</th>
          <th>Armazenamento</th><th>Status</th><th>Setup</th><th>Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach (($servidores ?? []) as $s):
          $sid        = (int)($s['id'] ?? 0);
          $hostname   = (string)($s['hostname'] ?? '');
          $setupSt    = (string)($s['setup_status'] ?? 'pending');
          $isOnline   = array_key_exists('is_online', $s) ? (int)($s['is_online'] ?? 0) : null;
        ?>
          <tr id="row-srv-<?php echo $sid; ?>">
            <td><strong><?php echo View::e($hostname); ?></strong></td>
            <td><?php echo View::e((string)($s['ip_address'] ?? '')); ?></td>
            <td><?php echo View::e((string)($s['cpu_used'] ?? 0)); ?>/<?php echo View::e((string)($s['cpu_total'] ?? 0)); ?></td>
            <td><?php echo View::e(formatarGb((int)($s['ram_used'] ?? 0))); ?>/<?php echo View::e(formatarGb((int)($s['ram_total'] ?? 0))); ?></td>
            <td><?php echo View::e(formatarGb((int)($s['storage_used'] ?? 0))); ?>/<?php echo View::e(formatarGb((int)($s['storage_total'] ?? 0))); ?></td>
            <td>
              <?php
                $st = (string)($s['status'] ?? '');
                if ($st === 'active')      echo '<span class="badge-new badge-green">Ativo</span>';
                elseif ($st === 'maintenance') echo '<span class="badge-new badge-yellow">Manutenção</span>';
                else                       echo '<span class="badge-new badge-gray">Inativo</span>';
                if ($isOnline !== null) {
                    echo $isOnline === 1
                        ? ' <span class="badge-new badge-green">Online</span>'
                        : ' <span class="badge-new badge-red">Offline</span>';
                }
              ?>
            </td>
            <td id="setup-badge-<?php echo $sid; ?>">
              <?php
                if ($setupSt === 'ready')         echo '<span class="badge-new badge-green">Pronto</span>';
                elseif ($setupSt === 'initializing') echo '<span class="badge-new badge-yellow">Inicializando…</span>';
                elseif ($setupSt === 'error')     echo '<span class="badge-new badge-red">Erro</span>';
                else                              echo '<span class="badge-new badge-gray">Pendente</span>';
              ?>
            </td>
            <td style="white-space:nowrap;">
              <a href="/equipe/servidores/editar?id=<?php echo $sid; ?>">Editar</a>
              &nbsp;·&nbsp;
              <a href="/equipe/servidores/terminal-seguro?id=<?php echo $sid; ?>">Terminal</a>
              &nbsp;·&nbsp;
              <?php if ($setupSt === 'error'): ?>
                <a href="#" onclick="abrirSetup(<?php echo $sid; ?>,<?php echo View::e(json_encode($hostname)); ?>,true);return false;"
                   style="color:#f59e0b;">Continuar setup</a>
                &nbsp;·&nbsp;
                <a href="#" onclick="abrirSetup(<?php echo $sid; ?>,<?php echo View::e(json_encode($hostname)); ?>,false);return false;">Reiniciar</a>
              <?php elseif ($setupSt === 'ready'): ?>
                <a href="#" onclick="abrirSetup(<?php echo $sid; ?>,<?php echo View::e(json_encode($hostname)); ?>,false);return false;"
                   style="color:#64748b;">Re-inicializar</a>
              <?php else: ?>
                <a href="#" onclick="abrirSetup(<?php echo $sid; ?>,<?php echo View::e(json_encode($hostname)); ?>,false);return false;">Inicializar</a>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($servidores)): ?>
          <tr><td colspan="8">Nenhum servidor cadastrado ainda.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal de inicialização -->
<div id="modal-setup" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:9000;align-items:center;justify-content:center;">
  <div style="background:#fff;border-radius:16px;padding:28px;width:min(700px,96vw);max-height:92vh;display:flex;flex-direction:column;gap:14px;box-shadow:0 20px 60px rgba(0,0,0,.3);">

    <div style="display:flex;justify-content:space-between;align-items:center;">
      <strong id="modal-setup-titulo" style="font-size:16px;">Inicializar servidor</strong>
      <button id="btn-fechar-x" onclick="fecharSetup()" style="background:none;border:none;font-size:20px;cursor:pointer;color:#64748b;line-height:1;">✕</button>
    </div>

    <p class="texto" style="margin:0;font-size:13px;">
      Conecta via SSH e prepara o servidor automaticamente: Docker, rede <code>lrv-net</code> e usuário de terminal.
    </p>

    <!-- Barra de progresso -->
    <div style="background:#e2e8f0;border-radius:99px;height:8px;overflow:hidden;">
      <div id="setup-progress-bar" style="height:100%;width:0%;background:#4F46E5;border-radius:99px;transition:width .4s ease;"></div>
    </div>
    <div id="setup-progress-txt" style="font-size:12px;color:#64748b;margin-top:-8px;">Aguardando início…</div>

    <!-- Log terminal -->
    <div id="setup-log" style="background:#0b1020;color:#e2e8f0;border-radius:12px;padding:14px;font-size:12px;font-family:monospace;min-height:200px;max-height:320px;overflow-y:auto;white-space:pre-wrap;flex:1;"></div>

    <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
      <button id="btn-iniciar-setup" class="botao" onclick="executarSetup()">Inicializar agora</button>
      <button id="btn-continuar-setup" class="botao" onclick="executarSetup(true)" style="display:none;background:#f59e0b;">Continuar de onde parou</button>
      <button id="btn-fechar-setup" class="botao" onclick="fecharSetup()" style="background:#64748b;">Fechar</button>
    </div>
  </div>
</div>

<script>
var _setupId = 0;
var _setupRunning = false;
var _setupRetomar = false;
var _setupTotal = 8; // número de passos definidos no service

function abrirSetup(id, hostname, retomar) {
    _setupId = id;
    _setupRunning = false;
    _setupRetomar = !!retomar;

    document.getElementById('modal-setup-titulo').textContent = 'Inicializar: ' + hostname;
    document.getElementById('setup-log').textContent = retomar
        ? 'Modo: continuar de onde parou.\nClique no botão para retomar.\n'
        : 'Clique em "Inicializar agora" para começar.\n';
    document.getElementById('setup-progress-bar').style.width = '0%';
    document.getElementById('setup-progress-txt').textContent = 'Aguardando início…';

    document.getElementById('btn-iniciar-setup').style.display = retomar ? 'none' : '';
    document.getElementById('btn-continuar-setup').style.display = retomar ? '' : 'none';
    document.getElementById('btn-iniciar-setup').disabled = false;
    document.getElementById('btn-continuar-setup').disabled = false;
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

function setProgress(concluidos, total) {
    var pct = total > 0 ? Math.round((concluidos / total) * 100) : 0;
    document.getElementById('setup-progress-bar').style.width = pct + '%';
    document.getElementById('setup-progress-txt').textContent = concluidos + ' / ' + total + ' etapas (' + pct + '%)';
}

function executarSetup(retomar) {
    if (_setupRunning || !_setupId) return;
    var modoRetomar = (retomar === true) || _setupRetomar;

    _setupRunning = true;
    document.getElementById('btn-iniciar-setup').disabled = true;
    document.getElementById('btn-continuar-setup').disabled = true;
    document.getElementById('btn-fechar-x').style.display = 'none';
    document.getElementById('setup-log').textContent = '';
    appendLog('▶ ' + (modoRetomar ? 'Retomando' : 'Iniciando') + ' setup do servidor #' + _setupId + '…\n');
    setProgress(0, _setupTotal);

    var fd = new FormData();
    fd.append('id', _setupId);
    fd.append('_csrf', (document.querySelector('meta[name=csrf-token]') || {}).content || '');
    if (modoRetomar) fd.append('retomar', '1');

    fetch('/equipe/servidores/inicializar', { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            _setupRunning = false;
            document.getElementById('btn-fechar-x').style.display = '';

            var steps = data.steps || [];
            var total = data.total || steps.length || _setupTotal;
            var concluidos = data.concluidos || 0;

            steps.forEach(function(s) {
                var icon = s.status === 'ok' ? '✔' : (s.status === 'error' ? '✘' : '…');
                appendLog(icon + ' ' + s.step);
                if (s.output && s.output.trim()) {
                    appendLog('    ' + s.output.trim().replace(/\n/g, '\n    '));
                }
            });

            setProgress(concluidos, total);

            if (data.ok) {
                appendLog('\n✔ Servidor pronto. Status atualizado para Ativo/Online.');
                // Atualiza badge na tabela sem reload
                var badge = document.getElementById('setup-badge-' + _setupId);
                if (badge) badge.innerHTML = '<span class="badge-new badge-green">Pronto</span>';
                document.getElementById('btn-iniciar-setup').style.display = 'none';
                document.getElementById('btn-continuar-setup').style.display = 'none';
            } else {
                appendLog('\n✘ Setup concluído com erros. Corrija e use "Continuar de onde parou".');
                var badge2 = document.getElementById('setup-badge-' + _setupId);
                if (badge2) badge2.innerHTML = '<span class="badge-new badge-red">Erro</span>';
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
            appendLog('✘ Erro de comunicação: ' + e.message);
        });
}
</script>

<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
