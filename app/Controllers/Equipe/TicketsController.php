<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Equipe;

use LRV\App\Services\Audit\AuditLogService;
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

        $filtroStatus = trim((string) ($req->query['status'] ?? ''));
        $filtroPrio   = trim((string) ($req->query['priority'] ?? ''));
        $filtroDept   = trim((string) ($req->query['department'] ?? ''));
        $busca        = trim((string) ($req->query['q'] ?? ''));

        $where = ['1=1'];
        $params = [];

        $statusValidos = ['open', 'in_progress', 'waiting_client', 'closed'];
        if ($filtroStatus !== '' && in_array($filtroStatus, $statusValidos, true)) {
            $where[] = 't.status = :status';
            $params[':status'] = $filtroStatus;
        }

        $prioValidos = ['low', 'medium', 'high'];
        if ($filtroPrio !== '' && in_array($filtroPrio, $prioValidos, true)) {
            $where[] = 't.priority = :priority';
            $params[':priority'] = $filtroPrio;
        }

        $deptValidos = ['suporte', 'financeiro', 'devops', 'comercial'];
        if ($filtroDept !== '' && in_array($filtroDept, $deptValidos, true)) {
            $where[] = 't.department = :department';
            $params[':department'] = $filtroDept;
        }

        if ($busca !== '') {
            $where[] = '(t.subject LIKE :q OR c.name LIKE :q OR c.email LIKE :q)';
            $params[':q'] = '%' . $busca . '%';
        }

        $whereStr = implode(' AND ', $where);

        $sql = "SELECT t.id, t.subject, t.status, t.priority, t.department, t.assigned_to, t.created_at, t.updated_at,
                       c.name AS client_name, c.email AS client_email,
                       u.name AS assigned_name
                FROM tickets t
                INNER JOIN clients c ON c.id = t.client_id
                LEFT JOIN users u ON u.id = t.assigned_to
                WHERE {$whereStr}
                ORDER BY t.updated_at DESC
                LIMIT 200";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
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
                       c.name AS client_name, c.email AS client_email,
                       u.name AS assigned_name
                FROM tickets t
                INNER JOIN clients c ON c.id = t.client_id
                LEFT JOIN users u ON u.id = t.assigned_to
                WHERE t.id = :id
                LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $ticket = $stmt->fetch();

        if (!is_array($ticket)) {
            return Resposta::texto('Ticket não encontrado.', 404);
        }

        $stmt2 = $pdo->prepare('SELECT id, sender_type, sender_id, message, attachment_name, attachment_size, created_at FROM ticket_messages WHERE ticket_id = :t ORDER BY id ASC');
        $stmt2->execute([':t' => $id]);
        $mensagens = $stmt2->fetchAll();

        $stmtU = $pdo->query('SELECT id, name FROM users ORDER BY name ASC');
        $usuarios = $stmtU->fetchAll();

        $html = View::renderizar(__DIR__ . '/../../Views/equipe/ticket-ver.php', [
            'ticket'    => $ticket,
            'mensagens' => is_array($mensagens) ? $mensagens : [],
            'usuarios'  => is_array($usuarios) ? $usuarios : [],
            'erro'      => '',
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

        // Processar anexo
        $attachmentName = null;
        $attachmentSize = null;
        $uploadedFile = $_FILES['attachment'] ?? null;
        if (is_array($uploadedFile) && (int) ($uploadedFile['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $origName = basename((string) ($uploadedFile['name'] ?? ''));
            $tmpPath  = (string) ($uploadedFile['tmp_name'] ?? '');
            $size     = (int) ($uploadedFile['size'] ?? 0);

            if ($size > 5 * 1024 * 1024) {
                return Resposta::texto('Anexo muito grande (máx. 5 MB).', 400);
            }

            $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
            $allowed = ['pdf', 'txt', 'log', 'png', 'jpg', 'jpeg', 'gif', 'zip', 'tar', 'gz', 'csv'];
            if (!in_array($ext, $allowed, true)) {
                return Resposta::texto('Tipo de arquivo não permitido.', 400);
            }

            $storageDir = dirname(__DIR__, 3) . '/storage/attachments';
            if (!is_dir($storageDir)) {
                @mkdir($storageDir, 0775, true);
            }

            $safeName = date('YmdHis') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
            $destPath = $storageDir . '/' . $safeName;

            if (move_uploaded_file($tmpPath, $destPath)) {
                $attachmentName = $origName;
                $attachmentSize = $size;
            }
        }

        $pdo->beginTransaction();
        try {
            $insMsg = $pdo->prepare('INSERT INTO ticket_messages (ticket_id, sender_type, sender_id, message, attachment_name, attachment_size, created_at) VALUES (:t, :ty, :sid, :m, :an, :as, :cr)');
            $insMsg->execute([
                ':t'  => $ticketId,
                ':ty' => 'team',
                ':sid'=> $equipeId,
                ':m'  => $message,
                ':an' => $attachmentName,
                ':as' => $attachmentSize,
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

                // Notificar o cliente via notificação interna
                $stmtClient = $pdo->prepare('SELECT client_id, subject FROM tickets WHERE id = :id LIMIT 1');
                $stmtClient->execute([':id' => $ticketId]);
                $ticketRow = $stmtClient->fetch();
                if (is_array($ticketRow)) {
                    $clientId = (int) ($ticketRow['client_id'] ?? 0);
                    $subject  = (string) ($ticketRow['subject'] ?? '');
                    if ($clientId > 0) {
                        $insNotif = $pdo->prepare(
                            'INSERT INTO client_notifications (client_id, type, title, body, read_at, created_at)
                             VALUES (:c, :t, :ti, :b, NULL, :cr)'
                        );
                        $insNotif->execute([
                            ':c'  => $clientId,
                            ':t'  => 'ticket_reply',
                            ':ti' => 'Nova resposta no ticket #' . $ticketId,
                            ':b'  => 'A equipe respondeu ao seu ticket "' . $subject . '".',
                            ':cr' => $agora,
                        ]);
                    }

                    (new AuditLogService())->registrar(
                        'team',
                        \LRV\Core\Auth::equipeId(),
                        'ticket.notify_client',
                        'ticket',
                        $ticketId,
                        ['client_id' => $clientId],
                        $req,
                    );
                }
            } catch (\Throwable $e) {
            }

            (new AuditLogService())->registrar(
                'team',
                \LRV\Core\Auth::equipeId(),
                'ticket.reply',
                'ticket',
                $ticketId,
                ['ticket_id' => $ticketId, 'message_len' => strlen($message)],
                $req,
            );
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

        (new AuditLogService())->registrar(
            'team',
            \LRV\Core\Auth::equipeId(),
            'ticket.close',
            'ticket',
            $ticketId,
            ['ticket_id' => $ticketId],
            $req,
        );

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

    public function atribuir(Requisicao $req): Resposta
    {
        $equipeId = Auth::equipeId();
        if ($equipeId === null) {
            return Resposta::redirecionar('/equipe/entrar');
        }

        $ticketId = (int) ($req->post['ticket_id'] ?? 0);
        $userId = (int) ($req->post['user_id'] ?? 0);

        if ($ticketId <= 0) {
            return Resposta::texto('Requisição inválida.', 400);
        }

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT id FROM tickets WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $ticketId]);
        if (!is_array($stmt->fetch())) {
            return Resposta::texto('Ticket não encontrado.', 404);
        }

        $assignedTo = $userId > 0 ? $userId : null;
        $up = $pdo->prepare('UPDATE tickets SET assigned_to = :a, updated_at = :u WHERE id = :id');
        $up->execute([':a' => $assignedTo, ':u' => date('Y-m-d H:i:s'), ':id' => $ticketId]);

        (new AuditLogService())->registrar('team', $equipeId, 'ticket.assign', 'ticket', $ticketId, ['assigned_to' => $assignedTo], $req);

        return Resposta::redirecionar('/equipe/tickets/ver?id=' . $ticketId);
    }

    public function alterarStatus(Requisicao $req): Resposta
    {
        $equipeId = Auth::equipeId();
        if ($equipeId === null) {
            return Resposta::redirecionar('/equipe/entrar');
        }

        $ticketId = (int) ($req->post['ticket_id'] ?? 0);
        $novoStatus = trim((string) ($req->post['status'] ?? ''));
        $statusValidos = ['open', 'in_progress', 'waiting_client', 'closed'];

        if ($ticketId <= 0 || !in_array($novoStatus, $statusValidos, true)) {
            return Resposta::texto('Requisição inválida.', 400);
        }

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT id, client_id, subject FROM tickets WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $ticketId]);
        $ticket = $stmt->fetch();

        if (!is_array($ticket)) {
            return Resposta::texto('Ticket não encontrado.', 404);
        }

        $up = $pdo->prepare('UPDATE tickets SET status = :s, updated_at = :u WHERE id = :id');
        $up->execute([':s' => $novoStatus, ':u' => date('Y-m-d H:i:s'), ':id' => $ticketId]);

        (new AuditLogService())->registrar('team', $equipeId, 'ticket.status_change', 'ticket', $ticketId, ['status' => $novoStatus], $req);

        return Resposta::redirecionar('/equipe/tickets/ver?id=' . $ticketId);
    }
}
