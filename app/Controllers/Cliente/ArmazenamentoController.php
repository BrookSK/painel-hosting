<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Cliente;

use LRV\Core\Auth;
use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;
use LRV\Core\Csrf;
use LRV\App\Services\Infra\SshExecutor;
use LRV\App\Services\Infra\SshCrypto;
use LRV\Core\ConfiguracoesSistema;

final class ArmazenamentoController
{
    /**
     * Visão geral do armazenamento de todas as VPS do cliente.
     */
    public function index(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::redirecionar('/cliente/entrar');

        $pdo = BancoDeDados::pdo();

        // Buscar VPS do cliente com dados do servidor
        $vpsStmt = $pdo->prepare(
            "SELECT v.id, v.cpu, v.ram, v.storage,
                    s.ip_address, s.ssh_port, s.ssh_user, s.ssh_password, s.ssh_auth_type, s.ssh_key_id
             FROM vps v
             JOIN servers s ON s.id = v.server_id
             WHERE v.client_id = :c AND v.status = 'running'
             ORDER BY v.id"
        );
        $vpsStmt->execute([':c' => $clienteId]);
        $vpsList = $vpsStmt->fetchAll() ?: [];

        // Para cada VPS, buscar apps, deploys e bancos vinculados
        $vpsData = [];
        foreach ($vpsList as $vps) {
            $vpsId = (int)$vps['id'];

            $apps = $pdo->prepare("SELECT a.id, t.name AS name, a.type AS app_type, a.domain FROM applications a LEFT JOIN app_templates t ON t.id = a.template_id WHERE a.vps_id = :v ORDER BY t.name");
            $apps->execute([':v' => $vpsId]);

            $deploys = $pdo->prepare("SELECT id, name, deploy_path, app_type FROM git_deployments WHERE vps_id = :v AND client_id = :c ORDER BY name");
            $deploys->execute([':v' => $vpsId, ':c' => $clienteId]);

            $dbs = $pdo->prepare("SELECT id, db_name FROM client_databases WHERE vps_id = :v AND client_id = :c ORDER BY db_name");
            $dbs->execute([':v' => $vpsId, ':c' => $clienteId]);

            $vpsData[] = [
                'vps' => $vps,
                'apps' => $apps->fetchAll() ?: [],
                'deploys' => $deploys->fetchAll() ?: [],
                'databases' => $dbs->fetchAll() ?: [],
            ];
        }

        $cStmt = $pdo->prepare('SELECT name, email FROM clients WHERE id = ?');
        $cStmt->execute([$clienteId]);
        $cliente = $cStmt->fetch() ?: [];

        return Resposta::html(View::renderizar(__DIR__ . '/../../Views/cliente/armazenamento.php', [
            'vpsData' => $vpsData,
            'cliente' => $cliente,
        ]));
    }

    /**
     * AJAX: Escanear uso de disco de uma VPS (executa du via SSH).
     */
    public function escanear(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::json(['ok' => false], 401);

        $vpsId = (int)($req->query['vps_id'] ?? 0);
        if ($vpsId <= 0) return Resposta::json(['ok' => false, 'erro' => 'VPS inválida.'], 422);

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare(
            "SELECT v.id, s.ip_address, s.ssh_port, s.ssh_user, s.ssh_password, s.ssh_auth_type, s.ssh_key_id
             FROM vps v JOIN servers s ON s.id = v.server_id
             WHERE v.id = :v AND v.client_id = :c AND v.status = 'running' LIMIT 1"
        );
        $stmt->execute([':v' => $vpsId, ':c' => $clienteId]);
        $srv = $stmt->fetch();
        if (!is_array($srv)) return Resposta::json(['ok' => false, 'erro' => 'VPS não encontrada.'], 404);

        // Buscar paths conhecidos (apenas git deploys — apps rodam em containers)
        $paths = [];
        $depsStmt = $pdo->prepare("SELECT id, name, deploy_path FROM git_deployments WHERE vps_id = :v AND client_id = :c");
        $depsStmt->execute([':v' => $vpsId, ':c' => $clienteId]);
        foreach ($depsStmt->fetchAll() ?: [] as $d) {
            if (!empty($d['deploy_path'])) $paths[] = ['tipo' => 'deploy', 'id' => (int)$d['id'], 'nome' => (string)$d['name'], 'path' => (string)$d['deploy_path']];
        }

        // Montar comando SSH: disco total + uso por path conhecido + /tmp + total usado
        $duPaths = array_map(fn($p) => escapeshellarg($p['path']), $paths);
        $cmd = 'df -BM / 2>/dev/null | tail -1 | awk \'{print $2" "$3" "$4}\';';
        if (!empty($duPaths)) {
            $cmd .= ' echo "---ITEMS---";';
            foreach ($paths as $p) {
                $cmd .= ' echo "' . $p['tipo'] . '|' . $p['id'] . '|"; du -sm ' . escapeshellarg($p['path']) . ' 2>/dev/null | cut -f1;';
            }
        }
        $cmd .= ' echo "---TMP---"; du -sm /tmp 2>/dev/null | cut -f1;';
        $cmd .= ' echo "---LOGS---"; du -sm /var/log 2>/dev/null | cut -f1;';

        try {
            $exec = new SshExecutor();
            $host = (string)$srv['ip_address'];
            $port = (int)$srv['ssh_port'];
            $user = (string)$srv['ssh_user'];
            $authType = (string)($srv['ssh_auth_type'] ?? 'password');

            if ($authType === 'password') {
                $senha = SshCrypto::decifrar((string)($srv['ssh_password'] ?? ''));
                $result = $exec->executarComSenha($host, $port, $user, $senha, $cmd, 60);
            } else {
                $keyPath = ConfiguracoesSistema::sshKeyDir() . DIRECTORY_SEPARATOR . (string)($srv['ssh_key_id'] ?? '');
                $result = $exec->executar($host, $port, $user, $keyPath, $cmd, 60);
            }
        } catch (\Throwable $e) {
            return Resposta::json(['ok' => false, 'erro' => 'Erro SSH: ' . $e->getMessage()]);
        }

        $output = (string)($result['saida'] ?? '');
        $lines = explode("\n", $output);

        // Parse disco total
        $diskLine = trim($lines[0] ?? '');
        $diskParts = preg_split('/\s+/', $diskLine);
        $totalMb = (int)str_replace('M', '', $diskParts[0] ?? '0');
        $usedMb = (int)str_replace('M', '', $diskParts[1] ?? '0');
        $availMb = (int)str_replace('M', '', $diskParts[2] ?? '0');

        // Parse items
        $items = [];
        $inItems = false;
        $itemIdx = 0;
        $tmpMb = 0;
        $logsMb = 0;
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '---ITEMS---') { $inItems = true; continue; }
            if ($line === '---TMP---') { $inItems = false; continue; }
            if ($line === '---LOGS---') { continue; }
            if ($inItems && isset($paths[$itemIdx])) {
                if (str_contains($line, '|')) {
                    // Header line (tipo|id|)
                    continue;
                }
                $sizeMb = (int)$line;
                $items[] = [
                    'tipo' => $paths[$itemIdx]['tipo'],
                    'id' => $paths[$itemIdx]['id'],
                    'nome' => $paths[$itemIdx]['nome'],
                    'path' => $paths[$itemIdx]['path'],
                    'size_mb' => $sizeMb,
                ];
                $itemIdx++;
            }
            if (!$inItems && is_numeric($line)) {
                if ($tmpMb === 0) $tmpMb = (int)$line;
                else $logsMb = (int)$line;
            }
        }

        // Calcular "outros" (espaço usado que não é dos items conhecidos)
        $knownMb = array_sum(array_column($items, 'size_mb')) + $tmpMb + $logsMb;
        $outrosMb = max(0, $usedMb - $knownMb);

        return Resposta::json([
            'ok' => true,
            'disco' => ['total_mb' => $totalMb, 'usado_mb' => $usedMb, 'livre_mb' => $availMb],
            'items' => $items,
            'tmp_mb' => $tmpMb,
            'logs_mb' => $logsMb,
            'outros_mb' => $outrosMb,
        ]);
    }

    /**
     * POST: Limpar itens selecionados via SSH.
     */
    public function limpar(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::json(['ok' => false], 401);

        $vpsId = (int)($req->post['vps_id'] ?? 0);
        $acao = (string)($req->post['acao'] ?? '');
        $path = trim((string)($req->post['path'] ?? ''));

        if ($vpsId <= 0 || $acao === '') return Resposta::json(['ok' => false, 'erro' => 'Parâmetros inválidos.'], 422);

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare(
            "SELECT s.ip_address, s.ssh_port, s.ssh_user, s.ssh_password, s.ssh_auth_type, s.ssh_key_id
             FROM vps v JOIN servers s ON s.id = v.server_id
             WHERE v.id = :v AND v.client_id = :c AND v.status = 'running' LIMIT 1"
        );
        $stmt->execute([':v' => $vpsId, ':c' => $clienteId]);
        $srv = $stmt->fetch();
        if (!is_array($srv)) return Resposta::json(['ok' => false, 'erro' => 'VPS não encontrada.'], 404);

        // Montar comando de limpeza conforme a ação
        $cmd = '';
        switch ($acao) {
            case 'limpar_tmp':
                $cmd = 'find /tmp -type f -mtime +1 -delete 2>/dev/null; du -sm /tmp 2>/dev/null | cut -f1';
                break;
            case 'limpar_logs':
                $cmd = 'find /var/log -name "*.log" -type f -exec truncate -s 0 {} + 2>/dev/null; find /var/log -name "*.gz" -delete 2>/dev/null; du -sm /var/log 2>/dev/null | cut -f1';
                break;
            case 'limpar_path':
                // Validar que o path pertence ao cliente (é um deploy ou app dele)
                if ($path === '' || $path === '/' || !str_starts_with($path, '/var/www/') && !str_starts_with($path, '/vps/')) {
                    return Resposta::json(['ok' => false, 'erro' => 'Caminho inválido ou não permitido.'], 403);
                }
                $pathCheck = $pdo->prepare(
                    "SELECT 1 FROM applications WHERE vps_id = :v AND deploy_path = :p
                     UNION SELECT 1 FROM git_deployments WHERE vps_id = :v2 AND client_id = :c AND deploy_path = :p2 LIMIT 1"
                );
                $pathCheck->execute([':v' => $vpsId, ':p' => $path, ':v2' => $vpsId, ':c' => $clienteId, ':p2' => $path]);
                if (!$pathCheck->fetch()) {
                    return Resposta::json(['ok' => false, 'erro' => 'Este caminho não pertence a nenhuma aplicação ou deploy seu.'], 403);
                }
                $cmd = 'rm -rf ' . escapeshellarg($path) . ' && echo deleted';
                break;
            case 'limpar_cache':
                // Limpar caches comuns (npm, composer, apt, pip)
                $cmd = 'rm -rf /root/.npm/_cacache 2>/dev/null; rm -rf /root/.composer/cache 2>/dev/null; rm -rf /var/cache/apt/archives/*.deb 2>/dev/null; rm -rf /root/.cache/pip 2>/dev/null; echo cache-cleared';
                break;
            default:
                return Resposta::json(['ok' => false, 'erro' => 'Ação desconhecida.'], 422);
        }

        try {
            $exec = new SshExecutor();
            $host = (string)$srv['ip_address'];
            $port = (int)$srv['ssh_port'];
            $user = (string)$srv['ssh_user'];
            $authType = (string)($srv['ssh_auth_type'] ?? 'password');

            if ($authType === 'password') {
                $senha = SshCrypto::decifrar((string)($srv['ssh_password'] ?? ''));
                $result = $exec->executarComSenha($host, $port, $user, $senha, $cmd, 120);
            } else {
                $keyPath = ConfiguracoesSistema::sshKeyDir() . DIRECTORY_SEPARATOR . (string)($srv['ssh_key_id'] ?? '');
                $result = $exec->executar($host, $port, $user, $keyPath, $cmd, 120);
            }
        } catch (\Throwable $e) {
            return Resposta::json(['ok' => false, 'erro' => 'Erro SSH: ' . $e->getMessage()]);
        }

        return Resposta::json(['ok' => true, 'output' => trim((string)($result['saida'] ?? ''))]);
    }
}
