<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Equipe;

use LRV\App\Services\Audit\AuditLogService;
use LRV\App\Services\Infra\SshCrypto;
use LRV\App\Services\Infra\SshExecutor;
use LRV\App\Services\Provisioning\DockerCli;
use LRV\App\Services\Provisioning\ServerSetupService;
use LRV\Core\BancoDeDados;
use LRV\Core\ConfiguracoesSistema;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;

final class ServidoresController
{
    // -------------------------------------------------------------------------
    // Listagem
    // -------------------------------------------------------------------------

    public function listar(Requisicao $req): Resposta
    {
        $pdo = BancoDeDados::pdo();
        try {
            $stmt = $pdo->query('SELECT id, hostname, ip_address, ssh_port, ssh_user, ssh_auth_type,
                terminal_ssh_user, ram_total, ram_used, cpu_total, cpu_used,
                storage_total, storage_used, status, setup_status, is_online, last_check_at, last_error, role
                FROM servers ORDER BY id DESC');
            $servidores = $stmt->fetchAll();
        } catch (\Throwable) {
            $servidores = [];
        }

        return Resposta::html(View::renderizar(__DIR__ . '/../../Views/equipe/servidores-listar.php', [
            'servidores' => is_array($servidores) ? $servidores : [],
        ]));
    }

    // -------------------------------------------------------------------------
    // Novo / Editar
    // -------------------------------------------------------------------------

    public function novo(Requisicao $req): Resposta
    {
        return Resposta::html(View::renderizar(__DIR__ . '/../../Views/equipe/servidor-editar.php', [
            'erro'        => '',
            'mensagem_ok' => '',
            'servidor'    => [
                'id'                  => null,
                'hostname'            => '',
                'ip_address'          => '',
                'ssh_port'            => 22,
                'ssh_user'            => 'root',
                'ssh_auth_type'       => 'password',
                'ssh_key_id'          => '',
                'terminal_ssh_user'   => 'lrv-terminal',
                'terminal_ssh_key_id' => '',
                'ram_total'           => 64 * 1024,
                'cpu_total'           => 16,
                'storage_total'       => 1000 * 1024,
                'status'              => 'active',
                'setup_status'        => 'pending',
            ],
        ]));
    }

    public function editar(Requisicao $req): Resposta
    {
        $id = (int)($req->query['id'] ?? 0);
        if ($id <= 0) return Resposta::texto('Servidor inválido.', 400);

        $pdo  = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT * FROM servers WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $servidor = $stmt->fetch();

        if (!is_array($servidor)) return Resposta::texto('Servidor não encontrado.', 404);

        $servidor['ssh_password'] = '';

        // Carregar passos detalhados para inicialização parcial
        $passos = [];
        try {
            $svc = new \LRV\App\Services\Provisioning\ServerSetupService();
            $res = $svc->listarPassos($id);
            if (!empty($res['ok'])) {
                $passos = $res['steps'] ?? [];
            }
        } catch (\Throwable) {}

        return Resposta::html(View::renderizar(__DIR__ . '/../../Views/equipe/servidor-editar.php', [
            'erro'              => '',
            'mensagem_ok'       => '',
            'servidor'          => $servidor,
            'passos_detalhados' => $passos,
        ]));
    }

    // -------------------------------------------------------------------------
    // Salvar
    // -------------------------------------------------------------------------

    public function salvar(Requisicao $req): Resposta
    {
        $id           = (int)($req->post['id'] ?? 0);
        $hostname     = trim((string)($req->post['hostname'] ?? ''));
        $ip           = trim((string)($req->post['ip_address'] ?? ''));
        $sshPort      = (int)($req->post['ssh_port'] ?? 22);
        $sshUser      = trim((string)($req->post['ssh_user'] ?? ''));
        $authType     = (string)($req->post['ssh_auth_type'] ?? 'password');
        $sshKeyId     = '';
        $sshPassword  = (string)($req->post['ssh_password'] ?? '');
        $useSudo      = !empty($req->post['use_sudo']);
        $sudoPassword = (string)($req->post['sudo_password'] ?? '');
        $termUser     = trim((string)($req->post['terminal_ssh_user'] ?? 'lrv-terminal'));
        $termKeyId    = '';
        $ramTotal     = (int)($req->post['ram_total'] ?? 0);
        $cpuTotal     = (int)($req->post['cpu_total'] ?? 0);
        $storageTotal = (int)($req->post['storage_total'] ?? 0);
        $status       = (string)($req->post['status'] ?? 'active');

        if (!in_array($authType, ['key', 'password'], true)) $authType = 'password';
        if (!in_array($status, ['active', 'inactive', 'maintenance'], true)) $status = 'active';

        // Processar upload de chave SSH principal
        $keyDir = rtrim(ConfiguracoesSistema::sshKeyDir(), "/\\");
        if (!is_dir($keyDir)) @mkdir($keyDir, 0700, true);

        if ($authType === 'key') {
            $uploadResult = $this->processarUploadChave('ssh_key_file', $hostname ?: 'server', $keyDir, $id, 'ssh_key_id');
            if ($uploadResult['erro'] !== '') {
                $dados = compact('id','hostname','ip','sshPort','sshUser','authType','sshKeyId','sshPassword','useSudo','sudoPassword','termUser','termKeyId','ramTotal','cpuTotal','storageTotal','status');
                return $this->renderizarComDados($dados, $uploadResult['erro']);
            }
            $sshKeyId = $uploadResult['id'];
        }

        // Processar upload de chave do terminal
        $termUpload = $this->processarUploadChave('terminal_ssh_key_file', ($hostname ?: 'server') . '-terminal', $keyDir, $id, 'terminal_ssh_key_id');
        if ($termUpload['erro'] !== '' && !empty($_FILES['terminal_ssh_key_file']['name'])) {
            $termKeyId = $termUpload['id'];
            $dados = compact('id','hostname','ip','sshPort','sshUser','authType','sshKeyId','sshPassword','useSudo','sudoPassword','termUser','termKeyId','ramTotal','cpuTotal','storageTotal','status');
            return $this->renderizarComDados($dados, $termUpload['erro']);
        }
        $termKeyId = $termUpload['id'];

        $dados = compact('id','hostname','ip','sshPort','sshUser','authType','sshKeyId','sshPassword','useSudo','sudoPassword','termUser','termKeyId','ramTotal','cpuTotal','storageTotal','status');

        // Validações básicas
        if ($hostname === '' || $ip === '' || $sshPort <= 0 || $sshPort > 65535 || $ramTotal <= 0 || $cpuTotal <= 0 || $storageTotal <= 0) {
            return $this->renderizarComDados($dados, 'Preencha todos os campos obrigatórios.');
        }
        if ($sshUser === '') {
            return $this->renderizarComDados($dados, 'Informe o usuário SSH.');
        }

        // Validação por tipo de auth
        if ($authType === 'key') {
            if ($sshKeyId === '') return $this->renderizarComDados($dados, 'Faça upload da chave privada SSH.');
        } else {
            // Modo senha: obrigatória apenas no cadastro novo; na edição pode ficar em branco (mantém a atual)
            if ($id === 0 && $sshPassword === '') {
                return $this->renderizarComDados($dados, 'Informe a senha SSH.');
            }
        }

        // Testa conexão
        [$conexaoOk, $erroConexao] = $this->testarCredenciais($ip, $sshPort, $sshUser, $authType, $sshKeyId, $sshPassword, $id);
        if (!$conexaoOk) {
            return $this->renderizarComDados($dados, $erroConexao);
        }

        // Verifica se app.secret_key está configurado (necessário para cifrar senhas)
        $precisaCifrar = ($authType === 'password' && $sshPassword !== '') || ($useSudo && $sudoPassword !== '');
        if ($precisaCifrar && (string)\LRV\Core\Settings::obter('app.secret_key', '') === '') {
            return $this->renderizarComDados($dados, 'Configure app.secret_key em /equipe/configuracoes antes de salvar servidores com senha.');
        }

        // Persiste
        $savedId = $this->persistir($id, $hostname, $ip, $sshPort, $sshUser, $authType, $sshKeyId, $sshPassword, $useSudo, $sudoPassword, $termUser, $termKeyId, $ramTotal, $cpuTotal, $storageTotal, $status);
        if ($savedId === 0) {
            return $this->renderizarComDados($dados, 'Não foi possível salvar o servidor.');
        }

        // Marca online
        try {
            BancoDeDados::pdo()->prepare('UPDATE servers SET is_online=1, last_check_at=:c, last_error=NULL WHERE id=:id')
                ->execute([':c' => date('Y-m-d H:i:s'), ':id' => $savedId]);
        } catch (\Throwable) {}

        (new AuditLogService())->registrar('team', \LRV\Core\Auth::equipeId(),
            $id > 0 ? 'server.update' : 'server.create', 'server', $savedId,
            ['hostname' => $hostname, 'ip' => $ip, 'auth_type' => $authType], $req);

        return Resposta::redirecionar('/equipe/servidores');
    }

    // -------------------------------------------------------------------------
    // Testar conexão (botão no form)
    // -------------------------------------------------------------------------

    public function testarConexao(Requisicao $req): Resposta
    {
        $id          = (int)($req->post['id'] ?? 0);
        $ip          = trim((string)($req->post['ip_address'] ?? ''));
        $sshPort     = (int)($req->post['ssh_port'] ?? 22);
        $sshUser     = trim((string)($req->post['ssh_user'] ?? ''));
        $authType    = (string)($req->post['ssh_auth_type'] ?? 'password');
        $sshKeyId    = '';
        $sshPassword = (string)($req->post['ssh_password'] ?? '');

        // Para testar conexão com chave, usa a chave já salva no banco
        if ($authType === 'key' && $id > 0) {
            try {
                $stmt = BancoDeDados::pdo()->prepare('SELECT ssh_key_id FROM servers WHERE id=:id');
                $stmt->execute([':id' => $id]);
                $row = $stmt->fetch();
                $sshKeyId = (string)($row['ssh_key_id'] ?? '');
            } catch (\Throwable) {}
        }

        $hostname     = trim((string)($req->post['hostname'] ?? ''));
        $termUser     = trim((string)($req->post['terminal_ssh_user'] ?? ''));
        $termKeyId    = '';
        if ($id > 0) {
            try {
                $stmt = BancoDeDados::pdo()->prepare('SELECT terminal_ssh_key_id FROM servers WHERE id=:id');
                $stmt->execute([':id' => $id]);
                $row = $stmt->fetch();
                $termKeyId = (string)($row['terminal_ssh_key_id'] ?? '');
            } catch (\Throwable) {}
        }
        $ramTotal     = (int)($req->post['ram_total'] ?? 0);
        $cpuTotal     = (int)($req->post['cpu_total'] ?? 0);
        $storageTotal = (int)($req->post['storage_total'] ?? 0);
        $status       = (string)($req->post['status'] ?? 'active');

        if (!in_array($authType, ['key', 'password'], true)) $authType = 'password';
        if (!in_array($status, ['active', 'inactive', 'maintenance'], true)) $status = 'active';

        $servidor = [
            'id' => $id > 0 ? $id : null, 'hostname' => $hostname, 'ip_address' => $ip,
            'ssh_port' => $sshPort, 'ssh_user' => $sshUser, 'ssh_auth_type' => $authType,
            'ssh_key_id' => $sshKeyId, 'ssh_password' => '',
            'terminal_ssh_user' => $termUser, 'terminal_ssh_key_id' => $termKeyId,
            'ram_total' => $ramTotal, 'cpu_total' => $cpuTotal, 'storage_total' => $storageTotal,
            'status' => $status,
        ];

        [$ok, $erro] = $this->testarCredenciais($ip, $sshPort, $sshUser, $authType, $sshKeyId, $sshPassword, $id);

        if ($ok && $id > 0) {
            try {
                BancoDeDados::pdo()->prepare('UPDATE servers SET is_online=1, last_check_at=:c, last_error=NULL WHERE id=:id')
                    ->execute([':c' => date('Y-m-d H:i:s'), ':id' => $id]);
                $servidor['is_online']     = 1;
                $servidor['last_check_at'] = date('Y-m-d H:i:s');
            } catch (\Throwable) {}
        } elseif (!$ok && $id > 0) {
            try {
                BancoDeDados::pdo()->prepare('UPDATE servers SET is_online=0, last_check_at=:c, last_error=:e WHERE id=:id')
                    ->execute([':c' => date('Y-m-d H:i:s'), ':e' => mb_substr($erro, 0, 255), ':id' => $id]);
                $servidor['is_online'] = 0;
            } catch (\Throwable) {}
        }

        (new AuditLogService())->registrar('team', \LRV\Core\Auth::equipeId(),
            'server.test_connection', 'server', $id > 0 ? $id : null,
            ['ip' => $ip, 'auth_type' => $authType, 'ok' => $ok], $req);

        return Resposta::html(View::renderizar(__DIR__ . '/../../Views/equipe/servidor-editar.php', [
            'erro'        => $ok ? '' : $erro,
            'mensagem_ok' => $ok ? 'Conexão SSH validada com sucesso.' : '',
            'servidor'    => $servidor,
        ]), $ok ? 200 : 422);
    }

    // -------------------------------------------------------------------------
    // Inicializar (setup automático)
    // -------------------------------------------------------------------------

    /**
     * Inicia o setup: retorna lista de passos (limpa logs se não retomar).
     */
    public function inicializar(Requisicao $req): Resposta
    {
        $id      = (int)($req->post['id'] ?? 0);
        $retomar = !empty($req->post['retomar']);

        if ($id <= 0) return Resposta::json(['ok' => false, 'erro' => 'ID inválido.'], 400);

        try {
            $svc = new ServerSetupService();
            $svc->prepararSetup($id, $retomar);
            $resultado = $svc->listarPassos($id);
        } catch (\Throwable $e) {
            return Resposta::json(['ok' => false, 'erro' => $e->getMessage()], 500);
        }

        try {
            (new AuditLogService())->registrar('team', \LRV\Core\Auth::equipeId(),
                'server.setup_start', 'server', $id, ['retomar' => $retomar], $req);
        } catch (\Throwable $e) {}

        return Resposta::json($resultado);
    }

    /**
     * Executa UM passo do setup e retorna o resultado.
     */
    public function inicializarPasso(Requisicao $req): Resposta
    {
        $id   = (int)($req->post['id'] ?? 0);
        $step = trim((string)($req->post['step'] ?? ''));

        if ($id <= 0 || $step === '') {
            return Resposta::json(['ok' => false, 'erro' => 'Parâmetros inválidos.'], 400);
        }

        try {
            $resultado = (new ServerSetupService())->executarPasso($id, $step);
        } catch (\Throwable $e) {
            return Resposta::json(['ok' => false, 'step' => $step, 'status' => 'error', 'output' => $e->getMessage()], 500);
        }

        return Resposta::json($resultado);
    }

    /**
     * Finaliza o setup: atualiza status do servidor com base nos logs.
     */
    public function inicializarFinalizar(Requisicao $req): Resposta
    {
        $id = (int)($req->post['id'] ?? 0);
        if ($id <= 0) return Resposta::json(['ok' => false, 'erro' => 'ID inválido.'], 400);

        try {
            $resultado = (new ServerSetupService())->finalizarSetup($id);
        } catch (\Throwable $e) {
            return Resposta::json(['ok' => false, 'erro' => $e->getMessage()], 500);
        }

        try {
            (new AuditLogService())->registrar('team', \LRV\Core\Auth::equipeId(),
                'server.setup_done', 'server', $id, ['ok' => $resultado['ok']], $req);
        } catch (\Throwable $e) {}

        return Resposta::json($resultado);
    }

    public function logsInicializacao(Requisicao $req): Resposta
    {
        $id = (int)($req->query['id'] ?? 0);
        return Resposta::json(['logs' => $id > 0 ? (new ServerSetupService())->obterLogs($id) : []]);
    }

    // -------------------------------------------------------------------------
    // Terminal seguro
    // -------------------------------------------------------------------------

    public function terminalSeguro(Requisicao $req): Resposta
    {
        $id = (int)($req->query['id'] ?? 0);
        if ($id <= 0) return Resposta::texto('Servidor inválido.', 400);

        $pdo  = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT * FROM servers WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $servidor = $stmt->fetch();

        if (!is_array($servidor)) return Resposta::texto('Servidor não encontrado.', 404);

        return Resposta::html(View::renderizar(__DIR__ . '/../../Views/equipe/servidor-terminal-seguro.php', [
            'servidor' => $servidor,
        ]));
    }

    // -------------------------------------------------------------------------
    // Helpers privados
    // -------------------------------------------------------------------------

    /**
     * Testa a conexão SSH com as credenciais fornecidas.
     * Retorna [bool $ok, string $erro]
     */
    private function testarCredenciais(
        string $ip, int $sshPort, string $sshUser,
        string $authType, string $sshKeyId, string $sshPassword,
        int $existingId
    ): array {
        // Busca use_sudo e sudo_password do banco se for edição
        $useSudo     = false;
        $sudoSenha   = '';
        if ($existingId > 0) {
            try {
                $stmt = BancoDeDados::pdo()->prepare('SELECT use_sudo, sudo_password, ssh_password FROM servers WHERE id=:id');
                $stmt->execute([':id' => $existingId]);
                $row = $stmt->fetch();
                if (is_array($row)) {
                    $useSudo = (bool)(int)($row['use_sudo'] ?? 0);
                    $sudoRaw = SshCrypto::decifrar((string)($row['sudo_password'] ?? ''));
                    if ($sudoRaw === '') $sudoRaw = SshCrypto::decifrar((string)($row['ssh_password'] ?? ''));
                    $sudoSenha = $sudoRaw;
                    if ($sshPassword === '' && $authType === 'password') {
                        $sshPassword = SshCrypto::decifrar((string)($row['ssh_password'] ?? ''));
                    }
                }
            } catch (\Throwable) {}
        }

        // 1) Testa conexão SSH básica (whoami)
        try {
            $exec = new SshExecutor();
            if ($authType === 'password') {
                if ($sshPassword === '') return [false, 'Informe a senha SSH.'];
                $t = $exec->executarComSenha($ip, $sshPort, $sshUser, $sshPassword, 'whoami', 15);
            } else {
                $keyDir  = rtrim(ConfiguracoesSistema::sshKeyDir(), "/\\");
                $keyPath = $keyDir . DIRECTORY_SEPARATOR . $sshKeyId;
                if (!is_file($keyPath)) return [false, 'Arquivo de chave não encontrado: ' . $sshKeyId];
                $t = $exec->executar($ip, $sshPort, $sshUser, $keyPath, 'whoami', 15);
            }

            if (empty($t['ok'])) {
                $saida = trim((string)($t['saida'] ?? ''));
                $msg = 'Falha na conexão SSH.';
                if ($saida !== '') $msg .= "\n\n" . $saida;
                return [false, $msg];
            }
        } catch (\Throwable $e) {
            return [false, $e->getMessage()];
        }

        // 2) SSH OK — aceita o servidor mesmo sem Docker
        return [true, ''];
    }

    /**
     * Insere ou atualiza o servidor no banco.
     * Retorna o ID salvo (0 em caso de falha).
     */
    private function persistir(
        int $id, string $hostname, string $ip, int $sshPort,
        string $sshUser, string $authType, string $sshKeyId, string $sshPassword,
        bool $useSudo, string $sudoPassword,
        string $termUser, string $termKeyId,
        int $ramTotal, int $cpuTotal, int $storageTotal, string $status
    ): int {
        $pdo = BancoDeDados::pdo();

        try {
            $senhaCifrada = null;
            if ($authType === 'password' && $sshPassword !== '') {
                $senhaCifrada = SshCrypto::cifrar($sshPassword);
            }

            $sudoCifrado = null;
            if ($useSudo && $sudoPassword !== '') {
                $sudoCifrado = SshCrypto::cifrar($sudoPassword);
            }

            if ($id > 0) {
                $sets  = 'hostname=:h, ip_address=:ip, ssh_port=:sp, ssh_user=:su, ssh_auth_type=:at, ssh_key_id=:sk, use_sudo=:us, terminal_ssh_user=:tsu, terminal_ssh_key_id=:tsk, ram_total=:rt, cpu_total=:ct, storage_total=:st, status=:s';
                $binds = [
                    ':h' => $hostname, ':ip' => $ip, ':sp' => $sshPort,
                    ':su' => $sshUser, ':at' => $authType,
                    ':sk' => $sshKeyId !== '' ? $sshKeyId : null,
                    ':us' => $useSudo ? 1 : 0,
                    ':tsu' => $termUser !== '' ? $termUser : null,
                    ':tsk' => $termKeyId !== '' ? $termKeyId : null,
                    ':rt' => $ramTotal, ':ct' => $cpuTotal, ':st' => $storageTotal,
                    ':s' => $status, ':id' => $id,
                ];
                if ($senhaCifrada !== null) { $sets .= ', ssh_password=:pw';   $binds[':pw'] = $senhaCifrada; }
                if ($sudoCifrado  !== null) { $sets .= ', sudo_password=:spw'; $binds[':spw'] = $sudoCifrado; }
                // Se use_sudo foi desmarcado, limpa sudo_password
                if (!$useSudo)             { $sets .= ', sudo_password=NULL'; }
                $pdo->prepare("UPDATE servers SET {$sets} WHERE id=:id")->execute($binds);
                return $id;
            } else {
                $stmt = $pdo->prepare('INSERT INTO servers
                    (hostname, ip_address, ssh_port, ssh_user, ssh_auth_type, ssh_key_id, ssh_password,
                     use_sudo, sudo_password,
                     terminal_ssh_user, terminal_ssh_key_id,
                     ram_total, ram_used, cpu_total, cpu_used, storage_total, storage_used,
                     status, setup_status, created_at)
                    VALUES (:h,:ip,:sp,:su,:at,:sk,:pw,:us,:spw,:tsu,:tsk,:rt,0,:ct,0,:st,0,:s,\'pending\',:cr)');
                $stmt->execute([
                    ':h' => $hostname, ':ip' => $ip, ':sp' => $sshPort,
                    ':su' => $sshUser, ':at' => $authType,
                    ':sk' => $sshKeyId !== '' ? $sshKeyId : null,
                    ':pw' => $senhaCifrada,
                    ':us' => $useSudo ? 1 : 0,
                    ':spw' => $sudoCifrado,
                    ':tsu' => $termUser !== '' ? $termUser : null,
                    ':tsk' => $termKeyId !== '' ? $termKeyId : null,
                    ':rt' => $ramTotal, ':ct' => $cpuTotal, ':st' => $storageTotal,
                    ':s' => $status, ':cr' => date('Y-m-d H:i:s'),
                ]);
                return (int)$pdo->lastInsertId();
            }
        } catch (\Throwable) {
            return 0;
        }
    }

    /**
     * Processa upload de arquivo de chave SSH.
     * Retorna ['id' => string, 'erro' => string].
     */
    private function processarUploadChave(string $fieldName, string $nomeBase, string $keyDir, int $existingId, string $dbColumn): array
    {
        $file = $_FILES[$fieldName] ?? null;
        $hasUpload = is_array($file) && !empty($file['name']) && ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK;

        if ($hasUpload) {
            $tmpPath = (string)($file['tmp_name'] ?? '');
            $size = (int)($file['size'] ?? 0);

            if ($size > 32768) {
                return ['id' => '', 'erro' => 'Arquivo de chave muito grande (máx 32KB).'];
            }
            if ($size === 0 || !is_file($tmpPath)) {
                return ['id' => '', 'erro' => 'Arquivo de chave inválido.'];
            }

            $conteudo = file_get_contents($tmpPath);
            if ($conteudo === false || trim($conteudo) === '') {
                return ['id' => '', 'erro' => 'Não foi possível ler o arquivo de chave.'];
            }

            // Validar que parece uma chave SSH (privada ou pública)
            $trimmed = trim($conteudo);
            $isPrivate = str_starts_with($trimmed, '-----BEGIN') && str_contains($trimmed, 'PRIVATE KEY');
            $isPublic = str_starts_with($trimmed, 'ssh-') || (str_starts_with($trimmed, '-----BEGIN') && str_contains($trimmed, 'PUBLIC KEY'));
            $isOpenSsh = str_starts_with($trimmed, '-----BEGIN OPENSSH PRIVATE KEY');

            if (!$isPrivate && !$isPublic && !$isOpenSsh) {
                return ['id' => '', 'erro' => 'O arquivo não parece ser uma chave SSH válida (privada ou pública).'];
            }

            // Gerar nome seguro para o arquivo
            $safeName = preg_replace('/[^a-zA-Z0-9_.-]/', '-', $nomeBase);
            if ($safeName === '' || $safeName === '-') $safeName = 'key-' . bin2hex(random_bytes(4));
            $destPath = $keyDir . DIRECTORY_SEPARATOR . $safeName;

            if (!@file_put_contents($destPath, $conteudo)) {
                return ['id' => '', 'erro' => 'Não foi possível salvar a chave no diretório configurado.'];
            }
            @chmod($destPath, 0600);

            return ['id' => $safeName, 'erro' => ''];
        }

        // Sem upload — manter chave existente do banco
        if ($existingId > 0) {
            try {
                $stmt = BancoDeDados::pdo()->prepare("SELECT {$dbColumn} FROM servers WHERE id=:id");
                $stmt->execute([':id' => $existingId]);
                $row = $stmt->fetch();
                return ['id' => (string)($row[$dbColumn] ?? ''), 'erro' => ''];
            } catch (\Throwable) {}
        }

        return ['id' => '', 'erro' => ''];
    }

    private function renderizarComDados(array $dados, string $erro): Resposta
    {
        $servidor = [
            'id'                  => $dados['id'] > 0 ? $dados['id'] : null,
            'hostname'            => $dados['hostname'],
            'ip_address'          => $dados['ip'],
            'ssh_port'            => $dados['sshPort'],
            'ssh_user'            => $dados['sshUser'],
            'ssh_auth_type'       => $dados['authType'],
            'ssh_key_id'          => $dados['sshKeyId'],
            'ssh_password'        => '',
            'use_sudo'            => $dados['useSudo'] ? 1 : 0,
            'sudo_password'       => '',
            'terminal_ssh_user'   => $dados['termUser'],
            'terminal_ssh_key_id' => $dados['termKeyId'],
            'ram_total'           => $dados['ramTotal'],
            'cpu_total'           => $dados['cpuTotal'],
            'storage_total'       => $dados['storageTotal'],
            'status'              => $dados['status'],
        ];

        return Resposta::html(View::renderizar(__DIR__ . '/../../Views/equipe/servidor-editar.php', [
            'erro'        => $erro,
            'mensagem_ok' => '',
            'servidor'    => $servidor,
        ]), 422);
    }
}
