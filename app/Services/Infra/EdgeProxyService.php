<?php

declare(strict_types=1);

namespace LRV\App\Services\Infra;

use LRV\Core\BancoDeDados;

/**
 * Proxy de borda (Traefik com "file provider") para servidores sem IP público próprio.
 *
 * Fluxo: domínio no DNS -> IP do dedicado -> Traefik (emite o SSL) -> http://<IP interno>:80
 * -> Nginx do servidor -> site/container do cliente.
 *
 * O painel só cria/apaga arquivos "lrv-<domínio>.yml" na pasta de regras. Regras de
 * outros sistemas (sem o prefixo lrv-) nunca são lidas nem alteradas.
 */
final class EdgeProxyService
{
    /** Configuração da borda do servidor, ou null se não usa borda (ou migration ausente). */
    public static function config(int $serverId): ?array
    {
        if ($serverId <= 0) return null;
        try {
            $st = BancoDeDados::pdo()->prepare(
                'SELECT edge_enabled, edge_ssh_host, edge_ssh_port, edge_ssh_user, edge_ssh_password,
                        edge_rules_dir, edge_backend_ip, edge_cert_resolver
                 FROM servers WHERE id = :id LIMIT 1'
            );
            $st->execute([':id' => $serverId]);
            $r = $st->fetch();
        } catch (\Throwable) {
            return null; // migration 0102 ainda não aplicada: comportamento antigo
        }
        if (!is_array($r) || (int) ($r['edge_enabled'] ?? 0) !== 1) return null;
        return $r;
    }

    public static function ativo(int $serverId): bool
    {
        return self::config($serverId) !== null;
    }

    /** Valida os campos vindos do formulário. Retorna mensagem de erro ou null. */
    public static function validarCampos(string $host, int $port, string $user, string $dir, string $backendIp, string $resolver): ?string
    {
        if ($host === '' || (!filter_var($host, FILTER_VALIDATE_IP) && !\LRV\App\Services\Seguranca\ValidadorInfra::dominioValido($host))) {
            return 'Proxy de borda: host SSH inválido.';
        }
        if ($port < 1 || $port > 65535) return 'Proxy de borda: porta SSH inválida.';
        if (!preg_match('/^[a-z_][a-z0-9._-]{0,31}$/i', $user)) return 'Proxy de borda: usuário SSH inválido.';
        if (!self::dirValido($dir)) return 'Proxy de borda: pasta das regras inválida (ex.: /opt/osops-edge/dynamic).';
        if (!filter_var($backendIp, FILTER_VALIDATE_IP)) return 'Proxy de borda: IP interno do servidor inválido.';
        if (!preg_match('/^[A-Za-z0-9_-]{1,64}$/', $resolver)) return 'Proxy de borda: nome do certResolver inválido.';
        return null;
    }

    private static function dirValido(string $dir): bool
    {
        return preg_match('#^/[A-Za-z0-9._-]+(/[A-Za-z0-9._-]+)*$#', $dir) === 1
            && !preg_match('#(^|/)\.\.?(/|$)#', $dir)
            && !in_array($dir, ['/', '/etc', '/root', '/usr', '/var', '/opt'], true);
    }

    private static function nomeArquivo(string $domain): string
    {
        return 'lrv-' . str_replace('.', '-', $domain) . '.yml';
    }

    /** Cria/atualiza a regra do domínio no proxy de borda. */
    public static function publicar(int $serverId, string $domain): array
    {
        $cfg = self::config($serverId);
        if ($cfg === null) return ['ok' => true, 'logs' => []];
        $domain = strtolower(trim($domain));
        if (!\LRV\App\Services\Seguranca\ValidadorInfra::dominioValido($domain)) {
            return ['ok' => false, 'logs' => ['Borda: domínio inválido.']];
        }
        $dir = rtrim((string) ($cfg['edge_rules_dir'] ?? ''), '/');
        $ip = (string) ($cfg['edge_backend_ip'] ?? '');
        $resolver = (string) ($cfg['edge_cert_resolver'] ?: 'letsencrypt');
        if (!self::dirValido($dir) || !filter_var($ip, FILTER_VALIDATE_IP) || !preg_match('/^[A-Za-z0-9_-]{1,64}$/', $resolver)) {
            return ['ok' => false, 'logs' => ['Borda: configuração do servidor inválida.']];
        }
        $nome = 'lrv-' . str_replace('.', '-', $domain);
        $url = 'http://' . (str_contains($ip, ':') ? '[' . $ip . ']' : $ip) . ':80';
        $yml = "# Gerado pelo painel LRV Cloud. Não editar: é sobrescrito a cada publicação.\n"
            . "http:\n"
            . "  routers:\n"
            . "    {$nome}:\n"
            . "      rule: Host(`{$domain}`)\n"
            . "      entryPoints: [websecure]\n"
            . "      tls: {certResolver: {$resolver}}\n"
            . "      service: {$nome}\n"
            . "  services:\n"
            . "    {$nome}:\n"
            . "      loadBalancer:\n"
            . "        passHostHeader: true\n"
            . "        servers:\n"
            . "          - url: {$url}\n";
        $alvo = $dir . '/' . self::nomeArquivo($domain);
        $tmp = $dir . '/.' . self::nomeArquivo($domain) . '.tmp';
        // Escrita atômica (tmp + mv) para o Traefik nunca ler arquivo pela metade
        $cmd = 'printf %s ' . escapeshellarg(base64_encode($yml)) . ' | base64 -d > ' . escapeshellarg($tmp)
            . ' && mv -f ' . escapeshellarg($tmp) . ' ' . escapeshellarg($alvo) . ' && echo LRV_EDGE_OK';
        $r = self::exec($cfg, $cmd);
        $ok = str_contains((string) ($r['saida'] ?? ''), 'LRV_EDGE_OK');
        return ['ok' => $ok, 'logs' => [$ok
            ? 'Borda: domínio ' . $domain . ' publicado no proxy (SSL emitido pelo proxy).'
            : 'Borda: falha ao gravar a regra do proxy: ' . mb_substr(trim((string) ($r['saida'] ?? '')), 0, 300)]];
    }

    /** Remove a regra do domínio (só arquivos lrv-*). */
    public static function remover(int $serverId, string $domain): void
    {
        $cfg = self::config($serverId);
        if ($cfg === null) return;
        $domain = strtolower(trim($domain));
        if (!\LRV\App\Services\Seguranca\ValidadorInfra::dominioValido($domain)) return;
        $dir = rtrim((string) ($cfg['edge_rules_dir'] ?? ''), '/');
        if (!self::dirValido($dir)) return;
        try {
            self::exec($cfg, 'rm -f ' . escapeshellarg($dir . '/' . self::nomeArquivo($domain)));
        } catch (\Throwable) {}
    }

    /** Teste de conexão + escrita na pasta (para o botão do formulário). */
    public static function testar(array $cfg): array
    {
        $dir = rtrim((string) ($cfg['edge_rules_dir'] ?? ''), '/');
        if (!self::dirValido($dir)) return ['ok' => false, 'erro' => 'Pasta das regras inválida.'];
        $t = escapeshellarg($dir . '/.lrv-teste-' . bin2hex(random_bytes(4)));
        $r = self::exec($cfg, 'touch ' . $t . ' && rm -f ' . $t . ' && echo LRV_EDGE_OK');
        return str_contains((string) ($r['saida'] ?? ''), 'LRV_EDGE_OK')
            ? ['ok' => true]
            : ['ok' => false, 'erro' => 'Sem permissão de escrita na pasta das regras: ' . mb_substr(trim((string) ($r['saida'] ?? '')), 0, 300)];
    }

    private static function exec(array $cfg, string $cmd): array
    {
        $senha = SshCrypto::decifrar((string) ($cfg['edge_ssh_password'] ?? ''));
        return (new SshExecutor())->executarComSenha(
            (string) $cfg['edge_ssh_host'],
            (int) ($cfg['edge_ssh_port'] ?: 22),
            (string) $cfg['edge_ssh_user'],
            $senha,
            $cmd,
            30
        );
    }
}
