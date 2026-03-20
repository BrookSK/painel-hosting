<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Equipe;

use LRV\App\Services\Audit\AuditLogService;
use LRV\App\Services\Terminal\TerminalTokensService;
use LRV\Core\Auth;
use LRV\Core\BancoDeDados;
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
}
