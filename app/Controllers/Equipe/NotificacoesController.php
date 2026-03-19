<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Equipe;

use LRV\Core\Auth;
use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;

final class NotificacoesController
{
    public function listar(Requisicao $req): Resposta
    {
        $equipeId = Auth::equipeId();
        if ($equipeId === null) {
            return Resposta::redirecionar('/equipe/entrar');
        }

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT id, message, `read`, created_at FROM notifications WHERE user_id = :u ORDER BY id DESC LIMIT 200');
        $stmt->execute([':u' => $equipeId]);
        $notificacoes = $stmt->fetchAll();

        $html = View::renderizar(__DIR__ . '/../../Views/equipe/notificacoes-listar.php', [
            'notificacoes' => is_array($notificacoes) ? $notificacoes : [],
        ]);

        return Resposta::html($html);
    }

    public function marcarLida(Requisicao $req): Resposta
    {
        $equipeId = Auth::equipeId();
        if ($equipeId === null) {
            return Resposta::redirecionar('/equipe/entrar');
        }

        $id = (int) ($req->post['id'] ?? 0);
        if ($id <= 0) {
            return Resposta::texto('Notificação inválida.', 400);
        }

        $pdo = BancoDeDados::pdo();
        $up = $pdo->prepare('UPDATE notifications SET `read` = 1 WHERE id = :id AND user_id = :u');
        $up->execute([':id' => $id, ':u' => $equipeId]);

        return Resposta::redirecionar('/equipe/notificacoes');
    }

    public function marcarTodasLidas(Requisicao $req): Resposta
    {
        $equipeId = Auth::equipeId();
        if ($equipeId === null) {
            return Resposta::redirecionar('/equipe/entrar');
        }

        $pdo = BancoDeDados::pdo();
        $up = $pdo->prepare('UPDATE notifications SET `read` = 1 WHERE user_id = :u AND `read` = 0');
        $up->execute([':u' => $equipeId]);

        return Resposta::redirecionar('/equipe/notificacoes');
    }
}
