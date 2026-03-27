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

        // Verificar alertas para servidores gerenciados (overselling)
        $this->verificarAlertasManagedServer($pdo, $serverId, $cpu, $ram, $disk);

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

    /**
     * Alerta quando um servidor gerenciado (overselling) atinge uso real alto.
     * Thresholds: CPU 80%, RAM 75%, Disco 85%.
     */
    private function verificarAlertasManagedServer(\PDO $pdo, int $serverId, float $cpu, float $ram, float $disk): void
    {
        try {
            $st = $pdo->prepare('SELECT is_managed_server, hostname, ip_address, ram_total FROM servers WHERE id = :id LIMIT 1');
            $st->execute([':id' => $serverId]);
            $srv = $st->fetch();
            if (!is_array($srv) || (int)($srv['is_managed_server'] ?? 0) !== 1) {
                return;
            }

            $alertas = [];
            if ($cpu >= 80)  $alertas[] = "CPU: {$cpu}% (limite: 80%)";
            if ($ram >= 75)  $alertas[] = "RAM: {$ram}% (limite: 75%)";
            if ($disk >= 85) $alertas[] = "Disco: {$disk}% (limite: 85%)";

            if (empty($alertas)) {
                return;
            }

            // Rate limit: 1 alerta por servidor a cada 1 hora
            $settingKey = 'alert.managed_server_' . $serverId;
            $ultimo = (string) Settings::obter($settingKey, '');
            if ($ultimo !== '' && (time() - strtotime($ultimo)) < 3600) {
                return;
            }
            Settings::definir($settingKey, date('Y-m-d H:i:s'));

            // Contar VPS e total vendido
            $ms = $pdo->prepare("SELECT COUNT(v.id) AS total_vps, COALESCE(SUM(v.ram),0) AS ram_vendida FROM vps v WHERE v.server_id = :sid AND v.status NOT IN ('removed')");
            $ms->execute([':sid' => $serverId]);
            $mRow = $ms->fetch();
            $totalVps = (int)($mRow['total_vps'] ?? 0);
            $ramVendidaGb = round((int)($mRow['ram_vendida'] ?? 0) / 1024, 1);
            $ramRealGb = round((int)($srv['ram_total'] ?? 0) / 1024, 1);

            $hostname = trim((string)($srv['hostname'] ?? ''));
            $ip = trim((string)($srv['ip_address'] ?? ''));

            $msg = "⚠️ Servidor gerenciado com uso alto\n\n"
                . "Servidor: {$hostname} ({$ip}) — #{$serverId}\n"
                . "VPS alocadas: {$totalVps}\n"
                . "RAM vendida: {$ramVendidaGb} GB / Real: {$ramRealGb} GB\n\n"
                . implode("\n", $alertas)
                . "\n\nConsidere migrar VPS ou adicionar um novo servidor gerenciado.";

            $svc = new \LRV\App\Services\Alertas\NotificacoesService(new \LRV\App\Services\Http\ClienteHttp());
            $svc->alertarAdmin('Servidor Gerenciado — Uso Alto', $msg);

            // Notificação interna
            $agora = date('Y-m-d H:i:s');
            $usuarios = $pdo->query("SELECT id FROM users WHERE status = 'active'")->fetchAll();
            $ins = $pdo->prepare('INSERT INTO notifications (user_id, message, `read`, created_at) VALUES (:u,:m,0,:c)');
            foreach (($usuarios ?: []) as $u) {
                $uid = (int)($u['id'] ?? 0);
                if ($uid > 0) {
                    $ins->execute([':u' => $uid, ':m' => $msg, ':c' => $agora]);
                }
            }
        } catch (\Throwable) {
            // silencioso
        }
    }
}
