<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Cliente;

use LRV\Core\Auth;
use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\Jobs\RepositorioJobs;
use LRV\Core\View;

final class BackupsController
{
    public function listar(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::redirecionar('/cliente/entrar');

        $pdo = BancoDeDados::pdo();

        // VPS do cliente
        $vpsStmt = $pdo->prepare("SELECT v.id, v.cpu, v.ram, v.storage, v.status, p.backup_slots
            FROM vps v LEFT JOIN plans p ON p.id = v.plan_id
            WHERE v.client_id = :c AND v.deleted_at IS NULL AND v.status NOT IN ('removed','expired')
            ORDER BY v.id DESC");
        $vpsStmt->execute([':c' => $clienteId]);
        $vpsList = $vpsStmt->fetchAll() ?: [];

        // Backups do cliente
        $backups = [];
        if (!empty($vpsList)) {
            $vpsIds = array_column($vpsList, 'id');
            $ph = implode(',', array_fill(0, count($vpsIds), '?'));
            $bkStmt = $pdo->prepare("SELECT id, vps_id, status, file_size, error, created_at, completed_at FROM backups WHERE vps_id IN ({$ph}) ORDER BY id DESC LIMIT 50");
            $bkStmt->execute($vpsIds);
            $backups = $bkStmt->fetchAll() ?: [];
        }

        return Resposta::html(View::renderizar(__DIR__ . '/../../Views/cliente/backups.php', [
            'vpsList' => $vpsList,
            'backups' => $backups,
            'erro'    => '',
            'sucesso' => (string)($req->query['ok'] ?? ''),
        ]));
    }

    public function criar(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::redirecionar('/cliente/entrar');

        $vpsId = (int)($req->post['vps_id'] ?? 0);
        if ($vpsId <= 0) return Resposta::texto('VPS inválida.', 400);

        $pdo = BancoDeDados::pdo();

        // Verificar que a VPS pertence ao cliente
        $vps = $pdo->prepare("SELECT v.id, v.plan_id FROM vps v WHERE v.id = :id AND v.client_id = :c AND v.deleted_at IS NULL");
        $vps->execute([':id' => $vpsId, ':c' => $clienteId]);
        if (!$vps->fetch()) return Resposta::texto('VPS não encontrada.', 404);

        // Verificar limite de backups do plano
        $planStmt = $pdo->prepare('SELECT backup_slots FROM plans WHERE id = (SELECT plan_id FROM vps WHERE id = :v)');
        $planStmt->execute([':v' => $vpsId]);
        $plan = $planStmt->fetch();
        $maxBackups = (int)($plan['backup_slots'] ?? 0);

        if ($maxBackups <= 0) {
            return Resposta::redirecionar('/cliente/backups?erro=sem_slots');
        }

        // Verificar se já tem backup em andamento
        $running = $pdo->prepare("SELECT COUNT(*) FROM backups WHERE vps_id = :v AND status IN ('queued','running')");
        $running->execute([':v' => $vpsId]);
        if ((int)$running->fetchColumn() > 0) {
            return Resposta::redirecionar('/cliente/backups?erro=em_andamento');
        }

        // Rotação: apagar mais antigo se exceder limite
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM backups WHERE vps_id = :v AND status IN ('completed','running','queued')");
        $countStmt->execute([':v' => $vpsId]);
        $existentes = (int)$countStmt->fetchColumn();

        if ($existentes >= $maxBackups) {
            $oldStmt = $pdo->prepare("SELECT id, file_path FROM backups WHERE vps_id = :v AND status = 'completed' ORDER BY id ASC LIMIT 1");
            $oldStmt->execute([':v' => $vpsId]);
            $old = $oldStmt->fetch();
            if (is_array($old)) {
                $oldPath = (string)($old['file_path'] ?? '');
                if ($oldPath !== '' && is_file($oldPath)) @unlink($oldPath);
                $pdo->prepare('DELETE FROM backups WHERE id = :id')->execute([':id' => (int)$old['id']]);
            }
        }

        // Criar backup
        $ins = $pdo->prepare("INSERT INTO backups (vps_id, job_id, status, created_at) VALUES (:v, NULL, 'queued', :c)");
        $ins->execute([':v' => $vpsId, ':c' => date('Y-m-d H:i:s')]);
        $backupId = (int)$pdo->lastInsertId();

        $repo = new RepositorioJobs();
        $jobId = $repo->criar('backup_vps', ['backup_id' => $backupId]);
        $pdo->prepare('UPDATE backups SET job_id = :j WHERE id = :id')->execute([':j' => $jobId, ':id' => $backupId]);

        return Resposta::redirecionar('/cliente/backups?ok=criado');
    }

    public function baixar(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::redirecionar('/cliente/entrar');

        $id = (int)($req->query['id'] ?? 0);
        if ($id <= 0) return Resposta::texto('Backup inválido.', 400);

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare("SELECT b.id, b.status, b.file_path, b.vps_id FROM backups b
            INNER JOIN vps v ON v.id = b.vps_id
            WHERE b.id = :id AND v.client_id = :c LIMIT 1");
        $stmt->execute([':id' => $id, ':c' => $clienteId]);
        $bk = $stmt->fetch();

        if (!is_array($bk)) return Resposta::texto('Backup não encontrado.', 404);
        if ((string)($bk['status'] ?? '') !== 'completed') return Resposta::texto('Backup não está pronto.', 409);

        $path = (string)($bk['file_path'] ?? '');
        if ($path === '' || !is_file($path)) return Resposta::texto('Arquivo não encontrado.', 404);

        $realPath = realpath($path);
        $backupDir = realpath(dirname(__DIR__, 3) . '/storage/backups');
        if ($realPath === false || $backupDir === false || !str_starts_with($realPath, $backupDir . DIRECTORY_SEPARATOR)) {
            return Resposta::texto('Acesso negado.', 403);
        }

        $nome = 'backup_vps_' . (int)$bk['vps_id'] . '_' . $id . '.tar.gz';
        return Resposta::arquivo($realPath, $nome);
    }

    public function restaurar(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::redirecionar('/cliente/entrar');

        $id = (int)($req->post['id'] ?? 0);
        if ($id <= 0) return Resposta::texto('Backup inválido.', 400);

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare("SELECT b.id, b.vps_id, b.file_path FROM backups b
            INNER JOIN vps v ON v.id = b.vps_id
            WHERE b.id = :id AND v.client_id = :c AND b.status = 'completed' LIMIT 1");
        $stmt->execute([':id' => $id, ':c' => $clienteId]);
        $bk = $stmt->fetch();

        if (!is_array($bk)) return Resposta::texto('Backup não encontrado ou não está pronto.', 404);

        $repo = new RepositorioJobs();
        $jobId = $repo->criar('restaurar_backup', ['backup_id' => $id, 'vps_id' => (int)$bk['vps_id']]);

        return Resposta::redirecionar('/cliente/backups?ok=restaurando');
    }
}
