<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\I18n;

$pageTitle = I18n::t('equipe.ajuda');
require __DIR__ . '/../_partials/layout-equipe-inicio.php';
?>
<div class="page-title"><?php echo View::e(I18n::t('equipe.ajuda')); ?></div>
<div class="page-subtitle"><?php echo View::e(I18n::t('eq_ajuda.subtitulo')); ?> — v<?php echo View::e(\LRV\Core\SistemaConfig::versao()); ?></div>

<style>
pre{white-space:pre-wrap;background:#0b1220;color:#e2e8f0;padding:12px 16px;border-radius:12px;overflow:auto;font-size:13px;line-height:1.6;}
.section-title{font-size:15px;font-weight:700;color:#0f172a;margin:24px 0 12px;padding-bottom:6px;border-bottom:2px solid #e2e8f0;}
.badge-cmd{display:inline-block;background:#eef2ff;color:#3730a3;padding:2px 8px;border-radius:6px;font-size:12px;font-family:monospace;}
</style>

<div class="card-new" style="max-width:980px;">

  <div class="section-title">1. Banco de dados — Migrations</div>
  <p style="margin-bottom:10px;color:#475569;font-size:14px;">Após importar <code>database/schema.sql</code>, execute as migrations em ordem:</p>
  <pre>mysql -u root -p lrv_cloud &lt; database/migrations/2026_03_20_0001_*.sql
...
mysql -u root -p lrv_cloud &lt; database/migrations/2026_03_21_0014_password_resets.sql
mysql -u root -p lrv_cloud &lt; database/migrations/2026_03_21_0024_client_address.sql
mysql -u root -p lrv_cloud &lt; database/migrations/2026_03_21_0025_client_totp.sql
mysql -u root -p lrv_cloud &lt; database/migrations/2026_03_21_0026_legal_defaults.sql
mysql -u root -p lrv_cloud &lt; database/migrations/2026_03_21_0026b_legal_force.sql
mysql -u root -p lrv_cloud &lt; database/migrations/2026_03_21_0026c_license_content.sql
mysql -u root -p lrv_cloud &lt; database/migrations/2026_03_21_0027_chat_files.sql
mysql -u root -p lrv_cloud &lt; database/migrations/2026_03_21_0028_app_templates.sql</pre>
  <p style="font-size:13px;color:#64748b;margin-top:8px;">Ou via painel: <a href="/equipe/inicializacao">/equipe/inicializacao</a> → "Aplicar migrations".</p>

  <div class="section-title">2. Worker (Jobs)</div>
  <pre>php worker.php          # loop contínuo (recomendado via systemd/supervisor)
php worker.php --once   # processa um job e sai</pre>
  <p style="font-size:13px;color:#64748b;">Acompanhe a fila em <a href="/equipe/jobs">/equipe/jobs</a>.</p>

  <div class="section-title">3. Terminal WebSocket</div>
  <pre>php terminal-ws.php</pre>
  <p style="font-size:13px;color:#64748b;">Porta configurável em <a href="/equipe/configuracoes">Configurações</a> → <span class="badge-cmd">terminal.ws_internal_port</span> (padrão: <strong>8081</strong>).<br>Configure o proxy reverso: <code>/ws/terminal</code> → <code>127.0.0.1:8081</code>.</p>

  <div class="section-title">4. Chat WebSocket</div>
  <pre>php chat-ws.php</pre>
  <p style="font-size:13px;color:#64748b;">Porta configurável em <a href="/equipe/configuracoes">Configurações</a> → <span class="badge-cmd">chat.ws_port</span> (padrão: <strong>8082</strong>).<br>Configure o proxy reverso: <code>/ws/chat</code> → <code>127.0.0.1:8082</code>.<br>Atendimento em <a href="/equipe/chat">/equipe/chat</a>.</p>

  <div class="section-title">5. E-mail (Mailcow)</div>
  <ul style="padding-left:18px;font-size:14px;color:#475569;line-height:2;">
    <li><span class="badge-cmd">email.mailcow_url</span> — URL base do Mailcow</li>
    <li><span class="badge-cmd">email.mailcow_key</span> — API key do Mailcow</li>
    <li><span class="badge-cmd">email.webmail_url</span> — URL do webmail (fallback global)</li>
  </ul>

  <div class="section-title">6. Billing — Asaas (BRL)</div>
  <pre>Webhook endpoint: POST /webhooks/asaas
Header obrigatório: asaas-access-token: {segredo configurado}</pre>
  <p style="font-size:13px;color:#64748b;">Acompanhe eventos em <a href="/equipe/asaas-eventos">/equipe/asaas-eventos</a>.</p>

  <div class="section-title">7. Billing — Stripe (USD)</div>
  <pre>Webhook endpoint: POST /webhooks/stripe</pre>

  <div class="section-title">8. Nodes / Servidores</div>
  <p style="font-size:14px;color:#475569;">Cadastre nodes em <a href="/equipe/servidores">/equipe/servidores</a>. O provisionamento seleciona automaticamente o node com mais capacidade disponível.</p>

  <div class="section-title">9. Usuários e Permissões</div>
  <p style="font-size:14px;color:#475569;">
    Gerencie usuários em <a href="/equipe/usuarios">/equipe/usuarios</a>.<br>
    Configure permissões por role em <a href="/equipe/permissoes">/equipe/permissoes</a>.<br>
    2FA (TOTP) disponível em <a href="/equipe/2fa/configurar">/equipe/2fa/configurar</a>.<br>
    Cada membro pode editar seu perfil e foto em <a href="/equipe/minha-conta">/equipe/minha-conta</a>.
  </p>

  <div class="section-title">10. Reset de senha</div>
  <p style="font-size:14px;color:#475569;">
    Equipe: <a href="/equipe/reset-senha">/equipe/reset-senha</a><br>
    Cliente: <a href="/cliente/reset-senha">/cliente/reset-senha</a><br>
    O link enviado por e-mail expira em <strong>1 hora</strong>. Configure <span class="badge-cmd">app_url</span> nas settings para que o link gerado aponte para o domínio correto.
  </p>

  <div class="section-title">11. Branding e Páginas Legais</div>
  <ul style="padding-left:18px;font-size:14px;color:#475569;line-height:2;">
    <li><span class="badge-cmd">system.name</span> — nome do sistema</li>
    <li><span class="badge-cmd">system.logo_url</span> — URL do logotipo</li>
    <li><span class="badge-cmd">system.favicon_url</span> — URL do favicon</li>
    <li><span class="badge-cmd">app_url</span> — URL base (usada nos e-mails de reset de senha)</li>
    <li><span class="badge-cmd">legal.terms_html</span> — HTML dos Termos de Uso (<a href="/termos">/termos</a>)</li>
    <li><span class="badge-cmd">legal.privacy_html</span> — HTML da Política de Privacidade (<a href="/privacidade">/privacidade</a>)</li>
  </ul>

  <div class="section-title">12. 2FA para clientes</div>
  <p style="font-size:14px;color:#475569;">
    Clientes podem ativar autenticação de dois fatores em <a href="/cliente/2fa/configurar">/cliente/2fa/configurar</a>.<br>
    Requer migration <span class="badge-cmd">0025_client_totp.sql</span> (tabela <code>client_totp</code>).<br>
    Quando ativo, o cliente é redirecionado para <a href="/cliente/2fa/verificar">/cliente/2fa/verificar</a> após o login.
  </p>

  <div class="section-title">13. Endereço do cliente</div>
  <p style="font-size:14px;color:#475569;">
    Campos de endereço disponíveis em <a href="/cliente/minha-conta">/cliente/minha-conta</a>.<br>
    Requer migration <span class="badge-cmd">0024_client_address.sql</span> (colunas <code>address_*</code> em <code>clients</code>).
  </p>

  <div class="section-title">14. Chat — Recursos avançados</div>
  <ul style="padding-left:18px;font-size:14px;color:#475569;line-height:2;">
    <li>Emoji picker e upload de arquivos (imagens, PDF, DOC, TXT até 5 MB) em todos os locais de chat</li>
    <li>Polling HTTP fallback automático — tenta WebSocket 2x, depois cai para polling a cada 3s</li>
    <li>Nome do agente exibido nas respostas da equipe (ex: "João · 16:21")</li>
    <li>Separadores visuais de dia entre mensagens de datas diferentes</li>
    <li>Pesquisa de satisfação enviada por e-mail ao encerrar um chat</li>
    <li>Histórico de chats abertos e encerrados em <a href="/equipe/chat">/equipe/chat</a></li>
    <li><span class="badge-cmd">chat.ws_url</span> — URL pública do WebSocket (ex: <code>wss://seudominio.com/ws/chat</code>)</li>
  </ul>
  <p style="font-size:13px;color:#64748b;">Requer migration <span class="badge-cmd">0027_chat_files.sql</span> (tabela <code>chat_files</code>).</p>

  <div class="section-title">15. Aplicações pré-configuradas (One-Click Install)</div>
  <p style="font-size:14px;color:#475569;">
    Catálogo visual em <a href="/cliente/aplicacoes/catalogo">/cliente/aplicacoes/catalogo</a> com 7 templates prontos.<br>
    O cliente seleciona VPS, domínio (se necessário), repositório (se necessário) e variáveis de ambiente.<br>
    A instalação é feita via job assíncrono (<code>install_app_template</code>): pull de imagem, clone de repo, criação de container Docker com labels.
  </p>
  <ul style="padding-left:18px;font-size:14px;color:#475569;line-height:2;">
    <li>Templates: WordPress, Node.js, PHP Laravel, MySQL, Redis, Nginx, Site Estático</li>
    <li>Auto-assign de porta (evita conflitos)</li>
    <li>Labels Docker: <code>lrv.app_id</code>, <code>lrv.client_id</code></li>
    <li>Status: <code>installing</code>, <code>running</code>, <code>stopped</code>, <code>error</code></li>
  </ul>
  <p style="font-size:13px;color:#64748b;">Requer migration <span class="badge-cmd">0028_app_templates.sql</span> (tabela <code>app_templates</code> + colunas extras em <code>applications</code>).</p>

  <div class="section-title">16. Segurança</div>
  <ul style="padding-left:18px;font-size:14px;color:#475569;line-height:2;">
    <li>CSRF em todos os formulários POST</li>
    <li>Rate limiting por IP, cliente e equipe</li>
    <li>Bloqueio de IP após 10 tentativas de login falhas em 30 min</li>
    <li>2FA (TOTP RFC 6238) para equipe e clientes</li>
    <li>Tokens de terminal e chat de uso único (SHA-256, TTL curto)</li>
    <li>Reset de senha via token único com expiração de 1 hora</li>
    <li>Validação de propriedade (IDOR) — retorna 403 em todos os endpoints sensíveis</li>
    <li>Replay attack prevention nos webhooks Asaas e Stripe</li>
  </ul>

</div>
<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
