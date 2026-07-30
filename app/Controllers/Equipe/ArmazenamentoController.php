<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Equipe;

use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;
use LRV\App\Services\Infra\SshExecutor;
use LRV\App\Services\Infra\SshCrypto;
use LRV\Core\ConfiguracoesSistema;

final class ArmazenamentoController
{
    /**
     * Visão geral do armazenamento por servidor/cliente.
     */
    public function index(Requisicao $req): Resposta
    {
        $pdo = BancoDeDados::pdo();

        // Filtrar por servidor ou cliente
        $filtroServidor = (int)($req->query['servidor'] ?? 0);
        $filtroCliente = (int)($req->query['cliente'] ?? 0);

        // Listar servidores ativos
        $servidores = $pdo->query("SELECT id, hostname, ip_address FROM servers WHERE status = 'active' ORDER BY hostname")->fetchAll() ?: [];

        // Listar clientes com VPS
        $clientes = $pdo->query("SELECT DISTINCT c.id, c.name, c.email FROM clients c JOIN vps v ON v.client_id = c.id WHERE v.status = 'running' ORDER BY c.name")->fetchAll() ?: [];

        // Buscar VPS com filtros
        $sql = "SELECT v.id, v.cpu, v.ram, v.storage, v.client_id, c.name as client_name, c.email as client_email,
                       s.id as server_id, s.hostname, s.ip_address, s.ssh_port, s.ssh_user, s.ssh_password, s.ssh_auth_type, s.ssh_key_id
                FROM vps v
                JOIN clients c ON c.id = v.client_id
                JOIN servers s ON s.id = v.server_id
                WHERE v.status = 'running'";
        $params = [];
        if ($filtroServidor > 0) { $sql .= ' AND v.server_id = :srv'; $params[':srv'] = $filtroServidor; }
        if ($filtroCliente > 0) { $sql .= ' AND v.client_id = :cl'; $params[':cl'] = $filtroCliente; }
        $sql .= ' ORDER BY c.name, v.id';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $vpsList = $stmt->fetchAll() ?: [];

        // Agrupar por cliente
        $porCliente = [];
        foreach ($vpsList as $vps) {
            $cid = (int)$vps['client_id'];
            if (!isset($porCliente[$cid])) {
                $porCliente[$cid] = ['name' => $vps['client_name'], 'email' => $vps['client_email'], 'vps' => []];
            }

            // Buscar apps e deploys desta VPS
            $apps = $pdo->prepare("SELECT id, name, deploy_path, app_type FROM applications WHERE vps_id = :v");
            $apps->execute([':v' => (int)$vps['id']]);
            $deploys = $pdo->prepare("SELECT id, name, deploy_path, app_type FROM git_deployments WHERE vps_id = :v");
            $deploys->execute([':v' => (int)$vps['id']]);
            $dbs = $pdo->prepare("SELECT id, db_name FROM client_databases WHERE vps_id = :v");
            $dbs->execute([':v' => (int)$vps['id']]);

            $porCliente[$cid]['vps'][] = [
                'vps' => $vps,
                'apps' => $apps->fetchAll() ?: [],
                'deploys' => $deploys->fetchAll() ?: [],
                'databases' => $dbs->fetchAll() ?: [],
            ];
        }

        return Resposta::html(View::renderizar(__DIR__ . '/../../Views/equipe/armazenamento.php', [
            'porCliente' => $porCliente,
            'servidores' => $servidores,
            'clientes' => $clientes,
            'filtroServidor' => $filtroServidor,
            'filtroCliente' => $filtroCliente,
        ]));
    }

    /**
     * AJAX: Escanear uso de disco de uma VPS (equipe, sem restrição de client_id).
     */
    public function escanear(Requisicao $req): Resposta
    {
        $vpsId = (int)($req->query['vps_id'] ?? 0);
        if ($vpsId <= 0) return Resposta::json(['ok' => false, 'erro' => 'VPS inválida.'], 422);

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare(
            "SELECT v.id, v.client_id, s.ip_address, s.ssh_port, s.ssh_user, s.ssh_password, s.ssh_auth_type, s.ssh_key_id
             FROM vps v JOIN servers s ON s.id = v.server_id
             WHERE v.id = :v AND v.status = 'running' LIMIT 1"
        );
        $stmt->execute([':v' => $vpsId]);
        $srv = $stmt->fetch();
        if (!is_array($srv)) return Resposta::json(['ok' => false, 'erro' => 'VPS não encontrada.'], 404);

        $clientId = (int)$srv['client_id'];

        // Buscar paths conhecidos
        $paths = [];
        $appsStmt = $pdo->prepare("SELECT id, name, deploy_path FROM applications WHERE vps_id = :v");
        $appsStmt->execute([':v' => $vpsId]);
        foreach ($appsStmt->fetchAll() ?: [] as $a) {
            if (!empty($a['deploy_path'])) $paths[] = ['tipo' => 'app', 'id' => (int)$a['id'], 'nome' => (string)$a['name'], 'path' => (string)$a['deploy_path']];
        }
        $depsStmt = $pdo->prepare("SELECT id, name, deploy_path FROM git_deployments WHERE vps_id = :v");
        $depsStmt->execute([':v' => $vpsId]);
        foreach ($depsStmt->fetchAll() ?: [] as $d) {
            if (!empty($d['deploy_path'])) $paths[] = ['tipo' => 'deploy', 'id' => (int)$d['id'], 'nome' => (string)$d['name'], 'path' => (string)$d['deploy_path']];
        }

        // Montar comando SSH
        $cmd = 'df -BM / 2>/dev/null | tail -1 | awk \'{print $2" "$3" "$4}\';';
        if (!empty($paths)) {
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

        $diskParts = preg_split('/\s+/', trim($lines[0] ?? ''));
        $totalMb = (int)str_replace('M', '', $diskParts[0] ?? '0');
        $usedMb = (int)str_replace('M', '', $diskParts[1] ?? '0');
        $availMb = (int)str_replace('M', '', $diskParts[2] ?? '0');

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
                if (str_contains($line, '|')) continue;
                $items[] = [
                    'tipo' => $paths[$itemIdx]['tipo'],
                    'id' => $paths[$itemIdx]['id'],
                    'nome' => $paths[$itemIdx]['nome'],
                    'path' => $paths[$itemIdx]['path'],
                    'size_mb' => (int)$line,
                ];
                $itemIdx++;
            }
            if (!$inItems && is_numeric($line)) {
                if ($tmpMb === 0) $tmpMb = (int)$line;
                else $logsMb = (int)$line;
            }
        }

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
     * POST: Limpar itens selecionados via SSH (equipe, sem restrição).
     */
    public function limpar(Requisicao $req): Resposta
    {
        $vpsId = (int)($req->post['vps_id'] ?? 0);
        $acao = (string)($req->post['acao'] ?? '');
        $path = trim((string)($req->post['path'] ?? ''));

        if ($vpsId <= 0 || $acao === '') return Resposta::json(['ok' => false, 'erro' => 'Parâmetros inválidos.'], 422);

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare(
            "SELECT s.ip_address, s.ssh_port, s.ssh_user, s.ssh_password, s.ssh_auth_type, s.ssh_key_id
             FROM vps v JOIN servers s ON s.id = v.server_id
             WHERE v.id = :v AND v.status = 'running' LIMIT 1"
        );
        $stmt->execute([':v' => $vpsId]);
        $srv = $stmt->fetch();
        if (!is_array($srv)) return Resposta::json(['ok' => false, 'erro' => 'VPS não encontrada.'], 404);

        $cmd = '';
        switch ($acao) {
            case 'limpar_tmp':
                $cmd = 'find /tmp -type f -mtime +1 -delete 2>/dev/null; du -sm /tmp 2>/dev/null | cut -f1';
                break;
            case 'limpar_logs':
                $cmd = 'find /var/log -name "*.log" -type f -exec truncate -s 0 {} + 2>/dev/null; find /var/log -name "*.gz" -delete 2>/dev/null; du -sm /var/log 2>/dev/null | cut -f1';
                break;
            case 'limpar_path':
                if ($path === '' || $path === '/') return Resposta::json(['ok' => false, 'erro' => 'Caminho inválido.'], 403);
                $cmd = 'rm -rf ' . escapeshellarg($path) . ' && echo deleted';
                break;
            case 'limpar_cache':
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
