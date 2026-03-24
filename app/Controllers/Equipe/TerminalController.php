<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Equipe;

use LRV\App\Services\Audit\AuditLogService;
use LRV\App\Services\Infra\SshCrypto;
use LRV\App\Services\Infra\SshExecutor;
use LRV\App\Services\Terminal\TerminalTokensService;
use LRV\Core\Auth;
use LRV\Core\BancoDeDados;
use LRV\Core\ConfiguracoesSistema;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;

final class TerminalController
{
    public function index(Requisicao $req): Resposta
    {
        $pdo = BancoDeDados::pdo();
        try {
            $stmt = $pdo->query("SELECT id, hostname, ip_address, status, is_online, last_check_at, last_error FROM servers ORDER BY id DESC");
            $servers = $stmt->fetchAll();
        } catch (\Throwable $e) {
            $stmt = $pdo->query("SELECT id, hostname, ip_address, status FROM servers ORDER BY id DESC");
            $servers = $stmt->fetchAll();
        }

        $html = View::renderizar(__DIR__ . '/../../Views/equipe/terminal.php', [
            'servers' => is_array($servers) ? $servers : [],
        ]);

        return Resposta::html($html);
    }

    public function emitirToken(Requisicao $req): Resposta
    {
        $equipeId = Auth::equipeId();
        if ($equipeId === null) {
            return Resposta::json(['ok' => false, 'erro' => 'Não autenticado.'], 401);
        }

        $in = $req->input();
        $serverId = $in->postInt('server_id', 1, 2147483647, true);
        if ($in->temErros() || $serverId <= 0) {
            return Resposta::json(['ok' => false, 'erro' => 'Servidor inválido.'], 400);
        }

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT id, status FROM servers WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $serverId]);
        $s = $stmt->fetch();

        if (!is_array($s)) {
            return Resposta::json(['ok' => false, 'erro' => 'Servidor não encontrado.'], 404);
        }

        $st = (string) ($s['status'] ?? '');
        if ($st !== 'active') {
            return Resposta::json(['ok' => false, 'erro' => 'Servidor não está ativo.'], 400);
        }

        $ip = '';
        $xff = trim((string) ($req->headers['x-forwarded-for'] ?? ''));
        if ($xff !== '') {
            $partes = array_map('trim', explode(',', $xff));
            $ip = (string) ($partes[0] ?? '');
        }
        if ($ip === '') {
            $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
        }

        $ua = trim((string) ($req->headers['user-agent'] ?? ''));
        if ($ua === '') {
            $ua = (string) ($_SERVER['HTTP_USER_AGENT'] ?? '');
        }

        try {
            $svc = new TerminalTokensService();
            $token = $svc->criarToken($equipeId, $serverId, $ip, $ua);

            (new AuditLogService())->registrar(
                'team',
                $equipeId,
                'terminal.token_issue',
                'server',
                $serverId,
                ['server_id' => $serverId],
                $req,
            );

            return Resposta::json([
                'ok' => true,
                'token' => $token,
            ]);
        } catch (\Throwable $e) {
            return Resposta::json(['ok' => false, 'erro' => 'Não foi possível emitir o token.'], 500);
        }
    }

    /**
     * Executa um comando via SSH (AJAX) e retorna o output.
     * Funciona sem WebSocket — alternativa HTTP pura.
     */
    public function exec(Requisicao $req): Resposta
    {
        $equipeId = Auth::equipeId();
        if ($equipeId === null) {
            return Resposta::json(['ok' => false, 'erro' => 'Não autenticado.'], 401);
        }

        $in = $req->input();
        $serverId = $in->postInt('server_id', 1, 2147483647, true);
        $comando = trim((string) ($req->post['command'] ?? ''));

        if ($in->temErros() || $serverId <= 0) {
            return Resposta::json(['ok' => false, 'erro' => 'Servidor inválido.'], 400);
        }
        if ($comando === '') {
            return Resposta::json(['ok' => false, 'erro' => 'Comando vazio.'], 400);
        }
        if (strlen($comando) > 4096) {
            return Resposta::json(['ok' => false, 'erro' => 'Comando muito longo.'], 400);
        }

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare(
            'SELECT id, ip_address, ssh_port, ssh_user, ssh_auth_type, ssh_key_id, ssh_password, use_sudo, status FROM servers WHERE id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $serverId]);
        $server = $stmt->fetch();

        if (!is_array($server) || (string) ($server['status'] ?? '') !== 'active') {
            return Resposta::json(['ok' => false, 'erro' => 'Servidor não encontrado ou inativo.'], 404);
        }

        $ip = (string) ($server['ip_address'] ?? '');
        $porta = (int) ($server['ssh_port'] ?? 22);
        $usuario = (string) ($server['ssh_user'] ?? 'root');
        $authType = (string) ($server['ssh_auth_type'] ?? 'key');
        $useSudo = (int) ($server['use_sudo'] ?? 0);

        $ssh = new SshExecutor();

        // Elevar com sudo se necessário
        $cmdRemoto = $comando;
        if ($useSudo === 1 && $usuario !== 'root') {
            if ($authType === 'password') {
                $senhaPlain = SshCrypto::decifrar((string) ($server['ssh_password'] ?? ''));
                $cmdRemoto = SshExecutor::elevarComSudo($comando, $senhaPlain);
            } else {
                $cmdRemoto = SshExecutor::elevarComSudo($comando);
            }
        }

        try {
            if ($authType === 'password') {
                $senhaPlain = SshCrypto::decifrar((string) ($server['ssh_password'] ?? ''));
                if ($senhaPlain === '') {
                    return Resposta::json(['ok' => false, 'erro' => 'Senha SSH não configurada ou falha ao decifrar.'], 500);
                }
                $result = $ssh->executarComSenha($ip, $porta, $usuario, $senhaPlain, $cmdRemoto, 30);
            } else {
                $keyDir = rtrim(ConfiguracoesSistema::sshKeyDir(), "/\\");
                $keyPath = $keyDir . DIRECTORY_SEPARATOR . (string) ($server['ssh_key_id'] ?? '');
                if (!is_file($keyPath)) {
                    return Resposta::json(['ok' => false, 'erro' => 'Chave SSH não encontrada.'], 500);
                }
                $result = $ssh->executar($ip, $porta, $usuario, $keyPath, $cmdRemoto, 30);
            }
        } catch (\Throwable $e) {
            return Resposta::json(['ok' => false, 'erro' => $e->getMessage()], 500);
        }

        // Auditoria
        try {
            (new AuditLogService())->registrar(
                'team', $equipeId, 'terminal.exec', 'server', $serverId,
                ['command' => $comando, 'exit_code' => $result['codigo'] ?? -1],
                $req,
            );
        } catch (\Throwable $e) {
            // silencioso
        }

        // Salvar comando na tabela de auditoria do terminal
        try {
            $pdo->prepare(
                'INSERT INTO terminal_session_commands (session_id, command, created_at) VALUES (0, :cmd, NOW())'
            )->execute([':cmd' => mb_substr($comando, 0, 2000)]);
        } catch (\Throwable $e) {
            // tabela pode não existir — silencioso
        }

        return Resposta::json([
            'ok' => true,
            'output' => (string) ($result['saida'] ?? ''),
            'exit_code' => (int) ($result['codigo'] ?? -1),
        ]);
    }

    public function auditoria(Requisicao $req): Resposta
    {
        $sessoes = [];
        try {
            $pdo = BancoDeDados::pdo();
            $stmt = $pdo->query(
                'SELECT ts.id, ts.session_uid, ts.started_at, ts.ended_at, ts.ip, ts.user_agent, u.name AS user_name, s.hostname AS server_hostname, s.ip_address AS server_ip '
                . 'FROM terminal_sessions ts '
                . 'JOIN users u ON u.id = ts.equipe_id '
                . 'JOIN servers s ON s.id = ts.server_id '
                . 'ORDER BY ts.id DESC LIMIT 200'
            );
            $sessoes = $stmt->fetchAll();
        } catch (\Throwable $e) {
            $sessoes = [];
        }

        $html = View::renderizar(__DIR__ . '/../../Views/equipe/terminal-auditoria.php', [
            'sessoes' => is_array($sessoes) ? $sessoes : [],
        ]);

        return Resposta::html($html);
    }

    public function auditoriaVer(Requisicao $req): Resposta
    {
        $in = $req->input();
        $id = $in->queryInt('id', 1, 2147483647, true);
        if ($in->temErros() || $id <= 0) {
            return Resposta::texto('Sessão inválida.', 400);
        }

        try {
            $pdo = BancoDeDados::pdo();
            $stmt = $pdo->prepare(
                'SELECT ts.id, ts.session_uid, ts.started_at, ts.ended_at, ts.ip, ts.user_agent, u.name AS user_name, s.hostname AS server_hostname, s.ip_address AS server_ip '
                . 'FROM terminal_sessions ts '
                . 'JOIN users u ON u.id = ts.equipe_id '
                . 'JOIN servers s ON s.id = ts.server_id '
                . 'WHERE ts.id = :id LIMIT 1'
            );
            $stmt->execute([':id' => $id]);
            $sessao = $stmt->fetch();
        } catch (\Throwable $e) {
            $sessao = null;
        }

        if (!is_array($sessao)) {
            return Resposta::texto('Sessão não encontrada.', 404);
        }

        $cmds = [];
        try {
            $stmt2 = $pdo->prepare('SELECT id, command, created_at FROM terminal_session_commands WHERE session_id = :id ORDER BY id ASC LIMIT 2000');
            $stmt2->execute([':id' => $id]);
            $cmds = $stmt2->fetchAll();
        } catch (\Throwable $e) {
            $cmds = [];
        }

        $html = View::renderizar(__DIR__ . '/../../Views/equipe/terminal-auditoria-ver.php', [
            'sessao' => $sessao,
            'comandos' => is_array($cmds) ? $cmds : [],
        ]);

        return Resposta::html($html);
    }

    public function upload(Requisicao $req): Resposta
    {
        $equipeId = Auth::equipeId();
        if ($equipeId === null) {
            return Resposta::json(['ok' => false, 'erro' => 'Não autenticado.'], 401);
        }

        $serverId = (int) ($req->post['server_id'] ?? 0);
        $remotePath = trim((string) ($req->post['remote_path'] ?? ''));

        if ($serverId <= 0 || $remotePath === '') {
            return Resposta::json(['ok' => false, 'erro' => 'Parâmetros inválidos.'], 400);
        }

        if (str_contains($remotePath, '..') || preg_match('/[;&|`$]/', $remotePath)) {
            return Resposta::json(['ok' => false, 'erro' => 'Caminho remoto inválido.'], 400);
        }

        $uploadedFile = $_FILES['file'] ?? null;
        if (!is_array($uploadedFile) || (int) ($uploadedFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return Resposta::json(['ok' => false, 'erro' => 'Arquivo não enviado.'], 400);
        }

        $size = (int) ($uploadedFile['size'] ?? 0);
        if ($size > 50 * 1024 * 1024) {
            return Resposta::json(['ok' => false, 'erro' => 'Arquivo muito grande (máx. 50 MB).'], 400);
        }

        $tmpPath = (string) ($uploadedFile['tmp_name'] ?? '');
        if (!is_file($tmpPath)) {
            return Resposta::json(['ok' => false, 'erro' => 'Arquivo temporário inválido.'], 400);
        }

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT id, ip_address, ssh_port, ssh_user, ssh_key_id, status FROM servers WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $serverId]);
        $server = $stmt->fetch();

        if (!is_array($server) || (string) ($server['status'] ?? '') !== 'active') {
            return Resposta::json(['ok' => false, 'erro' => 'Servidor não encontrado ou inativo.'], 404);
        }

        $keyDir = rtrim(ConfiguracoesSistema::sshKeyDir(), "/\\");
        $keyPath = $keyDir . DIRECTORY_SEPARATOR . (string) ($server['ssh_key_id'] ?? '');
        if (!is_file($keyPath)) {
            return Resposta::json(['ok' => false, 'erro' => 'Chave SSH não encontrada.'], 500);
        }

        try {
            $scp = new SshExecutor();
            $result = $scp->scpUpload(
                (string) $server['ip_address'],
                (int) ($server['ssh_port'] ?? 22),
                (string) $server['ssh_user'],
                $keyPath,
                $tmpPath,
                $remotePath,
            );

            (new AuditLogService())->registrar('team', $equipeId, 'terminal.scp_upload', 'server', $serverId, [
                'remote_path' => $remotePath,
                'size' => $size,
                'ok' => $result['ok'],
            ], $req);

            if (!$result['ok']) {
                return Resposta::json(['ok' => false, 'erro' => 'SCP falhou: ' . ($result['saida'] ?? '')], 500);
            }

            return Resposta::json(['ok' => true, 'mensagem' => 'Arquivo enviado para ' . $remotePath]);
        } catch (\Throwable $e) {
            return Resposta::json(['ok' => false, 'erro' => $e->getMessage()], 500);
        }
    }

    public function download(Requisicao $req): Resposta
    {
        $equipeId = Auth::equipeId();
        if ($equipeId === null) {
            return Resposta::texto('Não autenticado.', 401);
        }

        $serverId = (int) ($req->query['server_id'] ?? 0);
        $remotePath = trim((string) ($req->query['remote_path'] ?? ''));

        if ($serverId <= 0 || $remotePath === '') {
            return Resposta::texto('Parâmetros inválidos.', 400);
        }

        if (str_contains($remotePath, '..') || preg_match('/[;&|`$]/', $remotePath)) {
            return Resposta::texto('Caminho remoto inválido.', 400);
        }

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT id, ip_address, ssh_port, ssh_user, ssh_key_id, status FROM servers WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $serverId]);
        $server = $stmt->fetch();

        if (!is_array($server) || (string) ($server['status'] ?? '') !== 'active') {
            return Resposta::texto('Servidor não encontrado ou inativo.', 404);
        }

        $keyDir = rtrim(ConfiguracoesSistema::sshKeyDir(), "/\\");
        $keyPath = $keyDir . DIRECTORY_SEPARATOR . (string) ($server['ssh_key_id'] ?? '');
        if (!is_file($keyPath)) {
            return Resposta::texto('Chave SSH não encontrada.', 500);
        }

        try {
            $scp = new SshExecutor();
            $result = $scp->scpDownload(
                (string) $server['ip_address'],
                (int) ($server['ssh_port'] ?? 22),
                (string) $server['ssh_user'],
                $keyPath,
                $remotePath,
            );

            (new AuditLogService())->registrar('team', $equipeId, 'terminal.scp_download', 'server', $serverId, [
                'remote_path' => $remotePath,
                'ok' => $result['ok'],
            ], $req);

            if (!$result['ok']) {
                return Resposta::texto('SCP falhou: ' . ($result['saida'] ?? ''), 500);
            }

            $localPath = (string) ($result['local_path'] ?? '');
            if (!is_file($localPath)) {
                return Resposta::texto('Arquivo não encontrado após download.', 500);
            }

            $filename = basename($remotePath);
            register_shutdown_function(static function() use ($localPath): void { @unlink($localPath); });

            return Resposta::arquivo($localPath, $filename);
        } catch (\Throwable $e) {
            return Resposta::texto($e->getMessage(), 500);
        }
    }
}
