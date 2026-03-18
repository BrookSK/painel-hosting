<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Equipe;

use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;

final class AsaasEventosController
{
    public function listar(Requisicao $req): Resposta
    {
        $pdo = BancoDeDados::pdo();

        $erro = '';
        try {
            $stmt = $pdo->query('SELECT id, event_id, event_type, created_at FROM asaas_events ORDER BY id DESC LIMIT 300');
            $eventos = $stmt->fetchAll();
        } catch (\Throwable $e) {
            $eventos = [];
            $erro = 'Não foi possível carregar eventos. Verifique se as migrations foram executadas.';
        }

        $html = View::renderizar(__DIR__ . '/../../Views/equipe/asaas-eventos-listar.php', [
            'eventos' => is_array($eventos) ? $eventos : [],
            'erro' => $erro,
        ]);

        return Resposta::html($html);
    }
}
