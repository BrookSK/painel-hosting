<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Cliente;

use LRV\Core\Auth;
use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;

final class PainelController
{
    public function index(Requisicao $req): Resposta
    {
        $id = Auth::clienteId();
        if ($id === null) {
            return Resposta::redirecionar('/cliente/entrar');
        }

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT name, email FROM clients WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $c = $stmt->fetch();

        // Notificações não lidas
        $notifs = [];
        try {
            $stmtN = $pdo->prepare(
                'SELECT id, type, title, body, created_at FROM client_notifications
                 WHERE client_id = :c AND read_at IS NULL
                 ORDER BY id DESC LIMIT 10'
            );
            $stmtN->execute([':c' => $id]);
            $notifs = $stmtN->fetchAll() ?: [];
        } catch (\Throwable $e) {
        }

        $html = View::renderizar(__DIR__ . '/../../Views/cliente/painel.php', [
            'cliente'       => is_array($c) ? $c : ['name' => 'Cliente', 'email' => ''],
            'notificacoes'  => $notifs,
        ]);

        return Resposta::html($html);
    }
}
