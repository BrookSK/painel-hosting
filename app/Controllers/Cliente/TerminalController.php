<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Cliente;

use LRV\App\Services\Terminal\ClientTerminalTokensService;
use LRV\Core\Auth;
use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;

final class TerminalController
{
    public function vps(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) {
            return Resposta::redirecionar('/cliente/entrar');
        }

        $vpsId = (int) ($req->query['id'] ?? 0);
        if ($vpsId <= 0) {
            return Resposta::texto('VPS inválida.', 400);
        }

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT id, server_id, container_id, status, cpu, ram, storage, created_at FROM vps WHERE id = :id AND client_id = :c LIMIT 1');
        $stmt->execute([':id' => $vpsId, ':c' => $clienteId]);
        $vps = $stmt->fetch();

        if (!is_array($vps)) {
            return Resposta::texto('VPS não encontrada.', 404);
        }

        $html = View::renderizar(__DIR__ . '/../../Views/cliente/vps-terminal.php', [
            'vps' => $vps,
            'erro' => '',
        ]);

        return Resposta::html($html);
    }

    public function emitirToken(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) {
            return Resposta::json(['ok' => false, 'erro' => 'Não autenticado.'], 401);
        }

        $in = $req->input();
        $vpsId = $in->postInt('vps_id', 1, 2147483647, true);
        if ($in->temErros() || $vpsId <= 0) {
            return Resposta::json(['ok' => false, 'erro' => 'VPS inválida.'], 400);
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
            $svc = new ClientTerminalTokensService();
            $token = $svc->criarToken($clienteId, $vpsId, $ip, $ua);

            return Resposta::json([
                'ok' => true,
                'token' => $token,
            ]);
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            if ($msg === '') {
                $msg = 'Não foi possível emitir o token.';
            }
            return Resposta::json(['ok' => false, 'erro' => $msg], 422);
        }
    }
}
