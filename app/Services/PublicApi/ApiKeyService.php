<?php

declare(strict_types=1);

namespace LRV\App\Services\PublicApi;

use LRV\Core\BancoDeDados;

/**
 * Serviço de gerenciamento de API Keys.
 * Gera, valida, revoga e rotaciona chaves de API.
 */
final class ApiKeyService
{
    private const PREFIX_PRODUCTION = 'lrv_live_';
    private const PREFIX_SANDBOX = 'lrv_test_';
    private const KEY_LENGTH = 48; // caracteres aleatórios após prefixo

    /**
     * Gera uma nova API Key para um cliente.
     * @return array{id: int, key: string, prefix: string, hint: string}
     */
    public function criar(
        int $clienteId,
        string $nome,
        string $ambiente = 'production',
        array $escopos = [],
        ?string $descricao = null,
        ?string $expiraEm = null,
        int $rateLimitPorMinuto = 60,
    ): array {
        $prefix = $ambiente === 'sandbox' ? self::PREFIX_SANDBOX : self::PREFIX_PRODUCTION;
        $raw = $prefix . $this->gerarTokenSeguro(self::KEY_LENGTH);
        $hash = hash('sha256', $raw);
        $hint = substr($raw, -4);

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare(
            "INSERT INTO api_keys (client_id, name, description, prefix, key_hash, key_hint, environment, scopes, rate_limit_per_minute, expires_at)
             VALUES (:client_id, :name, :description, :prefix, :key_hash, :key_hint, :environment, :scopes, :rate_limit, :expires_at)"
        );
        $stmt->execute([
            ':client_id' => $clienteId,
            ':name' => $nome,
            ':description' => $descricao,
            ':prefix' => $prefix,
            ':key_hash' => $hash,
            ':key_hint' => $hint,
            ':environment' => $ambiente,
            ':scopes' => json_encode($escopos, JSON_UNESCAPED_UNICODE),
            ':rate_limit' => $rateLimitPorMinuto,
            ':expires_at' => $expiraEm,
        ]);

        $id = (int) $pdo->lastInsertId();

        return [
            'id' => $id,
            'key' => $raw,
            'prefix' => $prefix,
            'hint' => $hint,
        ];
    }

    /**
     * Valida uma API Key e retorna os dados associados.
     * @return array|null null se inválida/expirada/revogada
     */
    public function validar(string $rawKey): ?array
    {
        if (trim($rawKey) === '') {
            return null;
        }

        $hash = hash('sha256', $rawKey);
        $pdo = BancoDeDados::pdo();

        $stmt = $pdo->prepare(
            "SELECT k.id, k.client_id, k.name, k.environment, k.scopes, k.status,
                    k.rate_limit_per_minute, k.expires_at
             FROM api_keys k
             WHERE k.key_hash = :hash
             LIMIT 1"
        );
        $stmt->execute([':hash' => $hash]);
        $row = $stmt->fetch();

        if (!is_array($row)) {
            return null;
        }

        // Verificar status
        if ($row['status'] !== 'active') {
            return null;
        }

        // Verificar expiração
        if ($row['expires_at'] !== null && strtotime($row['expires_at']) < time()) {
            // Marcar como expirada
            $pdo->prepare("UPDATE api_keys SET status = 'expired' WHERE id = :id")
                ->execute([':id' => (int) $row['id']]);
            return null;
        }

        // Decodificar escopos
        $scopes = json_decode((string) $row['scopes'], true);
        $row['scopes_array'] = is_array($scopes) ? $scopes : [];

        return $row;
    }

    /**
     * Registra uso da API Key (último acesso, IP, contagem).
     */
    public function registrarUso(int $keyId, string $ip): void
    {
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare(
            "UPDATE api_keys SET last_used_at = NOW(), last_used_ip = :ip, request_count = request_count + 1 WHERE id = :id"
        );
        $stmt->execute([':ip' => $ip, ':id' => $keyId]);
    }

    /**
     * Revoga uma API Key.
     */
    public function revogar(int $keyId, int $clienteId): bool
    {
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare(
            "UPDATE api_keys SET status = 'revoked', revoked_at = NOW() WHERE id = :id AND client_id = :client_id AND status = 'active'"
        );
        $stmt->execute([':id' => $keyId, ':client_id' => $clienteId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Rotaciona uma API Key: revoga a atual e gera nova.
     * @return array{id: int, key: string, prefix: string, hint: string}|null
     */
    public function rotacionar(int $keyId, int $clienteId): ?array
    {
        $pdo = BancoDeDados::pdo();

        // Buscar dados da key atual
        $stmt = $pdo->prepare(
            "SELECT name, description, environment, scopes, rate_limit_per_minute, expires_at
             FROM api_keys WHERE id = :id AND client_id = :client_id AND status = 'active'"
        );
        $stmt->execute([':id' => $keyId, ':client_id' => $clienteId]);
        $old = $stmt->fetch();

        if (!is_array($old)) {
            return null;
        }

        // Revogar antiga
        $this->revogar($keyId, $clienteId);

        // Criar nova com mesmos dados
        $scopes = json_decode((string) $old['scopes'], true);
        return $this->criar(
            $clienteId,
            $old['name'],
            $old['environment'],
            is_array($scopes) ? $scopes : [],
            $old['description'],
            $old['expires_at'],
            (int) $old['rate_limit_per_minute'],
        );
    }

    /**
     * Lista todas as API Keys de um cliente.
     */
    public function listarPorCliente(int $clienteId, ?string $ambiente = null): array
    {
        $pdo = BancoDeDados::pdo();
        $sql = "SELECT id, name, description, prefix, key_hint, environment, scopes, status,
                       rate_limit_per_minute, last_used_at, last_used_ip, request_count,
                       expires_at, revoked_at, created_at
                FROM api_keys WHERE client_id = :client_id";
        $params = [':client_id' => $clienteId];

        if ($ambiente !== null) {
            $sql .= " AND environment = :env";
            $params[':env'] = $ambiente;
        }

        $sql .= " ORDER BY created_at DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Verifica se uma API Key tem um escopo específico.
     */
    public function temEscopo(array $keyData, string $escopo): bool
    {
        $escopos = $keyData['scopes_array'] ?? [];
        if (in_array('*', $escopos, true)) {
            return true;
        }
        return in_array($escopo, $escopos, true);
    }

    private function gerarTokenSeguro(int $tamanho): string
    {
        $bytes = random_bytes((int) ceil($tamanho * 0.75));
        $token = rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=');
        return substr($token, 0, $tamanho);
    }
}
