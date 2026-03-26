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
mysql -u root -p lrv_cloud &lt; database/migrations/2026_03_21_0028_app_templates.sql
mysql -u root -p lrv_cloud &lt; database/migrations/2026_03_21_0029_cookie_consents.sql
mysql -u root -p lrv_cloud &lt; database/migrations/2026_03_24_0030_plan_backup_slots.sql
mysql -u root -p lrv_cloud &lt; database/migrations/2026_03_24_0031_domain_webmail_subdomain.sql
mysql -u root -p lrv_cloud &lt; database/migrations/2026_03_24_0032_roundcube_template.sql
mysql -u root -p lrv_cloud &lt; database/migrations/2026_03_24_0033_client_webmail_app.sql</pre>
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
    <li><span class="badge-cmd">email.mailcow_url</span> — URL base do Mailcow (ex: <code>https://correio.seudominio.com</code>)</li>
    <li><span class="badge-cmd">email.mailcow_key</span> — API key Read-Write do Mailcow</li>
    <li><span class="badge-cmd">email.webmail_url</span> — URL do webmail SOGo (fallback global)</li>
    <li><span class="badge-cmd">email.default_domain</span> — Domínio padrão para clientes sem domínio próprio</li>
    <li><span class="badge-cmd">email.default_total_quota_mb</span> — Cota total padrão de e-mail por cliente (5120 MB = 5 GB)</li>
  </ul>
  <p style="font-size:14px;color:#475569;margin-top:8px;">O cliente pode ativar webmail personalizado (<code>webmail.seudominio.com</code>) via CNAME. Também pode instalar o Roundcube como alternativa ao SOGo pelo catálogo de aplicações.</p>

  <div class="section-title">5.1 Monitoramento do servidor de e-mail</div>
  <p style="font-size:14px;color:#475569;">Configure em <a href="/equipe/configuracoes">Configurações</a> → "Servidor de E-mail (Monitoramento)". Preencha o IP, SSH e limites de alerta. Quando CPU, RAM ou disco ultrapassam os limites, o admin recebe alerta por e-mail e WhatsApp (máximo 1 a cada 30 min).</p>

  <div class="section-title">6. Billing — Asaas (BRL)</div>
  <p style="font-size:14px;color:#475569;">Campos separados para <strong>Sandbox</strong> e <strong>Produção</strong>. Selecione o ambiente ativo no seletor. As keys legadas são atualizadas automaticamente ao salvar.</p>
  <p style="font-size:13px;color:#475569;">Para clientes em BRL (pt-BR), todos os métodos de pagamento (PIX, Boleto e Cartão) são processados via Asaas.</p>
  <pre>Webhook endpoint: POST /webhooks/asaas
Header obrigatório: asaas-access-token: {segredo configurado}</pre>
  <p style="font-size:13px;color:#64748b;">Acompanhe eventos em <a href="/equipe/asaas-eventos">/equipe/asaas-eventos</a>.</p>

  <div class="section-title">7. Billing — Stripe (USD)</div>
  <p style="font-size:14px;color:#475569;">Campos separados para <strong>Sandbox (Test)</strong> e <strong>Produção (Live)</strong>. Selecione o ambiente ativo no seletor.</p>
  <p style="font-size:13px;color:#475569;">Para clientes em USD (en-US/es-ES), o pagamento é feito exclusivamente via Stripe (cartão).</p>
  <pre>Webhook endpoint: POST /webhooks/stripe</pre>

  <div class="section-title">8. Nodes / Servidores</div>
  <p style="font-size:14px;color:#475569;">Cadastre nodes em <a href="/equipe/servidores">/equipe/servidores</a>. O provisionamento seleciona automaticamente o node com mais capacidade disponível.</p>
  <p style="font-size:14px;color:#475569;">A autenticação SSH suporta dois modos: <strong>chave privada</strong> (upload de arquivo .pem/id_rsa) ou <strong>usuário e senha</strong> (via ext-ssh2 ou pseudo-terminal, sem necessidade de sshpass). Ao fazer upload, o arquivo é salvo no diretório configurado em <code>infra.ssh_key_dir</code> com permissão 600.</p>

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
    <li>Templates: WordPress, Node.js, PHP Laravel, MySQL, Redis, Nginx, Site Estático, <strong>Roundcube Webmail</strong></li>
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

  <div class="section-title">17. Cookies e Consentimento (LGPD)</div>
  <p style="font-size:14px;color:#475569;">
    Banner de cookies exibido automaticamente na primeira visita. O usuário pode aceitar todos, rejeitar opcionais ou configurar preferências granulares (necessários, analytics, marketing, preferências).
  </p>
  <ul style="padding-left:18px;font-size:14px;color:#475569;line-height:2;">
    <li>Persistência no navegador (cookie 12 meses) e no banco de dados (usuários logados)</li>
    <li>Endpoints: <span class="badge-cmd">POST /cookies/consent</span> e <span class="badge-cmd">GET /cookies/consent</span></li>
    <li>Função JS <span class="badge-cmd">ckTemPermissao('analytics')</span> para bloqueio condicional de scripts</li>
    <li>Link "Cookies" no footer abre modal de preferências</li>
    <li>Seção de cookies integrada à página <a href="/privacidade">/privacidade</a></li>
  </ul>
  <p style="font-size:13px;color:#64748b;">Requer migration <span class="badge-cmd">0029_cookie_consents.sql</span>.</p>

  <div class="section-title">18. Landing Pages de Soluções</div>
  <p style="font-size:14px;color:#475569;">
    5 páginas de alta conversão em <code>/solucoes/</code>: VPS, Aplicações, DevOps, E-mail, Segurança.<br>
    Layout compartilhado com 8 seções (hero, problema, solução, benefícios, como funciona, prova social, CTA, FAQ).<br>
    Copywriting profissional nos 3 idiomas, focado em decisores.
  </p>

  <div class="section-title">19. Mega Menu</div>
  <p style="font-size:14px;color:#475569;">
    Navbar pública com mega menus "Produtos" (4 colunas) e "Recursos" (4 colunas).<br>
    Desktop: hover com fade-in. Mobile: drawer com accordion.<br>
    Links apontam para landing pages dedicadas em <code>/solucoes/</code>.
  </p>

  <div class="section-title">20. Internacionalização</div>
  <p style="font-size:14px;color:#475569;">
    Sistema traduzido em 3 idiomas: Português (pt-BR), Inglês (en-US), Espanhol (es-ES).<br>
    Seletor de idioma disponível na navbar pública e nos painéis.<br>
    Todas as strings usam <span class="badge-cmd">I18n::t('chave')</span> nas views.
  </p>

  <div class="section-title">21. Sessão e Redirect</div>
  <p style="font-size:14px;color:#475569;">
    Sessão dura <strong>24 horas</strong> de inatividade (equipe e cliente).<br>
    Ao acessar uma URL protegida sem login, o sistema salva a URL e redireciona de volta após autenticação (funciona com 2FA).<br>
    Cookie de sessão com <code>lifetime: 86400</code>, <code>Secure</code>, <code>HttpOnly</code>, <code>SameSite=Lax</code>.
  </p>

  <div class="section-title">22. Backup por plano</div>
  <p style="font-size:14px;color:#475569;">
    Coluna <code>backup_slots</code> na tabela <code>plans</code> (0, 1 ou 2 backups).<br>
    Rotação automática: ao criar um novo backup, o mais antigo é removido se o limite for atingido.<br>
    Suporte a autenticação SSH por senha no backup (sem sshpass).
  </p>

  <div class="section-title">23. Planos e Checkout</div>
  <p style="font-size:14px;color:#475569;">
  Cadastre planos em <a href="/equipe/planos">/equipe/planos</a>. Campos visuais para limites (e-mail, cota, domínios, apps, SLA).<br>
  Marque <strong>Destaque</strong> para exibir badge "POPULAR" na landing page.<br>
  O Stripe Price ID é gerado automaticamente ao salvar (se Stripe configurado).<br>
  Addons (ex: Backup diário, Suporte WhatsApp) são cobrados no checkout — o cliente seleciona antes de pagar.
</p>

<div class="section-title">24. Inicialização parcial de servidores</div>
<p style="font-size:14px;color:#475569;">
  Em <a href="/equipe/servidores">/equipe/servidores</a> → Editar, a seção "Inicialização parcial" lista os 20 passos individualmente.<br>
  Cada passo tem badge de risco: <strong>Sem risco</strong> (verde), <strong>Risco baixo</strong> (amarelo), <strong>Risco alto</strong> (vermelho).<br>
  O UFW (firewall) é marcado como <strong>Risco alto</strong> — não rode em servidores com serviços existentes.<br>
  Passos essenciais: Docker, redes Docker, diretório /vps, usuário terminal, ForceCommand.
</p>

</div>
<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
