<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Cliente;

use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\Settings;
use LRV\Core\View;

final class ResetSenhaController
{
    private const TIPO   = 'cliente';
    private const TABELA = 'clients';
    private const EXPIRA = 3600;

    public function formulario(Requisicao $req): Resposta
    {
        return $this->render('solicitar', '', '');
    }

    public function solicitar(Requisicao $req): Resposta
    {
        $email = trim(strtolower((string)($req->post['email'] ?? '')));

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->render('solicitar', '', 'E-mail inválido.');
        }

        $pdo = BancoDeDados::pdo();
        $s   = $pdo->prepare('SELECT id FROM ' . self::TABELA . ' WHERE email = :e LIMIT 1');
        $s->execute([':e' => $email]);
        $user = $s->fetch();

        if (is_array($user)) {
            $token   = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', time() + self::EXPIRA);

            $pdo->prepare('DELETE FROM password_resets WHERE tipo = :t AND user_id = :u')
                ->execute([':t' => self::TIPO, ':u' => (int)$user['id']]);

            $pdo->prepare('INSERT INTO password_resets (tipo, user_id, token, expires_at) VALUES (:t, :u, :tk, :e)')
                ->execute([':t' => self::TIPO, ':u' => (int)$user['id'], ':tk' => $token, ':e' => $expires]);

            $this->enviarEmail($email, $token);
        }

        return $this->render('solicitar', 'Se este e-mail estiver cadastrado, você receberá as instruções em breve.', '');
    }

    public function formularioNovaSenha(Requisicao $req): Resposta
    {
        $token = trim((string)($req->get['token'] ?? ''));
        if (!$this->tokenValido($token)) {
            return $this->render('solicitar', '', 'Link inválido ou expirado. Solicite um novo.');
        }
        return $this->render('nova-senha', '', '', $token);
    }

    public function salvar(Requisicao $req): Resposta
    {
        $token = trim((string)($req->post['token'] ?? ''));
        $nova  = (string)($req->post['nova_senha'] ?? '');
        $conf  = (string)($req->post['confirmar_senha'] ?? '');

        if (!$this->tokenValido($token)) {
            return $this->render('solicitar', '', 'Link inválido ou expirado. Solicite um novo.');
        }

        if (strlen($nova) < 8) {
            return $this->render('nova-senha', '', 'A senha deve ter ao menos 8 caracteres.', $token);
        }

        if ($nova !== $conf) {
            return $this->render('nova-senha', '', 'As senhas não coincidem.', $token);
        }

        $pdo = BancoDeDados::pdo();
        $s   = $pdo->prepare('SELECT user_id FROM password_resets WHERE token = :t AND tipo = :tp AND used_at IS NULL AND expires_at > NOW() LIMIT 1');
        $s->execute([':t' => $token, ':tp' => self::TIPO]);
        $row = $s->fetch();

        if (!is_array($row)) {
            return $this->render('solicitar', '', 'Link inválido ou expirado. Solicite um novo.');
        }

        $hash = password_hash($nova, PASSWORD_BCRYPT);
        $pdo->prepare('UPDATE ' . self::TABELA . ' SET password = :p WHERE id = :id')
            ->execute([':p' => $hash, ':id' => (int)$row['user_id']]);

        $pdo->prepare('UPDATE password_resets SET used_at = NOW() WHERE token = :t')
            ->execute([':t' => $token]);

        return $this->render('solicitar', 'Senha alterada com sucesso. Você já pode fazer login.', '');
    }

    private function tokenValido(string $token): bool
    {
        if (strlen($token) !== 64) return false;
        $pdo = BancoDeDados::pdo();
        $s   = $pdo->prepare('SELECT id FROM password_resets WHERE token = :t AND tipo = :tp AND used_at IS NULL AND expires_at > NOW() LIMIT 1');
        $s->execute([':t' => $token, ':tp' => self::TIPO]);
        return (bool)$s->fetch();
    }

    private function enviarEmail(string $email, string $token): void
    {
        try {
            $base    = rtrim((string)Settings::obter('app_url', ''), '/');
            $link    = $base . '/cliente/reset-senha/nova?token=' . $token;
            $assunto = 'Redefinição de senha';
            $corpo   = "Você solicitou a redefinição de senha.\n\nClique no link abaixo (válido por 1 hora):\n{$link}\n\nSe não foi você, ignore este e-mail.";
            $headers = "From: noreply@{$_SERVER['HTTP_HOST']}\r\nContent-Type: text/plain; charset=utf-8";
            @mail($email, $assunto, $corpo, $headers);
        } catch (\Throwable) {}
    }

    private function render(string $etapa, string $ok, string $erro, string $token = ''): Resposta
    {
        return Resposta::html(View::renderizar(
            __DIR__ . '/../../Views/cliente/reset-senha.php',
            ['etapa' => $etapa, 'ok' => $ok, 'erro' => $erro, 'token' => $token]
        ));
    }
}
