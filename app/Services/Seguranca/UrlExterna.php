<?php

declare(strict_types=1);

namespace LRV\App\Services\Seguranca;

use LRV\Core\BancoDeDados;

/**
 * Proteção contra SSRF: garante que uma URL informada por cliente aponta para a
 * internet pública — nunca para o próprio servidor, rede interna, metadados de
 * nuvem (169.254.169.254) ou para os nossos nodes.
 */
final class UrlExterna
{
    private const PRIVADAS = [
        '0.0.0.0/8', '10.0.0.0/8', '100.64.0.0/10', '127.0.0.0/8', '169.254.0.0/16',
        '172.16.0.0/12', '192.0.0.0/24', '192.0.2.0/24', '192.168.0.0/16', '198.18.0.0/15',
        '198.51.100.0/24', '203.0.113.0/24', '224.0.0.0/4', '240.0.0.0/4', '255.255.255.255/32',
        '::/128', '::1/128', '::ffff:0:0/96', '64:ff9b::/96', '100::/64', '2001:db8::/32',
        'fc00::/7', 'fe80::/10', 'ff00::/8',
    ];

    public static function ipPublico(string $ip): bool
    {
        if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
            return false;
        }
        if (\LRV\Core\IpCliente::dentro($ip, self::PRIVADAS)) {
            return false;
        }
        // IPs dos nossos servidores também não (evita bater em serviços internos deles)
        try {
            $st = BancoDeDados::pdo()->query('SELECT ip_address FROM servers');
            foreach ($st->fetchAll(\PDO::FETCH_COLUMN) ?: [] as $s) {
                if (trim((string) $s) === $ip) {
                    return false;
                }
            }
        } catch (\Throwable) {}
        $self = trim((string) ($_SERVER['SERVER_ADDR'] ?? ''));
        return $self === '' || $self !== $ip;
    }

    /**
     * Host (nome ou IP) de um servidor externo informado pelo cliente (ex.: origem de
     * migração). Não pode resolver para endereço interno/privado nem para nossos nodes.
     */
    public static function exigirHostPublico(string $host): string
    {
        $host = strtolower(trim($host, " \t\n\r[]"));
        if ($host === '' || strlen($host) > 253) {
            throw new \InvalidArgumentException('Endereço vazio ou inválido.');
        }
        if (filter_var($host, FILTER_VALIDATE_IP) !== false) {
            $ips = [$host];
        } else {
            if (!\LRV\App\Services\Seguranca\ValidadorInfra::dominioValido($host)) {
                throw new \InvalidArgumentException('Endereço inválido.');
            }
            $ips = [];
            foreach (@dns_get_record($host, DNS_A | DNS_AAAA) ?: [] as $r) {
                if (!empty($r['ip'])) $ips[] = (string) $r['ip'];
                if (!empty($r['ipv6'])) $ips[] = (string) $r['ipv6'];
            }
            if ($ips === []) {
                throw new \InvalidArgumentException('Não foi possível resolver o endereço.');
            }
        }
        foreach ($ips as $ip) {
            if (!self::ipPublico($ip)) {
                throw new \InvalidArgumentException('O endereço aponta para uma rede interna, o que não é permitido.');
            }
        }
        return $host;
    }

    /**
     * Valida a URL e resolve o DNS. Retorna [host, porta, ip] para o cURL fixar o IP
     * (CURLOPT_RESOLVE) e não sofrer DNS rebinding entre a checagem e a conexão.
     *
     * @return array{host:string, porta:int, ip:string}
     */
    public static function exigirHttpsPublica(string $url): array
    {
        $url = trim($url);
        if (strlen($url) > 1000 || filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw new \InvalidArgumentException('URL inválida.');
        }
        $p = parse_url($url);
        if (!is_array($p) || strtolower((string) ($p['scheme'] ?? '')) !== 'https') {
            throw new \InvalidArgumentException('A URL precisa começar com https://');
        }
        if (isset($p['user']) || isset($p['pass'])) {
            throw new \InvalidArgumentException('A URL não pode conter usuário/senha.');
        }
        $host = strtolower(trim((string) ($p['host'] ?? ''), '[]'));
        $porta = (int) ($p['port'] ?? 443);
        if ($host === '' || $porta < 1 || $porta > 65535) {
            throw new \InvalidArgumentException('URL inválida.');
        }

        if (filter_var($host, FILTER_VALIDATE_IP) !== false) {
            $ips = [$host];
        } else {
            if ($host === 'localhost' || str_ends_with($host, '.localhost') || str_ends_with($host, '.local') || str_ends_with($host, '.internal') || !str_contains($host, '.')) {
                throw new \InvalidArgumentException('Endereço não permitido.');
            }
            $ips = [];
            foreach (@dns_get_record($host, DNS_A | DNS_AAAA) ?: [] as $r) {
                if (!empty($r['ip'])) $ips[] = (string) $r['ip'];
                if (!empty($r['ipv6'])) $ips[] = (string) $r['ipv6'];
            }
            if ($ips === []) {
                $v4 = @gethostbynamel($host);
                $ips = is_array($v4) ? $v4 : [];
            }
            if ($ips === []) {
                throw new \InvalidArgumentException('Não foi possível resolver o endereço da URL.');
            }
        }

        foreach ($ips as $ip) {
            if (!self::ipPublico($ip)) {
                throw new \InvalidArgumentException('A URL aponta para um endereço interno/privado, o que não é permitido.');
            }
        }

        return ['host' => $host, 'porta' => $porta, 'ip' => $ips[0]];
    }
}
