<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Equipe;

use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\Jobs\RepositorioJobs;
use LRV\Core\View;

final class VpsController
{
    public function listar(Requisicao $req): Resposta
    {
        $pdo = BancoDeDados::pdo();
        $sql = "SELECT v.id, v.client_id, v.server_id, v.container_id, v.cpu, v.ram, v.storage, v.status, v.created_at,
                       c.name AS client_name, c.email AS client_email
                FROM vps v
                INNER JOIN clients c ON c.id = v.client_id
                ORDER BY v.id DESC";
        $stmt = $pdo->query($sql);
        $vps = $stmt->fetchAll();

        $html = View::renderizar(__DIR__ . '/../../Views/equipe/vps-listar.php', [
            'vps' => is_array($vps) ? $vps : [],
        ]);

        return Resposta::html($html);
    }

    public function provisionar(Requisicao $req): Resposta
    {
        $vpsId = (int) ($req->post['vps_id'] ?? 0);
        if ($vpsId <= 0) {
            return Resposta::texto('VPS inválida.', 400);
        }

        $repo = new RepositorioJobs();
        $repo->criar('provisionar_vps', ['vps_id' => $vpsId]);

        return Resposta::redirecionar('/equipe/vps');
    }

    public function suspender(Requisicao $req): Resposta
    {
        $vpsId = (int) ($req->post['vps_id'] ?? 0);
        if ($vpsId <= 0) {
            return Resposta::texto('VPS inválida.', 400);
        }

        $repo = new RepositorioJobs();
        $repo->criar('suspender_vps', ['vps_id' => $vpsId]);

        return Resposta::redirecionar('/equipe/vps');
    }

    public function reativar(Requisicao $req): Resposta
    {
        $vpsId = (int) ($req->post['vps_id'] ?? 0);
        if ($vpsId <= 0) {
            return Resposta::texto('VPS inválida.', 400);
        }

        $repo = new RepositorioJobs();
        $repo->criar('reativar_vps', ['vps_id' => $vpsId]);
        $repo->criar('provisionar_vps', ['vps_id' => $vpsId]);

        return Resposta::redirecionar('/equipe/vps');
    }
}
