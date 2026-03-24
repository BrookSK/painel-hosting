<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Equipe;

use LRV\Core\Auth;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;

final class EntrarController
{
    public function formulario(Requisicao $req): Resposta
    {
        if (Auth::equipeId() !== null) {
            return Resposta::redirecionar('/equipe/painel');
        }

        $html = View::renderizar(__DIR__ . '/../../Views/equipe/entrar.php', [
            'erro' => '',
            'email' => '',
        ]);

        return Resposta::html($html);
    }

    public function entrar(Requisicao $req): Resposta
    {
        $in = $req->input();
        $email = $in->postEmail('email', 190, true);
        $senha = $in->postStringRaw('senha', 255, true);

        if ($in->temErros() || $email === '' || $senha === '') {
            $html = View::renderizar(__DIR__ . '/../../Views/equipe/entrar.php', [
                'erro' => $in->temErros() ? $in->primeiroErro() : 'Preencha e-mail e senha.',
                'email' => $email,
            ]);
            return Resposta::html($html, 422);
        }

        // Verificar bloqueio de IP
        $ip = \LRV\Core\LoginBlocker::extrairIp();
        if (\LRV\Core\LoginBlocker::estaBloqueado($ip)) {
            $html = View::renderizar(__DIR__ . '/../../Views/equipe/entrar.php', [
                'erro' => 'Acesso temporariamente bloqueado por excesso de tentativas. Tente novamente em 30 minutos.',
                'email' => $email,
            ]);
            return Resposta::html($html, 429);
        }

        if (!Auth::entrarEquipe($email, $senha)) {
            \LRV\Core\LoginBlocker::registrarFalha($ip, 'team');
            $this->registrarAuthLog('team', $email, 'login_failed', $req);
            $html = View::renderizar(__DIR__ . '/../../Views/equipe/entrar.php', [
                'erro' => 'E-mail ou senha inválidos.',
                'email' => $email,
            ]);
            return Resposta::html($html, 401);
        }
        // Verificar se 2FA está ativo
        $equipeId = Auth::equipeId();
        if ($equipeId !== null) {
            $pdo = \LRV\Core\BancoDeDados::pdo();
            $stmt = $pdo->prepare('SELECT id FROM user_totp WHERE user_id = :id AND enabled = 1 LIMIT 1');
            $stmt->execute([':id' => $equipeId]);
            $totp = $stmt->fetch();

            if (is_array($totp)) {
                // Suspender sessão até verificar 2FA
                $_SESSION['2fa_pending_equipe_id'] = $equipeId;
                unset($_SESSION['auth_equipe_id']);
                return Resposta::redirecionar('/equipe/2fa/verificar');
            }

            $this->registrarAuthLog('team', $equipeId, 'login', $req);
        }

        return Resposta::redirecionar($this->urlRedirect('/equipe/painel'));
    }

    private function urlRedirect(string $fallback): string
    {
        $url = trim((string) ($_SESSION['redirect_after_login'] ?? ''));
        unset($_SESSION['redirect_after_login']);
        if ($url !== '' && str_starts_with($url, '/equipe/')) {
            return $url;
        }
        return $fallback;
    }

    private function registrarAuthLog(string $tipo, string|int $actor, string $acao, \LRV\Core\Http\Requisicao $req): void
    {
        try {
            if (!is_int($actor)) {
                return;
            }
            $ip = trim((string) ($req->headers['x-forwarded-for'] ?? ''));
            if ($ip !== '') {
                $partes = array_map('trim', explode(',', $ip));
                $ip = (string) ($partes[0] ?? '');
            }
            if ($ip === '') {
                $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
            }
            $ua = trim((string) ($req->headers['user-agent'] ?? ''));
            $pdo = \LRV\Core\BancoDeDados::pdo();
            $ins = $pdo->prepare('INSERT INTO auth_logs (actor_type, actor_id, action, ip_address, user_agent, created_at) VALUES (:t,:i,:a,:ip,:ua,:c)');
            $ins->execute([':t' => $tipo, ':i' => $actor, ':a' => $acao, ':ip' => $ip ?: null, ':ua' => $ua ?: null, ':c' => date('Y-m-d H:i:s')]);
        } catch (\Throwable $e) {
        }
    }
}
