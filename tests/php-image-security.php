<?php

declare(strict_types=1);

// Doubles isolados: nenhum banco, SSH ou Docker real é acessado.
namespace LRV\Core {
    final class Settings {
        private static array $values = [];
        public static function obter(string $key, mixed $default = null): mixed { return self::$values[$key] ?? $default; }
        public static function definir(string $key, mixed $value): void { self::$values[$key] = $value; }
    }
    final class ConfiguracoesSistema {
        public static function sshKeyDir(): string { return '/tmp/test-keys'; }
    }
    final class BancoDeDados {
        public static function pdo(): object {
            return new class {
                public function prepare(string $sql): object {
                    return new class($sql) {
                        public function __construct(private string $sql) {}
                        public function execute(array $params): bool { return true; }
                        public function fetch(): array {
                            if (str_contains($this->sql, 'FROM applications')) {
                                return ['id' => 7, 'slug' => 'php-laravel', 'server_id' => 1,
                                    'client_id' => 0, 'port' => 10007, 'docker_image' => 'example/php:test'];
                            }
                            return ['id' => 1, 'status' => 'active', 'ip_address' => '127.0.0.1',
                                'ssh_user' => 'test', 'ssh_key_id' => 'test'];
                        }
                    };
                }
            };
        }
    }
}

namespace LRV\App\Services\Infra {
    final class SshExecutor {
        public static string $mode = 'reject';
        public static array $commands = [];
        public function executar(string $host, int $port, string $user, string $key, string $cmd, int $timeout): array {
            self::$commands[] = $cmd;
            if ($cmd === 'docker version') return ['ok' => true, 'saida' => 'Version: test'];
            if (str_contains($cmd, 'lrv-php-security-ok:')) {
                return ['ok' => self::$mode !== 'reject',
                    'saida' => self::$mode === 'accept' ? 'lrv-php-security-ok:8.3.36' : 'probe failed'];
            }
            if (str_starts_with($cmd, 'docker rm ')) return ['ok' => true, 'saida' => 'removed'];
            throw new \RuntimeException('TEST_STOP_BEFORE_START');
        }
    }
}

namespace {
    use LRV\App\Services\Provisioning\PhpImageSecurity;
    use LRV\App\Services\Provisioning\DockerCli;
    use LRV\App\Services\Deploy\AppInstallService;
    use LRV\App\Services\Infra\SshExecutor;

    require __DIR__ . '/../app/Services/Provisioning/PhpImageSecurity.php';
    require __DIR__ . '/../app/Services/Provisioning/DockerCli.php';
    require __DIR__ . '/../app/Services/Provisioning/ServerSetupService.php';
    require __DIR__ . '/../app/Services/Deploy/AppInstallService.php';

    function check(bool $ok, string $message): void {
        if (!$ok) throw new \RuntimeException($message);
    }
    function runCommand(string $command, ?array $env = null): array {
        $process = proc_open($command, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, null, $env);
        if (!is_resource($process)) throw new \RuntimeException('Não foi possível iniciar o teste.');
        fclose($pipes[0]);
        $out = stream_get_contents($pipes[1]); fclose($pipes[1]);
        $err = stream_get_contents($pipes[2]); fclose($pipes[2]);
        return [proc_close($process), $out, $err];
    }

    $cases = ['7.4.34' => false, '8.0.30' => false, '8.1.34' => false,
        '8.2.34' => false, '8.2.35' => true, '8.2.36' => true,
        '8.3.35' => false, '8.3.36RC1' => false, '8.3.36' => true,
        '8.4.26' => false, '8.4.27' => true, '8.5.11' => false, '8.5.12' => true,
        '8.6.0' => false];
    foreach ($cases as $version => $expected) {
        $code = str_replace('$version = PHP_VERSION;', '$version = ' . var_export($version, true) . ';', PhpImageSecurity::checkCode());
        [$exit, $out] = runCommand(escapeshellarg(PHP_BINARY) . ' -n -r ' . escapeshellarg($code));
        check(($exit === 0) === $expected, 'Política incorreta para ' . $version);
        check(str_contains($out, PhpImageSecurity::OK) === $expected, 'Marcador incorreto para ' . $version);
    }

    $tmp = sys_get_temp_dir() . '/lrv-php-test-' . bin2hex(random_bytes(8));
    mkdir($tmp, 0700);
    try {
        file_put_contents($tmp . '/docker', <<<'SH'
#!/bin/sh
printf '%s\n' "$1" >> "$TEST_LOG"
case "$1" in
  ps) case "$TEST_MODE" in existing-*) echo lrv_phpmyadmin ;; esac ;;
  exec) test "$TEST_MODE" = existing-ok || exit 1; echo lrv-php-security-ok:8.3.36 ;;
  pull) test "$TEST_MODE" = pull-ok ;;
  image) test "$TEST_MODE" = local-ok || test "$TEST_MODE" = probe-fails ;;
  run) test "$TEST_MODE" != probe-fails || exit 1; echo lrv-php-security-ok:8.3.36 ;;
  *) exit 99 ;;
esac
SH);
        chmod($tmp . '/docker', 0700);
        foreach (['pull-ok' => true, 'local-ok' => true, 'missing-image' => false, 'probe-fails' => false] as $mode => $expected) {
            $env = array_merge(getenv(), ['PATH' => $tmp . ':' . getenv('PATH'), 'TEST_MODE' => $mode, 'TEST_LOG' => $tmp . '/calls']);
            [$exit, $out] = runCommand(PhpImageSecurity::prepareCommand('example/php:test'), $env);
            check(($exit === 0) === $expected, 'Falha no fluxo Docker: ' . $mode);
            check(str_contains($out, PhpImageSecurity::OK) === $expected, 'Aceite indevido: ' . $mode);
        }
        $env['TEST_MODE'] = 'pull-ok';
        runCommand(PhpImageSecurity::prepareCommand('image; touch ' . $tmp . '/injected'), $env);
        check(!file_exists($tmp . '/injected'), 'Referência da imagem não foi escapada.');

        $setup = new \LRV\App\Services\Provisioning\ServerSetupService(new SshExecutor());
        $method = new \ReflectionMethod($setup, 'definirPassos');
        $steps = $method->invoke($setup, ['id' => 1, 'ip_address' => '127.0.0.1']);
        $pma = array_values(array_filter($steps, static fn(array $step): bool => $step['name'] === 'Instalar phpMyAdmin (Docker)'))[0];
        foreach (['pull-ok' => true, 'missing-image' => false, 'probe-fails' => false, 'existing-ok' => true, 'existing-bad' => false] as $mode => $expected) {
            file_put_contents($tmp . '/calls', '');
            $env['TEST_MODE'] = $mode;
            [$exit, $out] = runCommand($pma['cmd'], $env);
            check(($exit === 0) === $expected, 'Exit code phpMyAdmin incorreto: ' . $mode);
            check(str_contains($out, 'lrv-pma-ok') === $expected, 'Aceite phpMyAdmin incorreto: ' . $mode);
            $calls = file($tmp . '/calls', FILE_IGNORE_NEW_LINES);
            $runs = count(array_filter($calls, static fn(string $call): bool => $call === 'run'));
            check($runs === ($mode === 'pull-ok' ? 2 : ($mode === 'probe-fails' ? 1 : 0)), 'phpMyAdmin criado sem validação: ' . $mode);
        }
    } finally {
        foreach (glob($tmp . '/*') as $file) unlink($file);
        rmdir($tmp);
    }

    foreach (['reject', 'missing-marker', 'accept'] as $mode) {
        SshExecutor::$mode = $mode;
        SshExecutor::$commands = [];
        try {
            (new AppInstallService(new DockerCli(new SshExecutor())))->instalar(7, static function (string $message): void {});
            throw new \RuntimeException('O teste deveria interromper antes da criação.');
        } catch (\RuntimeException $e) {
            if ($mode === 'accept') check($e->getMessage() === 'TEST_STOP_BEFORE_START', 'Falha antes do ponto esperado: ' . $e->getMessage());
        }
        $removals = array_filter(SshExecutor::$commands, static fn(string $cmd): bool => str_starts_with($cmd, 'docker rm '));
        check((count($removals) > 0) === ($mode === 'accept'), 'Container anterior removido antes de validar imagem: ' . $mode);
    }
    echo 'OK: 14 versões, 4 fluxos Docker, 5 cenários phpMyAdmin, escaping e 3 cenários de instalação.', PHP_EOL;
}
