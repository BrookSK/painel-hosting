<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Api\Public;

use LRV\App\Services\PublicApi\WebhookService;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;

/**
 * Endpoints de gerenciamento de Webhooks.
 * - GET    /api/v1/webhooks                → Listar webhooks
 * - POST   /api/v1/webhooks                → Criar webhook
 * - PUT    /api/v1/webhooks?id=            → Atualizar webhook
 * - DELETE /api/v1/webhooks?id=            → Remover webhook
 * - GET    /api/v1/webhooks/events         → Listar eventos disponíveis
 * - GET    /api/v1/webhooks/deliveries?id= → Histórico de entregas
 * - POST   /api/v1/webhooks/resend?id=     → Reenviar delivery
 */
final class WebhooksController extends BaseApiController
{
    /**
     * GET /api/v1/webhooks
     */
    public function listar(Requisicao $req): Resposta
    {
        if (!$this->temEscopo($req, 'webhooks.read')) {
            return $this->proibido('Scope webhooks.read is required.');
        }

        $clienteId = $this->clienteId($req);
        $service = new WebhookService();
        $webhooks = $service->listarPorCliente($clienteId);

        return $this->sucesso($webhooks);
    }

    /**
     * POST /api/v1/webhooks
     */
    public function criar(Requisicao $req): Resposta
    {
        if (!$this->temEscopo($req, 'webhooks.write')) {
            return $this->proibido('Scope webhooks.write is required.');
        }

        $dados = $req->json();
        $validacao = $this->validarObrigatorios($dados, ['url', 'events']);
        if ($validacao !== null) {
            return $validacao;
        }

        $url = trim((string) ($dados['url'] ?? ''));
        $events = $dados['events'] ?? [];

        // Validar URL
        if (!filter_var($url, FILTER_VALIDATE_URL) || !str_starts_with($url, 'https://')) {
            return $this->validacaoFalhou([
                ['field' => 'url', 'message' => 'URL must be a valid HTTPS URL.'],
            ]);
        }

        // Validar eventos
        if (!is_array($events) || empty($events)) {
            return $this->validacaoFalhou([
                ['field' => 'events', 'message' => 'At least one event is required.'],
            ]);
        }

        $availableEvents = WebhookService::EVENTS;
        $invalid = array_diff($events, $availableEvents, ['*']);
        if (!empty($invalid)) {
            return $this->validacaoFalhou([
                ['field' => 'events', 'message' => 'Invalid events: ' . implode(', ', $invalid)],
            ]);
        }

        $maxRetries = (int) ($dados['max_retries'] ?? 5);
        $timeout = (int) ($dados['timeout_seconds'] ?? 30);

        $service = new WebhookService();
        $result = $service->criar($this->clienteId($req), $url, $events, $maxRetries, $timeout);

        return $this->criado([
            'id' => $result['id'],
            'url' => $result['url'],
            'events' => $result['events'],
            'secret' => $result['secret'],
            'warning' => 'Store the secret securely. It will not be shown again.',
        ], 'Webhook created successfully.');
    }

    /**
     * PUT /api/v1/webhooks?id=
     */
    public function atualizar(Requisicao $req): Resposta
    {
        if (!$this->temEscopo($req, 'webhooks.write')) {
            return $this->proibido('Scope webhooks.write is required.');
        }

        $webhookId = (int) ($req->query['id'] ?? 0);
        if ($webhookId <= 0) {
            return $this->erro('MISSING_ID', 'The webhook ID is required.', 400);
        }

        $dados = $req->json();
        $service = new WebhookService();
        $updated = $service->atualizar($webhookId, $this->clienteId($req), $dados);

        if (!$updated) {
            return $this->naoEncontrado('Webhook');
        }

        return $this->sucesso(null, 'Webhook updated successfully.');
    }

    /**
     * DELETE /api/v1/webhooks?id=
     */
    public function remover(Requisicao $req): Resposta
    {
        if (!$this->temEscopo($req, 'webhooks.write')) {
            return $this->proibido('Scope webhooks.write is required.');
        }

        $webhookId = (int) ($req->query['id'] ?? 0);
        if ($webhookId <= 0) {
            return $this->erro('MISSING_ID', 'The webhook ID is required.', 400);
        }

        $service = new WebhookService();
        $removed = $service->remover($webhookId, $this->clienteId($req));

        if (!$removed) {
            return $this->naoEncontrado('Webhook');
        }

        return $this->sucesso(null, 'Webhook deleted successfully.');
    }

    /**
     * GET /api/v1/webhooks/events
     */
    public function eventos(Requisicao $req): Resposta
    {
        $service = new WebhookService();
        return $this->sucesso($service->eventosDisponiveis());
    }

    /**
     * GET /api/v1/webhooks/deliveries?webhook_id=&page=&per_page=
     */
    public function deliveries(Requisicao $req): Resposta
    {
        if (!$this->temEscopo($req, 'webhooks.read')) {
            return $this->proibido('Scope webhooks.read is required.');
        }

        $webhookId = (int) ($req->query['webhook_id'] ?? 0);
        if ($webhookId <= 0) {
            return $this->erro('MISSING_ID', 'The webhook_id parameter is required.', 400);
        }

        $pag = $this->paginacao($req);
        $service = new WebhookService();
        $result = $service->historicoDeliveries($webhookId, $this->clienteId($req), $pag['page'], $pag['per_page']);

        return $this->paginado($result['data'], $result['meta'], '/api/v1/webhooks/deliveries?webhook_id=' . $webhookId);
    }

    /**
     * POST /api/v1/webhooks/resend?delivery_id=
     */
    public function reenviar(Requisicao $req): Resposta
    {
        if (!$this->temEscopo($req, 'webhooks.write')) {
            return $this->proibido('Scope webhooks.write is required.');
        }

        $deliveryId = (int) ($req->query['delivery_id'] ?? ($req->json()['delivery_id'] ?? 0));
        if ($deliveryId <= 0) {
            return $this->erro('MISSING_ID', 'The delivery_id is required.', 400);
        }

        $service = new WebhookService();
        $sent = $service->reenviar($deliveryId, $this->clienteId($req));

        if (!$sent) {
            return $this->naoEncontrado('Delivery');
        }

        return $this->sucesso(null, 'Webhook delivery resent successfully.');
    }
}
