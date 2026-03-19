<?php

declare(strict_types=1);

namespace LRV\App\Services\Status;

use LRV\Core\BancoDeDados;

final class StatusRepository
{
    public function obterServicePorKey(string $key): ?array
    {
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT id, `key`, status, last_check_at, last_ok_at, last_error FROM status_services WHERE `key` = :k LIMIT 1');
        $stmt->execute([':k' => $key]);
        $r = $stmt->fetch();
        return is_array($r) ? $r : null;
    }

    public function upsertService(
        string $key,
        string $name,
        ?string $description,
        string $scope,
        ?int $clientId,
        ?int $serverId,
        ?int $vpsId,
        string $status,
        ?\DateTimeInterface $lastCheckAt,
        ?\DateTimeInterface $lastOkAt,
        ?string $lastError,
        ?array $meta
    ): int {
        $pdo = BancoDeDados::pdo();

        $stmt = $pdo->prepare('SELECT id FROM status_services WHERE `key` = :k LIMIT 1');
        $stmt->execute([':k' => $key]);
        $r = $stmt->fetch();

        $agora = date('Y-m-d H:i:s');

        $metaStr = $meta === null ? null : json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $params = [
            ':k' => $key,
            ':n' => $name,
            ':d' => $description,
            ':sc' => $scope,
            ':cid' => $clientId,
            ':sid' => $serverId,
            ':vid' => $vpsId,
            ':st' => $status,
            ':lca' => $lastCheckAt ? $lastCheckAt->format('Y-m-d H:i:s') : null,
            ':loa' => $lastOkAt ? $lastOkAt->format('Y-m-d H:i:s') : null,
            ':le' => $lastError,
            ':mj' => $metaStr,
            ':u' => $agora,
        ];

        if (is_array($r)) {
            $id = (int) ($r['id'] ?? 0);

            $up = $pdo->prepare('UPDATE status_services SET name=:n, description=:d, scope=:sc, client_id=:cid, server_id=:sid, vps_id=:vid, status=:st, last_check_at=:lca, last_ok_at=:loa, last_error=:le, meta_json=:mj, updated_at=:u WHERE id=:id');
            $params[':id'] = $id;
            $up->execute($params);

            return $id;
        }

        $ins = $pdo->prepare('INSERT INTO status_services (`key`, name, description, scope, client_id, server_id, vps_id, status, last_check_at, last_ok_at, last_error, meta_json, created_at, updated_at) VALUES (:k,:n,:d,:sc,:cid,:sid,:vid,:st,:lca,:loa,:le,:mj,:c,:u)');
        $params[':c'] = $agora;
        $ins->execute($params);

        return (int) $pdo->lastInsertId();
    }

    public function registrarLog(
        int $serviceId,
        string $status,
        ?string $message,
        ?array $metrics,
        \DateTimeInterface $checkedAt
    ): void {
        $pdo = BancoDeDados::pdo();

        $metricsStr = $metrics === null ? null : json_encode($metrics, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $stmt = $pdo->prepare('INSERT INTO status_logs (service_id, status, message, metrics_json, checked_at, created_at) VALUES (:sid,:st,:m,:mj,:ca,:c)');
        $stmt->execute([
            ':sid' => $serviceId,
            ':st' => $status,
            ':m' => $message,
            ':mj' => $metricsStr,
            ':ca' => $checkedAt->format('Y-m-d H:i:s'),
            ':c' => date('Y-m-d H:i:s'),
        ]);
    }
}
