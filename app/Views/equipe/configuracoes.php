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
  <title>Configurações</title>
  <?php require __DIR__ . '/../_partials/estilo.php'; ?>
</head>
<body>
  <div class="topo">
    <div class="conteudo linha" style="justify-content:space-between;">
      <div>
        <div style="font-size:18px;font-weight:700;">Configurações</div>
        <div style="opacity:.9; font-size:13px;">Pagamentos, webhooks, alertas</div>
      </div>
      <div class="linha">
        <?php require __DIR__ . '/../_partials/idioma.php'; ?>
        <a href="/equipe/painel">Painel</a>
        <a href="/equipe/ajuda">Ajuda</a>
        <a href="/equipe/vps">VPS</a>
        <a href="/equipe/jobs">Jobs</a>
        <a href="/equipe/sair">Sair</a>
      </div>
    </div>
  </div>

  <div class="conteudo">
    <div class="card" style="max-width:920px; margin:0 auto;">
      <h1 class="titulo">Configurações do sistema</h1>
      <p class="texto">Tudo aqui fica salvo no banco (tabela <strong>settings</strong>). Não usamos arquivo <strong>.env</strong>.</p>

      <?php if (!empty($erro)): ?>
        <div class="erro"><?php echo View::e((string) $erro); ?></div>
      <?php endif; ?>

      <?php if (!empty($salvo)): ?>
        <div style="background:#ecfeff;border:1px solid #a5f3fc;color:#155e75;padding:10px 12px;border-radius:12px;margin-bottom:10px;">
          Configurações salvas.
        </div>
      <?php endif; ?>

      <form method="post" action="/equipe/configuracoes">
        <input type="hidden" name="_csrf" value="<?php echo View::e(\LRV\Core\Csrf::token()); ?>" />
        <div class="grid">
          <div>
            <label style="display:block; font-size:13px; margin-bottom:6px;">Token do Asaas (access_token)</label>
            <input class="input" type="password" name="asaas_token" value="<?php echo View::e((string) ($asaas_token ?? '')); ?>" />
          </div>
          <div>
            <label style="display:block; font-size:13px; margin-bottom:6px;">URL base do Asaas</label>
            <input class="input" type="text" name="asaas_url_base" value="<?php echo View::e((string) ($asaas_url_base ?? '')); ?>" />
          </div>
        </div>

        <div class="grid" style="margin-top:12px;">
          <div>
            <label style="display:block; font-size:13px; margin-bottom:6px;">Segredo do webhook do Asaas (asaas-access-token)</label>
            <input class="input" type="text" name="asaas_webhook_segredo" value="<?php echo View::e((string) ($asaas_webhook_segredo ?? '')); ?>" />
            <p class="texto" style="font-size:13px; margin-top:8px;">Endpoint: <strong>/webhooks/asaas</strong></p>
          </div>
          <div>
            <label style="display:block; font-size:13px; margin-bottom:6px;">Tolerância de inadimplência (dias)</label>
            <input class="input" type="number" name="tolerancia_dias" value="<?php echo View::e((string) ($tolerancia_dias ?? '3')); ?>" min="1" />
          </div>
        </div>

        <div class="grid" style="margin-top:12px;">
          <div>
            <label style="display:block; font-size:13px; margin-bottom:6px;">Evolution API - URL base</label>
            <input class="input" type="text" name="evolution_url_base" value="<?php echo View::e((string) ($evolution_url_base ?? '')); ?>" />
          </div>
          <div>
            <label style="display:block; font-size:13px; margin-bottom:6px;">Evolution API - Token</label>
            <input class="input" type="password" name="evolution_token" value="<?php echo View::e((string) ($evolution_token ?? '')); ?>" />
          </div>
        </div>

        <div class="grid" style="margin-top:12px;">
          <div>
            <label style="display:block; font-size:13px; margin-bottom:6px;">E-mail do admin para alertas</label>
            <input class="input" type="email" name="email_admin" value="<?php echo View::e((string) ($email_admin ?? '')); ?>" />
          </div>
        </div>

        <div class="grid" style="margin-top:12px;">
          <div>
            <label style="display:block; font-size:13px; margin-bottom:6px;">WhatsApp do admin (DDI+DDD+Número)</label>
            <input class="input" type="text" name="whatsapp_admin_numero" value="<?php echo View::e((string) ($whatsapp_admin_numero ?? '')); ?>" />
            <p class="texto" style="font-size:13px; margin-top:8px;">Exemplo: <strong>5511999999999</strong></p>
          </div>
          <div>
            <label style="display:block; font-size:13px; margin-bottom:6px;">Evolution API - Instância</label>
            <input class="input" type="text" name="evolution_instance" value="<?php echo View::e((string) ($evolution_instance ?? '')); ?>" />
            <p class="texto" style="font-size:13px; margin-top:8px;">Usado em <strong>/message/sendText/&lt;instance&gt;</strong></p>
          </div>
        </div>

        <div class="grid" style="margin-top:12px;">
          <div>
            <label style="display:block; font-size:13px; margin-bottom:6px;">Diretório das chaves SSH (base)</label>
            <input class="input" type="text" name="ssh_key_dir" value="<?php echo View::e((string) ($ssh_key_dir ?? '')); ?>" />
            <p class="texto" style="font-size:13px; margin-top:8px;">As chaves são referenciadas por identificador no cadastro de nodes. Não salvamos a chave no banco.</p>
          </div>
        </div>

        <div class="grid" style="margin-top:12px;">
          <div>
            <label style="display:block; font-size:13px; margin-bottom:6px;">Limite máximo de utilização do node (%)</label>
            <input class="input" type="number" name="infra_node_max_util_percent" value="<?php echo View::e((string) ($infra_node_max_util_percent ?? '85')); ?>" min="50" max="100" />
            <p class="texto" style="font-size:13px; margin-top:8px;">Quando um node passa desse limite (CPU/RAM/Storage), ele não é escolhido para provisionamento automático.</p>
          </div>
        </div>

        <div class="grid" style="margin-top:12px;">
          <div>
            <label style="display:block; font-size:13px; margin-bottom:6px;">Token do monitoramento (x-monitoring-token)</label>
            <input class="input" type="password" name="monitoring_token" value="<?php echo View::e((string) ($monitoring_token ?? '')); ?>" />
            <p class="texto" style="font-size:13px; margin-top:8px;">Endpoint: <strong>/api/metrics/servers</strong></p>
          </div>
        </div>

        <div style="margin-top:16px; border-top:1px solid #e5e7eb; padding-top:14px;">
          <h2 class="titulo" style="font-size:16px;">Terminal (Admin)</h2>

          <div class="grid" style="margin-top:12px;">
            <div>
              <label style="display:block; font-size:13px; margin-bottom:6px;">Porta interna do WebSocket (bind em 127.0.0.1)</label>
              <input class="input" type="number" name="terminal_ws_internal_port" value="<?php echo View::e((string) ($terminal_ws_internal_port ?? '8081')); ?>" min="1" max="65535" />
              <p class="texto" style="font-size:13px; margin-top:8px;">Proxy reverso deve apontar <strong>/ws/terminal</strong> para <strong>127.0.0.1:&lt;porta&gt;</strong>.</p>
            </div>
            <div>
              <label style="display:block; font-size:13px; margin-bottom:6px;">TTL do token (segundos)</label>
              <input class="input" type="number" name="terminal_token_ttl_seconds" value="<?php echo View::e((string) ($terminal_token_ttl_seconds ?? '60')); ?>" min="10" />
              <p class="texto" style="font-size:13px; margin-top:8px;">Recomendado: <strong>60</strong> (token de uso único).</p>
            </div>
          </div>

          <div class="grid" style="margin-top:12px;">
            <div>
              <label style="display:block; font-size:13px; margin-bottom:6px;">Timeout por inatividade (segundos)</label>
              <input class="input" type="number" name="terminal_idle_timeout_seconds" value="<?php echo View::e((string) ($terminal_idle_timeout_seconds ?? '900')); ?>" min="60" />
              <p class="texto" style="font-size:13px; margin-top:8px;">Quando expira, a sessão no WS é encerrada.</p>
            </div>
          </div>

          <div class="grid" style="margin-top:12px;">
            <div>
              <label style="display:block; font-size:13px; margin-bottom:6px;">Modo seguro (bloqueia comandos perigosos)</label>
              <select class="input" name="terminal_safe_mode">
                <option value="1" <?php echo ((string) ($terminal_safe_mode ?? '1')) === '1' ? 'selected' : ''; ?>>Ativado</option>
                <option value="0" <?php echo ((string) ($terminal_safe_mode ?? '1')) === '0' ? 'selected' : ''; ?>>Desativado</option>
              </select>
              <p class="texto" style="font-size:13px; margin-top:8px;">Recomendado manter ativado. Bloqueia pipes/redirecionamentos e comandos sensíveis.</p>
            </div>
          </div>
        </div>

        <div style="margin-top:14px;">
          <button class="botao" type="submit">Salvar</button>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
