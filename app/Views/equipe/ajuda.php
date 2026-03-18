<?php

declare(strict_types=1);

$langAtual = (string) ($_GET['lang'] ?? ($_COOKIE['lang'] ?? 'pt-BR'));
$langAtual = trim($langAtual) !== '' ? trim($langAtual) : 'pt-BR';

?>
<!doctype html>
<html lang="<?php echo htmlspecialchars($langAtual, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Ajuda</title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
</head>
<body>
  <div class="topo">
    <div class="conteudo linha" style="justify-content:space-between;">
      <div>
        <div style="font-size:18px;font-weight:700;">Ajuda</div>
        <div style="opacity:.9; font-size:13px;">Configuração e operação</div>
      </div>
      <div class="linha">
        <a href="/equipe/ajuda?lang=pt-BR">PT</a>
        <a href="/equipe/ajuda?lang=en-US">EN</a>
        <a href="/equipe/ajuda?lang=es-ES">ES</a>
        <a href="/equipe/painel">Painel</a>
        <a href="/equipe/configuracoes">Configurações</a>
        <a href="/equipe/jobs">Jobs</a>
        <a href="/equipe/sair">Sair</a>
      </div>
    </div>
  </div>

  <div class="conteudo">
    <div class="card" style="max-width:980px; margin:0 auto;">
      <h1 class="titulo">Guia rápido</h1>

      <h2 class="titulo" style="font-size:16px; margin-top:16px;">1) Migrations</h2>
      <p class="texto">Depois de importar <strong>database/schema.sql</strong>, execute as migrations:</p>
      <pre style="white-space:pre-wrap; background:#0b1220; color:#e2e8f0; padding:12px; border-radius:12px; overflow:auto;">php migrar.php</pre>

      <h2 class="titulo" style="font-size:16px; margin-top:16px;">2) Worker (Jobs)</h2>
      <p class="texto">Para processar provisionamento, alertas e automações:</p>
      <pre style="white-space:pre-wrap; background:#0b1220; color:#e2e8f0; padding:12px; border-radius:12px; overflow:auto;">php worker.php</pre>
      <p class="texto">Você também pode rodar uma vez:</p>
      <pre style="white-space:pre-wrap; background:#0b1220; color:#e2e8f0; padding:12px; border-radius:12px; overflow:auto;">php worker.php --once</pre>

      <h2 class="titulo" style="font-size:16px; margin-top:16px;">3) Asaas (Billing)</h2>
      <p class="texto">Configure token, URL base, segredo do webhook e tolerância em <strong>/equipe/configuracoes</strong>.</p>
      <p class="texto">Webhook:</p>
      <pre style="white-space:pre-wrap; background:#0b1220; color:#e2e8f0; padding:12px; border-radius:12px; overflow:auto;">POST /webhooks/asaas
Header: asaas-access-token: (segredo configurado)</pre>
      <p class="texto">Para acompanhar o recebimento:</p>
      <p class="texto"><a href="/equipe/asaas-eventos">/equipe/asaas-eventos</a></p>

      <h2 class="titulo" style="font-size:16px; margin-top:16px;">4) Evolution API (WhatsApp)</h2>
      <p class="texto">Configure URL base, token (apikey), instância e número do admin em <strong>/equipe/configuracoes</strong>.</p>
      <p class="texto">Teste rápido (precisa estar logado como equipe):</p>
      <pre style="white-space:pre-wrap; background:#0b1220; color:#e2e8f0; padding:12px; border-radius:12px; overflow:auto;">POST /api/alertas/teste/enfileirar</pre>

      <h2 class="titulo" style="font-size:16px; margin-top:16px;">5) Nodes/Servidores</h2>
      <p class="texto">Cadastre nodes em <strong>/equipe/servidores</strong>. O provisionamento usa capacidade disponível (RAM/CPU/Storage) para escolher o node.</p>

      <h2 class="titulo" style="font-size:16px; margin-top:16px;">6) VPS</h2>
      <p class="texto">Equipe: <a href="/equipe/vps">/equipe/vps</a></p>
      <p class="texto">Cliente: <a href="/cliente/vps">/cliente/vps</a></p>
      <p class="texto">Logs e fila: <a href="/equipe/jobs">/equipe/jobs</a></p>
    </div>
  </div>
</body>
</html>
