<?php

declare(strict_types=1);

namespace LRV\App\Services\Seguranca;

use LRV\Core\BancoDeDados;

/**
 * Validações de tudo que vira comando/arquivo de configuração no SERVIDOR.
 *
 * Regra geral: o que vem do cliente nunca é confiável. Qualquer valor que acabe
 * num comando SSH, num vhost do Nginx, num php.ini ou num caminho de disco passa
 * por aqui primeiro. Usado pelo painel, pela API e de novo na hora de executar
 * (defesa em profundidade: registros antigos inválidos também são barrados).
 */
final class ValidadorInfra
{
    /** Pasta onde os sites dos clientes ficam no servidor. */
    public const BASE_SITES = '/var/www';

    public static function dominioValido(string $d): bool
    {
        $d = strtolower(trim($d));
        return strlen($d) <= 253
            && preg_match('/^(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$/', $d) === 1;
    }

    public static function exigirDominio(string $d): string
    {
        $d = strtolower(trim($d));
        if (!self::dominioValido($d)) {
            throw new \InvalidArgumentException('Domínio inválido.');
        }
        return $d;
    }

    public static function phpVersionValida(string $v): bool
    {
        return preg_match('/^[5-9]\.[0-9]$/', trim($v)) === 1;
    }

    public static function exigirPhpVersion(string $v): string
    {
        $v = trim($v);
        if (!self::phpVersionValida($v)) {
            throw new \InvalidArgumentException('Versão do PHP inválida.');
        }
        return $v;
    }

    /**
     * Repositório Git: só https:// ou git@host:caminho, sem caracteres de shell.
     * Bloqueia file://, ssh://, ext::, opções (--upload-pack) e hosts internos.
     */
    public static function repoUrlValida(string $url): bool
    {
        $url = trim($url);
        if (strlen($url) > 300 || $url === '') {
            return false;
        }
        if (preg_match('#^https://([a-zA-Z0-9.-]+)(:[0-9]{2,5})?/[a-zA-Z0-9._/-]+$#', $url, $m) === 1) {
            return self::hostGitPublico($m[1]);
        }
        if (preg_match('#^git@([a-zA-Z0-9.-]+):[a-zA-Z0-9._/-]+$#', $url, $m) === 1) {
            return self::hostGitPublico($m[1]);
        }
        return false;
    }

    private static function hostGitPublico(string $host): bool
    {
        $host = strtolower($host);
        if ($host === 'localhost' || !str_contains($host, '.') || str_contains($host, '..')) {
            return false;
        }
        // IP literal: só se for público
        if (filter_var($host, FILTER_VALIDATE_IP) !== false) {
            return UrlExterna::ipPublico($host);
        }
        return self::dominioValido($host);
    }

    public static function branchValido(string $b): bool
    {
        $b = trim($b);
        return $b !== '' && strlen($b) <= 100
            && preg_match('#^[A-Za-z0-9][A-Za-z0-9._/-]*$#', $b) === 1
            && !str_contains($b, '..') && !str_ends_with($b, '/') && !str_ends_with($b, '.lock');
    }

    /**
     * Caminho de deploy: sempre dentro de /var/www, sem "..", sem caracteres estranhos,
     * nunca a pasta compartilhada /var/www/html e nunca sobreposto ao de OUTRO cliente.
     *
     * @return string caminho normalizado
     */
    /**
     * @param bool $legado true = validação na hora de EXECUTAR um deploy já salvo: aceita
     *        também pastas antigas em /www/wwwroot e /var/www/html (desde que não colidam
     *        com outro cliente). Pastas novas/alteradas sempre ficam em /var/www.
     */
    public static function exigirCaminhoDeploy(string $path, int $clienteId, ?int $ignorarDeployId = null, bool $legado = false): string
    {
        $path = rtrim(trim(str_replace('\\', '/', $path)), '/');
        $re = $legado
            ? '#^/(var/www|www/wwwroot)(/[A-Za-z0-9][A-Za-z0-9._-]{0,80}){1,4}$#'
            : '#^/var/www(/[A-Za-z0-9][A-Za-z0-9._-]{0,80}){1,4}$#';
        if (!preg_match($re, $path)) {
            throw new \InvalidArgumentException('Pasta de publicação inválida: use algo como /var/www/meu-site (só letras, números, ponto, hífen e underline).');
        }
        foreach (explode('/', $path) as $seg) {
            if ($seg === '..' || $seg === '.') {
                throw new \InvalidArgumentException('Pasta de publicação inválida.');
            }
        }
        if (!$legado && in_array($path, ['/var/www/html', '/var/www/default', '/var/www/lrv'], true)) {
            throw new \InvalidArgumentException('Esta pasta é compartilhada do servidor e não pode ser usada. Escolha outro nome.');
        }

        // Não pode coincidir nem ficar dentro/por cima da pasta de outro cliente
        $pdo = BancoDeDados::pdo();
        $st = $pdo->prepare('SELECT id, client_id, deploy_path FROM git_deployments WHERE client_id <> :c');
        $st->execute([':c' => $clienteId]);
        foreach ($st->fetchAll() ?: [] as $r) {
            if ($ignorarDeployId !== null && (int) $r['id'] === $ignorarDeployId) {
                continue;
            }
            $outro = rtrim((string) $r['deploy_path'], '/');
            if ($outro === '') {
                continue;
            }
            if ($outro === $path || str_starts_with($path . '/', $outro . '/') || str_starts_with($outro . '/', $path . '/')) {
                throw new \InvalidArgumentException('Esta pasta já está em uso no servidor. Escolha outro nome.');
            }
        }
        return $path;
    }

    /**
     * Configurações de PHP do site: só chaves conhecidas e valores numéricos com teto.
     * (Evita injetar diretivas como auto_prepend_file no php.ini do servidor.)
     *
     * @return array<string,string>
     */
    public static function phpSettings(array $in): array
    {
        $regras = [
            'memory_limit'        => ['/^\d{1,4}M$/', 1024],
            'upload_max_filesize' => ['/^\d{1,4}M$/', 512],
            'post_max_size'       => ['/^\d{1,4}M$/', 512],
            'max_execution_time'  => ['/^\d{1,4}$/', 600],
            'max_input_vars'      => ['/^\d{1,5}$/', 20000],
        ];
        $out = [];
        foreach ($regras as $k => [$re, $teto]) {
            $v = strtoupper(trim((string) ($in[$k] ?? '')));
            if ($v === '' || preg_match($re, $v) !== 1) {
                continue;
            }
            $n = (int) $v;
            if ($n <= 0) {
                continue;
            }
            $n = min($n, $teto);
            $out[$k] = str_ends_with($v, 'M') ? $n . 'M' : (string) $n;
        }
        return $out;
    }

    /** Nome de usuário/banco MySQL seguro. */
    public static function identificadorMysqlValido(string $s): bool
    {
        return preg_match('/^[A-Za-z0-9_]{1,32}$/', $s) === 1;
    }

    /** Agenda de cron: 5 campos (ou @hourly etc.), sem quebras de linha. */
    public static function cronValido(string $s): bool
    {
        $s = trim($s);
        if ($s === '' || strlen($s) > 100 || preg_match('/[\r\n]/', $s)) {
            return false;
        }
        if (preg_match('/^@(hourly|daily|weekly|monthly|yearly|annually|midnight|reboot)$/', $s)) {
            return true;
        }
        return preg_match('#^([0-9*/,\-]+|[A-Za-z]{3})(\s+([0-9*/,\-]+|[A-Za-z]{3})){4}$#', $s) === 1;
    }
}
