<?php

declare(strict_types=1);

namespace LRV\App\Services\Infra;

use LRV\Core\BancoDeDados;

/**
 * Isolamento por cliente em servidores gerenciados (aPanel, sem container por cliente).
 *
 * Cada cliente ganha um usuário Linux próprio (lrv_c<id>), SEM sudo. Ele é dono das
 * pastas do cliente (deploys e pastas de dados), roda o próprio PM2 e é quem executa
 * o console, o Git Deploy e o gerenciador de arquivos. O grupo dos arquivos é o grupo
 * do PHP-FPM/Nginx (ex.: www), para o site continuar sendo servido; "outros" ficam sem
 * acesso. Assim o sistema operacional garante que um cliente não lê nem altera nada de
 * outro cliente, e o cliente pode rodar qualquer comando dentro do que é dele.
 *
 * O usuário SSH do painel (root ou um usuário com sudo NOPASSWD) só é usado pelo painel,
 * com comandos fixos, para criar o usuário e ajustar donos.
 */
final class UsuarioClienteService
{
    /** Nome do usuário Linux do cliente. */
    public static function nomeUsuario(int $clienteId): string
    {
        if ($clienteId <= 0) {
            throw new \InvalidArgumentException('Cliente inválido.');
        }
        return 'lrv_c' . $clienteId;
    }

    public static function homeUsuario(int $clienteId): string
    {
        return '/home/' . self::nomeUsuario($clienteId);
    }

    /** O servidor é gerenciado (host compartilhado) e por isso usa usuário por cliente? */
    public static function servidorIsolado(int $serverId): bool
    {
        if ($serverId <= 0) {
            return false;
        }
        try {
            $st = BancoDeDados::pdo()->prepare('SELECT is_managed_server FROM servers WHERE id = :id LIMIT 1');
            $st->execute([':id' => $serverId]);
            return (int) $st->fetchColumn() === 1;
        } catch (\Throwable) {
            return false;
        }
    }

    /** Prefixo para comandos ADMINISTRATIVOS do painel (fixos, nunca do cliente). */
    public static function prefixoAdmin(string $sshUser): string
    {
        return $sshUser === 'root' ? '' : 'sudo -n ';
    }

    /**
     * Cria (se não existir) o usuário do cliente, sem sudo e com home 700.
     * Idempotente. Retorna o nome do usuário.
     *
     * @param callable $runCmd fn(string $cmd, int $timeout = 0): array{saida:string}
     */
    public static function garantir(callable $runCmd, int $clienteId, string $sshUser): string
    {
        $u = self::nomeUsuario($clienteId);
        $h = self::homeUsuario($clienteId);
        $s = self::prefixoAdmin($sshUser);
        $eu = escapeshellarg($u);
        $eh = escapeshellarg($h);
        $cmd = 'if id -u ' . $eu . ' >/dev/null 2>&1; then echo LRV_USR_OK; else '
            . $s . 'useradd -m -d ' . $eh . ' -s /bin/bash -U ' . $eu . ' 2>&1 && echo LRV_USR_OK; fi; '
            . $s . 'chmod 700 ' . $eh . ' 2>/dev/null; '
            // Garante que o usuário não tem sudo nem está em grupos administrativos
            . 'for g in sudo wheel admin docker; do ' . $s . 'gpasswd -d ' . $eu . ' "$g" >/dev/null 2>&1; done; '
            . $s . 'rm -f ' . escapeshellarg('/etc/sudoers.d/' . $u) . ' 2>/dev/null; true';
        $r = $runCmd($cmd, 60);
        if (!str_contains((string) ($r['saida'] ?? ''), 'LRV_USR_OK')) {
            throw new \RuntimeException('Não foi possível preparar o usuário do cliente no servidor: ' . trim((string) ($r['saida'] ?? '')));
        }
        return $u;
    }

    /** Usuário/grupo do PHP-FPM do servidor (quem serve os sites). Nunca root. */
    public static function grupoWeb(callable $runCmd): string
    {
        try {
            $r = $runCmd(
                "ps -o group= -C php-fpm 2>/dev/null | sort -u | grep -v '^root$' | head -1;"
                . " id -gn www 2>/dev/null; id -gn www-data 2>/dev/null; true",
                30
            );
            foreach (preg_split('/\R/', trim((string) ($r['saida'] ?? ''))) ?: [] as $g) {
                $g = trim($g);
                if ($g !== '' && $g !== 'root' && preg_match('/^[A-Za-z0-9._-]{1,32}$/', $g) === 1) {
                    return $g;
                }
            }
        } catch (\Throwable) {}
        return 'www-data';
    }

    /**
     * Passa uma pasta para o cliente: dono lrv_c<id>, grupo do web, "outros" sem acesso,
     * setgid nas pastas (arquivos novos herdam o grupo do web).
     * As pastas liberadas para gravação ganham escrita para o grupo (PHP grava uploads).
     *
     * @param list<string> $gravaveis caminhos absolutos dentro de $path
     */
    public static function adotarPasta(callable $runCmd, int $clienteId, string $sshUser, string $path, string $grupoWeb, array $gravaveis = []): void
    {
        $u = escapeshellarg(self::nomeUsuario($clienteId));
        $g = escapeshellarg($grupoWeb);
        $p = escapeshellarg($path);
        $s = self::prefixoAdmin($sshUser);
        // Só refaz o ajuste recursivo se achar algo fora do padrão (rápido nos próximos deploys)
        $cmd = $s . 'mkdir -p ' . $p
            . ' && if [ -n "$(' . $s . 'find ' . $p . ' \( ! -user ' . $u . ' -o ! -group ' . $g . ' -o -perm /o=rwx \) -print -quit 2>/dev/null)" ]; then '
            . $s . 'timeout 170 chown -R ' . $u . ':' . $g . ' ' . $p
            . ' && ' . $s . 'timeout 170 chmod -R u+rwX,g+rX,g-w,o-rwx ' . $p
            . ' && ' . $s . 'timeout 120 find ' . $p . ' -type d -exec chmod g+s {} +; fi';
        foreach ($gravaveis as $alvo) {
            $a = escapeshellarg($alvo);
            $cmd .= ' ; (' . $s . 'mkdir -p ' . $a . ' && ' . $s . 'chown -R ' . $u . ':' . $g . ' ' . $a
                . ' && ' . $s . 'chmod -R ug+rwX,o-rwx ' . $a . ') 2>/dev/null';
        }
        // O Nginx precisa atravessar as pastas-pai (ex.: /var/www) até o site
        $cmd .= ' ; ' . $s . 'chmod o+x ' . escapeshellarg(dirname($path)) . ' 2>/dev/null; true';
        $runCmd($cmd, 180);
    }

    /**
     * Envolve um comando para rodar como o usuário do cliente, com ambiente limpo,
     * HOME próprio e PATH com node/npm/pnpm/pm2/git/composer do servidor.
     * O comando do cliente vai inteiro dentro de um único argumento (escapeshellarg).
     */
    public static function comoCliente(int $clienteId, string $sshUser, string $script, string $cwd = ''): string
    {
        $u = self::nomeUsuario($clienteId);
        $h = self::homeUsuario($clienteId);
        $descobrePath = '__X=""; for b in node npm npx pnpm yarn corepack pm2 git composer; do'
            . ' c=$(command -v "$b" 2>/dev/null) && __X="$__X:$(dirname "$c")"; done; ';
        // umask 027: arquivos novos ficam legíveis pelo grupo do web e fechados para os demais
        $interno = 'umask 027; ' . ($cwd !== '' ? 'cd ' . escapeshellarg($cwd) . ' || exit 1; ' : 'cd ~ || exit 1; ') . $script;
        $troca = $sshUser === 'root'
            ? 'runuser -u ' . escapeshellarg($u) . ' -- '
            : 'sudo -n -u ' . escapeshellarg($u) . ' -- ';
        return $descobrePath . $troca
            . 'env -i HOME=' . escapeshellarg($h) . ' USER=' . escapeshellarg($u) . ' LOGNAME=' . escapeshellarg($u)
            . ' PATH="/usr/local/bin:/usr/bin:/bin$__X" LANG=C.UTF-8 TERM=dumb'
            . ' bash --noprofile --norc -c ' . escapeshellarg($interno);
    }

    /**
     * Configura o PM2 do cliente para voltar sozinho após reiniciar o servidor
     * (serviço pm2-lrv_c<id>). Comando administrativo fixo. Retorna a saída.
     */
    public static function garantirPm2Startup(callable $runCmd, int $clienteId, string $sshUser): string
    {
        $u = self::nomeUsuario($clienteId);
        $h = self::homeUsuario($clienteId);
        $s = self::prefixoAdmin($sshUser);
        $cmd = 'P=$(command -v pm2 2>/dev/null || ls /usr/local/bin/pm2 2>/dev/null);'
            . ' if [ -z "$P" ]; then echo "Aviso: pm2 não encontrado no servidor.";'
            . ' elif systemctl list-unit-files ' . escapeshellarg('pm2-' . $u . '.service') . ' 2>/dev/null | grep -q pm2-; then echo "Inicialização automática do PM2 já configurada.";'
            . ' else ' . $s . 'env PATH="$PATH:/usr/local/bin:$(dirname "$P")" "$P" startup systemd -u ' . escapeshellarg($u) . ' --hp ' . escapeshellarg($h) . ' 2>&1 | tail -3'
            . ' || echo "Aviso: não foi possível configurar a inicialização automática do PM2."; fi; true';
        return trim((string) (($runCmd($cmd, 60))['saida'] ?? ''));
    }
}
