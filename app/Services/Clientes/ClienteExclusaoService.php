<?php

declare(strict_types=1);

namespace LRV\App\Services\Clientes;

use LRV\App\Services\Audit\AuditLogService;
use LRV\App\Services\Billing\Asaas\AsaasApi;
use LRV\App\Services\Billing\Asaas\AsaasExcecao;
use LRV\App\Services\Http\ClienteHttp;
use LRV\App\Services\Provisioning\DockerCli;
use LRV\App\Services\Provisioning\VpsProvisioningService;
use LRV\Core\BancoDeDados;
use LRV\Core\Jobs\RepositorioJobs;

/**
 * Inativação e exclusão definitiva de clientes.
 *
 * - Inativar: bloqueia login, sessões abertas e API; suspende as VPS. Nada é apagado
 *   e a cobrança não é alterada. Reversível com reativar().
 * - Excluir: roda em segundo plano (job excluir_cliente). Cancela cobranças nos
 *   gateways, remove containers e dados no servidor e apaga todos os registros do
 *   cliente. Se algum passo externo falhar, para ali (status "failed") sem apagar o
 *   banco, para que a exclusão possa ser tentada de novo sem deixar nada órfão.
 *   Logs de auditoria/API/login são mantidos como evidência.
 */
final class ClienteExclusaoService
{
    /** Tabelas com client_id que NUNCA são apagadas (evidência/segurança). */
    private const MANTER = ['audit_logs', 'api_logs', 'auth_logs', 'clients'];

    /** Tabelas onde client_id é só uma referência opcional: vira NULL. */
    private const DESVINCULAR = ['plans', 'status_services'];

    // ── Inativar / Reativar ───────────────────────────────────────────

    public function inativar(int $clientId, ?int $equipeId, string $motivo = ''): int
    {
        $pdo = BancoDeDados::pdo();
        $pdo->prepare('UPDATE clients SET disabled_at = :d, disabled_reason = :m WHERE id = :id AND disabled_at IS NULL')
            ->execute([':d' => date('Y-m-d H:i:s'), ':m' => $motivo !== '' ? mb_substr($motivo, 0, 255) : null, ':id' => $clientId]);

        // Suspender as VPS ativas (tira do ar, mantém os dados)
        $s = $pdo->prepare("SELECT id FROM vps WHERE client_id = :c AND deleted_at IS NULL AND status NOT IN ('removed','suspended_payment')");
        $s->execute([':c' => $clientId]);
        $repo = new RepositorioJobs();
        $total = 0;
        foreach ($s->fetchAll(\PDO::FETCH_COLUMN) ?: [] as $vpsId) {
            $repo->criar('suspender_vps', ['vps_id' => (int) $vpsId]);
            $total++;
        }

        (new AuditLogService())->registrar('team', $equipeId, 'client.disable', 'client', $clientId, ['motivo' => $motivo, 'vps_suspensas' => $total]);
        return $total;
    }

    public function reativar(int $clientId, ?int $equipeId): int
    {
        $pdo = BancoDeDados::pdo();
        $pdo->prepare('UPDATE clients SET disabled_at = NULL, disabled_reason = NULL WHERE id = :id')
            ->execute([':id' => $clientId]);

        // Religar só as VPS suspensas cuja assinatura não está inadimplente/cancelada
        $s = $pdo->prepare(
            "SELECT v.id FROM vps v
             WHERE v.client_id = :c AND v.deleted_at IS NULL AND v.status = 'suspended_payment'
               AND NOT EXISTS (
                   SELECT 1 FROM subscriptions s
                   WHERE s.vps_id = v.id AND UPPER(s.status) IN ('OVERDUE','SUSPENDED','CANCELED','EXPIRED')
               )"
        );
        $s->execute([':c' => $clientId]);
        $repo = new RepositorioJobs();
        $total = 0;
        foreach ($s->fetchAll(\PDO::FETCH_COLUMN) ?: [] as $vpsId) {
            $repo->criar('reativar_vps', ['vps_id' => (int) $vpsId]);
            $total++;
        }

        (new AuditLogService())->registrar('team', $equipeId, 'client.enable', 'client', $clientId, ['vps_reativadas' => $total]);
        return $total;
    }

    // ── Exclusão definitiva ───────────────────────────────────────────

    /** Bloqueia o cliente na hora e agenda o job de exclusão. Retorna o ID do job. */
    public function agendarExclusao(int $clientId, ?int $equipeId): int
    {
        $pdo = BancoDeDados::pdo();
        $up = $pdo->prepare(
            "UPDATE clients
             SET deletion_status = 'pending', deletion_error = NULL, deletion_requested_at = :t,
                 disabled_at = COALESCE(disabled_at, :t2)
             WHERE id = :id AND (
                 deletion_status IS NULL OR deletion_status = 'failed'
                 -- destrava exclusão presa (worker caiu no meio) depois de 2h
                 OR (deletion_status IN ('pending','running') AND deletion_requested_at < DATE_SUB(NOW(), INTERVAL 2 HOUR))
             )"
        );
        $up->execute([':t' => date('Y-m-d H:i:s'), ':t2' => date('Y-m-d H:i:s'), ':id' => $clientId]);
        if ($up->rowCount() === 0) {
            throw new \RuntimeException('A exclusão deste cliente já está em andamento.');
        }

        $jobId = (new RepositorioJobs())->criar('excluir_cliente', ['client_id' => $clientId, 'equipe_id' => $equipeId]);
        (new AuditLogService())->registrar('team', $equipeId, 'client.delete_requested', 'client', $clientId, ['job_id' => $jobId]);
        return $jobId;
    }

    /** Executado pelo worker. Lança exceção (e marca failed) se não conseguir concluir. */
    public function executarExclusao(int $clientId, ?int $equipeId, callable $log): void
    {
        $pdo = BancoDeDados::pdo();

        $lock = $pdo->prepare("UPDATE clients SET deletion_status = 'running' WHERE id = :id AND deletion_status = 'pending'");
        $lock->execute([':id' => $clientId]);
        if ($lock->rowCount() === 0) {
            $chk = $pdo->prepare('SELECT deletion_status FROM clients WHERE id = :id');
            $chk->execute([':id' => $clientId]);
            $st = $chk->fetchColumn();
            if ($st === false) {
                $log('Cliente #' . $clientId . ' já não existe. Nada a fazer.');
                return;
            }
            throw new \RuntimeException('Exclusão não está pendente (status atual: ' . (string) $st . ').');
        }

        try {
            $resumo = [];
            $resumo['assinaturas_canceladas'] = $this->cancelarCobrancas($clientId, $log);
            $resumo['emails_removidos'] = $this->removerEmails($clientId, $log);
            $resumo['bancos_removidos'] = $this->removerBancosDeDados($clientId, $log); // antes das VPS (remover() apaga os registros)
            $resumo['vps_removidas'] = $this->removerVps($clientId, $log);
            $resumo['nodes_limpos'] = $this->removerDadosNosNodes($clientId, $log);
            $this->removerColaboradoresGit($clientId, $log);
            $this->apagarBackupsLocais($clientId, $log);
            $resumo['tabelas'] = $this->purgarBanco($clientId, $log);
        } catch (\Throwable $e) {
            try {
                $pdo->prepare("UPDATE clients SET deletion_status = 'failed', deletion_error = :e WHERE id = :id")
                    ->execute([':e' => mb_substr($e->getMessage(), 0, 4000), ':id' => $clientId]);
            } catch (\Throwable) {}
            $log('FALHA: ' . $e->getMessage());
            throw $e;
        }

        (new AuditLogService())->registrar('team', $equipeId, 'client.delete', 'client', $clientId, $resumo);
        $log('Cliente #' . $clientId . ' excluído por completo.');
    }

    /** Cancela as recorrências no Asaas/Stripe. "Não encontrada" conta como cancelada. */
    private function cancelarCobrancas(int $clientId, callable $log): int
    {
        $pdo = BancoDeDados::pdo();
        $s = $pdo->prepare('SELECT id, status, asaas_subscription_id, stripe_subscription_id FROM subscriptions WHERE client_id = :c');
        $s->execute([':c' => $clientId]);
        $subs = $s->fetchAll() ?: [];

        $asaas = null;
        $total = 0;
        foreach ($subs as $sub) {
            $id = (int) $sub['id'];
            $asaasId = trim((string) ($sub['asaas_subscription_id'] ?? ''));
            $stripeId = trim((string) ($sub['stripe_subscription_id'] ?? ''));

            if ($asaasId !== '') {
                $asaas ??= new AsaasApi(new ClienteHttp());
                try {
                    $asaas->cancelarAssinatura($asaasId);
                    $log('Asaas: assinatura ' . $asaasId . ' cancelada.');
                } catch (AsaasExcecao $e) {
                    if ($e->status !== 404) {
                        throw new \RuntimeException('Asaas recusou cancelar a assinatura ' . $asaasId . ': ' . $e->getMessage(), 0, $e);
                    }
                    $log('Asaas: assinatura ' . $asaasId . ' já não existia.');
                }
                $total++;
            }

            if ($stripeId !== '') {
                try {
                    (new \LRV\App\Services\Billing\Stripe\StripeCheckoutService())->cancelarAssinatura($stripeId);
                    $log('Stripe: assinatura ' . $stripeId . ' cancelada.');
                } catch (\Throwable $e) {
                    $m = strtolower($e->getMessage());
                    if (!str_contains($m, 'no such subscription') && !str_contains($m, 'canceled') && !str_contains($m, 'resource_missing')) {
                        throw new \RuntimeException('Stripe recusou cancelar a assinatura ' . $stripeId . ': ' . $e->getMessage(), 0, $e);
                    }
                    $log('Stripe: assinatura ' . $stripeId . ' já estava cancelada/inexistente.');
                }
                $total++;
            }

            $pdo->prepare("UPDATE subscriptions SET status = 'CANCELED' WHERE id = :id")->execute([':id' => $id]);
        }
        return $total;
    }

    /** Apaga as caixas de e-mail do cliente no Mailcow. */
    private function removerEmails(int $clientId, callable $log): int
    {
        try {
            $s = BancoDeDados::pdo()->prepare('SELECT id, email FROM client_emails WHERE client_id = :c');
            $s->execute([':c' => $clientId]);
            $emails = $s->fetchAll() ?: [];
        } catch (\Throwable) {
            return 0;
        }
        if (empty($emails)) {
            return 0;
        }

        $mc = new \LRV\App\Services\Email\MailcowService(new ClienteHttp());
        foreach ($emails as $e) {
            try {
                $mc->removerEmail($clientId, (int) $e['id']);
                $log('E-mail removido: ' . $e['email']);
            } catch (\Throwable $ex) {
                throw new \RuntimeException('Falha ao remover o e-mail ' . $e['email'] . ' no servidor de e-mail: ' . $ex->getMessage(), 0, $ex);
            }
        }
        return count($emails);
    }

    /** Apaga os bancos de dados do cliente no servidor (MySQL nativo ou container). */
    private function removerBancosDeDados(int $clientId, callable $log): int
    {
        try {
            $s = BancoDeDados::pdo()->prepare(
                'SELECT cd.id, cd.db_name, cd.db_user, cd.container_id, cd.engine,
                        s.ip_address, s.ssh_port, s.ssh_user, s.ssh_password, s.ssh_auth_type, s.ssh_key_id, s.mysql_root_password
                 FROM client_databases cd
                 JOIN vps v ON v.id = cd.vps_id
                 JOIN servers s ON s.id = v.server_id
                 WHERE cd.client_id = :c'
            );
            $s->execute([':c' => $clientId]);
            $bancos = $s->fetchAll() ?: [];
        } catch (\Throwable $e) {
            $log('Aviso ao listar bancos de dados: ' . $e->getMessage());
            return 0;
        }

        $ident = '/^[A-Za-z0-9_]+$/';
        foreach ($bancos as $db) {
            $dbName = (string) ($db['db_name'] ?? '');
            $dbUser = (string) ($db['db_user'] ?? '');
            $engine = (string) ($db['engine'] ?? 'docker');
            $containerId = trim((string) ($db['container_id'] ?? ''));

            if ($engine !== 'native' && $containerId !== '') {
                if (preg_match('/^[A-Za-z0-9][A-Za-z0-9_.-]+$/', $containerId) !== 1) {
                    throw new \RuntimeException('container_id inválido no banco #' . $db['id'] . '.');
                }
                $cmd = 'docker rm -f ' . escapeshellarg($containerId) . ' 2>&1 || true';
            } else {
                if (preg_match($ident, $dbName) !== 1 || ($dbUser !== '' && preg_match($ident, $dbUser) !== 1)) {
                    throw new \RuntimeException('Nome de banco/usuário inválido no banco #' . $db['id'] . '.');
                }
                $rootEnc = (string) ($db['mysql_root_password'] ?? '');
                $rootPass = $rootEnc !== '' ? \LRV\App\Services\Infra\SshCrypto::decifrar($rootEnc) : '';
                $auth = $rootPass !== '' ? '-u root -p' . escapeshellarg($rootPass) : '-u root';
                $sql = "DROP DATABASE IF EXISTS `{$dbName}`;";
                if ($dbUser !== '') {
                    $sql .= " DROP USER IF EXISTS '{$dbUser}'@'localhost'; DROP USER IF EXISTS '{$dbUser}'@'%';";
                }
                $sql .= ' FLUSH PRIVILEGES;';
                $cmd = 'mysql ' . $auth . ' -e ' . escapeshellarg($sql) . ' 2>&1';
            }

            $r = $this->ssh($db, $cmd);
            if (empty($r['ok'])) {
                throw new \RuntimeException('Falha ao apagar o banco ' . $dbName . ': ' . trim((string) ($r['saida'] ?? '')));
            }
            $log('Banco de dados removido: ' . $dbName);
        }
        return count($bancos);
    }

    private function ssh(array $srv, string $cmd): array
    {
        $exec = new \LRV\App\Services\Infra\SshExecutor();
        $host = (string) ($srv['ip_address'] ?? '');
        $port = (int) ($srv['ssh_port'] ?? 22);
        $user = (string) ($srv['ssh_user'] ?? 'root');
        if ((string) ($srv['ssh_auth_type'] ?? 'password') === 'password') {
            $senha = \LRV\App\Services\Infra\SshCrypto::decifrar((string) ($srv['ssh_password'] ?? ''));
            return $exec->executarComSenha($host, $port, $user, $senha, $cmd, 30);
        }
        $key = \LRV\Core\ConfiguracoesSistema::sshKeyDir() . DIRECTORY_SEPARATOR . (string) ($srv['ssh_key_id'] ?? '');
        return $exec->executar($host, $port, $user, $key, $cmd, 30);
    }

    /** Remove containers de todas as VPS do cliente (inclusive as já marcadas como removidas). */
    private function removerVps(int $clientId, callable $log): int
    {
        $pdo = BancoDeDados::pdo();
        $s = $pdo->prepare('SELECT id, deleted_at FROM vps WHERE client_id = :c ORDER BY id');
        $s->execute([':c' => $clientId]);
        $lista = $s->fetchAll() ?: [];

        $total = 0;
        foreach ($lista as $v) {
            $vpsId = (int) $v['id'];
            $svc = new VpsProvisioningService(new DockerCli());
            if ($v['deleted_at'] === null) {
                $log('Removendo VPS #' . $vpsId . '...');
                $svc->remover($vpsId, $log); // lança exceção se o container não sair
            } else {
                // Já "removida" no banco: confere se o container realmente sumiu do servidor
                $log('Conferindo containers da VPS #' . $vpsId . ' (já marcada como removida)...');
                $svc->garantirContainersRemovidos($vpsId, $log);
            }
            $total++;
        }
        return $total;
    }

    /** Apaga a pasta de dados do cliente em cada node onde ele teve VPS. */
    private function removerDadosNosNodes(int $clientId, callable $log): int
    {
        $pdo = BancoDeDados::pdo();
        $s = $pdo->prepare('SELECT DISTINCT server_id FROM vps WHERE client_id = :c AND server_id IS NOT NULL AND server_id > 0');
        $s->execute([':c' => $clientId]);
        $nodes = array_map('intval', $s->fetchAll(\PDO::FETCH_COLUMN) ?: []);

        foreach ($nodes as $serverId) {
            (new VpsProvisioningService(new DockerCli()))->removerDadosClienteNoNode($clientId, $serverId, $log);
        }
        return count($nodes);
    }

    /** Tira o acesso dos colaboradores nos repositórios GitHub. O repositório em si é mantido. */
    private function removerColaboradoresGit(int $clientId, callable $log): void
    {
        try {
            $pdo = BancoDeDados::pdo();
            $s = $pdo->prepare(
                'SELECT r.full_name, c.username FROM git_repository_collaborators c
                 INNER JOIN git_repositories r ON r.id = c.repository_id
                 WHERE r.client_id = :c'
            );
            $s->execute([':c' => $clientId]);
            $rows = $s->fetchAll() ?: [];
        } catch (\Throwable) {
            return; // tabela inexistente
        }
        if (empty($rows)) {
            return;
        }

        $gh = new \LRV\App\Services\Git\GitHubService();
        if (!$gh->configurado()) {
            $log('Aviso: GitHub não configurado; colaboradores não foram removidos dos repositórios.');
            return;
        }
        foreach ($rows as $r) {
            try {
                $gh->removerColaborador((string) $r['full_name'], (string) $r['username']);
                $log('GitHub: acesso de ' . $r['username'] . ' removido de ' . $r['full_name'] . '.');
            } catch (\Throwable $e) {
                $log('Aviso GitHub (' . $r['full_name'] . '/' . $r['username'] . '): ' . $e->getMessage());
            }
        }
    }

    /** Apaga os arquivos de backup guardados no painel. */
    private function apagarBackupsLocais(int $clientId, callable $log): void
    {
        try {
            $pdo = BancoDeDados::pdo();
            $s = $pdo->prepare('SELECT b.file_path FROM backups b INNER JOIN vps v ON v.id = b.vps_id WHERE v.client_id = :c');
            $s->execute([':c' => $clientId]);
            foreach ($s->fetchAll(\PDO::FETCH_COLUMN) ?: [] as $f) {
                $f = (string) $f;
                if ($f !== '' && is_file($f)) {
                    @unlink($f);
                    $log('Backup apagado: ' . basename($f));
                }
            }
        } catch (\Throwable $e) {
            $log('Aviso ao apagar backups: ' . $e->getMessage());
        }
    }

    /**
     * Apaga todos os registros do cliente em qualquer tabela que tenha client_id
     * (descobertas automaticamente), mais as tabelas "filhas" ligadas por FK.
     * Tudo numa transação: ou apaga tudo, ou nada.
     */
    private function purgarBanco(int $clientId, callable $log): int
    {
        $pdo = BancoDeDados::pdo();
        $pdo->beginTransaction();
        try {
            // Filhas sem client_id (ordem importa)
            $filhas = [
                'DELETE cm FROM chat_messages cm INNER JOIN chat_rooms cr ON cr.id = cm.room_id WHERE cr.client_id = :c',
                'DELETE cp FROM chat_presence cp INNER JOIN chat_rooms cr ON cr.id = cp.room_id WHERE cr.client_id = :c',
                'DELETE tm FROM ticket_messages tm INNER JOIN tickets t ON t.id = tm.ticket_id WHERE t.client_id = :c',
                'DELETE l FROM git_deploy_logs l INNER JOIN git_deployments d ON d.id = l.deployment_id WHERE d.client_id = :c',
                'DELETE d FROM api_webhook_deliveries d INNER JOIN api_webhooks w ON w.id = d.webhook_id WHERE w.client_id = :c',
                'DELETE t FROM api_tokens t INNER JOIN api_keys k ON k.id = t.api_key_id WHERE k.client_id = :c',
                'DELETE gc FROM git_repository_collaborators gc INNER JOIN git_repositories r ON r.id = gc.repository_id WHERE r.client_id = :c',
                'DELETE sc FROM client_terminal_session_commands sc INNER JOIN client_terminal_sessions s ON s.id = sc.session_id WHERE s.client_id = :c',
                'DELETE p FROM ports p INNER JOIN applications a ON a.id = p.application_id INNER JOIN vps v ON v.id = a.vps_id WHERE v.client_id = :c',
                'DELETE a FROM applications a INNER JOIN vps v ON v.id = a.vps_id WHERE v.client_id = :c',
                'DELETE b FROM backups b INNER JOIN vps v ON v.id = b.vps_id WHERE v.client_id = :c',
                'UPDATE subscriptions SET vps_id = NULL WHERE client_id = :c',
                'UPDATE clients SET created_by_client_id = NULL WHERE created_by_client_id = :c',
            ];
            foreach ($filhas as $sql) {
                $this->execTolerante($sql, [':c' => $clientId], $log);
            }
            foreach (self::DESVINCULAR as $t) {
                $this->execTolerante('UPDATE `' . $t . '` SET client_id = NULL WHERE client_id = :c', [':c' => $clientId], $log);
            }

            // Todas as tabelas com coluna client_id
            $st = $pdo->query(
                "SELECT DISTINCT TABLE_NAME FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE() AND COLUMN_NAME = 'client_id'"
            );
            $tabelas = array_values(array_filter(
                $st->fetchAll(\PDO::FETCH_COLUMN) ?: [],
                fn ($t) => preg_match('/^[A-Za-z0-9_]+$/', (string) $t) === 1
                    && !in_array($t, self::MANTER, true)
                    && !in_array($t, self::DESVINCULAR, true)
            ));

            // Várias passadas: a ordem entre tabelas com FK entre si se resolve sozinha
            $pendentes = $tabelas;
            $ultimoErro = [];
            for ($passada = 0; $passada < 6 && !empty($pendentes); $passada++) {
                $restantes = [];
                foreach ($pendentes as $t) {
                    try {
                        $this->apagarComFilhas($t, 'client_id = ?', [$clientId]);
                    } catch (\PDOException $e) {
                        $restantes[] = $t;
                        $ultimoErro[$t] = $e->getMessage();
                    }
                }
                $pendentes = $restantes;
            }
            if (!empty($pendentes)) {
                $det = [];
                foreach ($pendentes as $t) {
                    $det[] = $t . ': ' . ($ultimoErro[$t] ?? '?');
                }
                throw new \RuntimeException('Não foi possível apagar registros em: ' . implode(' | ', $det));
            }

            // Por fim, o próprio cliente
            $this->apagarComFilhas('clients', 'id = ?', [$clientId]);

            $pdo->commit();
            $log('Banco limpo (' . count($tabelas) . ' tabelas verificadas).');
            return count($tabelas);
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * DELETE em $tabela; se uma FK bloquear, apaga antes as linhas das tabelas filhas
     * que apontam para as linhas a remover (um nível) e tenta de novo.
     */
    private function apagarComFilhas(string $tabela, string $where, array $params): void
    {
        $pdo = BancoDeDados::pdo();
        $sql = 'DELETE FROM `' . $tabela . '` WHERE ' . $where;
        try {
            $pdo->prepare($sql)->execute($params);
            return;
        } catch (\PDOException $e) {
            if ((int) ($e->errorInfo[1] ?? 0) !== 1451) {
                throw $e;
            }
        }

        $fk = $pdo->prepare(
            'SELECT TABLE_NAME, COLUMN_NAME, REFERENCED_COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE() AND REFERENCED_TABLE_NAME = :t'
        );
        $fk->execute([':t' => $tabela]);
        foreach ($fk->fetchAll() ?: [] as $r) {
            $filha = (string) $r['TABLE_NAME'];
            $col = (string) $r['COLUMN_NAME'];
            $ref = (string) $r['REFERENCED_COLUMN_NAME'];
            if (in_array($filha, self::MANTER, true) || $filha === $tabela) {
                continue;
            }
            foreach ([$filha, $col, $ref] as $ident) {
                if (preg_match('/^[A-Za-z0-9_]+$/', $ident) !== 1) {
                    continue 2;
                }
            }
            // Tabela derivada evita o erro "can't specify target table" do MySQL
            $pdo->prepare(
                'DELETE FROM `' . $filha . '` WHERE `' . $col . '` IN (SELECT x.`' . $ref . '` FROM (SELECT `' . $ref . '` FROM `' . $tabela . '` WHERE ' . $where . ') x)'
            )->execute($params);
        }

        $pdo->prepare($sql)->execute($params);
    }

    /** Executa ignorando tabela/coluna inexistente (bancos sem todas as migrações). */
    private function execTolerante(string $sql, array $params, callable $log): void
    {
        try {
            BancoDeDados::pdo()->prepare($sql)->execute($params);
        } catch (\PDOException $e) {
            $estado = (string) ($e->errorInfo[0] ?? $e->getCode());
            if ($estado === '42S02' || $estado === '42S22') {
                return;
            }
            throw $e;
        }
    }
}
