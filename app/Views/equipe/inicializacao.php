<?php

declare(strict_types=1);

use LRV\Core\View;
use LRV\Core\I18n;

function badgeInit(bool $ok): string
{
    return $ok
        ? '<span class="badge-new badge-success">' . View::e(I18n::t('eq_init.ok')) . '</span>'
        : '<span class="badge-new badge-danger">' . View::e(I18n::t('eq_init.pendente')) . '</span>';
}

function badgeInfo(?bool $ok, string $textoOk, string $textoKo): string
{
    if ($ok === null) return '<span class="badge-new badge-neutral">N/A</span>';
    return $ok
        ? '<span class="badge-new badge-success">' . View::e($textoOk) . '</span>'
        : '<span class="badge-new badge-danger">' . View::e($textoKo) . '</span>';
}

$pageTitle = I18n::t('eq_init.titulo');
require __DIR__ . '/../_partials/layout-equipe-inicio.php';
?>

<div class="page-title"><?php echo View::e(I18n::t('eq_init.titulo')); ?></div>
<div class="page-subtitle"><?php echo View::e(I18n::t('eq_init.subtitulo')); ?></div>

<div class="card-new" style="max-width:980px;">

  <?php if (!empty($erro)): ?>
    <div class="erro" style="margin-bottom:10px;"><?php echo View::e(I18n::t('eq_init.erro')); ?>: <?php echo View::e((string)$erro); ?></div>
  <?php endif; ?>

  <?php if (!empty($ok)): ?>
    <div class="sucesso" style="margin-bottom:10px;"><?php echo View::e(I18n::t('eq_init.acao_executada')); ?></div>
  <?php endif; ?>

  <h2 class="titulo" style="font-size:16px;margin-bottom:12px;"><?php echo View::e(I18n::t('eq_init.status')); ?></h2>
  <div style="overflow:auto;">
    <table style="width:100%;border-collapse:collapse;">
      <thead>
        <tr>
          <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;"><?php echo View::e(I18n::t('eq_init.item')); ?></th>
          <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;"><?php echo View::e(I18n::t('eq_init.status')); ?></th>
          <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;"><?php echo View::e(I18n::t('eq_init.detalhe')); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach (((array)($status ?? [])) as $k => $v): ?>
          <tr>
            <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><strong><?php echo View::e((string)$k); ?></strong></td>
            <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><?php echo badgeInit((bool)($v['ok'] ?? false)); ?></td>
            <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><code><?php echo View::e((string)($v['detalhe'] ?? '')); ?></code></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php if (!empty($pendentes)): ?>
    <div style="margin-top:12px;">
      <div class="texto" style="margin:0 0 6px 0;"><strong><?php echo View::e(I18n::t('eq_init.migrations_pendentes')); ?></strong></div>
      <div class="linha" style="flex-wrap:wrap;gap:6px;">
        <?php foreach (($pendentes ?? []) as $p): ?>
          <span class="badge-new badge-warning"><?php echo View::e((string)$p); ?></span>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

  <div style="margin-top:16px;border-top:1px solid #e5e7eb;padding-top:14px;">
    <h2 class="titulo" style="font-size:16px;margin-bottom:12px;"><?php echo View::e(I18n::t('eq_init.acoes')); ?></h2>
    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(240px,1fr));">
      <form method="post" action="/equipe/inicializacao/aplicar-schema" class="card-new" style="margin:0;">
        <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
        <div class="texto" style="margin:0 0 10px 0;"><strong><?php echo View::e(I18n::t('eq_init.aplicar_schema')); ?></strong></div>
        <button class="botao" type="submit"><?php echo View::e(I18n::t('eq_init.executar')); ?></button>
      </form>

      <form method="post" action="/equipe/inicializacao/aplicar-migrations" class="card-new" style="margin:0;">
        <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
        <div class="texto" style="margin:0 0 10px 0;"><strong><?php echo View::e(I18n::t('eq_init.aplicar_migrations')); ?></strong></div>
        <button class="botao" type="submit"><?php echo View::e(I18n::t('eq_init.executar')); ?></button>
      </form>

      <form method="post" action="/equipe/inicializacao/criar-diretorios" class="card-new" style="margin:0;">
        <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
        <div class="texto" style="margin:0 0 10px 0;"><strong><?php echo View::e(I18n::t('eq_init.criar_diretorios')); ?></strong></div>
        <button class="botao" type="submit"><?php echo View::e(I18n::t('eq_init.executar')); ?></button>
      </form>

      <form method="post" action="/equipe/inicializacao/gerar-tokens" class="card-new" style="margin:0;">
        <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
        <div class="texto" style="margin:0 0 10px 0;"><strong><?php echo View::e(I18n::t('eq_init.gerar_tokens')); ?></strong></div>
        <button class="botao" type="submit"><?php echo View::e(I18n::t('eq_init.executar')); ?></button>
      </form>

      <form method="post" action="/equipe/inicializacao/processar-job" class="card-new" style="margin:0;">
        <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
        <div class="texto" style="margin:0 0 10px 0;"><strong><?php echo View::e(I18n::t('eq_init.processar_job')); ?></strong></div>
        <button class="botao" type="submit"><?php echo View::e(I18n::t('eq_init.executar')); ?></button>
      </form>

      <form method="post" action="/equipe/inicializacao/coletar-status" class="card-new" style="margin:0;">
        <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
        <div class="texto" style="margin:0 0 10px 0;"><strong><?php echo View::e(I18n::t('eq_init.coletar_status')); ?></strong></div>
        <button class="botao" type="submit"><?php echo View::e(I18n::t('eq_init.enfileirar')); ?></button>
      </form>

      <form method="post" action="/equipe/inicializacao/coletar-status-continuo" class="card-new" style="margin:0;">
        <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
        <div class="texto" style="margin:0 0 10px 0;"><strong><?php echo View::e(I18n::t('eq_init.coleta_continua')); ?></strong></div>
        <button class="botao" type="submit"><?php echo View::e(I18n::t('eq_init.iniciar')); ?></button>
      </form>

      <form method="post" action="/equipe/inicializacao/backup-automatico" class="card-new" style="margin:0;">
        <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
        <div class="texto" style="margin:0 0 10px 0;"><strong>💾 Backup automático (24h)</strong></div>
        <p class="texto" style="margin:0 0 8px;font-size:12px;">Inicia o ciclo de backups automáticos para todas as VPS com backup_slots > 0. Reagenda a cada 24h.</p>
        <button class="botao" type="submit">Iniciar</button>
      </form>

      <form method="post" action="/equipe/inicializacao/testar-nodes" class="card-new" style="margin:0;">
        <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
        <div class="texto" style="margin:0 0 10px 0;"><strong><?php echo View::e(I18n::t('eq_init.testar_nodes')); ?></strong></div>
        <button class="botao" type="submit"><?php echo View::e(I18n::t('eq_init.executar')); ?></button>
      </form>
    </div>
  </div>

  <div style="margin-top:16px;border-top:1px solid #e5e7eb;padding-top:14px;">
    <h2 class="titulo" style="font-size:16px;margin-bottom:12px;"><?php echo View::e(I18n::t('eq_init.terminal_ws')); ?></h2>
    <?php $tw = (array)($terminal_ws ?? []); ?>
    <div style="overflow:auto;">
      <table style="width:100%;border-collapse:collapse;">
        <thead>
          <tr>
            <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;"><?php echo View::e(I18n::t('eq_init.item')); ?></th>
            <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;"><?php echo View::e(I18n::t('eq_init.status')); ?></th>
            <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;"><?php echo View::e(I18n::t('eq_init.detalhe')); ?></th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><strong>Script</strong></td>
            <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><?php echo badgeInfo(isset($tw['script_ok']) ? (bool)$tw['script_ok'] : null, 'OK', 'Ausente'); ?></td>
            <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><code>terminal-ws.php</code></td>
          </tr>
          <tr>
            <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><strong>Composer</strong></td>
            <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><?php echo badgeInfo(isset($tw['composer_ok']) ? (bool)$tw['composer_ok'] : null, 'OK', 'Ausente'); ?></td>
            <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><code>composer</code></td>
          </tr>
          <tr>
            <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><strong>Dependências (vendor)</strong></td>
            <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><?php echo badgeInfo(isset($tw['vendor_ok']) ? (bool)$tw['vendor_ok'] : null, 'OK', 'Pendente'); ?></td>
            <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><code>vendor/autoload.php</code></td>
          </tr>
          <tr>
            <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><strong>Daemon</strong></td>
            <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><?php echo badgeInfo(isset($tw['daemon_ok']) ? (bool)$tw['daemon_ok'] : null, 'Rodando', 'Parado'); ?></td>
            <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><code>pid=<?php echo View::e((string)($tw['pid'] ?? '')); ?></code></td>
          </tr>
          <tr>
            <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><strong>Porta interna</strong></td>
            <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><?php echo badgeInfo(isset($tw['porta_ok']) ? (bool)$tw['porta_ok'] : null, 'Respondendo', 'Fechada'); ?></td>
            <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><code>127.0.0.1:<?php echo (int)($tw['porta'] ?? 0); ?></code></td>
          </tr>
          <tr>
            <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><strong>Instalação (composer)</strong></td>
            <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><?php echo badgeInfo(isset($tw['composer_running']) ? (bool)$tw['composer_running'] : null, 'Rodando', 'Inativo'); ?></td>
            <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><code>pid=<?php echo View::e((string)($tw['composer_pid'] ?? '')); ?></code></td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="grid" style="margin-top:12px;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));">
      <form method="post" action="/equipe/inicializacao/terminal/instalar-deps" class="card-new" style="margin:0;">
        <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
        <div class="texto" style="margin:0 0 10px 0;"><strong><?php echo View::e(I18n::t('eq_init.instalar_deps')); ?></strong></div>
        <button class="botao" type="submit"><?php echo View::e(I18n::t('eq_init.executar')); ?></button>
        <div class="texto" style="font-size:13px;opacity:.9;margin-top:10px;">Log: <code><?php echo View::e((string)($tw['composer_log_path'] ?? '')); ?></code></div>
      </form>

      <form method="post" action="/equipe/inicializacao/terminal/iniciar-daemon" class="card-new" style="margin:0;">
        <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
        <div class="texto" style="margin:0 0 10px 0;"><strong><?php echo View::e(I18n::t('eq_init.iniciar_daemon')); ?></strong></div>
        <button class="botao" type="submit"><?php echo View::e(I18n::t('eq_init.executar')); ?></button>
        <div class="texto" style="font-size:13px;opacity:.9;margin-top:10px;">Log: <code><?php echo View::e((string)($tw['log_path'] ?? '')); ?></code></div>
      </form>

      <form method="post" action="/equipe/inicializacao/terminal/parar-daemon" class="card-new" style="margin:0;">
        <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
        <div class="texto" style="margin:0 0 10px 0;"><strong><?php echo View::e(I18n::t('eq_init.parar_daemon')); ?></strong></div>
        <button class="botao" type="submit"><?php echo View::e(I18n::t('eq_init.executar')); ?></button>
      </form>
    </div>

    <div class="texto" style="margin-top:10px;font-size:13px;opacity:.9;">
      Proxy reverso deve apontar <strong>/ws/terminal</strong> para <code>127.0.0.1:<?php echo (int)($tw['porta'] ?? 0); ?></code>.
    </div>
  </div>

  <div style="margin-top:16px;border-top:1px solid #e5e7eb;padding-top:14px;">
    <h2 class="titulo" style="font-size:16px;margin-bottom:12px;"><?php echo View::e(I18n::t('eq_init.nodes_cadastrados')); ?></h2>
    <div class="texto" style="margin:0 0 10px 0;opacity:.9;">
      <?php echo View::e(I18n::t('eq_init.nodes_desc')); ?>
    </div>
    <div style="overflow:auto;">
      <table style="width:100%;border-collapse:collapse;">
        <thead>
          <tr>
            <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;">Node</th>
            <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;"><?php echo View::e(I18n::t('eq_init.host')); ?></th>
            <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;"><?php echo View::e(I18n::t('eq_init.status')); ?></th>
            <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;">Conectividade</th>
            <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;"><?php echo View::e(I18n::t('eq_init.ultima_checagem')); ?></th>
            <th style="text-align:left;padding:10px;border-bottom:1px solid #e5e7eb;"><?php echo View::e(I18n::t('eq_init.ultimo_erro')); ?></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach (((array)($nodes ?? [])) as $n): ?>
            <tr>
              <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><strong>#<?php echo (int)($n['id'] ?? 0); ?></strong> <?php echo View::e((string)($n['hostname'] ?? '')); ?></td>
              <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><code><?php echo View::e((string)($n['ip_address'] ?? '')); ?></code></td>
              <td style="padding:10px;border-bottom:1px solid #f1f5f9;">
                <?php
                  $stNode = (string)($n['status'] ?? '');
                  if ($stNode === 'active') echo '<span class="badge-new badge-success">' . View::e(I18n::t('geral.ativo')) . '</span>';
                  elseif ($stNode === 'maintenance') echo '<span class="badge-new badge-warning">' . View::e(I18n::t('eq_servidores.manutencao')) . '</span>';
                  else echo '<span class="badge-new badge-neutral">' . View::e(I18n::t('geral.inativo')) . '</span>';
                ?>
              </td>
              <td style="padding:10px;border-bottom:1px solid #f1f5f9;">
                <?php
                  if (!array_key_exists('is_online', (array)$n)) {
                      echo '<span class="badge-new badge-neutral">N/A</span>';
                  } elseif ((int)($n['is_online'] ?? 0) === 1) {
                      echo '<span class="badge-new badge-success">Online</span>';
                  } else {
                      echo '<span class="badge-new badge-danger">Offline</span>';
                  }
                ?>
              </td>
              <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><code><?php echo View::e((string)($n['last_check_at'] ?? '')); ?></code></td>
              <td style="padding:10px;border-bottom:1px solid #f1f5f9;"><code><?php echo View::e((string)($n['last_error'] ?? '')); ?></code></td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($nodes)): ?>
            <tr><td colspan="6" style="padding:12px;"><?php echo View::e(I18n::t('eq_init.nenhum_node')); ?></td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div style="margin-top:16px;border-top:1px solid #e5e7eb;padding-top:14px;">
    <h2 class="titulo" style="font-size:16px;margin-bottom:12px;"><?php echo View::e(I18n::t('eq_init.worker_http')); ?></h2>
    <?php
      $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
      $host = (string)($_SERVER['HTTP_HOST'] ?? 'localhost');
      $base = $scheme . '://' . $host;
      $tokenWorker = (string)(\LRV\Core\Settings::obter('worker.http_token', ''));
      $urlOnce = $base . '/api/worker/run-once';
    ?>
    <div class="texto" style="margin:0 0 10px 0;opacity:.9;">
      <?php echo View::e(I18n::t('eq_init.worker_desc')); ?> <code>x-worker-token</code>.
    </div>
    <div class="card-new" style="margin:0;">
      <div class="texto" style="margin:0 0 6px 0;"><strong>URL:</strong></div>
      <code id="worker-url" style="padding:6px 8px;background:#f1f5f9;border-radius:10px;display:inline-block;"><?php echo View::e($urlOnce); ?></code>
      <div class="texto" style="margin:12px 0 6px 0;"><strong>Token:</strong></div>
      <code id="worker-token" style="padding:6px 8px;background:#f1f5f9;border-radius:10px;display:inline-block;"><?php echo View::e($tokenWorker); ?></code>
      <div style="margin-top:12px;display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
        <button class="botao" type="button" id="btn-testar-worker"><?php echo View::e(I18n::t('eq_init.testar_worker')); ?></button>
        <span id="worker-resp" class="texto" style="margin:0;opacity:.9;"></span>
      </div>
    </div>
  </div>

  <?php if (!empty($logs)): ?>
    <div style="margin-top:16px;border-top:1px solid #e5e7eb;padding-top:14px;">
      <h2 class="titulo" style="font-size:16px;margin-bottom:12px;"><?php echo View::e(I18n::t('eq_init.logs')); ?></h2>
      <pre style="white-space:pre-wrap;background:#0b1020;color:#e5e7eb;padding:12px;border-radius:12px;overflow:auto;"><?php echo View::e(implode("\n", (array)$logs)); ?></pre>
    </div>
  <?php endif; ?>

  <div style="margin-top:14px;">
    <p class="texto" style="font-size:13px;margin:0;opacity:.85;">
      <?php echo View::e(I18n::t('eq_init.obs_deploy')); ?>
    </p>
  </div>
</div>

<script>
(function () {
  var btn = document.getElementById('btn-testar-worker');
  if (!btn) return;
  btn.addEventListener('click', async function () {
    var respEl = document.getElementById('worker-resp');
    var url = document.getElementById('worker-url').textContent;
    var token = document.getElementById('worker-token').textContent;
    if (respEl) respEl.textContent = 'Executando...';
    try {
      var r = await fetch(url, { method: 'POST', headers: { 'x-worker-token': token } });
      var j = await r.json();
      if (respEl) respEl.textContent = JSON.stringify(j);
    } catch (e) {
      if (respEl) respEl.textContent = 'Falha: ' + (e && e.message ? e.message : String(e));
    }
  });
})();
</script>

<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
