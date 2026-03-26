<?php

declare(strict_types=1);

namespace LRV\App\Services\Chat;

use LRV\Core\BancoDeDados;
use LRV\Core\Settings;

final class ChatFlowExecutionService
{
    public function iniciar(int $flowId, int $roomId, string $triggerSource): int
    {
        $pdo = BancoDeDados::pdo();
        $agora = date('Y-m-d H:i:s');
        $stmt = $pdo->prepare(
            'INSERT INTO chat_flow_executions (flow_id, room_id, trigger_source, current_step, status, started_at)
             VALUES (:f, :r, :ts, 0, \'running\', :s)'
        );
        $stmt->execute([
            ':f'  => $flowId,
            ':r'  => $roomId,
            ':ts' => $triggerSource,
            ':s'  => $agora,
        ]);
        $execId = (int) $pdo->lastInsertId();

        // Process first step immediately
        try {
            $this->processarProximoPasso($execId);
        } catch (\Throwable $e) {
            $this->marcarFalha($execId);
            throw $e;
        }

        return $execId;
    }

    public function processarProximoPasso(int $executionId): void
    {
        $pdo = BancoDeDados::pdo();

        $exec = $this->buscarPorId($executionId);
        if ($exec === null || $exec['status'] !== 'running') {
            return;
        }

        $flowId = (int) $exec['flow_id'];
        $roomId = (int) $exec['room_id'];
        $currentStep = (int) $exec['current_step'];
        $nextStep = $currentStep + 1;

        // Get the next step
        $stmt = $pdo->prepare(
            'SELECT * FROM chat_flow_steps WHERE flow_id = :f AND sort_order = :o LIMIT 1'
        );
        $stmt->execute([':f' => $flowId, ':o' => $nextStep]);
        $step = $stmt->fetch();

        if (!is_array($step)) {
            // No more steps — mark completed
            $this->marcarCompleto($executionId);
            return;
        }

        try {
            $stepType = (string) $step['step_type'];

            if ($stepType === 'message') {
                $content = (string) ($step['content'] ?? '');
                $this->inserirMensagemSistema($roomId, $content);
            } elseif ($stepType === 'delay') {
                $delaySec = (int) ($step['delay_seconds'] ?? 0);
                $nextRunAt = date('Y-m-d H:i:s', time() + $delaySec);
                $pdo->prepare('UPDATE chat_flow_executions SET current_step = :cs, next_run_at = :nr WHERE id = :id')
                    ->execute([':cs' => $nextStep, ':nr' => $nextRunAt, ':id' => $executionId]);
                return; // Stop here — cron will resume
            } elseif ($stepType === 'action') {
                $actionType = (string) ($step['action_type'] ?? '');
                $this->executarAcao($actionType, $roomId);
            }

            // Advance step
            $pdo->prepare('UPDATE chat_flow_executions SET current_step = :cs, next_run_at = NULL WHERE id = :id')
                ->execute([':cs' => $nextStep, ':id' => $executionId]);

            // Process next step immediately (unless it was a delay)
            $this->processarProximoPasso($executionId);
        } catch (\Throwable $e) {
            $this->marcarFalha($executionId);
            throw $e;
        }
    }

    public function jaExecutando(int $roomId): bool
    {
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) AS cnt FROM chat_flow_executions WHERE room_id = :r AND status = 'running'"
        );
        $stmt->execute([':r' => $roomId]);
        return ((int) ($stmt->fetch()['cnt'] ?? 0)) > 0;
    }

    public function jaDisparadoNaSessao(int $roomId, int $flowId): bool
    {
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) AS cnt FROM chat_flow_executions
             WHERE room_id = :r AND flow_id = :f AND status IN ('running','completed')"
        );
        $stmt->execute([':r' => $roomId, ':f' => $flowId]);
        return ((int) ($stmt->fetch()['cnt'] ?? 0)) > 0;
    }

    public function listarPorFlow(int $flowId, int $limite = 50): array
    {
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare(
            'SELECT e.*, r.client_id FROM chat_flow_executions e
             LEFT JOIN chat_rooms r ON r.id = e.room_id
             WHERE e.flow_id = :f ORDER BY e.started_at DESC LIMIT ' . min(200, $limite)
        );
        $stmt->execute([':f' => $flowId]);
        return $stmt->fetchAll() ?: [];
    }

    public function buscarPorId(int $id): ?array
    {
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT * FROM chat_flow_executions WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $r = $stmt->fetch();
        return is_array($r) ? $r : null;
    }

    private function inserirMensagemSistema(int $roomId, string $content): void
    {
        $pdo = BancoDeDados::pdo();
        $agora = date('Y-m-d H:i:s');
        $pdo->prepare(
            'INSERT INTO chat_messages (room_id, sender_type, sender_id, message, created_at)
             VALUES (:r, \'system\', 0, :m, :c)'
        )->execute([':r' => $roomId, ':m' => $content, ':c' => $agora]);

        $pdo->prepare('UPDATE chat_rooms SET updated_at = :u WHERE id = :id')
            ->execute([':u' => $agora, ':id' => $roomId]);
    }

    private function executarAcao(string $actionType, int $roomId): void
    {
        if ($actionType === 'close_chat') {
            (new ChatRoomService())->fechar($roomId);
        } elseif ($actionType === 'send_satisfaction_link') {
            $base = \LRV\Core\ConfiguracoesSistema::appUrlBase();
            $link = $base . '/cliente/avaliar?type=chat&id=' . $roomId;
            $msg = 'Avalie seu atendimento: ' . $link;
            $this->inserirMensagemSistema($roomId, $msg);
        }
    }

    private function marcarCompleto(int $executionId): void
    {
        $pdo = BancoDeDados::pdo();
        $pdo->prepare(
            "UPDATE chat_flow_executions SET status = 'completed', completed_at = :c, next_run_at = NULL WHERE id = :id"
        )->execute([':c' => date('Y-m-d H:i:s'), ':id' => $executionId]);
    }

    private function marcarFalha(int $executionId): void
    {
        $pdo = BancoDeDados::pdo();
        $pdo->prepare(
            "UPDATE chat_flow_executions SET status = 'failed', completed_at = :c, next_run_at = NULL WHERE id = :id"
        )->execute([':c' => date('Y-m-d H:i:s'), ':id' => $executionId]);
    }
}
