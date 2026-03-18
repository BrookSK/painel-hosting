<?php

declare(strict_types=1);

namespace LRV\Core;

final class InicializadorSistema
{
    public static function garantirDadosBase(): void
    {
        try {
            $pdo = BancoDeDados::pdo();
        } catch (\Throwable $e) {
            return;
        }

        try {
            $stmt = $pdo->query('SELECT COUNT(*) AS total FROM permissions');
            $r = $stmt->fetch();
            $total = (int) ($r['total'] ?? 0);
        } catch (\Throwable $e) {
            return;
        }

        if ($total > 0) {
            return;
        }

        $permissoes = [
            ['key' => 'view_tickets', 'description' => 'Ver tickets'],
            ['key' => 'reply_tickets', 'description' => 'Responder tickets'],
            ['key' => 'close_tickets', 'description' => 'Fechar tickets'],
            ['key' => 'manage_users', 'description' => 'Gerenciar usuários internos'],
            ['key' => 'manage_billing', 'description' => 'Gerenciar cobranças e assinaturas'],
            ['key' => 'manage_servers', 'description' => 'Gerenciar servidores/nodes'],
            ['key' => 'manage_vps', 'description' => 'Gerenciar VPS'],
            ['key' => 'view_reports', 'description' => 'Ver relatórios'],
        ];

        $pdo->beginTransaction();
        try {
            $ins = $pdo->prepare('INSERT INTO permissions (`key`, description) VALUES (:k, :d)');
            foreach ($permissoes as $p) {
                $ins->execute([':k' => $p['key'], ':d' => $p['description']]);
            }

            $stmt = $pdo->query('SELECT id, `key` FROM permissions');
            $map = [];
            foreach ($stmt->fetchAll() as $l) {
                $map[(string) $l['key']] = (int) $l['id'];
            }

            $roles = [
                'superadmin' => array_keys($map),
                'admin' => ['view_tickets', 'reply_tickets', 'close_tickets', 'manage_vps', 'view_reports'],
                'financeiro' => ['manage_billing', 'view_reports'],
                'devops' => ['manage_servers', 'manage_vps', 'view_reports'],
                'programador' => ['manage_vps'],
                'suporte' => ['view_tickets', 'reply_tickets', 'close_tickets'],
            ];

            $insRp = $pdo->prepare('INSERT INTO role_permissions (role, permission_id) VALUES (:r, :pid)');
            foreach ($roles as $role => $perms) {
                foreach ($perms as $k) {
                    if (!isset($map[$k])) {
                        continue;
                    }
                    $insRp->execute([':r' => $role, ':pid' => $map[$k]]);
                }
            }

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            return;
        }
    }
}
