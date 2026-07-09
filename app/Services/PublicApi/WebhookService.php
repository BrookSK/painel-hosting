<?php

declare(strict_types=1);

namespace LRV\App\Services\PublicApi;

use LRV\Core\BancoDeDados;

/**
 * Serviço de Webhooks da API Pública.
 * Gerencia configuração, disparo e histórico de webhooks.
 */
final class WebhookService
{
    /** Eventos disponíveis */
    public const EVENTS = [
        'client.created',
        'client.updated',
        'ticket.created',
        'ticket.replied',
        'ticket.closed',
        'hosting.created',
        'hosting.suspended',
        'hosting.cancelled',
        'hosting.restarted',
        'subscription.created',
        'subscription.cancelled',
        'subscription.renewed',
        'payment.received',
        'payment.overdue',
        'payment.refunded',
        'backup.created',
        'backup.restored',
        'domain.added',
        'domain.removed',
        'application.installed',
        'application.removed',
        'monitoring.alert',
    ];

    /**
     * Cria um webhook para um cliente.
     */
    public function criar(int $clienteId, string $url, array $eventos, int $maxRetries = 5, int $timeout = 30): array
    {
        $secret = bin2hex(random_bytes(32));

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare(
            "INSERT INTO api_webhooks (client_id, url, secret, events, max_retries, timeout_seconds)
             VALUES (:client_id, :url, :secret, :events, :max_retries, :timeout)"
        );
        $stmt->execute([
            ':client_id' => $clienteId,
            ':url' => $url,
            ':secret' => $secret,
            ':events' => json_encode($eventos, JSON_UNESCAPED_UNICODE),
            ':max_retries' => $maxRetries,
            ':timeout' => $timeout,
        ]);

        $id = (int) $pdo->lastInsertId();

        return [
            'id' => $id,
            'secret' => $secret,
            'url' => $url,
            'events' => $eventos,
        ];
    }

    /**
     * Lista webhooks de um cliente.
     */
    public function listarPorCliente(int $clienteId): array
    {
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare(
            "SELECT id, url, events, status, max_retries, timeout_seconds,
                    last_triggered_at, last_success_at, last_failure_at, failure_count, created_at
             FROM api_webhooks WHERE client_id = :client_id ORDER BY created_at DESC"
        );
        $stmt->execute([':client_id' => $clienteId]);
        $rows = $stmt->fetchAll() ?: [];

        foreach ($rows as $i => $row) {
            $rows[$i]['events'] = json_decode((string) $row['events'], true) ?: [];
        }

        return $rows;
    }

    /**
     * Dispara um evento para todos os webhooks relevantes de um cliente.
     */
    public function disparar(int $clienteId, string $evento, array $payload): void
    {
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare(
            "SELECT id, url, secret, events, max_retries, timeout_seconds
             FROM api_webhooks
             WHERE client_id = :client_id AND status = 'active'"
        );
        $stmt->execute([':client_id' => $clienteId]);
        $webhooks = $stmt->fetchAll() ?: [];

        foreach ($webhooks as $wh) {
            $events = json_decode((string) $wh['events'], true) ?: [];
            if (!in_array($evento, $events, true) && !in_array('*', $events, true)) {
                continue;
            }

            $this->enviar((int) $wh['id'], $wh['url'], $wh['secret'], $evento, $payload, (int) $wh['timeout_seconds']);
        }
    }

    /**
     * Envia um webhook para uma URL específica.
     */
    private function enviar(int $webhookId, string $url, string $secret, string $evento, array $payload, int $timeout): void
    {
        $body = json_encode([
            'event' => $evento,
            'timestamp' => date('c'),
            'data' => $payload,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $signature = hash_hmac('sha256', $body, $secret);

        $startTime = microtime(true);
        $responseStatus = null;
        $responseBody = null;
        $error = null;
        $success = false;

        try {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $body,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => $timeout,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'X-Webhook-Event: ' . $evento,
                    'X-Webhook-Signature: sha256=' . $signature,
                    'X-Webhook-Timestamp: ' . time(),
                    'User-Agent: LRVCloud-Webhook/1.0',
                ],
            ]);

            $responseBody = curl_exec($ch);
            if ($responseBody === false) {
                $error = curl_error($ch);
                $responseBody = null;
            } else {
                $responseStatus = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $success = $responseStatus >= 200 && $responseStatus < 300;
            }
            curl_close($ch);
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        $durationMs = (int) ((microtime(true) - $startTime) * 1000);

        // Registrar delivery
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare(
            "INSERT INTO api_webhook_deliveries (webhook_id, event_type, payload, response_status, response_body, attempt, duration_ms, success, error_message)
             VALUES (:wh_id, :event, :payload, :status, :resp, 1, :duration, :success, :error)"
        );
        $stmt->execute([
            ':wh_id' => $webhookId,
            ':event' => $evento,
            ':payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            ':status' => $responseStatus,
            ':resp' => $responseBody !== null ? substr((string) $responseBody, 0, 5000) : null,
            ':duration' => $durationMs,
            ':success' => $success ? 1 : 0,
            ':error' => $error,
        ]);

        // Atualizar webhook
        if ($success) {
            $pdo->prepare("UPDATE api_webhooks SET last_triggered_at = NOW(), last_success_at = NOW(), failure_count = 0 WHERE id = :id")
                ->execute([':id' => $webhookId]);
        } else {
            $pdo->prepare("UPDATE api_webhooks SET last_triggered_at = NOW(), last_failure_at = NOW(), failure_count = failure_count + 1 WHERE id = :id")
                ->execute([':id' => $webhookId]);

            // Desabilitar webhook após muitas falhas consecutivas
            $pdo->prepare("UPDATE api_webhooks SET status = 'disabled' WHERE id = :id AND failure_count >= max_retries")
                ->execute([':id' => $webhookId]);
        }
    }

    /**
     * Reenviar manualmente uma delivery.
     */
    public function reenviar(int $deliveryId, int $clienteId): bool
    {
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare(
            "SELECT d.id, d.webhook_id, d.event_type, d.payload,
                    w.url, w.secret, w.timeout_seconds, w.client_id
             FROM api_webhook_deliveries d
             INNER JOIN api_webhooks w ON w.id = d.webhook_id
             WHERE d.id = :id AND w.client_id = :client_id"
        );
        $stmt->execute([':id' => $deliveryId, ':client_id' => $clienteId]);
        $row = $stmt->fetch();

        if (!is_array($row)) {
            return false;
        }

        $payload = json_decode((string) $row['payload'], true) ?: [];
        $this->enviar(
            (int) $row['webhook_id'],
            $row['url'],
            $row['secret'],
            $row['event_type'],
            $payload,
            (int) $row['timeout_seconds'],
        );

        return true;
    }

    /**
     * Atualiza um webhook.
     */
    public function atualizar(int $webhookId, int $clienteId, array $dados): bool
    {
        $pdo = BancoDeDados::pdo();
        $sets = [];
        $params = [':id' => $webhookId, ':client_id' => $clienteId];

        if (isset($dados['url'])) {
            $sets[] = 'url = :url';
            $params[':url'] = $dados['url'];
        }
        if (isset($dados['events'])) {
            $sets[] = 'events = :events';
            $params[':events'] = json_encode($dados['events']);
        }
        if (isset($dados['status'])) {
            $sets[] = 'status = :status';
            $params[':status'] = $dados['status'];
        }
        if (isset($dados['max_retries'])) {
            $sets[] = 'max_retries = :max_retries';
            $params[':max_retries'] = (int) $dados['max_retries'];
        }

        if (empty($sets)) {
            return false;
        }

        $sql = "UPDATE api_webhooks SET " . implode(', ', $sets) . " WHERE id = :id AND client_id = :client_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }

    /**
     * Remove um webhook.
     */
    public function remover(int $webhookId, int $clienteId): bool
    {
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare("DELETE FROM api_webhooks WHERE id = :id AND client_id = :client_id");
        $stmt->execute([':id' => $webhookId, ':client_id' => $clienteId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Lista histórico de deliveries de um webhook.
     */
    public function historicoDeliveries(int $webhookId, int $clienteId, int $pagina = 1, int $porPagina = 25): array
    {
        $pdo = BancoDeDados::pdo();

        // Verificar propriedade
        $check = $pdo->prepare("SELECT id FROM api_webhooks WHERE id = :id AND client_id = :client_id");
        $check->execute([':id' => $webhookId, ':client_id' => $clienteId]);
        if (!$check->fetch()) {
            return ['data' => [], 'meta' => ['total' => 0]];
        }

        $countStmt = $pdo->prepare("SELECT COUNT(*) AS total FROM api_webhook_deliveries WHERE webhook_id = :wh_id");
        $countStmt->execute([':wh_id' => $webhookId]);
        $total = (int) ($countStmt->fetch()['total'] ?? 0);

        $offset = ($pagina - 1) * $porPagina;
        $stmt = $pdo->prepare(
            "SELECT id, event_type, response_status, attempt, duration_ms, success, error_message, delivered_at
             FROM api_webhook_deliveries WHERE webhook_id = :wh_id
             ORDER BY delivered_at DESC LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':wh_id', $webhookId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $porPagina, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return [
            'data' => $stmt->fetchAll() ?: [],
            'meta' => [
                'current_page' => $pagina,
                'per_page' => $porPagina,
                'total' => $total,
                'last_page' => (int) ceil($total / $porPagina),
            ],
        ];
    }

    /**
     * Retorna a lista de eventos disponíveis.
     */
    public function eventosDisponiveis(): array
    {
        return self::EVENTS;
    }

    /**
     * Gera assinatura HMAC para validação do lado do receptor.
     */
    public static function gerarAssinatura(string $payload, string $secret): string
    {
        return hash_hmac('sha256', $payload, $secret);
    }

    /**
     * Valida assinatura de webhook.
     */
    public static function validarAssinatura(string $payload, string $signature, string $secret): bool
    {
        $expected = self::gerarAssinatura($payload, $secret);
        return hash_equals($expected, $signature);
    }
}
