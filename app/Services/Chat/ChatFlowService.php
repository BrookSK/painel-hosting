<?php

declare(strict_types=1);

namespace LRV\App\Services\Chat;

use LRV\Core\BancoDeDados;

final class ChatFlowService
{
    /** List all active flows */
    public function listar(): array
    {
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->query(
            'SELECT f.*, (SELECT COUNT(*) FROM chat_flow_steps s WHERE s.flow_id = f.id) AS step_count
             FROM chat_flows f WHERE f.active = 1 ORDER BY f.created_at DESC'
        );
        return $stmt->fetchAll() ?: [];
    }

    public function buscarPorId(int $id): ?array
    {
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare(
            'SELECT f.*, (SELECT COUNT(*) FROM chat_flow_steps s WHERE s.flow_id = f.id) AS step_count
             FROM chat_flows f WHERE f.id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $r = $stmt->fetch();
        return is_array($r) ? $r : null;
    }

    public function criar(string $name, string $description, string $triggerType): int
    {
        $this->validar($name, $triggerType);

        $pdo = BancoDeDados::pdo();
        $agora = date('Y-m-d H:i:s');
        $stmt = $pdo->prepare(
            'INSERT INTO chat_flows (name, description, trigger_type, active, created_at, updated_at)
             VALUES (:n, :d, :t, 1, :c, :u)'
        );
        $stmt->execute([
            ':n' => $name,
            ':d' => $description !== '' ? $description : null,
            ':t' => $triggerType,
            ':c' => $agora,
            ':u' => $agora,
        ]);
        return (int) $pdo->lastInsertId();
    }

    public function atualizar(int $id, string $name, string $description, string $triggerType, int $active): void
    {
        $this->validar($name, $triggerType);

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare(
            'UPDATE chat_flows SET name = :n, description = :d, trigger_type = :t, active = :a, updated_at = :u WHERE id = :id'
        );
        $stmt->execute([
            ':n'  => $name,
            ':d'  => $description !== '' ? $description : null,
            ':t'  => $triggerType,
            ':a'  => $active,
            ':u'  => date('Y-m-d H:i:s'),
            ':id' => $id,
        ]);
    }

    /** Soft-delete: set active = 0 */
    public function desativar(int $id): void
    {
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('UPDATE chat_flows SET active = 0, updated_at = :u WHERE id = :id');
        $stmt->execute([':u' => date('Y-m-d H:i:s'), ':id' => $id]);
    }

    /** List active flows by trigger type */
    public function listarPorTrigger(string $triggerType): array
    {
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare(
            'SELECT f.*, (SELECT COUNT(*) FROM chat_flow_steps s WHERE s.flow_id = f.id) AS step_count
             FROM chat_flows f WHERE f.active = 1 AND f.trigger_type = :t ORDER BY f.name ASC'
        );
        $stmt->execute([':t' => $triggerType]);
        return $stmt->fetchAll() ?: [];
    }

    private function validar(string $name, string $triggerType): void
    {
        if ($name === '' || mb_strlen($name) > 100) {
            throw new \InvalidArgumentException('Nome do fluxo é obrigatório (máx. 100 caracteres).');
        }
        if (!in_array($triggerType, ['client_inactive', 'chat_closed', 'manual'], true)) {
            throw new \InvalidArgumentException('Tipo de gatilho inválido.');
        }
    }
}
