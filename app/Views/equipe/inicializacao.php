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
            <?php foreach (($status ?? []) as $k => $v): ?>
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
            <div class="texto" style="margin:0 0 10px 0;"><strong>Aplicar schema.sql</strong></div>
            <button class="botao" type="submit">Executar</button>
          </form>

          <form method="post" action="/equipe/inicializacao/aplicar-migrations" class="card" style="margin:0;">
            <div class="texto" style="margin:0 0 10px 0;"><strong>Aplicar migrations</strong></div>
            <button class="botao" type="submit">Executar</button>
          </form>

          <form method="post" action="/equipe/inicializacao/criar-diretorios" class="card" style="margin:0;">
            <div class="texto" style="margin:0 0 10px 0;"><strong>Criar diretórios</strong></div>
            <button class="botao" type="submit">Executar</button>
          </form>

          <form method="post" action="/equipe/inicializacao/gerar-tokens" class="card" style="margin:0;">
            <div class="texto" style="margin:0 0 10px 0;"><strong>Gerar tokens/defaults</strong></div>
            <button class="botao" type="submit">Executar</button>
          </form>

          <form method="post" action="/equipe/inicializacao/processar-job" class="card" style="margin:0;">
            <div class="texto" style="margin:0 0 10px 0;"><strong>Processar 1 job</strong></div>
            <button class="botao" type="submit">Executar</button>
          </form>
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
