<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Equipe;

use LRV\App\Services\Audit\AuditLogService;
use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\Jobs\RepositorioJobs;
use LRV\Core\View;

final class ClientesController
{
    public function listar(Requisicao $req): Resposta
    {
        $pdo    = BancoDeDados::pdo();
        $busca  = trim((string)($req->query['q'] ?? ''));
        $showHidden = (string)($req->query['hidden'] ?? '') === '1';
        $hiddenFilter = $showHidden ? '' : ' AND (c.hidden_at IS NULL)';

        $sortMap = [
            'nome'       => 'c.name ASC',
            'cadastro'   => 'c.created_at DESC',
            'atividade'  => 'c.last_login_at DESC',
            'vps'        => 'total_vps DESC',
            'assinaturas'=> 'assinaturas_ativas DESC, total_assinaturas DESC',
            'recursos'   => 'total_cpu DESC, total_ram DESC',
        ];
        $sort = trim((string)($req->query['sort'] ?? ''));
        $orderBy = $sortMap[$sort] ?? 'c.id DESC';

        $baseSelect = "SELECT c.id, c.name, c.email, c.mobile_phone, c.created_at, c.hidden_at, c.last_login_at,
                        COUNT(DISTINCT v.id) AS total_vps,
                        COALESCE(SUM(DISTINCT v.cpu), 0) AS total_cpu,
                        COALESCE(SUM(DISTINCT v.ram), 0) AS total_ram,
                        COUNT(DISTINCT sub.id) AS total_assinaturas,
                        SUM(CASE WHEN sub.status IN ('active','ACTIVE') THEN 1 ELSE 0 END) AS assinaturas_ativas
                 FROM clients c
                 LEFT JOIN vps v ON v.client_id = c.id
                 LEFT JOIN subscriptions sub ON sub.client_id = c.id";

        if ($busca !== '') {
            $s = $pdo->prepare(
                "{$baseSelect}
                 WHERE (c.name LIKE :q OR c.email LIKE :q){$hiddenFilter}
                 GROUP BY c.id ORDER BY {$orderBy} LIMIT 200"
            );
            $s->execute([':q' => '%' . $busca . '%']);
        } else {
            $s = $pdo->query(
                "{$baseSelect}
                 WHERE 1=1{$hiddenFilter}
                 GROUP BY c.id ORDER BY {$orderBy} LIMIT 500"
            );
        }

        $clientes = $s->fetchAll();

        return Resposta::html(View::renderizar(__DIR__ . '/../../Views/equipe/clientes-listar.php', [
            'clientes'    => is_array($clientes) ? $clientes : [],
            'busca'       => $busca,
            'showHidden'  => $showHidden,
            'sort'        => $sort,
        ]));
    }

    public function novo(Requisicao $req): Resposta
    {
        return Resposta::html(View::renderizar(__DIR__ . '/../../Views/equipe/cliente-editar.php', [
            'cliente' => ['id' => null, 'name' => '', 'email' => '', 'phone' => '', 'cpf_cnpj' => ''],
            'planos'  => $this->planos(),
            'ok'      => '',
            'erro'    => '',
        ]));
    }

    public function editar(Requisicao $req): Resposta
    {
        $id = (int)($req->query['id'] ?? 0);
        if ($id <= 0) return Resposta::redirecionar('/equipe/clientes');

        $pdo = BancoDeDados::pdo();
        $s   = $pdo->prepare('SELECT id, name, email, phone, cpf_cnpj FROM clients WHERE id = :id');
        $s->execute([':id' => $id]);
        $cliente = $s->fetch();
        if (!is_array($cliente)) return Resposta::texto('Cliente não encontrado.', 404);

        return Resposta::html(View::renderizar(__DIR__ . '/../../Views/equipe/cliente-editar.php', [
            'cliente' => $cliente,
            'planos'  => $this->planos(),
            'ok'      => '',
            'erro'    => '',
        ]));
    }

    public function ver(Requisicao $req): Resposta
    {
        $id = (int)($req->query['id'] ?? 0);
        if ($id <= 0) return Resposta::redirecionar('/equipe/clientes');

        $pdo = BancoDeDados::pdo();
        $s   = $pdo->prepare('SELECT * FROM clients WHERE id = :id');
        $s->execute([':id' => $id]);
        $cliente = $s->fetch();
        if (!is_array($cliente)) return Resposta::texto('Cliente não encontrado.', 404);

        $vps = $pdo->prepare(
            "SELECT v.id, v.container_id, v.status, v.cpu, v.ram, v.storage, p.name AS plan_name
             FROM vps v LEFT JOIN plans p ON p.id = v.plan_id
             WHERE v.client_id = :id ORDER BY v.id DESC"
        );
        $vps->execute([':id' => $id]);

        $subs = $pdo->prepare(
            "SELECT s.id, s.status, s.next_due_date, s.created_at, p.name AS plan_name, p.price_monthly
             FROM subscriptions s LEFT JOIN plans p ON p.id = s.plan_id
             WHERE s.client_id = :id ORDER BY s.id DESC"
        );
        $subs->execute([':id' => $id]);

        return Resposta::html(View::renderizar(__DIR__ . '/../../Views/equipe/cliente-ver.php', [
            'cliente'      => $cliente,
            'vps'          => $vps->fetchAll() ?: [],
            'assinaturas'  => $subs->fetchAll() ?: [],
            'planos'       => $this->planos(),
            'ok'           => '',
            'erro'         => '',
        ]));
    }

    public function salvar(Requisicao $req): Resposta
    {
        $id     = (int)($req->post['id'] ?? 0);

        // Toggle tester flag (from cliente-ver page)
        if (!empty($req->post['toggle_tester']) && $id > 0) {
            $pdo = BancoDeDados::pdo();
            $pdo->prepare('UPDATE clients SET is_tester = IF(is_tester = 1, 0, 1) WHERE id = :id')->execute([':id' => $id]);
            return Resposta::redirecionar('/equipe/clientes/ver?id=' . $id);
        }

        $nome   = trim((string)($req->post['name'] ?? ''));
        $email  = trim(strtolower((string)($req->post['email'] ?? '')));
        $phone  = trim((string)($req->post['phone'] ?? ''));
        $cpf    = trim((string)($req->post['cpf_cnpj'] ?? ''));
        $senha  = (string)($req->post['password'] ?? '');

        if ($nome === '' || $email === '') {
            return $this->renderErro($id, $nome, $email, $phone, $cpf, 'Nome e e-mail são obrigatórios.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->renderErro($id, $nome, $email, $phone, $cpf, 'E-mail inválido.');
        }
        if ($id <= 0 && $senha === '') {
            return $this->renderErro($id, $nome, $email, $phone, $cpf, 'Informe uma senha para o novo cliente.');
        }

        $pdo = BancoDeDados::pdo();

        // Verificar e-mail duplicado
        $dup = $pdo->prepare('SELECT id FROM clients WHERE email = :e AND id != :id');
        $dup->execute([':e' => $email, ':id' => $id ?: 0]);
        if ($dup->fetchColumn()) {
            return $this->renderErro($id, $nome, $email, $phone, $cpf, 'Este e-mail já está cadastrado.');
        }

        try {
            if ($id > 0) {
                if ($senha !== '') {
                    $pdo->prepare('UPDATE clients SET name=:n, email=:e, phone=:p, cpf_cnpj=:c, password=:pw WHERE id=:id')
                        ->execute([':n' => $nome, ':e' => $email, ':p' => $phone, ':c' => $cpf, ':pw' => password_hash($senha, PASSWORD_BCRYPT), ':id' => $id]);
                } else {
                    $pdo->prepare('UPDATE clients SET name=:n, email=:e, phone=:p, cpf_cnpj=:c WHERE id=:id')
                        ->execute([':n' => $nome, ':e' => $email, ':p' => $phone, ':c' => $cpf, ':id' => $id]);
                }
            } else {
                $pdo->prepare('INSERT INTO clients (name, email, phone, cpf_cnpj, password, created_at) VALUES (:n,:e,:p,:c,:pw,:dt)')
                    ->execute([':n' => $nome, ':e' => $email, ':p' => $phone, ':c' => $cpf, ':pw' => password_hash($senha, PASSWORD_BCRYPT), ':dt' => date('Y-m-d H:i:s')]);
                $id = (int)$pdo->lastInsertId();
            }
        } catch (\Throwable) {
            return $this->renderErro($id, $nome, $email, $phone, $cpf, 'Erro ao salvar. Verifique os dados.');
        }

        (new AuditLogService())->registrar('team', \LRV\Core\Auth::equipeId(),
            $id > 0 ? 'client.update' : 'client.create', 'client', $id,
            ['email' => $email], $req);

        return Resposta::redirecionar('/equipe/clientes/ver?id=' . $id);
    }

    public function assinarPlano(Requisicao $req): Resposta
    {
        $clienteId = (int)($req->post['client_id'] ?? 0);
        $planoId   = (int)($req->post['plan_id'] ?? 0);

        if ($clienteId <= 0 || $planoId <= 0) {
            return Resposta::texto('Dados inválidos.', 400);
        }

        $pdo = BancoDeDados::pdo();

        $c = $pdo->prepare('SELECT id FROM clients WHERE id = :id');
        $c->execute([':id' => $clienteId]);
        if (!$c->fetch()) return Resposta::texto('Cliente não encontrado.', 404);

        $p = $pdo->prepare("SELECT id, name, cpu, ram, storage FROM plans WHERE id = :id AND status = 'active'");
        $p->execute([':id' => $planoId]);
        $plano = $p->fetch();
        if (!is_array($plano)) return Resposta::texto('Plano inválido.', 404);

        $agora = date('Y-m-d H:i:s');

        $pdo->beginTransaction();
        try {
            // Criar VPS
            $insVps = $pdo->prepare('INSERT INTO vps (client_id, server_id, container_id, cpu, ram, storage, status, created_at, plan_id) VALUES (:c, NULL, NULL, :cpu, :ram, :st, :s, :cr, :pid)');
            $insVps->execute([
                ':c'   => $clienteId,
                ':cpu' => (int)$plano['cpu'],
                ':ram' => (int)$plano['ram'],
                ':st'  => (int)$plano['storage'],
                ':s'   => 'pending_provisioning',
                ':cr'  => $agora,
                ':pid' => $planoId,
            ]);
            $vpsId = (int)$pdo->lastInsertId();

            // Criar assinatura gratuita (status active, sem gateway)
            $insSub = $pdo->prepare('INSERT INTO subscriptions (client_id, vps_id, plan_id, status, next_due_date, created_at) VALUES (:c, :v, :p, :s, :n, :cr)');
            $insSub->execute([
                ':c'  => $clienteId,
                ':v'  => $vpsId,
                ':p'  => $planoId,
                ':s'  => 'active',
                ':n'  => null,
                ':cr' => $agora,
            ]);
            $subId = (int)$pdo->lastInsertId();

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            return Resposta::texto('Erro ao dar plano: ' . $e->getMessage(), 500);
        }

        // Enfileirar provisionamento automático
        try {
            (new RepositorioJobs())->criar('provisionar_vps', ['vps_id' => $vpsId]);
        } catch (\Throwable) {}

        (new AuditLogService())->registrar('team', \LRV\Core\Auth::equipeId(),
            'client.grant_plan', 'subscription', $subId,
            ['client_id' => $clienteId, 'plan_id' => $planoId, 'vps_id' => $vpsId, 'free' => true], $req);

        return Resposta::redirecionar('/equipe/clientes/ver?id=' . $clienteId . '&ok=assinatura_criada');
    }

    // ── helpers ─────────────────────────────────────────────
    private function planos(): array
    {
        try {
            $s = BancoDeDados::pdo()->query("SELECT id, name, price_monthly, cpu, ram, storage FROM plans WHERE status = 'active' ORDER BY price_monthly ASC");
            return $s->fetchAll() ?: [];
        } catch (\Throwable) {
            try {
                $s = BancoDeDados::pdo()->query("SELECT id, name, price_monthly, cpu, ram, storage FROM plans WHERE active = 1 ORDER BY price_monthly ASC");
                return $s->fetchAll() ?: [];
            } catch (\Throwable) { return []; }
        }
    }

    private function renderErro(int $id, string $nome, string $email, string $phone, string $cpf, string $erro): Resposta
    {
        return Resposta::html(View::renderizar(__DIR__ . '/../../Views/equipe/cliente-editar.php', [
            'cliente' => ['id' => $id ?: null, 'name' => $nome, 'email' => $email, 'phone' => $phone, 'cpf_cnpj' => $cpf],
            'planos'  => $this->planos(),
            'ok'      => '',
            'erro'    => $erro,
        ]), 422);
    }

    public function ocultar(Requisicao $req): Resposta
    {
        $id = (int)($req->post['id'] ?? 0);
        if ($id <= 0) {
            return Resposta::texto('ID inválido.', 400);
        }

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT id, hidden_at FROM clients WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $c = $stmt->fetch();
        if (!is_array($c)) {
            return Resposta::texto('Cliente não encontrado.', 404);
        }

        $isHidden = ($c['hidden_at'] ?? null) !== null;
        if ($isHidden) {
            $pdo->prepare('UPDATE clients SET hidden_at = NULL WHERE id = :id')->execute([':id' => $id]);
        } else {
            $pdo->prepare('UPDATE clients SET hidden_at = :h WHERE id = :id')->execute([':h' => date('Y-m-d H:i:s'), ':id' => $id]);
        }

        return Resposta::redirecionar('/equipe/clientes/ver?id=' . $id);
    }

    public function deletar(Requisicao $req): Resposta
    {
        $id = (int)($req->post['id'] ?? 0);
        if ($id <= 0) {
            return Resposta::texto('ID inválido.', 400);
        }

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT id FROM clients WHERE id = :id');
        $stmt->execute([':id' => $id]);
        if (!$stmt->fetch()) {
            return Resposta::texto('Cliente não encontrado.', 404);
        }

        $pdo->beginTransaction();
        try {
            // Deletar em ordem de dependência (filhos primeiro)

            // Terminal sessions e tokens
            $pdo->prepare('DELETE FROM client_terminal_sessions WHERE client_id = :c')->execute([':c' => $id]);
            $pdo->prepare('DELETE FROM client_terminal_tokens WHERE client_id = :c')->execute([':c' => $id]);

            // Chat: messages → rooms → tokens
            $pdo->exec("DELETE cm FROM chat_messages cm INNER JOIN chat_rooms cr ON cr.id = cm.room_id WHERE cr.client_id = {$id}");
            $pdo->prepare('DELETE FROM chat_tokens WHERE client_id = :c')->execute([':c' => $id]);
            $pdo->prepare('DELETE FROM chat_rooms WHERE client_id = :c')->execute([':c' => $id]);

            // Satisfaction surveys
            $pdo->prepare('DELETE FROM satisfaction_surveys WHERE client_id = :c')->execute([':c' => $id]);

            // Tickets: messages → tickets
            $pdo->exec("DELETE tm FROM ticket_messages tm INNER JOIN tickets t ON t.id = tm.ticket_id WHERE t.client_id = {$id}");
            $pdo->prepare('DELETE FROM tickets WHERE client_id = :c')->execute([':c' => $id]);

            // Emails e domínios
            $pdo->prepare('DELETE FROM client_emails WHERE client_id = :c')->execute([':c' => $id]);
            $pdo->prepare('DELETE FROM client_domains WHERE client_id = :c')->execute([':c' => $id]);

            // Git deployments e databases
            $pdo->prepare('DELETE FROM git_deployments WHERE client_id = :c')->execute([':c' => $id]);
            $pdo->prepare('DELETE FROM client_databases WHERE client_id = :c')->execute([':c' => $id]);

            // Notificações
            $pdo->prepare('DELETE FROM client_notifications WHERE client_id = :c')->execute([':c' => $id]);

            // TOTP
            $pdo->prepare('DELETE FROM client_totp WHERE client_id = :c')->execute([':c' => $id]);

            // Trials
            $pdo->prepare('DELETE FROM client_trials WHERE client_id = :c')->execute([':c' => $id]);

            // Status services (nullable client_id)
            $pdo->prepare('UPDATE status_services SET client_id = NULL WHERE client_id = :c')->execute([':c' => $id]);

            // Subscriptions (depende de vps)
            $pdo->prepare('DELETE FROM subscriptions WHERE client_id = :c')->execute([':c' => $id]);

            // VPS: applications → ports → vps
            $vpsIds = $pdo->prepare('SELECT id FROM vps WHERE client_id = :c');
            $vpsIds->execute([':c' => $id]);
            $vIds = $vpsIds->fetchAll(\PDO::FETCH_COLUMN) ?: [];
            if (!empty($vIds)) {
                $ph = implode(',', $vIds);
                $pdo->exec("DELETE FROM ports WHERE application_id IN (SELECT id FROM applications WHERE vps_id IN ({$ph}))");
                $pdo->exec("DELETE FROM applications WHERE vps_id IN ({$ph})");
            }
            $pdo->prepare('DELETE FROM vps WHERE client_id = :c')->execute([':c' => $id]);

            // Audit logs
            $pdo->prepare("DELETE FROM audit_logs WHERE actor_type = 'client' AND actor_id = :c")->execute([':c' => $id]);

            // Finalmente, o cliente
            $pdo->prepare('DELETE FROM clients WHERE id = :c')->execute([':c' => $id]);

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            return Resposta::texto('Erro ao deletar: ' . $e->getMessage(), 500);
        }

        return Resposta::redirecionar('/equipe/clientes');
    }

}
