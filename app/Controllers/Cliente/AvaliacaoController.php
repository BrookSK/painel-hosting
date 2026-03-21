<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Cliente;

use LRV\Core\Auth;
use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;

final class AvaliacaoController
{
    /** GET /cliente/avaliar?type=ticket&id=X */
    public function formulario(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) {
            return Resposta::redirecionar('/cliente/entrar');
        }

        [$type, $refId, $agentId, $erro] = $this->resolverContexto($req, $clienteId);
        if ($erro !== '') {
            return Resposta::texto($erro, 400);
        }

        // Já avaliou?
        $jaAvaliou = $this->jaAvaliou($type, $refId);

        return Resposta::html(View::renderizar(
            __DIR__ . '/../../Views/cliente/avaliar.php',
            compact('type', 'refId', 'agentId', 'jaAvaliou')
        ));
    }

    /** POST /cliente/avaliar */
    public function salvar(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) {
            return Resposta::json(['ok' => false, 'erro' => 'Não autenticado.'], 401);
        }

        $type    = trim((string) ($req->post['type'] ?? ''));
        $refId   = (int) ($req->post['reference_id'] ?? 0);
        $rating  = (int) ($req->post['rating'] ?? 0);
        $comment = trim((string) ($req->post['comment'] ?? ''));
        $agentId = (int) ($req->post['agent_id'] ?? 0);

        if (!in_array($type, ['ticket', 'chat'], true) || $refId <= 0 || $rating < 1 || $rating > 5) {
            return Resposta::json(['ok' => false, 'erro' => 'Dados inválidos.'], 422);
        }

        if ($this->jaAvaliou($type, $refId)) {
            return Resposta::json(['ok' => false, 'erro' => 'Já avaliado.'], 409);
        }

        // Verificar que o reference_id pertence ao cliente
        if (!$this->pertenceAoCliente($type, $refId, $clienteId)) {
            return Resposta::json(['ok' => false, 'erro' => 'Acesso negado.'], 403);
        }

        $pdo = BancoDeDados::pdo();
        $pdo->prepare(
            'INSERT INTO satisfaction_surveys (type, reference_id, client_id, rating, comment, agent_id, created_at)
             VALUES (:t, :r, :c, :rt, :cm, :a, :cr)'
        )->execute([
            ':t'  => $type,
            ':r'  => $refId,
            ':c'  => $clienteId,
            ':rt' => $rating,
            ':cm' => $comment !== '' ? substr($comment, 0, 1000) : null,
            ':a'  => $agentId > 0 ? $agentId : null,
            ':cr' => date('Y-m-d H:i:s'),
        ]);

        return Resposta::json(['ok' => true]);
    }

    private function jaAvaliou(string $type, int $refId): bool
    {
        $pdo  = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT id FROM satisfaction_surveys WHERE type = :t AND reference_id = :r LIMIT 1');
        $stmt->execute([':t' => $type, ':r' => $refId]);
        return (bool) $stmt->fetch();
    }

    private function pertenceAoCliente(string $type, int $refId, int $clienteId): bool
    {
        $pdo = BancoDeDados::pdo();
        if ($type === 'ticket') {
            $s = $pdo->prepare('SELECT id FROM tickets WHERE id = :id AND client_id = :c LIMIT 1');
        } else {
            $s = $pdo->prepare('SELECT id FROM chat_rooms WHERE id = :id AND client_id = :c LIMIT 1');
        }
        $s->execute([':id' => $refId, ':c' => $clienteId]);
        return (bool) $s->fetch();
    }

    /** @return array{string, int, int, string} [type, refId, agentId, erro] */
    private function resolverContexto(Requisicao $req, int $clienteId): array
    {
        $type  = trim((string) ($req->query['type'] ?? ''));
        $refId = (int) ($req->query['id'] ?? 0);

        if (!in_array($type, ['ticket', 'chat'], true) || $refId <= 0) {
            return ['', 0, 0, 'Parâmetros inválidos.'];
        }

        $pdo     = BancoDeDados::pdo();
        $agentId = 0;

        if ($type === 'ticket') {
            $s = $pdo->prepare("SELECT id, status, assigned_to FROM tickets WHERE id = :id AND client_id = :c LIMIT 1");
            $s->execute([':id' => $refId, ':c' => $clienteId]);
            $row = $s->fetch();
            if (!is_array($row)) return ['', 0, 0, 'Ticket não encontrado.'];
            if ((string) ($row['status'] ?? '') !== 'closed') return ['', 0, 0, 'Ticket ainda não encerrado.'];
            $agentId = (int) ($row['assigned_to'] ?? 0);
        } else {
            $s = $pdo->prepare("SELECT id, status, user_id FROM chat_rooms WHERE id = :id AND client_id = :c LIMIT 1");
            $s->execute([':id' => $refId, ':c' => $clienteId]);
            $row = $s->fetch();
            if (!is_array($row)) return ['', 0, 0, 'Chat não encontrado.'];
            if ((string) ($row['status'] ?? '') !== 'closed') return ['', 0, 0, 'Chat ainda não encerrado.'];
            $agentId = (int) ($row['user_id'] ?? 0);
        }

        return [$type, $refId, $agentId, ''];
    }
}
