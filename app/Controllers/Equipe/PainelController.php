<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Equipe;

use LRV\Core\Auth;
use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;

final class PainelController
{
    public function index(Requisicao $req): Resposta
    {
        $id = Auth::equipeId();
        if ($id === null) {
            return Resposta::redirecionar('/equipe/entrar');
        }

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT name, email, role FROM users WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $u = $stmt->fetch();

        $html = View::renderizar(__DIR__ . '/../../Views/equipe/painel.php', [
            'usuario' => is_array($u) ? $u : ['name' => 'Usuário', 'email' => '', 'role' => ''],
        ]);

        return Resposta::html($html);
    }
}
