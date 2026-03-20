<?php

declare(strict_types=1);

namespace LRV\App\Services\Email;

use LRV\App\Services\Http\ClienteHttp;
use LRV\Core\BancoDeDados;
use LRV\Core\Settings;

/**
 * Integração com Mailcow via API REST.
 * Configuração via settings:
 *   email.mailcow_url  — ex: https://mail.seudominio.com
 *   email.mailcow_key  — API key do Mailcow
 *   email.default_quota_mb — quota padrão (default 1024)
 */
final class MailcowService
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct(private readonly ClienteHttp $http = new ClienteHttp())
    {
        $this->baseUrl = rtrim((string) Settings::obter('email.mailcow_url', ''), '/');
        $this->apiKey  = (string) Settings::obter('email.mailcow_key', '');
    }

    /** Cria mailbox no Mailcow e salva no banco */
    public function criarEmail(int $clientId, string $localPart, string $domain, string $senha): array
    {
        $this->validarConfig();

        $localPart = strtolower(trim($localPart));
        $domain    = strtolower(trim($domain));

        if (!preg_match('/^[a-z0-9._+-]{1,64}$/', $localPart)) {
            throw new \InvalidArgumentException('Parte local do e-mail inválida.');
        }

        if (!preg_match('/^[a-z0-9.-]{3,191}$/', $domain)) {
            throw new \InvalidArgumentException('Domínio inválido.');
        }

        $email   = $localPart . '@' . $domain;
        $quotaMb = (int) Settings::obter('email.default_quota_mb', '1024');

        $resp = $this->post('/api/v1/add/mailbox', [
            'local_part'  => $localPart,
            'domain'      => $domain,
            'password'    => $senha,
            'password2'   => $senha,
            'quota'       => $quotaMb,
            'active'      => '1',
            'force_pw_update' => '0',
        ]);

        $mailcowId = (string) ($resp[0]['msg'][0] ?? $email);

        $pdo = BancoDeDados::pdo();
        $pdo->prepare('INSERT INTO client_emails (client_id, email, domain, mailcow_id, quota_mb, active, created_at) VALUES (:c,:e,:d,:m,:q,1,:cr)')
            ->execute([
                ':c'  => $clientId,
                ':e'  => $email,
                ':d'  => $domain,
                ':m'  => $mailcowId,
                ':q'  => $quotaMb,
                ':cr' => date('Y-m-d H:i:s'),
            ]);

        return ['email' => $email, 'quota_mb' => $quotaMb, 'mailcow_id' => $mailcowId];
    }

    /** Remove mailbox do Mailcow e do banco */
    public function removerEmail(int $clientId, int $emailId): void
    {
        $this->validarConfig();

        $pdo  = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT id, email, mailcow_id FROM client_emails WHERE id = :id AND client_id = :c LIMIT 1');
        $stmt->execute([':id' => $emailId, ':c' => $clientId]);
        $row = $stmt->fetch();

        if (!is_array($row)) {
            throw new \RuntimeException('E-mail não encontrado.');
        }

        $this->delete('/api/v1/delete/mailbox', [(string) $row['email']]);

        $pdo->prepare('DELETE FROM client_emails WHERE id = :id')->execute([':id' => $emailId]);
    }

    /** Lista emails do cliente */
    public function listar(int $clientId): array
    {
        $pdo  = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT id, email, domain, quota_mb, active, created_at FROM client_emails WHERE client_id = :c ORDER BY id DESC');
        $stmt->execute([':c' => $clientId]);
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    /** URL do webmail configurada */
    public function webmailUrl(): string
    {
        $url = (string) Settings::obter('email.webmail_url', '');
        return $url !== '' ? $url : $this->baseUrl;
    }

    private function validarConfig(): void
    {
        if ($this->baseUrl === '' || $this->apiKey === '') {
            throw new \RuntimeException('Mailcow não configurado. Defina email.mailcow_url e email.mailcow_key nas configurações.');
        }
    }

    private function post(string $path, array $data): array
    {
        $url  = $this->baseUrl . $path;
        $resp = $this->http->post($url, $data, [
            'X-API-Key: ' . $this->apiKey,
            'Content-Type: application/json',
        ]);

        $decoded = json_decode((string) $resp, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function delete(string $path, array $items): void
    {
        $url = $this->baseUrl . $path;
        $this->http->post($url, $items, [
            'X-API-Key: ' . $this->apiKey,
            'Content-Type: application/json',
            'X-HTTP-Method-Override: DELETE',
        ]);
    }
}
