<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Api\Public;

use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;

/**
 * Endpoints REST de Tickets da API Pública.
 * - GET  /api/v1/tickets           → Listar tickets
 * - GET  /api/v1/tickets/show?id=  → Ver ticket + mensagens
 * - POST /api/v1/tickets           → Criar ticket
 * - POST /api/v1/tickets/reply     → Responder ticket
 * - POST /api/v1/tickets/close?id= → Fechar ticket
 */
final class TicketsController extends BaseApiController
{
    /**
     * GET /api/v1/tickets
     */
    public function listar(Requisicao $req): Resposta
    {
        if (!$this->temEscopo($req, 'tickets.read')) {
            return $this->proibido('Scope tickets.read is required.');
        }

        $clienteId = $this->clienteId($req);
        $pag = $this->paginacao($req);
        $pdo = BancoDeDados::pdo();

        $where = ['t.client_id = :client_id'];
        $params = [':client_id' => $clienteId];

        $status = $req->query['status'] ?? null;
        if ($status !== null && in_array($status, ['open', 'in_progress', 'waiting_client', 'closed'], true)) {
            $where[] = 't.status = :status';
            $params[':status'] = $status;
        }

        $priority = $req->query['priority'] ?? null;
        if ($priority !== null && in_array($priority, ['low', 'medium', 'high'], true)) {
            $where[] = 't.priority = :priority';
            $params[':priority'] = $priority;
        }

        $search = trim((string) ($req->query['search'] ?? ''));
        if ($search !== '') {
            $where[] = '(t.subject LIKE :search)';
            $params[':search'] = '%' . $search . '%';
        }

        $whereSql = 'WHERE ' . implode(' AND ', $where);

        // Count
        $countStmt = $pdo->prepare("SELECT COUNT(*) AS total FROM tickets t $whereSql");
        $countStmt->execute($params);
        $total = (int) ($countStmt->fetch()['total'] ?? 0);

        // Dados
        $offset = ($pag['page'] - 1) * $pag['per_page'];
        $order = ($req->query['sort'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';

        $sql = "SELECT t.id, t.subject, t.status, t.priority, t.department, t.created_at, t.updated_at
                FROM tickets t $whereSql ORDER BY t.id $order LIMIT :limit OFFSET :offset";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $pag['per_page'], \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll() ?: [];

        $meta = [
            'current_page' => $pag['page'],
            'per_page' => $pag['per_page'],
            'total' => $total,
            'last_page' => (int) ceil($total / $pag['per_page']),
        ];

        return $this->paginado($items, $meta, '/api/v1/tickets');
    }

    /**
     * GET /api/v1/tickets/show?id=
     */
    public function show(Requisicao $req): Resposta
    {
        if (!$this->temEscopo($req, 'tickets.read')) {
            return $this->proibido('Scope tickets.read is required.');
        }

        $ticketId = (int) ($req->query['id'] ?? 0);
        if ($ticketId <= 0) {
            return $this->erro('MISSING_ID', 'The ticket id parameter is required.', 400);
        }

        $clienteId = $this->clienteId($req);
        $pdo = BancoDeDados::pdo();

        $stmt = $pdo->prepare(
            "SELECT id, subject, status, priority, department, created_at, updated_at
             FROM tickets WHERE id = :id AND client_id = :client_id LIMIT 1"
        );
        $stmt->execute([':id' => $ticketId, ':client_id' => $clienteId]);
        $ticket = $stmt->fetch();

        if (!is_array($ticket)) {
            return $this->naoEncontrado('Ticket');
        }

        // Mensagens
        $msgStmt = $pdo->prepare(
            "SELECT id, sender_type, sender_name, message, created_at
             FROM ticket_messages WHERE ticket_id = :tid ORDER BY id ASC"
        );
        $msgStmt->execute([':tid' => $ticketId]);
        $messages = $msgStmt->fetchAll() ?: [];

        $ticket['messages'] = $messages;

        return $this->sucesso($ticket);
    }

    /**
     * POST /api/v1/tickets
     */
    public function criar(Requisicao $req): Resposta
    {
        if (!$this->temEscopo($req, 'tickets.write')) {
            return $this->proibido('Scope tickets.write is required.');
        }

        $dados = $req->json();
        $validacao = $this->validarObrigatorios($dados, ['subject', 'message']);
        if ($validacao !== null) {
            return $validacao;
        }

        // Sandbox: simular sem executar
        if ($this->isSandbox($req)) {
            return $this->respostaSandbox('Ticket', 'created');
        }

        $clienteId = $this->clienteId($req);
        $subject = trim((string) ($dados['subject'] ?? ''));
        $message = trim((string) ($dados['message'] ?? ''));
        $priority = in_array($dados['priority'] ?? '', ['low', 'medium', 'high'], true) ? $dados['priority'] : 'medium';
        $department = in_array($dados['department'] ?? '', ['suporte', 'financeiro', 'devops', 'comercial', 'api'], true) ? $dados['department'] : 'suporte';

        if (mb_strlen($subject) > 200) {
            return $this->validacaoFalhou([['field' => 'subject', 'message' => 'Subject must be 200 characters or less.']]);
        }

        $pdo = BancoDeDados::pdo();

        // Buscar nome do cliente
        $cs = $pdo->prepare("SELECT name FROM clients WHERE id = :id");
        $cs->execute([':id' => $clienteId]);
        $clienteName = (string) ($cs->fetch()['name'] ?? 'Cliente');

        // Criar ticket
        $stmt = $pdo->prepare(
            "INSERT INTO tickets (client_id, subject, status, priority, department, created_at, updated_at)
             VALUES (:client_id, :subject, 'open', :priority, :department, NOW(), NOW())"
        );
        $stmt->execute([
            ':client_id' => $clienteId,
            ':subject' => $subject,
            ':priority' => $priority,
            ':department' => $department,
        ]);
        $ticketId = (int) $pdo->lastInsertId();

        // Primeira mensagem
        $stmt = $pdo->prepare(
            "INSERT INTO ticket_messages (ticket_id, sender_type, sender_id, sender_name, message, created_at)
             VALUES (:tid, 'client', :sender_id, :sender_name, :msg, NOW())"
        );
        $stmt->execute([
            ':tid' => $ticketId,
            ':sender_id' => $clienteId,
            ':sender_name' => $clienteName,
            ':msg' => $message,
        ]);

        return $this->criado([
            'id' => $ticketId,
            'subject' => $subject,
            'status' => 'open',
            'priority' => $priority,
            'department' => $department,
        ], 'Ticket created successfully.');
    }

    /**
     * POST /api/v1/tickets/reply
     */
    public function responder(Requisicao $req): Resposta
    {
        if (!$this->temEscopo($req, 'tickets.write')) {
            return $this->proibido('Scope tickets.write is required.');
        }

        $dados = $req->json();
        $validacao = $this->validarObrigatorios($dados, ['ticket_id', 'message']);
        if ($validacao !== null) {
            return $validacao;
        }

        if ($this->isSandbox($req)) {
            return $this->respostaSandbox('Ticket reply', 'sent');
        }

        $ticketId = (int) ($dados['ticket_id'] ?? 0);
        $message = trim((string) ($dados['message'] ?? ''));
        $clienteId = $this->clienteId($req);
        $pdo = BancoDeDados::pdo();

        // Verificar propriedade e status
        $stmt = $pdo->prepare("SELECT id, status FROM tickets WHERE id = :id AND client_id = :client_id");
        $stmt->execute([':id' => $ticketId, ':client_id' => $clienteId]);
        $ticket = $stmt->fetch();

        if (!is_array($ticket)) {
            return $this->naoEncontrado('Ticket');
        }

        if ($ticket['status'] === 'closed') {
            return $this->erro('TICKET_CLOSED', 'Cannot reply to a closed ticket.', 409);
        }

        // Buscar nome
        $cs = $pdo->prepare("SELECT name FROM clients WHERE id = :id");
        $cs->execute([':id' => $clienteId]);
        $clienteName = (string) ($cs->fetch()['name'] ?? 'Cliente');

        // Inserir mensagem
        $stmt = $pdo->prepare(
            "INSERT INTO ticket_messages (ticket_id, sender_type, sender_id, sender_name, message, created_at)
             VALUES (:tid, 'client', :sender_id, :sender_name, :msg, NOW())"
        );
        $stmt->execute([
            ':tid' => $ticketId,
            ':sender_id' => $clienteId,
            ':sender_name' => $clienteName,
            ':msg' => $message,
        ]);

        // Atualizar status para aberto (se estava aguardando resposta do cliente)
        $pdo->prepare("UPDATE tickets SET status = 'open', updated_at = NOW() WHERE id = :id AND status = 'waiting_client'")
            ->execute([':id' => $ticketId]);

        return $this->sucesso(['ticket_id' => $ticketId], 'Reply sent successfully.', 201);
    }

    /**
     * POST /api/v1/tickets/close?id=
     */
    public function fechar(Requisicao $req): Resposta
    {
        if (!$this->temEscopo($req, 'tickets.write')) {
            return $this->proibido('Scope tickets.write is required.');
        }

        $ticketId = (int) ($req->query['id'] ?? ($req->json()['id'] ?? 0));
        if ($ticketId <= 0) {
            return $this->erro('MISSING_ID', 'The ticket id is required.', 400);
        }

        if ($this->isSandbox($req)) {
            return $this->respostaSandbox('Ticket', 'closed');
        }

        $clienteId = $this->clienteId($req);
        $pdo = BancoDeDados::pdo();

        $stmt = $pdo->prepare("UPDATE tickets SET status = 'closed', updated_at = NOW() WHERE id = :id AND client_id = :client_id AND status != 'closed'");
        $stmt->execute([':id' => $ticketId, ':client_id' => $clienteId]);

        if ($stmt->rowCount() === 0) {
            return $this->naoEncontrado('Ticket');
        }

        return $this->sucesso(['ticket_id' => $ticketId, 'status' => 'closed'], 'Ticket closed successfully.');
    }
}
