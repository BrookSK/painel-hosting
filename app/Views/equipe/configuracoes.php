<?php
declare(strict_types=1);
use LRV\Core\View;
use LRV\Core\SistemaConfig;

$pageTitle = 'Configuracoes';
require __DIR__ . '/../_partials/layout-equipe-inicio.php';
?>
<div class="page-title">Configuracoes</div>
<div class="page-subtitle">Pagamentos, webhooks, alertas e identidade visual</div>

<div class="card-new" style="max-width:920px;">
  <p class="texto">Tudo aqui fica salvo no banco (tabela <strong>settings</strong>). Nao usamos arquivo <strong>.env</strong>.</p>

  <?php if (!empty($erro)): ?><div class="erro"><?php echo View::e((string)$erro); ?></div><?php endif; ?>
  <?php if (!empty($salvo)): ?><div class="sucesso">Configuracoes salvas.</div><?php endif; ?>

  <form method="post" action="/equipe/configuracoes">
    <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />

    <h2 class="titulo" style="font-size:16px;margin-bottom:12px;">Asaas</h2>
    <div class="grid">
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Token do Asaas</label>
        <input class="input" type="password" name="asaas_token" value="<?php echo View::e((string)($asaas_token??'')); ?>" />
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">URL base do Asaas</label>
        <input class="input" type="text" name="asaas_url_base" value="<?php echo View::e((string)($asaas_url_base??'')); ?>" />
      </div>
    </div>
    <div class="grid" style="margin-top:12px;">
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Segredo do webhook Asaas</label>
        <input class="input" type="text" name="asaas_webhook_segredo" value="<?php echo View::e((string)($asaas_webhook_segredo??'')); ?>" />
        <p class="texto" style="font-size:13px;margin-top:8px;">Endpoint: <strong>/webhooks/asaas</strong></p>
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Tolerancia de inadimplencia (dias)</label>
        <input class="input" type="number" name="tolerancia_dias" value="<?php echo View::e((string)($tolerancia_dias??'3')); ?>" min="1" />
      </div>
    </div>

    <h2 class="titulo" style="font-size:16px;margin:20px 0 12px;">Stripe</h2>
    <div class="grid">
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Stripe - Secret Key</label>
        <input class="input" type="password" name="stripe_secret_key" value="<?php echo View::e((string)($stripe_secret_key??'')); ?>" />
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Stripe - Webhook Secret</label>
        <input class="input" type="text" name="stripe_webhook_secret" value="<?php echo View::e((string)($stripe_webhook_secret??'')); ?>" />
        <p class="texto" style="font-size:13px;margin-top:8px;">Endpoint: <strong>/webhooks/stripe</strong></p>
      </div>
    </div>

    <h2 class="titulo" style="font-size:16px;margin:20px 0 12px;">Geral</h2>
    <div class="grid">
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">URL base publica da aplicacao</label>
        <input class="input" type="text" name="app_url_base" value="<?php echo View::e((string)($app_url_base??'')); ?>" placeholder="https://painel.seudominio.com" />
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">E-mail do admin para alertas</label>
        <input class="input" type="email" name="email_admin" value="<?php echo View::e((string)($email_admin??'')); ?>" />
      </div>
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

    <h2 class="titulo" style="font-size:16px;margin:20px 0 12px;">Chat ao vivo (WebSocket)</h2>
    <div class="grid">
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Porta do WebSocket do chat</label>
        <input class="input" type="number" name="chat_ws_port" value="<?php echo View::e((string)($chat_ws_port??'8082')); ?>" min="1" max="65535" />
        <p class="texto" style="font-size:13px;margin-top:8px;">Proxy reverso: <strong>/ws/chat</strong> &rarr; <strong>127.0.0.1:porta</strong>. Padrao: <strong>8082</strong>.</p>
      </div>
    </div>

    <h2 class="titulo" style="font-size:16px;margin:20px 0 12px;">Identidade Visual</h2>
    <div class="grid">
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Nome do sistema</label>
        <input class="input" type="text" name="system_name" value="<?php echo View::e((string)($system_name??'')); ?>" placeholder="LRV Cloud Manager" />
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Nome da empresa</label>
        <input class="input" type="text" name="system_company_name" value="<?php echo View::e((string)($system_company_name??'')); ?>" placeholder="LRV Cloud" />
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">URL do logotipo</label>
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
        <label style="display:block;font-size:13px;margin-bottom:6px;">URL do favicon</label>
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
        <label style="display:block;font-size:13px;margin-bottom:6px;">Texto de copyright (footer)</label>
        <input class="input" type="text" name="system_copyright_text" value="<?php echo View::e((string)($system_copyright_text??'')); ?>" placeholder="2025 LRV Cloud" />
      </div>
    </div>

    <h2 class="titulo" style="font-size:16px;margin:20px 0 12px;">Paginas Legais</h2>
    <p class="texto" style="font-size:13px;">HTML permitido. Acessivel em <a href="/termos" target="_blank">/termos</a>, <a href="/privacidade" target="_blank">/privacidade</a> e <a href="/licenca" target="_blank">/licenca</a>.</p>
    <div style="margin-top:12px;">
      <label style="display:block;font-size:13px;margin-bottom:6px;">Termos de Uso (HTML)</label>
      <textarea class="input" name="legal_terms_html" rows="8" style="resize:vertical;font-family:monospace;font-size:13px;"><?php echo View::e((string)($legal_terms_html??'')); ?></textarea>
    </div>
    <div style="margin-top:12px;">
      <label style="display:block;font-size:13px;margin-bottom:6px;">Politica de Privacidade (HTML)</label>
      <textarea class="input" name="legal_privacy_html" rows="8" style="resize:vertical;font-family:monospace;font-size:13px;"><?php echo View::e((string)($legal_privacy_html??'')); ?></textarea>
    </div>
    <div style="margin-top:12px;">
      <label style="display:block;font-size:13px;margin-bottom:6px;">Licença de Uso (HTML)</label>
      <p class="texto" style="font-size:12px;margin-bottom:6px;">Se preenchido, substitui o texto padrão da licença em <a href="/licenca" target="_blank">/licenca</a>. Deixe em branco para usar o texto padrão do sistema.</p>
      <textarea class="input" name="legal_license_html" rows="8" style="resize:vertical;font-family:monospace;font-size:13px;"><?php echo View::e((string)($legal_license_html??'')); ?></textarea>
    </div>

    <h2 class="titulo" style="font-size:16px;margin:20px 0 12px;">Teste Grátis (Trial)</h2>
    <p class="texto" style="font-size:13px;">Quando ativo, novos clientes recebem um servidor de teste ao criar conta.</p>
    <div class="grid">
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Trial ativo</label>
        <select class="input" name="trial_enabled">
          <option value="1" <?php echo ((string)($trial_enabled??'0'))==='1'?'selected':''; ?>>Sim — exibir CTA na home</option>
          <option value="0" <?php echo ((string)($trial_enabled??'0'))==='0'?'selected':''; ?>>Não</option>
        </select>
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Duração (dias)</label>
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
        <label style="display:block;font-size:13px;margin-bottom:6px;">Label do botão CTA</label>
        <input class="input" type="text" name="trial_label_cta" value="<?php echo View::e((string)($trial_label_cta??'Testar grátis')); ?>" placeholder="Testar grátis por 7 dias" />
      </div>
    </div>
    <div style="margin-top:12px;">
      <label style="display:block;font-size:13px;margin-bottom:6px;">Descrição do trial (exibida na home)</label>
      <input class="input" type="text" name="trial_descricao" value="<?php echo View::e((string)($trial_descricao??'')); ?>" placeholder="Experimente gratuitamente por 7 dias, sem cartão de crédito." />
    </div>

    <h2 class="titulo" style="font-size:16px;margin:20px 0 12px;">SEO & Indexação</h2>
    <p class="texto" style="font-size:13px;">Configurações para o Google e outros buscadores indexarem corretamente o site público.</p>
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
      <button class="botao" type="submit">Salvar</button>
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
</script>

<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
