<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Equipe;

use LRV\App\Services\Audit\AuditLogService;
use LRV\Core\Auth;
use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\Totp;
use LRV\Core\View;

final class DoisFatoresController
{
    public function configurar(Requisicao $req): Resposta
    {
        $equipeId = Auth::equipeId();
        if ($equipeId === null) {
            return Resposta::redirecionar('/equipe/entrar');
        }

        $pdo = BancoDeDados::pdo();
        $stmtUser = $pdo->prepare('SELECT name, email FROM users WHERE id = :id LIMIT 1');
        $stmtUser->execute([':id' => $equipeId]);
        $user = $stmtUser->fetch();

        if (!is_array($user)) {
            return Resposta::texto('Usuário não encontrado.', 404);
        }

        $stmt = $pdo->prepare('SELECT secret, enabled FROM user_totp WHERE user_id = :id LIMIT 1');
        $stmt->execute([':id' => $equipeId]);
        $totp = $stmt->fetch();

        $secret = '';
        $enabled = false;

        if (is_array($totp)) {
            $secret = (string) ($totp['secret'] ?? '');
            $enabled = (bool) ($totp['enabled'] ?? false);
        }

        if ($secret === '') {
            $secret = Totp::gerarSecret();
            $agora = date('Y-m-d H:i:s');
            $ins = $pdo->prepare('INSERT INTO user_totp (user_id, secret, enabled, created_at, updated_at) VALUES (:u,:s,0,:c,:c) ON DUPLICATE KEY UPDATE secret = IF(enabled=0, :s2, secret), updated_at = :c2');
            $ins->execute([':u' => $equipeId, ':s' => $secret, ':s2' => $secret, ':c' => $agora, ':c2' => $agora]);
        }

        $email = (string) ($user['email'] ?? '');
        $qrUrl = Totp::gerarQrCodeUrl($secret, $email);

        $html = View::renderizar(__DIR__ . '/../../Views/equipe/2fa-configurar.php', [
            'secret' => $secret,
            'qr_url' => $qrUrl,
            'enabled' => $enabled,
            'erro' => '',
            'ok' => '',
        ]);

        return Resposta::html($html);
    }

    public function ativar(Requisicao $req): Resposta
    {
        $equipeId = Auth::equipeId();
        if ($equipeId === null) {
            return Resposta::redirecionar('/equipe/entrar');
        }

        $codigo = trim((string) ($req->post['codigo'] ?? ''));

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT secret, enabled FROM user_totp WHERE user_id = :id LIMIT 1');
        $stmt->execute([':id' => $equipeId]);
        $totp = $stmt->fetch();

        if (!is_array($totp)) {
            return Resposta::redirecionar('/equipe/2fa/configurar');
        }

        $secret = (string) ($totp['secret'] ?? '');

        if (!Totp::verificar($secret, $codigo)) {
            $stmtUser = $pdo->prepare('SELECT name, email FROM users WHERE id = :id LIMIT 1');
            $stmtUser->execute([':id' => $equipeId]);
            $user = $stmtUser->fetch();
            $email = is_array($user) ? (string) ($user['email'] ?? '') : '';
            $qrUrl = Totp::gerarQrCodeUrl($secret, $email);

            $html = View::renderizar(__DIR__ . '/../../Views/equipe/2fa-configurar.php', [
                'secret' => $secret,
                'qr_url' => $qrUrl,
                'enabled' => false,
                'erro' => 'Código inválido. Verifique o horário do dispositivo e tente novamente.',
                'ok' => '',
            ]);
            return Resposta::html($html, 422);
        }

        $up = $pdo->prepare('UPDATE user_totp SET enabled = 1, updated_at = :u WHERE user_id = :id');
        $up->execute([':u' => date('Y-m-d H:i:s'), ':id' => $equipeId]);

        (new AuditLogService())->registrar('team', $equipeId, '2fa.enabled', 'user', $equipeId, [], $req);

        $stmtUser = $pdo->prepare('SELECT name, email FROM users WHERE id = :id LIMIT 1');
        $stmtUser->execute([':id' => $equipeId]);
        $user = $stmtUser->fetch();
        $email = is_array($user) ? (string) ($user['email'] ?? '') : '';
        $qrUrl = Totp::gerarQrCodeUrl($secret, $email);

        $html = View::renderizar(__DIR__ . '/../../Views/equipe/2fa-configurar.php', [
            'secret' => $secret,
            'qr_url' => $qrUrl,
            'enabled' => true,
            'erro' => '',
            'ok' => '2FA ativado com sucesso.',
        ]);
        return Resposta::html($html);
    }

    public function desativar(Requisicao $req): Resposta
    {
        $equipeId = Auth::equipeId();
        if ($equipeId === null) {
            return Resposta::redirecionar('/equipe/entrar');
        }

        $codigo = trim((string) ($req->post['codigo'] ?? ''));

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT secret FROM user_totp WHERE user_id = :id AND enabled = 1 LIMIT 1');
        $stmt->execute([':id' => $equipeId]);
        $totp = $stmt->fetch();

        if (!is_array($totp) || !Totp::verificar((string) ($totp['secret'] ?? ''), $codigo)) {
            return Resposta::redirecionar('/equipe/2fa/configurar');
        }

        $up = $pdo->prepare('UPDATE user_totp SET enabled = 0, updated_at = :u WHERE user_id = :id');
        $up->execute([':u' => date('Y-m-d H:i:s'), ':id' => $equipeId]);

        (new AuditLogService())->registrar('team', $equipeId, '2fa.disabled', 'user', $equipeId, [], $req);

        return Resposta::redirecionar('/equipe/2fa/configurar');
    }

    public function formularioVerificar(Requisicao $req): Resposta
    {
        $html = View::renderizar(__DIR__ . '/../../Views/equipe/2fa-verificar.php', ['erro' => '']);
        return Resposta::html($html);
    }

    public function verificar(Requisicao $req): Resposta
    {
        $pendingId = (int) ($_SESSION['2fa_pending_equipe_id'] ?? 0);
        if ($pendingId <= 0) {
            return Resposta::redirecionar('/equipe/entrar');
        }

        $codigo = trim((string) ($req->post['codigo'] ?? ''));

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT secret FROM user_totp WHERE user_id = :id AND enabled = 1 LIMIT 1');
        $stmt->execute([':id' => $pendingId]);
        $totp = $stmt->fetch();

        if (!is_array($totp) || !Totp::verificar((string) ($totp['secret'] ?? ''), $codigo)) {
            $this->registrarAuthLog('team', $pendingId, '2fa_failed', $req);
            $html = View::renderizar(__DIR__ . '/../../Views/equipe/2fa-verificar.php', ['erro' => 'Código inválido.']);
            return Resposta::html($html, 422);
        }

        unset($_SESSION['2fa_pending_equipe_id']);
        $_SESSION['auth_equipe_id'] = $pendingId;

        $this->registrarAuthLog('team', $pendingId, 'login', $req);

        return Resposta::redirecionar('/equipe/painel');
    }

    private function registrarAuthLog(string $tipo, int $id, string $acao, Requisicao $req): void
    {
        try {
            $ip = trim((string) ($req->headers['x-forwarded-for'] ?? ''));
            if ($ip !== '') {
                $partes = array_map('trim', explode(',', $ip));
                $ip = (string) ($partes[0] ?? '');
            }
            if ($ip === '') {
                $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
            }
            $ua = trim((string) ($req->headers['user-agent'] ?? ''));
            $pdo = BancoDeDados::pdo();
            $ins = $pdo->prepare('INSERT INTO auth_logs (actor_type, actor_id, action, ip_address, user_agent, created_at) VALUES (:t,:i,:a,:ip,:ua,:c)');
            $ins->execute([':t' => $tipo, ':i' => $id, ':a' => $acao, ':ip' => $ip ?: null, ':ua' => $ua ?: null, ':c' => date('Y-m-d H:i:s')]);
        } catch (\Throwable $e) {
        }
    }
}
