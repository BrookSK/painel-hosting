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
        $stmt = $pdo->prepare('SELECT id, status FROM vps WHERE id = :id AND deleted_at IS NULL LIMIT 1');
        $stmt->execute([':id' => $vpsId]);
        $vps = $stmt->fetch();
        if (!is_array($vps)) {
            return Resposta::texto('VPS não encontrada.', 404);
        }

        // Marcar status imediatamente
        $pdo->prepare("UPDATE vps SET status = 'provisioning' WHERE id = :id")
            ->execute([':id' => $vpsId]);

        // Enfileirar job
        $repo = new RepositorioJobs();
        $repo->criar('provisionar_vps', ['vps_id' => $vpsId]);

        // Tentar executar o provisionamento direto (sem esperar worker)
        try {
            $svc = new \LRV\App\Services\Provisioning\VpsProvisioningService(
                new \LRV\App\Services\Provisioning\DockerCli()
            );
            $svc->provisionar($vpsId, function(string $m) {});
        } catch (\Throwable) {
            // Silencioso — o job no worker vai tentar de novo
        }

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

    public function logs(Requisicao $req): Resposta
    {
        $vpsId = (int)($req->query['id'] ?? 0);
        if ($vpsId <= 0) return Resposta::redirecionar('/equipe/vps');

        $pdo = BancoDeDados::pdo();

        // VPS info
        $stmt = $pdo->prepare("SELECT v.*, c.name AS client_name, c.email AS client_email FROM vps v INNER JOIN clients c ON c.id = v.client_id WHERE v.id = :id LIMIT 1");
        $stmt->execute([':id' => $vpsId]);
        $vps = $stmt->fetch();
        if (!is_array($vps)) return Resposta::redirecionar('/equipe/vps');

        // Job logs related to this VPS
        $logs = [];
        try {
            $logStmt = $pdo->prepare(
                "SELECT j.id, j.type, j.status, j.output, j.created_at, j.started_at, j.finished_at
                 FROM jobs j
                 WHERE j.payload LIKE :p
                 ORDER BY j.id DESC LIMIT 50"
            );
            $logStmt->execute([':p' => '%"vps_id":' . $vpsId . '%']);
            $logs = $logStmt->fetchAll() ?: [];
        } catch (\Throwable) {
            // Try alternative: payload might use different format
            try {
                $logStmt = $pdo->prepare(
                    "SELECT j.id, j.type, j.status, j.output, j.created_at, j.started_at, j.finished_at
                     FROM jobs j
                     WHERE j.payload LIKE :p
                     ORDER BY j.id DESC LIMIT 50"
                );
                $logStmt->execute([':p' => '%vps_id%' . $vpsId . '%']);
                $logs = $logStmt->fetchAll() ?: [];
            } catch (\Throwable) {}
        }

        // Audit logs
        $auditLogs = [];
        try {
            $aStmt = $pdo->prepare(
                "SELECT action, details, created_at, actor_type, actor_id
                 FROM audit_logs
                 WHERE entity_type = 'vps' AND entity_id = :id
                 ORDER BY id DESC LIMIT 30"
            );
            $aStmt->execute([':id' => $vpsId]);
            $auditLogs = $aStmt->fetchAll() ?: [];
        } catch (\Throwable) {}

        return Resposta::html(View::renderizar(__DIR__ . '/../../Views/equipe/vps-logs.php', [
            'vps' => $vps,
            'logs' => $logs,
            'audit_logs' => $auditLogs,
        ]));
    }

}
