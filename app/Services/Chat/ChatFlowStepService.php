<?php

declare(strict_types=1);

namespace LRV\App\Services\Chat;

use LRV\Core\BancoDeDados;

final class ChatFlowStepService
{
    public function listarPorFlow(int $flowId): array
    {
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT * FROM chat_flow_steps WHERE flow_id = :f ORDER BY sort_order ASC');
        $stmt->execute([':f' => $flowId]);
        return $stmt->fetchAll() ?: [];
    }

    public function criar(int $flowId, string $stepType, ?string $content, ?int $delaySeconds, ?string $actionType): int
    {
        $this->validar($stepType, $content, $delaySeconds, $actionType);

        $pdo = BancoDeDados::pdo();
        // Next sort_order
        $stmt = $pdo->prepare('SELECT COALESCE(MAX(sort_order), 0) + 1 AS next_order FROM chat_flow_steps WHERE flow_id = :f');
        $stmt->execute([':f' => $flowId]);
        $nextOrder = (int) ($stmt->fetch()['next_order'] ?? 1);

        $ins = $pdo->prepare(
            'INSERT INTO chat_flow_steps (flow_id, sort_order, step_type, content, delay_seconds, action_type, created_at)
             VALUES (:f, :o, :t, :c, :d, :a, :cr)'
        );
        $ins->execute([
            ':f'  => $flowId,
            ':o'  => $nextOrder,
            ':t'  => $stepType,
            ':c'  => $content,
            ':d'  => $delaySeconds,
            ':a'  => $actionType,
            ':cr' => date('Y-m-d H:i:s'),
        ]);
        return (int) $pdo->lastInsertId();
    }

    public function atualizar(int $id, string $stepType, ?string $content, ?int $delaySeconds, ?string $actionType): void
    {
        $this->validar($stepType, $content, $delaySeconds, $actionType);

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare(
            'UPDATE chat_flow_steps SET step_type = :t, content = :c, delay_seconds = :d, action_type = :a WHERE id = :id'
        );
        $stmt->execute([
            ':t'  => $stepType,
            ':c'  => $content,
            ':d'  => $delaySeconds,
            ':a'  => $actionType,
            ':id' => $id,
        ]);
    }

    /** Delete step and re-sequence remaining */
    public function remover(int $id): void
    {
        $pdo = BancoDeDados::pdo();

        $stmt = $pdo->prepare('SELECT flow_id FROM chat_flow_steps WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        if (!is_array($row)) {
            return;
        }
        $flowId = (int) $row['flow_id'];

        $pdo->prepare('DELETE FROM chat_flow_steps WHERE id = :id')->execute([':id' => $id]);
        $this->resequenciar($flowId);
    }

    /** Reorder steps in a single transaction */
    public function reordenar(int $flowId, array $orderedIds): void
    {
        $pdo = BancoDeDados::pdo();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('UPDATE chat_flow_steps SET sort_order = :o WHERE id = :id AND flow_id = :f');
            $order = 1;
            foreach ($orderedIds as $stepId) {
                $stmt->execute([':o' => $order, ':id' => (int) $stepId, ':f' => $flowId]);
                $order++;
            }
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function contarPorFlow(int $flowId): int
    {
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT COUNT(*) AS cnt FROM chat_flow_steps WHERE flow_id = :f');
        $stmt->execute([':f' => $flowId]);
        return (int) ($stmt->fetch()['cnt'] ?? 0);
    }

    private function resequenciar(int $flowId): void
    {
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT id FROM chat_flow_steps WHERE flow_id = :f ORDER BY sort_order ASC');
        $stmt->execute([':f' => $flowId]);
        $rows = $stmt->fetchAll() ?: [];

        $upd = $pdo->prepare('UPDATE chat_flow_steps SET sort_order = :o WHERE id = :id');
        $order = 1;
        foreach ($rows as $row) {
            $upd->execute([':o' => $order, ':id' => (int) $row['id']]);
            $order++;
        }
    }

    private function validar(string $stepType, ?string $content, ?int $delaySeconds, ?string $actionType): void
    {
        if (!in_array($stepType, ['message', 'delay', 'action'], true)) {
            throw new \InvalidArgumentException('Tipo de passo inválido.');
        }

        if ($stepType === 'message') {
            if ($content === null || trim($content) === '' || mb_strlen($content) > 1000) {
                throw new \InvalidArgumentException('Conteúdo da mensagem é obrigatório (máx. 1000 caracteres).');
            }
        }

        if ($stepType === 'delay') {
            if ($delaySeconds === null || $delaySeconds < 1 || $delaySeconds > 3600) {
                throw new \InvalidArgumentException('Atraso deve ser entre 1 e 3600 segundos.');
            }
        }

        if ($stepType === 'action') {
            if (!in_array($actionType, ['close_chat', 'send_satisfaction_link'], true)) {
                throw new \InvalidArgumentException('Tipo de ação inválido.');
            }
        }
    }
}
