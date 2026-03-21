<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Equipe;

use LRV\App\Services\Audit\AuditLogService;
use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;

final class ClientesController
{
    public function listar(Requisicao $req): Resposta
    {
        $pdo    = BancoDeDados::pdo();
        $busca  = trim((string)($req->query['q'] ?? ''));

        if ($busca !== '') {
            $s = $pdo->prepare(
                "SELECT c.id, c.name, c.email, c.phone, c.created_at,
                        COUNT(DISTINCT v.id) AS total_vps,
                        COUNT(DISTINCT sub.id) AS total_assinaturas,
                        SUM(CASE WHEN sub.status='active' THEN 1 ELSE 0 END) AS assinaturas_ativas
                 FROM clients c
                 LEFT JOIN vps v ON v.client_id = c.id AND v.deleted_at IS NULL
                 LEFT JOIN subscriptions sub ON sub.client_id = c.id
                 WHERE c.name LIKE :q OR c.email LIKE :q
                 GROUP BY c.id ORDER BY c.id DESC LIMIT 200"
            );
            $s->execute([':q' => '%' . $busca . '%']);
        } else {
            $s = $pdo->query(
                "SELECT c.id, c.name, c.email, c.phone, c.created_at,
                        COUNT(DISTINCT v.id) AS total_vps,
                        COUNT(DISTINCT sub.id) AS total_assinaturas,
                        SUM(CASE WHEN sub.status='active' THEN 1 ELSE 0 END) AS assinaturas_ativas
                 FROM clients c
                 LEFT JOIN vps v ON v.client_id = c.id AND v.deleted_at IS NULL
                 LEFT JOIN subscriptions sub ON sub.client_id = c.id
                 GROUP BY c.id ORDER BY c.id DESC LIMIT 500"
            );
        }

        $clientes = $s->fetchAll();

        return Resposta::html(View::renderizar(__DIR__ . '/../../Views/equipe/clientes-listar.php', [
            'clientes' => is_array($clientes) ? $clientes : [],
            'busca'    => $busca,
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
            "SELECT v.id, v.hostname, v.status, v.ip_address, p.name AS plan_name
             FROM vps v LEFT JOIN plans p ON p.id = v.plan_id
             WHERE v.client_id = :id AND v.deleted_at IS NULL ORDER BY v.id DESC"
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
            return Resposta::json(['ok' => false, 'erro' => 'Dados inválidos.'], 400);
        }

        $pdo = BancoDeDados::pdo();

        $c = $pdo->prepare('SELECT id FROM clients WHERE id = :id');
        $c->execute([':id' => $clienteId]);
        if (!$c->fetch()) return Resposta::json(['ok' => false, 'erro' => 'Cliente não encontrado.'], 404);

        $p = $pdo->prepare('SELECT id FROM plans WHERE id = :id AND active = 1');
        $p->execute([':id' => $planoId]);
        if (!$p->fetch()) return Resposta::json(['ok' => false, 'erro' => 'Plano inválido.'], 404);

        $pdo->prepare(
            'INSERT INTO subscriptions (client_id, plan_id, status, created_at) VALUES (:c, :p, :s, :dt)'
        )->execute([':c' => $clienteId, ':p' => $planoId, ':s' => 'active', ':dt' => date('Y-m-d H:i:s')]);

        (new AuditLogService())->registrar('team', \LRV\Core\Auth::equipeId(),
            'client.subscribe', 'subscription', (int)$pdo->lastInsertId(),
            ['client_id' => $clienteId, 'plan_id' => $planoId], $req);

        return Resposta::redirecionar('/equipe/clientes/ver?id=' . $clienteId . '&ok=assinatura_criada');
    }

    // ── helpers ─────────────────────────────────────────────
    private function planos(): array
    {
        try {
            $s = BancoDeDados::pdo()->query('SELECT id, name, price_monthly FROM plans WHERE active = 1 ORDER BY price_monthly ASC');
            return $s->fetchAll() ?: [];
        } catch (\Throwable) { return []; }
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
}
