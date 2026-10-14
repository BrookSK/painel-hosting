<?php

declare(strict_types=1);

namespace LRV\Core;

/**
 * Descobre o IP real de quem fez a requisição.
 *
 * Os cabeçalhos X-Forwarded-For / CF-Connecting-IP só são aceitos quando a conexão
 * veio de um proxy confiável (localhost, rede privada, Cloudflare ou a lista em
 * Settings "security.trusted_proxies"). Sem isso, qualquer pessoa trocaria o IP
 * a cada requisição e passaria por cima de rate limit, bloqueio de login e 2FA.
 */
final class IpCliente
{
    /** Faixas publicadas da Cloudflare (https://www.cloudflare.com/ips/). */
    private const CLOUDFLARE = [
        '173.245.48.0/20', '103.21.244.0/22', '103.22.200.0/22', '103.31.4.0/22',
        '141.101.64.0/18', '108.162.192.0/18', '190.93.240.0/20', '188.114.96.0/20',
        '197.234.240.0/22', '198.41.128.0/17', '162.158.0.0/15', '104.16.0.0/13',
        '104.24.0.0/14', '172.64.0.0/13', '131.0.72.0/22',
        '2400:cb00::/32', '2606:4700::/32', '2803:f800::/32', '2405:b500::/32',
        '2405:8100::/32', '2a06:98c0::/29', '2c0f:f248::/32',
    ];

    /** Loopback e redes privadas (proxy reverso local / balanceador interno). */
    private const LOCAIS = [
        '127.0.0.0/8', '10.0.0.0/8', '172.16.0.0/12', '192.168.0.0/16', '::1/128', 'fc00::/7',
    ];

    private static ?array $extras = null;

    /**
     * @param array<string,string>|null $headers headers em minúsculas (Requisicao::$headers); null = $_SERVER
     */
    public static function obter(?array $headers = null, ?string $remoteAddr = null): string
    {
        $remote = trim((string) ($remoteAddr ?? ($_SERVER['REMOTE_ADDR'] ?? '')));
        if (filter_var($remote, FILTER_VALIDATE_IP) === false) {
            return '0.0.0.0';
        }
        if (!self::confiavel($remote)) {
            return $remote;
        }

        $h = static function (string $nome) use ($headers): string {
            if ($headers !== null) {
                return trim((string) ($headers[$nome] ?? ''));
            }
            $k = 'HTTP_' . strtoupper(str_replace('-', '_', $nome));
            return trim((string) ($_SERVER[$k] ?? ''));
        };

        // Cloudflare informa o IP do visitante neste cabeçalho
        if (self::dentro($remote, self::CLOUDFLARE)) {
            $cf = $h('cf-connecting-ip');
            if (filter_var($cf, FILTER_VALIDATE_IP) !== false) {
                return $cf;
            }
        }

        // X-Forwarded-For: da direita para a esquerda, pula proxies confiáveis
        $xff = $h('x-forwarded-for');
        if ($xff !== '') {
            $partes = array_reverse(array_map('trim', explode(',', $xff)));
            foreach ($partes as $ip) {
                if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
                    break; // cabeçalho adulterado: para e usa o último confiável
                }
                if (!self::confiavel($ip)) {
                    return $ip;
                }
            }
        }

        $real = $h('x-real-ip');
        if (filter_var($real, FILTER_VALIDATE_IP) !== false) {
            return $real;
        }

        return $remote;
    }

    private static function confiavel(string $ip): bool
    {
        if (self::$extras === null) {
            self::$extras = [];
            try {
                $cfg = (string) Settings::obter('security.trusted_proxies', '');
                foreach (preg_split('/[\s,]+/', $cfg) ?: [] as $c) {
                    if ($c !== '') {
                        self::$extras[] = $c;
                    }
                }
            } catch (\Throwable) {}
        }
        return self::dentro($ip, self::LOCAIS) || self::dentro($ip, self::CLOUDFLARE) || self::dentro($ip, self::$extras);
    }

    /** @param string[] $faixas */
    public static function dentro(string $ip, array $faixas): bool
    {
        $bin = @inet_pton($ip);
        if ($bin === false) {
            return false;
        }
        foreach ($faixas as $faixa) {
            [$rede, $bits] = array_pad(explode('/', $faixa, 2), 2, null);
            $redeBin = @inet_pton((string) $rede);
            if ($redeBin === false || strlen($redeBin) !== strlen($bin)) {
                continue;
            }
            $bits = $bits === null ? strlen($bin) * 8 : (int) $bits;
            $bytes = intdiv($bits, 8);
            $resto = $bits % 8;
            if ($bytes > 0 && substr($bin, 0, $bytes) !== substr($redeBin, 0, $bytes)) {
                continue;
            }
            if ($resto > 0) {
                $mask = (0xFF << (8 - $resto)) & 0xFF;
                if ((ord($bin[$bytes]) & $mask) !== (ord($redeBin[$bytes]) & $mask)) {
                    continue;
                }
            }
            return true;
        }
        return false;
    }
}
