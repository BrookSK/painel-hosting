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
        ]);

        return Resposta::html($html);
    }

    public function salvar(Requisicao $req): Resposta
    {
        $asaasToken = trim((string) ($req->post['asaas_token'] ?? ''));
        $asaasUrl = trim((string) ($req->post['asaas_url_base'] ?? ''));
        $asaasSegredo = trim((string) ($req->post['asaas_webhook_segredo'] ?? ''));
        $tolerancia = (int) ($req->post['tolerancia_dias'] ?? 3);
        $evoUrl = trim((string) ($req->post['evolution_url_base'] ?? ''));
        $evoToken = trim((string) ($req->post['evolution_token'] ?? ''));
        $emailAdmin = trim((string) ($req->post['email_admin'] ?? ''));
        $whatsAdminNumero = trim((string) ($req->post['whatsapp_admin_numero'] ?? ''));
        $evoInstance = trim((string) ($req->post['evolution_instance'] ?? ''));
        $sshKeyDir = trim((string) ($req->post['ssh_key_dir'] ?? ''));
        $monitoringToken = trim((string) ($req->post['monitoring_token'] ?? ''));
        $infraNodeMaxUtilPercent = (int) ($req->post['infra_node_max_util_percent'] ?? 85);
        $terminalPorta = (int) ($req->post['terminal_ws_internal_port'] ?? 8081);
        $terminalTokenTtl = (int) ($req->post['terminal_token_ttl_seconds'] ?? 60);
        $terminalIdleTimeout = (int) ($req->post['terminal_idle_timeout_seconds'] ?? 900);
        $terminalSafeMode = (string) ($req->post['terminal_safe_mode'] ?? '0');

        if ($tolerancia <= 0) {
            $tolerancia = 3;
        }

        Settings::definir('asaas.token', $asaasToken);
        Settings::definir('asaas.url_base', $asaasUrl !== '' ? $asaasUrl : 'https://api.asaas.com/v3');
        Settings::definir('asaas.webhook_segredo', $asaasSegredo);
        Settings::definir('cobranca.tolerancia_dias', $tolerancia);
        Settings::definir('whatsapp.evolution.url_base', $evoUrl);
        Settings::definir('whatsapp.evolution.token', $evoToken);
        Settings::definir('alertas.email_admin', $emailAdmin);
        Settings::definir('alertas.whatsapp_admin_numero', $whatsAdminNumero);
        Settings::definir('whatsapp.evolution.instance', $evoInstance);
        Settings::definir('infra.ssh_key_dir', $sshKeyDir);
        Settings::definir('monitoring.token', $monitoringToken);
        Settings::definir('infra.node_max_util_percent', ($infraNodeMaxUtilPercent >= 50 && $infraNodeMaxUtilPercent <= 100) ? $infraNodeMaxUtilPercent : 85);
        Settings::definir('terminal.ws_internal_port', ($terminalPorta > 0 && $terminalPorta <= 65535) ? $terminalPorta : 8081);
        Settings::definir('terminal.token_ttl_seconds', $terminalTokenTtl >= 10 ? $terminalTokenTtl : 60);
        Settings::definir('terminal.idle_timeout_seconds', $terminalIdleTimeout >= 60 ? $terminalIdleTimeout : 900);
        Settings::definir('terminal.safe_mode', ($terminalSafeMode === '1' || $terminalSafeMode === 'on' || $terminalSafeMode === 'true') ? 1 : 0);

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
                'asaas_url_base' => $asaasUrl !== '' ? $asaasUrl : 'https://api.asaas.com/v3',
                'asaas_token_set' => $asaasToken !== '',
                'asaas_webhook_segredo_set' => $asaasSegredo !== '',
                'tolerancia_dias' => $tolerancia,
                'evolution_url_base' => $evoUrl,
                'evolution_token_set' => $evoToken !== '',
                'evolution_instance' => $evoInstance,
                'email_admin' => $emailAdmin,
                'whatsapp_admin_last4' => $whatsLast4,
                'ssh_key_dir' => $sshKeyDir,
                'monitoring_token_set' => $monitoringToken !== '',
                'infra_node_max_util_percent' => ($infraNodeMaxUtilPercent >= 50 && $infraNodeMaxUtilPercent <= 100) ? $infraNodeMaxUtilPercent : 85,
                'terminal_ws_internal_port' => ($terminalPorta > 0 && $terminalPorta <= 65535) ? $terminalPorta : 8081,
                'terminal_token_ttl_seconds' => $terminalTokenTtl >= 10 ? $terminalTokenTtl : 60,
                'terminal_idle_timeout_seconds' => $terminalIdleTimeout >= 60 ? $terminalIdleTimeout : 900,
                'terminal_safe_mode' => (($terminalSafeMode === '1' || $terminalSafeMode === 'on' || $terminalSafeMode === 'true') ? 1 : 0),
            ],
            $req,
        );

        $html = View::renderizar(__DIR__ . '/../../Views/equipe/configuracoes.php', [
            'salvo' => true,
            'erro' => '',
            'asaas_token' => $asaasToken,
            'asaas_url_base' => $asaasUrl !== '' ? $asaasUrl : 'https://api.asaas.com/v3',
            'asaas_webhook_segredo' => $asaasSegredo,
            'tolerancia_dias' => (string) $tolerancia,
            'evolution_url_base' => $evoUrl,
            'evolution_token' => $evoToken,
            'email_admin' => $emailAdmin,
            'whatsapp_admin_numero' => $whatsAdminNumero,
            'evolution_instance' => $evoInstance,
            'ssh_key_dir' => $sshKeyDir,
            'monitoring_token' => $monitoringToken,
            'infra_node_max_util_percent' => (string) (($infraNodeMaxUtilPercent >= 50 && $infraNodeMaxUtilPercent <= 100) ? $infraNodeMaxUtilPercent : 85),
            'terminal_ws_internal_port' => (string) (($terminalPorta > 0 && $terminalPorta <= 65535) ? $terminalPorta : 8081),
            'terminal_token_ttl_seconds' => (string) ($terminalTokenTtl >= 10 ? $terminalTokenTtl : 60),
            'terminal_idle_timeout_seconds' => (string) ($terminalIdleTimeout >= 60 ? $terminalIdleTimeout : 900),
            'terminal_safe_mode' => (($terminalSafeMode === '1' || $terminalSafeMode === 'on' || $terminalSafeMode === 'true') ? '1' : '0'),
        ]);

        return Resposta::html($html);
    }
}
