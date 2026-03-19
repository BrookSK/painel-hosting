<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Equipe;

use LRV\App\Services\Provisioning\DockerCli;
use LRV\Core\ConfiguracoesSistema;
use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;

final class ServidoresController
{
    public function listar(Requisicao $req): Resposta
    {
        $pdo = BancoDeDados::pdo();

        try {
            try {
                $stmt = $pdo->query('SELECT id, hostname, ip_address, ssh_port, ssh_user, ssh_key_id, terminal_ssh_user, terminal_ssh_key_id, ram_total, ram_used, cpu_total, cpu_used, storage_total, storage_used, status, is_online, last_check_at, last_error FROM servers ORDER BY id DESC');
                $servidores = $stmt->fetchAll();
            } catch (\Throwable $e2) {
                $stmt = $pdo->query('SELECT id, hostname, ip_address, ssh_port, ssh_user, ssh_key_id, ram_total, ram_used, cpu_total, cpu_used, storage_total, storage_used, status, is_online, last_check_at, last_error FROM servers ORDER BY id DESC');
                $servidores = $stmt->fetchAll();
            }
        } catch (\Throwable $e) {
            $stmt = $pdo->query('SELECT id, hostname, ip_address, ssh_port, ram_total, ram_used, cpu_total, cpu_used, storage_total, storage_used, status FROM servers ORDER BY id DESC');
            $servidores = $stmt->fetchAll();
        }

        $html = View::renderizar(__DIR__ . '/../../Views/equipe/servidores-listar.php', [
            'servidores' => is_array($servidores) ? $servidores : [],
        ]);

        return Resposta::html($html);
    }

    public function novo(Requisicao $req): Resposta
    {
        $html = View::renderizar(__DIR__ . '/../../Views/equipe/servidor-editar.php', [
            'erro' => '',
            'servidor' => [
                'id' => null,
                'hostname' => '',
                'ip_address' => '',
                'ssh_port' => 22,
                'ssh_user' => 'root',
                'ssh_key_id' => '',
                'terminal_ssh_user' => 'lrv-terminal',
                'terminal_ssh_key_id' => '',
                'ram_total' => 64 * 1024,
                'cpu_total' => 16,
                'storage_total' => 1000 * 1024,
                'status' => 'active',
            ],
        ]);

        return Resposta::html($html);
    }

    public function editar(Requisicao $req): Resposta
    {
        $id = (int) ($req->query['id'] ?? 0);
        if ($id <= 0) {
            return Resposta::texto('Servidor inválido.', 400);
        }

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT * FROM servers WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $servidor = $stmt->fetch();

        if (!is_array($servidor)) {
            return Resposta::texto('Servidor não encontrado.', 404);
        }

        $html = View::renderizar(__DIR__ . '/../../Views/equipe/servidor-editar.php', [
            'erro' => '',
            'servidor' => $servidor,
        ]);

        return Resposta::html($html);
    }

    public function salvar(Requisicao $req): Resposta
    {
        $id = (int) ($req->post['id'] ?? 0);
        $hostname = trim((string) ($req->post['hostname'] ?? ''));
        $ip = trim((string) ($req->post['ip_address'] ?? ''));
        $sshPort = (int) ($req->post['ssh_port'] ?? 0);
        $sshUser = trim((string) ($req->post['ssh_user'] ?? ''));
        $sshKeyId = trim((string) ($req->post['ssh_key_id'] ?? ''));
        $terminalSshUser = trim((string) ($req->post['terminal_ssh_user'] ?? ''));
        $terminalSshKeyId = trim((string) ($req->post['terminal_ssh_key_id'] ?? ''));
        $ramTotal = (int) ($req->post['ram_total'] ?? 0);
        $cpuTotal = (int) ($req->post['cpu_total'] ?? 0);
        $storageTotal = (int) ($req->post['storage_total'] ?? 0);
        $status = (string) ($req->post['status'] ?? 'active');

        if (!in_array($status, ['active', 'inactive', 'maintenance'], true)) {
            $status = 'active';
        }

        $conexaoValidada = false;

        if ($hostname === '' || $ip === '' || $sshPort <= 0 || $sshPort > 65535 || $ramTotal <= 0 || $cpuTotal <= 0 || $storageTotal <= 0) {
            return $this->renderizarErro($id, $hostname, $ip, $sshPort, $sshUser, $sshKeyId, $terminalSshUser, $terminalSshKeyId, $ramTotal, $cpuTotal, $storageTotal, $status, 'Preencha os campos obrigatórios.');
        }

        if ($status === 'active') {
            if ($sshUser === '' || $sshKeyId === '') {
                return $this->renderizarErro($id, $hostname, $ip, $sshPort, $sshUser, $sshKeyId, $terminalSshUser, $terminalSshKeyId, $ramTotal, $cpuTotal, $storageTotal, $status, 'Informe usuário SSH e identificador da chave para nodes ativos.');
            }

            if (preg_match('/^[a-zA-Z0-9][a-zA-Z0-9_.-]+$/', $sshKeyId) !== 1) {
                return $this->renderizarErro($id, $hostname, $ip, $sshPort, $sshUser, $sshKeyId, $terminalSshUser, $terminalSshKeyId, $ramTotal, $cpuTotal, $storageTotal, $status, 'Identificador da chave inválido.');
            }

            if ($terminalSshKeyId !== '' && preg_match('/^[a-zA-Z0-9][a-zA-Z0-9_.-]+$/', $terminalSshKeyId) !== 1) {
                return $this->renderizarErro($id, $hostname, $ip, $sshPort, $sshUser, $sshKeyId, $terminalSshUser, $terminalSshKeyId, $ramTotal, $cpuTotal, $storageTotal, $status, 'Identificador da chave do terminal seguro inválido.');
            }

            $keyDir = rtrim(ConfiguracoesSistema::sshKeyDir(), "/\\");
            if ($keyDir === '') {
                return $this->renderizarErro($id, $hostname, $ip, $sshPort, $sshUser, $sshKeyId, $terminalSshUser, $terminalSshKeyId, $ramTotal, $cpuTotal, $storageTotal, $status, 'Configure o diretório base das chaves SSH em /equipe/configuracoes.');
            }

            $keyPath = $keyDir . DIRECTORY_SEPARATOR . $sshKeyId;
            if (!is_file($keyPath)) {
                return $this->renderizarErro($id, $hostname, $ip, $sshPort, $sshUser, $sshKeyId, $terminalSshUser, $terminalSshKeyId, $ramTotal, $cpuTotal, $storageTotal, $status, 'Arquivo de chave não encontrado: ' . $sshKeyId);
            }

            try {
                $docker = new DockerCli();
                $docker->definirRemoto($ip, $sshPort, $sshUser, $keyPath);
                $t = $docker->testarConexao();
                if (empty($t['ok'])) {
                    $saida = trim((string) ($t['saida'] ?? ''));
                    $msg = 'Falha ao validar conexão SSH/Docker.';
                    if ($saida !== '') {
                        $msg .= "\n\n" . $saida;
                    }

                    if ($id > 0) {
                        try {
                            $pdo = BancoDeDados::pdo();
                            $stOnline = $pdo->prepare('UPDATE servers SET is_online=0, last_check_at=:c, last_error=:e WHERE id=:id');
                            $stOnline->execute([
                                ':c' => date('Y-m-d H:i:s'),
                                ':e' => (function_exists('mb_substr') ? mb_substr($saida !== '' ? $saida : $msg, 0, 255) : substr($saida !== '' ? $saida : $msg, 0, 255)),
                                ':id' => $id,
                            ]);
                        } catch (\Throwable $e) {
                        }
                    }

                    return $this->renderizarErro($id, $hostname, $ip, $sshPort, $sshUser, $sshKeyId, $terminalSshUser, $terminalSshKeyId, $ramTotal, $cpuTotal, $storageTotal, $status, $msg);
                }

                $conexaoValidada = true;
            } catch (\Throwable $e) {
                if ($id > 0) {
                    try {
                        $pdo = BancoDeDados::pdo();
                        $stOnline = $pdo->prepare('UPDATE servers SET is_online=0, last_check_at=:c, last_error=:e WHERE id=:id');
                        $stOnline->execute([
                            ':c' => date('Y-m-d H:i:s'),
                            ':e' => (function_exists('mb_substr') ? mb_substr($e->getMessage(), 0, 255) : substr($e->getMessage(), 0, 255)),
                            ':id' => $id,
                        ]);
                    } catch (\Throwable $e2) {
                    }
                }
                return $this->renderizarErro($id, $hostname, $ip, $sshPort, $sshUser, $sshKeyId, $terminalSshUser, $terminalSshKeyId, $ramTotal, $cpuTotal, $storageTotal, $status, 'Falha ao validar conexão: ' . $e->getMessage());
            }
        }

        $pdo = BancoDeDados::pdo();

        $savedId = 0;

        try {
            if ($id > 0) {
                try {
                    $stmt = $pdo->prepare('UPDATE servers SET hostname=:h, ip_address=:ip, ssh_port=:sp, ssh_user=:su, ssh_key_id=:sk, terminal_ssh_user=:tsu, terminal_ssh_key_id=:tsk, ram_total=:rt, cpu_total=:ct, storage_total=:st, status=:s WHERE id=:id');
                    $stmt->execute([
                        ':h' => $hostname,
                        ':ip' => $ip,
                        ':sp' => $sshPort,
                        ':su' => $sshUser !== '' ? $sshUser : null,
                        ':sk' => $sshKeyId !== '' ? $sshKeyId : null,
                        ':tsu' => $terminalSshUser !== '' ? $terminalSshUser : null,
                        ':tsk' => $terminalSshKeyId !== '' ? $terminalSshKeyId : null,
                        ':rt' => $ramTotal,
                        ':ct' => $cpuTotal,
                        ':st' => $storageTotal,
                        ':s' => $status,
                        ':id' => $id,
                    ]);
                    $savedId = $id;
                } catch (\Throwable $e) {
                    $stmt = $pdo->prepare('UPDATE servers SET hostname=:h, ip_address=:ip, ssh_port=:sp, ram_total=:rt, cpu_total=:ct, storage_total=:st, status=:s WHERE id=:id');
                    $stmt->execute([
                        ':h' => $hostname,
                        ':ip' => $ip,
                        ':sp' => $sshPort,
                        ':rt' => $ramTotal,
                        ':ct' => $cpuTotal,
                        ':st' => $storageTotal,
                        ':s' => $status,
                        ':id' => $id,
                    ]);
                    $savedId = $id;
                }
            } else {
                try {
                    $stmt = $pdo->prepare('INSERT INTO servers (hostname, ip_address, ssh_port, ssh_user, ssh_key_id, terminal_ssh_user, terminal_ssh_key_id, ram_total, ram_used, cpu_total, cpu_used, storage_total, storage_used, status, created_at) VALUES (:h,:ip,:sp,:su,:sk,:tsu,:tsk,:rt,0,:ct,0,:st,0,:s,:cr)');
                    $stmt->execute([
                        ':h' => $hostname,
                        ':ip' => $ip,
                        ':sp' => $sshPort,
                        ':su' => $sshUser !== '' ? $sshUser : null,
                        ':sk' => $sshKeyId !== '' ? $sshKeyId : null,
                        ':tsu' => $terminalSshUser !== '' ? $terminalSshUser : null,
                        ':tsk' => $terminalSshKeyId !== '' ? $terminalSshKeyId : null,
                        ':rt' => $ramTotal,
                        ':ct' => $cpuTotal,
                        ':st' => $storageTotal,
                        ':s' => $status,
                        ':cr' => date('Y-m-d H:i:s'),
                    ]);
                    $savedId = (int) $pdo->lastInsertId();
                } catch (\Throwable $e) {
                    $stmt = $pdo->prepare('INSERT INTO servers (hostname, ip_address, ssh_port, ram_total, ram_used, cpu_total, cpu_used, storage_total, storage_used, status, created_at) VALUES (:h,:ip,:sp,:rt,0,:ct,0,:st,0,:s,:cr)');
                    $stmt->execute([
                        ':h' => $hostname,
                        ':ip' => $ip,
                        ':sp' => $sshPort,
                        ':rt' => $ramTotal,
                        ':ct' => $cpuTotal,
                        ':st' => $storageTotal,
                        ':s' => $status,
                        ':cr' => date('Y-m-d H:i:s'),
                    ]);
                    $savedId = (int) $pdo->lastInsertId();
                }
            }
        } catch (\Throwable $e) {
            return $this->renderizarErro($id, $hostname, $ip, $sshPort, $sshUser, $sshKeyId, $terminalSshUser, $terminalSshKeyId, $ramTotal, $cpuTotal, $storageTotal, $status, 'Não foi possível salvar o servidor.');
        }

        if ($conexaoValidada && $savedId > 0) {
            try {
                $pdo = BancoDeDados::pdo();
                $stOnline = $pdo->prepare('UPDATE servers SET is_online=1, last_check_at=:c, last_error=NULL WHERE id=:id');
                $stOnline->execute([
                    ':c' => date('Y-m-d H:i:s'),
                    ':id' => $savedId,
                ]);
            } catch (\Throwable $e) {
            }
        }

        return Resposta::redirecionar('/equipe/servidores');
    }

    public function testarConexao(Requisicao $req): Resposta
    {
        $id = (int) ($req->post['id'] ?? 0);
        $hostname = trim((string) ($req->post['hostname'] ?? ''));
        $ip = trim((string) ($req->post['ip_address'] ?? ''));
        $sshPort = (int) ($req->post['ssh_port'] ?? 0);
        $sshUser = trim((string) ($req->post['ssh_user'] ?? ''));
        $sshKeyId = trim((string) ($req->post['ssh_key_id'] ?? ''));
        $terminalSshUser = trim((string) ($req->post['terminal_ssh_user'] ?? ''));
        $terminalSshKeyId = trim((string) ($req->post['terminal_ssh_key_id'] ?? ''));
        $ramTotal = (int) ($req->post['ram_total'] ?? 0);
        $cpuTotal = (int) ($req->post['cpu_total'] ?? 0);
        $storageTotal = (int) ($req->post['storage_total'] ?? 0);
        $status = (string) ($req->post['status'] ?? 'active');

        if (!in_array($status, ['active', 'inactive', 'maintenance'], true)) {
            $status = 'active';
        }

        $servidor = [
            'id' => $id > 0 ? $id : null,
            'hostname' => $hostname,
            'ip_address' => $ip,
            'ssh_port' => $sshPort,
            'ssh_user' => $sshUser,
            'ssh_key_id' => $sshKeyId,
            'terminal_ssh_user' => $terminalSshUser,
            'terminal_ssh_key_id' => $terminalSshKeyId,
            'ram_total' => $ramTotal,
            'cpu_total' => $cpuTotal,
            'storage_total' => $storageTotal,
            'status' => $status,
        ];

        if ($status !== 'active') {
            return $this->renderizarServidor($servidor, 'Node não está ativo.', '', 422);
        }

        if ($hostname === '' || $ip === '' || $sshPort <= 0 || $sshPort > 65535 || $sshUser === '' || $sshKeyId === '') {
            return $this->renderizarServidor($servidor, 'Preencha IP/Hostname, SSH (porta/usuário) e identificador da chave.', '', 422);
        }

        if (preg_match('/^[a-zA-Z0-9][a-zA-Z0-9_.-]+$/', $sshKeyId) !== 1) {
            return $this->renderizarServidor($servidor, 'Identificador da chave inválido.', '', 422);
        }

        $keyDir = rtrim(ConfiguracoesSistema::sshKeyDir(), "/\\");
        if ($keyDir === '') {
            return $this->renderizarServidor($servidor, 'Configure o diretório base das chaves SSH em /equipe/configuracoes.', '', 422);
        }

        $keyPath = $keyDir . DIRECTORY_SEPARATOR . $sshKeyId;
        if (!is_file($keyPath)) {
            return $this->renderizarServidor($servidor, 'Arquivo de chave não encontrado: ' . $sshKeyId, '', 422);
        }

        $docker = new DockerCli();
        $docker->definirRemoto($ip, $sshPort, $sshUser, $keyPath);
        $t = $docker->testarConexao();

        if (empty($t['ok'])) {
            $saida = trim((string) ($t['saida'] ?? ''));
            $msg = 'Falha ao validar conexão SSH/Docker.';
            if ($saida !== '') {
                $msg .= "\n\n" . $saida;
            }

            if ($id > 0) {
                try {
                    $pdo = BancoDeDados::pdo();
                    $stOnline = $pdo->prepare('UPDATE servers SET is_online=0, last_check_at=:c, last_error=:e WHERE id=:id');
                    $stOnline->execute([
                        ':c' => date('Y-m-d H:i:s'),
                        ':e' => (function_exists('mb_substr') ? mb_substr($saida !== '' ? $saida : $msg, 0, 255) : substr($saida !== '' ? $saida : $msg, 0, 255)),
                        ':id' => $id,
                    ]);
                    $servidor['is_online'] = 0;
                    $servidor['last_check_at'] = date('Y-m-d H:i:s');
                    $servidor['last_error'] = $saida !== '' ? $saida : $msg;
                } catch (\Throwable $e) {
                }
            }

            return $this->renderizarServidor($servidor, $msg, '', 422);
        }

        if ($id > 0) {
            try {
                $pdo = BancoDeDados::pdo();
                $stOnline = $pdo->prepare('UPDATE servers SET is_online=1, last_check_at=:c, last_error=NULL WHERE id=:id');
                $stOnline->execute([
                    ':c' => date('Y-m-d H:i:s'),
                    ':id' => $id,
                ]);
                $servidor['is_online'] = 1;
                $servidor['last_check_at'] = date('Y-m-d H:i:s');
                $servidor['last_error'] = null;
            } catch (\Throwable $e) {
            }
        }

        return $this->renderizarServidor($servidor, '', 'Conexão SSH/Docker validada com sucesso.', 200);
    }

    public function terminalSeguro(Requisicao $req): Resposta
    {
        $id = (int) ($req->query['id'] ?? 0);
        if ($id <= 0) {
            return Resposta::texto('Servidor inválido.', 400);
        }

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT * FROM servers WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $servidor = $stmt->fetch();

        if (!is_array($servidor)) {
            return Resposta::texto('Servidor não encontrado.', 404);
        }

        $html = View::renderizar(__DIR__ . '/../../Views/equipe/servidor-terminal-seguro.php', [
            'servidor' => $servidor,
        ]);

        return Resposta::html($html);
    }

    private function renderizarErro(int $id, string $hostname, string $ip, int $sshPort, string $sshUser, string $sshKeyId, string $terminalSshUser, string $terminalSshKeyId, int $ramTotal, int $cpuTotal, int $storageTotal, string $status, string $erro): Resposta
    {
        return $this->renderizarServidor([
            'id' => $id > 0 ? $id : null,
            'hostname' => $hostname,
            'ip_address' => $ip,
            'ssh_port' => $sshPort,
            'ssh_user' => $sshUser,
            'ssh_key_id' => $sshKeyId,
            'terminal_ssh_user' => $terminalSshUser,
            'terminal_ssh_key_id' => $terminalSshKeyId,
            'ram_total' => $ramTotal,
            'cpu_total' => $cpuTotal,
            'storage_total' => $storageTotal,
            'status' => $status,
        ], $erro, '', 422);
    }

    private function renderizarServidor(array $servidor, string $erro, string $mensagemOk, int $statusCode): Resposta
    {
        $html = View::renderizar(__DIR__ . '/../../Views/equipe/servidor-editar.php', [
            'erro' => $erro,
            'mensagem_ok' => $mensagemOk,
            'servidor' => $servidor,
        ]);

        return Resposta::html($html, $statusCode);
    }
}
