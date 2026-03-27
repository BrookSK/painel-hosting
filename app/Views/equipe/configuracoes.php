<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\SistemaConfig;
use LRV\Core\I18n;

$pageTitle = I18n::t('eq_config.titulo');
require __DIR__ . '/../_partials/layout-equipe-inicio.php';
?>
<div class="page-title"><?php echo View::e(I18n::t('eq_config.titulo')); ?></div>
<div class="page-subtitle"><?php echo View::e(I18n::t('eq_config.subtitulo')); ?></div>

<div class="card-new" style="max-width:920px;">
  <p class="texto"><?php echo I18n::t('eq_config.desc_banco'); ?></p>

  <?php if (!empty($erro)): ?><div class="erro"><?php echo View::e((string)$erro); ?></div><?php endif; ?>
  <?php if (!empty($salvo)): ?><div class="sucesso"><?php echo View::e(I18n::t('eq_config.salvas')); ?></div><?php endif; ?>

  <form method="post" action="/equipe/configuracoes">
    <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />

    <h2 class="titulo" style="font-size:16px;margin-bottom:12px;">Asaas</h2>
    <div style="margin-bottom:12px;">
      <label style="display:block;font-size:13px;margin-bottom:6px;">Ambiente ativo</label>
      <select class="input" name="asaas_mode" style="max-width:200px;">
        <option value="sandbox" <?php echo ((string)($asaas_mode??'sandbox'))==='sandbox'?'selected':''; ?>>🧪 Sandbox</option>
        <option value="production" <?php echo ((string)($asaas_mode??'sandbox'))==='production'?'selected':''; ?>>🟢 Produção</option>
      </select>
    </div>
    <div style="border:1px solid #fde68a;background:#fffbeb;border-radius:10px;padding:14px;margin-bottom:12px;">
      <div style="font-weight:600;font-size:13px;margin-bottom:8px;">🧪 Sandbox</div>
      <div class="grid">
        <div>
          <label style="display:block;font-size:13px;margin-bottom:6px;">Token (Sandbox)</label>
          <input class="input" type="password" name="asaas_token_sandbox" value="<?php echo View::e((string)($asaas_token_sandbox??'')); ?>" />
        </div>
        <div>
          <label style="display:block;font-size:13px;margin-bottom:6px;">URL base (Sandbox)</label>
          <input class="input" type="text" name="asaas_url_base_sandbox" value="<?php echo View::e((string)($asaas_url_base_sandbox??'https://sandbox.asaas.com/api/v3')); ?>" />
        </div>
      </div>
      <div style="margin-top:8px;">
        <label style="display:block;font-size:13px;margin-bottom:6px;">Segredo webhook (Sandbox)</label>
        <input class="input" type="text" name="asaas_webhook_segredo_sandbox" value="<?php echo View::e((string)($asaas_webhook_segredo_sandbox??'')); ?>" />
      </div>
    </div>
    <div style="border:1px solid #bbf7d0;background:#f0fdf4;border-radius:10px;padding:14px;margin-bottom:12px;">
      <div style="font-weight:600;font-size:13px;margin-bottom:8px;">🟢 Produção</div>
      <div class="grid">
        <div>
          <label style="display:block;font-size:13px;margin-bottom:6px;">Token (Produção)</label>
          <input class="input" type="password" name="asaas_token_production" value="<?php echo View::e((string)($asaas_token_production??'')); ?>" />
        </div>
        <div>
          <label style="display:block;font-size:13px;margin-bottom:6px;">URL base (Produção)</label>
          <input class="input" type="text" name="asaas_url_base_production" value="<?php echo View::e((string)($asaas_url_base_production??'https://api.asaas.com/v3')); ?>" />
        </div>
      </div>
      <div style="margin-top:8px;">
        <label style="display:block;font-size:13px;margin-bottom:6px;">Segredo webhook (Produção)</label>
        <input class="input" type="text" name="asaas_webhook_segredo_production" value="<?php echo View::e((string)($asaas_webhook_segredo_production??'')); ?>" />
      </div>
    </div>
    <div class="grid">
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Tolerancia de inadimplencia (dias)</label>
        <input class="input" type="number" name="tolerancia_dias" value="<?php echo View::e((string)($tolerancia_dias??'3')); ?>" min="1" />
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Desconto semestral (%)</label>
        <input class="input" type="number" name="desconto_6m" value="<?php echo View::e((string)($desconto_6m??'5')); ?>" min="0" max="50" step="1" />
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Desconto anual (%)</label>
        <input class="input" type="number" name="desconto_12m" value="<?php echo View::e((string)($desconto_12m??'10')); ?>" min="0" max="50" step="1" />
      </div>
    </div>
    <p class="texto" style="font-size:13px;margin-top:8px;">Endpoint: <strong>/webhooks/asaas</strong></p>
    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:10px 14px;margin-top:8px;">
      <div style="font-size:12px;font-weight:600;color:#475569;margin-bottom:6px;">Eventos que o sistema processa:</div>
      <div style="font-size:12px;color:#64748b;font-family:monospace;line-height:2;">
        <code>PAYMENT_CONFIRMED</code> — pagamento confirmado → ativa VPS<br>
        <code>PAYMENT_RECEIVED</code> — pagamento recebido → ativa VPS<br>
        <code>PAYMENT_OVERDUE</code> — pagamento atrasado → suspende VPS após tolerância<br>
        <code>SUBSCRIPTION_CANCELED</code> — assinatura cancelada → suspende VPS<br>
        <code>SUBSCRIPTION_DELETED</code> — assinatura removida → suspende VPS<br>
        <code>SUBSCRIPTION_INACTIVATED</code> — assinatura inativada → suspende VPS
      </div>
      <p style="font-size:11px;color:#94a3b8;margin-top:6px;">No painel do Asaas, marque esses 6 eventos ao criar o webhook.</p>
    </div>

    <h2 class="titulo" style="font-size:16px;margin:20px 0 12px;">Stripe</h2>
    <div style="margin-bottom:12px;">
      <label style="display:block;font-size:13px;margin-bottom:6px;">Ambiente ativo</label>
      <select class="input" name="stripe_mode" style="max-width:200px;">
        <option value="sandbox" <?php echo ((string)($stripe_mode??'sandbox'))==='sandbox'?'selected':''; ?>>🧪 Sandbox (Test)</option>
        <option value="production" <?php echo ((string)($stripe_mode??'sandbox'))==='production'?'selected':''; ?>>🟢 Produção (Live)</option>
      </select>
    </div>
    <div style="border:1px solid #fde68a;background:#fffbeb;border-radius:10px;padding:14px;margin-bottom:12px;">
      <div style="font-weight:600;font-size:13px;margin-bottom:8px;">🧪 Sandbox (Test)</div>
      <div class="grid">
        <div>
          <label style="display:block;font-size:13px;margin-bottom:6px;">Secret Key (Test)</label>
          <input class="input" type="password" name="stripe_secret_key_sandbox" value="<?php echo View::e((string)($stripe_secret_key_sandbox??'')); ?>" placeholder="sk_test_..." />
        </div>
        <div>
          <label style="display:block;font-size:13px;margin-bottom:6px;">Webhook Secret (Test)</label>
          <input class="input" type="text" name="stripe_webhook_secret_sandbox" value="<?php echo View::e((string)($stripe_webhook_secret_sandbox??'')); ?>" placeholder="whsec_..." />
        </div>
      </div>
    </div>
    <div style="border:1px solid #bbf7d0;background:#f0fdf4;border-radius:10px;padding:14px;margin-bottom:12px;">
      <div style="font-weight:600;font-size:13px;margin-bottom:8px;">🟢 Produção (Live)</div>
      <div class="grid">
        <div>
          <label style="display:block;font-size:13px;margin-bottom:6px;">Secret Key (Live)</label>
          <input class="input" type="password" name="stripe_secret_key_production" value="<?php echo View::e((string)($stripe_secret_key_production??'')); ?>" placeholder="sk_live_..." />
        </div>
        <div>
          <label style="display:block;font-size:13px;margin-bottom:6px;">Webhook Secret (Live)</label>
          <input class="input" type="text" name="stripe_webhook_secret_production" value="<?php echo View::e((string)($stripe_webhook_secret_production??'')); ?>" placeholder="whsec_..." />
        </div>
      </div>
    </div>
    <p class="texto" style="font-size:13px;">Endpoint: <strong>/webhooks/stripe</strong></p>
    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:10px 14px;margin-top:8px;">
      <div style="font-size:12px;font-weight:600;color:#475569;margin-bottom:6px;">Eventos que o sistema processa:</div>
      <div style="font-size:12px;color:#64748b;font-family:monospace;line-height:2;">
        <code>checkout.session.completed</code> — checkout concluído → ativa assinatura<br>
        <code>invoice.paid</code> — fatura paga → ativa/reativa VPS<br>
        <code>invoice.payment_failed</code> — pagamento falhou → suspende VPS após tolerância<br>
        <code>customer.subscription.created</code> — assinatura criada<br>
        <code>customer.subscription.updated</code> — assinatura atualizada<br>
        <code>customer.subscription.deleted</code> — assinatura cancelada → suspende VPS
      </div>
      <p style="font-size:11px;color:#94a3b8;margin-top:6px;">No dashboard do Stripe → Developers → Webhooks, marque esses 6 eventos.</p>
    </div>

    <h2 class="titulo" style="font-size:16px;margin:20px 0 12px;">Conversão de Moeda</h2>
    <div class="grid">
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Taxa de conversão BRL → USD</label>
        <input class="input" type="text" name="taxa_conversao_usd" value="<?php echo View::e((string)($taxa_conversao_usd??'5.0')); ?>" placeholder="5.00" style="max-width:160px;" />
        <p class="texto" style="font-size:12px;color:#64748b;margin-top:6px;">Ex: 5.00 = R$ 100 será exibido como $ 20.00 para clientes em EN/ES. Também usado na cobrança via Stripe.</p>
      </div>
    </div>

    <h2 class="titulo" style="font-size:16px;margin:20px 0 12px;"><?php echo View::e(I18n::t('eq_config.geral')); ?></h2>
    <div class="grid">
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;"><?php echo View::e(I18n::t('eq_config.url_base')); ?></label>
        <input class="input" type="text" name="app_url_base" value="<?php echo View::e((string)($app_url_base??'')); ?>" placeholder="https://painel.seudominio.com" />
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;"><?php echo View::e(I18n::t('eq_config.email_admin')); ?></label>
        <input class="input" type="email" name="email_admin" value="<?php echo View::e((string)($email_admin??'')); ?>" />
      </div>
    </div>
    <div style="margin-top:12px;">
      <label style="display:block;font-size:13px;margin-bottom:6px;"><?php echo View::e(I18n::t('eq_config.secret_key')); ?></label>
      <div style="display:flex;gap:8px;align-items:center;">
        <input class="input" type="text" name="app_secret_key" id="app_secret_key" value="<?php echo View::e((string)($app_secret_key??'')); ?>" style="flex:1;font-family:monospace;" />
        <button type="button" class="botao" style="white-space:nowrap;" onclick="document.getElementById('app_secret_key').value=Array.from(crypto.getRandomValues(new Uint8Array(32))).map(b=>b.toString(16).padStart(2,'0')).join('')"><?php echo View::e(I18n::t('eq_config.gerar_chave')); ?></button>
      </div>
      <p class="texto" style="font-size:12px;margin-top:6px;opacity:.8;"><?php echo View::e(I18n::t('eq_config.hint_secret_key')); ?></p>
    </div>

    <h2 class="titulo" style="font-size:16px;margin:20px 0 12px;">WhatsApp (Evolution API)</h2>
    <div class="grid">
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Evolution API - URL base</label>
        <input class="input" type="text" name="evolution_url_base" value="<?php echo View::e((string)($evolution_url_base??'')); ?>" />
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Evolution API - Token</label>
        <input class="input" type="password" name="evolution_token" value="<?php echo View::e((string)($evolution_token??'')); ?>" />
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">WhatsApp do admin (DDI+DDD+Numero)</label>
        <input class="input" type="text" name="whatsapp_admin_numero" value="<?php echo View::e((string)($whatsapp_admin_numero??'')); ?>" placeholder="5511999999999" />
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Evolution API - Instancia</label>
        <input class="input" type="text" name="evolution_instance" value="<?php echo View::e((string)($evolution_instance??'')); ?>" />
      </div>
    </div>

    <h2 class="titulo" style="font-size:16px;margin:20px 0 12px;">Infraestrutura</h2>
    <div class="grid">
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Diretorio das chaves SSH (base)</label>
        <input class="input" type="text" name="ssh_key_dir" value="<?php echo View::e((string)($ssh_key_dir??'')); ?>" placeholder="<?php echo View::e(\LRV\Core\ConfiguracoesSistema::sshKeyDir()); ?>" />
        <p class="texto" style="font-size:12px;margin-top:4px;">Padrão: <code><?php echo View::e(\LRV\Core\ConfiguracoesSistema::sshKeyDir()); ?></code></p>
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Limite maximo de utilizacao do node (%)</label>
        <input class="input" type="number" name="infra_node_max_util_percent" value="<?php echo View::e((string)($infra_node_max_util_percent??'85')); ?>" min="50" max="100" />
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Token do monitoramento (x-monitoring-token)</label>
        <input class="input" type="password" name="monitoring_token" value="<?php echo View::e((string)($monitoring_token??'')); ?>" />
        <p class="texto" style="font-size:13px;margin-top:8px;">Endpoint: <strong>/api/metrics/servers</strong></p>
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Domínio base para domínios temporários</label>
        <input class="input" type="text" name="infra_temp_domain_base" value="<?php echo View::e((string)($infra_temp_domain_base??'')); ?>" placeholder="apps.lrvweb.com.br" />
        <p class="texto" style="font-size:12px;margin-top:4px;">Configure um wildcard DNS <code>*.apps.lrvweb.com.br</code> → IP do servidor. Clientes sem domínio próprio recebem um subdomínio automático.</p>
      </div>
    </div>

    <h2 class="titulo" style="font-size:16px;margin:20px 0 12px;">Servidor Proxy (Domínios Temporários)</h2>
    <p class="texto" style="font-size:13px;margin-bottom:12px;">Domínios temporários são criados via Cloudflare DNS API. Configure o token e o Zone ID do domínio base.</p>
    <div class="grid">
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Cloudflare API Token</label>
        <input class="input" type="password" name="cloudflare_api_token" value="<?php echo View::e((string)($cloudflare_api_token??'')); ?>" />
        <p class="texto" style="font-size:12px;margin-top:4px;">Token com permissão Zone:DNS:Edit. Crie em <a href="https://dash.cloudflare.com/profile/api-tokens" target="_blank" rel="noopener">Cloudflare → API Tokens</a>.</p>
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Cloudflare Zone ID</label>
        <input class="input" type="text" name="cloudflare_zone_id" value="<?php echo View::e((string)($cloudflare_zone_id??'')); ?>" placeholder="abc123..." />
        <p class="texto" style="font-size:12px;margin-top:4px;">Zone ID do domínio base (ex: lrvweb.com.br). Encontre no dashboard do Cloudflare → Overview → API.</p>
      </div>
    </div>

    <h2 class="titulo" style="font-size:16px;margin:20px 0 12px;">Terminal (Admin)</h2>
    <div class="grid">
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Porta interna do WebSocket</label>
        <input class="input" type="number" name="terminal_ws_internal_port" value="<?php echo View::e((string)($terminal_ws_internal_port??'8081')); ?>" min="1" max="65535" />
        <p class="texto" style="font-size:13px;margin-top:8px;">Proxy reverso: <strong>/ws/terminal</strong> &rarr; <strong>127.0.0.1:porta</strong>.</p>
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">TTL do token (segundos)</label>
        <input class="input" type="number" name="terminal_token_ttl_seconds" value="<?php echo View::e((string)($terminal_token_ttl_seconds??'60')); ?>" min="10" />
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Timeout por inatividade (segundos)</label>
        <input class="input" type="number" name="terminal_idle_timeout_seconds" value="<?php echo View::e((string)($terminal_idle_timeout_seconds??'900')); ?>" min="60" />
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Modo seguro</label>
        <select class="input" name="terminal_safe_mode">
          <option value="1" <?php echo ((string)($terminal_safe_mode??'1'))==='1'?'selected':''; ?>>Ativado</option>
          <option value="0" <?php echo ((string)($terminal_safe_mode??'1'))==='0'?'selected':''; ?>>Desativado</option>
        </select>
      </div>
    </div>

    <!-- Botões de setup dos daemons WebSocket -->
    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:16px;margin-top:16px;">
      <div style="font-weight:600;font-size:14px;margin-bottom:8px;">⚙️ Setup dos daemons e proxy</div>
      <p style="font-size:12px;color:#64748b;margin-bottom:12px;">Instala os serviços systemd (terminal + chat) e configura o proxy reverso no Apache. Executa no servidor local.</p>
      <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <button class="botao sm" type="button" id="btnSetupDaemons" onclick="setupDaemons()">🚀 Instalar daemons + proxy</button>
        <span id="setupDaemonsStatus" style="font-size:13px;align-self:center;"></span>
      </div>
    </div>

    <h2 class="titulo" style="font-size:16px;margin:20px 0 12px;">SMTP (envio de e-mails do sistema)</h2>
    <p class="texto" style="font-size:13px;margin-bottom:12px;">Usado para reset de senha, alertas e notificações. Se não configurado, o sistema usa o <code>mail()</code> nativo do PHP.</p>
    <div class="grid">
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Host SMTP</label>
        <input class="input" type="text" name="smtp_host" value="<?php echo View::e((string)($smtp_host??'')); ?>" placeholder="smtp.gmail.com" />
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Porta</label>
        <input class="input" type="number" name="smtp_port" value="<?php echo View::e((string)($smtp_port??'587')); ?>" placeholder="587" min="1" max="65535" />
        <p class="texto" style="font-size:12px;margin-top:4px;">587 = TLS · 465 = SSL · 25 = sem criptografia</p>
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Usuário (e-mail de login)</label>
        <input class="input" type="text" name="smtp_user" value="<?php echo View::e((string)($smtp_user??'')); ?>" placeholder="noreply@seudominio.com" autocomplete="off" />
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Senha</label>
        <input class="input" type="password" name="smtp_pass" value="<?php echo View::e((string)($smtp_pass??'')); ?>" autocomplete="new-password" />
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Criptografia</label>
        <select class="input" name="smtp_encryption">
          <option value="tls" <?php echo ((string)($smtp_encryption??'tls'))==='tls'?'selected':''; ?>>TLS (STARTTLS) — porta 587</option>
          <option value="ssl" <?php echo ((string)($smtp_encryption??'tls'))==='ssl'?'selected':''; ?>>SSL — porta 465</option>
          <option value="none" <?php echo ((string)($smtp_encryption??'tls'))==='none'?'selected':''; ?>>Nenhuma — porta 25</option>
        </select>
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">E-mail remetente (From)</label>
        <input class="input" type="email" name="smtp_from_email" value="<?php echo View::e((string)($smtp_from_email??'')); ?>" placeholder="noreply@seudominio.com" />
        <p class="texto" style="font-size:12px;margin-top:4px;">Se vazio, usa o usuário acima.</p>
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Nome do remetente</label>
        <input class="input" type="text" name="smtp_from_name" value="<?php echo View::e((string)($smtp_from_name??'')); ?>" placeholder="LRV Web" />
      </div>
    </div>

    <h2 class="titulo" style="font-size:16px;margin:20px 0 12px;">E-mail (Mailcow)</h2>
    <div class="grid">
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">URL base do Mailcow</label>
        <input class="input" type="text" name="mailcow_url" value="<?php echo View::e((string)($mailcow_url??'')); ?>" placeholder="https://mail.seudominio.com" />
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">API Key do Mailcow</label>
        <input class="input" type="password" name="mailcow_key" value="<?php echo View::e((string)($mailcow_key??'')); ?>" />
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">URL do Webmail</label>
        <input class="input" type="text" name="webmail_url" value="<?php echo View::e((string)($webmail_url??'')); ?>" placeholder="https://webmail.seudominio.com" />
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Domínio padrão global</label>
        <input class="input" type="text" name="email_default_domain" value="<?php echo View::e((string)($email_default_domain??'')); ?>" placeholder="lrvmail.com" />
        <p class="texto" style="font-size:12px;margin-top:4px;">Usado quando o cliente não tem domínio próprio.</p>
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Modo de webmail</label>
        <select class="input" name="email_webmail_mode">
          <option value="global" <?php echo ((string)($email_webmail_mode??'global'))==='global'?'selected':''; ?>>Global (domínio do sistema)</option>
          <option value="custom" <?php echo ((string)($email_webmail_mode??'global'))==='custom'?'selected':''; ?>>Custom (domínio do cliente)</option>
        </select>
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Limite de contas por plano (padrão)</label>
        <input class="input" type="number" name="email_max_accounts" value="<?php echo View::e((string)($email_max_accounts??'5')); ?>" min="1" max="9999" />
        <p class="texto" style="font-size:12px;margin-top:4px;">Pode ser sobrescrito por plano via specs_json → email_accounts.</p>
      </div>
    </div>
    <div style="margin-top:12px;">
      <label style="display:block;font-size:13px;margin-bottom:6px;">Template de instruções DNS</label>
      <textarea class="input" name="email_dns_template" rows="6" style="resize:vertical;font-family:monospace;font-size:13px;"><?php echo View::e((string)($email_dns_template??'')); ?></textarea>
      <p class="texto" style="font-size:12px;margin-top:4px;">Use <code>{domain}</code> como placeholder. Exibido ao cliente ao configurar domínio próprio.</p>
    </div>

    <h2 class="titulo" style="font-size:16px;margin:20px 0 12px;">Servidor de E-mail (Monitoramento)</h2>
    <p class="texto" style="font-size:13px;margin-bottom:12px;">Configure o monitoramento do servidor onde o Mailcow está instalado. Alertas são enviados quando CPU, RAM ou disco ultrapassam os limites.</p>
    <div class="grid">
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">IP do servidor de e-mail</label>
        <input class="input" type="text" name="email_server_ip" value="<?php echo View::e((string)($email_server_ip??'')); ?>" placeholder="185.217.126.133" />
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Porta SSH</label>
        <input class="input" type="number" name="email_server_ssh_port" value="<?php echo View::e((string)($email_server_ssh_port??'22')); ?>" min="1" max="65535" />
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Usuário SSH</label>
        <input class="input" type="text" name="email_server_ssh_user" value="<?php echo View::e((string)($email_server_ssh_user??'root')); ?>" />
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Senha SSH</label>
        <input class="input" type="password" name="email_server_ssh_password" value="<?php echo View::e((string)($email_server_ssh_password??'')); ?>" autocomplete="new-password" />
      </div>
    </div>
    <div class="grid" style="margin-top:12px;">
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Alerta CPU (%)</label>
        <input class="input" type="number" name="email_alert_cpu" value="<?php echo View::e((string)($email_alert_cpu??'80')); ?>" min="50" max="100" />
        <p class="texto" style="font-size:12px;margin-top:4px;">Alerta quando CPU ultrapassar este valor.</p>
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Alerta RAM (%)</label>
        <input class="input" type="number" name="email_alert_ram" value="<?php echo View::e((string)($email_alert_ram??'85')); ?>" min="50" max="100" />
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Alerta Disco (%)</label>
        <input class="input" type="number" name="email_alert_disk" value="<?php echo View::e((string)($email_alert_disk??'90')); ?>" min="50" max="100" />
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Monitoramento ativo</label>
        <select class="input" name="email_monitoring_enabled">
          <option value="1" <?php echo ((string)($email_monitoring_enabled??'0'))==='1'?'selected':''; ?>>Sim</option>
          <option value="0" <?php echo ((string)($email_monitoring_enabled??'0'))==='0'?'selected':''; ?>>Não</option>
        </select>
      </div>
    </div>
    <div style="margin-top:14px;">
      <button type="button" class="botao sm" id="btnInstalarAgente" onclick="instalarAgenteEmail()">📡 Instalar agente de monitoramento</button>
      <span id="agenteStatus" style="font-size:13px;color:#64748b;margin-left:10px;"></span>
      <p class="texto" style="font-size:12px;margin-top:6px;">Conecta via SSH no servidor de e-mail e instala o script de coleta de métricas (CPU, RAM, disco) com cron a cada 5 min. Salve as configurações acima antes de clicar.</p>
    </div>

    <h2 class="titulo" style="font-size:16px;margin:20px 0 12px;">Chat ao vivo (WebSocket)</h2>
    <div class="grid">
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Porta do WebSocket do chat</label>
        <input class="input" type="number" name="chat_ws_port" value="<?php echo View::e((string)($chat_ws_port??'8082')); ?>" min="1" max="65535" />
        <p class="texto" style="font-size:13px;margin-top:8px;">Porta interna do servidor WebSocket. Padrao: <strong>8082</strong>.</p>
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">URL pública do WebSocket</label>
        <input class="input" type="text" name="chat_ws_url" value="<?php echo View::e((string)($chat_ws_url??'')); ?>" placeholder="wss://seudominio.com/ws/chat" />
        <p class="texto" style="font-size:13px;margin-top:8px;">URL completa do proxy reverso para o WebSocket. Ex: <strong>wss://cloud.lrvweb.com.br/ws/chat</strong>. Se vazio, conecta direto na porta.</p>
      </div>
    </div>

    <h2 class="titulo" style="font-size:16px;margin:20px 0 12px;"><?php echo View::e(I18n::t('eq_config.identidade_visual')); ?></h2>
    <div class="grid">
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;"><?php echo View::e(I18n::t('eq_config.nome_sistema')); ?></label>
        <input class="input" type="text" name="system_name" value="<?php echo View::e((string)($system_name??'')); ?>" placeholder="LRV Cloud Manager" />
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;"><?php echo View::e(I18n::t('eq_config.nome_empresa')); ?></label>
        <input class="input" type="text" name="system_company_name" value="<?php echo View::e((string)($system_company_name??'')); ?>" placeholder="LRV Cloud" />
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;"><?php echo View::e(I18n::t('eq_config.url_logo')); ?></label>
        <div style="display:flex;gap:8px;align-items:center;">
          <input class="input" type="text" name="system_logo_url" id="system_logo_url" value="<?php echo View::e((string)($system_logo_url??'')); ?>" placeholder="https://... ou /uploads/imagens/..." style="flex:1;" />
          <label class="botao sec" style="cursor:pointer;white-space:nowrap;padding:9px 14px;font-size:13px;" title="Fazer upload de imagem">
            <input type="file" accept="image/*" style="display:none;" data-target="system_logo_url" class="img-upload-input" />
            ↑ Upload
          </label>
        </div>
        <?php if (!empty($system_logo_url)): ?>
          <img src="<?php echo View::e((string)$system_logo_url); ?>" alt="preview logo" style="margin-top:8px;max-height:40px;max-width:200px;border-radius:6px;border:1px solid #e2e8f0;" />
        <?php endif; ?>
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;"><?php echo View::e(I18n::t('eq_config.url_favicon')); ?></label>
        <div style="display:flex;gap:8px;align-items:center;">
          <input class="input" type="text" name="system_favicon_url" id="system_favicon_url" value="<?php echo View::e((string)($system_favicon_url??'')); ?>" placeholder="https://... ou /uploads/imagens/..." style="flex:1;" />
          <label class="botao sec" style="cursor:pointer;white-space:nowrap;padding:9px 14px;font-size:13px;" title="Fazer upload de imagem">
            <input type="file" accept="image/*,.ico" style="display:none;" data-target="system_favicon_url" class="img-upload-input" />
            ↑ Upload
          </label>
        </div>
        <?php if (!empty($system_favicon_url)): ?>
          <img src="<?php echo View::e((string)$system_favicon_url); ?>" alt="preview favicon" style="margin-top:8px;max-height:32px;max-width:64px;border-radius:4px;border:1px solid #e2e8f0;" />
        <?php endif; ?>
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;"><?php echo View::e(I18n::t('eq_config.copyright_footer')); ?></label>
        <input class="input" type="text" name="system_copyright_text" value="<?php echo View::e((string)($system_copyright_text??'')); ?>" placeholder="2025 LRV Cloud" />
      </div>
    </div>

    <h2 class="titulo" style="font-size:16px;margin:20px 0 12px;"><?php echo View::e(I18n::t('eq_config.paginas_legais')); ?></h2>
    <p class="texto" style="font-size:13px;"><?php echo I18n::t('eq_config.desc_legais'); ?> <a href="/termos" target="_blank">/termos</a>, <a href="/privacidade" target="_blank">/privacidade</a> e <a href="/licenca" target="_blank">/licenca</a>.</p>
    <div style="margin-top:12px;">
      <label style="display:block;font-size:13px;margin-bottom:6px;">Termos de Uso (HTML) — Português</label>
      <textarea class="input" name="legal_terms_html" rows="8" style="resize:vertical;font-family:monospace;font-size:13px;"><?php echo View::e((string)($legal_terms_html??'')); ?></textarea>
    </div>
    <div style="margin-top:12px;">
      <label style="display:block;font-size:13px;margin-bottom:6px;">Terms of Service (HTML) — English</label>
      <textarea class="input" name="legal_terms_html_en" rows="6" style="resize:vertical;font-family:monospace;font-size:13px;"><?php echo View::e((string)($legal_terms_html_en??'')); ?></textarea>
    </div>
    <div style="margin-top:12px;">
      <label style="display:block;font-size:13px;margin-bottom:6px;">Términos de Servicio (HTML) — Español</label>
      <textarea class="input" name="legal_terms_html_es" rows="6" style="resize:vertical;font-family:monospace;font-size:13px;"><?php echo View::e((string)($legal_terms_html_es??'')); ?></textarea>
    </div>
    <div style="margin-top:12px;">
      <label style="display:block;font-size:13px;margin-bottom:6px;">Politica de Privacidade (HTML) — Português</label>
      <textarea class="input" name="legal_privacy_html" rows="8" style="resize:vertical;font-family:monospace;font-size:13px;"><?php echo View::e((string)($legal_privacy_html??'')); ?></textarea>
    </div>
    <div style="margin-top:12px;">
      <label style="display:block;font-size:13px;margin-bottom:6px;">Privacy Policy (HTML) — English</label>
      <textarea class="input" name="legal_privacy_html_en" rows="6" style="resize:vertical;font-family:monospace;font-size:13px;"><?php echo View::e((string)($legal_privacy_html_en??'')); ?></textarea>
    </div>
    <div style="margin-top:12px;">
      <label style="display:block;font-size:13px;margin-bottom:6px;">Política de Privacidad (HTML) — Español</label>
      <textarea class="input" name="legal_privacy_html_es" rows="6" style="resize:vertical;font-family:monospace;font-size:13px;"><?php echo View::e((string)($legal_privacy_html_es??'')); ?></textarea>
    </div>
    <div style="margin-top:12px;">
      <label style="display:block;font-size:13px;margin-bottom:6px;">Licença de Uso (HTML) — Português</label>
      <p class="texto" style="font-size:12px;margin-bottom:6px;">Se preenchido, substitui o texto padrão da licença em <a href="/licenca" target="_blank">/licenca</a>. Deixe em branco para usar o texto padrão do sistema.</p>
      <textarea class="input" name="legal_license_html" rows="8" style="resize:vertical;font-family:monospace;font-size:13px;"><?php echo View::e((string)($legal_license_html??'')); ?></textarea>
    </div>
    <div style="margin-top:12px;">
      <label style="display:block;font-size:13px;margin-bottom:6px;">License (HTML) — English</label>
      <textarea class="input" name="legal_license_html_en" rows="6" style="resize:vertical;font-family:monospace;font-size:13px;"><?php echo View::e((string)($legal_license_html_en??'')); ?></textarea>
    </div>
    <div style="margin-top:12px;">
      <label style="display:block;font-size:13px;margin-bottom:6px;">Licencia (HTML) — Español</label>
      <textarea class="input" name="legal_license_html_es" rows="6" style="resize:vertical;font-family:monospace;font-size:13px;"><?php echo View::e((string)($legal_license_html_es??'')); ?></textarea>
    </div>

    <h2 class="titulo" style="font-size:16px;margin:20px 0 12px;"><?php echo View::e(I18n::t('eq_config.trial')); ?></h2>
    <p class="texto" style="font-size:13px;"><?php echo View::e(I18n::t('eq_config.trial_desc')); ?></p>
    <div class="grid">
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;"><?php echo View::e(I18n::t('eq_config.trial_ativo')); ?></label>
        <select class="input" name="trial_enabled">
          <option value="1" <?php echo ((string)($trial_enabled??'0'))==='1'?'selected':''; ?>>Sim — exibir CTA na home</option>
          <option value="0" <?php echo ((string)($trial_enabled??'0'))==='0'?'selected':''; ?>>Não</option>
        </select>
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;"><?php echo View::e(I18n::t('eq_config.duracao_dias')); ?></label>
        <input class="input" type="number" name="trial_dias" value="<?php echo View::e((string)($trial_dias??'7')); ?>" min="1" max="365" />
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">vCPU do servidor trial</label>
        <input class="input" type="number" name="trial_vcpu" value="<?php echo View::e((string)($trial_vcpu??'1')); ?>" min="1" max="64" />
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">RAM (MB)</label>
        <input class="input" type="number" name="trial_ram_mb" value="<?php echo View::e((string)($trial_ram_mb??'1024')); ?>" min="128" step="128" />
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Disco (GB)</label>
        <input class="input" type="number" name="trial_disco_gb" value="<?php echo View::e((string)($trial_disco_gb??'20')); ?>" min="1" />
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;"><?php echo View::e(I18n::t('eq_config.label_cta')); ?></label>
        <input class="input" type="text" name="trial_label_cta" value="<?php echo View::e((string)($trial_label_cta??'Testar grátis')); ?>" placeholder="Testar grátis por 7 dias" />
      </div>
    </div>
    <div style="margin-top:12px;">
      <label style="display:block;font-size:13px;margin-bottom:6px;"><?php echo View::e(I18n::t('eq_config.desc_trial')); ?></label>
      <input class="input" type="text" name="trial_descricao" value="<?php echo View::e((string)($trial_descricao??'')); ?>" placeholder="Experimente gratuitamente por 7 dias, sem cartão de crédito." />
    </div>

    <h2 class="titulo" style="font-size:16px;margin:20px 0 12px;"><?php echo View::e(I18n::t('eq_config.seo')); ?></h2>
    <p class="texto" style="font-size:13px;"><?php echo View::e(I18n::t('eq_config.seo_desc')); ?></p>
    <div class="grid">
      <div>
        <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px;">Título padrão (tag &lt;title&gt;)</label>
        <input class="input" type="text" name="seo_titulo" value="<?php echo View::e((string)($seo_titulo??'')); ?>" placeholder="<?php echo View::e(SistemaConfig::nome()); ?> — Infraestrutura Cloud" />
        <p class="texto" style="font-size:12px;margin-top:4px;">Recomendado: 50–60 caracteres.</p>
      </div>
      <div>
        <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px;">URL canônica base</label>
        <input class="input" type="text" name="seo_canonical_base" value="<?php echo View::e((string)($seo_canonical_base??'')); ?>" placeholder="https://seudominio.com" />
        <p class="texto" style="font-size:12px;margin-top:4px;">Sem barra no final. Usado para gerar &lt;link rel="canonical"&gt;.</p>
      </div>
    </div>
    <div style="margin-top:12px;">
      <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px;">Meta description</label>
      <textarea class="input" name="seo_descricao" rows="3" style="resize:vertical;"><?php echo View::e((string)($seo_descricao??'')); ?></textarea>
      <p class="texto" style="font-size:12px;margin-top:4px;">Recomendado: 120–160 caracteres. Exibida nos resultados do Google.</p>
    </div>
    <div class="grid" style="margin-top:12px;">
      <div>
        <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px;">Palavras-chave (keywords)</label>
        <input class="input" type="text" name="seo_palavras_chave" value="<?php echo View::e((string)($seo_palavras_chave??'')); ?>" placeholder="vps, cloud, hospedagem, servidor" />
        <p class="texto" style="font-size:12px;margin-top:4px;">Separadas por vírgula. Pouco impacto no Google, mas útil para outros buscadores.</p>
      </div>
      <div>
        <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px;">Imagem Open Graph (og:image)</label>
        <div style="display:flex;gap:8px;align-items:center;">
          <input class="input" type="text" name="seo_og_image" id="seo_og_image" value="<?php echo View::e((string)($seo_og_image??'')); ?>" placeholder="https://seudominio.com/og.png" style="flex:1;" />
          <label class="botao sec" style="cursor:pointer;white-space:nowrap;padding:9px 14px;font-size:13px;" title="Fazer upload de imagem">
            <input type="file" accept="image/*" style="display:none;" data-target="seo_og_image" class="img-upload-input" />
            ↑ Upload
          </label>
        </div>
        <p class="texto" style="font-size:12px;margin-top:4px;">Tamanho ideal: 1200×630px. Exibida ao compartilhar no WhatsApp, Twitter, etc.</p>
        <?php if (!empty($seo_og_image)): ?>
          <img src="<?php echo View::e((string)$seo_og_image); ?>" alt="preview og" style="margin-top:8px;max-height:60px;max-width:200px;border-radius:6px;border:1px solid #e2e8f0;" />
        <?php endif; ?>
      </div>
      <div>
        <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px;">Robots (indexação)</label>
        <select class="input" name="seo_robots">
          <?php foreach (['index, follow' => 'Indexar e seguir links (padrão)', 'noindex, nofollow' => 'Não indexar, não seguir', 'noindex, follow' => 'Não indexar, seguir links', 'index, nofollow' => 'Indexar, não seguir links'] as $_v => $_l): ?>
            <option value="<?php echo View::e($_v); ?>" <?php echo ((string)($seo_robots??'index, follow'))===$_v?'selected':''; ?>><?php echo View::e($_l); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px;">Google Analytics ID</label>
        <input class="input" type="text" name="seo_google_analytics_id" value="<?php echo View::e((string)($seo_google_analytics_id??'')); ?>" placeholder="G-XXXXXXXXXX ou UA-XXXXXXXX-X" />
        <p class="texto" style="font-size:12px;margin-top:4px;">Deixe em branco para não incluir o script do GA.</p>
      </div>
      <div>
        <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px;">Schema.org — tipo da organização</label>
        <select class="input" name="seo_schema_type">
          <?php foreach (['Organization' => 'Organization (padrão)', 'LocalBusiness' => 'LocalBusiness', 'SoftwareApplication' => 'SoftwareApplication', 'WebSite' => 'WebSite'] as $_v => $_l): ?>
            <option value="<?php echo View::e($_v); ?>" <?php echo ((string)($seo_schema_type??'Organization'))===$_v?'selected':''; ?>><?php echo View::e($_l); ?></option>
          <?php endforeach; ?>
        </select>
        <p class="texto" style="font-size:12px;margin-top:4px;">Usado no JSON-LD para rich results no Google.</p>
      </div>
    </div>

    <div style="margin-top:20px;">
      <button class="botao" type="submit"><?php echo View::e(I18n::t('eq_config.salvar')); ?></button>
    </div>
  </form>
</div>

<script>
(function () {
  var csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

  document.querySelectorAll('.img-upload-input').forEach(function (input) {
    input.addEventListener('change', async function () {
      var file = this.files[0];
      if (!file) return;

      var targetId = this.dataset.target;
      var targetInput = document.getElementById(targetId);
      if (!targetInput) return;

      // Preview imediato
      var label = this.closest('label');
      var oldText = label.childNodes[label.childNodes.length - 1].textContent;
      label.childNodes[label.childNodes.length - 1].textContent = ' Enviando...';

      var fd = new FormData();
      fd.append('imagem', file);

      try {
        var resp = await fetch('/equipe/configuracoes/upload-imagem', {
          method: 'POST',
          headers: { 'x-csrf-token': csrf },
          body: fd
        });
        var json = await resp.json();

        if (json.ok && json.url) {
          targetInput.value = json.url;
          // Atualizar preview
          var container = targetInput.closest('div').parentElement;
          var preview = container.querySelector('img');
          if (preview) {
            preview.src = json.url;
          } else {
            var img = document.createElement('img');
            img.src = json.url;
            img.alt = 'preview';
            img.style.cssText = 'margin-top:8px;max-height:40px;max-width:200px;border-radius:6px;border:1px solid #e2e8f0;';
            container.appendChild(img);
          }
          label.childNodes[label.childNodes.length - 1].textContent = ' ✓ Enviado';
          setTimeout(function () {
            label.childNodes[label.childNodes.length - 1].textContent = oldText;
          }, 2000);
        } else {
          alert('Erro: ' + (json.erro || 'Falha no upload.'));
          label.childNodes[label.childNodes.length - 1].textContent = oldText;
        }
      } catch (e) {
        alert('Erro de rede ao fazer upload.');
        label.childNodes[label.childNodes.length - 1].textContent = oldText;
      }

      // Limpar input para permitir re-upload do mesmo arquivo
      this.value = '';
    });
  });
})();

function instalarAgenteEmail(){
  var btn=document.getElementById('btnInstalarAgente');
  var st=document.getElementById('agenteStatus');
  btn.disabled=true;st.textContent='Conectando via SSH...';st.style.color='#64748b';
  var csrf=(document.querySelector('meta[name="csrf-token"]')||{}).content||'';
  fetch('/equipe/configuracoes/instalar-agente-email',{method:'POST',headers:{'x-csrf-token':csrf,'Content-Type':'application/json'}})
  .then(function(r){return r.json();}).then(function(d){
    if(d.ok){st.textContent='✓ '+d.mensagem;st.style.color='#16a34a';}
    else{st.textContent='✘ '+(d.erro||'Erro');st.style.color='#dc2626';}
    btn.disabled=false;
  }).catch(function(){st.textContent='✘ Erro de rede';st.style.color='#dc2626';btn.disabled=false;});
}
</script>

<script>
function setupDaemons(){
  var btn=document.getElementById('btnSetupDaemons');
  var st=document.getElementById('setupDaemonsStatus');
  btn.disabled=true;st.textContent='Executando...';st.style.color='#64748b';
  var csrf=(document.querySelector('meta[name="csrf-token"]')||{}).content||'';
  fetch('/equipe/configuracoes/setup-daemons',{method:'POST',headers:{'x-csrf-token':csrf,'Content-Type':'application/json'}})
  .then(function(r){return r.json();}).then(function(d){
    if(d.ok){st.textContent='✓ '+d.mensagem;st.style.color='#16a34a';}
    else{st.textContent='✗ '+(d.erro||'Erro');st.style.color='#dc2626';}
    btn.disabled=false;
  }).catch(function(e){st.textContent='✗ Erro de rede';st.style.color='#dc2626';btn.disabled=false;});
}
</script>

<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
