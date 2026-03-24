<?php

declare(strict_types=1);

namespace LRV\App\Services\Terminal;

use LRV\Core\BancoDeDados;
use LRV\Core\ConfiguracoesSistema;
use LRV\Core\Rbac;

final class TerminalWsApp implements \Ratchet\MessageComponentInterface
{
    private \SplObjectStorage $conns;

    private readonly TerminalTokensService $tokens;

    private readonly ClientTerminalTokensService $clientTokens;

    private readonly TerminalAuditoriaService $audit;

    private readonly ClientTerminalAuditoriaService $clientAudit;

    private readonly int $idleTimeout;

    public function __construct(private readonly object $loop)
    {
        $this->conns = new \SplObjectStorage();
        $this->tokens = new TerminalTokensService();
        $this->clientTokens = new ClientTerminalTokensService();
        $this->audit = new TerminalAuditoriaService();
        $this->clientAudit = new ClientTerminalAuditoriaService();
        $this->idleTimeout = ConfiguracoesSistema::terminalIdleTimeoutSegundos();

        $this->loop->addPeriodicTimer(0.05, function (): void {
            $this->tick();
        });
    }

    public function onOpen($conn): void
    {
        try {
            $path = '';
            $query = '';
            $headers = [];

            if (isset($conn->httpRequest)) {
                $path = (string) $conn->httpRequest->getUri()->getPath();
                $query = (string) $conn->httpRequest->getUri()->getQuery();
                $headers = [
                    'user-agent' => (string) $conn->httpRequest->getHeaderLine('User-Agent'),
                    'x-forwarded-for' => (string) $conn->httpRequest->getHeaderLine('X-Forwarded-For'),
                ];
            }

            if ($path !== '' && $path !== '/' && $path !== '/ws/terminal') {
                $conn->send("Path inválido.\n");
                $conn->close();
                return;
            }

            parse_str($query, $q);
            $token = trim((string) ($q['token'] ?? ''));
            if ($token === '') {
                $conn->send("Token ausente.\n");
                $conn->close();
                return;
            }

            $ip = $this->extrairIp($headers);
            $ua = trim((string) ($headers['user-agent'] ?? ''));

            $tipo = '';
            $sessionId = 0;
            $sessionUid = '';
            $serverId = 0;
            $vpsId = 0;
            $clientId = 0;
            $containerId = '';

            $sshHost = '';
            $sshPort = 22;
            $sshUser = '';
            $sshKeyId = '';
            $sshAuthType = 'key';
            $sshPasswordEnc = '';
            $remoteCommand = null;

            $val = $this->tokens->consumirToken($token);
            if (!empty($val['ok'])) {
                $tipo = 'admin';

                $equipeId = (int) ($val['equipe_id'] ?? 0);
                $serverId = (int) ($val['server_id'] ?? 0);

                if ($equipeId <= 0 || $serverId <= 0) {
                    $conn->send("Token inválido.\n");
                    $conn->close();
                    return;
                }

                try {
                    if (!Rbac::temPermissao($equipeId, 'manage_terminal')) {
                        $conn->send("Acesso negado.\n");
                        $conn->close();
                        return;
                    }
                } catch (\Throwable $e) {
                    $conn->send("Acesso negado.\n");
                    $conn->close();
                    return;
                }

                $server = $this->carregarServidor($serverId);
                if ($server === null) {
                    $conn->send("Servidor não encontrado.\n");
                    $conn->close();
                    return;
                }

                if (($server['status'] ?? '') !== 'active') {
                    $conn->send("Servidor não está ativo.\n");
                    $conn->close();
                    return;
                }

                if (array_key_exists('is_online', (array) $server) && (int) ($server['is_online'] ?? 0) !== 1) {
                    $conn->send("Servidor offline.\n");
                    $conn->close();
                    return;
                }

                $sshHost = (string) ($server['ip_address'] ?? '');
                $sshPort = (int) ($server['ssh_port'] ?? 22);

                $sshUser = (string) ($server['ssh_user'] ?? '');
                $sshKeyId = (string) ($server['ssh_key_id'] ?? '');
                $sshAuthType = (string) ($server['ssh_auth_type'] ?? 'key');
                $sshPasswordEnc = (string) ($server['ssh_password'] ?? '');

                if (trim($sshHost) === '' || $sshPort <= 0 || $sshPort > 65535 || trim($sshUser) === '') {
                    $conn->send("Servidor sem dados de SSH.\n");
                    $conn->close();
                    return;
                }

                if ($sshAuthType === 'key' && trim($sshKeyId) === '') {
                    $conn->send("Servidor sem chave SSH configurada.\n");
                    $conn->close();
                    return;
                }

                $sess = $this->audit->iniciarSessao($equipeId, $serverId, $ip, $ua);
                $sessionId = (int) ($sess['session_id'] ?? 0);
                $sessionUid = (string) ($sess['session_uid'] ?? '');

                $remoteCommand = null;
            } else {
                $valCli = $this->clientTokens->consumirToken($token);
                if (empty($valCli['ok'])) {
                    $conn->send(((string) ($valCli['erro'] ?? 'Token inválido.')) . "\n");
                    $conn->close();
                    return;
                }

                $tipo = 'client';
                $clientId = (int) ($valCli['client_id'] ?? 0);
                $vpsId = (int) ($valCli['vps_id'] ?? 0);

                if ($clientId <= 0 || $vpsId <= 0) {
                    $conn->send("Token inválido.\n");
                    $conn->close();
                    return;
                }

                $vps = $this->carregarVps($vpsId);
                if (!is_array($vps) || (int) ($vps['client_id'] ?? 0) !== $clientId) {
                    $conn->send("VPS não encontrada.\n");
                    $conn->close();
                    return;
                }

                if ((string) ($vps['status'] ?? '') !== 'running') {
                    $conn->send("VPS não está em execução.\n");
                    $conn->close();
                    return;
                }

                $serverId = (int) ($vps['server_id'] ?? 0);
                $containerId = trim((string) ($vps['container_id'] ?? ''));

                if ($serverId <= 0 || $containerId === '') {
                    $conn->send("VPS sem node/contêiner.\n");
                    $conn->close();
                    return;
                }

                $server = $this->carregarServidor($serverId);
                if ($server === null) {
                    $conn->send("Node não encontrado.\n");
                    $conn->close();
                    return;
                }

                if (($server['status'] ?? '') !== 'active') {
                    $conn->send("Node não está ativo.\n");
                    $conn->close();
                    return;
                }

                if (array_key_exists('is_online', (array) $server) && (int) ($server['is_online'] ?? 0) !== 1) {
                    $conn->send("Node offline.\n");
                    $conn->close();
                    return;
                }

                $sshHost = (string) ($server['ip_address'] ?? '');
                $sshPort = (int) ($server['ssh_port'] ?? 22);
                $sshUser = (string) ($server['terminal_ssh_user'] ?? '');
                $sshKeyId = (string) ($server['terminal_ssh_key_id'] ?? '');

                if (trim($sshHost) === '' || $sshPort <= 0 || $sshPort > 65535 || trim($sshUser) === '' || trim($sshKeyId) === '') {
                    $conn->send("Terminal seguro não configurado no node.\n");
                    $conn->close();
                    return;
                }

                if (preg_match('/^[a-zA-Z0-9][a-zA-Z0-9_.-]+$/', $containerId) !== 1) {
                    $conn->send("Contêiner inválido.\n");
                    $conn->close();
                    return;
                }

                $sess = $this->clientAudit->iniciarSessao($clientId, $vpsId, $serverId, $ip, $ua);
                $sessionId = (int) ($sess['session_id'] ?? 0);
                $sessionUid = (string) ($sess['session_uid'] ?? '');

                $remoteCommand = 'lrv-terminal --mode=client --client-id=' . $clientId . ' --vps-id=' . $vpsId . ' --container-id=' . $containerId . ' --session=' . $sessionUid;
            }

            $keyDir = rtrim(ConfiguracoesSistema::sshKeyDir(), "/\\");

            $proc = null;

            if ($sshAuthType === 'password' && $sshPasswordEnc !== '') {
                // Modo senha — SSH_ASKPASS + setsid
                $sshPassword = \LRV\App\Services\Infra\SshCrypto::decifrar($sshPasswordEnc);
                if ($sshPassword === '') {
                    if ($tipo === 'admin' && $sessionId > 0) $this->audit->encerrarSessao($sessionId);
                    if ($tipo === 'client' && $sessionId > 0) $this->clientAudit->encerrarSessao($sessionId);
                    $conn->send("Senha SSH não pôde ser decifrada.\n");
                    $conn->close();
                    return;
                }
                $proc = $this->abrirSshComSenha($sshHost, $sshPort, $sshUser, $sshPassword, $remoteCommand);
            } else {
                // Modo chave
                if ($keyDir === '') {
                    if ($tipo === 'admin' && $sessionId > 0) $this->audit->encerrarSessao($sessionId);
                    if ($tipo === 'client' && $sessionId > 0) $this->clientAudit->encerrarSessao($sessionId);
                    $conn->send("Diretório de chaves SSH não configurado.\n");
                    $conn->close();
                    return;
                }

                $keyPath = $keyDir . DIRECTORY_SEPARATOR . $sshKeyId;
                if (!is_file($keyPath)) {
                    if ($tipo === 'admin' && $sessionId > 0) $this->audit->encerrarSessao($sessionId);
                    if ($tipo === 'client' && $sessionId > 0) $this->clientAudit->encerrarSessao($sessionId);
                    $conn->send("Chave SSH não encontrada.\n");
                    $conn->close();
                    return;
                }
                $proc = $this->abrirSsh($sshHost, $sshPort, $sshUser, $keyPath, $remoteCommand);
            }

            if (!function_exists('proc_open')) {
                if ($tipo === 'admin' && $sessionId > 0) $this->audit->encerrarSessao($sessionId);
                if ($tipo === 'client' && $sessionId > 0) $this->clientAudit->encerrarSessao($sessionId);
                $conn->send("proc_open indisponível.\n");
                $conn->close();
                return;
            }

            if ($proc === null) {
                if ($tipo === 'admin' && $sessionId > 0) $this->audit->encerrarSessao($sessionId);
                if ($tipo === 'client' && $sessionId > 0) $this->clientAudit->encerrarSessao($sessionId);
                $conn->send("Falha ao iniciar SSH.\n");
                $conn->close();
                return;
            }

            $this->conns[$conn] = [
                'proc' => $proc['proc'],
                'pipes' => $proc['pipes'],
                'session_id' => $sessionId,
                'session_tipo' => $tipo,
                'last_activity' => microtime(true),
            ];

            if ($tipo === 'client') {
                $conn->send("Conectado à VPS.\n");
            } else {
                $conn->send("Conectado ao node.\n");
            }
        } catch (\Throwable $e) {
            try {
                $conn->send("Erro ao iniciar sessão.\n");
            } catch (\Throwable $e2) {
            }

            try {
                $conn->close();
            } catch (\Throwable $e3) {
            }
        }
    }

    public function onMessage($from, $msg): void
    {
        if (!isset($this->conns[$from])) {
            try {
                $from->send("Sessão inválida.\n");
            } catch (\Throwable $e) {
            }
            $from->close();
            return;
        }

        $meta = $this->conns[$from];
        $pipes = $meta['pipes'] ?? null;

        if (!is_array($pipes) || !isset($pipes[0]) || !is_resource($pipes[0])) {
            $from->close();
            return;
        }

        $texto = (string) $msg;
        if ($texto === '') {
            return;
        }

        // Tratar mensagem de resize JSON: {"type":"resize","cols":X,"rows":Y}
        if ($texto[0] === '{') {
            $decoded = json_decode($texto, true);
            if (is_array($decoded) && ($decoded['type'] ?? '') === 'resize') {
                $cols = max(1, min(500, (int) ($decoded['cols'] ?? 80)));
                $rows = max(1, min(200, (int) ($decoded['rows'] ?? 24)));
                $proc = $meta['proc'] ?? null;
                if (is_resource($proc)) {
                    // Enviar sequência de escape ANSI para resize (stty)
                    $pipes = $meta['pipes'] ?? null;
                    if (is_array($pipes) && isset($pipes[0]) && is_resource($pipes[0])) {
                        @fwrite($pipes[0], "stty cols {$cols} rows {$rows}\n");
                    }
                }
                return;
            }
        }

        if (strlen($texto) > 32_000) {
            $texto = substr($texto, 0, 32_000);
        }

        $this->conns[$from]['last_activity'] = microtime(true);

        $sessionId = (int) ($meta['session_id'] ?? 0);
        $tipo = (string) ($meta['session_tipo'] ?? 'admin');
        $linhas = preg_split('/\R/', $texto) ?: [];
        $toSend = '';

        foreach ($linhas as $l) {
            $l = rtrim((string) $l);
            if (trim($l) === '') {
                continue;
            }

            if ($tipo !== 'client') {
                $val = $this->validarComando($l);
                if (empty($val['ok'])) {
                    $erro = (string) ($val['erro'] ?? 'Comando bloqueado.');
                    $this->audit->registrarComando($sessionId, '[BLOQUEADO] ' . $l);
                    try {
                        $from->send($erro . "\n");
                    } catch (\Throwable $e) {
                    }
                    continue;
                }
                $this->audit->registrarComando($sessionId, $l);
            } else {
                $valCli = $this->validarComandoCliente($l);
                if (empty($valCli['ok'])) {
                    $erro = (string) ($valCli['erro'] ?? 'Comando bloqueado.');
                    $this->clientAudit->registrarComando($sessionId, '[BLOQUEADO] ' . $l);
                    try {
                        $from->send($erro . "\n");
                    } catch (\Throwable $e) {
                    }
                    continue;
                }
                $this->clientAudit->registrarComando($sessionId, $l);
            }
            $toSend .= $l . "\n";
        }

        if ($toSend !== '') {
            @fwrite($pipes[0], $toSend);
        }
    }

    public function onClose($conn): void
    {
        if (!isset($this->conns[$conn])) {
            return;
        }

        $meta = $this->conns[$conn];
        $this->cleanupConn($meta);

        $this->conns->detach($conn);
    }

    public function onError($conn, \Exception $e): void
    {
        try {
            $conn->send("Erro na conexão.\n");
        } catch (\Throwable $e2) {
        }

        try {
            $conn->close();
        } catch (\Throwable $e3) {
        }

        $this->onClose($conn);
    }

    private function tick(): void
    {
        foreach ($this->conns as $conn) {
            $meta = $this->conns[$conn];

            $proc = $meta['proc'] ?? null;
            $pipes = $meta['pipes'] ?? null;

            if (!is_resource($proc) || !is_array($pipes)) {
                $this->fechar($conn, $meta, "Sessão encerrada.\n");
                continue;
            }

            $saida = '';
            foreach ([1, 2] as $i) {
                if (isset($pipes[$i]) && is_resource($pipes[$i])) {
                    $chunk = (string) @stream_get_contents($pipes[$i]);
                    if ($chunk !== '') {
                        $saida .= $chunk;
                    }
                }
            }

            if ($saida !== '') {
                try {
                    $conn->send($saida);
                } catch (\Throwable $e) {
                    $this->fechar($conn, $meta, '');
                    continue;
                }
                $this->conns[$conn]['last_activity'] = microtime(true);
            }

            $st = @proc_get_status($proc);
            if (is_array($st) && empty($st['running'])) {
                $this->fechar($conn, $meta, "SSH finalizado.\n");
                continue;
            }

            $last = (float) ($meta['last_activity'] ?? microtime(true));
            if ($this->idleTimeout > 0 && (microtime(true) - $last) > $this->idleTimeout) {
                $this->fechar($conn, $meta, "Timeout por inatividade.\n");
                continue;
            }
        }
    }

    private function fechar($conn, array $meta, string $msg): void
    {
        if ($msg !== '') {
            try {
                $conn->send($msg);
            } catch (\Throwable $e) {
            }
        }

        try {
            $conn->close();
        } catch (\Throwable $e) {
        }

        $this->cleanupConn($meta);

        if (isset($this->conns[$conn])) {
            $this->conns->detach($conn);
        }
    }

    private function cleanupConn(array $meta): void
    {
        $sessionId = (int) ($meta['session_id'] ?? 0);
        $tipo = (string) ($meta['session_tipo'] ?? 'admin');
        if ($sessionId > 0) {
            try {
                if ($tipo === 'client') {
                    $this->clientAudit->encerrarSessao($sessionId);
                } else {
                    $this->audit->encerrarSessao($sessionId);
                }
            } catch (\Throwable $e) {
            }
        }

        $pipes = $meta['pipes'] ?? null;
        if (is_array($pipes)) {
            foreach ([0, 1, 2] as $i) {
                if (isset($pipes[$i]) && is_resource($pipes[$i])) {
                    @fclose($pipes[$i]);
                }
            }
        }

        $proc = $meta['proc'] ?? null;
        if (is_resource($proc)) {
            try {
                @proc_terminate($proc);
            } catch (\Throwable $e) {
            }
            @proc_close($proc);
        }
    }

    private function carregarServidor(int $serverId): ?array
    {
        $pdo = BancoDeDados::pdo();
        try {
            $stmt = $pdo->prepare('SELECT id, hostname, ip_address, ssh_port, ssh_user, ssh_auth_type, ssh_key_id, ssh_password, terminal_ssh_user, terminal_ssh_key_id, status, is_online FROM servers WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $serverId]);
            $s = $stmt->fetch();
            return is_array($s) ? $s : null;
        } catch (\Throwable $e) {
            try {
                $stmt = $pdo->prepare('SELECT id, hostname, ip_address, ssh_port, ssh_user, ssh_auth_type, ssh_key_id, ssh_password, terminal_ssh_user, terminal_ssh_key_id, status FROM servers WHERE id = :id LIMIT 1');
                $stmt->execute([':id' => $serverId]);
                $s = $stmt->fetch();
                return is_array($s) ? $s : null;
            } catch (\Throwable $e2) {
                $stmt = $pdo->prepare('SELECT id, hostname, ip_address, ssh_port, ssh_user, ssh_key_id, status FROM servers WHERE id = :id LIMIT 1');
                $stmt->execute([':id' => $serverId]);
                $s = $stmt->fetch();
                return is_array($s) ? $s : null;
            }
        }
    }

    private function carregarVps(int $vpsId): ?array
    {
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT id, client_id, server_id, container_id, status FROM vps WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $vpsId]);
        $v = $stmt->fetch();
        return is_array($v) ? $v : null;
    }

    private function abrirSsh(string $host, int $porta, string $usuario, string $keyPath, ?string $remoteCommand = null): ?array
    {
        $knownHosts = '/dev/null';
        if (PHP_OS_FAMILY === 'Windows') {
            $knownHosts = 'NUL';
        }

        $destino = trim($usuario) . '@' . trim($host);

        $args = [];
        $args[] = 'ssh';
        $args[] = '-tt';
        $args[] = '-i ' . escapeshellarg($keyPath);
        $args[] = '-p ' . (int) $porta;
        $args[] = '-o BatchMode=yes';
        $args[] = '-o ConnectTimeout=8';
        $args[] = '-o StrictHostKeyChecking=no';
        $args[] = '-o UserKnownHostsFile=' . $knownHosts;
        $args[] = escapeshellarg($destino);

        if ($remoteCommand !== null && trim($remoteCommand) !== '') {
            $args[] = escapeshellarg($remoteCommand);
        }

        $cmd = implode(' ', $args);

        $descriptorspec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $proc = @proc_open($cmd, $descriptorspec, $pipes);
        if (!is_resource($proc) || !is_array($pipes)) {
            return null;
        }

        foreach ([0, 1, 2] as $i) {
            if (isset($pipes[$i]) && is_resource($pipes[$i])) {
                @stream_set_blocking($pipes[$i], false);
            }
        }

        return [
            'proc' => $proc,
            'pipes' => $pipes,
        ];
    }

    /**
     * Abre sessão SSH interativa com senha via SSH_ASKPASS + setsid.
     */
    private function abrirSshComSenha(string $host, int $porta, string $usuario, string $senha, ?string $remoteCommand = null): ?array
    {
        $knownHosts = PHP_OS_FAMILY === 'Windows' ? 'NUL' : '/dev/null';

        // Cria script askpass em storage/ (evita /tmp noexec)
        $storageDir = defined('BASE_PATH') ? BASE_PATH . '/storage' : dirname(__DIR__, 3) . '/storage';
        if (!is_dir($storageDir)) @mkdir($storageDir, 0700, true);

        $senhaFile = $storageDir . '/ssh_pw_' . bin2hex(random_bytes(8));
        file_put_contents($senhaFile, $senha);
        chmod($senhaFile, 0600);

        $askpassScript = $storageDir . '/askpass_' . bin2hex(random_bytes(8)) . '.sh';
        file_put_contents($askpassScript, "#!/bin/sh\ncat " . escapeshellarg($senhaFile) . "\n");
        chmod($askpassScript, 0700);

        $destino = trim($usuario) . '@' . trim($host);

        $args = [];
        $args[] = 'setsid';
        $args[] = 'ssh';
        $args[] = '-tt';
        $args[] = '-p ' . (int) $porta;
        $args[] = '-o ConnectTimeout=8';
        $args[] = '-o StrictHostKeyChecking=no';
        $args[] = '-o UserKnownHostsFile=' . $knownHosts;
        $args[] = '-o PasswordAuthentication=yes';
        $args[] = '-o PubkeyAuthentication=no';
        $args[] = '-o NumberOfPasswordPrompts=1';
        $args[] = escapeshellarg($destino);

        if ($remoteCommand !== null && trim($remoteCommand) !== '') {
            $args[] = escapeshellarg($remoteCommand);
        }

        $cmd = implode(' ', $args);

        $env = [
            'SSH_ASKPASS' => $askpassScript,
            'SSH_ASKPASS_REQUIRE' => 'force',
            'DISPLAY' => ':0',
            'PATH' => getenv('PATH') ?: '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin',
        ];

        $descriptorspec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $proc = @proc_open($cmd, $descriptorspec, $pipes, null, $env);

        // Limpa arquivos temporários após um delay (o SSH já leu a senha)
        // Usamos o loop para limpar depois de 5 segundos
        $this->loop->addTimer(5.0, function () use ($askpassScript, $senhaFile) {
            @unlink($askpassScript);
            @unlink($senhaFile);
        });

        if (!is_resource($proc) || !is_array($pipes)) {
            @unlink($askpassScript);
            @unlink($senhaFile);
            return null;
        }

        foreach ([0, 1, 2] as $i) {
            if (isset($pipes[$i]) && is_resource($pipes[$i])) {
                @stream_set_blocking($pipes[$i], false);
            }
        }

        return [
            'proc' => $proc,
            'pipes' => $pipes,
        ];
    }

    private function extrairIp(array $headers): string
    {
        $xff = trim((string) ($headers['x-forwarded-for'] ?? ''));
        if ($xff !== '') {
            $partes = array_map('trim', explode(',', $xff));
            $ip = (string) ($partes[0] ?? '');
            if ($ip !== '') {
                return $ip;
            }
        }

        if (isset($_SERVER['REMOTE_ADDR'])) {
            return (string) $_SERVER['REMOTE_ADDR'];
        }

        return '';
    }

    private function validarComando(string $cmd): array
    {
        $cmd = trim($cmd);
        if ($cmd === '') {
            return ['ok' => false, 'erro' => 'Comando vazio.'];
        }

        if (!ConfiguracoesSistema::terminalSafeModeHabilitado()) {
            return ['ok' => true];
        }

        if (preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', $cmd) === 1) {
            return ['ok' => false, 'erro' => 'Comando bloqueado.'];
        }

        if (preg_match('/[;&|><`]/', $cmd) === 1) {
            return ['ok' => false, 'erro' => 'Comando bloqueado (caracteres não permitidos).'];
        }

        if (str_contains($cmd, '$(') || str_contains($cmd, '${')) {
            return ['ok' => false, 'erro' => 'Comando bloqueado (expansão não permitida).'];
        }

        if (preg_match('/\b(xmrig|minerd|cpuminer|cryptonight|nicehash|ethminer|stratum\+tcp)\b/i', $cmd) === 1) {
            return ['ok' => false, 'erro' => 'Comando bloqueado (padrão suspeito).'];
        }

        if (preg_match('/^\s*(sudo|su|apt|apt-get|yum|dnf|apk|pacman|pip3?|npm|yarn|node|python3?|perl|ruby|php|gcc|make|git|iptables|ufw|nft|mount|umount|mkfs|dd|rm|mv|chmod|chown|useradd|adduser|passwd|reboot|shutdown|poweroff|kill(all)?|pkill|nohup|screen|tmux|crontab|at|wget|curl|nc|netcat|ncat|socat|telnet)\b/i', $cmd) === 1) {
            return ['ok' => false, 'erro' => 'Comando bloqueado pelo modo seguro.'];
        }

        if (preg_match('/^\s*docker\b/i', $cmd) === 1) {
            $parts = preg_split('/\s+/', $cmd) ?: [];
            $sub = strtolower((string) ($parts[1] ?? ''));
            if (!in_array($sub, ['ps', 'logs', 'inspect', 'stats', 'top', 'version', 'info'], true)) {
                return ['ok' => false, 'erro' => 'Comando docker bloqueado pelo modo seguro.'];
            }
            return ['ok' => true];
        }

        if (preg_match('/^\s*systemctl\b/i', $cmd) === 1) {
            $parts = preg_split('/\s+/', $cmd) ?: [];
            $sub = strtolower((string) ($parts[1] ?? ''));
            if (!in_array($sub, ['status', 'is-active', 'list-units', 'list-unit-files'], true)) {
                return ['ok' => false, 'erro' => 'Comando systemctl bloqueado pelo modo seguro.'];
            }
            return ['ok' => true];
        }

        if (preg_match('/^\s*(ls|pwd|cd|clear|whoami|id|uname|uptime|free|df|du|ps|ss|netstat|ping|traceroute|dig|nslookup|host|cat|tail|head|grep|find|journalctl|dmesg|exit)\b/i', $cmd) !== 1) {
            return ['ok' => false, 'erro' => 'Comando não permitido no modo seguro.'];
        }

        return ['ok' => true];
    }

    private function validarComandoCliente(string $cmd): array
    {
        $cmd = trim($cmd);
        if ($cmd === '') {
            return ['ok' => false, 'erro' => 'Comando vazio.'];
        }

        if (preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', $cmd) === 1) {
            return ['ok' => false, 'erro' => 'Comando bloqueado.'];
        }

        if (preg_match('/[;&|><`]/', $cmd) === 1) {
            return ['ok' => false, 'erro' => 'Comando bloqueado (caracteres não permitidos).'];
        }

        if (str_contains($cmd, '$(') || str_contains($cmd, '${')) {
            return ['ok' => false, 'erro' => 'Comando bloqueado (expansão não permitida).'];
        }

        if (preg_match('/\b(xmrig|minerd|cpuminer|cryptonight|nicehash|ethminer|stratum\+tcp)\b/i', $cmd) === 1) {
            return ['ok' => false, 'erro' => 'Comando bloqueado (padrão suspeito).'];
        }

        if (preg_match('/^\s*rm\s+-rf\s+\/\s*(--no-preserve-root\s*)?$/i', $cmd) === 1) {
            return ['ok' => false, 'erro' => 'Comando bloqueado (padrão perigoso).'];
        }

        if (preg_match('/^\s*find\b/i', $cmd) === 1) {
            if (preg_match('/\s-(exec|execdir|ok)\b/i', $cmd) === 1) {
                return ['ok' => false, 'erro' => 'Comando bloqueado (find -exec).'];
            }
        }

        if (preg_match('/^\s*(sudo|su|shutdown|poweroff|reboot|halt|init|systemctl|service|docker|iptables|ufw|nft|mount|umount|mkfs|dd|kill(all)?|pkill|nohup|screen|tmux|crontab|at|wget|curl|nc|netcat|ncat|socat|telnet)\b/i', $cmd) === 1) {
            return ['ok' => false, 'erro' => 'Comando bloqueado.'];
        }

        if (preg_match('/^\s*(ls|pwd|cd|clear|whoami|id|uname|uptime|free|df|du|ps|ss|netstat|ping|traceroute|dig|nslookup|host|cat|tail|head|grep|find|env|printenv|echo|exit)\b/i', $cmd) !== 1) {
            return ['ok' => false, 'erro' => 'Comando não permitido no terminal seguro.'];
        }

        return ['ok' => true];
    }
}
