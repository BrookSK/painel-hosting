<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Equipe;

use LRV\App\Services\Audit\AuditLogService;
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
        $pdo = BancoDeDados::pdo();

        $sql = "SELECT b.id, b.vps_id, b.job_id, b.status, b.file_path, b.file_size, b.error, b.created_at, b.completed_at,
                       c.email AS client_email
                FROM backups b
                INNER JOIN vps v ON v.id = b.vps_id
                INNER JOIN clients c ON c.id = v.client_id
                ORDER BY b.id DESC
                LIMIT 200";
        $stmt = $pdo->query($sql);
        $backups = $stmt->fetchAll();

        $stmt2 = $pdo->query('SELECT v.id, c.email AS client_email FROM vps v INNER JOIN clients c ON c.id = v.client_id ORDER BY v.id DESC');
        $vps = $stmt2->fetchAll();

        $html = View::renderizar(__DIR__ . '/../../Views/equipe/backups-listar.php', [
            'backups' => is_array($backups) ? $backups : [],
            'vps' => is_array($vps) ? $vps : [],
        ]);

        return Resposta::html($html);
    }

    public function criar(Requisicao $req): Resposta
    {
        $vpsId = (int) ($req->post['vps_id'] ?? 0);
        if ($vpsId <= 0) {
            return Resposta::texto('VPS inválida.', 400);
        }

        $pdo = BancoDeDados::pdo();

        $stmt = $pdo->prepare('SELECT id FROM vps WHERE id = :id');
        $stmt->execute([':id' => $vpsId]);
        $vps = $stmt->fetch();
        if (!is_array($vps)) {
            return Resposta::texto('VPS não encontrada.', 404);
        }

        $ins = $pdo->prepare('INSERT INTO backups (vps_id, job_id, status, file_path, file_size, error, created_at, completed_at) VALUES (:v,NULL,:s,NULL,0,NULL,:c,NULL)');
        $ins->execute([
            ':v' => $vpsId,
            ':s' => 'queued',
            ':c' => date('Y-m-d H:i:s'),
        ]);

        $backupId = (int) $pdo->lastInsertId();

        $repo = new RepositorioJobs();
        $jobId = $repo->criar('backup_vps', [
            'backup_id' => $backupId,
            'user_id' => (int) (Auth::equipeId() ?? 0),
        ]);

        $up = $pdo->prepare('UPDATE backups SET job_id = :j WHERE id = :id');
        $up->execute([':j' => $jobId, ':id' => $backupId]);

        (new AuditLogService())->registrar(
            'team',
            \LRV\Core\Auth::equipeId(),
            'backup.create',
            'backup',
            $backupId,
            ['backup_id' => $backupId, 'vps_id' => $vpsId, 'job_id' => $jobId],
            $req,
        );

        return Resposta::redirecionar('/equipe/jobs/ver?id=' . $jobId);
    }

    public function baixar(Requisicao $req): Resposta
    {
        $id = (int) ($req->query['id'] ?? 0);
        if ($id <= 0) {
            return Resposta::texto('Backup inválido.', 400);
        }

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT id, status, file_path, vps_id FROM backups WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $bk = $stmt->fetch();

        if (!is_array($bk)) {
            return Resposta::texto('Backup não encontrado.', 404);
        }

        if ((string) ($bk['status'] ?? '') !== 'completed') {
            return Resposta::texto('Backup ainda não está pronto.', 409);
        }

        $path = (string) ($bk['file_path'] ?? '');
        if ($path === '' || !is_file($path)) {
            return Resposta::texto('Arquivo não encontrado.', 404);
        }

        // Prevenir path traversal: verificar que o arquivo está no diretório de backups
        $realPath = realpath($path);
        $backupDir = realpath(dirname(__DIR__, 3) . '/storage/backups');
        if ($realPath === false || $backupDir === false || !str_starts_with($realPath, $backupDir . DIRECTORY_SEPARATOR)) {
            return Resposta::texto('Acesso negado.', 403);
        }

        $nome = 'backup_vps_' . (int) ($bk['vps_id'] ?? 0) . '_' . (int) ($bk['id'] ?? 0) . '.tar.gz';
        return Resposta::arquivo($realPath, $nome);
    }

    public function excluir(Requisicao $req): Resposta
    {
        $id = (int) ($req->post['id'] ?? 0);
        if ($id <= 0) {
            return Resposta::texto('Backup inválido.', 400);
        }

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT id, file_path FROM backups WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $bk = $stmt->fetch();

        if (!is_array($bk)) {
            return Resposta::texto('Backup não encontrado.', 404);
        }

        $path = (string) ($bk['file_path'] ?? '');

        $del = $pdo->prepare('DELETE FROM backups WHERE id = :id');
        $del->execute([':id' => $id]);

        if ($path !== '' && is_file($path)) {
            @unlink($path);
        }

        (new AuditLogService())->registrar(
            'team',
            \LRV\Core\Auth::equipeId(),
            'backup.delete',
            'backup',
            $id,
            ['backup_id' => $id],
            $req,
        );

        return Resposta::redirecionar('/equipe/backups');
    }
}
