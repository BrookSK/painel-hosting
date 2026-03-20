<?php

declare(strict_types=1);

use LRV\Core\I18n;
use LRV\Core\View;

?>
<!doctype html>
<html lang="<?php echo View::e(I18n::idioma()); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Ajuda — Equipe</title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
  <style>
    pre { white-space:pre-wrap; background:#0b1220; color:#e2e8f0; padding:12px 16px; border-radius:12px; overflow:auto; font-size:13px; line-height:1.6; }
    .section-title { font-size:15px; font-weight:700; color:#0B1C3D; margin:24px 0 12px; padding-bottom:6px; border-bottom:2px solid #e5e7eb; }
    .badge-cmd { display:inline-block; background:#eef2ff; color:#3730a3; padding:2px 8px; border-radius:6px; font-size:12px; font-family:monospace; }
  </style>
</head>
<body>
  <div class="topo">
    <div class="conteudo linha" style="justify-content:space-between;">
      <div>
        <div style="font-size:18px;font-weight:700;">Ajuda — Equipe</div>
        <div style="opacity:.9; font-size:13px;">Configuração e operação — v1.4.0</div>
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
      <h1 class="titulo">Guia de operação — LRV Cloud Manager v1.4.0</h1>

      <div class="section-title">🗄️ 1. Banco de dados — Migrations</div>
      <p class="texto">Após importar <code>database/schema.sql</code>, execute as migrations em ordem:</p>
      <pre>mysql -u root -p lrv_cloud &lt; database/migrations/2026_03_20_0001_*.sql
mysql -u root -p lrv_cloud &lt; database/migrations/2026_03_20_0002_*.sql
mysql -u root -p lrv_cloud &lt; database/migrations/2026_03_20_0003_*.sql
mysql -u root -p lrv_cloud &lt; database/migrations/2026_03_20_0004_*.sql
mysql -u root -p lrv_cloud &lt; database/migrations/2026_03_20_0005_*.sql
mysql -u root -p lrv_cloud &lt; database/migrations/2026_03_20_0006_*.sql
mysql -u root -p lrv_cloud &lt; database/migrations/2026_03_20_0007_chat_email.sql</pre>
      <p class="texto">Ou via painel: <a href="/equipe/inicializacao">/equipe/inicializacao</a> → "Aplicar migrations".</p>

      <div class="section-title">⚙️ 2. Worker (Jobs)</div>
      <p class="texto">Processa provisionamento de VPS, alertas e automações:</p>
      <pre>php worker.php          # loop contínuo (recomendado via systemd/supervisor)
php worker.php --once   # processa um job e sai</pre>
      <p class="texto">Acompanhe a fila em <a href="/equipe/jobs">/equipe/jobs</a>.</p>

      <div class="section-title">💻 3. Terminal WebSocket</div>
      <p class="texto">Daemon separado para terminal SSH no navegador:</p>
      <pre>php terminal-ws.php</pre>
      <p class="texto">Porta configurável em <a href="/equipe/configuracoes">Configurações</a> → <span class="badge-cmd">terminal.ws_internal_port</span> (padrão: <strong>8081</strong>).<br>
      Configure o proxy reverso: <code>/ws/terminal</code> → <code>127.0.0.1:8081</code>.</p>

      <div class="section-title">💬 4. Chat WebSocket</div>
      <p class="texto">Daemon separado para chat em tempo real com clientes:</p>
      <pre>php chat-ws.php</pre>
      <p class="texto">Porta configurável em <a href="/equipe/configuracoes">Configurações</a> → <span class="badge-cmd">chat.ws_port</span> (padrão: <strong>8082</strong>).<br>
      Configure o proxy reverso: <code>/ws/chat</code> → <code>127.0.0.1:8082</code>.<br>
      Atendimento em <a href="/equipe/chat">/equipe/chat</a>.</p>

      <div class="section-title">📧 5. E-mail (Mailcow)</div>
      <p class="texto">Configure em <a href="/equipe/configuracoes">Configurações</a> → seção "E-mail (Mailcow)":</p>
      <ul class="texto" style="padding-left:18px;">
        <li><span class="badge-cmd">email.mailcow_url</span> — URL base do Mailcow (ex: <code>https://mail.seudominio.com</code>)</li>
        <li><span class="badge-cmd">email.mailcow_key</span> — API key do Mailcow (gerada em Mailcow → API)</li>
        <li><span class="badge-cmd">email.webmail_url</span> — URL do webmail (fallback global; por padrão usa <code>https://webmail.{dominio}</code>)</li>
      </ul>
      <p class="texto">Clientes gerenciam e-mails em <a href="/cliente/emails">/cliente/emails</a>.</p>

      <div class="section-title">💳 6. Billing — Asaas (BRL)</div>
      <p class="texto">Configure token, URL base, segredo do webhook e tolerância em <a href="/equipe/configuracoes">Configurações</a>.</p>
      <pre>Webhook endpoint: POST /webhooks/asaas
Header obrigatório: asaas-access-token: {segredo configurado}</pre>
      <p class="texto">Acompanhe eventos em <a href="/equipe/asaas-eventos">/equipe/asaas-eventos</a>.</p>

      <div class="section-title">💳 7. Billing — Stripe (USD)</div>
      <pre>Webhook endpoint: POST /webhooks/stripe</pre>
      <p class="texto">Configure <span class="badge-cmd">stripe.secret_key</span> e <span class="badge-cmd">stripe.webhook_secret</span> em <a href="/equipe/configuracoes">Configurações</a>.</p>

      <div class="section-title">📱 8. WhatsApp (Evolution API)</div>
      <p class="texto">Configure URL base, token (apikey), instância e número do admin em <a href="/equipe/configuracoes">Configurações</a>.</p>
      <pre>POST /api/alertas/teste/enfileirar   # teste de envio (requer login equipe)</pre>

      <div class="section-title">🖥️ 9. Nodes / Servidores</div>
      <p class="texto">Cadastre nodes em <a href="/equipe/servidores">/equipe/servidores</a>. O provisionamento seleciona automaticamente o node com mais capacidade disponível (CPU/RAM/Storage abaixo do limite configurado em <span class="badge-cmd">infra.node_max_util_percent</span>).</p>

      <div class="section-title">👥 10. Usuários e Permissões</div>
      <p class="texto">Gerencie usuários da equipe em <a href="/equipe/usuarios">/equipe/usuarios</a>.<br>
      Configure permissões por role em <a href="/equipe/permissoes">/equipe/permissoes</a>.<br>
      2FA (TOTP) disponível para cada usuário em <a href="/equipe/2fa/configurar">/equipe/2fa/configurar</a>.</p>

      <div class="section-title">🔒 11. Segurança</div>
      <ul class="texto" style="padding-left:18px;">
        <li>CSRF em todos os formulários POST</li>
        <li>Rate limiting por IP, cliente e equipe</li>
        <li>Bloqueio de IP após 10 tentativas de login falhas em 30 min</li>
        <li>2FA (TOTP RFC 6238) para equipe</li>
        <li>Tokens de terminal e chat de uso único (SHA-256, TTL curto)</li>
        <li>Validação de propriedade (IDOR) — retorna 403 em todos os endpoints sensíveis</li>
        <li>Replay attack prevention nos webhooks Asaas e Stripe</li>
        <li>Logs de autenticação em <code>auth_logs</code></li>
        <li>Logs de aplicação em <code>storage/logs/app-YYYY-MM-DD.log</code></li>
      </ul>

    </div>
  </div>
  <?php require __DIR__ . '/../_partials/footer.php'; ?>
</body>
</html>
