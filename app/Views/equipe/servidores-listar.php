<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;

function formatarGb(int $mb): string {
    if ($mb <= 0) return '0 GB';
    return ((int)round($mb / 1024)) . ' GB';
}

$pageTitle = I18n::t('eq_servidores.titulo');
require __DIR__ . '/../_partials/layout-equipe-inicio.php';
?>
<div class="page-title"><?php echo View::e(I18n::t('eq_servidores.titulo')); ?></div>
<div class="page-subtitle"><?php echo View::e(I18n::t('eq_servidores.subtitulo')); ?></div>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
  <span class="texto" style="margin:0;"><?php echo View::e(I18n::t('eq_servidores.cadastre')); ?></span>
  <a class="botao" href="/equipe/servidores/novo"><?php echo View::e(I18n::t('eq_servidores.novo')); ?></a>
</div>

<div class="card-new">
  <div style="overflow:auto;">
    <table>
      <thead>
        <tr>
          <th><?php echo View::e(I18n::t('eq_servidores.hostname')); ?></th><th><?php echo View::e(I18n::t('eq_servidores.ip')); ?></th><th><?php echo View::e(I18n::t('eq_servidores.cpu')); ?></th><th><?php echo View::e(I18n::t('eq_servidores.memoria')); ?></th>
          <th><?php echo View::e(I18n::t('eq_servidores.armazenamento')); ?></th><th><?php echo View::e(I18n::t('eq_servidores.status')); ?></th><th><?php echo View::e(I18n::t('eq_servidores.setup')); ?></th><th><?php echo View::e(I18n::t('eq_servidores.acoes')); ?></th>
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
                if ($st === 'active')      echo '<span class="badge-new badge-green">' . View::e(I18n::t('eq_servidores.ativo')) . '</span>';
                elseif ($st === 'maintenance') echo '<span class="badge-new badge-yellow">' . View::e(I18n::t('eq_servidores.manutencao')) . '</span>';
                else                       echo '<span class="badge-new badge-gray">' . View::e(I18n::t('eq_servidores.inativo')) . '</span>';
                if ($isOnline !== null) {
                    echo $isOnline === 1
                        ? ' <span class="badge-new badge-green">' . View::e(I18n::t('eq_servidores.online')) . '</span>'
                        : ' <span class="badge-new badge-red">' . View::e(I18n::t('eq_servidores.offline')) . '</span>';
                }
              ?>
            </td>
            <td id="setup-badge-<?php echo $sid; ?>">
              <?php
                if ($setupSt === 'ready')         echo '<span class="badge-new badge-green">' . View::e(I18n::t('eq_servidores.pronto')) . '</span>';
                elseif ($setupSt === 'initializing') echo '<span class="badge-new badge-yellow">' . View::e(I18n::t('eq_servidores.inicializando')) . '</span>';
                elseif ($setupSt === 'error')     echo '<span class="badge-new badge-red">' . View::e(I18n::t('eq_servidores.erro')) . '</span>';
                else                              echo '<span class="badge-new badge-gray">' . View::e(I18n::t('eq_servidores.pendente')) . '</span>';
              ?>
            </td>
            <td style="white-space:nowrap;">
              <a href="/equipe/servidores/editar?id=<?php echo $sid; ?>"><?php echo View::e(I18n::t('eq_servidores.editar')); ?></a>
              &nbsp;·&nbsp;
              <a href="/equipe/servidores/terminal-seguro?id=<?php echo $sid; ?>">Terminal</a>
              &nbsp;·&nbsp;
              <?php if ($setupSt === 'error'): ?>
                <a href="#" onclick="abrirSetup(<?php echo $sid; ?>,<?php echo View::e(json_encode($hostname)); ?>,true);return false;"
                   style="color:#f59e0b;"><?php echo View::e(I18n::t('eq_servidores.continuar_setup')); ?></a>
                &nbsp;·&nbsp;
                <a href="#" onclick="abrirSetup(<?php echo $sid; ?>,<?php echo View::e(json_encode($hostname)); ?>,false);return false;"><?php echo View::e(I18n::t('eq_servidores.reiniciar')); ?></a>
              <?php elseif ($setupSt === 'ready'): ?>
                <a href="#" onclick="abrirSetup(<?php echo $sid; ?>,<?php echo View::e(json_encode($hostname)); ?>,false);return false;"
                   style="color:#64748b;"><?php echo View::e(I18n::t('eq_servidores.re_inicializar')); ?></a>
              <?php else: ?>
                <a href="#" onclick="abrirSetup(<?php echo $sid; ?>,<?php echo View::e(json_encode($hostname)); ?>,false);return false;"><?php echo View::e(I18n::t('eq_servidores.inicializar')); ?></a>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($servidores)): ?>
          <tr><td colspan="8"><?php echo View::e(I18n::t('eq_servidores.nenhum')); ?></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal de inicialização -->
<div id="modal-setup" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:9000;align-items:center;justify-content:center;">
  <div style="background:#fff;border-radius:16px;padding:28px;width:min(700px,96vw);max-height:92vh;display:flex;flex-direction:column;gap:14px;box-shadow:0 20px 60px rgba(0,0,0,.3);">

    <div style="display:flex;justify-content:space-between;align-items:center;">
      <strong id="modal-setup-titulo" style="font-size:16px;"><?php echo View::e(I18n::t('eq_servidores.modal_titulo')); ?></strong>
      <button id="btn-fechar-x" onclick="fecharSetup()" style="background:none;border:none;font-size:20px;cursor:pointer;color:#64748b;line-height:1;">✕</button>
    </div>

    <p class="texto" style="margin:0;font-size:13px;">
      <?php echo View::e(I18n::t('eq_servidores.modal_desc')); ?>
    </p>

    <!-- Barra de progresso -->
    <div style="background:#e2e8f0;border-radius:99px;height:8px;overflow:hidden;">
      <div id="setup-progress-bar" style="height:100%;width:0%;background:#4F46E5;border-radius:99px;transition:width .4s ease;"></div>
    </div>
    <div id="setup-progress-txt" style="font-size:12px;color:#64748b;margin-top:-8px;">Aguardando início…</div>

    <!-- Log terminal -->
    <div id="setup-log" style="background:#0b1020;color:#e2e8f0;border-radius:12px;padding:14px;font-size:12px;font-family:monospace;min-height:200px;max-height:320px;overflow-y:auto;white-space:pre-wrap;flex:1;"></div>

    <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
      <button id="btn-iniciar-setup" class="botao" onclick="executarSetup()"><?php echo View::e(I18n::t('eq_servidores.inicializar_agora')); ?></button>
      <button id="btn-continuar-setup" class="botao" onclick="executarSetup(true)" style="display:none;background:#f59e0b;">Continuar de onde parou</button>
      <button id="btn-fechar-setup" class="botao" onclick="fecharSetup()" style="background:#64748b;"><?php echo View::e(I18n::t('geral.fechar')); ?></button>
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

    var csrfVal = (document.querySelector('meta[name=csrf-token]') || {}).content || '';

    // 1) Chamar /inicializar para obter lista de passos
    var fd = new FormData();
    fd.append('id', _setupId);
    fd.append('_csrf', csrfVal);
    if (modoRetomar) fd.append('retomar', '1');

    fetch('/equipe/servidores/inicializar', { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data.ok) {
                appendLog('✘ ' + (data.erro || 'Erro ao iniciar setup.'));
                _setupRunning = false;
                document.getElementById('btn-fechar-x').style.display = '';
                document.getElementById('btn-iniciar-setup').disabled = false;
                return;
            }

            var steps = data.steps || [];
            var total = steps.length;
            setProgress(0, total);

            // 2) Executar passos sequencialmente
            var idx = 0;
            var concluidos = 0;
            var temErroFatal = false;

            function proximoPasso() {
                if (idx >= steps.length || temErroFatal) {
                    // 3) Finalizar
                    finalizarSetup(concluidos, total);
                    return;
                }

                var s = steps[idx];
                idx++;

                // Se já ok (retomar), pula
                if (s.status === 'ok' && modoRetomar) {
                    appendLog('⏭ ' + s.name + ' (já concluído)');
                    concluidos++;
                    setProgress(concluidos, total);
                    proximoPasso();
                    return;
                }

                appendLog('⏳ ' + s.name + '…');

                var fd2 = new FormData();
                fd2.append('id', _setupId);
                fd2.append('step', s.name);
                fd2.append('_csrf', csrfVal);

                fetch('/equipe/servidores/inicializar-passo', { method: 'POST', body: fd2 })
                    .then(function(r2) { return r2.json(); })
                    .then(function(r) {
                        var icon = r.status === 'ok' ? '✔' : '✘';
                        appendLog(icon + ' ' + s.name);
                        if (r.output && r.output.trim() && r.status !== 'ok') {
                            appendLog('    ' + r.output.trim().replace(/\n/g, '\n    '));
                        }
                        if (r.skipped) {
                            concluidos++;
                        } else if (r.ok) {
                            concluidos++;
                        } else if (r.fatal) {
                            temErroFatal = true;
                        }
                        setProgress(concluidos, total);
                        proximoPasso();
                    })
                    .catch(function(e) {
                        appendLog('✘ ' + s.name + ' — Erro de rede: ' + e.message);
                        temErroFatal = true;
                        setProgress(concluidos, total);
                        proximoPasso();
                    });
            }

            proximoPasso();
        })
        .catch(function(e) {
            _setupRunning = false;
            document.getElementById('btn-fechar-x').style.display = '';
            document.getElementById('btn-iniciar-setup').disabled = false;
            document.getElementById('btn-continuar-setup').disabled = false;
            appendLog('✘ Erro de comunicação: ' + e.message);
        });
}

function finalizarSetup(concluidos, total) {
    var csrfVal = (document.querySelector('meta[name=csrf-token]') || {}).content || '';
    var fd = new FormData();
    fd.append('id', _setupId);
    fd.append('_csrf', csrfVal);

    fetch('/equipe/servidores/inicializar-finalizar', { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            _setupRunning = false;
            document.getElementById('btn-fechar-x').style.display = '';
            setProgress(data.concluidos || concluidos, data.total || total);

            if (data.ok) {
                appendLog('\n✔ Servidor pronto. Status atualizado para Ativo/Online.');
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
            appendLog('✘ Erro ao finalizar: ' + e.message);
        });
}
</script>

<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
