<?php
declare(strict_types=1);
use LRV\Core\View;

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
        <input class="input" type="text" name="ssh_key_dir" value="<?php echo View::e((string)($ssh_key_dir??'')); ?>" />
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
        <input class="input" type="text" name="system_logo_url" value="<?php echo View::e((string)($system_logo_url??'')); ?>" />
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">URL do favicon</label>
        <input class="input" type="text" name="system_favicon_url" value="<?php echo View::e((string)($system_favicon_url??'')); ?>" />
      </div>
      <div>
        <label style="display:block;font-size:13px;margin-bottom:6px;">Texto de copyright (footer)</label>
        <input class="input" type="text" name="system_copyright_text" value="<?php echo View::e((string)($system_copyright_text??'')); ?>" placeholder="2025 LRV Cloud" />
      </div>
    </div>

    <h2 class="titulo" style="font-size:16px;margin:20px 0 12px;">Paginas Legais</h2>
    <p class="texto" style="font-size:13px;">HTML permitido. Acessivel em <a href="/termos" target="_blank">/termos</a> e <a href="/privacidade" target="_blank">/privacidade</a>.</p>
    <div style="margin-top:12px;">
      <label style="display:block;font-size:13px;margin-bottom:6px;">Termos de Uso (HTML)</label>
      <textarea class="input" name="legal_terms_html" rows="8" style="resize:vertical;font-family:monospace;font-size:13px;"><?php echo View::e((string)($legal_terms_html??'')); ?></textarea>
    </div>
    <div style="margin-top:12px;">
      <label style="display:block;font-size:13px;margin-bottom:6px;">Politica de Privacidade (HTML)</label>
      <textarea class="input" name="legal_privacy_html" rows="8" style="resize:vertical;font-family:monospace;font-size:13px;"><?php echo View::e((string)($legal_privacy_html??'')); ?></textarea>
    </div>

    <div style="margin-top:20px;">
      <button class="botao" type="submit">Salvar</button>
    </div>
  </form>
</div>
<?php require __DIR__ . '/../_partials/layout-equipe-fim.php'; ?>
