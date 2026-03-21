<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Equipe;

use LRV\App\Services\Email\MailcowService;
use LRV\App\Services\Http\ClienteHttp;
use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;

final class EmailsController
{
    public function listar(Requisicao $req): Resposta
    {
        $pdo = BancoDeDados::pdo();

        $busca    = trim((string) ($req->query['q'] ?? ''));
        $clienteId = (int) ($req->query['cliente_id'] ?? 0);

        $where  = [];
        $params = [];

        if ($busca !== '') {
            $where[]          = '(e.email LIKE :q OR c.name LIKE :q)';
            $params[':q']     = '%' . $busca . '%';
        }
        if ($clienteId > 0) {
            $where[]              = 'e.client_id = :cid';
            $params[':cid']       = $clienteId;
        }

        $sql = 'SELECT e.id, e.email, e.domain, e.quota_mb, e.active, e.created_at,
                       c.id AS client_id, c.name AS client_name
                FROM client_emails e
                JOIN clients c ON c.id = e.client_id'
            . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
            . ' ORDER BY e.id DESC LIMIT 500';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $emails = $stmt->fetchAll();

        // Domínios cadastrados
        $stmtD = $pdo->query('SELECT cd.id, cd.domain, cd.status, cd.client_id, c.name AS client_name
                               FROM client_domains cd
                               JOIN clients c ON c.id = cd.client_id
                               ORDER BY cd.id DESC LIMIT 500');
        $dominios = $stmtD ? $stmtD->fetchAll() : [];

        $svc = new MailcowService(new ClienteHttp());

        $html = View::renderizar(__DIR__ . '/../../Views/equipe/emails-listar.php', [
            'emails'      => is_array($emails) ? $emails : [],
            'dominios'    => is_array($dominios) ? $dominios : [],
            'webmail_url' => $svc->webmailUrl(),
            'busca'       => $busca,
            'cliente_id'  => $clienteId,
            'erro'        => '',
            'sucesso'     => '',
        ]);

        return Resposta::html($html);
    }

    public function removerEmail(Requisicao $req): Resposta
    {
        $emailId = (int) ($req->post['email_id'] ?? 0);
        if ($emailId <= 0) {
            return Resposta::texto('ID inválido.', 400);
        }

        $pdo  = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT client_id, email FROM client_emails WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $emailId]);
        $row = $stmt->fetch();

        if (!is_array($row)) {
            return Resposta::texto('E-mail não encontrado.', 404);
        }

        try {
            (new MailcowService(new ClienteHttp()))->removerEmail((int) $row['client_id'], $emailId);
        } catch (\Throwable $e) {
            return $this->renderizarErro($e->getMessage());
        }

        return Resposta::redirecionar('/equipe/emails?sucesso=1');
    }

    public function removerDominio(Requisicao $req): Resposta
    {
        $dominioId = (int) ($req->post['dominio_id'] ?? 0);
        if ($dominioId <= 0) {
            return Resposta::texto('ID inválido.', 400);
        }

        $pdo  = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT client_id FROM client_domains WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $dominioId]);
        $row = $stmt->fetch();

        if (!is_array($row)) {
            return Resposta::texto('Domínio não encontrado.', 404);
        }

        try {
            (new MailcowService(new ClienteHttp()))->removerDominio((int) $row['client_id'], $dominioId);
        } catch (\Throwable $e) {
            return $this->renderizarErro($e->getMessage());
        }

        return Resposta::redirecionar('/equipe/emails?sucesso=1');
    }

    private function renderizarErro(string $erro): Resposta
    {
        $pdo    = BancoDeDados::pdo();
        $emails = $pdo->query('SELECT e.id, e.email, e.domain, e.quota_mb, e.active, e.created_at,
                                      c.id AS client_id, c.name AS client_name
                               FROM client_emails e JOIN clients c ON c.id = e.client_id
                               ORDER BY e.id DESC LIMIT 500')?->fetchAll() ?? [];
        $dominios = $pdo->query('SELECT cd.id, cd.domain, cd.status, cd.client_id, c.name AS client_name
                                 FROM client_domains cd JOIN clients c ON c.id = cd.client_id
                                 ORDER BY cd.id DESC LIMIT 500')?->fetchAll() ?? [];

        $svc  = new MailcowService(new ClienteHttp());
        $html = View::renderizar(__DIR__ . '/../../Views/equipe/emails-listar.php', [
            'emails'      => is_array($emails) ? $emails : [],
            'dominios'    => is_array($dominios) ? $dominios : [],
            'webmail_url' => $svc->webmailUrl(),
            'busca'       => '',
            'cliente_id'  => 0,
            'erro'        => $erro,
            'sucesso'     => '',
        ]);

        return Resposta::html($html, 422);
    }
}
