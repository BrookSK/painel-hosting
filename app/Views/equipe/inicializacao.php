<?php

declare(strict_types=1);

use LRV\Core\I18n;
use LRV\Core\View;

function badgeInit(bool $ok): string
{
    if ($ok) {
        return '<span class="badge" style="background:#dcfce7;color:#166534;">OK</span>';
    }
    return '<span class="badge" style="background:#fee2e2;color:#991b1b;">Pendente</span>';
}

function badgeInfo(?bool $ok, string $textoOk, string $textoKo): string
{
    if ($ok === null) {
        return '<span class="badge" style="background:#f1f5f9;color:#334155;">N/A</span>';
    }
    if ($ok) {
        return '<span class="badge" style="background:#dcfce7;color:#166534;">' . View::e($textoOk) . '</span>';
    }
    return '<span class="badge" style="background:#fee2e2;color:#991b1b;">' . View::e($textoKo) . '</span>';
}

?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Inicialização</title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
</head>
<body>
  <div class="topo">
    <div class="conteudo linha" style="justify-content:space-between;">
      <div>
        <div style="font-size:18px;font-weight:700;">Assistente de inicialização</div>
        <div style="opacity:.9; font-size:13px;">Verificações e ações para deixar o servidor pronto</div>
      </div>
      <div class="linha">
        <?php require __DIR__ . '/../_partials/idioma.php'; ?>
        <a href="/equipe/painel">Painel</a>
        <a href="/equipe/configuracoes">Configurações</a>
        <a href="/equipe/jobs">Jobs</a>
        <a href="/equipe/sair">Sair</a>
      </div>
    </div>
  </div>

  <div class="conteudo">
    <div class="card" style="max-width:980px; margin:0 auto;">
      <h1 class="titulo">Status</h1>

      <?php if (!empty($erro)): ?>
        <div class="erro" style="margin-bottom:10px;">Erro: <?php echo View::e((string) $erro); ?></div>
      <?php endif; ?>

      <?php if (!empty($ok)): ?>
        <div style="background:#ecfeff;border:1px solid #a5f3fc;color:#155e75;padding:10px 12px;border-radius:12px;margin-bottom:10px;">
          Ação executada.
        </div>
      <?php endif; ?>

      <div style="overflow:auto;">
        <table style="width:100%; border-collapse:collapse;">
          <thead>
            <tr>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Item</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Status</th>
              <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Detalhe</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach (((array) ($status ?? [])) as $k => $v): ?>
              <tr>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><strong><?php echo View::e((string) $k); ?></strong></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;">
                  <?php echo badgeInit((bool) ($v['ok'] ?? false)); ?>
                </td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><code><?php echo View::e((string) ($v['detalhe'] ?? '')); ?></code></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <?php if (!empty($pendentes)): ?>
        <div style="margin-top:12px;">
          <div class="texto" style="margin:0 0 6px 0;"><strong>Migrations pendentes:</strong></div>
          <div class="linha" style="flex-wrap:wrap; gap:6px;">
            <?php foreach (($pendentes ?? []) as $p): ?>
              <span class="badge" style="background:#fef3c7;color:#92400e;"><?php echo View::e((string) $p); ?></span>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

      <div style="margin-top:16px; border-top:1px solid #e5e7eb; padding-top:14px;">
        <h2 class="titulo" style="font-size:16px;">Ações</h2>

        <div class="grid" style="grid-template-columns:repeat(auto-fit, minmax(240px, 1fr));">
          <form method="post" action="/equipe/inicializacao/aplicar-schema" class="card" style="margin:0;">
            <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
            <div class="texto" style="margin:0 0 10px 0;"><strong>Aplicar schema.sql</strong></div>
            <button class="botao" type="submit">Executar</button>
          </form>

          <form method="post" action="/equipe/inicializacao/aplicar-migrations" class="card" style="margin:0;">
            <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
            <div class="texto" style="margin:0 0 10px 0;"><strong>Aplicar migrations</strong></div>
            <button class="botao" type="submit">Executar</button>
          </form>

          <form method="post" action="/equipe/inicializacao/criar-diretorios" class="card" style="margin:0;">
            <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
            <div class="texto" style="margin:0 0 10px 0;"><strong>Criar diretórios</strong></div>
            <button class="botao" type="submit">Executar</button>
          </form>

          <form method="post" action="/equipe/inicializacao/gerar-tokens" class="card" style="margin:0;">
            <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
            <div class="texto" style="margin:0 0 10px 0;"><strong>Gerar tokens/defaults</strong></div>
            <button class="botao" type="submit">Executar</button>
          </form>

          <form method="post" action="/equipe/inicializacao/processar-job" class="card" style="margin:0;">
            <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
            <div class="texto" style="margin:0 0 10px 0;"><strong>Processar 1 job</strong></div>
            <button class="botao" type="submit">Executar</button>
          </form>

          <form method="post" action="/equipe/inicializacao/coletar-status" class="card" style="margin:0;">
            <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
            <div class="texto" style="margin:0 0 10px 0;"><strong>Coletar status (1x)</strong></div>
            <button class="botao" type="submit">Enfileirar</button>
          </form>

          <form method="post" action="/equipe/inicializacao/coletar-status-continuo" class="card" style="margin:0;">
            <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
            <div class="texto" style="margin:0 0 10px 0;"><strong>Iniciar coleta contínua</strong></div>
            <button class="botao" type="submit">Iniciar</button>
          </form>

          <form method="post" action="/equipe/inicializacao/testar-nodes" class="card" style="margin:0;">
            <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
            <div class="texto" style="margin:0 0 10px 0;"><strong>Testar conectividade dos nodes</strong></div>
            <button class="botao" type="submit">Executar</button>
          </form>
        </div>
      </div>

      <div style="margin-top:16px; border-top:1px solid #e5e7eb; padding-top:14px;">
        <h2 class="titulo" style="font-size:16px;">Terminal WS (Admin)</h2>

        <?php $tw = (array) ($terminal_ws ?? []); ?>

        <div style="overflow:auto;">
          <table style="width:100%; border-collapse:collapse;">
            <thead>
              <tr>
                <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Item</th>
                <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Status</th>
                <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Detalhe</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><strong>Script</strong></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo badgeInfo(isset($tw['script_ok']) ? (bool) $tw['script_ok'] : null, 'OK', 'Ausente'); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><code>terminal-ws.php</code></td>
              </tr>
              <tr>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><strong>Composer</strong></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo badgeInfo(isset($tw['composer_ok']) ? (bool) $tw['composer_ok'] : null, 'OK', 'Ausente'); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><code>composer</code></td>
              </tr>
              <tr>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><strong>Dependências (vendor)</strong></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo badgeInfo(isset($tw['vendor_ok']) ? (bool) $tw['vendor_ok'] : null, 'OK', 'Pendente'); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><code>vendor/autoload.php</code></td>
              </tr>
              <tr>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><strong>Daemon</strong></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo badgeInfo(isset($tw['daemon_ok']) ? (bool) $tw['daemon_ok'] : null, 'Rodando', 'Parado'); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><code>pid=<?php echo View::e((string) ($tw['pid'] ?? '')); ?></code></td>
              </tr>
              <tr>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><strong>Porta interna</strong></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo badgeInfo(isset($tw['porta_ok']) ? (bool) $tw['porta_ok'] : null, 'Respondendo', 'Fechada'); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><code>127.0.0.1:<?php echo (int) ($tw['porta'] ?? 0); ?></code></td>
              </tr>
              <tr>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><strong>Instalação (composer)</strong></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><?php echo badgeInfo(isset($tw['composer_running']) ? (bool) $tw['composer_running'] : null, 'Rodando', 'Inativo'); ?></td>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><code>pid=<?php echo View::e((string) ($tw['composer_pid'] ?? '')); ?></code></td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="grid" style="margin-top:12px; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr));">
          <form method="post" action="/equipe/inicializacao/terminal/instalar-deps" class="card" style="margin:0;">
            <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
            <div class="texto" style="margin:0 0 10px 0;"><strong>Instalar dependências (composer)</strong></div>
            <button class="botao" type="submit">Executar</button>
            <div class="texto" style="font-size:13px; opacity:.9; margin-top:10px;">
              Log: <code><?php echo View::e((string) ($tw['composer_log_path'] ?? '')); ?></code>
            </div>
          </form>

          <form method="post" action="/equipe/inicializacao/terminal/iniciar-daemon" class="card" style="margin:0;">
            <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
            <div class="texto" style="margin:0 0 10px 0;"><strong>Iniciar daemon</strong></div>
            <button class="botao" type="submit">Executar</button>
            <div class="texto" style="font-size:13px; opacity:.9; margin-top:10px;">
              Log: <code><?php echo View::e((string) ($tw['log_path'] ?? '')); ?></code>
            </div>
          </form>

          <form method="post" action="/equipe/inicializacao/terminal/parar-daemon" class="card" style="margin:0;">
            <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
            <div class="texto" style="margin:0 0 10px 0;"><strong>Parar daemon</strong></div>
            <button class="botao" type="submit">Executar</button>
          </form>
        </div>

        <div class="texto" style="margin-top:10px; font-size:13px; opacity:.9;">
          Proxy reverso deve apontar <strong>/ws/terminal</strong> para <code>127.0.0.1:<?php echo (int) ($tw['porta'] ?? 0); ?></code>.
        </div>
      </div>

      <div style="margin-top:16px; border-top:1px solid #e5e7eb; padding-top:14px;">
        <h2 class="titulo" style="font-size:16px;">Nodes cadastrados</h2>

        <div class="texto" style="margin:0 0 10px 0; opacity:.9;">
          Esta lista mostra o status e o último resultado de conectividade (SSH + <code>docker version</code>).
        </div>

        <div style="overflow:auto;">
          <table style="width:100%; border-collapse:collapse;">
            <thead>
              <tr>
                <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Node</th>
                <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Host</th>
                <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Status</th>
                <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Conectividade</th>
                <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Última checagem</th>
                <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Último erro</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach (((array) ($nodes ?? [])) as $n): ?>
                <tr>
                  <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><strong>#<?php echo (int) ($n['id'] ?? 0); ?></strong> <?php echo View::e((string) ($n['hostname'] ?? '')); ?></td>
                  <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><code><?php echo View::e((string) ($n['ip_address'] ?? '')); ?></code></td>
                  <td style="padding:10px; border-bottom:1px solid #f1f5f9;">
                    <?php
                      $stNode = (string) ($n['status'] ?? '');
                      if ($stNode === 'active') {
                          echo '<span class="badge">Ativo</span>';
                      } elseif ($stNode === 'maintenance') {
                          echo '<span class="badge" style="background:#fef3c7;color:#92400e;">Manutenção</span>';
                      } else {
                          echo '<span class="badge" style="background:#f1f5f9;color:#334155;">Inativo</span>';
                      }
                    ?>
                  </td>
                  <td style="padding:10px; border-bottom:1px solid #f1f5f9;">
                    <?php
                      if (!array_key_exists('is_online', (array) $n)) {
                          echo '<span class="badge" style="background:#f1f5f9;color:#334155;">N/A</span>';
                      } else {
                          $on = (int) ($n['is_online'] ?? 0);
                          if ($on === 1) {
                              echo '<span class="badge" style="background:#dcfce7;color:#166534;">Online</span>';
                          } else {
                              echo '<span class="badge" style="background:#fee2e2;color:#991b1b;">Offline</span>';
                          }
                      }
                    ?>
                  </td>
                  <td style="padding:10px; border-bottom:1px solid #f1f5f9;"><code><?php echo View::e((string) ($n['last_check_at'] ?? '')); ?></code></td>
                  <td style="padding:10px; border-bottom:1px solid #f1f5f9;">
                    <?php
                      $err = (string) ($n['last_error'] ?? '');
                      echo '<code>' . View::e($err) . '</code>';
                    ?>
                  </td>
                </tr>
              <?php endforeach; ?>
              <?php if (empty($nodes)): ?>
                <tr>
                  <td colspan="6" style="padding:12px;">Nenhum node cadastrado ainda.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div style="margin-top:16px; border-top:1px solid #e5e7eb; padding-top:14px;">
        <h2 class="titulo" style="font-size:16px;">Worker via HTTP (sem CLI)</h2>

        <?php
          $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
          $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
          $base = $scheme . '://' . $host;
          $tokenWorker = (string) (\LRV\Core\Settings::obter('worker.http_token', ''));
          $urlOnce = $base . '/api/worker/run-once';
        ?>

        <div class="texto" style="margin:0 0 10px 0; opacity:.9;">
          Endpoint para rodar 1 job (ideal para cron externo). Autenticação por header <code>x-worker-token</code>.
        </div>

        <div class="card" style="margin:0;">
          <div class="texto" style="margin:0 0 6px 0;"><strong>URL:</strong></div>
          <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
            <code id="worker-url" style="padding:6px 8px; background:#f1f5f9; border-radius:10px; display:inline-block;"><?php echo View::e($urlOnce); ?></code>
          </div>

          <div class="texto" style="margin:12px 0 6px 0;"><strong>Token:</strong></div>
          <code id="worker-token" style="padding:6px 8px; background:#f1f5f9; border-radius:10px; display:inline-block;"><?php echo View::e($tokenWorker); ?></code>

          <div style="margin-top:12px; display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
            <button class="botao" type="button" id="btn-testar-worker">Testar (executar 1 job via HTTP)</button>
            <span id="worker-resp" class="texto" style="margin:0; opacity:.9;"></span>
          </div>
        </div>
      </div>

      <?php if (!empty($logs)): ?>
        <div style="margin-top:16px; border-top:1px solid #e5e7eb; padding-top:14px;">
          <h2 class="titulo" style="font-size:16px;">Logs</h2>
          <pre style="white-space:pre-wrap; background:#0b1020; color:#e5e7eb; padding:12px; border-radius:12px; overflow:auto;"><?php echo View::e(implode("\n", (array) $logs)); ?></pre>
        </div>
      <?php endif; ?>

      <div style="margin-top:14px;">
        <p class="texto" style="font-size:13px; margin:0; opacity:.85;">
          Observação: para Deploy/Backups o servidor precisa ter <strong>ssh</strong> e <strong>scp</strong> disponíveis e o PHP com <strong>shell_exec</strong> habilitado.
        </p>
      </div>
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
        var r = await fetch(url, {
          method: 'POST',
          headers: {
            'x-worker-token': token
          }
        });
        var j = await r.json();
        if (respEl) respEl.textContent = JSON.stringify(j);
      } catch (e) {
        if (respEl) respEl.textContent = 'Falha: ' + (e && e.message ? e.message : String(e));
      }
    });
  })();
  </script>
</body>
</html>
