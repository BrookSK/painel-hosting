<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Cliente;

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
        $clienteId = Auth::clienteId();
        if ($clienteId === null) {
            return Resposta::redirecionar('/cliente/entrar');
        }

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT id, subject, status, priority, department, created_at, updated_at FROM tickets WHERE client_id = :c ORDER BY updated_at DESC');
        $stmt->execute([':c' => $clienteId]);
        $tickets = $stmt->fetchAll();

        $html = View::renderizar(__DIR__ . '/../../Views/cliente/tickets-listar.php', [
            'tickets' => is_array($tickets) ? $tickets : [],
        ]);

        return Resposta::html($html);
    }

    public function novo(Requisicao $req): Resposta
    {
        $html = View::renderizar(__DIR__ . '/../../Views/cliente/ticket-novo.php', [
            'erro' => '',
            'form' => [
                'subject' => '',
                'priority' => 'medium',
                'department' => 'suporte',
                'message' => '',
            ],
        ]);

        return Resposta::html($html);
    }

    public function criar(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) {
            return Resposta::redirecionar('/cliente/entrar');
        }

        $subject = trim((string) ($req->post['subject'] ?? ''));
        $priority = (string) ($req->post['priority'] ?? 'medium');
        $department = (string) ($req->post['department'] ?? 'suporte');
        $message = trim((string) ($req->post['message'] ?? ''));

        if (!in_array($priority, ['low', 'medium', 'high'], true)) {
            $priority = 'medium';
        }

        if (!in_array($department, ['suporte', 'financeiro', 'devops', 'comercial'], true)) {
            $department = 'suporte';
        }

        if ($subject === '' || $message === '') {
            return $this->renderizarErroNovo($subject, $priority, $department, $message, 'Preencha assunto e mensagem.');
        }

        $pdo = BancoDeDados::pdo();
        $agora = date('Y-m-d H:i:s');

        $pdo->beginTransaction();
        try {
            $ins = $pdo->prepare('INSERT INTO tickets (client_id, subject, status, priority, department, assigned_to, created_at, updated_at) VALUES (:c, :s, :st, :p, :d, NULL, :cr, :up)');
            $ins->execute([
                ':c' => $clienteId,
                ':s' => $subject,
                ':st' => 'open',
                ':p' => $priority,
                ':d' => $department,
                ':cr' => $agora,
                ':up' => $agora,
            ]);

            $ticketId = (int) $pdo->lastInsertId();

            $insMsg = $pdo->prepare('INSERT INTO ticket_messages (ticket_id, sender_type, sender_id, message, attachment, created_at) VALUES (:t, :ty, :sid, :m, NULL, :cr)');
            $insMsg->execute([
                ':t' => $ticketId,
                ':ty' => 'client',
                ':sid' => $clienteId,
                ':m' => $message,
                ':cr' => $agora,
            ]);

            $pdo->commit();

            try {
                $repoJobs = new RepositorioJobs();
                $repoJobs->criar('alerta_ticket', [
                    'titulo' => 'Novo ticket #' . $ticketId,
                    'mensagem' => "Novo ticket criado pelo cliente.\n\nTicket: #{$ticketId}\nAssunto: {$subject}\nPrioridade: {$priority}\nDepartamento: {$department}",
                ]);
            } catch (\Throwable $e) {
            }

            return Resposta::redirecionar('/cliente/tickets/ver?id=' . $ticketId);
        } catch (\Throwable $e) {
            $pdo->rollBack();
            return $this->renderizarErroNovo($subject, $priority, $department, $message, 'Não foi possível criar o ticket.');
        }
    }

    public function ver(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) {
            return Resposta::redirecionar('/cliente/entrar');
        }

        $id = (int) ($req->query['id'] ?? 0);
        if ($id <= 0) {
            return Resposta::texto('Ticket inválido.', 400);
        }

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT id, subject, status, priority, department, created_at, updated_at FROM tickets WHERE id = :id AND client_id = :c LIMIT 1');
        $stmt->execute([':id' => $id, ':c' => $clienteId]);
        $ticket = $stmt->fetch();

        if (!is_array($ticket)) {
            return Resposta::texto('Ticket não encontrado.', 404);
        }

        $stmt2 = $pdo->prepare('SELECT id, sender_type, sender_id, message, created_at FROM ticket_messages WHERE ticket_id = :t ORDER BY id ASC');
        $stmt2->execute([':t' => $id]);
        $mensagens = $stmt2->fetchAll();

        $html = View::renderizar(__DIR__ . '/../../Views/cliente/ticket-ver.php', [
            'ticket' => $ticket,
            'mensagens' => is_array($mensagens) ? $mensagens : [],
            'erro' => '',
        ]);

        return Resposta::html($html);
    }

    public function responder(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) {
            return Resposta::redirecionar('/cliente/entrar');
        }

        $ticketId = (int) ($req->post['ticket_id'] ?? 0);
        $message = trim((string) ($req->post['message'] ?? ''));

        if ($ticketId <= 0 || $message === '') {
            return Resposta::texto('Requisição inválida.', 400);
        }

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT id, status FROM tickets WHERE id = :id AND client_id = :c LIMIT 1');
        $stmt->execute([':id' => $ticketId, ':c' => $clienteId]);
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
                ':ty' => 'client',
                ':sid' => $clienteId,
                ':m' => $message,
                ':cr' => $agora,
            ]);

            $up = $pdo->prepare('UPDATE tickets SET updated_at = :u WHERE id = :id');
            $up->execute([':u' => $agora, ':id' => $ticketId]);

            $pdo->commit();

            try {
                $repoJobs = new RepositorioJobs();
                $repoJobs->criar('alerta_ticket', [
                    'titulo' => 'Resposta do cliente no ticket #' . $ticketId,
                    'mensagem' => "Cliente respondeu no ticket.\n\nTicket: #{$ticketId}\nMensagem:\n{$message}",
                ]);
            } catch (\Throwable $e) {
            }
            return Resposta::redirecionar('/cliente/tickets/ver?id=' . $ticketId);
        } catch (\Throwable $e) {
            $pdo->rollBack();
            return Resposta::texto('Não foi possível responder.', 500);
        }
    }

    private function renderizarErroNovo(string $subject, string $priority, string $department, string $message, string $erro): Resposta
    {
        $html = View::renderizar(__DIR__ . '/../../Views/cliente/ticket-novo.php', [
            'erro' => $erro,
            'form' => [
                'subject' => $subject,
                'priority' => $priority,
                'department' => $department,
                'message' => $message,
            ],
        ]);

        return Resposta::html($html, 422);
    }
}
