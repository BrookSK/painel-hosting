<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Cliente;

use LRV\Core\Auth;
use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;

final class AplicacoesController
{
    public function listar(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) {
            return Resposta::redirecionar('/cliente/entrar');
        }

        $pdo = BancoDeDados::pdo();

        $sql = "SELECT a.id, a.vps_id, a.type, a.domain, a.port, a.status, a.repository, a.created_at
                FROM applications a
                INNER JOIN vps v ON v.id = a.vps_id
                WHERE v.client_id = :c
                ORDER BY a.id DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':c' => $clienteId]);
        $aplicacoes = $stmt->fetchAll();

        $html = View::renderizar(__DIR__ . '/../../Views/cliente/aplicacoes-listar.php', [
            'aplicacoes' => is_array($aplicacoes) ? $aplicacoes : [],
        ]);

        return Resposta::html($html);
    }
}
