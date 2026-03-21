<?php

declare(strict_types=1);

namespace LRV\App\Services\Infra;

use LRV\Core\Settings;

/**
 * Cifra/decifra senhas SSH armazenadas no banco.
 * Usa AES-256-CBC com chave derivada de app.secret_key (settings).
 * Se a chave não estiver configurada, usa uma chave fixa de fallback
 * (menos seguro, mas funcional para ambientes simples).
 */
final class SshCrypto
{
    private const CIPHER = 'AES-256-CBC';

    public static function cifrar(string $texto): string
    {
        if ($texto === '') return '';
        $key = self::chave();
        $iv  = random_bytes(16);
        $enc = openssl_encrypt($texto, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv);
        if ($enc === false) throw new \RuntimeException('Falha ao cifrar senha SSH.');
        return base64_encode($iv . $enc);
    }

    public static function decifrar(string $cifrado): string
    {
        if ($cifrado === '') return '';
        $raw = base64_decode($cifrado, true);
        if ($raw === false || strlen($raw) < 17) return '';
        $iv  = substr($raw, 0, 16);
        $enc = substr($raw, 16);
        $dec = openssl_decrypt($enc, self::CIPHER, self::chave(), OPENSSL_RAW_DATA, $iv);
        return $dec === false ? '' : $dec;
    }

    private static function chave(): string
    {
        $secret = (string)Settings::obter('app.secret_key', '');
        if ($secret === '') {
            throw new \RuntimeException('app.secret_key não configurado. Defina nas configurações do sistema.');
        }
        // Deriva 32 bytes para AES-256
        return substr(hash('sha256', $secret, true), 0, 32);
    }
}
