<?php

declare(strict_types=1);

namespace LRV\App\Services\Email;

use LRV\App\Services\Http\ClienteHttp;
use LRV\Core\BancoDeDados;
use LRV\Core\Settings;

/**
 * Integração com Mailcow via API REST.
 * Configuração via settings:
 *   email.mailcow_url        — ex: https://mail.seudominio.com
 *   email.mailcow_key        — API key do Mailcow
 *   email.default_quota_mb   — quota padrão (default 1024)
 *   email.default_domain     — domínio padrão global
 *   email.webmail_mode       — global | custom
 *   email.max_accounts_per_plan — limite padrão de contas
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

    // ── Mailboxes ──────────────────────────────────────────────────────────

    /** Cria mailbox no Mailcow e salva no banco */
    public function criarEmail(int $clientId, string $localPart, string $domain, string $senha, int $quotaMb = 0): array
    {
        $this->validarConfig();

        $localPart = strtolower(trim($localPart));
        $domain    = strtolower(trim($domain));

        if (!preg_match('/^[a-z0-9._+-]{1,64}$/', $localPart)) {
            throw new \InvalidArgumentException('Parte local do e-mail inválida.');
        }
        if (!$this->validarFormatoDominio($domain)) {
            throw new \InvalidArgumentException('Domínio inválido.');
        }

        $this->verificarLimitePlano($clientId);

        // Quota: usar valor informado ou default
        $defaultQuota = (int) Settings::obter('email.default_quota_mb', '1024');
        if ($quotaMb <= 0) {
            $quotaMb = $defaultQuota;
        }

        // Verificar cota total do plano
        $cotaTotal = $this->cotaTotalPlano($clientId);
        if ($cotaTotal > 0) {
            $usada = $this->cotaUsada($clientId);
            if (($usada + $quotaMb) > $cotaTotal) {
                $disponivel = $cotaTotal - $usada;
                throw new \RuntimeException("Cota insuficiente. Disponível: {$disponivel} MB de {$cotaTotal} MB.");
            }
        }

        $email = $localPart . '@' . $domain;

        $resp = $this->post('/api/v1/add/mailbox', [
            'local_part'      => $localPart,
            'domain'          => $domain,
            'password'        => $senha,
            'password2'       => $senha,
            'quota'           => $quotaMb,
            'active'          => '1',
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

    /** Altera senha de uma mailbox no Mailcow */
    public function alterarSenha(int $clientId, int $emailId, string $novaSenha): void
    {
        $this->validarConfig();

        if (strlen($novaSenha) < 8) {
            throw new \InvalidArgumentException('Senha mínima de 8 caracteres.');
        }

        $pdo  = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT email FROM client_emails WHERE id = :id AND client_id = :c LIMIT 1');
        $stmt->execute([':id' => $emailId, ':c' => $clientId]);
        $row = $stmt->fetch();

        if (!is_array($row)) {
            throw new \RuntimeException('E-mail não encontrado.');
        }

        $this->post('/api/v1/edit/mailbox/' . rawurlencode((string) $row['email']), [
            'attr' => ['password' => $novaSenha, 'password2' => $novaSenha],
        ]);
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

    // ── Domínios ───────────────────────────────────────────────────────────

    /** Cria domínio no Mailcow */
    public function criarDominio(string $domain): array
    {
        $this->validarConfig();

        $domain = strtolower(trim($domain));
        if (!$this->validarFormatoDominio($domain)) {
            throw new \InvalidArgumentException('Formato de domínio inválido.');
        }

        $resp = $this->post('/api/v1/add/domain', [
            'domain'        => $domain,
            'description'   => 'Custom domain',
            'aliases'       => 400,
            'mailboxes'     => 10,
            'defquota'      => 1024,
            'maxquota'      => 10240,
            'quota'         => 10240,
            'active'        => '1',
            'restart_sogo'  => '1',
        ]);

        $ok = isset($resp[0]['type']) && $resp[0]['type'] === 'success';
        return ['ok' => $ok, 'resp' => $resp];
    }

    /** Verifica se o domínio está ativo no Mailcow */
    public function verificarDominio(string $domain): bool
    {
        $this->validarConfig();

        $domain = strtolower(trim($domain));
        $url    = $this->baseUrl . '/api/v1/get/domain/' . rawurlencode($domain);
        $resp   = $this->http->requestJson('GET', $url, ['X-API-Key' => $this->apiKey], []);
        $data   = $resp['json'] ?? json_decode($resp['body'] ?? '', true);

        if (!is_array($data)) {
            return false;
        }

        // Mailcow retorna objeto com campo 'active' quando domínio existe
        return isset($data['active']) && (int) $data['active'] === 1;
    }

    /** Obtém registro DKIM público do domínio */
    public function obterDKIM(string $domain): string
    {
        $this->validarConfig();

        $domain = strtolower(trim($domain));
        $url    = $this->baseUrl . '/api/v1/get/dkim/' . rawurlencode($domain);
        $resp   = $this->http->requestJson('GET', $url, ['X-API-Key' => $this->apiKey], []);
        $data   = $resp['json'] ?? json_decode($resp['body'] ?? '', true);

        if (!is_array($data)) {
            return '';
        }

        return (string) ($data['dkim_txt'] ?? $data['pubkey'] ?? '');
    }

    // ── Domínios do cliente (banco) ────────────────────────────────────────

    /** Lista domínios cadastrados pelo cliente */
    public function listarDominios(int $clientId): array
    {
        $pdo  = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT id, domain, status, error_msg, created_at FROM client_domains WHERE client_id = :c ORDER BY id DESC');
        $stmt->execute([':c' => $clientId]);
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    /** Adiciona domínio custom para o cliente */
    public function adicionarDominio(int $clientId, string $domain): array
    {
        $domain = strtolower(trim($domain));

        if (!$this->validarFormatoDominio($domain)) {
            throw new \InvalidArgumentException('Formato de domínio inválido. Use apenas letras, números, hífens e pontos.');
        }

        $pdo = BancoDeDados::pdo();

        // Verificar duplicata global
        $stmt = $pdo->prepare('SELECT id, client_id FROM client_domains WHERE domain = :d LIMIT 1');
        $stmt->execute([':d' => $domain]);
        $existente = $stmt->fetch();
        if (is_array($existente)) {
            if ((int) $existente['client_id'] === $clientId) {
                throw new \RuntimeException('Você já cadastrou este domínio.');
            }
            throw new \RuntimeException('Este domínio já está em uso por outro cliente.');
        }

        // Tentar criar no Mailcow (não fatal se falhar — DNS ainda não propagado)
        $mailcowOk = false;
        $errMsg    = null;
        if ($this->baseUrl !== '' && $this->apiKey !== '') {
            try {
                $res       = $this->criarDominio($domain);
                $mailcowOk = $res['ok'];
                if (!$mailcowOk) {
                    $errMsg = 'Domínio criado localmente. Aguardando propagação DNS para ativar no servidor de email.';
                }
            } catch (\Throwable $e) {
                $errMsg = $e->getMessage();
            }
        }

        $pdo->prepare('INSERT INTO client_domains (client_id, domain, status, error_msg, created_at) VALUES (:c,:d,:s,:e,:cr)')
            ->execute([
                ':c'  => $clientId,
                ':d'  => $domain,
                ':s'  => 'pending_dns',
                ':e'  => $errMsg,
                ':cr' => date('Y-m-d H:i:s'),
            ]);

        return ['domain' => $domain, 'mailcow_ok' => $mailcowOk];
    }

    /** Verifica DNS e atualiza status do domínio */
    public function verificarDNSDominio(int $clientId, int $dominioId): array
    {
        $pdo  = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT id, domain FROM client_domains WHERE id = :id AND client_id = :c LIMIT 1');
        $stmt->execute([':id' => $dominioId, ':c' => $clientId]);
        $row = $stmt->fetch();

        if (!is_array($row)) {
            throw new \RuntimeException('Domínio não encontrado.');
        }

        $domain = (string) $row['domain'];
        $ativo  = false;
        $errMsg = null;

        // Verificar MX via DNS
        $mxRecords = @dns_get_record($domain, DNS_MX);
        $temMx     = is_array($mxRecords) && count($mxRecords) > 0;

        if ($temMx) {
            // Tentar verificar no Mailcow também
            if ($this->baseUrl !== '' && $this->apiKey !== '') {
                try {
                    $ativo = $this->verificarDominio($domain);
                    if (!$ativo) {
                        // Tentar criar se ainda não existe
                        $this->criarDominio($domain);
                        $ativo = $this->verificarDominio($domain);
                    }
                } catch (\Throwable $e) {
                    $errMsg = $e->getMessage();
                }
            } else {
                // Sem Mailcow configurado, aceitar se MX existe
                $ativo = true;
            }
        } else {
            $errMsg = 'Registro MX não encontrado. Verifique as instruções DNS.';
        }

        $novoStatus = $ativo ? 'active' : ($temMx ? 'error' : 'pending_dns');
        $pdo->prepare('UPDATE client_domains SET status = :s, error_msg = :e WHERE id = :id')
            ->execute([':s' => $novoStatus, ':e' => $errMsg, ':id' => $dominioId]);

        return ['ok' => $ativo, 'status' => $novoStatus, 'erro' => $errMsg, 'tem_mx' => $temMx];
    }

    /** Remove domínio do cliente */
    public function removerDominio(int $clientId, int $dominioId): void
    {
        $pdo  = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT domain FROM client_domains WHERE id = :id AND client_id = :c LIMIT 1');
        $stmt->execute([':id' => $dominioId, ':c' => $clientId]);
        $row = $stmt->fetch();

        if (!is_array($row)) {
            throw new \RuntimeException('Domínio não encontrado.');
        }

        $pdo->prepare('DELETE FROM client_domains WHERE id = :id')->execute([':id' => $dominioId]);
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    /** Retorna o domínio padrão global */
    public function dominioPadrao(): string
    {
        return (string) Settings::obter('email.default_domain', '');
    }

    /** Retorna o modo de webmail configurado */
    public function webmailMode(): string
    {
        $mode = (string) Settings::obter('email.webmail_mode', 'global');
        return in_array($mode, ['global', 'custom'], true) ? $mode : 'global';
    }

    /** URL do webmail configurada */
    public function webmailUrl(): string
    {
        $url = (string) Settings::obter('email.webmail_url', '');
        return $url !== '' ? $url : $this->baseUrl;
    }

    /** Template de instruções DNS */
    public function dnsInstructionsTemplate(): string
    {
        return (string) Settings::obter('email.dns_instructions_template', '');
    }

    /** Limite de contas por plano (fallback global) */
    public function limiteContasPorPlano(int $clientId): int
    {
        $pdo  = BancoDeDados::pdo();
        // Buscar limite do plano ativo do cliente
        $stmt = $pdo->prepare(
            'SELECT p.specs_json FROM subscriptions s
             JOIN plans p ON p.id = s.plan_id
             WHERE s.client_id = :c AND s.status = "active"
             ORDER BY s.id DESC LIMIT 1'
        );
        $stmt->execute([':c' => $clientId]);
        $row = $stmt->fetch();

        if (is_array($row) && !empty($row['specs_json'])) {
            $specs = json_decode((string) $row['specs_json'], true);
            if (is_array($specs) && isset($specs['email_accounts'])) {
                return (int) $specs['email_accounts'];
            }
        }

        return (int) Settings::obter('email.max_accounts_per_plan', '5');
    }

    private function verificarLimitePlano(int $clientId): void
    {
        $limite = $this->limiteContasPorPlano($clientId);
        $pdo    = BancoDeDados::pdo();
        $stmt   = $pdo->prepare('SELECT COUNT(*) FROM client_emails WHERE client_id = :c');
        $stmt->execute([':c' => $clientId]);
        $atual = (int) $stmt->fetchColumn();

        if ($atual >= $limite) {
            throw new \RuntimeException("Seu plano permite até {$limite} conta(s) de e-mail. Faça upgrade para criar mais.");
        }
    }

    public function cotaTotalPlano(int $clientId): int
    {
        $pdo  = BancoDeDados::pdo();
        $stmt = $pdo->prepare(
            'SELECT p.specs_json FROM subscriptions s
             JOIN plans p ON p.id = s.plan_id
             WHERE s.client_id = :c AND s.status = "active"
             ORDER BY s.id DESC LIMIT 1'
        );
        $stmt->execute([':c' => $clientId]);
        $row = $stmt->fetch();

        if (is_array($row) && !empty($row['specs_json'])) {
            $specs = json_decode((string) $row['specs_json'], true);
            if (is_array($specs) && isset($specs['email_quota_mb'])) {
                return (int) $specs['email_quota_mb'];
            }
        }

        return (int) Settings::obter('email.default_total_quota_mb', '5120');
    }

    public function cotaUsada(int $clientId): int
    {
        $pdo  = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT COALESCE(SUM(quota_mb), 0) FROM client_emails WHERE client_id = :c AND active = 1');
        $stmt->execute([':c' => $clientId]);
        return (int) $stmt->fetchColumn();
    }


    private function validarFormatoDominio(string $domain): bool
    {
        return (bool) preg_match('/^(?:[a-z0-9](?:[a-z0-9\-]{0,61}[a-z0-9])?\.)+[a-z]{2,}$/', $domain);
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
        $resp = $this->http->requestJson('POST', $url, ['X-API-Key' => $this->apiKey], $data);
        $decoded = $resp['json'] ?? json_decode($resp['body'] ?? '', true);
        return is_array($decoded) ? $decoded : [];
    }

    private function delete(string $path, array $items): void
    {
        $url = $this->baseUrl . $path;
        $this->http->requestJson('DELETE', $url, ['X-API-Key' => $this->apiKey], $items);
    }

    public function ativarWebmailPersonalizado(int $clientId, int $dominioId): array
    {
        $pdo  = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT id, domain, status, webmail_enabled FROM client_domains WHERE id = :id AND client_id = :c LIMIT 1');
        $stmt->execute([':id' => $dominioId, ':c' => $clientId]);
        $row = $stmt->fetch();

        if (!is_array($row)) {
            throw new \RuntimeException('Domínio não encontrado.');
        }
        if ($row['status'] !== 'active') {
            throw new \RuntimeException('O domínio precisa estar ativo (DNS verificado) antes de ativar o webmail personalizado.');
        }

        $domain = (string) $row['domain'];
        $webmailHost = 'webmail.' . $domain;

        // Verificar CNAME
        $cnameOk = $this->verificarCNAME($webmailHost);

        if (!$cnameOk) {
            $pdo->prepare('UPDATE client_domains SET webmail_enabled = 1, webmail_verified = 0 WHERE id = :id')
                ->execute([':id' => $dominioId]);
            $mailcowHost = parse_url($this->baseUrl, PHP_URL_HOST) ?: '';
            return [
                'ok' => false,
                'erro' => "CNAME não encontrado. Crie um registro CNAME: webmail.{$domain} → {$mailcowHost}",
                'webmail_host' => $webmailHost,
            ];
        }

        // CNAME ok — registrar SAN no Mailcow e marcar como verificado
        $this->registrarSAN($webmailHost);

        $pdo->prepare('UPDATE client_domains SET webmail_enabled = 1, webmail_verified = 1 WHERE id = :id')
            ->execute([':id' => $dominioId]);

        return ['ok' => true, 'webmail_url' => 'https://' . $webmailHost . '/SOGo'];
    }

    public function verificarWebmail(int $clientId, int $dominioId): array
    {
        $pdo  = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT id, domain FROM client_domains WHERE id = :id AND client_id = :c AND webmail_enabled = 1 LIMIT 1');
        $stmt->execute([':id' => $dominioId, ':c' => $clientId]);
        $row = $stmt->fetch();

        if (!is_array($row)) {
            throw new \RuntimeException('Domínio não encontrado ou webmail não ativado.');
        }

        $webmailHost = 'webmail.' . (string) $row['domain'];
        $cnameOk = $this->verificarCNAME($webmailHost);

        if (!$cnameOk) {
            return ['ok' => false, 'erro' => 'CNAME ainda não propagado.'];
        }

        $this->registrarSAN($webmailHost);

        $pdo->prepare('UPDATE client_domains SET webmail_verified = 1 WHERE id = :id')
            ->execute([':id' => $dominioId]);

        return ['ok' => true, 'webmail_url' => 'https://' . $webmailHost . '/SOGo'];
    }

    private function verificarCNAME(string $hostname): bool
    {
        $records = @dns_get_record($hostname, DNS_CNAME);
        if (!is_array($records) || count($records) === 0) {
            // Fallback: check if A record resolves to Mailcow IP
            $ip = @gethostbyname($hostname);
            $mailcowHost = parse_url($this->baseUrl, PHP_URL_HOST) ?: '';
            $mailcowIp = $mailcowHost !== '' ? @gethostbyname($mailcowHost) : '';
            return $ip !== $hostname && $mailcowIp !== '' && $ip === $mailcowIp;
        }
        return true;
    }

    private function registrarSAN(string $hostname): void
    {
        // Adicionar hostname como SAN adicional no Mailcow via API
        try {
            $this->post('/api/v1/edit/domain-admin/san', [
                'san' => $hostname,
            ]);
        } catch (\Throwable) {
            // SAN registration is best-effort — Mailcow may handle it via ACME automatically
        }
    }

    public function webmailUrlParaDominio(string $domain): string
    {
        $pdo  = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT webmail_enabled, webmail_verified, webmail_app_id FROM client_domains WHERE domain = :d LIMIT 1');
        $stmt->execute([':d' => $domain]);
        $row = $stmt->fetch();

        if (is_array($row)) {
            // Roundcube instalado — usar IP:porta do container
            $appId = (int) ($row['webmail_app_id'] ?? 0);
            if ($appId > 0) {
                $aStmt = $pdo->prepare("SELECT port FROM applications WHERE id = :id AND status = 'running' LIMIT 1");
                $aStmt->execute([':id' => $appId]);
                $app = $aStmt->fetch();
                if (is_array($app)) {
                    // Roundcube roda na VPS do cliente, acessível via IP do servidor + porta
                    $vStmt = $pdo->prepare(
                        'SELECT s.ip_address FROM applications a
                         JOIN vps v ON v.id = a.vps_id
                         JOIN servers s ON s.id = v.server_id
                         WHERE a.id = :id LIMIT 1'
                    );
                    $vStmt->execute([':id' => $appId]);
                    $srv = $vStmt->fetch();
                    if (is_array($srv)) {
                        return 'http://' . $srv['ip_address'] . ':' . $app['port'];
                    }
                }
            }

            // Webmail personalizado via CNAME
            if ((int) ($row['webmail_enabled'] ?? 0) === 1 && (int) ($row['webmail_verified'] ?? 0) === 1) {
                return 'https://webmail.' . $domain . '/SOGo';
            }
        }

        return $this->webmailUrl();
    }
}
