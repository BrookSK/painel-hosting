<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Cliente;

use LRV\Core\Auth;
use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;

final class VpsController
{
    public function listar(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) {
            return Resposta::redirecionar('/cliente/entrar');
        }

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare("SELECT id, server_id, container_id, cpu, ram, storage, status, created_at FROM vps WHERE client_id = :c AND status NOT IN ('expired','removed') ORDER BY id DESC");
        $stmt->execute([':c' => $clienteId]);
        $vps = $stmt->fetchAll();

        $html = View::renderizar(__DIR__ . '/../../Views/cliente/vps-listar.php', [
            'vps' => is_array($vps) ? $vps : [],
        ]);

        return Resposta::html($html);
    }
}
