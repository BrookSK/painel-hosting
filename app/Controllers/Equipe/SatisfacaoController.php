<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Equipe;

use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;

final class SatisfacaoController
{
    public function index(Requisicao $req): Resposta
    {
        $pdo = BancoDeDados::pdo();

        // Totais gerais
        $totais = $pdo->query(
            "SELECT
                COUNT(*) AS total,
                ROUND(AVG(rating), 2) AS media,
                SUM(rating = 5) AS cinco,
                SUM(rating = 4) AS quatro,
                SUM(rating = 3) AS tres,
                SUM(rating = 2) AS dois,
                SUM(rating = 1) AS um
             FROM satisfaction_surveys"
        )->fetch();

        // Por tipo
        $porTipo = $pdo->query(
            "SELECT type, COUNT(*) AS total, ROUND(AVG(rating), 2) AS media
             FROM satisfaction_surveys
             GROUP BY type"
        )->fetchAll();

        // Por agente
        $porAgente = $pdo->query(
            "SELECT u.name, COUNT(*) AS total, ROUND(AVG(s.rating), 2) AS media,
                    SUM(s.rating >= 4) AS positivas
             FROM satisfaction_surveys s
             INNER JOIN users u ON u.id = s.agent_id
             WHERE s.agent_id IS NOT NULL
             GROUP BY s.agent_id
             ORDER BY media DESC
             LIMIT 20"
        )->fetchAll();

        // Últimas avaliações
        $recentes = $pdo->query(
            "SELECT s.id, s.type, s.reference_id, s.rating, s.comment, s.created_at,
                    c.name AS client_name, u.name AS agent_name
             FROM satisfaction_surveys s
             INNER JOIN clients c ON c.id = s.client_id
             LEFT JOIN users u ON u.id = s.agent_id
             ORDER BY s.id DESC
             LIMIT 50"
        )->fetchAll();

        $html = View::renderizar(__DIR__ . '/../../Views/equipe/satisfacao.php', [
            'totais'    => is_array($totais)    ? $totais    : [],
            'porTipo'   => is_array($porTipo)   ? $porTipo   : [],
            'porAgente' => is_array($porAgente) ? $porAgente : [],
            'recentes'  => is_array($recentes)  ? $recentes  : [],
        ]);

        return Resposta::html($html);
    }
}
