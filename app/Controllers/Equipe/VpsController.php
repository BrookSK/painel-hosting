<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Equipe;

use LRV\App\Services\Audit\AuditLogService;
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
                WHERE v.deleted_at IS NULL
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

        $pdo = \LRV\Core\BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT id FROM vps WHERE id = :id AND deleted_at IS NULL LIMIT 1');
        $stmt->execute([':id' => $vpsId]);
        if (!is_array($stmt->fetch())) {
            return Resposta::texto('VPS não encontrada.', 404);
        }

        // Marcar status imediatamente (job faz o provisionamento real)
        $pdo->prepare("UPDATE vps SET status = 'pending_provisioning' WHERE id = :id")
            ->execute([':id' => $vpsId]);

        $repo = new RepositorioJobs();
        $repo->criar('provisionar_vps', ['vps_id' => $vpsId]);

        (new AuditLogService())->registrar(
            'team',
            \LRV\Core\Auth::equipeId(),
            'vps.provision',
            'vps',
            $vpsId,
            ['vps_id' => $vpsId],
            $req,
        );

        return Resposta::redirecionar('/equipe/vps');
    }

    public function suspender(Requisicao $req): Resposta
    {
        $vpsId = (int) ($req->post['vps_id'] ?? 0);
        if ($vpsId <= 0) {
            return Resposta::texto('VPS inválida.', 400);
        }

        $pdo = \LRV\Core\BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT id FROM vps WHERE id = :id AND deleted_at IS NULL LIMIT 1');
        $stmt->execute([':id' => $vpsId]);
        if (!is_array($stmt->fetch())) {
            return Resposta::texto('VPS não encontrada.', 404);
        }

        // Marcar status imediatamente (job faz a suspensão no container)
        $pdo->prepare("UPDATE vps SET status = 'suspended_payment' WHERE id = :id")
            ->execute([':id' => $vpsId]);

        $repo = new RepositorioJobs();
        $repo->criar('suspender_vps', ['vps_id' => $vpsId]);

        (new AuditLogService())->registrar(
            'team',
            \LRV\Core\Auth::equipeId(),
            'vps.suspend',
            'vps',
            $vpsId,
            ['vps_id' => $vpsId],
            $req,
        );

        return Resposta::redirecionar('/equipe/vps');
    }

    public function reativar(Requisicao $req): Resposta
    {
        $vpsId = (int) ($req->post['vps_id'] ?? 0);
        if ($vpsId <= 0) {
            return Resposta::texto('VPS inválida.', 400);
        }

        $pdo = \LRV\Core\BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT id FROM vps WHERE id = :id AND deleted_at IS NULL LIMIT 1');
        $stmt->execute([':id' => $vpsId]);
        if (!is_array($stmt->fetch())) {
            return Resposta::texto('VPS não encontrada.', 404);
        }

        // Marcar status imediatamente (job faz a reativação no container)
        $pdo->prepare("UPDATE vps SET status = 'pending_provisioning' WHERE id = :id")
            ->execute([':id' => $vpsId]);

        $repo = new RepositorioJobs();
        $repo->criar('reativar_vps', ['vps_id' => $vpsId]);
        $repo->criar('provisionar_vps', ['vps_id' => $vpsId]);

        (new AuditLogService())->registrar(
            'team',
            \LRV\Core\Auth::equipeId(),
            'vps.reactivate',
            'vps',
            $vpsId,
            ['vps_id' => $vpsId],
            $req,
        );

        return Resposta::redirecionar('/equipe/vps');
    }

    public function reiniciar(Requisicao $req): Resposta
    {
        $vpsId = (int) ($req->post['vps_id'] ?? 0);
        if ($vpsId <= 0) {
            return Resposta::texto('VPS inválida.', 400);
        }

        $pdo = \LRV\Core\BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT id FROM vps WHERE id = :id AND deleted_at IS NULL LIMIT 1');
        $stmt->execute([':id' => $vpsId]);
        if (!is_array($stmt->fetch())) {
            return Resposta::texto('VPS não encontrada.', 404);
        }

        // Marcar status imediatamente (job faz o restart no container)
        $pdo->prepare("UPDATE vps SET status = 'provisioning' WHERE id = :id")
            ->execute([':id' => $vpsId]);

        $repo = new RepositorioJobs();
        $repo->criar('reiniciar_vps', ['vps_id' => $vpsId]);

        (new AuditLogService())->registrar(
            'team',
            \LRV\Core\Auth::equipeId(),
            'vps.restart',
            'vps',
            $vpsId,
            ['vps_id' => $vpsId],
            $req,
        );

        return Resposta::redirecionar('/equipe/vps');
    }

    public function remover(Requisicao $req): Resposta
    {
        $vpsId = (int) ($req->post['vps_id'] ?? 0);
        if ($vpsId <= 0) {
            return Resposta::texto('VPS inválida.', 400);
        }

        $pdo = \LRV\Core\BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT id, status FROM vps WHERE id = :id AND deleted_at IS NULL LIMIT 1');
        $stmt->execute([':id' => $vpsId]);
        $vps = $stmt->fetch();

        if (!is_array($vps)) {
            return Resposta::texto('VPS não encontrada.', 404);
        }

        $repo = new RepositorioJobs();
        $repo->criar('remover_vps', ['vps_id' => $vpsId]);

        // Marcar como removida imediatamente (job cuida da limpeza do container)
        $pdo->prepare('UPDATE vps SET status = :s, deleted_at = :d WHERE id = :id')
            ->execute([':s' => 'removed', ':d' => date('Y-m-d H:i:s'), ':id' => $vpsId]);

        (new AuditLogService())->registrar(
            'team',
            \LRV\Core\Auth::equipeId(),
            'vps.remove',
            'vps',
            $vpsId,
            ['vps_id' => $vpsId],
            $req,
        );

        return Resposta::redirecionar('/equipe/vps');
    }
}
