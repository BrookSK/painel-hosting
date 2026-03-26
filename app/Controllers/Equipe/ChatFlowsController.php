<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Equipe;

use LRV\App\Services\Chat\ChatFlowExecutionService;
use LRV\App\Services\Chat\ChatFlowService;
use LRV\App\Services\Chat\ChatFlowStepService;
use LRV\App\Services\Chat\ChatRoomService;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\Settings;
use LRV\Core\View;

final class ChatFlowsController
{
    public function listar(Requisicao $req): Resposta
    {
        $svc = new ChatFlowService();
        $flows = $svc->listar();

        $html = View::renderizar(__DIR__ . '/../../Views/equipe/chat-flows-listar.php', [
            'flows' => $flows,
        ]);
        return Resposta::html($html);
    }

    public function novo(Requisicao $req): Resposta
    {
        $html = View::renderizar(__DIR__ . '/../../Views/equipe/chat-flow-editar.php', [
            'flow'       => null,
            'steps'      => [],
            'executions' => [],
            'erro'       => '',
            'sucesso'    => '',
            'timeout'    => (int) Settings::obter('chat_flow.inactivity_minutes', 10),
        ]);
        return Resposta::html($html);
    }

    public function salvar(Requisicao $req): Resposta
    {
        $id          = (int) ($req->post['id'] ?? 0);
        $name        = trim((string) ($req->post['name'] ?? ''));
        $description = trim((string) ($req->post['description'] ?? ''));
        $triggerType = trim((string) ($req->post['trigger_type'] ?? ''));
        $active      = (int) ($req->post['active'] ?? 1);

        $svc = new ChatFlowService();

        try {
            if ($id > 0) {
                $svc->atualizar($id, $name, $description, $triggerType, $active);

                // Save inactivity timeout if client_inactive
                if ($triggerType === 'client_inactive') {
                    $timeout = (int) ($req->post['inactivity_minutes'] ?? 10);
                    if ($timeout >= 1 && $timeout <= 120) {
                        Settings::definir('chat_flow.inactivity_minutes', $timeout);
                    }
                }

                return Resposta::redirecionar('/equipe/chat-flows');
            }

            $newId = $svc->criar($name, $description, $triggerType);

            if ($triggerType === 'client_inactive') {
                $timeout = (int) ($req->post['inactivity_minutes'] ?? 10);
                if ($timeout >= 1 && $timeout <= 120) {
                    Settings::definir('chat_flow.inactivity_minutes', $timeout);
                }
            }

            return Resposta::redirecionar('/equipe/chat-flows/editar?id=' . $newId);
        } catch (\InvalidArgumentException $e) {
            $flow = $id > 0 ? $svc->buscarPorId($id) : null;
            $steps = $id > 0 ? (new ChatFlowStepService())->listarPorFlow($id) : [];
            $executions = $id > 0 ? (new ChatFlowExecutionService())->listarPorFlow($id) : [];

            $html = View::renderizar(__DIR__ . '/../../Views/equipe/chat-flow-editar.php', [
                'flow'       => $flow ?? ['id' => 0, 'name' => $name, 'description' => $description, 'trigger_type' => $triggerType, 'active' => $active],
                'steps'      => $steps,
                'executions' => $executions,
                'erro'       => $e->getMessage(),
                'sucesso'    => '',
                'timeout'    => (int) Settings::obter('chat_flow.inactivity_minutes', 10),
            ]);
            return Resposta::html($html);
        }
    }

    public function editar(Requisicao $req): Resposta
    {
        $id = (int) ($req->query['id'] ?? 0);
        $svc = new ChatFlowService();
        $flow = $svc->buscarPorId($id);
        if ($flow === null) {
            return Resposta::redirecionar('/equipe/chat-flows');
        }

        $steps = (new ChatFlowStepService())->listarPorFlow($id);
        $executions = (new ChatFlowExecutionService())->listarPorFlow($id);

        $html = View::renderizar(__DIR__ . '/../../Views/equipe/chat-flow-editar.php', [
            'flow'       => $flow,
            'steps'      => $steps,
            'executions' => $executions,
            'erro'       => '',
            'sucesso'    => (string) ($req->query['ok'] ?? '') === '1' ? 'Salvo com sucesso.' : '',
            'timeout'    => (int) Settings::obter('chat_flow.inactivity_minutes', 10),
        ]);
        return Resposta::html($html);
    }

    public function excluir(Requisicao $req): Resposta
    {
        $id = (int) ($req->post['id'] ?? 0);
        if ($id > 0) {
            (new ChatFlowService())->desativar($id);
        }
        return Resposta::redirecionar('/equipe/chat-flows');
    }

    public function salvarPasso(Requisicao $req): Resposta
    {
        $flowId      = (int) ($req->post['flow_id'] ?? 0);
        $stepId      = (int) ($req->post['step_id'] ?? 0);
        $stepType    = trim((string) ($req->post['step_type'] ?? ''));
        $content     = trim((string) ($req->post['content'] ?? ''));
        $delaySec    = ($req->post['delay_seconds'] ?? '') !== '' ? (int) $req->post['delay_seconds'] : null;
        $actionType  = trim((string) ($req->post['action_type'] ?? '')) ?: null;

        $svc = new ChatFlowStepService();

        try {
            if ($stepId > 0) {
                $svc->atualizar($stepId, $stepType, $content ?: null, $delaySec, $actionType);
            } else {
                $svc->criar($flowId, $stepType, $content ?: null, $delaySec, $actionType);
            }
        } catch (\InvalidArgumentException $e) {
            // Redirect back with error in query string
            return Resposta::redirecionar('/equipe/chat-flows/editar?id=' . $flowId . '&step_erro=' . urlencode($e->getMessage()));
        }

        return Resposta::redirecionar('/equipe/chat-flows/editar?id=' . $flowId . '&ok=1');
    }

    public function removerPasso(Requisicao $req): Resposta
    {
        $flowId = (int) ($req->post['flow_id'] ?? 0);
        $stepId = (int) ($req->post['step_id'] ?? 0);
        if ($stepId > 0) {
            (new ChatFlowStepService())->remover($stepId);
        }
        return Resposta::redirecionar('/equipe/chat-flows/editar?id=' . $flowId);
    }

    public function reordenarPassos(Requisicao $req): Resposta
    {
        $flowId = (int) ($req->post['flow_id'] ?? 0);
        $idsRaw = (string) ($req->post['order'] ?? '');
        $ids = array_filter(array_map('intval', explode(',', $idsRaw)));

        if ($flowId > 0 && !empty($ids)) {
            (new ChatFlowStepService())->reordenar($flowId, $ids);
        }

        return Resposta::json(['ok' => true]);
    }

    public function dispatch(Requisicao $req): Resposta
    {
        $flowId = (int) ($req->post['flow_id'] ?? 0);
        $roomId = (int) ($req->post['room_id'] ?? 0);

        if ($flowId <= 0 || $roomId <= 0) {
            return Resposta::json(['ok' => false, 'erro' => 'Parâmetros inválidos.']);
        }

        $room = (new ChatRoomService())->buscarPorId($roomId);
        if ($room === null || (string) ($room['status'] ?? '') !== 'open') {
            return Resposta::json(['ok' => false, 'erro' => 'Chat encerrado.']);
        }

        $execSvc = new ChatFlowExecutionService();
        if ($execSvc->jaExecutando($roomId)) {
            return Resposta::json(['ok' => false, 'erro' => 'Fluxo já em execução.']);
        }

        try {
            $execSvc->iniciar($flowId, $roomId, 'manual');
            return Resposta::json(['ok' => true]);
        } catch (\Throwable $e) {
            return Resposta::json(['ok' => false, 'erro' => $e->getMessage()]);
        }
    }
}
