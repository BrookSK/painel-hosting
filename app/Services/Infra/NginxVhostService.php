<?php

declare(strict_types=1);

namespace LRV\App\Services\Infra;

use LRV\Core\BancoDeDados;
use LRV\Core\ConfiguracoesSistema;

/**
 * Gerencia vhosts Nginx e certificados SSL nos nodes.
 * Cria configuração de reverse proxy para aplicações dos clientes.
 */
final class NginxVhostService
{
    /**
     * Retorna o caminho dos vhosts Nginx para o servidor.
     * Se tem nginx_vhost_path customizado (ex: aaPanel), usa ele.
     * Senão usa o padrão /etc/nginx/sites-available/lrv.
     */
    private function getVhostPath(array $srv): string
    {
        $custom = trim((string)($srv['nginx_vhost_path'] ?? ''));
        return $custom !== '' ? rtrim($custom, '/') : '/etc/nginx/sites-available/lrv';
    }

    /**
     * Verifica se o servidor usa caminho customizado de vhosts (aaPanel, etc).
     * Nesse caso, não precisa de symlink pra sites-enabled.
     */
    private function isCustomNginxPath(array $srv): bool
    {
        return trim((string)($srv['nginx_vhost_path'] ?? '')) !== '';
    }

    /**
     * Retorna o comando de reload do Nginx para o servidor.
     */
    private function getNginxReloadCmd(array $srv): string
    {
        $custom = trim((string)($srv['nginx_reload_cmd'] ?? ''));
        return $custom !== '' ? $custom : 'systemctl reload nginx';
    }

    public function criarVhost(int $serverId, string $domain, int $port, bool $ssl = true): array
    {
        $pdo = BancoDeDados::pdo();
        $srv = $this->getServer($pdo, $serverId);
        if (!$srv) return ['ok' => false, 'erro' => 'Servidor não encontrado.'];

        $logs = [];
        $vhostPath = $this->getVhostPath($srv);
        $isCustom = $this->isCustomNginxPath($srv);
        $reloadCmd = $this->getNginxReloadCmd($srv);
        $sudo = $this->needsSudo($srv) ? 'sudo ' : '';

        // 1. Criar config Nginx
        $vhostName = $this->getVhostFileName($domain, $isCustom);
        $config = $this->gerarConfig($domain, $port);

        $ssh = new SshExecutor();
        $this->configurarSsh($ssh, $srv);

        // Escrever config — se caminho customizado (aaPanel), não faz symlink
        $b64 = base64_encode($config);
        if ($isCustom) {
            $cmd = $sudo . 'mkdir -p ' . escapeshellarg($vhostPath)
                . ' && echo ' . escapeshellarg($b64) . ' | base64 -d | ' . $sudo . 'tee ' . escapeshellarg($vhostPath . '/' . $vhostName . '.conf') . ' > /dev/null'
                . ' && ' . $sudo . 'nginx -t 2>&1 && ' . $sudo . $reloadCmd . ' 2>&1 && echo lrv-vhost-ok';
        } else {
            $cmd = $sudo . 'mkdir -p /etc/nginx/sites-available/lrv'
                . ' && echo ' . escapeshellarg($b64) . ' | base64 -d | ' . $sudo . 'tee /etc/nginx/sites-available/lrv/' . escapeshellarg($vhostName) . '.conf > /dev/null'
                . ' && ' . $sudo . 'ln -sf /etc/nginx/sites-available/lrv/' . escapeshellarg($vhostName) . '.conf /etc/nginx/sites-enabled/' . escapeshellarg($vhostName) . '.conf'
                . ' && ' . $sudo . 'nginx -t 2>&1 && ' . $sudo . $reloadCmd . ' 2>&1 && echo lrv-vhost-ok';
        }

        $result = $this->exec($ssh, $srv, $cmd);
        $logs[] = 'Vhost: ' . trim($result['saida'] ?? '');

        if (!str_contains($result['saida'] ?? '', 'lrv-vhost-ok')) {
            return ['ok' => false, 'erro' => 'Falha ao criar vhost Nginx.', 'logs' => $logs];
        }

        // 2. Gerar SSL com Certbot (se solicitado)
        if ($ssl) {
            $certCmd = 'certbot --nginx -d ' . escapeshellarg($domain) . ' --non-interactive --agree-tos --email admin@' . escapeshellarg($domain) . ' --no-redirect 2>&1 || certbot --nginx -d ' . escapeshellarg($domain) . ' --non-interactive --agree-tos --register-unsafely-without-email --no-redirect 2>&1; echo lrv-cert-done';
            $certResult = $this->exec($ssh, $srv, $certCmd);
            $certOutput = trim($certResult['saida'] ?? '');
            $logs[] = 'SSL: ' . $certOutput;

            $sslOk = str_contains($certOutput, 'Successfully') || str_contains($certOutput, 'Certificate not yet due for renewal') || str_contains($certOutput, 'Congratulations');
            if (!$sslOk) {
                $logs[] = 'Aviso: SSL pode não ter sido gerado. O site funciona em HTTP.';
            }
        }

        return ['ok' => true, 'logs' => $logs];
    }

    public function removerVhost(int $serverId, string $domain): void
    {
        $pdo = BancoDeDados::pdo();
        $srv = $this->getServer($pdo, $serverId);
        if (!$srv) return;

        $vhostPath = $this->getVhostPath($srv);
        $isCustom = $this->isCustomNginxPath($srv);
        $reloadCmd = $this->getNginxReloadCmd($srv);
        $sudo = $this->needsSudo($srv) ? 'sudo ' : '';
        $vhostName = $this->getVhostFileName($domain, $isCustom);
        $ssh = new SshExecutor();
        $this->configurarSsh($ssh, $srv);

        if ($isCustom) {
            $cmd = $sudo . 'rm -f ' . escapeshellarg($vhostPath . '/' . $vhostName . '.conf')
                . ' && ' . $sudo . 'nginx -t 2>&1 && ' . $sudo . $reloadCmd . ' 2>&1';
        } else {
            $cmd = $sudo . 'rm -f /etc/nginx/sites-enabled/' . escapeshellarg($vhostName) . '.conf'
                . ' /etc/nginx/sites-available/lrv/' . escapeshellarg($vhostName) . '.conf'
                . ' && ' . $sudo . 'nginx -t 2>&1 && ' . $sudo . $reloadCmd . ' 2>&1';
        }

        try { $this->exec($ssh, $srv, $cmd); } catch (\Throwable) {}
    }

    private function gerarConfig(string $domain, int $port): string
    {
        return "server {\n"
            . "    listen 80;\n"
            . "    server_name {$domain};\n"
            . "\n"
            . "    location / {\n"
            . "        proxy_pass http://127.0.0.1:{$port};\n"
            . "        proxy_set_header Host \$host;\n"
            . "        proxy_set_header X-Real-IP \$remote_addr;\n"
            . "        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;\n"
            . "        proxy_set_header X-Forwarded-Proto \$scheme;\n"
            . "        proxy_http_version 1.1;\n"
            . "        proxy_set_header Upgrade \$http_upgrade;\n"
            . "        proxy_set_header Connection \"upgrade\";\n"
            . "        proxy_read_timeout 86400;\n"
            . "    }\n"
            . "}\n";
    }

    private function gerarConfigStaticSite(string $domain, string $rootPath, string $phpVersion = '8.3', bool $isAaPanel = false): string
    {
        // aaPanel usa /tmp/php-cgi-XX.sock, instalação padrão usa /run/php/phpX.X-fpm.sock
        $phpShort = str_replace('.', '', $phpVersion); // "8.3" → "83"
        $fpmSocket = $isAaPanel
            ? '/tmp/php-cgi-' . $phpShort . '.sock'
            : '/run/php/php' . $phpVersion . '-fpm.sock';

        // Se o root termina em /public, adicionar alias para /public/ → root
        $publicAlias = '';
        if (str_ends_with($rootPath, '/public')) {
            $publicAlias = "\n    location ^~ /public/ {\n"
                . "        alias {$rootPath}/;\n"
                . "    }\n";
        }
        return "server {\n"
            . "    listen 80;\n"
            . "    server_name {$domain};\n"
            . "    root {$rootPath};\n"
            . "    index index.php index.html index.htm;\n"
            . $publicAlias
            . "\n"
            . "    location / {\n"
            . "        try_files \$uri \$uri/ /index.php?\$query_string;\n"
            . "    }\n"
            . "\n"
            . "    location ~ \\.php\$ {\n"
            . "        include fastcgi_params;\n"
            . "        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;\n"
            . "        fastcgi_pass unix:{$fpmSocket};\n"
            . "        fastcgi_index index.php;\n"
            . "        fastcgi_read_timeout 300;\n"
            . "    }\n"
            . "\n"
            . "    location ~* \\.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot|map)\$ {\n"
            . "        expires 30d;\n"
            . "        access_log off;\n"
            . "    }\n"
            . "\n"
            . "    gzip on;\n"
            . "    gzip_types text/plain text/css application/json application/javascript text/xml application/xml image/svg+xml;\n"
            . "}\n";
    }


    /**
     * Cria vhost para servir arquivos estáticos de um diretório (Git Deploy).
     */
    public function criarVhostStaticSite(int $serverId, string $domain, string $rootPath, bool $ssl = true, string $phpVersion = '8.3', array $phpSettings = []): array
    {
        $pdo = BancoDeDados::pdo();
        $srv = $this->getServer($pdo, $serverId);
        if (!$srv) return ['ok' => false, 'erro' => 'Servidor não encontrado.'];

        $vhostPath = $this->getVhostPath($srv);
        $isCustom = $this->isCustomNginxPath($srv);
        $reloadCmd = $this->getNginxReloadCmd($srv);

        $logs = [];
        $ssh = new SshExecutor();

        // Detectar pasta de build/public (dist/, build/, public/, out/)
        $detectCmd = 'test -f ' . escapeshellarg($rootPath . '/public/index.php') . ' && echo "public-php"'
            . ' || (test -f ' . escapeshellarg($rootPath . '/public/index.html') . ' && echo "public-html")'
            . ' || (test -d ' . escapeshellarg($rootPath . '/dist') . ' && echo "dist")'
            . ' || (test -d ' . escapeshellarg($rootPath . '/build') . ' && echo "build")'
            . ' || (test -d ' . escapeshellarg($rootPath . '/out') . ' && echo "out")'
            . ' || echo "root"';
        $detectResult = $this->exec($ssh, $srv, $detectCmd);
        $buildDir = trim((string)($detectResult['saida'] ?? 'root'));
        $buildDir = preg_replace('/^.*?(public-php|public-html|dist|build|out|root).*$/s', '$1', $buildDir) ?: 'root';

        $actualRoot = $rootPath;
        if ($buildDir === 'public-php' || $buildDir === 'public-html') {
            $actualRoot = $rootPath . '/public';
        } elseif ($buildDir !== 'root') {
            $actualRoot = $rootPath . '/' . $buildDir;
        }
        $logs[] = 'Root detectado: ' . $actualRoot;

        $vhostName = $this->getVhostFileName($domain, $isCustom);
        $config = $this->gerarConfigStaticSite($domain, $actualRoot, $phpVersion, $isCustom);
        $sudo = $this->needsSudo($srv) ? 'sudo ' : '';

        $b64 = base64_encode($config);
        // Se o vhost já existe com SSL (Certbot), atualizar root e try_files sem sobrescrever SSL
        if ($isCustom) {
            $cmd = $sudo . 'mkdir -p ' . escapeshellarg($vhostPath)
                . ' && if ' . $sudo . 'grep -q "listen 443 ssl" ' . escapeshellarg($vhostPath . '/' . $vhostName . '.conf') . ' 2>/dev/null; then'
                . '   ' . $sudo . 'sed -i "s|root .*|root ' . $actualRoot . ';|g" ' . escapeshellarg($vhostPath . '/' . $vhostName . '.conf')
                . '   && ' . $sudo . 'sed -i "s|try_files .*|try_files \\$uri \\$uri/ /index.php?\\$query_string;|g" ' . escapeshellarg($vhostPath . '/' . $vhostName . '.conf')
                . '   && ' . $sudo . 'sed -i "s|fastcgi_pass unix:[^;]*;|fastcgi_pass unix:' . ($isCustom ? '/tmp/php-cgi-' . str_replace('.', '', $phpVersion) . '.sock' : '/run/php/php' . $phpVersion . '-fpm.sock') . ';|g" ' . escapeshellarg($vhostPath . '/' . $vhostName . '.conf')
                . '   && ' . $sudo . 'nginx -t 2>&1 && ' . $sudo . $reloadCmd . ' 2>&1 && echo lrv-vhost-ok;'
                . ' else'
                . '   echo ' . escapeshellarg($b64) . ' | base64 -d | ' . $sudo . 'tee ' . escapeshellarg($vhostPath . '/' . $vhostName . '.conf') . ' > /dev/null'
                . '   && ' . $sudo . 'nginx -t 2>&1 && ' . $sudo . $reloadCmd . ' 2>&1 && echo lrv-vhost-ok;'
                . ' fi';
        } else {
        $cmd = $sudo . 'mkdir -p /etc/nginx/sites-available/lrv'
            . ' && if ' . $sudo . 'grep -q "listen 443 ssl" /etc/nginx/sites-available/lrv/' . escapeshellarg($vhostName) . '.conf 2>/dev/null; then'
            . '   ' . $sudo . 'sed -i "s|root .*|root ' . $actualRoot . ';|g" /etc/nginx/sites-available/lrv/' . escapeshellarg($vhostName) . '.conf'
            . '   && ' . $sudo . 'sed -i "s|try_files .*|try_files \\$uri \\$uri/ /index.php?\\$query_string;|g" /etc/nginx/sites-available/lrv/' . escapeshellarg($vhostName) . '.conf'
            . '   && ' . $sudo . 'sed -i "s|fastcgi_pass unix:[^;]*;|fastcgi_pass unix:/run/php/php' . $phpVersion . '-fpm.sock;|g" /etc/nginx/sites-available/lrv/' . escapeshellarg($vhostName) . '.conf'
            . '   && ' . $sudo . 'nginx -t 2>&1 && ' . $sudo . $reloadCmd . ' 2>&1 && echo lrv-vhost-ok;'
            . ' else'
            . '   echo ' . escapeshellarg($b64) . ' | base64 -d | ' . $sudo . 'tee /etc/nginx/sites-available/lrv/' . escapeshellarg($vhostName) . '.conf > /dev/null'
            . '   && ' . $sudo . 'ln -sf /etc/nginx/sites-available/lrv/' . escapeshellarg($vhostName) . '.conf /etc/nginx/sites-enabled/' . escapeshellarg($vhostName) . '.conf'
            . '   && ' . $sudo . 'nginx -t 2>&1 && ' . $sudo . $reloadCmd . ' 2>&1 && echo lrv-vhost-ok;'
            . ' fi';
        } // end else (default nginx path)

        $result = $this->exec($ssh, $srv, $cmd);
        $logs[] = 'Vhost: ' . trim($result['saida'] ?? '');

        if (!str_contains($result['saida'] ?? '', 'lrv-vhost-ok')) {
            return ['ok' => false, 'erro' => 'Falha ao criar vhost Nginx.', 'logs' => $logs];
        }

        if ($ssl) {
            $certCmd = $sudo . 'certbot --nginx -d ' . escapeshellarg($domain) . ' --non-interactive --agree-tos --register-unsafely-without-email --no-redirect 2>&1; echo lrv-cert-done';
            $certResult = $this->exec($ssh, $srv, $certCmd);
            $logs[] = 'SSL: ' . trim($certResult['saida'] ?? '');
        }

        // Aplicar configurações PHP personalizadas
        if (!empty($phpSettings)) {
            $iniLines = '';
            foreach ($phpSettings as $key => $val) {
                if ($val !== '' && preg_match('/^[a-z_]+$/', $key)) {
                    $iniLines .= $key . ' = ' . $val . "\n";
                }
            }
            if ($iniLines !== '') {
                $iniB64 = base64_encode($iniLines);
                $iniPath = '/etc/php/' . $phpVersion . '/fpm/conf.d/99-lrv-' . str_replace('.', '_', $domain) . '.ini';
                $phpCmd = 'echo ' . escapeshellarg($iniB64) . ' | base64 -d | ' . $sudo . 'tee ' . escapeshellarg($iniPath) . ' > /dev/null'
                    . ' && ' . $sudo . 'systemctl reload php' . $phpVersion . '-fpm 2>&1 && echo lrv-php-ok';
                $phpResult = $this->exec($ssh, $srv, $phpCmd);
                $logs[] = 'PHP config: ' . trim($phpResult['saida'] ?? '');
            }
        }

        return ['ok' => true, 'logs' => $logs];
    }

    /**
     * Cria vhost reverse proxy para apps Node.js/Python (Git Deploy).
     */
    /**
     * Cria vhost reverse proxy para apps Node.js/Python (Git Deploy).
     * Sempre sobrescreve o vhost existente para garantir config de proxy correta.
     */
    public function criarVhostProxy(int $serverId, string $domain, int $appPort, bool $ssl = true): array
    {
        $pdo = BancoDeDados::pdo();
        $srv = $this->getServer($pdo, $serverId);
        if (!$srv) return ['ok' => false, 'erro' => 'Servidor não encontrado.'];

        $logs = [];
        $ssh = new SshExecutor();

        $vhostPath = $this->getVhostPath($srv);
        $isCustom = $this->isCustomNginxPath($srv);
        $reloadCmd = $this->getNginxReloadCmd($srv);
        $vhostName = $this->getVhostFileName($domain, $isCustom);
        $config = $this->gerarConfig($domain, $appPort);
        $sudo = $this->needsSudo($srv) ? 'sudo ' : '';

        $b64 = base64_encode($config);
        // Sempre sobrescrever — se tinha SSL, o certbot vai re-adicionar
        if ($isCustom) {
            $cmd = $sudo . 'mkdir -p ' . escapeshellarg($vhostPath)
                . ' && echo ' . escapeshellarg($b64) . ' | base64 -d | ' . $sudo . 'tee ' . escapeshellarg($vhostPath . '/' . $vhostName . '.conf') . ' > /dev/null'
                . ' && ' . $sudo . 'nginx -t 2>&1 && ' . $sudo . $reloadCmd . ' 2>&1 && echo lrv-vhost-ok';
        } else {
            $cmd = $sudo . 'mkdir -p /etc/nginx/sites-available/lrv'
                . ' && echo ' . escapeshellarg($b64) . ' | base64 -d | ' . $sudo . 'tee /etc/nginx/sites-available/lrv/' . escapeshellarg($vhostName) . '.conf > /dev/null'
                . ' && ' . $sudo . 'ln -sf /etc/nginx/sites-available/lrv/' . escapeshellarg($vhostName) . '.conf /etc/nginx/sites-enabled/' . escapeshellarg($vhostName) . '.conf'
                . ' && ' . $sudo . 'nginx -t 2>&1 && ' . $sudo . $reloadCmd . ' 2>&1 && echo lrv-vhost-ok';
        }

        $result = $this->exec($ssh, $srv, $cmd);
        $logs[] = 'Vhost proxy: ' . trim($result['saida'] ?? '');

        if (!str_contains($result['saida'] ?? '', 'lrv-vhost-ok')) {
            return ['ok' => false, 'erro' => 'Falha ao criar vhost proxy Nginx.', 'logs' => $logs];
        }

        // Sempre rodar certbot para (re)configurar SSL
        if ($ssl) {
            $certCmd = 'certbot --nginx -d ' . escapeshellarg($domain) . ' --non-interactive --agree-tos --register-unsafely-without-email --no-redirect 2>&1; echo lrv-cert-done';
            $certResult = $this->exec($ssh, $srv, $certCmd);
            $logs[] = 'SSL: ' . trim($certResult['saida'] ?? '');
        }

        return ['ok' => true, 'logs' => $logs];
    }


    /**
     * Verifica se o servidor precisa de sudo (usuário SSH não é root).
     */
    private function needsSudo(array $srv): bool
    {
        $user = trim((string)($srv['ssh_user'] ?? 'root'));
        return $user !== 'root';
    }

    /**
     * Gera o nome do arquivo vhost.
     * aaPanel usa domínio com pontos (ex: lumiclinic.com.br.conf)
     * Instalação padrão usa underscores (ex: lumiclinic_com_br.conf)
     */
    private function getVhostFileName(string $domain, bool $isCustom): string
    {
        return $isCustom ? $domain : str_replace('.', '_', $domain);
    }

    private function getServer(\PDO $pdo, int $id): ?array
    {
        $stmt = $pdo->prepare('SELECT id, ip_address, ssh_port, ssh_user, ssh_auth_type, ssh_key_id, ssh_password, nginx_vhost_path, nginx_reload_cmd FROM servers WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $srv = $stmt->fetch();
        return is_array($srv) ? $srv : null;
    }

    private function configurarSsh(SshExecutor $ssh, array $srv): void
    {
        // SSH config is handled per-call in exec
    }

    private function exec(SshExecutor $ssh, array $srv, string $cmd): array
    {
        $ip = (string)($srv['ip_address'] ?? '');
        $porta = (int)($srv['ssh_port'] ?? 22);
        $usuario = (string)($srv['ssh_user'] ?? 'root');
        $authType = (string)($srv['ssh_auth_type'] ?? 'key');

        if ($authType === 'password') {
            $senha = SshCrypto::decifrar((string)($srv['ssh_password'] ?? ''));
            return $ssh->executarComSenha($ip, $porta, $usuario, $senha, $cmd, 60);
        }

        $keyDir = rtrim(ConfiguracoesSistema::sshKeyDir(), "/\\");
        $keyPath = $keyDir . DIRECTORY_SEPARATOR . (string)($srv['ssh_key_id'] ?? '');
        return $ssh->executar($ip, $porta, $usuario, $keyPath, $cmd, 60);
    }
}
