<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Cliente;

use LRV\Core\Auth;
use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;

final class CriarContaController
{
    public function formulario(Requisicao $req): Resposta
    {
        if (Auth::clienteId() !== null) {
            return Resposta::redirecionar('/cliente/painel');
        }

        $html = View::renderizar(__DIR__ . '/../../Views/cliente/criar-conta.php', [
            'erro' => '',
            'nome' => '',
            'email' => '',
        ]);

        return Resposta::html($html);
    }

    public function criar(Requisicao $req): Resposta
    {
        if (Auth::clienteId() !== null) {
            return Resposta::redirecionar('/cliente/painel');
        }

        $nome = trim((string) ($req->post['nome'] ?? ''));
        $email = trim((string) ($req->post['email'] ?? ''));
        $senha = (string) ($req->post['senha'] ?? '');

        if ($nome === '' || $email === '' || $senha === '') {
            $html = View::renderizar(__DIR__ . '/../../Views/cliente/criar-conta.php', [
                'erro' => 'Preencha nome, e-mail e senha.',
                'nome' => $nome,
                'email' => $email,
            ]);
            return Resposta::html($html, 422);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $html = View::renderizar(__DIR__ . '/../../Views/cliente/criar-conta.php', [
                'erro' => 'E-mail inválido.',
                'nome' => $nome,
                'email' => $email,
            ]);
            return Resposta::html($html, 422);
        }

        $hash = password_hash($senha, PASSWORD_BCRYPT);
        $agora = date('Y-m-d H:i:s');

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('INSERT INTO clients (name, email, password, created_at) VALUES (:n, :e, :p, :c)');

        try {
            $stmt->execute([
                ':n' => $nome,
                ':e' => $email,
                ':p' => $hash,
                ':c' => $agora,
            ]);
        } catch (\Throwable $e) {
            $html = View::renderizar(__DIR__ . '/../../Views/cliente/criar-conta.php', [
                'erro' => 'Não foi possível criar a conta. Verifique se o e-mail já existe.',
                'nome' => $nome,
                'email' => $email,
            ]);
            return Resposta::html($html, 400);
        }

        Auth::entrarCliente($email, $senha);
        return Resposta::redirecionar('/cliente/painel');
    }
}
