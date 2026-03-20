<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Equipe;

use LRV\App\Services\Audit\AuditLogService;
use LRV\Core\ConfiguracoesSistema;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\Settings;
use LRV\Core\View;

final class ConfiguracoesController
{
    public function formulario(Requisicao $req): Resposta
    {
        $html = View::renderizar(__DIR__ . '/../../Views/equipe/configuracoes.php', [
            'salvo' => false,
            'erro' => '',
            'asaas_token' => ConfiguracoesSistema::asaasToken(),
            'asaas_url_base' => ConfiguracoesSistema::asaasUrlBase(),
            'asaas_webhook_segredo' => ConfiguracoesSistema::webhookSegredoAsaas(),
            'stripe_secret_key' => ConfiguracoesSistema::stripeSecretKey(),
            'stripe_webhook_secret' => ConfiguracoesSistema::stripeWebhookSecret(),
            'app_url_base' => ConfiguracoesSistema::appUrlBase(),
            'tolerancia_dias' => (string) ConfiguracoesSistema::toleranciaPagamentoDias(),
            'evolution_url_base' => ConfiguracoesSistema::evolutionUrlBase(),
            'evolution_token' => ConfiguracoesSistema::evolutionToken(),
            'email_admin' => ConfiguracoesSistema::emailAdmin(),
            'whatsapp_admin_numero' => ConfiguracoesSistema::whatsappAdminNumero(),
            'evolution_instance' => ConfiguracoesSistema::evolutionInstance(),
            'ssh_key_dir' => ConfiguracoesSistema::sshKeyDir(),
            'monitoring_token' => ConfiguracoesSistema::monitoringToken(),
            'infra_node_max_util_percent' => (string) ConfiguracoesSistema::infraNodeMaxUtilPercent(),
            'terminal_ws_internal_port' => (string) ConfiguracoesSistema::terminalWsInternalPort(),
            'terminal_token_ttl_seconds' => (string) ConfiguracoesSistema::terminalTokenTtlSegundos(),
            'terminal_idle_timeout_seconds' => (string) ConfiguracoesSistema::terminalIdleTimeoutSegundos(),
            'terminal_safe_mode' => ConfiguracoesSistema::terminalSafeModeHabilitado() ? '1' : '0',
            'mailcow_url'    => (string) Settings::obter('email.mailcow_url', ''),
            'mailcow_key'    => (string) Settings::obter('email.mailcow_key', ''),
            'webmail_url'    => (string) Settings::obter('email.webmail_url', ''),
            'chat_ws_port'   => (string) Settings::obter('chat.ws_port', '8082'),
        ]);

        return Resposta::html($html);
    }

    public function salvar(Requisicao $req): Resposta
    {
        $in = $req->input();

        $asaasToken = $in->postString('asaas_token', 300, false);
        $asaasUrl = $in->postUrl('asaas_url_base', 255, false);
        $asaasSegredo = $in->postString('asaas_webhook_segredo', 255, false);
        $stripeSecretKey = $in->postString('stripe_secret_key', 255, false);
        $stripeWebhookSecret = $in->postString('stripe_webhook_secret', 255, false);
        $appUrlBase = $in->postUrl('app_url_base', 255, false);

        $tolerancia = $in->postInt('tolerancia_dias', 1, 365, false);

        $evoUrl = $in->postUrl('evolution_url_base', 255, false);
        $evoToken = $in->postString('evolution_token', 255, false);
        $emailAdmin = $in->postEmail('email_admin', 190, false);

        $whatsAdminNumero = $in->postString('whatsapp_admin_numero', 30, false);
        $evoInstance = $in->postString('evolution_instance', 190, false);
        $sshKeyDir = $in->postString('ssh_key_dir', 400, false);
        $monitoringToken = $in->postString('monitoring_token', 255, false);

        $infraNodeMaxUtilPercent = $in->postInt('infra_node_max_util_percent', 50, 100, false);
        $terminalPorta = $in->postInt('terminal_ws_internal_port', 1, 65535, false);
        $terminalTokenTtl = $in->postInt('terminal_token_ttl_seconds', 10, 86400, false);
        $terminalIdleTimeout = $in->postInt('terminal_idle_timeout_seconds', 60, 604800, false);
        $terminalSafeMode = $in->postEnum('terminal_safe_mode', ['0', '1', 'on', 'true'], '0');

        $mailcowUrl    = $in->postUrl('mailcow_url', 255, false);
        $mailcowKey    = $in->postString('mailcow_key', 255, false);
        $webmailUrl    = $in->postUrl('webmail_url', 255, false);
        $chatWsPort    = $in->postInt('chat_ws_port', 1, 65535, false);

        if ($in->temErros()) {
            $html = View::renderizar(__DIR__ . '/../../Views/equipe/configuracoes.php', [
                'salvo' => false,
                'erro' => $in->primeiroErro(),
                'asaas_token' => $asaasToken,
                'asaas_url_base' => $asaasUrl,
                'asaas_webhook_segredo' => $asaasSegredo,
                'stripe_secret_key' => $stripeSecretKey,
                'stripe_webhook_secret' => $stripeWebhookSecret,
                'app_url_base' => $appUrlBase,
                'tolerancia_dias' => $tolerancia > 0 ? (string) $tolerancia : '3',
                'evolution_url_base' => $evoUrl,
                'evolution_token' => $evoToken,
                'email_admin' => $emailAdmin,
                'whatsapp_admin_numero' => $whatsAdminNumero,
                'evolution_instance' => $evoInstance,
                'ssh_key_dir' => $sshKeyDir,
                'monitoring_token' => $monitoringToken,
                'infra_node_max_util_percent' => $infraNodeMaxUtilPercent > 0 ? (string) $infraNodeMaxUtilPercent : '85',
                'terminal_ws_internal_port' => $terminalPorta > 0 ? (string) $terminalPorta : '8081',
                'terminal_token_ttl_seconds' => $terminalTokenTtl > 0 ? (string) $terminalTokenTtl : '60',
                'terminal_idle_timeout_seconds' => $terminalIdleTimeout > 0 ? (string) $terminalIdleTimeout : '900',
                'terminal_safe_mode' => (($terminalSafeMode === '1' || $terminalSafeMode === 'on' || $terminalSafeMode === 'true') ? '1' : '0'),
                'mailcow_url'  => $mailcowUrl,
                'mailcow_key'  => $mailcowKey,
                'webmail_url'  => $webmailUrl,
                'chat_ws_port' => $chatWsPort > 0 ? (string) $chatWsPort : '8082',
            ]);

            return Resposta::html($html, 422);
        }

        if ($asaasUrl === '') {
            $asaasUrl = 'https://api.asaas.com/v3';
        }

        Settings::definir('asaas.token', $asaasToken);
        Settings::definir('asaas.url_base', $asaasUrl);
        Settings::definir('asaas.webhook_segredo', $asaasSegredo);
        Settings::definir('stripe.secret_key', $stripeSecretKey);
        Settings::definir('stripe.webhook_secret', $stripeWebhookSecret);
        Settings::definir('app.url_base', rtrim($appUrlBase, '/'));
        Settings::definir('cobranca.tolerancia_dias', $tolerancia > 0 ? $tolerancia : 3);
        Settings::definir('whatsapp.evolution.url_base', $evoUrl);
        Settings::definir('whatsapp.evolution.token', $evoToken);
        Settings::definir('alertas.email_admin', $emailAdmin);
        Settings::definir('alertas.whatsapp_admin_numero', $whatsAdminNumero);
        Settings::definir('whatsapp.evolution.instance', $evoInstance);
        Settings::definir('infra.ssh_key_dir', $sshKeyDir);
        Settings::definir('monitoring.token', $monitoringToken);
        Settings::definir('infra.node_max_util_percent', $infraNodeMaxUtilPercent > 0 ? $infraNodeMaxUtilPercent : 85);
        Settings::definir('terminal.ws_internal_port', $terminalPorta > 0 ? $terminalPorta : 8081);
        Settings::definir('terminal.token_ttl_seconds', $terminalTokenTtl > 0 ? $terminalTokenTtl : 60);
        Settings::definir('terminal.idle_timeout_seconds', $terminalIdleTimeout > 0 ? $terminalIdleTimeout : 900);
        Settings::definir('terminal.safe_mode', ($terminalSafeMode === '1' || $terminalSafeMode === 'on' || $terminalSafeMode === 'true') ? 1 : 0);
        Settings::definir('email.mailcow_url', $mailcowUrl);
        Settings::definir('email.mailcow_key', $mailcowKey);
        Settings::definir('email.webmail_url', $webmailUrl);
        Settings::definir('chat.ws_port', $chatWsPort > 0 ? $chatWsPort : 8082);

        $whatsLast4 = '';
        if ($whatsAdminNumero !== '') {
            $digits = preg_replace('/\D+/', '', $whatsAdminNumero);
            $whatsLast4 = (string) substr((string) $digits, -4);
        }

        (new AuditLogService())->registrar(
            'team',
            \LRV\Core\Auth::equipeId(),
            'settings.update',
            'settings',
            null,
            [
                'asaas_url_base' => $asaasUrl,
                'asaas_token_set' => $asaasToken !== '',
                'asaas_webhook_segredo_set' => $asaasSegredo !== '',
                'stripe_secret_key_set' => $stripeSecretKey !== '',
                'stripe_webhook_secret_set' => $stripeWebhookSecret !== '',
                'app_url_base' => rtrim($appUrlBase, '/'),
                'tolerancia_dias' => $tolerancia > 0 ? $tolerancia : 3,
                'evolution_url_base' => $evoUrl,
                'evolution_token_set' => $evoToken !== '',
                'evolution_instance' => $evoInstance,
                'email_admin' => $emailAdmin,
                'whatsapp_admin_last4' => $whatsLast4,
                'ssh_key_dir' => $sshKeyDir,
                'monitoring_token_set' => $monitoringToken !== '',
                'infra_node_max_util_percent' => $infraNodeMaxUtilPercent > 0 ? $infraNodeMaxUtilPercent : 85,
                'terminal_ws_internal_port' => $terminalPorta > 0 ? $terminalPorta : 8081,
                'terminal_token_ttl_seconds' => $terminalTokenTtl > 0 ? $terminalTokenTtl : 60,
                'terminal_idle_timeout_seconds' => $terminalIdleTimeout > 0 ? $terminalIdleTimeout : 900,
                'terminal_safe_mode' => (($terminalSafeMode === '1' || $terminalSafeMode === 'on' || $terminalSafeMode === 'true') ? 1 : 0),
            ],
            $req,
        );

        $html = View::renderizar(__DIR__ . '/../../Views/equipe/configuracoes.php', [
            'salvo' => true,
            'erro' => '',
            'asaas_token' => $asaasToken,
            'asaas_url_base' => $asaasUrl,
            'asaas_webhook_segredo' => $asaasSegredo,
            'stripe_secret_key' => $stripeSecretKey,
            'stripe_webhook_secret' => $stripeWebhookSecret,
            'app_url_base' => rtrim($appUrlBase, '/'),
            'tolerancia_dias' => (string) ($tolerancia > 0 ? $tolerancia : 3),
            'evolution_url_base' => $evoUrl,
            'evolution_token' => $evoToken,
            'email_admin' => $emailAdmin,
            'whatsapp_admin_numero' => $whatsAdminNumero,
            'evolution_instance' => $evoInstance,
            'ssh_key_dir' => $sshKeyDir,
            'monitoring_token' => $monitoringToken,
            'infra_node_max_util_percent' => (string) ($infraNodeMaxUtilPercent > 0 ? $infraNodeMaxUtilPercent : 85),
            'terminal_ws_internal_port' => (string) ($terminalPorta > 0 ? $terminalPorta : 8081),
            'terminal_token_ttl_seconds' => (string) ($terminalTokenTtl > 0 ? $terminalTokenTtl : 60),
            'terminal_idle_timeout_seconds' => (string) ($terminalIdleTimeout > 0 ? $terminalIdleTimeout : 900),
            'terminal_safe_mode' => (($terminalSafeMode === '1' || $terminalSafeMode === 'on' || $terminalSafeMode === 'true') ? '1' : '0'),
            'mailcow_url'  => $mailcowUrl,
            'mailcow_key'  => $mailcowKey,
            'webmail_url'  => $webmailUrl,
            'chat_ws_port' => $chatWsPort > 0 ? (string) $chatWsPort : '8082',
        ]);

        return Resposta::html($html);
    }
}
