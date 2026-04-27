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
use LRV\Core\Settings;

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

        // Keep encrypted password for reveal toggle, mask display
        foreach ($bancos as &$b) {
            $b['db_password_enc_raw'] = (string)($b['db_password_enc'] ?? '');
            $b['db_password_enc'] = '';
        }
        unset($b);

        $cStmt = $pdo->prepare('SELECT name, email FROM clients WHERE id = ?');
        $cStmt->execute([$clienteId]);
        $cliente = $cStmt->fetch() ?: [];

        // Buscar domínios do cliente para associação
        $dStmt = $pdo->prepare("SELECT subdomain FROM client_subdomains WHERE client_id = :c AND status = 'active' ORDER BY subdomain");
        $dStmt->execute([':c' => $clienteId]);
        $dominiosCliente = $dStmt->fetchAll() ?: [];

        return Resposta::html(View::renderizar(__DIR__ . '/../../Views/cliente/banco-dados-listar.php', [
            'bancos'  => $bancos,
            'cliente' => $cliente,
            'dominiosCliente' => $dominiosCliente,
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
        $customUser = trim((string)($req->post['db_user'] ?? ''));
        $customPass = trim((string)($req->post['db_password'] ?? ''));

        if ($name === '' || $vpsId <= 0) {
            return $this->renderizarErro($clienteId, 'Preencha o nome e selecione a VPS.');
        }

        // Verificar limite de bancos de dados do plano
        [$podeCriar, $atual, $limite] = \LRV\App\Services\Plans\PlanFeatureService::podeCriarBanco($clienteId);
        if (!$podeCriar) {
            return $this->renderizarErro($clienteId, "Limite de bancos de dados atingido ({$atual}/{$limite}). Faça upgrade do seu plano para criar mais.");
        }

        $dbName = 'db_' . $clienteId . '_' . preg_replace('/[^a-z0-9_]/', '_', strtolower($name));
        $dbUser = $customUser !== '' ? preg_replace('/[^a-zA-Z0-9_\-]/', '', $customUser) : 'u_' . $clienteId . '_' . substr(md5($name . time()), 0, 8);
        $dbPass = $customPass !== '' ? $customPass : bin2hex(random_bytes(12));

        $pdo = BancoDeDados::pdo();

        // Buscar dados da VPS + flags do servidor (is_managed_server)
        $vStmt = $pdo->prepare(
            "SELECT v.id, v.server_id, s.ip_address, s.ssh_port, s.ssh_user, s.ssh_password, s.ssh_auth_type, s.ssh_key_id, s.is_managed_server, s.mysql_root_password
             FROM vps v JOIN servers s ON s.id = v.server_id
             WHERE v.id = :v AND v.client_id = :c AND v.status = 'running' LIMIT 1"
        );
        $vStmt->execute([':v' => $vpsId, ':c' => $clienteId]);
        $vps = $vStmt->fetch();
        if (!is_array($vps)) {
            return $this->renderizarErro($clienteId, 'VPS não encontrada ou inativa.');
        }

        // Determinar engine: servidores gerenciados usam MySQL nativo do host
        $isManaged = !empty($vps['is_managed_server']);
        $engine = $isManaged ? 'native' : 'docker';

        if ($engine === 'native') {
            // MySQL nativo: host é localhost, porta 3306, sem container
            $pdo->prepare(
                'INSERT INTO client_databases (client_id, vps_id, name, db_name, db_user, db_password_enc, db_host, db_port, container_id, engine, status, created_at)
                 VALUES (:c,:v,:n,:dn,:du,:dp,:dh,:dport,NULL,:eng,:s,:cr)'
            )->execute([
                ':c' => $clienteId, ':v' => $vpsId, ':n' => $name,
                ':dn' => $dbName, ':du' => $dbUser,
                ':dp' => SshCrypto::cifrar($dbPass),
                ':dh' => 'localhost',
                ':dport' => 3306,
                ':eng' => 'native',
                ':s' => 'creating', ':cr' => date('Y-m-d H:i:s'),
            ]);
            $dbId = (int)$pdo->lastInsertId();

            // Obter senha root do MySQL (cifrada no servidor)
            $mysqlRootEnc = (string)($vps['mysql_root_password'] ?? '');
            $mysqlRootPass = $mysqlRootEnc !== '' ? SshCrypto::decifrar($mysqlRootEnc) : '';

            if ($mysqlRootPass === '') {
                $pdo->prepare('UPDATE client_databases SET status="error" WHERE id=:id')->execute([':id' => $dbId]);
                return $this->renderizarErro($clienteId, 'Senha root do MySQL não configurada neste servidor. Peça ao administrador para configurar em Equipe → Servidores → Editar.');
            }

            // Criar banco e usuário no MySQL nativo do host via SSH
            try {
                $createSql = "CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
                    . " CREATE USER IF NOT EXISTS '{$dbUser}'@'localhost' IDENTIFIED BY " . "'" . addslashes($dbPass) . "';"
                    . " CREATE USER IF NOT EXISTS '{$dbUser}'@'%' IDENTIFIED BY " . "'" . addslashes($dbPass) . "';"
                    . " GRANT ALL PRIVILEGES ON `{$dbName}`.* TO '{$dbUser}'@'localhost';"
                    . " GRANT ALL PRIVILEGES ON `{$dbName}`.* TO '{$dbUser}'@'%';"
                    . " FLUSH PRIVILEGES;";

                $cmd = 'mysql -u root -p' . escapeshellarg($mysqlRootPass) . ' -e ' . escapeshellarg($createSql) . ' 2>&1 && echo lrv-db-ok';

                $result = $this->executarSshServidor($vps, $cmd, 30);
                $output = (string)($result['saida'] ?? '');

                if (str_contains($output, 'lrv-db-ok')) {
                    $pdo->prepare('UPDATE client_databases SET status="active" WHERE id=:id')->execute([':id' => $dbId]);
                } else {
                    $pdo->prepare('UPDATE client_databases SET status="error" WHERE id=:id')->execute([':id' => $dbId]);
                }
            } catch (\Throwable $e) {
                $pdo->prepare('UPDATE client_databases SET status="error" WHERE id=:id')->execute([':id' => $dbId]);
            }
        } else {
            // Docker: container MySQL dedicado (comportamento original)
            $containerName = 'db_client_' . $clienteId . '_' . preg_replace('/[^a-z0-9]/', '', strtolower($name));
            $rede = (string)Settings::obter('infra.docker_rede', 'lrvcloud_network');

            $pdo->prepare(
                'INSERT INTO client_databases (client_id, vps_id, name, db_name, db_user, db_password_enc, db_host, db_port, container_id, engine, status, created_at)
                 VALUES (:c,:v,:n,:dn,:du,:dp,:dh,:dport,:ci,:eng,:s,:cr)'
            )->execute([
                ':c' => $clienteId, ':v' => $vpsId, ':n' => $name,
                ':dn' => $dbName, ':du' => $dbUser,
                ':dp' => SshCrypto::cifrar($dbPass),
                ':dh' => $containerName,
                ':dport' => 3306,
                ':ci' => $containerName,
                ':eng' => 'docker',
                ':s' => 'creating', ':cr' => date('Y-m-d H:i:s'),
            ]);
            $dbId = (int)$pdo->lastInsertId();

            // Encontrar porta livre para expor o MySQL (3307+)
            $mysqlPort = 3307 + ($dbId % 1000);
            $pdo->prepare('UPDATE client_databases SET db_port = :p WHERE id = :id')
                ->execute([':p' => $mysqlPort, ':id' => $dbId]);

            try {
                $exec = new SshExecutor();
                $authType = (string)($vps['ssh_auth_type'] ?? 'password');
                $host = (string)($vps['ip_address'] ?? '');
                $port = (int)($vps['ssh_port'] ?? 22);
                $user = (string)($vps['ssh_user'] ?? 'root');

                $dockerCmd = 'docker ps -a --format "{{.Names}}" | grep -q ' . escapeshellarg($containerName) . ' && echo "already exists"'
                    . ' || docker run -d'
                    . ' --name ' . escapeshellarg($containerName)
                    . ' --network ' . escapeshellarg($rede)
                    . ' --restart unless-stopped'
                    . ' -p 127.0.0.1:' . $mysqlPort . ':3306'
                    . ' -e MYSQL_ROOT_PASSWORD=' . escapeshellarg($dbPass)
                    . ' -e MYSQL_DATABASE=' . escapeshellarg($dbName)
                    . ' -e MYSQL_USER=' . escapeshellarg($dbUser)
                    . ' -e MYSQL_PASSWORD=' . escapeshellarg($dbPass)
                    . ' mysql:8 2>&1 && echo lrv-db-ok';

                if ($authType === 'password') {
                    $senha = SshCrypto::decifrar((string)($vps['ssh_password'] ?? ''));
                    $result = $exec->executarComSenha($host, $port, $user, $senha, $dockerCmd, 60);
                } else {
                    $keyPath = \LRV\Core\ConfiguracoesSistema::sshKeyDir() . DIRECTORY_SEPARATOR . (string)($vps['ssh_key_id'] ?? '');
                    $result = $exec->executar($host, $port, $user, $keyPath, $dockerCmd, 60);
                }

                $pdo->prepare('UPDATE client_databases SET status="active" WHERE id=:id')->execute([':id' => $dbId]);
            } catch (\Throwable $e) {
                $pdo->prepare('UPDATE client_databases SET status="error" WHERE id=:id')->execute([':id' => $dbId]);
            }
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
        $engine = (string)($banco['engine'] ?? 'docker');

        // Escape SQL for shell
        $sqlEscaped = str_replace("'", "'\\''", $sql);

        if ($engine === 'native') {
            // MySQL nativo: executar direto no host
            $cmd = "mysql -u " . escapeshellarg($dbUser) . " -p" . escapeshellarg($dbPass) . " " . escapeshellarg($dbName) . " -e '" . $sqlEscaped . "' 2>&1";
        } else {
            // Docker: executar dentro do container (mesmo comando, roda no host que tem acesso)
            $cmd = "mysql -u " . escapeshellarg($dbUser) . " -p" . escapeshellarg($dbPass) . " " . escapeshellarg($dbName) . " -e '" . $sqlEscaped . "' 2>&1";
        }

        try {
            $result = $this->executarSshServidor($banco, $cmd, 60);
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

        // Buscar dados do banco para dropar no servidor
        $stmt = $pdo->prepare(
            'SELECT cd.db_name, cd.db_user, cd.container_id, cd.engine, cd.vps_id,
                    s.ip_address, s.ssh_port, s.ssh_user, s.ssh_password, s.ssh_auth_type, s.ssh_key_id, s.mysql_root_password
             FROM client_databases cd
             JOIN vps v ON v.id = cd.vps_id
             JOIN servers s ON s.id = v.server_id
             WHERE cd.id = :id AND cd.client_id = :c LIMIT 1'
        );
        $stmt->execute([':id' => $id, ':c' => $clienteId]);
        $db = $stmt->fetch();

        if (is_array($db)) {
            $dbName = (string)($db['db_name'] ?? '');
            $dbUser = (string)($db['db_user'] ?? '');
            $engine = (string)($db['engine'] ?? 'docker');

            if ($dbName !== '') {
                try {
                    if ($engine === 'native') {
                        // MySQL nativo: dropar banco e usuário com senha root
                        $mysqlRootEnc = (string)($db['mysql_root_password'] ?? '');
                        $mysqlRootPass = $mysqlRootEnc !== '' ? SshCrypto::decifrar($mysqlRootEnc) : '';
                        $rootAuth = $mysqlRootPass !== '' ? '-u root -p' . escapeshellarg($mysqlRootPass) : '-u root';

                        $dropCmd = 'mysql ' . $rootAuth . ' -e '
                            . escapeshellarg("DROP DATABASE IF EXISTS `{$dbName}`; DROP USER IF EXISTS '{$dbUser}'@'localhost'; DROP USER IF EXISTS '{$dbUser}'@'%'; FLUSH PRIVILEGES;")
                            . ' 2>&1';
                        $this->executarSshServidor($db, $dropCmd, 15);
                    } else {
                        // Docker: parar e remover container + dropar no MySQL do container
                        $containerId = (string)($db['container_id'] ?? '');
                        if ($containerId !== '') {
                            $dropCmd = 'docker stop ' . escapeshellarg($containerId) . ' 2>/dev/null;'
                                . ' docker rm ' . escapeshellarg($containerId) . ' 2>/dev/null;'
                                . ' echo lrv-drop-ok';
                            $this->executarSshServidor($db, $dropCmd, 15);
                        } else {
                            // Fallback: dropar no MySQL do host
                            $dropCmd = 'mysql -u root -e '
                                . escapeshellarg("DROP DATABASE IF EXISTS `{$dbName}`; DROP USER IF EXISTS '{$dbUser}'@'%'; FLUSH PRIVILEGES;")
                                . ' 2>&1';
                            $this->executarSshServidor($db, $dropCmd, 15);
                        }
                    }
                } catch (\Throwable) {
                    // Continuar mesmo se falhar — pelo menos remove o registro
                }
            }
        }

        $pdo->prepare('DELETE FROM client_databases WHERE id = :id AND client_id = :c')->execute([':id' => $id, ':c' => $clienteId]);

        return Resposta::redirecionar('/cliente/banco-dados');
    }

    /**
     * AJAX: salvar nota/associação de um banco.
     */
    public function nota(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::json(['ok' => false], 401);

        $id = (int)($req->post['id'] ?? 0);
        $notes = trim((string)($req->post['notes'] ?? ''));
        $pdo = BancoDeDados::pdo();
        $pdo->prepare('UPDATE client_databases SET notes = :n WHERE id = :id AND client_id = :c')
            ->execute([':n' => $notes !== '' ? $notes : null, ':id' => $id, ':c' => $clienteId]);
        return Resposta::json(['ok' => true]);
    }

    /**
     * AJAX: retorna a senha decifrada de um banco.
     */
    public function senha(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::json(['ok' => false], 401);

        $id = (int)($req->query['id'] ?? 0);
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT db_password_enc FROM client_databases WHERE id = :id AND client_id = :c LIMIT 1');
        $stmt->execute([':id' => $id, ':c' => $clienteId]);
        $row = $stmt->fetch();
        if (!is_array($row)) return Resposta::json(['ok' => false, 'erro' => 'Não encontrado.'], 404);

        $senha = SshCrypto::decifrar((string)($row['db_password_enc'] ?? ''));
        return Resposta::json(['ok' => true, 'senha' => $senha]);
    }

    /**
     * Redireciona para phpMyAdmin com auto-login via URL params.
     */
    public function phpmyadmin(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::redirecionar('/cliente/entrar');

        $id = (int)($req->query['id'] ?? 0);
        $pdo = BancoDeDados::pdo();
        // Buscar dados do banco + URL do phpMyAdmin do servidor
        $stmt = $pdo->prepare(
            'SELECT cd.db_host, cd.db_port, cd.db_user, cd.db_name, s.phpmyadmin_url
             FROM client_databases cd
             JOIN vps v ON v.id = cd.vps_id
             JOIN servers s ON s.id = v.server_id
             WHERE cd.id = :id AND cd.client_id = :c LIMIT 1'
        );
        $stmt->execute([':id' => $id, ':c' => $clienteId]);
        $row = $stmt->fetch();
        if (!is_array($row)) return Resposta::texto('Banco não encontrado.', 404);

        // Tentar URL do servidor, fallback para setting global
        $pmaUrl = trim((string)($row['phpmyadmin_url'] ?? ''));
        if ($pmaUrl === '') {
            $pmaUrl = (string)Settings::obter('infra.phpmyadmin_url', '');
        }
        if ($pmaUrl === '') {
            return Resposta::texto('phpMyAdmin não instalado neste servidor. Execute o passo "Instalar phpMyAdmin" na inicialização do servidor.', 500);
        }

        $url = rtrim($pmaUrl, '/') . '/';

        return Resposta::redirecionar($url);
    }

    /**
     * AJAX GET: lê configurações PHP atuais do phpMyAdmin no servidor.
     */
    public function lerConfigPhpmyadmin(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::json(['ok' => false], 401);

        $vpsId = (int)($req->query['vps_id'] ?? 0);
        if ($vpsId <= 0) return Resposta::json(['ok' => false, 'erro' => 'VPS não informada.']);

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare(
            "SELECT s.ip_address, s.ssh_port, s.ssh_user, s.ssh_password, s.ssh_auth_type, s.ssh_key_id
             FROM vps v JOIN servers s ON s.id = v.server_id
             WHERE v.id = :v AND v.client_id = :c AND v.status = 'running' LIMIT 1"
        );
        $stmt->execute([':v' => $vpsId, ':c' => $clienteId]);
        $srv = $stmt->fetch();
        if (!is_array($srv)) return Resposta::json(['ok' => false, 'erro' => 'VPS não encontrada.']);

        // Ler valores atuais do PHP do phpMyAdmin (Docker ou direto)
        $cmd = 'PMA_CONTAINER=$(docker ps --format "{{.Names}}" 2>/dev/null | grep -i phpmyadmin | head -1)'
            . ' && if [ -n "$PMA_CONTAINER" ]; then'
            . '   docker exec $PMA_CONTAINER php -r "echo json_encode(['
            . '     \"upload_max_filesize\"=>ini_get(\"upload_max_filesize\"),'
            . '     \"post_max_size\"=>ini_get(\"post_max_size\"),'
            . '     \"max_execution_time\"=>ini_get(\"max_execution_time\"),'
            . '     \"max_input_time\"=>ini_get(\"max_input_time\"),'
            . '     \"memory_limit\"=>ini_get(\"memory_limit\"),'
            . '     \"mode\"=>\"docker\"'
            . '   ]);" 2>/dev/null;'
            . ' else'
            . '   PMA_PHP=$(find /etc/phpmyadmin /usr/share/phpmyadmin /var/www/phpmyadmin -name "*.php" -maxdepth 1 2>/dev/null | head -1)'
            . '   && if [ -n "$PMA_PHP" ]; then'
            . '     php -r "echo json_encode(['
            . '       \"upload_max_filesize\"=>ini_get(\"upload_max_filesize\"),'
            . '       \"post_max_size\"=>ini_get(\"post_max_size\"),'
            . '       \"max_execution_time\"=>ini_get(\"max_execution_time\"),'
            . '       \"max_input_time\"=>ini_get(\"max_input_time\"),'
            . '       \"memory_limit\"=>ini_get(\"memory_limit\"),'
            . '       \"mode\"=>\"native\"'
            . '     ]);" 2>/dev/null;'
            . '   else echo "NOT_FOUND"; fi;'
            . ' fi';

        try {
            $result = $this->executarSshServidor($srv, $cmd, 15);
            $output = trim((string)($result['saida'] ?? ''));
            // Limpar warnings SSH
            $lines = explode("\n", $output);
            $jsonLine = '';
            foreach ($lines as $l) {
                $l = trim($l);
                if (str_starts_with($l, '{')) { $jsonLine = $l; break; }
            }
            if ($jsonLine !== '') {
                $data = json_decode($jsonLine, true);
                if (is_array($data)) {
                    return Resposta::json(['ok' => true, 'config' => $data]);
                }
            }
            return Resposta::json(['ok' => true, 'config' => null]);
        } catch (\Throwable $e) {
            return Resposta::json(['ok' => false, 'erro' => $e->getMessage()]);
        }
    }

    /**
     * AJAX POST: aplica configurações PHP no phpMyAdmin via SSH.
     * Suporta Docker e instalação nativa.
     */
    public function configPhpmyadmin(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::json(['ok' => false], 401);

        $vpsId = (int)($req->post['vps_id'] ?? 0);
        $uploadMax = $this->sanitizarTamanho((string)($req->post['upload_max_filesize'] ?? '256M'));
        $postMax = $this->sanitizarTamanho((string)($req->post['post_max_size'] ?? '256M'));
        $maxExec = max(0, min(7200, (int)($req->post['max_execution_time'] ?? 1800)));
        $maxInput = max(0, min(7200, (int)($req->post['max_input_time'] ?? 1800)));
        $memoryLimit = $this->sanitizarTamanho((string)($req->post['memory_limit'] ?? '512M'));

        if ($vpsId <= 0) return Resposta::json(['ok' => false, 'erro' => 'Selecione uma VPS.']);

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare(
            "SELECT s.ip_address, s.ssh_port, s.ssh_user, s.ssh_password, s.ssh_auth_type, s.ssh_key_id
             FROM vps v JOIN servers s ON s.id = v.server_id
             WHERE v.id = :v AND v.client_id = :c AND v.status = 'running' LIMIT 1"
        );
        $stmt->execute([':v' => $vpsId, ':c' => $clienteId]);
        $srv = $stmt->fetch();
        if (!is_array($srv)) return Resposta::json(['ok' => false, 'erro' => 'VPS não encontrada.']);

        // Gerar conteúdo do php.ini customizado
        $iniContent = "upload_max_filesize = {$uploadMax}\n"
            . "post_max_size = {$postMax}\n"
            . "max_execution_time = {$maxExec}\n"
            . "max_input_time = {$maxInput}\n"
            . "memory_limit = {$memoryLimit}\n";

        $iniB64 = base64_encode($iniContent);

        // Tentar Docker primeiro, fallback para instalação nativa
        $cmd = 'PMA_CONTAINER=$(docker ps --format "{{.Names}}" 2>/dev/null | grep -i phpmyadmin | head -1)'
            . ' && if [ -n "$PMA_CONTAINER" ]; then'
            // Docker: escrever ini + restart container
            . '   echo ' . escapeshellarg($iniB64) . ' | base64 -d | docker exec -i $PMA_CONTAINER tee /usr/local/etc/php/conf.d/99-custom-limits.ini > /dev/null'
            . '   && docker restart $PMA_CONTAINER 2>&1'
            . '   && echo lrv-pma-ok;'
            . ' else'
            // Nativo: detectar versão PHP e escrever ini + restart php-fpm/apache
            . '   PHP_VER=$(php -r "echo PHP_MAJOR_VERSION.\".\".PHP_MINOR_VERSION;" 2>/dev/null || echo "8.3")'
            . '   && echo ' . escapeshellarg($iniB64) . ' | base64 -d > /etc/php/$PHP_VER/fpm/conf.d/99-phpmyadmin-limits.ini 2>/dev/null'
            . '   && echo ' . escapeshellarg($iniB64) . ' | base64 -d > /etc/php/$PHP_VER/apache2/conf.d/99-phpmyadmin-limits.ini 2>/dev/null; true'
            . '   && (systemctl reload php$PHP_VER-fpm 2>/dev/null || true)'
            . '   && (systemctl reload apache2 2>/dev/null || systemctl reload nginx 2>/dev/null || true)'
            . '   && echo lrv-pma-ok;'
            . ' fi';

        try {
            $result = $this->executarSshServidor($srv, $cmd, 30);
            $output = (string)($result['saida'] ?? '');

            if (!str_contains($output, 'lrv-pma-ok')) {
                return Resposta::json(['ok' => false, 'erro' => 'Falha ao aplicar configurações. Verifique se o phpMyAdmin está instalado. Saída: ' . substr($output, 0, 300)]);
            }

            return Resposta::json(['ok' => true]);
        } catch (\Throwable $e) {
            return Resposta::json(['ok' => false, 'erro' => $e->getMessage()]);
        }
    }

    /**
     * Executa comando SSH no servidor de uma VPS.
     */
    private function executarSshServidor(array $srv, string $cmd, int $timeout = 30): array
    {
        $exec = new SshExecutor();
        $host = (string)($srv['ip_address'] ?? '');
        $port = (int)($srv['ssh_port'] ?? 22);
        $user = (string)($srv['ssh_user'] ?? 'root');
        $authType = (string)($srv['ssh_auth_type'] ?? 'password');

        if ($authType === 'password') {
            $senha = SshCrypto::decifrar((string)($srv['ssh_password'] ?? ''));
            return $exec->executarComSenha($host, $port, $user, $senha, $cmd, $timeout);
        }
        $keyPath = \LRV\Core\ConfiguracoesSistema::sshKeyDir() . DIRECTORY_SEPARATOR . (string)($srv['ssh_key_id'] ?? '');
        return $exec->executar($host, $port, $user, $keyPath, $cmd, $timeout);
    }

    /**
     * Sanitiza valor de tamanho PHP (ex: 256M, 1G, 512M).
     */
    private function sanitizarTamanho(string $val): string
    {
        $val = strtoupper(trim($val));
        if (preg_match('/^\d+[MG]$/', $val)) return $val;
        return '256M';
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
