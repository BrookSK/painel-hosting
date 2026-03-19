<?php

declare(strict_types=1);

use LRV\Core\View;
use LRV\Core\I18n;

?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Auditoria - Sessão</title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
</head>
<body>
  <div class="topo">
    <div class="conteudo linha" style="justify-content:space-between;">
      <div>
        <div style="font-size:18px;font-weight:700;">Auditoria - Sessão</div>
        <div style="opacity:.9; font-size:13px;">Comandos executados</div>
      </div>
      <div class="linha">
        <?php require __DIR__ . '/../_partials/idioma.php'; ?>
        <a href="/equipe/terminal/auditoria">Voltar</a>
        <a href="/equipe/terminal">Terminal</a>
        <a href="/equipe/painel">Painel</a>
        <a href="/equipe/sair">Sair</a>
      </div>
    </div>
  </div>

  <div class="conteudo">
    <div class="card">
      <h1 class="titulo" style="margin-bottom:8px;">Sessão #<?php echo (int) ($sessao['id'] ?? 0); ?></h1>

      <div class="grid" style="grid-template-columns:repeat(auto-fit, minmax(240px, 1fr));">
        <div>
          <div class="texto" style="margin:0 0 6px 0;"><strong>Usuário</strong></div>
          <div><?php echo View::e((string) ($sessao['user_name'] ?? '')); ?></div>
        </div>
        <div>
          <div class="texto" style="margin:0 0 6px 0;"><strong>Node</strong></div>
          <div><?php echo View::e((string) ($sessao['server_hostname'] ?? '')); ?></div>
          <div style="opacity:.85; font-size:12px;"><code><?php echo View::e((string) ($sessao['server_ip'] ?? '')); ?></code></div>
        </div>
        <div>
          <div class="texto" style="margin:0 0 6px 0;"><strong>Início</strong></div>
          <div><?php echo View::e((string) ($sessao['started_at'] ?? '')); ?></div>
        </div>
        <div>
          <div class="texto" style="margin:0 0 6px 0;"><strong>Fim</strong></div>
          <div><?php echo View::e((string) ($sessao['ended_at'] ?? '')); ?></div>
        </div>
        <div>
          <div class="texto" style="margin:0 0 6px 0;"><strong>IP</strong></div>
          <div><code><?php echo View::e((string) ($sessao['ip'] ?? '')); ?></code></div>
        </div>
      </div>

      <div style="margin-top:14px; border:1px solid #0b1220; border-radius:14px; overflow:hidden; background:#020617;">
        <div style="padding:10px 12px; border-bottom:1px solid rgba(148,163,184,.18); color:#e2e8f0; font-size:13px;">Comandos</div>
        <pre style="margin:0; padding:12px; color:#e2e8f0; font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace; font-size:13px; max-height:520px; overflow:auto;">
<?php foreach (((array) ($comandos ?? [])) as $c): ?><?php echo View::e((string) ($c['created_at'] ?? '')); ?>  $ <?php echo View::e((string) ($c['command'] ?? '')); ?>
<?php endforeach; ?><?php if (empty($comandos)): ?>Nenhum comando registrado.
<?php endif; ?></pre>
      </div>
    </div>
  </div>
</body>
</html>
