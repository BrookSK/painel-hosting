<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Api;

use LRV\Core\BancoDeDados;
use LRV\Core\ConfiguracoesSistema;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;

final class MetricsController
{
    public function registrarServidor(Requisicao $req): Resposta
    {
        $token = (string) ($req->headers['x-monitoring-token'] ?? '');
        $esperado = ConfiguracoesSistema::monitoringToken();

        if ($esperado === '' || $token === '' || !hash_equals($esperado, $token)) {
            return Resposta::json(['ok' => false, 'erro' => 'unauthorized'], 401);
        }

        $dados = $req->json();

        $serverId = (int) ($dados['server_id'] ?? 0);
        $cpu = (float) ($dados['cpu_usage'] ?? -1);
        $ram = (float) ($dados['ram_usage'] ?? -1);
        $disk = (float) ($dados['disk_usage'] ?? -1);
        $timestamp = (string) ($dados['timestamp'] ?? '');

        if ($serverId <= 0 || $cpu < 0 || $ram < 0 || $disk < 0) {
            return Resposta::json(['ok' => false, 'erro' => 'invalid_payload'], 422);
        }

        if ($cpu > 100 || $ram > 100 || $disk > 100) {
            return Resposta::json(['ok' => false, 'erro' => 'invalid_values'], 422);
        }

        $ts = time();
        if (trim($timestamp) !== '') {
            $p = strtotime($timestamp);
            if ($p !== false) {
                $ts = $p;
            }
        }

        $pdo = BancoDeDados::pdo();

        $st = $pdo->prepare('SELECT id FROM servers WHERE id = :id');
        $st->execute([':id' => $serverId]);
        $srv = $st->fetch();
        if (!is_array($srv)) {
            return Resposta::json(['ok' => false, 'erro' => 'server_not_found'], 404);
        }

        $ins = $pdo->prepare('INSERT INTO server_metrics (server_id, cpu_usage, ram_usage, disk_usage, timestamp) VALUES (:sid,:cpu,:ram,:disk,:ts)');
        $ins->execute([
            ':sid' => $serverId,
            ':cpu' => $cpu,
            ':ram' => $ram,
            ':disk' => $disk,
            ':ts' => date('Y-m-d H:i:s', $ts),
        ]);

        return Resposta::json(['ok' => true]);
    }
}
