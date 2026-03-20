<?php

declare(strict_types=1);

namespace LRV\App\Services\Audit;

use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;

final class AuditLogService
{
    public function registrar(
        string $actorType,
        ?int $actorId,
        string $action,
        string $entity,
        int|string|null $entityId,
        array $payload,
        ?Requisicao $req = null,
    ): void {
        $actorType = trim($actorType);
        if ($actorType === '') {
            $actorType = 'system';
        }

        $action = trim($action);
        if ($action === '') {
            $action = 'unknown';
        }

        $entity = trim($entity);
        if ($entity === '') {
            $entity = 'unknown';
        }

        $ip = null;
        $ua = null;

        if ($req instanceof Requisicao) {
            $ip = $this->extrairIp($req);
            $ua = $this->normalizarUa((string) ($req->headers['user-agent'] ?? ''));
        }

        $payloadJson = null;
        if ($payload !== []) {
            $payloadJson = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        if ($payloadJson !== null && strlen($payloadJson) > 200_000) {
            $payloadJson = substr($payloadJson, 0, 200_000);
        }

        if ($ua !== null && strlen($ua) > 255) {
            $ua = substr($ua, 0, 255);
        }

        $eid = null;
        if ($entityId !== null && $entityId !== '') {
            if (is_int($entityId)) {
                $eid = $entityId;
            } elseif (is_string($entityId) && preg_match('/^\d+$/', $entityId) === 1) {
                $eid = (int) $entityId;
            }
        }

        try {
            $pdo = BancoDeDados::pdo();
            $stmt = $pdo->prepare('INSERT INTO audit_logs (actor_type, actor_id, action, entity, entity_id, payload_json, ip_address, user_agent, created_at) VALUES (:at,:ai,:ac,:en,:eid,:pj,:ip,:ua,:cr)');
            $stmt->execute([
                ':at' => $actorType,
                ':ai' => $actorId,
                ':ac' => $action,
                ':en' => $entity,
                ':eid' => $eid,
                ':pj' => $payloadJson,
                ':ip' => $ip,
                ':ua' => $ua,
                ':cr' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            // auditoria nao pode derrubar request
        }
    }

    private function extrairIp(Requisicao $req): ?string
    {
        $xff = trim((string) ($req->headers['x-forwarded-for'] ?? ''));
        if ($xff !== '') {
            $partes = array_map('trim', explode(',', $xff));
            $ip = (string) ($partes[0] ?? '');
            if ($ip !== '') {
                return $ip;
            }
        }

        if (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = trim((string) $_SERVER['REMOTE_ADDR']);
            return $ip !== '' ? $ip : null;
        }

        return null;
    }

    private function normalizarUa(string $ua): ?string
    {
        $ua = trim($ua);
        if ($ua === '') {
            return null;
        }
        return $ua;
    }
}
