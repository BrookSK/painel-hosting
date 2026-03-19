<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Equipe;

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
        ]);

        return Resposta::html($html);
    }
}
