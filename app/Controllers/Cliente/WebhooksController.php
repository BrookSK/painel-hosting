<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Cliente;

use LRV\App\Services\PublicApi\WebhookService;
use LRV\Core\Auth;
use LRV\Core\Csrf;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;

/**
 * Tela de Webhooks no painel do cliente.
 * Permite criar, listar, remover webhooks e ver o histórico de entregas,
 * sem precisar usar a API diretamente. Reaproveita o WebhookService.
 */
final class WebhooksController
{
    public function listar(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) {
            return Resposta::redirecionar('/cliente/entrar');
        }

        $service = new WebhookService();
        $webhooks = $service->listarPorCliente($clienteId);

        // Secret recém-criado fica na sessão para exibir uma única vez
        $novoSecret = (string) ($_SESSION['webhook_secret_criado'] ?? '');
        $novoUrl = (string) ($_SESSION['webhook_url_criado'] ?? '');
        unset($_SESSION['webhook_secret_criado'], $_SESSION['webhook_url_criado']);

        $html = View::renderizar(__DIR__ . '/../../Views/cliente/webhooks.php', [
            'webhooks'   => $webhooks,
            'novoSecret' => $novoSecret,
            'novoUrl'    => $novoUrl,
            'sucesso'    => (string) ($req->query['ok'] ?? ''),
            'erro'       => (string) ($req->query['erro'] ?? ''),
        ]);

        return Resposta::html($html);
    }

    public function formularioCriar(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) {
            return Resposta::redirecionar('/cliente/entrar');
        }

        $html = View::renderizar(__DIR__ . '/../../Views/cliente/webhooks-criar.php', [
            'eventos' => (new WebhookService())->eventosDisponiveis(),
            'erro'    => (string) ($req->query['erro'] ?? ''),
        ]);

        return Resposta::html($html);
    }

    public function criar(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) {
            return Resposta::redirecionar('/cliente/entrar');
        }
        if (!Csrf::validar((string) ($req->post['_csrf'] ?? ''))) {
            return Resposta::redirecionar('/cliente/webhooks?erro=csrf');
        }

        $url = trim((string) ($req->post['url'] ?? ''));
        $eventos = is_array($req->post['eventos'] ?? null) ? $req->post['eventos'] : [];

        if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL) || !str_starts_with($url, 'https://')) {
            return Resposta::redirecionar('/cliente/webhooks/novo?erro=url');
        }

        // Validar eventos contra a lista oficial
        $validos = array_values(array_intersect($eventos, WebhookService::EVENTS));
        if (empty($validos)) {
            return Resposta::redirecionar('/cliente/webhooks/novo?erro=eventos');
        }

        $service = new WebhookService();
        $resultado = $service->criar($clienteId, $url, $validos);

        // Guardar o secret para exibir uma única vez
        $_SESSION['webhook_secret_criado'] = (string) ($resultado['secret'] ?? '');
        $_SESSION['webhook_url_criado'] = $url;

        return Resposta::redirecionar('/cliente/webhooks?ok=criado');
    }

    public function remover(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) {
            return Resposta::redirecionar('/cliente/entrar');
        }
        if (!Csrf::validar((string) ($req->post['_csrf'] ?? ''))) {
            return Resposta::redirecionar('/cliente/webhooks?erro=csrf');
        }

        $webhookId = (int) ($req->post['webhook_id'] ?? 0);
        if ($webhookId <= 0) {
            return Resposta::redirecionar('/cliente/webhooks?erro=invalido');
        }

        $ok = (new WebhookService())->remover($webhookId, $clienteId);
        return Resposta::redirecionar('/cliente/webhooks?ok=' . ($ok ? 'removido' : 'nao_encontrado'));
    }

    /**
     * Histórico de entregas de um webhook (JSON, carregado via fetch na tela).
     */
    public function deliveries(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) {
            return Resposta::json(['ok' => false], 401);
        }

        $webhookId = (int) ($req->query['id'] ?? 0);
        if ($webhookId <= 0) {
            return Resposta::json(['ok' => false, 'erro' => 'invalido'], 400);
        }

        $resultado = (new WebhookService())->historicoDeliveries($webhookId, $clienteId, 1, 25);
        $deliveries = is_array($resultado['data'] ?? null) ? $resultado['data'] : [];
        return Resposta::json(['ok' => true, 'deliveries' => $deliveries]);
    }
}
