<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Api;

use LRV\Core\BancoDeDados;
use LRV\Core\ConfiguracoesSistema;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\Settings;

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

        // Verificar alertas para o servidor de email
        $this->verificarAlertasEmailServer($serverId, $cpu, $ram, $disk);

        return Resposta::json(['ok' => true]);
    }

    private function verificarAlertasEmailServer(int $serverId, float $cpu, float $ram, float $disk): void
    {
        $emailServerIp = trim((string) Settings::obter('email.server_ip', ''));
        if ($emailServerIp === '' || (string) Settings::obter('email.monitoring_enabled', '0') !== '1') {
            return;
        }

        // Verificar se este servidor é o servidor de email
        $pdo = BancoDeDados::pdo();
        $st = $pdo->prepare('SELECT ip_address FROM servers WHERE id = :id LIMIT 1');
        $st->execute([':id' => $serverId]);
        $srv = $st->fetch();
        if (!is_array($srv) || trim((string) $srv['ip_address']) !== $emailServerIp) {
            return;
        }

        $alertCpu  = (int) Settings::obter('email.alert_cpu', 80);
        $alertRam  = (int) Settings::obter('email.alert_ram', 85);
        $alertDisk = (int) Settings::obter('email.alert_disk', 90);

        $alertas = [];
        if ($cpu >= $alertCpu)  $alertas[] = "CPU: {$cpu}% (limite: {$alertCpu}%)";
        if ($ram >= $alertRam)  $alertas[] = "RAM: {$ram}% (limite: {$alertRam}%)";
        if ($disk >= $alertDisk) $alertas[] = "Disco: {$disk}% (limite: {$alertDisk}%)";

        if (empty($alertas)) {
            return;
        }

        // Rate limit: não enviar mais de 1 alerta a cada 30 minutos
        $ultimoAlerta = (string) Settings::obter('email.last_alert_at', '');
        if ($ultimoAlerta !== '' && (time() - strtotime($ultimoAlerta)) < 1800) {
            return;
        }

        Settings::definir('email.last_alert_at', date('Y-m-d H:i:s'));

        $msg = "⚠️ Alerta: Servidor de E-mail ({$emailServerIp})\n\n"
            . implode("\n", $alertas)
            . "\n\nConsidere aumentar os recursos do servidor.";

        try {
            $svc = new \LRV\App\Services\Alertas\NotificacoesService(new \LRV\App\Services\Http\ClienteHttp());
            $svc->alertarAdmin('Servidor de E-mail — Recursos', $msg);
        } catch (\Throwable) {
            // silencioso
        }
    }
}
