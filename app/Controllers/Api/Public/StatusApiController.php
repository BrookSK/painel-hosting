<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Api\Public;

use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;

/**
 * Endpoints públicos de Status da API.
 * - GET /api/v1/status            → Status atual dos serviços
 * - GET /api/v1/status/incidents  → Histórico de incidentes
 */
final class StatusApiController extends BaseApiController
{
    /**
     * GET /api/v1/status
     */
    public function index(Requisicao $req): Resposta
    {
        $pdo = BancoDeDados::pdo();

        $stmt = $pdo->query(
            "SELECT id, `key`, name, description, status, last_check_at, last_ok_at
             FROM status_services WHERE scope = 'public' ORDER BY name ASC"
        );
        $services = $stmt->fetchAll() ?: [];

        // Calcular status geral
        $allOperational = true;
        foreach ($services as $s) {
            if (($s['status'] ?? 'operational') !== 'operational') {
                $allOperational = false;
                break;
            }
        }

        return $this->sucesso([
            'overall' => $allOperational ? 'operational' : 'degraded',
            'services' => $services,
            'checked_at' => date('c'),
        ]);
    }

    /**
     * GET /api/v1/status/incidents
     */
    public function incidentes(Requisicao $req): Resposta
    {
        $pag = $this->paginacao($req, 50);
        $pdo = BancoDeDados::pdo();

        $countStmt = $pdo->query("SELECT COUNT(*) AS total FROM status_incidents WHERE scope = 'public'");
        $total = (int) ($countStmt->fetch()['total'] ?? 0);

        $offset = ($pag['page'] - 1) * $pag['per_page'];
        $stmt = $pdo->prepare(
            "SELECT id, title, status, impact, message, started_at, resolved_at, created_at
             FROM status_incidents WHERE scope = 'public'
             ORDER BY started_at DESC LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':limit', $pag['per_page'], \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll() ?: [];

        $meta = [
            'current_page' => $pag['page'],
            'per_page' => $pag['per_page'],
            'total' => $total,
            'last_page' => (int) ceil($total / $pag['per_page']),
        ];

        return $this->paginado($items, $meta, '/api/v1/status/incidents');
    }
}
