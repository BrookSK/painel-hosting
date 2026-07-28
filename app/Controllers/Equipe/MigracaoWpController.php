<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Equipe;

use LRV\Core\Auth;
use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;
use LRV\Core\Jobs\RepositorioJobs;
use LRV\App\Services\Infra\SshCrypto;
use LRV\App\Services\Migration\WordPressMigrationService;

final class MigracaoWpController
{
    /**
     * Lista todas as migrações (com filtro por status).
     */
    public function listar(Requisicao $req): Resposta
    {
        $pdo = BancoDeDados::pdo();
        $filtro = trim((string)($req->query['status'] ?? ''));

        $sql = "SELECT m.*, c.name AS client_name, c.email AS client_email
                FROM wp_migrations m
                LEFT JOIN clients c ON c.id = m.client_id
                ORDER BY m.id DESC LIMIT 100";

        if ($filtro !== '' && in_array($filtro, ['pending','connecting','syncing_files','dumping_db','importing_db','configuring','finalizing','completed','failed','cancelled'], true)) {
            $sql = "SELECT m.*, c.name AS client_name, c.email AS client_email
                    FROM wp_migrations m
                    LEFT JOIN clients c ON c.id = m.client_id
                    WHERE m.status = :s
                    ORDER BY m.id DESC LIMIT 100";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':s' => $filtro]);
        } else {
            $stmt = $pdo->query($sql);
        }

        $migracoes = $stmt->fetchAll() ?: [];

        return Resposta::html(View::renderizar(
            __DIR__ . '/../../Views/equipe/migracao-wp-listar.php',
            ['migracoes' => $migracoes, 'filtro' => $filtro]
        ));
    }

    /**
     * Formulário para criar nova migração.
     */
    public function novo(Requisicao $req): Resposta
    {
        $pdo = BancoDeDados::pdo();

        // Listar clientes com VPS ativa
        $clientes = $pdo->query(
            "SELECT DISTINCT c.id, c.name, c.email
             FROM clients c
             INNER JOIN vps v ON v.client_id = c.id AND v.status IN ('running','active')
             ORDER BY c.name"
        )->fetchAll() ?: [];

        // Listar VPS ativas
        $vpsList = $pdo->query(
            "SELECT v.id, v.client_id, v.cpu, v.ram, s.hostname AS server_name
             FROM vps v
             JOIN servers s ON s.id = v.server_id
             WHERE v.status IN ('running','active')
             ORDER BY v.id"
        )->fetchAll() ?: [];

        return Resposta::html(View::renderizar(
            __DIR__ . '/../../Views/equipe/migracao-wp-form.php',
            ['clientes' => $clientes, 'vpsList' => $vpsList, 'erro' => '']
        ));
    }

    /**
     * Salva nova migração e enfileira job.
     */
    public function salvar(Requisicao $req): Resposta
    {
        $clientId = (int)($req->post['client_id'] ?? 0);
        $vpsId = (int)($req->post['vps_id'] ?? 0);
        $sourceHost = trim((string)($req->post['source_host'] ?? ''));
        $sourcePort = (int)($req->post['source_port'] ?? 22);
        $sourceUser = trim((string)($req->post['source_user'] ?? 'root'));
        $sourcePassword = (string)($req->post['source_password'] ?? '');
        $sourceWpPath = trim((string)($req->post['source_wp_path'] ?? ''));
        $sourceDbName = trim((string)($req->post['source_db_name'] ?? ''));
        $sourceDbUser = trim((string)($req->post['source_db_user'] ?? 'root'));
        $sourceDbPassword = (string)($req->post['source_db_password'] ?? '');
        $sourceDbHost = trim((string)($req->post['source_db_host'] ?? 'localhost'));
        $sourceDbPort = (int)($req->post['source_db_port'] ?? 3306);
        $destDomain = trim((string)($req->post['dest_domain'] ?? ''));

        // Validações
        if ($clientId <= 0 || $vpsId <= 0) {
            return $this->erroForm('Selecione o cliente e a VPS de destino.');
        }
        if ($sourceHost === '' || $sourceWpPath === '' || $sourceDbName === '') {
            return $this->erroForm('Preencha o host, caminho do WordPress e nome do banco de origem.');
        }
        if ($sourcePassword === '') {
            return $this->erroForm('Senha SSH do servidor de origem é obrigatória.');
        }
        if ($sourcePort <= 0 || $sourcePort > 65535) $sourcePort = 22;
        if ($sourceDbPort <= 0 || $sourceDbPort > 65535) $sourceDbPort = 3306;
        if ($sourceUser === '') $sourceUser = 'root';

        $pdo = BancoDeDados::pdo();
        $userId = Auth::equipeId();

        $pdo->prepare(
            "INSERT INTO wp_migrations (client_id, vps_id, source_host, source_port, source_user, source_password_enc,
             source_wp_path, source_db_name, source_db_user, source_db_password_enc, source_db_host, source_db_port,
             dest_domain, status, progress_percent, created_at, created_by)
             VALUES (:c, :v, :sh, :sp, :su, :spe, :swp, :sdn, :sdu, :sdpe, :sdh, :sdp, :dd, 'pending', 0, :cr, :cb)"
        )->execute([
            ':c' => $clientId, ':v' => $vpsId,
            ':sh' => $sourceHost, ':sp' => $sourcePort,
            ':su' => $sourceUser, ':spe' => SshCrypto::cifrar($sourcePassword),
            ':swp' => $sourceWpPath, ':sdn' => $sourceDbName,
            ':sdu' => $sourceDbUser, ':sdpe' => SshCrypto::cifrar($sourceDbPassword),
            ':sdh' => $sourceDbHost, ':sdp' => $sourceDbPort,
            ':dd' => $destDomain !== '' ? $destDomain : null,
            ':cr' => date('Y-m-d H:i:s'), ':cb' => $userId,
        ]);

        $migrationId = (int)$pdo->lastInsertId();

        // Enfileirar job
        $repo = new RepositorioJobs();
        $jobId = $repo->criar('wp_migration', ['migration_id' => $migrationId]);

        $pdo->prepare('UPDATE wp_migrations SET job_id = :j WHERE id = :id')
            ->execute([':j' => $jobId, ':id' => $migrationId]);

        return Resposta::redirecionar('/equipe/migracoes-wp/ver?id=' . $migrationId);
    }

    /**
     * Visualizar detalhes e progresso de uma migração.
     */
    public function ver(Requisicao $req): Resposta
    {
        $id = (int)($req->query['id'] ?? 0);
        if ($id <= 0) return Resposta::redirecionar('/equipe/migracoes-wp');

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare(
            "SELECT m.*, c.name AS client_name, c.email AS client_email, u.name AS created_by_name
             FROM wp_migrations m
             LEFT JOIN clients c ON c.id = m.client_id
             LEFT JOIN users u ON u.id = m.created_by
             WHERE m.id = :id LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        $migration = $stmt->fetch();

        if (!is_array($migration)) {
            return Resposta::redirecionar('/equipe/migracoes-wp');
        }

        return Resposta::html(View::renderizar(
            __DIR__ . '/../../Views/equipe/migracao-wp-ver.php',
            ['migration' => $migration]
        ));
    }

    /**
     * API para polling de progresso (AJAX).
     */
    public function progresso(Requisicao $req): Resposta
    {
        $id = (int)($req->query['id'] ?? 0);
        if ($id <= 0) return Resposta::json(['ok' => false], 404);

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT status, progress_percent, current_step, error_message, logs, completed_at FROM wp_migrations WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        if (!is_array($row)) return Resposta::json(['ok' => false], 404);

        return Resposta::json([
            'ok' => true,
            'status' => $row['status'],
            'progress' => (int)$row['progress_percent'],
            'step' => $row['current_step'],
            'error' => $row['error_message'],
            'logs' => $row['logs'],
            'completed_at' => $row['completed_at'],
        ]);
    }

    /**
     * Testar conexão SSH com o servidor de origem.
     */
    public function testarConexao(Requisicao $req): Resposta
    {
        $id = (int)($req->post['id'] ?? 0);
        if ($id <= 0) return Resposta::json(['ok' => false, 'erro' => 'ID inválido.'], 422);

        $svc = new WordPressMigrationService();
        $result = $svc->testarConexao($id);

        return Resposta::json($result);
    }

    /**
     * Reexecutar uma migração que falhou.
     */
    public function reexecutar(Requisicao $req): Resposta
    {
        $id = (int)($req->post['id'] ?? 0);
        if ($id <= 0) return Resposta::json(['ok' => false, 'erro' => 'ID inválido.'], 422);

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare("SELECT id, status FROM wp_migrations WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        if (!is_array($row) || !in_array($row['status'], ['failed', 'cancelled'], true)) {
            return Resposta::json(['ok' => false, 'erro' => 'Migração não pode ser reexecutada.'], 422);
        }

        // Reset status
        $pdo->prepare("UPDATE wp_migrations SET status = 'pending', progress_percent = 0, current_step = NULL, error_message = NULL, logs = NULL, started_at = NULL, completed_at = NULL WHERE id = :id")
            ->execute([':id' => $id]);

        $repo = new RepositorioJobs();
        $jobId = $repo->criar('wp_migration', ['migration_id' => $id]);
        $pdo->prepare('UPDATE wp_migrations SET job_id = :j WHERE id = :id')
            ->execute([':j' => $jobId, ':id' => $id]);

        return Resposta::json(['ok' => true, 'job_id' => $jobId]);
    }

    /**
     * Cancelar migração em andamento.
     */
    public function cancelar(Requisicao $req): Resposta
    {
        $id = (int)($req->post['id'] ?? 0);
        if ($id <= 0) return Resposta::json(['ok' => false, 'erro' => 'ID inválido.'], 422);

        $pdo = BancoDeDados::pdo();
        $pdo->prepare("UPDATE wp_migrations SET status = 'cancelled', completed_at = :ca WHERE id = :id AND status NOT IN ('completed','cancelled')")
            ->execute([':ca' => date('Y-m-d H:i:s'), ':id' => $id]);

        return Resposta::json(['ok' => true]);
    }

    /**
     * Ativar/trocar domínio real de uma migração concluída.
     */
    public function ativarDominio(Requisicao $req): Resposta
    {
        $id = (int)($req->post['id'] ?? 0);
        $novoDominio = trim((string)($req->post['dominio'] ?? ''));

        if ($id <= 0 || $novoDominio === '') {
            return Resposta::json(['ok' => false, 'erro' => 'ID e domínio são obrigatórios.'], 422);
        }

        $svc = new WordPressMigrationService();
        $result = $svc->ativarDominio($id, $novoDominio);

        return Resposta::json($result);
    }

    private function erroForm(string $msg): Resposta
    {
        $pdo = BancoDeDados::pdo();
        $clientes = $pdo->query(
            "SELECT DISTINCT c.id, c.name, c.email FROM clients c
             INNER JOIN vps v ON v.client_id = c.id AND v.status IN ('running','active') ORDER BY c.name"
        )->fetchAll() ?: [];
        $vpsList = $pdo->query(
            "SELECT v.id, v.client_id, v.cpu, v.ram, s.hostname AS server_name FROM vps v
             JOIN servers s ON s.id = v.server_id WHERE v.status IN ('running','active') ORDER BY v.id"
        )->fetchAll() ?: [];

        return Resposta::html(View::renderizar(
            __DIR__ . '/../../Views/equipe/migracao-wp-form.php',
            ['clientes' => $clientes, 'vpsList' => $vpsList, 'erro' => $msg]
        ));
    }
}
