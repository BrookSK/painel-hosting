<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Equipe;

use LRV\Core\Auth;
use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;

final class PrimeiroAcessoController
{
    public function formulario(Requisicao $req): Resposta
    {
        if (Auth::equipeExiste()) {
            return Resposta::texto('Não encontrado.', 404);
        }

        $html = View::renderizar(__DIR__ . '/../../Views/equipe/primeiro-acesso.php', [
            'erro' => '',
            'nome' => '',
            'email' => '',
        ]);

        return Resposta::html($html);
    }

    public function criar(Requisicao $req): Resposta
    {
        if (Auth::equipeExiste()) {
            return Resposta::texto('Não encontrado.', 404);
        }

        $nome = trim((string) ($req->post['nome'] ?? ''));
        $email = trim((string) ($req->post['email'] ?? ''));
        $senha = (string) ($req->post['senha'] ?? '');

        if ($nome === '' || $email === '' || $senha === '') {
            $html = View::renderizar(__DIR__ . '/../../Views/equipe/primeiro-acesso.php', [
                'erro' => 'Preencha nome, e-mail e senha.',
                'nome' => $nome,
                'email' => $email,
            ]);
            return Resposta::html($html, 422);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $html = View::renderizar(__DIR__ . '/../../Views/equipe/primeiro-acesso.php', [
                'erro' => 'E-mail inválido.',
                'nome' => $nome,
                'email' => $email,
            ]);
            return Resposta::html($html, 422);
        }

        $hash = password_hash($senha, PASSWORD_BCRYPT);
        $agora = date('Y-m-d H:i:s');

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role, status, created_at) VALUES (:n, :e, :p, :r, :s, :c)');

        try {
            $stmt->execute([
                ':n' => $nome,
                ':e' => $email,
                ':p' => $hash,
                ':r' => 'superadmin',
                ':s' => 'active',
                ':c' => $agora,
            ]);
        } catch (\Throwable $e) {
            $html = View::renderizar(__DIR__ . '/../../Views/equipe/primeiro-acesso.php', [
                'erro' => 'Não foi possível criar o usuário. Verifique se o e-mail já existe.',
                'nome' => $nome,
                'email' => $email,
            ]);
            return Resposta::html($html, 400);
        }

        Auth::entrarEquipe($email, $senha);
        return Resposta::redirecionar('/equipe/painel');
    }
}
