<?php
declare(strict_types=1);
use LRV\Core\View;

$pageTitle = 'Ajuda';
require __DIR__ . '/../_partials/layout-equipe-inicio.php';
?>
<div class="page-title">Ajuda</div>
<div class="page-subtitle">Configuracao e operacao — v1.4.0</div>

<style>
pre{white-space:pre-wrap;background:#0b1220;color:#e2e8f0;padding:12px 16px;border-radius:12px;overflow:auto;font-size:13px;line-height:1.6;}
.section-title{font-size:15px;font-weight:700;color:#0B1C3D;margin:24px 0 12px;padding-bottom:6px;border-bottom:2px solid #e5e7eb;}
.badge-cmd{display:inline-block;background:#eef2ff;color:#3730a3;padding:2px 8px;border-radius:6px;font-size:12px;font-family:monospace;}
</style>

<div class="card-new" style="max-width:980px;">
  <h1 class="titulo">Guia de operacao — LRV Cloud Manager v1.4.0</h1>

  <div class="section-title">1. Banco de dados — Migrations</div>
  <p class="texto">Apos importar <code>database/schema.sql</code>, execute as migrations em ordem:</p>
  <pre>mysql -u root -p lrv_cloud &lt; database/migrations/2026_03_20_0001_*.sql
mysql -u root -p lrv_cloud &lt; database/migrations/2026_03_20_0002_*.sql
mysql -u root -p lrv_cloud &lt; database/migrations/2026_03_20_0003_*.sql
mysql -u root -p lrv_cloud &lt; database/migrations/2026_03_20_0004_*.sql
mysql -u root -p lrv_cloud &lt; database/migrations/2026_03_20_0005_*.sql
mysql -u root -p lrv_cloud &lt; database/migrations/2026_03_20_0006_*.sql
mysql -u root -p lrv_cloud &lt; database/migrations/2026_03_20_0007_chat_email.sql
mysql -u root -p lrv_cloud &lt; database/migrations/2026_03_20_0008_branding_legal.sql
mysql -u root -p lrv_cloud &lt; database/migrations/2026_03_20_0009_seed_permissions.sql</pre>
  <p class="texto">Ou via painel: <a href="/equipe/inicializacao">/equipe/inicializacao</a> &rarr; "Aplicar migrations".</p>

  <div class="section-title">2. Worker (Jobs)</div>
  <pre>php worker.php          # loop continuo (recomendado via systemd/supervisor)
php worker.php --once   # processa um job e sai</pre>
  <p class="texto">Acompanhe a fila em <a href="/equipe/jobs">/equipe/jobs</a>.</p>

  <div class="section-title">3. Terminal WebSocket</div>
  <pre>php terminal-ws.php</pre>
  <p class="texto">Porta configuravel em <a href="/equipe/configuracoes">Configuracoes</a> &rarr; <span class="badge-cmd">terminal.ws_internal_port</span> (padrao: <strong>8081</strong>).<br>Configure o proxy reverso: <code>/ws/terminal</code> &rarr; <code>127.0.0.1:8081</code>.</p>

  <div class="section-title">4. Chat WebSocket</div>
  <pre>php chat-ws.php</pre>
  <p class="texto">Porta configuravel em <a href="/equipe/configuracoes">Configuracoes</a> &rarr; <span class="badge-cmd">chat.ws_port</span> (padrao: <strong>8082</strong>).<br>Configure o proxy reverso: <code>/ws/chat</code> &rarr; <code>127.0.0.1:8082</code>.<br>Atendimento em <a href="/equipe/chat">/equipe/chat</a>.</p>

  <div class="section-title">5. E-mail (Mailcow)</div>
  <ul class="texto" style="padding-left:18px;">
    <li><span class="badge-cmd">email.mailcow_url</span> — URL base do Mailcow</li>
    <li><span class="badge-cmd">email.mailcow_key</span> — API key do Mailcow</li>
    <li><span class="badge-cmd">email.webmail_url</span> — URL do webmail</li>
  </ul>

  <div class="section-title">6. Billing — Asaas (BRL)</div>
  <pre>Webhook endpoint: POST /webhooks/asaas
Header obrigatorio: asaas-access-token: {segredo configurado}</pre>
  <p class="texto">Acompanhe eventos em <a href="/equipe/asaas-eventos">/equipe/asaas-eventos</a>.</p>

  <div class="section-title">7. Billing — Stripe (USD)</div>
  <pre>Webhook endpoint: POST /webhooks/stripe</pre>

  <div class="section-title">8. WhatsApp (Evolution API)</div>
  <pre>POST /api/alertas/teste/enfileirar</pre>

  <div class="section-title">9. Nodes / Servidores</div>
  <p class="texto">Cadastre nodes em <a href="/equipe/servidores">/equipe/servidores</a>. O provisionamento seleciona automaticamente o node com mais capacidade disponivel.</p>

  <div class="section-title">10. Usuarios e Permissoes</div>
  <p class="texto">Gerencie usuarios em <a href="/equipe/usuarios">/equipe/usuarios</a>.<br>Configure permissoes por role em <a href="/equipe/permissoes">/equipe/permissoes</a>.<br>2FA (TOTP) disponivel em <a href="/equipe/2fa/configurar">/equipe/2fa/configurar</a>.</p>

  <div class="section-title">11. Branding e Paginas Legais</div>
  <ul class="texto" style="padding-left:18px;">
    <li><span class="badge-cmd">system.name</span> — nome do sistema</li>
    <li><span class="badge-cmd">system.logo_url</span> — URL do logotipo</li>
    <li><span class="badge-cmd">system.favicon_url</span> — URL do favicon</li>
    <li><span class="badge-cmd">legal.terms_html</span> — HTML dos Termos de Uso (<a href="/termos">/termos</a>)</li>
    <li><span class="badge-cmd">legal.privacy_html</span> — HTML da Politica de Privacidade (<a href="/privacidade">/privacidade</a>)</li>
  </ul>

  <div class="section-title">12. Seguranca</div>
  <ul class="texto" style="padding-left:18px;">
    <li>CSRF em todos os formularios POST</li>
    <li>Rate limiting por IP, cliente e equipe</li>
    <li>Bloqueio de IP apos 10 tentativas de login falhas em 30 min</li>
    <li>2FA (TOTP RFC 6238) para equipe</li>
    <li>Tokens de terminal e chat de uso unico (SHA-256, TTL curto)</li>
    <li>Validacao de propriedade (IDOR) — retorna 403 em todos os endpoints sensiveis</li>
    <li>Replay attack prevention nos webhooks Asaas e Stripe</li>
  </ul>
</div>
<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
