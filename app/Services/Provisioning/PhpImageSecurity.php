<?php

declare(strict_types=1);

namespace LRV\App\Services\Provisioning;

/** Verifica o binário da imagem, pois a tag não garante a versão do PHP. */
final class PhpImageSecurity
{
    public const OK = 'lrv-php-security-ok:';

    public static function checkCode(): string
    {
        return <<<'PHP'
$minimums = ['8.2' => '8.2.35', '8.3' => '8.3.36', '8.4' => '8.4.27', '8.5' => '8.5.12'];
$version = PHP_VERSION;
$branch = implode('.', array_slice(explode('.', $version), 0, 2));
if (!isset($minimums[$branch]) || version_compare($version, $minimums[$branch], '<')) {
    fwrite(STDERR, "PHP $version sem correção confirmada para CVE-2026-109618/CVE-2026-109619. Atualize a imagem ou revise o backport do fornecedor.\n");
    exit(1);
}
echo 'lrv-php-security-ok:', $version, "\n";
PHP;
    }

    public static function prepareCommand(string $image): string
    {
        $image = trim($image);
        if ($image === '' || str_contains($image, "\0")) {
            throw new \InvalidArgumentException('Imagem PHP inválida.');
        }
        $quoted = escapeshellarg($image);

        // Imagens locais corrigidas continuam disponíveis quando não há registry.
        return '(docker pull ' . $quoted . ' >&2 || docker image inspect ' . $quoted . ' >/dev/null 2>&1)'
            . ' && docker run --rm --network none --read-only --cap-drop ALL'
            . ' --security-opt no-new-privileges --pids-limit 64 --memory 128m'
            . ' --entrypoint php ' . $quoted . ' -n -r ' . escapeshellarg(self::checkCode());
    }

    public static function existingContainerCommand(string $container): string
    {
        return 'docker exec ' . escapeshellarg($container) . ' php -n -r ' . escapeshellarg(self::checkCode());
    }
}
