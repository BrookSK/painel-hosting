<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Equipe;

use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;

final class MonitoramentoController
{
    public function listar(Requisicao $req): Resposta
    {
        $pdo = BancoDeDados::pdo();

        $sql = "SELECT s.id, s.hostname, s.ip_address, s.status,
                       m.cpu_usage, m.ram_usage, m.disk_usage, m.timestamp
                FROM servers s
                LEFT JOIN (
                    SELECT sm.*
                    FROM server_metrics sm
                    INNER JOIN (
                        SELECT server_id, MAX(timestamp) AS ts
                        FROM server_metrics
                        GROUP BY server_id
                    ) ult ON ult.server_id = sm.server_id AND ult.ts = sm.timestamp
                ) m ON m.server_id = s.id
                ORDER BY s.id DESC";

        $stmt = $pdo->query($sql);
        $servidores = $stmt->fetchAll();

        $html = View::renderizar(__DIR__ . '/../../Views/equipe/monitoramento-listar.php', [
            'servidores' => is_array($servidores) ? $servidores : [],
        ]);

        return Resposta::html($html);
    }

    public function ver(Requisicao $req): Resposta
    {
        $serverId = (int) ($req->query['id'] ?? 0);
        if ($serverId <= 0) {
            return Resposta::texto('Servidor inválido.', 400);
        }

        $pdo = BancoDeDados::pdo();

        $st = $pdo->prepare('SELECT id, hostname, ip_address, status FROM servers WHERE id = :id');
        $st->execute([':id' => $serverId]);
        $servidor = $st->fetch();

        if (!is_array($servidor)) {
            return Resposta::texto('Servidor não encontrado.', 404);
        }

        $st = $pdo->prepare('SELECT cpu_usage, ram_usage, disk_usage, timestamp FROM server_metrics WHERE server_id = :id ORDER BY timestamp DESC LIMIT 200');
        $st->execute([':id' => $serverId]);
        $metricas = $st->fetchAll();

        $html = View::renderizar(__DIR__ . '/../../Views/equipe/monitoramento-ver.php', [
            'servidor' => $servidor,
            'metricas' => is_array($metricas) ? $metricas : [],
        ]);

        return Resposta::html($html);
    }
}
