<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Cliente;

use LRV\Core\Auth;
use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;
use LRV\App\Services\Infra\SshCrypto;
use LRV\App\Services\Infra\SshExecutor;

final class BancoDadosController
{
    public function listar(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::redirecionar('/cliente/entrar');

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT * FROM client_databases WHERE client_id = :c ORDER BY id DESC');
        $stmt->execute([':c' => $clienteId]);
        $bancos = $stmt->fetchAll() ?: [];

        // Mask passwords
        foreach ($bancos as &$b) { $b['db_password_enc'] = ''; }
        unset($b);

        $cStmt = $pdo->prepare('SELECT name, email FROM clients WHERE id = ?');
        $cStmt->execute([$clienteId]);
        $cliente = $cStmt->fetch() ?: [];

        return Resposta::html(View::renderizar(__DIR__ . '/../../Views/cliente/banco-dados-listar.php', [
            'bancos'  => $bancos,
            'cliente' => $cliente,
        ]));
    }

    public function criar(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::redirecionar('/cliente/entrar');

        $pdo = BancoDeDados::pdo();
        $vpsStmt = $pdo->prepare("SELECT id, cpu, ram FROM vps WHERE client_id = :c AND status = 'running' ORDER BY id");
        $vpsStmt->execute([':c' => $clienteId]);
        $vpsList = $vpsStmt->fetchAll() ?: [];

        $cStmt = $pdo->prepare('SELECT name, email FROM clients WHERE id = ?');
        $cStmt->execute([$clienteId]);
        $cliente = $cStmt->fetch() ?: [];

        return Resposta::html(View::renderizar(__DIR__ . '/../../Views/cliente/banco-dados-form.php', [
            'vpsList' => $vpsList,
            'cliente' => $cliente,
            'erro'    => '',
        ]));
    }

    public function salvar(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::redirecionar('/cliente/entrar');

        $vpsId = (int)($req->post['vps_id'] ?? 0);
        $name = trim((string)($req->post['name'] ?? ''));

        if ($name === '' || $vpsId <= 0) {
            return $this->renderizarErro($clienteId, 'Preencha o nome e selecione a VPS.');
        }

        // Sanitize db name
        $dbName = 'db_' . $clienteId . '_' . preg_replace('/[^a-z0-9_]/', '_', strtolower($name));
        $dbUser = 'u_' . $clienteId . '_' . substr(md5($name . time()), 0, 8);
        $dbPass = bin2hex(random_bytes(12));

        $pdo = BancoDeDados::pdo();

        // Validate VPS ownership
        $vStmt = $pdo->prepare("SELECT v.id, s.ip_address, s.ssh_port, s.ssh_user, s.ssh_password, s.ssh_auth_type, s.ssh_key_id FROM vps v JOIN servers s ON s.id = v.server_id WHERE v.id = :v AND v.client_id = :c AND v.status = 'running' LIMIT 1");
        $vStmt->execute([':v' => $vpsId, ':c' => $clienteId]);
        $vps = $vStmt->fetch();
        if (!is_array($vps)) {
            return $this->renderizarErro($clienteId, 'VPS não encontrada ou inativa.');
        }

        // Insert pending record
        $pdo->prepare('INSERT INTO client_databases (client_id, vps_id, name, db_name, db_user, db_password_enc, db_host, db_port, status, created_at) VALUES (:c,:v,:n,:dn,:du,:dp,:dh,:dport,:s,:cr)')
            ->execute([
                ':c' => $clienteId, ':v' => $vpsId, ':n' => $name,
                ':dn' => $dbName, ':du' => $dbUser,
                ':dp' => SshCrypto::cifrar($dbPass),
                ':dh' => (string)($vps['ip_address'] ?? '127.0.0.1'),
                ':dport' => 3306,
                ':s' => 'creating', ':cr' => date('Y-m-d H:i:s'),
            ]);
        $dbId = (int)$pdo->lastInsertId();

        // Create MySQL database via SSH
        try {
            $exec = new SshExecutor();
            $authType = (string)($vps['ssh_auth_type'] ?? 'password');
            $host = (string)($vps['ip_address'] ?? '');
            $port = (int)($vps['ssh_port'] ?? 22);
            $user = (string)($vps['ssh_user'] ?? 'root');

            $mysqlCmd = 'docker run --rm mysql:8 mysql -h 127.0.0.1 -u root -p"$MYSQL_ROOT_PASSWORD" -e '
                . escapeshellarg("CREATE DATABASE IF NOT EXISTS `{$dbName}`; CREATE USER IF NOT EXISTS '{$dbUser}'@'%' IDENTIFIED BY '{$dbPass}'; GRANT ALL PRIVILEGES ON `{$dbName}`.* TO '{$dbUser}'@'%'; FLUSH PRIVILEGES;")
                . ' 2>&1 || mysql -u root -e '
                . escapeshellarg("CREATE DATABASE IF NOT EXISTS `{$dbName}`; CREATE USER IF NOT EXISTS '{$dbUser}'@'%' IDENTIFIED BY '{$dbPass}'; GRANT ALL PRIVILEGES ON `{$dbName}`.* TO '{$dbUser}'@'%'; FLUSH PRIVILEGES;")
                . ' 2>&1';

            if ($authType === 'password') {
                $senha = SshCrypto::decifrar((string)($vps['ssh_password'] ?? ''));
                $result = $exec->executarComSenha($host, $port, $user, $senha, $mysqlCmd, 30);
            } else {
                $keyPath = \LRV\Core\ConfiguracoesSistema::sshKeyDir() . DIRECTORY_SEPARATOR . (string)($vps['ssh_key_id'] ?? '');
                $result = $exec->executar($mysqlCmd, $host, $port, $user, $keyPath, 30);
            }

            $pdo->prepare('UPDATE client_databases SET status="active" WHERE id=:id')->execute([':id' => $dbId]);
        } catch (\Throwable $e) {
            $pdo->prepare('UPDATE client_databases SET status="error" WHERE id=:id')->execute([':id' => $dbId]);
        }

        return Resposta::redirecionar('/cliente/banco-dados');
    }

    public function ver(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::redirecionar('/cliente/entrar');

        $id = (int)($req->query['id'] ?? 0);
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT * FROM client_databases WHERE id = :id AND client_id = :c LIMIT 1');
        $stmt->execute([':id' => $id, ':c' => $clienteId]);
        $banco = $stmt->fetch();
        if (!is_array($banco)) return Resposta::texto('Não encontrado.', 404);

        // Decrypt password for display
        $banco['db_password_plain'] = SshCrypto::decifrar((string)$banco['db_password_enc']);
        $banco['db_password_enc'] = '';

        $cStmt = $pdo->prepare('SELECT name, email FROM clients WHERE id = ?');
        $cStmt->execute([$clienteId]);
        $cliente = $cStmt->fetch() ?: [];

        return Resposta::html(View::renderizar(__DIR__ . '/../../Views/cliente/banco-dados-ver.php', [
            'banco'   => $banco,
            'cliente' => $cliente,
        ]));
    }

    public function executarSql(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::json(['ok' => false], 401);

        $id = (int)($req->post['id'] ?? 0);
        $sql = trim((string)($req->post['sql'] ?? ''));

        if ($sql === '') return Resposta::json(['ok' => false, 'erro' => 'SQL vazio.']);

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare(
            'SELECT d.*, s.ip_address, s.ssh_port, s.ssh_user, s.ssh_password, s.ssh_auth_type, s.ssh_key_id
             FROM client_databases d
             JOIN vps v ON v.id = d.vps_id
             JOIN servers s ON s.id = v.server_id
             WHERE d.id = :id AND d.client_id = :c AND d.status = "active" LIMIT 1'
        );
        $stmt->execute([':id' => $id, ':c' => $clienteId]);
        $banco = $stmt->fetch();
        if (!is_array($banco)) return Resposta::json(['ok' => false, 'erro' => 'Banco não encontrado.'], 404);

        $dbName = (string)$banco['db_name'];
        $dbUser = (string)$banco['db_user'];
        $dbPass = SshCrypto::decifrar((string)$banco['db_password_enc']);

        // Escape SQL for shell
        $sqlEscaped = str_replace("'", "'\\''", $sql);
        $cmd = "mysql -u " . escapeshellarg($dbUser) . " -p" . escapeshellarg($dbPass) . " " . escapeshellarg($dbName) . " -e '" . $sqlEscaped . "' 2>&1";

        try {
            $exec = new SshExecutor();
            $authType = (string)($banco['ssh_auth_type'] ?? 'password');
            $host = (string)($banco['ip_address'] ?? '');
            $port = (int)($banco['ssh_port'] ?? 22);
            $user = (string)($banco['ssh_user'] ?? 'root');

            if ($authType === 'password') {
                $senha = SshCrypto::decifrar((string)($banco['ssh_password'] ?? ''));
                $result = $exec->executarComSenha($host, $port, $user, $senha, $cmd, 60);
            } else {
                $keyPath = \LRV\Core\ConfiguracoesSistema::sshKeyDir() . DIRECTORY_SEPARATOR . (string)($banco['ssh_key_id'] ?? '');
                $result = $exec->executar($cmd, $host, $port, $user, $keyPath, 60);
            }

            return Resposta::json(['ok' => true, 'output' => (string)($result['saida'] ?? '')]);
        } catch (\Throwable $e) {
            return Resposta::json(['ok' => false, 'erro' => $e->getMessage()]);
        }
    }

    public function excluir(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::redirecionar('/cliente/entrar');

        $id = (int)($req->post['id'] ?? 0);
        $pdo = BancoDeDados::pdo();
        $pdo->prepare('DELETE FROM client_databases WHERE id = :id AND client_id = :c')->execute([':id' => $id, ':c' => $clienteId]);

        return Resposta::redirecionar('/cliente/banco-dados');
    }

    private function renderizarErro(int $clienteId, string $erro): Resposta
    {
        $pdo = BancoDeDados::pdo();
        $vpsStmt = $pdo->prepare("SELECT id, cpu, ram FROM vps WHERE client_id = :c AND status = 'running' ORDER BY id");
        $vpsStmt->execute([':c' => $clienteId]);
        $cStmt = $pdo->prepare('SELECT name, email FROM clients WHERE id = ?');
        $cStmt->execute([$clienteId]);
        return Resposta::html(View::renderizar(__DIR__ . '/../../Views/cliente/banco-dados-form.php', [
            'vpsList' => $vpsStmt->fetchAll() ?: [],
            'cliente' => $cStmt->fetch() ?: [],
            'erro'    => $erro,
        ]), 422);
    }
}
