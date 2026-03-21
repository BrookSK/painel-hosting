<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Cliente;

use LRV\Core\Auth;
use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;

final class PlanosController
{
    public function listar(Requisicao $req): Resposta
    {
        $pdo = BancoDeDados::pdo();

        $erro = '';
        try {
            try {
                $stmt = $pdo->query("SELECT id, name, description, cpu, ram, storage, price_monthly, stripe_price_id FROM plans WHERE status = 'active' ORDER BY price_monthly ASC");
                $planos = $stmt->fetchAll();
            } catch (\Throwable $e) {
                $stmt = $pdo->query("SELECT id, name, description, cpu, ram, storage, price_monthly FROM plans WHERE status = 'active' ORDER BY price_monthly ASC");
                $planos = $stmt->fetchAll();
            }
        } catch (\Throwable $e) {
            $planos = [];
            $erro = 'Não foi possível carregar os planos. Verifique se as migrations foram executadas.';
        }

        $clienteId = Auth::clienteId() ?? 0;
        $cliente = [];
        if ($clienteId > 0) {
            $s = BancoDeDados::pdo()->prepare('SELECT name, email FROM clients WHERE id = ?');
            $s->execute([$clienteId]);
            $cliente = $s->fetch() ?: [];
        }

        $html = View::renderizar(__DIR__ . '/../../Views/cliente/planos.php', [
            'planos'  => is_array($planos) ? $planos : [],
            'erro'    => $erro,
            'cliente' => $cliente,
        ]);

        return Resposta::html($html);
    }
}
