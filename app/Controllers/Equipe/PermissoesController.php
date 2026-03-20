<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Equipe;

use LRV\App\Services\Audit\AuditLogService;
use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\Rbac;
use LRV\Core\View;

final class PermissoesController
{
    private const ROLES = ['superadmin', 'admin', 'financeiro', 'devops', 'programador', 'suporte'];

    public function index(Requisicao $req): Resposta
    {
        $pdo = BancoDeDados::pdo();

        $stmt = $pdo->query('SELECT id, `key`, description FROM permissions ORDER BY `key` ASC');
        $permissoes = $stmt->fetchAll();
        $permissoes = is_array($permissoes) ? $permissoes : [];

        // Permissões atuais por role (banco)
        $stmt2 = $pdo->query('SELECT rp.role, p.`key` FROM role_permissions rp INNER JOIN permissions p ON p.id = rp.permission_id');
        $rows = $stmt2->fetchAll();
        $rows = is_array($rows) ? $rows : [];

        $atual = [];
        foreach ($rows as $r) {
            if (!is_array($r)) {
                continue;
            }
            $role = (string) ($r['role'] ?? '');
            $key = (string) ($r['key'] ?? '');
            if ($role !== '' && $key !== '') {
                $atual[$role][$key] = true;
            }
        }

        $html = View::renderizar(__DIR__ . '/../../Views/equipe/permissoes.php', [
            'roles' => self::ROLES,
            'permissoes' => $permissoes,
            'atual' => $atual,
            'ok' => '',
            'erro' => '',
        ]);

        return Resposta::html($html);
    }

    public function salvar(Requisicao $req): Resposta
    {
        $pdo = BancoDeDados::pdo();

        $stmt = $pdo->query('SELECT id, `key` FROM permissions');
        $permissoes = $stmt->fetchAll();
        $permissoes = is_array($permissoes) ? $permissoes : [];

        $permMap = [];
        foreach ($permissoes as $p) {
            if (!is_array($p)) {
                continue;
            }
            $permMap[(string) ($p['key'] ?? '')] = (int) ($p['id'] ?? 0);
        }

        $novas = [];
        foreach (self::ROLES as $role) {
            $novas[$role] = [];
            foreach ($permMap as $key => $permId) {
                $campo = 'perm_' . $role . '_' . $key;
                if (!empty($req->post[$campo])) {
                    $novas[$role][$key] = $permId;
                }
            }
        }

        $pdo->beginTransaction();
        try {
            foreach (self::ROLES as $role) {
                $pdo->prepare('DELETE FROM role_permissions WHERE role = :r')->execute([':r' => $role]);
                $ins = $pdo->prepare('INSERT INTO role_permissions (role, permission_id) VALUES (:r, :p)');
                foreach ($novas[$role] as $key => $permId) {
                    if ($permId > 0) {
                        $ins->execute([':r' => $role, ':p' => $permId]);
                    }
                }
            }
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            return Resposta::texto('Erro ao salvar permissões.', 500);
        }

        Rbac::limparCache();

        (new AuditLogService())->registrar(
            'team',
            \LRV\Core\Auth::equipeId(),
            'permissions.update',
            'role_permissions',
            null,
            ['roles_updated' => self::ROLES],
            $req,
        );

        // Recarregar atual
        $stmt2 = $pdo->query('SELECT rp.role, p.`key` FROM role_permissions rp INNER JOIN permissions p ON p.id = rp.permission_id');
        $rows = $stmt2->fetchAll();
        $rows = is_array($rows) ? $rows : [];
        $atual = [];
        foreach ($rows as $r) {
            if (!is_array($r)) {
                continue;
            }
            $role = (string) ($r['role'] ?? '');
            $key = (string) ($r['key'] ?? '');
            if ($role !== '' && $key !== '') {
                $atual[$role][$key] = true;
            }
        }

        $html = View::renderizar(__DIR__ . '/../../Views/equipe/permissoes.php', [
            'roles' => self::ROLES,
            'permissoes' => $permissoes,
            'atual' => $atual,
            'ok' => 'Permissões salvas com sucesso.',
            'erro' => '',
        ]);

        return Resposta::html($html);
    }
}
