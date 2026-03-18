<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Cliente;

use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;

final class PlanosController
{
    public function listar(Requisicao $req): Resposta
    {
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->query("SELECT id, name, description, cpu, ram, storage, price_monthly FROM plans WHERE status = 'active' ORDER BY price_monthly ASC");
        $planos = $stmt->fetchAll();

        $html = View::renderizar(__DIR__ . '/../../Views/cliente/planos.php', [
            'planos' => is_array($planos) ? $planos : [],
        ]);

        return Resposta::html($html);
    }
}
