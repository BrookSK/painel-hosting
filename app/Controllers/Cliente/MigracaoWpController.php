<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Cliente;

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
     * Lista migrações do cliente logado.
     */
    public function listar(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::redirecionar('/cliente/entrar');

        $pdo = BancoDeDados::pdo();

        $stmt = $pdo->prepare(
            "SELECT * FROM wp_migrations WHERE client_id = :c ORDER BY id DESC LIMIT 50"
        );
        $stmt->execute([':c' => $clienteId]);
        $migracoes = $stmt->fetchAll() ?: [];

        $cStmt = $pdo->prepare('SELECT name, email FROM clients WHERE id = ?');
        $cStmt->execute([$clienteId]);
        $cliente = $cStmt->fetch() ?: [];

        return Resposta::html(View::renderizar(
            __DIR__ . '/../../Views/cliente/migracao-wp-listar.php',
            ['migracoes' => $migracoes, 'cliente' => $cliente]
        ));
    }

    /**
     * Formulário para criar nova migração.
     */
    public function novo(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::redirecionar('/cliente/entrar');

        $pdo = BancoDeDados::pdo();

        // VPS ativas do cliente
        $vpsStmt = $pdo->prepare(
            "SELECT v.id, v.cpu, v.ram, s.hostname AS server_name
             FROM vps v
             JOIN servers s ON s.id = v.server_id
             WHERE v.client_id = :c AND v.status IN ('running','active')
             ORDER BY v.id"
        );
        $vpsStmt->execute([':c' => $clienteId]);
        $vpsList = $vpsStmt->fetchAll() ?: [];

        $cStmt = $pdo->prepare('SELECT name, email FROM clients WHERE id = ?');
        $cStmt->execute([$clienteId]);
        $cliente = $cStmt->fetch() ?: [];

        return Resposta::html(View::renderizar(
            __DIR__ . '/../../Views/cliente/migracao-wp-form.php',
            ['vpsList' => $vpsList, 'cliente' => $cliente, 'erro' => '']
        ));
    }

    /**
     * Salva nova migração e enfileira job.
     */
    public function salvar(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::redirecionar('/cliente/entrar');

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
        $sourceUseSudo = (int)(!empty($req->post['source_use_sudo']));
        $sourceSudoPassword = (string)($req->post['source_sudo_password'] ?? '');
        $destDomain = trim((string)($req->post['dest_domain'] ?? ''));

        // Validações
        if ($vpsId <= 0) {
            return $this->erroForm($clienteId, 'Selecione a VPS de destino.');
        }
        if ($sourceHost === '' || $sourceWpPath === '' || $sourceDbName === '') {
            return $this->erroForm($clienteId, 'Preencha o host, caminho do WordPress e nome do banco de origem.');
        }
        if ($sourcePassword === '') {
            return $this->erroForm($clienteId, 'Senha SSH do servidor de origem é obrigatória.');
        }

        // Validar VPS pertence ao cliente
        $pdo = BancoDeDados::pdo();
        $vCheck = $pdo->prepare("SELECT id FROM vps WHERE id = :v AND client_id = :c AND status IN ('running','active') LIMIT 1");
        $vCheck->execute([':v' => $vpsId, ':c' => $clienteId]);
        if (!$vCheck->fetch()) {
            return $this->erroForm($clienteId, 'VPS não encontrada ou inativa.');
        }

        if ($sourcePort <= 0 || $sourcePort > 65535) $sourcePort = 22;
        if ($sourceDbPort <= 0 || $sourceDbPort > 65535) $sourceDbPort = 3306;
        if ($sourceUser === '') $sourceUser = 'root';

        // Limite: máximo 5 migrações simultâneas por cliente
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM wp_migrations WHERE client_id = :c AND status NOT IN ('completed','failed','cancelled')");
        $countStmt->execute([':c' => $clienteId]);
        if ((int)$countStmt->fetchColumn() >= 5) {
            return $this->erroForm($clienteId, 'Limite de migrações simultâneas atingido (máx. 5). Aguarde as atuais finalizarem.');
        }

        $pdo->prepare(
            "INSERT INTO wp_migrations (client_id, vps_id, source_host, source_port, source_user, source_password_enc,
             source_wp_path, source_db_name, source_db_user, source_db_password_enc, source_db_host, source_db_port,
             source_use_sudo, source_sudo_password_enc, dest_domain, status, progress_percent, created_at, created_by)
             VALUES (:c, :v, :sh, :sp, :su, :spe, :swp, :sdn, :sdu, :sdpe, :sdh, :sdp, :sudo, :sudop, :dd, 'pending', 0, :cr, NULL)"
        )->execute([
            ':c' => $clienteId, ':v' => $vpsId,
            ':sh' => $sourceHost, ':sp' => $sourcePort,
            ':su' => $sourceUser, ':spe' => SshCrypto::cifrar($sourcePassword),
            ':swp' => $sourceWpPath, ':sdn' => $sourceDbName,
            ':sdu' => $sourceDbUser, ':sdpe' => SshCrypto::cifrar($sourceDbPassword),
            ':sdh' => $sourceDbHost, ':sdp' => $sourceDbPort,
            ':sudo' => $sourceUseSudo,
            ':sudop' => $sourceUseSudo && $sourceSudoPassword !== '' ? SshCrypto::cifrar($sourceSudoPassword) : '',
            ':dd' => $destDomain !== '' ? $destDomain : null,
            ':cr' => date('Y-m-d H:i:s'),
        ]);

        $migrationId = (int)$pdo->lastInsertId();

        $repo = new RepositorioJobs();
        $jobId = $repo->criar('wp_migration', ['migration_id' => $migrationId]);
        $pdo->prepare('UPDATE wp_migrations SET job_id = :j WHERE id = :id')
            ->execute([':j' => $jobId, ':id' => $migrationId]);

        return Resposta::redirecionar('/cliente/migracoes-wp/ver?id=' . $migrationId);
    }

    /**
     * Ver progresso de uma migração.
     */
    public function ver(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::redirecionar('/cliente/entrar');

        $id = (int)($req->query['id'] ?? 0);
        if ($id <= 0) return Resposta::redirecionar('/cliente/migracoes-wp');

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare("SELECT * FROM wp_migrations WHERE id = :id AND client_id = :c LIMIT 1");
        $stmt->execute([':id' => $id, ':c' => $clienteId]);
        $migration = $stmt->fetch();

        if (!is_array($migration)) {
            return Resposta::redirecionar('/cliente/migracoes-wp');
        }

        $cStmt = $pdo->prepare('SELECT name, email FROM clients WHERE id = ?');
        $cStmt->execute([$clienteId]);
        $cliente = $cStmt->fetch() ?: [];

        return Resposta::html(View::renderizar(
            __DIR__ . '/../../Views/cliente/migracao-wp-ver.php',
            ['migration' => $migration, 'cliente' => $cliente]
        ));
    }

    /**
     * API de polling de progresso (AJAX).
     */
    public function progresso(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::json(['ok' => false], 401);

        $id = (int)($req->query['id'] ?? 0);
        if ($id <= 0) return Resposta::json(['ok' => false], 404);

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT status, progress_percent, current_step, error_message, logs, completed_at FROM wp_migrations WHERE id = :id AND client_id = :c');
        $stmt->execute([':id' => $id, ':c' => $clienteId]);
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
     * Atividade em tempo real: tamanho transferido e número de arquivos.
     * Executa du -sh no diretório de destino via SSH.
     */
    public function atividade(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::json(['ok' => false], 401);

        $id = (int)($req->query['id'] ?? 0);
        if ($id <= 0) return Resposta::json(['ok' => false], 404);

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare(
            'SELECT m.status, m.vps_id, v.server_id, s.ip_address, s.ssh_port, s.ssh_user, s.ssh_password, s.ssh_auth_type, s.ssh_key_id
             FROM wp_migrations m
             JOIN vps v ON v.id = m.vps_id
             JOIN servers s ON s.id = v.server_id
             WHERE m.id = :id AND m.client_id = :c LIMIT 1'
        );
        $stmt->execute([':id' => $id, ':c' => $clienteId]);
        $row = $stmt->fetch();

        if (!is_array($row)) return Resposta::json(['ok' => false], 404);

        // Só mostra atividade se estiver em andamento
        if (in_array($row['status'], ['completed', 'failed', 'cancelled'], true)) {
            return Resposta::json(['ok' => true, 'size' => '—', 'files' => 0, 'active' => false]);
        }

        // Construir o deploy path (mesma lógica do WordPressMigrationService)
        $volumeBase = rtrim((string)\LRV\Core\Settings::obter('infra.volume_base', '/vps'), '/');
        $destPath = $volumeBase . '/client_' . $clienteId . '/wordpress_' . $id;

        try {
            $exec = new \LRV\App\Services\Infra\SshExecutor();
            $host = (string)$row['ip_address'];
            $port = (int)$row['ssh_port'];
            $user = (string)$row['ssh_user'];
            $authType = (string)($row['ssh_auth_type'] ?? 'password');

            $cmd = 'du -sh ' . escapeshellarg($destPath) . ' 2>/dev/null | cut -f1; ls -1 ' . escapeshellarg($destPath) . ' 2>/dev/null | wc -l';

            if ($authType === 'password') {
                $senha = \LRV\App\Services\Infra\SshCrypto::decifrar((string)($row['ssh_password'] ?? ''));
                $result = $exec->executarComSenha($host, $port, $user, $senha, $cmd, 30);
            } else {
                $keyPath = \LRV\Core\ConfiguracoesSistema::sshKeyDir() . DIRECTORY_SEPARATOR . (string)($row['ssh_key_id'] ?? '');
                $result = $exec->executar($host, $port, $user, $keyPath, $cmd, 30);
            }

            $output = trim((string)($result['saida'] ?? ''));
            $lines = explode("\n", $output);
            $size = trim($lines[0] ?? '0');
            $files = (int)trim($lines[1] ?? '0');

            return Resposta::json(['ok' => true, 'size' => $size, 'files' => $files, 'active' => true]);
        } catch (\Throwable) {
            return Resposta::json(['ok' => true, 'size' => '—', 'files' => 0, 'active' => true]);
        }
    }

    /**
     * Testar conexão SSH com o servidor de origem.
     */
    public function testarConexao(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::json(['ok' => false, 'erro' => 'Não autenticado.'], 401);

        $id = (int)($req->post['id'] ?? 0);
        if ($id <= 0) return Resposta::json(['ok' => false, 'erro' => 'ID inválido.'], 422);

        // Verificar que pertence ao cliente
        $pdo = BancoDeDados::pdo();
        $check = $pdo->prepare("SELECT id FROM wp_migrations WHERE id = :id AND client_id = :c LIMIT 1");
        $check->execute([':id' => $id, ':c' => $clienteId]);
        if (!$check->fetch()) return Resposta::json(['ok' => false, 'erro' => 'Migração não encontrada.'], 404);

        $svc = new WordPressMigrationService();
        return Resposta::json($svc->testarConexao($id));
    }

    /**
     * Cancelar migração.
     */
    public function cancelar(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::json(['ok' => false], 401);

        $id = (int)($req->post['id'] ?? 0);
        $pdo = BancoDeDados::pdo();
        $pdo->prepare("UPDATE wp_migrations SET status = 'cancelled', completed_at = :ca WHERE id = :id AND client_id = :c AND status NOT IN ('completed','cancelled')")
            ->execute([':ca' => date('Y-m-d H:i:s'), ':id' => $id, ':c' => $clienteId]);

        return Resposta::json(['ok' => true]);
    }

    /**
     * Ativar/trocar domínio real de uma migração concluída.
     */
    public function ativarDominio(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::json(['ok' => false, 'erro' => 'Não autenticado.'], 401);

        $id = (int)($req->post['id'] ?? 0);
        $novoDominio = trim((string)($req->post['dominio'] ?? ''));

        if ($id <= 0 || $novoDominio === '') {
            return Resposta::json(['ok' => false, 'erro' => 'ID e domínio são obrigatórios.'], 422);
        }

        // Verificar que pertence ao cliente
        $pdo = BancoDeDados::pdo();
        $check = $pdo->prepare("SELECT id FROM wp_migrations WHERE id = :id AND client_id = :c AND status = 'completed' LIMIT 1");
        $check->execute([':id' => $id, ':c' => $clienteId]);
        if (!$check->fetch()) {
            return Resposta::json(['ok' => false, 'erro' => 'Migração não encontrada ou não concluída.'], 404);
        }

        $svc = new WordPressMigrationService();
        $result = $svc->ativarDominio($id, $novoDominio);

        return Resposta::json($result);
    }

    private function erroForm(int $clienteId, string $msg): Resposta
    {
        $pdo = BancoDeDados::pdo();
        $vpsStmt = $pdo->prepare(
            "SELECT v.id, v.cpu, v.ram, s.hostname AS server_name FROM vps v
             JOIN servers s ON s.id = v.server_id
             WHERE v.client_id = :c AND v.status IN ('running','active') ORDER BY v.id"
        );
        $vpsStmt->execute([':c' => $clienteId]);
        $vpsList = $vpsStmt->fetchAll() ?: [];

        $cStmt = $pdo->prepare('SELECT name, email FROM clients WHERE id = ?');
        $cStmt->execute([$clienteId]);
        $cliente = $cStmt->fetch() ?: [];

        return Resposta::html(View::renderizar(
            __DIR__ . '/../../Views/cliente/migracao-wp-form.php',
            ['vpsList' => $vpsList, 'cliente' => $cliente, 'erro' => $msg]
        ));
    }
}
