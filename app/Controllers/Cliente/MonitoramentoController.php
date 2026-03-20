<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Cliente;

use LRV\Core\Auth;
use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;

final class MonitoramentoController
{
    public function listar(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) {
            return Resposta::redirecionar('/cliente/entrar');
        }

        $pdo = BancoDeDados::pdo();

        $sql = "SELECT v.id AS vps_id, v.server_id,
                       s.hostname, s.ip_address,
                       m.cpu_usage, m.ram_usage, m.disk_usage, m.timestamp
                FROM vps v
                LEFT JOIN servers s ON s.id = v.server_id
                LEFT JOIN (
                    SELECT sm.*
                    FROM server_metrics sm
                    INNER JOIN (
                        SELECT server_id, MAX(timestamp) AS ts
                        FROM server_metrics
                        GROUP BY server_id
                    ) ult ON ult.server_id = sm.server_id AND ult.ts = sm.timestamp
                ) m ON m.server_id = v.server_id
                WHERE v.client_id = :c
                ORDER BY v.id DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':c' => $clienteId]);
        $linhas = $stmt->fetchAll();

        $html = View::renderizar(__DIR__ . '/../../Views/cliente/monitoramento-listar.php', [
            'linhas' => is_array($linhas) ? $linhas : [],
        ]);

        return Resposta::html($html);
    }

    public function ver(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) {
            return Resposta::redirecionar('/cliente/entrar');
        }

        $vpsId = (int) ($req->query['vps_id'] ?? 0);
        if ($vpsId <= 0) {
            return Resposta::texto('VPS inválida.', 400);
        }

        $pdo = BancoDeDados::pdo();

        $stmt = $pdo->prepare('SELECT id, server_id FROM vps WHERE id = :id AND client_id = :c LIMIT 1');
        $stmt->execute([':id' => $vpsId, ':c' => $clienteId]);
        $vps = $stmt->fetch();

        if (!is_array($vps)) {
            return Resposta::texto('Acesso negado.', 403);
        }

        $serverId = (int) ($vps['server_id'] ?? 0);
        if ($serverId <= 0) {
            $html = View::renderizar(__DIR__ . '/../../Views/cliente/monitoramento-ver.php', [
                'vps' => $vps,
                'servidor' => null,
                'metricas' => [],
            ]);
            return Resposta::html($html);
        }

        $stmt = $pdo->prepare('SELECT id, hostname, ip_address, status FROM servers WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $serverId]);
        $servidor = $stmt->fetch();

        $stmt = $pdo->prepare('SELECT cpu_usage, ram_usage, disk_usage, timestamp FROM server_metrics WHERE server_id = :id ORDER BY timestamp DESC LIMIT 200');
        $stmt->execute([':id' => $serverId]);
        $metricas = $stmt->fetchAll();

        $html = View::renderizar(__DIR__ . '/../../Views/cliente/monitoramento-ver.php', [
            'vps' => $vps,
            'servidor' => is_array($servidor) ? $servidor : null,
            'metricas' => is_array($metricas) ? $metricas : [],
        ]);

        return Resposta::html($html);
    }
}
