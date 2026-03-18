<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Equipe;

use LRV\Core\Auth;
use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\Jobs\RepositorioJobs;
use LRV\Core\View;

final class TicketsController
{
    public function listar(Requisicao $req): Resposta
    {
        $equipeId = Auth::equipeId();
        if ($equipeId === null) {
            return Resposta::redirecionar('/equipe/entrar');
        }

        $pdo = BancoDeDados::pdo();
        $sql = "SELECT t.id, t.subject, t.status, t.priority, t.department, t.assigned_to, t.created_at, t.updated_at,
                       c.name AS client_name, c.email AS client_email
                FROM tickets t
                INNER JOIN clients c ON c.id = t.client_id
                ORDER BY t.updated_at DESC";
        $stmt = $pdo->query($sql);
        $tickets = $stmt->fetchAll();

        $html = View::renderizar(__DIR__ . '/../../Views/equipe/tickets-listar.php', [
            'tickets' => is_array($tickets) ? $tickets : [],
        ]);

        return Resposta::html($html);
    }

    public function ver(Requisicao $req): Resposta
    {
        $equipeId = Auth::equipeId();
        if ($equipeId === null) {
            return Resposta::redirecionar('/equipe/entrar');
        }

        $id = (int) ($req->query['id'] ?? 0);
        if ($id <= 0) {
            return Resposta::texto('Ticket inválido.', 400);
        }

        $pdo = BancoDeDados::pdo();
        $sql = "SELECT t.id, t.client_id, t.subject, t.status, t.priority, t.department, t.assigned_to, t.created_at, t.updated_at,
                       c.name AS client_name, c.email AS client_email
                FROM tickets t
                INNER JOIN clients c ON c.id = t.client_id
                WHERE t.id = :id
                LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $ticket = $stmt->fetch();

        if (!is_array($ticket)) {
            return Resposta::texto('Ticket não encontrado.', 404);
        }

        $stmt2 = $pdo->prepare('SELECT id, sender_type, sender_id, message, created_at FROM ticket_messages WHERE ticket_id = :t ORDER BY id ASC');
        $stmt2->execute([':t' => $id]);
        $mensagens = $stmt2->fetchAll();

        $html = View::renderizar(__DIR__ . '/../../Views/equipe/ticket-ver.php', [
            'ticket' => $ticket,
            'mensagens' => is_array($mensagens) ? $mensagens : [],
            'erro' => '',
        ]);

        return Resposta::html($html);
    }

    public function responder(Requisicao $req): Resposta
    {
        $equipeId = Auth::equipeId();
        if ($equipeId === null) {
            return Resposta::redirecionar('/equipe/entrar');
        }

        $ticketId = (int) ($req->post['ticket_id'] ?? 0);
        $message = trim((string) ($req->post['message'] ?? ''));

        if ($ticketId <= 0 || $message === '') {
            return Resposta::texto('Requisição inválida.', 400);
        }

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT id, status, assigned_to FROM tickets WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $ticketId]);
        $ticket = $stmt->fetch();

        if (!is_array($ticket)) {
            return Resposta::texto('Ticket não encontrado.', 404);
        }

        if ((string) ($ticket['status'] ?? '') === 'closed') {
            return Resposta::texto('Ticket fechado.', 409);
        }

        $agora = date('Y-m-d H:i:s');

        $pdo->beginTransaction();
        try {
            $insMsg = $pdo->prepare('INSERT INTO ticket_messages (ticket_id, sender_type, sender_id, message, attachment, created_at) VALUES (:t, :ty, :sid, :m, NULL, :cr)');
            $insMsg->execute([
                ':t' => $ticketId,
                ':ty' => 'team',
                ':sid' => $equipeId,
                ':m' => $message,
                ':cr' => $agora,
            ]);

            $up = $pdo->prepare('UPDATE tickets SET updated_at = :u, assigned_to = COALESCE(assigned_to, :aid) WHERE id = :id');
            $up->execute([
                ':u' => $agora,
                ':aid' => $equipeId,
                ':id' => $ticketId,
            ]);

            $pdo->commit();

            try {
                $repoJobs = new RepositorioJobs();
                $repoJobs->criar('alerta_ticket', [
                    'titulo' => 'Resposta da equipe no ticket #' . $ticketId,
                    'mensagem' => "Equipe respondeu no ticket.\n\nTicket: #{$ticketId}\nMensagem:\n{$message}",
                ]);
            } catch (\Throwable $e) {
            }
            return Resposta::redirecionar('/equipe/tickets/ver?id=' . $ticketId);
        } catch (\Throwable $e) {
            $pdo->rollBack();
            return Resposta::texto('Não foi possível responder.', 500);
        }
    }

    public function fechar(Requisicao $req): Resposta
    {
        $equipeId = Auth::equipeId();
        if ($equipeId === null) {
            return Resposta::redirecionar('/equipe/entrar');
        }

        $ticketId = (int) ($req->post['ticket_id'] ?? 0);
        if ($ticketId <= 0) {
            return Resposta::texto('Requisição inválida.', 400);
        }

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT id, subject, status FROM tickets WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $ticketId]);
        $ticket = $stmt->fetch();

        if (!is_array($ticket)) {
            return Resposta::texto('Ticket não encontrado.', 404);
        }

        if ((string) ($ticket['status'] ?? '') === 'closed') {
            return Resposta::redirecionar('/equipe/tickets/ver?id=' . $ticketId);
        }

        $up = $pdo->prepare("UPDATE tickets SET status = 'closed', updated_at = :u WHERE id = :id");
        $up->execute([
            ':u' => date('Y-m-d H:i:s'),
            ':id' => $ticketId,
        ]);

        try {
            $repoJobs = new RepositorioJobs();
            $repoJobs->criar('alerta_ticket', [
                'titulo' => 'Ticket fechado #' . $ticketId,
                'mensagem' => "Ticket fechado pela equipe.\n\nTicket: #{$ticketId}\nAssunto: " . (string) ($ticket['subject'] ?? ''),
            ]);
        } catch (\Throwable $e) {
        }

        return Resposta::redirecionar('/equipe/tickets/ver?id=' . $ticketId);
    }
}
