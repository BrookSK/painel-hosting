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
            try {
                $bkStmt = $pdo->prepare("SELECT id, vps_id, status, file_size, error, warnings, manifest, created_at, completed_at FROM backups WHERE vps_id IN ({$ph}) ORDER BY id DESC LIMIT 50");
                $bkStmt->execute($vpsIds);
            } catch (\Throwable) {
                // migration 0101 ainda não aplicada
                $bkStmt = $pdo->prepare("SELECT id, vps_id, status, file_size, error, created_at, completed_at FROM backups WHERE vps_id IN ({$ph}) ORDER BY id DESC LIMIT 50");
                $bkStmt->execute($vpsIds);
            }
            $backups = $bkStmt->fetchAll() ?: [];
        }

        // Pastas extras e atalhos (pastas dos Git Deploys) por VPS
        $pastasExtras = [];
        $pastasDeploy = [];
        foreach ($vpsList as $v) {
            $vId = (int) $v['id'];
            $pastasExtras[$vId] = self::pastasDaVps($vId);
            $gd = $pdo->prepare('SELECT DISTINCT deploy_path FROM git_deployments WHERE client_id = :c AND vps_id = :v');
            $gd->execute([':c' => $clienteId, ':v' => $vId]);
            $pastasDeploy[$vId] = array_values(array_filter(array_map(
                static fn ($p) => rtrim((string) $p, '/'),
                $gd->fetchAll(\PDO::FETCH_COLUMN) ?: []
            )));
        }

        return Resposta::html(View::renderizar(__DIR__ . '/../../Views/cliente/backups.php', [
            'vpsList' => $vpsList,
            'backups' => $backups,
            'pastasExtras' => $pastasExtras,
            'pastasDeploy' => $pastasDeploy,
            'erro'    => mb_substr((string)($req->query['erro'] ?? ''), 0, 300),
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

        // Somar addons de backup extra ativos
        $maxBackups += \LRV\App\Services\Billing\AddonEffectService::contarAddonsAtivos($clienteId, 'backup_extra');

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

    /** Lê as pastas extras de backup da VPS ([] se a migration 0101 não rodou). */
    private static function pastasDaVps(int $vpsId): array
    {
        try {
            $st = BancoDeDados::pdo()->prepare('SELECT backup_extra_paths FROM vps WHERE id = :id LIMIT 1');
            $st->execute([':id' => $vpsId]);
            $lista = json_decode((string) ($st->fetchColumn() ?: ''), true);
        } catch (\Throwable) {
            return [];
        }
        return is_array($lista) ? array_values(array_filter($lista, 'is_string')) : [];
    }

    /** Adiciona/remove uma pasta extra do backup da VPS. */
    public function pastas(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::redirecionar('/cliente/entrar');

        $vpsId = (int)($req->post['vps_id'] ?? 0);
        $acao = (string)($req->post['acao'] ?? '');
        $caminho = (string)($req->post['caminho'] ?? '');
        if ($vpsId <= 0 || !in_array($acao, ['adicionar', 'remover'], true)) {
            return Resposta::texto('Dados inválidos.', 400);
        }

        $pdo = BancoDeDados::pdo();
        $vps = $pdo->prepare("SELECT v.id, v.plan_id FROM vps v WHERE v.id = :id AND v.client_id = :c AND v.deleted_at IS NULL");
        $vps->execute([':id' => $vpsId, ':c' => $clienteId]);
        if (!$vps->fetch()) return Resposta::texto('VPS não encontrada.', 404);

        $lista = self::pastasDaVps($vpsId);

        if ($acao === 'adicionar') {
            try {
                $caminho = \LRV\App\Services\Seguranca\ValidadorInfra::exigirCaminhoBackupExtra($caminho, $clienteId, $vpsId);
            } catch (\InvalidArgumentException $e) {
                return Resposta::redirecionar('/cliente/backups?erro=' . urlencode($e->getMessage()));
            }
            if (!in_array($caminho, $lista, true)) {
                if (count($lista) >= 20) {
                    return Resposta::redirecionar('/cliente/backups?erro=' . urlencode('Limite de pastas extras atingido.'));
                }
                $lista[] = $caminho;
            }
        } else {
            $caminho = rtrim(trim(str_replace('\\', '/', $caminho)), '/');
            $lista = array_values(array_filter($lista, static fn ($p) => $p !== $caminho));
        }

        try {
            $pdo->prepare('UPDATE vps SET backup_extra_paths = :p WHERE id = :id')->execute([
                ':p' => $lista === [] ? null : json_encode(array_values(array_unique($lista)), JSON_UNESCAPED_SLASHES),
                ':id' => $vpsId,
            ]);
        } catch (\Throwable) {
            return Resposta::redirecionar('/cliente/backups?erro=' . urlencode('Recurso ainda não disponível. Tente novamente mais tarde.'));
        }

        return Resposta::redirecionar('/cliente/backups?ok=pastas');
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

        $filePath = (string)($bk['file_path'] ?? '');
        $svc = new \LRV\App\Services\Backup\VpsBackupService(new \LRV\App\Services\Provisioning\DockerCli());
        $localFile = $svc->baixarRemoto($filePath);

        if ($localFile === null) return Resposta::texto('Arquivo não encontrado.', 404);

        $nome = 'backup_vps_' . (int)$bk['vps_id'] . '_' . $id . '.tar.gz';
        $resp = Resposta::arquivo($localFile, $nome);

        // Limpar arquivo temporário após envio (se é download remoto)
        if (str_starts_with($filePath, 'remote:')) {
            register_shutdown_function(function() use ($localFile) { @unlink($localFile); });
        }

        return $resp;
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
