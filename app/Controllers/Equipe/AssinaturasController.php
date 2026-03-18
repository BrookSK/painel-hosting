<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Equipe;

use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;

final class AssinaturasController
{
    public function listar(Requisicao $req): Resposta
    {
        $pdo = BancoDeDados::pdo();

        $erro = '';

        $sql = "SELECT s.id, s.client_id, s.vps_id, s.plan_id, s.asaas_subscription_id, s.status, s.next_due_date, s.created_at,
                       c.name AS client_name, c.email AS client_email,
                       p.name AS plan_name, p.price_monthly AS plan_price
                FROM subscriptions s
                INNER JOIN clients c ON c.id = s.client_id
                LEFT JOIN plans p ON p.id = s.plan_id
                ORDER BY s.id DESC
                LIMIT 300";

        try {
            $stmt = $pdo->query($sql);
            $assinaturas = $stmt->fetchAll();
        } catch (\Throwable $e) {
            $assinaturas = [];
            $erro = 'Não foi possível carregar assinaturas. Verifique se as migrations foram executadas.';
        }

        $html = View::renderizar(__DIR__ . '/../../Views/equipe/assinaturas-listar.php', [
            'assinaturas' => is_array($assinaturas) ? $assinaturas : [],
            'erro' => $erro,
        ]);

        return Resposta::html($html);
    }
}
