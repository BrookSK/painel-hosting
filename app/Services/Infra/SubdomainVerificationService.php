<?php

declare(strict_types=1);

namespace LRV\App\Services\Infra;

use LRV\Core\BancoDeDados;
use LRV\Core\Settings;

final class SubdomainVerificationService
{
    public function adicionarSubdominio(int $clientId, string $subdomain): array
    {
        $subdomain = strtolower(trim($subdomain));
        if (!$this->validarFormato($subdomain)) {
            throw new \InvalidArgumentException('Formato de domínio inválido. Use algo como meudominio.com.br ou app.meudominio.com.br');
        }

        $root = $this->extrairRaiz($subdomain);
        $isRootDomain = ($root === '' || $root === $subdomain);

        $pdo = BancoDeDados::pdo();

        // Subdomínios não precisam mais de domínio raiz cadastrado em client_domains

        // Verificar duplicata
        $dup = $pdo->prepare('SELECT id, client_id FROM client_subdomains WHERE subdomain = :s LIMIT 1');
        $dup->execute([':s' => $subdomain]);
        $existing = $dup->fetch();
        if (is_array($existing)) {
            if ((int)$existing['client_id'] === $clientId) {
                throw new \RuntimeException('Você já cadastrou este domínio.');
            }
            throw new \RuntimeException('Este domínio já está em uso por outro cliente.');
        }

        $token = bin2hex(random_bytes(16));

        if ($isRootDomain) {
            // Domínio raiz → registro A direto para o IP do servidor
            $pdo->prepare(
                'INSERT INTO client_subdomains (client_id, subdomain, root_domain, type, verify_token, cname_target, status, created_at)
                 VALUES (:c, :s, :r, :t, :vt, :ct, :st, :cr)'
            )->execute([
                ':c'  => $clientId,
                ':s'  => $subdomain,
                ':r'  => $subdomain,
                ':t'  => 'root_vps',
                ':vt' => $token,
                ':ct' => '',
                ':st' => 'pending_dns',
                ':cr' => date('Y-m-d H:i:s'),
            ]);

            return [
                'id' => (int)$pdo->lastInsertId(),
                'subdomain' => $subdomain,
                'verify_token' => $token,
                'cname_target' => '',
                'type' => 'root_vps',
            ];
        }

        // Subdomínio → CNAME
        $cnameTarget = $this->gerarCnameTarget($clientId);

        $pdo->prepare(
            'INSERT INTO client_subdomains (client_id, subdomain, root_domain, type, verify_token, cname_target, status, created_at)
             VALUES (:c, :s, :r, :t, :vt, :ct, :st, :cr)'
        )->execute([
            ':c'  => $clientId,
            ':s'  => $subdomain,
            ':r'  => $root,
            ':t'  => 'subdomain',
            ':vt' => $token,
            ':ct' => $cnameTarget,
            ':st' => 'pending_cname',
            ':cr' => date('Y-m-d H:i:s'),
        ]);

        return [
            'id' => (int)$pdo->lastInsertId(),
            'subdomain' => $subdomain,
            'verify_token' => $token,
            'cname_target' => $cnameTarget,
            'type' => 'subdomain',
        ];
    }

    public function verificarTxt(int $clientId, int $subId): array
    {
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT * FROM client_subdomains WHERE id = :id AND client_id = :c LIMIT 1');
        $stmt->execute([':id' => $subId, ':c' => $clientId]);
        $row = $stmt->fetch();

        if (!is_array($row)) {
            throw new \RuntimeException('Subdomínio não encontrado.');
        }

        $subdomain = (string)$row['subdomain'];
        $token = (string)$row['verify_token'];
        $txtHost = '_lrv-verify.' . $subdomain;
        $expected = 'lrv-verify=' . $token;

        // Tentar via dns_get_record
        $records = @dns_get_record($txtHost, DNS_TXT);
        $found = false;
        if (is_array($records)) {
            foreach ($records as $r) {
                $txt = (string)($r['txt'] ?? '');
                if (str_contains($txt, $expected)) {
                    $found = true;
                    break;
                }
            }
        }

        // Fallback: tentar via shell dig/nslookup
        if (!$found) {
            $digOutput = @shell_exec('dig +short TXT ' . escapeshellarg($txtHost) . ' 2>/dev/null') ?? '';
            if (str_contains($digOutput, $expected)) {
                $found = true;
            }
        }

        // Fallback 2: tentar sem o subdomínio completo (caso o provedor adicione a zona automaticamente)
        if (!$found) {
            $parts = explode('.', $subdomain);
            $subPart = $parts[0] ?? '';
            $altHost = '_lrv-verify.' . $subPart;
            $rootDomain = (string)$row['root_domain'];
            $altFull = $altHost . '.' . $rootDomain;
            if ($altFull !== $txtHost) {
                $altRecords = @dns_get_record($altFull, DNS_TXT);
                if (is_array($altRecords)) {
                    foreach ($altRecords as $r) {
                        if (str_contains((string)($r['txt'] ?? ''), $expected)) {
                            $found = true;
                            break;
                        }
                    }
                }
            }
        }

        if ($found) {
            $pdo->prepare("UPDATE client_subdomains SET status = 'pending_cname', error_msg = NULL WHERE id = :id")
                ->execute([':id' => $subId]);
            return ['ok' => true, 'status' => 'pending_cname'];
        }

        // Coletar o que foi encontrado pra debug
        $foundTxts = [];
        if (is_array($records)) {
            foreach ($records as $r) {
                $foundTxts[] = (string)($r['txt'] ?? '');
            }
        }
        $debugInfo = $foundTxts ? 'Encontrado: ' . implode(', ', $foundTxts) : 'Nenhum registro TXT encontrado';

        $pdo->prepare("UPDATE client_subdomains SET error_msg = :e WHERE id = :id")
            ->execute([':e' => 'TXT não encontrado em ' . $txtHost . '. ' . $debugInfo, ':id' => $subId]);
        return ['ok' => false, 'erro' => 'Registro TXT não encontrado. Verifique se criou _lrv-verify.' . $subdomain . ' com valor "' . $expected . '". ' . $debugInfo . '. A propagação DNS pode levar alguns minutos.'];
    }

    public function verificarCname(int $clientId, int $subId): array
    {
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT * FROM client_subdomains WHERE id = :id AND client_id = :c LIMIT 1');
        $stmt->execute([':id' => $subId, ':c' => $clientId]);
        $row = $stmt->fetch();

        if (!is_array($row)) {
            throw new \RuntimeException('Subdomínio não encontrado.');
        }

        $subdomain = (string)$row['subdomain'];
        $cnameTarget = strtolower(trim((string)$row['cname_target']));

        $records = @dns_get_record($subdomain, DNS_CNAME);
        $found = false;
        if (is_array($records)) {
            foreach ($records as $r) {
                $target = strtolower(trim((string)($r['target'] ?? '')));
                if ($target === $cnameTarget || $target === $cnameTarget . '.') {
                    $found = true;
                    break;
                }
            }
        }

        if ($found) {
            $pdo->prepare("UPDATE client_subdomains SET status = 'active', error_msg = NULL WHERE id = :id")
                ->execute([':id' => $subId]);
            return ['ok' => true, 'status' => 'active'];
        }

        $pdo->prepare("UPDATE client_subdomains SET error_msg = :e WHERE id = :id")
            ->execute([':e' => 'CNAME não encontrado apontando para ' . $cnameTarget, ':id' => $subId]);
        return ['ok' => false, 'erro' => 'CNAME não encontrado. Aponte ' . $subdomain . ' para ' . $cnameTarget];
    }

    /** Verifica se o registro A de um domínio raiz aponta para o IP do servidor da VPS do cliente. */
    public function verificarA(int $clientId, int $subId): array
    {
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT * FROM client_subdomains WHERE id = :id AND client_id = :c LIMIT 1');
        $stmt->execute([':id' => $subId, ':c' => $clientId]);
        $row = $stmt->fetch();

        if (!is_array($row)) {
            throw new \RuntimeException('Domínio não encontrado.');
        }

        $domain = (string)$row['subdomain'];

        // Buscar IP do servidor da VPS do cliente
        $ipStmt = $pdo->prepare(
            "SELECT s.ip_address FROM vps v
             INNER JOIN servers s ON s.id = v.server_id
             WHERE v.client_id = :c AND v.status IN ('running','pending_provisioning','provisioning')
             ORDER BY v.id DESC LIMIT 1"
        );
        $ipStmt->execute([':c' => $clientId]);
        $ipRow = $ipStmt->fetch();
        $expectedIp = is_array($ipRow) ? trim((string)($ipRow['ip_address'] ?? '')) : '';

        if ($expectedIp === '') {
            return ['ok' => false, 'erro' => 'Nenhuma VPS ativa encontrada para verificar o apontamento.'];
        }

        $records = @dns_get_record($domain, DNS_A);
        $found = false;
        if (is_array($records)) {
            foreach ($records as $r) {
                $ip = trim((string)($r['ip'] ?? ''));
                if ($ip === $expectedIp) {
                    $found = true;
                    break;
                }
            }
        }

        if ($found) {
            $pdo->prepare("UPDATE client_subdomains SET status = 'active', error_msg = NULL WHERE id = :id")
                ->execute([':id' => $subId]);
            return ['ok' => true, 'status' => 'active'];
        }

        $pdo->prepare("UPDATE client_subdomains SET error_msg = :e WHERE id = :id")
            ->execute([':e' => 'Registro A não encontrado apontando para ' . $expectedIp, ':id' => $subId]);
        return ['ok' => false, 'erro' => 'Registro A não encontrado. Aponte ' . $domain . ' para ' . $expectedIp];
    }

    public function removerSubdominio(int $clientId, int $subId): void
    {
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT id, used_by_type FROM client_subdomains WHERE id = :id AND client_id = :c LIMIT 1');
        $stmt->execute([':id' => $subId, ':c' => $clientId]);
        $row = $stmt->fetch();

        if (!is_array($row)) {
            throw new \RuntimeException('Subdomínio não encontrado.');
        }

        if (($row['used_by_type'] ?? null) !== null) {
            throw new \RuntimeException('Este subdomínio está em uso. Remova a associação antes de deletar.');
        }

        $pdo->prepare('DELETE FROM client_subdomains WHERE id = :id AND client_id = :c')
            ->execute([':id' => $subId, ':c' => $clientId]);
    }

    public function listar(int $clientId): array
    {
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT * FROM client_subdomains WHERE client_id = :c ORDER BY root_domain, subdomain');
        $stmt->execute([':c' => $clientId]);
        return $stmt->fetchAll() ?: [];
    }

    public function listarAtivosDisponiveis(int $clientId): array
    {
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare("SELECT id, subdomain, root_domain FROM client_subdomains WHERE client_id = :c AND status = 'active' AND used_by_type IS NULL ORDER BY subdomain");
        $stmt->execute([':c' => $clientId]);
        return $stmt->fetchAll() ?: [];
    }

    public function marcarEmUso(int $subId, string $type, int $entityId): void
    {
        $pdo = BancoDeDados::pdo();
        $pdo->prepare('UPDATE client_subdomains SET used_by_type = :t, used_by_id = :eid WHERE id = :id')
            ->execute([':t' => $type, ':eid' => $entityId, ':id' => $subId]);
    }

    public function liberarUso(string $type, int $entityId): void
    {
        $pdo = BancoDeDados::pdo();
        $pdo->prepare('UPDATE client_subdomains SET used_by_type = NULL, used_by_id = NULL WHERE used_by_type = :t AND used_by_id = :eid')
            ->execute([':t' => $type, ':eid' => $entityId]);
    }

    private function gerarCnameTarget(int $clientId): string
    {
        $tempBase = trim((string)Settings::obter('infra.temp_domain_base', ''));
        if ($tempBase === '') return '';

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare("SELECT id, temp_subdomain FROM vps WHERE client_id = :c AND status IN ('running','pending_provisioning','provisioning') ORDER BY id DESC LIMIT 1");
        $stmt->execute([':c' => $clientId]);
        $vps = $stmt->fetch();

        if (!is_array($vps)) return 'vps0.' . $tempBase;

        $existing = trim((string)($vps['temp_subdomain'] ?? ''));
        if ($existing !== '') return $existing;

        return 'vps' . (int)$vps['id'] . '.' . $tempBase;
    }

    private function validarFormato(string $s): bool
    {
        return preg_match('/^[a-z0-9][a-z0-9.\-]*\.[a-z]{2,}$/', $s) === 1 && strlen($s) <= 253;
    }

    private function extrairRaiz(string $subdomain): string
    {
        $parts = explode('.', $subdomain);
        $total = count($parts);
        if ($total < 2) return $subdomain;

        // TLDs compostos: .com.br, .co.uk, .org.br, .net.br, .edu.br, .gov.br, etc.
        $tldCompostos = ['com.br','co.uk','org.br','net.br','edu.br','gov.br','com.au','co.nz','co.za','com.ar','com.mx','com.pt','co.in','com.co'];
        $last2 = implode('.', array_slice($parts, -2));

        if (in_array($last2, $tldCompostos, true)) {
            // TLD composto: raiz tem 3 partes (ex: lrvweb.com.br)
            if ($total <= 3) return $subdomain; // é raiz
            return implode('.', array_slice($parts, -3));
        }

        // TLD simples: raiz tem 2 partes (ex: example.com)
        if ($total <= 2) return $subdomain; // é raiz
        return implode('.', array_slice($parts, -2));
    }
}
