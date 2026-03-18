<?php

declare(strict_types=1);

namespace LRV\Core;

final class Rbac
{
    private static array $cachePermissoesPorEquipeId = [];

    public static function permissoesDaEquipe(int $equipeId): array
    {
        if (isset(self::$cachePermissoesPorEquipeId[$equipeId])) {
            return self::$cachePermissoesPorEquipeId[$equipeId];
        }

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare(
            'SELECT p.`key` AS permissao
             FROM users u
             JOIN role_permissions rp ON rp.role = u.role
             JOIN permissions p ON p.id = rp.permission_id
             WHERE u.id = :id'
        );
        $stmt->execute([':id' => $equipeId]);
        $linhas = $stmt->fetchAll();

        $perms = [];
        foreach ($linhas as $l) {
            $k = (string) ($l['permissao'] ?? '');
            if ($k !== '') {
                $perms[$k] = true;
            }
        }

        self::$cachePermissoesPorEquipeId[$equipeId] = $perms;
        return $perms;
    }

    public static function temPermissao(int $equipeId, string $permissao): bool
    {
        $perms = self::permissoesDaEquipe($equipeId);
        return isset($perms[$permissao]);
    }

    public static function limparCache(): void
    {
        self::$cachePermissoesPorEquipeId = [];
    }
}
