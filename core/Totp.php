<?php

declare(strict_types=1);

namespace LRV\Core;

/**
 * Implementação TOTP (RFC 6238) sem dependências externas.
 * Compatível com Google Authenticator, Authy, etc.
 */
final class Totp
{
    private const DIGITS = 6;
    private const PERIOD = 30;
    private const WINDOW = 1; // ±1 período de tolerância

    public static function gerarSecret(): string
    {
        $bytes = random_bytes(20);
        return self::base32Encode($bytes);
    }

    public static function gerarCodigo(string $secret, ?int $timestamp = null): string
    {
        $timestamp = $timestamp ?? time();
        $counter = (int) floor($timestamp / self::PERIOD);
        return self::hotp($secret, $counter);
    }

    public static function verificar(string $secret, string $codigo, ?int $timestamp = null): bool
    {
        $codigo = trim($codigo);
        if (!preg_match('/^\d{6}$/', $codigo)) {
            return false;
        }

        $timestamp = $timestamp ?? time();
        $counter = (int) floor($timestamp / self::PERIOD);

        for ($i = -self::WINDOW; $i <= self::WINDOW; $i++) {
            if (hash_equals(self::hotp($secret, $counter + $i), $codigo)) {
                return true;
            }
        }

        return false;
    }

    public static function gerarQrCodeUrl(string $secret, string $email, string $issuer = 'LRV Cloud'): string
    {
        $label = rawurlencode($issuer . ':' . $email);
        $params = http_build_query([
            'secret' => $secret,
            'issuer' => $issuer,
            'algorithm' => 'SHA1',
            'digits' => self::DIGITS,
            'period' => self::PERIOD,
        ]);
        return 'otpauth://totp/' . $label . '?' . $params;
    }

    private static function hotp(string $secret, int $counter): string
    {
        $key = self::base32Decode($secret);
        $msg = pack('J', $counter);
        $hash = hash_hmac('sha1', $msg, $key, true);
        $offset = ord($hash[19]) & 0x0F;
        $code = (
            ((ord($hash[$offset]) & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) << 8) |
            (ord($hash[$offset + 3]) & 0xFF)
        ) % (10 ** self::DIGITS);
        return str_pad((string) $code, self::DIGITS, '0', STR_PAD_LEFT);
    }

    private static function base32Encode(string $data): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $output = '';
        $v = 0;
        $vbits = 0;
        for ($i = 0, $len = strlen($data); $i < $len; $i++) {
            $v = ($v << 8) | ord($data[$i]);
            $vbits += 8;
            while ($vbits >= 5) {
                $vbits -= 5;
                $output .= $alphabet[($v >> $vbits) & 0x1F];
            }
        }
        if ($vbits > 0) {
            $output .= $alphabet[($v << (5 - $vbits)) & 0x1F];
        }
        return $output;
    }

    private static function base32Decode(string $data): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $data = strtoupper(trim($data));
        $output = '';
        $v = 0;
        $vbits = 0;
        for ($i = 0, $len = strlen($data); $i < $len; $i++) {
            $pos = strpos($alphabet, $data[$i]);
            if ($pos === false) {
                continue;
            }
            $v = ($v << 5) | $pos;
            $vbits += 5;
            if ($vbits >= 8) {
                $vbits -= 8;
                $output .= chr(($v >> $vbits) & 0xFF);
            }
        }
        return $output;
    }
}
