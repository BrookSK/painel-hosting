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
        if ($custom !== '') return rtrim($custom, '/');

        // Auto-detectar aaPanel: se is_managed_server=1, assumir path aaPanel
        if ((int)($srv['is_managed_server'] ?? 0) === 1) {
            return '/www/server/panel/vhost/nginx';
        }

        return '/etc/nginx/sites-available/lrv';
    }

    /**
     * Verifica se o servidor usa caminho customizado de vhosts (aaPanel, etc).
     * Nesse caso, não precisa de symlink pra sites-enabled.
     */
    private function isCustomNginxPath(array $srv): bool
    {
        if (trim((string)($srv['nginx_vhost_path'] ?? '')) !== '') return true;
        // Servidores gerenciados usam aaPanel com path customizado
        return (int)($srv['is_managed_server'] ?? 0) === 1;
    }

    /**
     * Retorna o comando de reload do Nginx para o servidor.
     * aaPanel usa nginx.real — precisa de kill -HUP no master process.
     */
    private function getNginxReloadCmd(array $srv): string
    {
        $custom = trim((string)($srv['nginx_reload_cmd'] ?? ''));
        if ($custom !== '') return $custom;

        // Auto-detectar aaPanel: servidores gerenciados usam nginx do /www/server/nginx.
        // O método mais confiável é enviar HUP direto no PID do master real, lendo o
        // pidfile do local correto (varia: /run/nginx.pid ou /www/server/nginx/logs/nginx.pid).
        // Tentamos em cascata, parando na primeira forma que funcionar.
        if ((int)($srv['is_managed_server'] ?? 0) === 1) {
            // pkill -HUP no master pelo nome é o método mais confiável no aaPanel
            // (independe do caminho do pidfile, que varia entre instalações).
            return '('
                . 'sudo pkill -HUP -f "nginx: master" 2>/dev/null'
                . ' || sudo kill -HUP "$(cat /run/nginx.pid 2>/dev/null)" 2>/dev/null'
                . ' || sudo /etc/init.d/nginx reload 2>/dev/null'
                . '); true';
        }

        return 'systemctl reload nginx';
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

        // 2. Gerar SSL (se solicitado)
        if ($ssl) {
            $srv['_app_port'] = $port;
            $sslResult = $this->emitirSSL($ssh, $srv, $domain, $vhostPath, $vhostName, $sudo, $reloadCmd);
            $logs = array_merge($logs, $sslResult['logs']);
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

    /**
     * Emite certificado SSL e atualiza o vhost com HTTPS.
     * Para servidores gerenciados (aaPanel): usa acme.sh com Cloudflare DNS API.
     * Para outros servidores: usa certbot --nginx (fallback).
     */
    private function emitirSSL(SshExecutor $ssh, array $srv, string $domain, string $vhostPath, string $vhostName, string $sudo, string $reloadCmd): array
    {
        // Este método é mantido para o fluxo de proxy (Node/Python). Ele emite o cert
        // e reescreve o vhost de proxy com SSL. Para sites PHP/estáticos, o
        // criarVhostStaticSite() usa emitirCertificado() + gerarConfigStaticSite($certDir).
        $logs = [];
        $isManaged = (int)($srv['is_managed_server'] ?? 0) === 1;

        if (!$isManaged) {
            // Não-gerenciado: certbot --nginx
            $installCertbot = '(which certbot >/dev/null 2>&1 || (apt-get update -qq && apt-get install -y -qq certbot python3-certbot-nginx 2>&1)) && echo certbot-ready';
            $installResult = $this->exec($ssh, $srv, $installCertbot);
            if (!str_contains($installResult['saida'] ?? '', 'certbot-ready')) {
                $logs[] = 'Aviso: Não foi possível instalar certbot. SSL não emitido.';
                return ['logs' => $logs];
            }
            $certCmd = 'certbot --nginx -d ' . escapeshellarg($domain) . ' --non-interactive --agree-tos --register-unsafely-without-email --no-redirect 2>&1; echo lrv-cert-done';
            $certResult = $this->exec($ssh, $srv, $certCmd);
            $logs[] = 'SSL: ' . trim($certResult['saida'] ?? '');
            return ['logs' => $logs];
        }

        // Gerenciado (aaPanel): emitir cert e reescrever vhost de proxy com SSL
        $certResult = $this->emitirCertificado($ssh, $srv, $domain, $sudo);
        $logs = array_merge($logs, $certResult['logs']);
        $certDir = (string)($certResult['certDir'] ?? '');

        // FALLBACK CRÍTICO: se não reemitiu agora mas JÁ EXISTE cert no disco, reaproveita.
        // Evita que um redeploy de app Node/Python derrube o HTTPS que já estava ativo.
        if ($certDir === '') {
            $certDirExistente = '/www/server/panel/vhost/cert/' . $domain;
            $checkCert = 'if ' . $sudo . 'test -f ' . escapeshellarg($certDirExistente . '/fullchain.pem')
                . ' && ' . $sudo . 'test -f ' . escapeshellarg($certDirExistente . '/privkey.pem')
                . '; then echo lrv-cert-existe; fi';
            $rCert = $this->exec($ssh, $srv, $checkCert);
            if (str_contains((string)($rCert['saida'] ?? ''), 'lrv-cert-existe')) {
                $certDir = $certDirExistente;
                $logs[] = 'SSL preservado (proxy): certificado existente reaproveitado.';
            }
        }

        if ($certDir === '') {
            return ['logs' => $logs];
        }

        // Ler a porta do proxy do vhost existente e regravar com SSL
        $confFile = $vhostPath . '/' . $vhostName . '.conf';
        $readCmd = 'grep -oP "proxy_pass http://127.0.0.1:\\K[0-9]+" ' . escapeshellarg($confFile) . ' 2>/dev/null | head -1';
        $portResult = $this->exec($ssh, $srv, $readCmd);
        $port = (int)trim($portResult['saida'] ?? '');
        if ($port <= 0) {
            $port = (int)($srv['_app_port'] ?? 0);
        }

        if ($port > 0) {
            $sslConfig = $this->gerarConfigComSSL($domain, $port, $certDir);
            $b64 = base64_encode($sslConfig);
            $updateCmd = 'echo ' . escapeshellarg($b64) . ' | base64 -d | ' . $sudo . 'tee ' . escapeshellarg($confFile) . ' > /dev/null'
                . ' && ' . $sudo . 'nginx -t 2>&1 && ' . $sudo . $reloadCmd . ' 2>&1 && echo lrv-ssl-ok';
            $updateResult = $this->exec($ssh, $srv, $updateCmd);
            $logs[] = 'SSL vhost (proxy): ' . trim($updateResult['saida'] ?? '');
        }

        return ['logs' => $logs];
    }

    /**
     * Emite e instala um certificado SSL via acme.sh (só o cert — não mexe no vhost).
     * Tenta HTTP challenge (webroot) primeiro; se não der, cai no Cloudflare DNS.
     * Retorna ['logs' => [...], 'certDir' => '/path' | null].
     */
    private function emitirCertificado(SshExecutor $ssh, array $srv, string $domain, string $sudo): array
    {
        $logs = [];
        $certDir = '/www/server/panel/vhost/cert/' . $domain;
        $webroot = trim((string)($srv['_webroot'] ?? ''));
        $acmeOk = false;
        $acmeOutput = '';

        // MÉTODO 1 (preferencial): HTTP challenge via webroot — não precisa de token.
        if ($webroot !== '') {
            $acmeHttpCmd = 'sudo /root/.acme.sh/acme.sh --issue -d ' . escapeshellarg($domain)
                . ' -w ' . escapeshellarg($webroot)
                . ' --server letsencrypt --keylength ec-256 2>&1; echo lrv-acme-exit-$?';
            $r = $this->exec($ssh, $srv, $acmeHttpCmd);
            $acmeOutput = trim($r['saida'] ?? '');
            $logs[] = 'acme.sh (HTTP): ' . mb_substr($acmeOutput, -300);
            $acmeOk = str_contains($acmeOutput, 'Cert success')
                || str_contains($acmeOutput, 'Cert is already valid')
                || str_contains($acmeOutput, 'lrv-acme-exit-0');
        }

        // MÉTODO 2 (fallback): DNS challenge via Cloudflare (precisa de token).
        $cfToken = trim((string)\LRV\Core\Settings::obter('cloudflare.api_token', ''));
        if (!$acmeOk && $cfToken !== '') {
            $acmeCmd = 'export CF_Token=' . escapeshellarg($cfToken) . ' && '
                . 'sudo /root/.acme.sh/acme.sh --issue -d ' . escapeshellarg($domain)
                . ' --dns dns_cf --server letsencrypt --force --keylength ec-256 2>&1; echo lrv-acme-exit-$?';
            $r = $this->exec($ssh, $srv, $acmeCmd);
            $acmeOutput = trim($r['saida'] ?? '');
            $logs[] = 'acme.sh (Cloudflare DNS): ' . mb_substr($acmeOutput, -300);
            $acmeOk = str_contains($acmeOutput, 'Cert success')
                || str_contains($acmeOutput, 'Cert is already valid')
                || str_contains($acmeOutput, 'lrv-acme-exit-0');
        }

        if (!$acmeOk) {
            $logs[] = 'Aviso: SSL não emitido. O site funciona em HTTP.';
            return ['logs' => $logs, 'certDir' => null];
        }

        // Instalar o cert no path do aaPanel
        $installCmd = $sudo . 'mkdir -p ' . escapeshellarg($certDir)
            . ' && sudo /root/.acme.sh/acme.sh --install-cert -d ' . escapeshellarg($domain) . ' --ecc'
            . ' --cert-file ' . escapeshellarg($certDir . '/cert.pem')
            . ' --key-file ' . escapeshellarg($certDir . '/privkey.pem')
            . ' --fullchain-file ' . escapeshellarg($certDir . '/fullchain.pem')
            . ' 2>&1';
        $installResult = $this->exec($ssh, $srv, $installCmd);
        $logs[] = 'Install cert: ' . trim($installResult['saida'] ?? '');

        return ['logs' => $logs, 'certDir' => $certDir];
    }

    /**
     * Gera config Nginx completa com SSL (HTTP + HTTPS) para reverse proxy.
     */
    private function gerarConfigComSSL(string $domain, int $port, string $certDir): string
    {
        return "server {\n"
            . "    listen 80;\n"
            . "    listen 443 ssl http2;\n"
            . "    server_name {$domain};\n"
            . "\n"
            . "    ssl_certificate {$certDir}/fullchain.pem;\n"
            . "    ssl_certificate_key {$certDir}/privkey.pem;\n"
            . "    ssl_protocols TLSv1.2 TLSv1.3;\n"
            . "    ssl_ciphers EECDH+CHACHA20:EECDH+AES128:RSA+AES128:EECDH+AES256:RSA+AES256;\n"
            . "    ssl_prefer_server_ciphers on;\n"
            . "    ssl_session_cache shared:SSL:10m;\n"
            . "    ssl_session_timeout 10m;\n"
            . "    error_page 497 https://\$host\$request_uri;\n"
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

    /**
     * Gera a config Nginx de um site estático/PHP.
     * Se $certDir for informado, adiciona as diretivas SSL (listen 443 + certs) no
     * MESMO server block — gerando um arquivo completo e válido de uma vez, sem
     * precisar injetar linhas via sed depois (que era frágil e quebrava o vhost).
     */
    private function gerarConfigStaticSite(string $domain, string $rootPath, string $phpVersion = '8.3', bool $isAaPanel = false, string $certDir = ''): string
    {
        // aaPanel usa /tmp/php-cgi-XX.sock, instalação padrão usa /run/php/phpX.X-fpm.sock
        $phpShort = str_replace('.', '', $phpVersion); // "8.3" → "83"
        $fpmSocket = $isAaPanel
            ? '/tmp/php-cgi-' . $phpShort . '.sock'
            : '/run/php/php' . $phpVersion . '-fpm.sock';

        // Bloco de listen: com ou sem SSL
        $listenBlock = "    listen 80;\n";
        $sslBlock = '';
        if ($certDir !== '') {
            $listenBlock .= "    listen 443 ssl http2;\n";
            $sslBlock = "    ssl_certificate {$certDir}/fullchain.pem;\n"
                . "    ssl_certificate_key {$certDir}/privkey.pem;\n"
                . "    ssl_protocols TLSv1.2 TLSv1.3;\n"
                . "    error_page 497 https://\$host\$request_uri;\n";
        }

        return "server {\n"
            . $listenBlock
            . "    server_name {$domain};\n"
            . "    root {$rootPath};\n"
            . "    index index.php index.html index.htm;\n"
            . $sslBlock
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
        $sudo = $this->needsSudo($srv) ? 'sudo ' : '';

        // ETAPA 1: Se SSL solicitado, emitir/instalar o certificado ANTES de montar o
        // vhost final. Assim geramos o arquivo de config completo (com as diretivas SSL)
        // de uma só vez — sem injetar linhas via sed depois (frágil, quebrava o vhost).
        $certDir = '';
        if ($ssl) {
            $srv['_webroot'] = $actualRoot;
            $certResult = $this->emitirCertificado($ssh, $srv, $domain, $sudo);
            $logs = array_merge($logs, $certResult['logs']);
            if (!empty($certResult['certDir'])) {
                $certDir = (string) $certResult['certDir'];
            }

            // FALLBACK CRÍTICO: se o acme.sh não reemitiu agora (já válido / challenge falhou),
            // mas JÁ EXISTE um certificado no disco, usamos ele mesmo assim. Isso evita que
            // um novo deploy derrube o HTTPS de um site que já tinha SSL (gerando vhost só HTTP).
            if ($certDir === '') {
                $certDirExistente = '/www/server/panel/vhost/cert/' . $domain;
                $checkCert = 'if ' . $sudo . 'test -f ' . escapeshellarg($certDirExistente . '/fullchain.pem')
                    . ' && ' . $sudo . 'test -f ' . escapeshellarg($certDirExistente . '/privkey.pem')
                    . '; then echo lrv-cert-existe; fi';
                $rCert = $this->exec($ssh, $srv, $checkCert);
                if (str_contains((string)($rCert['saida'] ?? ''), 'lrv-cert-existe')) {
                    $certDir = $certDirExistente;
                    $logs[] = 'SSL preservado: certificado existente reaproveitado (' . $certDir . ').';
                }
            }
        }

        // ETAPA 2: Gerar o vhost completo (com SSL embutido se o cert foi emitido) e gravar.
        $config = $this->gerarConfigStaticSite($domain, $actualRoot, $phpVersion, $isCustom, $certDir);
        $b64 = base64_encode($config);

        if ($isCustom) {
            $confPath = $vhostPath . '/' . $vhostName . '.conf';
            $cmd = $sudo . 'mkdir -p ' . escapeshellarg($vhostPath)
                . ' && echo ' . escapeshellarg($b64) . ' | base64 -d | ' . $sudo . 'tee ' . escapeshellarg($confPath) . ' > /dev/null'
                . ' && ' . $sudo . 'nginx -t 2>&1 && ' . $sudo . $reloadCmd . ' 2>&1 && echo lrv-vhost-ok';
        } else {
            $confAvail = '/etc/nginx/sites-available/lrv/' . $vhostName . '.conf';
            $confEnabled = '/etc/nginx/sites-enabled/' . $vhostName . '.conf';
            $cmd = $sudo . 'mkdir -p /etc/nginx/sites-available/lrv'
                . ' && echo ' . escapeshellarg($b64) . ' | base64 -d | ' . $sudo . 'tee ' . escapeshellarg($confAvail) . ' > /dev/null'
                . ' && ' . $sudo . 'ln -sf ' . escapeshellarg($confAvail) . ' ' . escapeshellarg($confEnabled)
                . ' && ' . $sudo . 'nginx -t 2>&1 && ' . $sudo . $reloadCmd . ' 2>&1 && echo lrv-vhost-ok';
        }

        $result = $this->exec($ssh, $srv, $cmd);
        $logs[] = 'Vhost: ' . trim($result['saida'] ?? '');

        if (!str_contains($result['saida'] ?? '', 'lrv-vhost-ok')) {
            return ['ok' => false, 'erro' => 'Falha ao criar vhost Nginx.', 'logs' => $logs];
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

        // Sempre emitir SSL
        if ($ssl) {
            $srv['_app_port'] = $appPort;
            $sslResult = $this->emitirSSL($ssh, $srv, $domain, $vhostPath, $vhostName, $sudo, $reloadCmd);
            $logs = array_merge($logs, $sslResult['logs']);
        }

        return ['ok' => true, 'logs' => $logs];
    }


    /**
     * Verifica se o servidor precisa de sudo (usuário SSH não é root).
     */
    /**
     * Suspende um site bloqueando o acesso via Nginx, PRESERVANDO a config original.
     *
     * Estratégia segura (sem perda de dados):
     *  - O vhost original .conf é RENOMEADO para .conf.suspenso (fica inerte, pois o
     *    Nginx só carrega arquivos *.conf — mas o conteúdo é 100% preservado).
     *  - Um novo .conf mínimo é gravado apenas para exibir a página de manutenção (503).
     *  - Os arquivos do site, banco, cache, certificados: NADA é tocado. Só o roteamento.
     *
     * Idempotente: se já houver um .conf.suspenso, não sobrescreve o backup.
     */
    public function suspenderVhost(int $serverId, string $domain): array
    {
        $pdo = BancoDeDados::pdo();
        $srv = $this->getServer($pdo, $serverId);
        if (!$srv) return ['ok' => false, 'erro' => 'Servidor não encontrado.'];

        $vhostPath = $this->getVhostPath($srv);
        $isCustom = $this->isCustomNginxPath($srv);
        $reloadCmd = $this->getNginxReloadCmd($srv);
        $sudo = $this->needsSudo($srv) ? 'sudo ' : '';
        $vhostName = $this->getVhostFileName($domain, $isCustom);
        $confFile = $vhostPath . '/' . $vhostName . '.conf';
        $backupFile = $confFile . '.suspenso';

        $ssh = new SshExecutor();
        $logs = [];

        $pageDir = '/var/www/_lrv_suspenso';
        $pageFile = $pageDir . '/index.html';

        // 1) Extrair o cert do vhost ATUAL (mais confiável que adivinhar o path):
        //    lê ssl_certificate/ssl_certificate_key direto do .conf existente.
        $certLine = ''; $keyLine = '';
        try {
            $grepCmd = $sudo . 'grep -hoP "ssl_certificate(_key)?\\s+\\K[^;]+" ' . escapeshellarg($confFile) . ' 2>/dev/null';
            $r = $this->exec($ssh, $srv, $grepCmd);
            $linhas = array_values(array_filter(array_map('trim', explode("\n", (string)($r['saida'] ?? '')))));
            foreach ($linhas as $ln) {
                if (str_contains($ln, 'fullchain') || (str_contains($ln, 'cert') && !str_contains($ln, 'key'))) {
                    if ($certLine === '') $certLine = $ln;
                } elseif (str_contains($ln, 'key') || str_contains($ln, 'privkey')) {
                    if ($keyLine === '') $keyLine = $ln;
                }
            }
            // fallback: se achou 2 linhas e não classificou, assume ordem cert/key
            if ($certLine === '' && count($linhas) >= 1) $certLine = $linhas[0];
            if ($keyLine === '' && count($linhas) >= 2) $keyLine = $linhas[1];
        } catch (\Throwable) {}

        if ($certLine !== '' && $keyLine !== '') {
            $logs[] = 'Cert SSL do vhost atual detectado (bloqueio HTTPS ativo).';
        } else {
            $logs[] = 'Sem cert detectado — bloqueio só em HTTP.';
        }

        // 2) Gravar a página HTML de manutenção (comando isolado)
        $htmlB64 = base64_encode($this->gerarPaginaSuspensao($domain));
        $cmdPage = $sudo . 'mkdir -p ' . escapeshellarg($pageDir)
            . ' && echo ' . escapeshellarg($htmlB64) . ' | base64 -d | ' . $sudo . 'tee ' . escapeshellarg($pageFile) . ' > /dev/null'
            . ' && echo lrv-page-ok';
        $rPage = $this->exec($ssh, $srv, $cmdPage);
        if (!str_contains((string)($rPage['saida'] ?? ''), 'lrv-page-ok')) {
            $logs[] = 'Falha ao gravar página de manutenção: ' . trim((string)($rPage['saida'] ?? ''));
            return ['ok' => false, 'erro' => 'Falha ao preparar página de manutenção.', 'logs' => $logs];
        }

        // 3) Preservar o vhost original: copiar .conf -> .conf.suspenso (só se ainda não há backup).
        //    Usa 'sudo test' porque os arquivos de vhost são root:root e o usuário SSH pode não ser root.
        $cmdBackup = 'if ' . $sudo . 'test ! -f ' . escapeshellarg($backupFile) . ' && ' . $sudo . 'test -f ' . escapeshellarg($confFile) . '; then '
            . $sudo . 'cp -p ' . escapeshellarg($confFile) . ' ' . escapeshellarg($backupFile) . ' && echo lrv-backup-ok; '
            . 'elif ' . $sudo . 'test -f ' . escapeshellarg($backupFile) . '; then echo lrv-backup-existe; '
            . 'else echo lrv-sem-conf; fi';
        $rBackup = $this->exec($ssh, $srv, $cmdBackup);
        $saidaBackup = (string)($rBackup['saida'] ?? '');
        $logs[] = 'Backup vhost: ' . trim($saidaBackup);

        // TRAVA DE SEGURANÇA: só prosseguimos se o vhost original foi preservado
        // (backup criado agora OU já existia de uma suspensão anterior).
        // Se não há .conf original pra preservar (lrv-sem-conf), NÃO gravamos o bloqueio,
        // pra nunca deixar um domínio sem a config original recuperável.
        $backupOk = str_contains($saidaBackup, 'lrv-backup-ok') || str_contains($saidaBackup, 'lrv-backup-existe');
        if (!$backupOk) {
            $logs[] = 'ABORTADO: vhost original não encontrado para preservar. Bloqueio não aplicado (segurança).';
            return ['ok' => false, 'erro' => 'Vhost original ausente — bloqueio abortado para não perder config.', 'logs' => $logs];
        }

        // 4) Gravar o vhost de bloqueio (sobrescreve o .conf), validar e recarregar (comando isolado)
        $blqB64 = base64_encode($this->gerarVhostBloqueio($domain, $pageDir, $certLine, $keyLine));
        $cmdBloqueio = 'echo ' . escapeshellarg($blqB64) . ' | base64 -d | ' . $sudo . 'tee ' . escapeshellarg($confFile) . ' > /dev/null'
            . ' && ' . $sudo . 'nginx -t 2>&1 && ' . $sudo . $reloadCmd . ' 2>&1 && echo lrv-suspend-ok';
        $rBloqueio = $this->exec($ssh, $srv, $cmdBloqueio);
        $saida = (string)($rBloqueio['saida'] ?? '');
        $logs[] = 'Bloqueio vhost (' . $domain . '): ' . trim($saida);

        if (!str_contains($saida, 'lrv-suspend-ok')) {
            // Se falhou o nginx -t, tenta restaurar o backup pra não deixar o site quebrado
            if (str_contains($saidaBackup, 'lrv-backup-ok')) {
                $restore = $sudo . 'cp -pf ' . escapeshellarg($backupFile) . ' ' . escapeshellarg($confFile)
                    . ' && ' . $sudo . $reloadCmd . ' 2>&1; echo lrv-restore-done';
                $this->exec($ssh, $srv, $restore);
                $logs[] = 'Bloqueio falhou — vhost original restaurado por segurança.';
            }
            return ['ok' => false, 'erro' => 'Falha ao aplicar vhost de bloqueio.', 'logs' => $logs];
        }

        return ['ok' => true, 'logs' => $logs];
    }

    /**
     * Reativa um site suspenso, RESTAURANDO a config original preservada.
     *
     *  - Restaura o .conf.suspenso de volta para .conf (sobrescreve o vhost de bloqueio).
     *  - Se não houver backup .conf.suspenso, não faz nada (o vhost pode ser recriado
     *    pelo fluxo normal de deploy). Nunca apaga config boa.
     *
     * Idempotente: se não há bloqueio ativo, apenas retorna ok.
     */
    public function reativarVhost(int $serverId, string $domain): array
    {
        $pdo = BancoDeDados::pdo();
        $srv = $this->getServer($pdo, $serverId);
        if (!$srv) return ['ok' => false, 'erro' => 'Servidor não encontrado.'];

        $vhostPath = $this->getVhostPath($srv);
        $isCustom = $this->isCustomNginxPath($srv);
        $reloadCmd = $this->getNginxReloadCmd($srv);
        $sudo = $this->needsSudo($srv) ? 'sudo ' : '';
        $vhostName = $this->getVhostFileName($domain, $isCustom);
        $confFile = $vhostPath . '/' . $vhostName . '.conf';
        $backupFile = $confFile . '.suspenso';

        $ssh = new SshExecutor();
        $logs = [];

        // Se existe o backup, restaura (sobrescreve o vhost de bloqueio) e remove o backup. Senão, nada a fazer.
        // Usa 'sudo test' porque os arquivos de vhost são root:root.
        $cmd = 'if ' . $sudo . 'test -f ' . escapeshellarg($backupFile) . '; then '
            . $sudo . 'cp -pf ' . escapeshellarg($backupFile) . ' ' . escapeshellarg($confFile)
            . ' && ' . $sudo . 'nginx -t 2>&1 && ' . $sudo . $reloadCmd . ' 2>&1'
            . ' && ' . $sudo . 'rm -f ' . escapeshellarg($backupFile)
            . ' && echo lrv-reativar-ok; '
            . 'else echo lrv-sem-backup; fi';

        $result = $this->exec($ssh, $srv, $cmd);
        $saida = (string)($result['saida'] ?? '');
        $logs[] = 'Reativar vhost (' . $domain . '): ' . trim($saida);

        if (str_contains($saida, 'lrv-reativar-ok')) {
            return ['ok' => true, 'logs' => $logs];
        }
        if (str_contains($saida, 'lrv-sem-backup')) {
            $logs[] = 'Nenhum vhost suspenso encontrado (nada a restaurar).';
            return ['ok' => true, 'semBackup' => true, 'logs' => $logs];
        }

        return ['ok' => false, 'erro' => 'Falha ao reativar vhost.', 'logs' => $logs];
    }

    /**
     * Gera o vhost mínimo de bloqueio: serve a página de manutenção (HTTP 503)
     * para qualquer requisição. Responde tanto em 80 quanto em 443 (se houver cert),
     * mas para simplicidade e segurança, serve em ambos sem exigir cert específico.
     */
    private function gerarVhostBloqueio(string $domain, string $pageDir, string $certLine = '', string $keyLine = ''): string
    {
        // Escuta em 80 e, se houver cert (extraído do vhost original), também em 443
        // no mesmo server block, para a página de manutenção abrir limpa em http e https.
        $listenBlock = "    listen 80;\n";
        $sslBlock = '';
        if ($certLine !== '' && $keyLine !== '') {
            $listenBlock .= "    listen 443 ssl http2;\n";
            $sslBlock = "    ssl_certificate {$certLine};\n"
                . "    ssl_certificate_key {$keyLine};\n"
                . "    ssl_protocols TLSv1.2 TLSv1.3;\n"
                . "    error_page 497 https://\$host\$request_uri;\n";
        }

        return "server {\n"
            . $listenBlock
            . "    server_name {$domain};\n"
            . "    root {$pageDir};\n"
            . "    index index.html;\n"
            . $sslBlock
            . "\n"
            . "    location / {\n"
            . "        return 503;\n"
            . "    }\n"
            . "\n"
            . "    error_page 503 /index.html;\n"
            . "    location = /index.html {\n"
            . "        internal;\n"
            . "        add_header Retry-After 3600 always;\n"
            . "    }\n"
            . "}\n";
    }

    /**
     * Regenera o SSL de um site: reemite/renova o certificado e garante que o vhost
     * existente tenha o bloco HTTPS (listen 443 + certs). Não altera o root nem o tipo
     * do site — só conserta/adiciona o SSL. Ideal para um botão "Regerar SSL" no painel.
     *
     * @param string $webroot Caminho do site (para HTTP challenge do acme.sh). Opcional.
     */
    public function regerarSSL(int $serverId, string $domain, string $webroot = ''): array
    {
        $pdo = BancoDeDados::pdo();
        $srv = $this->getServer($pdo, $serverId);
        if (!$srv) return ['ok' => false, 'erro' => 'Servidor não encontrado.'];

        $isManaged = (int)($srv['is_managed_server'] ?? 0) === 1;
        $vhostPath = $this->getVhostPath($srv);
        $isCustom = $this->isCustomNginxPath($srv);
        $reloadCmd = $this->getNginxReloadCmd($srv);
        $sudo = $this->needsSudo($srv) ? 'sudo ' : '';
        $vhostName = $this->getVhostFileName($domain, $isCustom);
        $confFile = $vhostPath . '/' . $vhostName . '.conf';
        $ssh = new SshExecutor();
        $logs = [];

        // Não-gerenciado: certbot --nginx resolve tudo (emite + injeta no vhost)
        if (!$isManaged) {
            $certCmd = 'certbot --nginx -d ' . escapeshellarg($domain) . ' --non-interactive --agree-tos --register-unsafely-without-email --no-redirect 2>&1; echo lrv-cert-done';
            $r = $this->exec($ssh, $srv, $certCmd);
            $out = trim((string)($r['saida'] ?? ''));
            $logs[] = 'SSL (certbot): ' . mb_substr($out, -300);
            $ok = str_contains($out, 'Successfully') || str_contains($out, 'Congratulations') || str_contains($out, 'not yet due for renewal');
            return ['ok' => $ok, 'logs' => $logs];
        }

        // Gerenciado (aaPanel): emitir/renovar cert via acme.sh
        if ($webroot === '') {
            // Descobrir o root a partir do vhost atual, se não veio por parâmetro
            $rootCmd = $sudo . 'grep -oP "root\\s+\\K[^;]+" ' . escapeshellarg($confFile) . ' 2>/dev/null | head -1';
            $rr = $this->exec($ssh, $srv, $rootCmd);
            $webroot = trim((string)($rr['saida'] ?? ''));
        }

        $srv['_webroot'] = $webroot;
        $certResult = $this->emitirCertificado($ssh, $srv, $domain, $sudo);
        $logs = array_merge($logs, $certResult['logs']);
        $certDir = (string)($certResult['certDir'] ?? '');

        // Se não reemitiu mas o cert existe no disco, usa o existente
        if ($certDir === '') {
            $certDirExistente = '/www/server/panel/vhost/cert/' . $domain;
            $checkCert = 'if ' . $sudo . 'test -f ' . escapeshellarg($certDirExistente . '/fullchain.pem')
                . ' && ' . $sudo . 'test -f ' . escapeshellarg($certDirExistente . '/privkey.pem')
                . '; then echo lrv-cert-existe; fi';
            $rc = $this->exec($ssh, $srv, $checkCert);
            if (str_contains((string)($rc['saida'] ?? ''), 'lrv-cert-existe')) {
                $certDir = $certDirExistente;
                $logs[] = 'Certificado existente reaproveitado.';
            }
        }

        if ($certDir === '') {
            $logs[] = 'Não foi possível emitir nem localizar um certificado.';
            return ['ok' => false, 'erro' => 'Falha ao obter certificado SSL.', 'logs' => $logs];
        }

        // Garantir o bloco 443 no vhost existente: injeta o listen 443 + certs logo após o listen 80,
        // apenas se ainda não houver "listen 443". Preserva todo o resto do vhost (root, php, etc.).
        $sslInject = "    listen 443 ssl http2;\\n"
            . "    ssl_certificate {$certDir}/fullchain.pem;\\n"
            . "    ssl_certificate_key {$certDir}/privkey.pem;\\n"
            . "    ssl_protocols TLSv1.2 TLSv1.3;\\n"
            . "    error_page 497 https://\$host\$request_uri;";
        $cmd = 'if ' . $sudo . 'grep -q "listen 443" ' . escapeshellarg($confFile) . ' 2>/dev/null; then '
            . 'echo lrv-ja-tem-ssl; '
            . 'else '
            . $sudo . "sed -i '0,/listen 80;/s|listen 80;|listen 80;\\n" . $sslInject . "|' " . escapeshellarg($confFile)
            . ' && echo lrv-ssl-injetado; fi';
        $ri = $this->exec($ssh, $srv, $cmd);
        $saidaInject = (string)($ri['saida'] ?? '');
        $logs[] = 'Vhost SSL: ' . trim($saidaInject);

        // Validar e recarregar
        $reload = $sudo . 'nginx -t 2>&1 && ' . $sudo . $reloadCmd . ' 2>&1 && echo lrv-reload-ok';
        $rl = $this->exec($ssh, $srv, $reload);
        $saidaReload = (string)($rl['saida'] ?? '');
        $logs[] = 'Reload: ' . trim($saidaReload);

        if (!str_contains($saidaReload, 'lrv-reload-ok')) {
            return ['ok' => false, 'erro' => 'Nginx não recarregou (config inválida?).', 'logs' => $logs];
        }

        return ['ok' => true, 'logs' => $logs];
    }

    /**
     * Regrava APENAS o HTML da página de manutenção num servidor, sem tocar em
     * vhosts nem em suspensões. Útil para aplicar melhorias no visual da página
     * em sites que já estão suspensos, sem precisar re-suspender.
     */
    public function atualizarPaginaSuspensao(int $serverId): array
    {
        $pdo = BancoDeDados::pdo();
        $srv = $this->getServer($pdo, $serverId);
        if (!$srv) return ['ok' => false, 'erro' => 'Servidor não encontrado.'];

        $sudo = $this->needsSudo($srv) ? 'sudo ' : '';
        $ssh = new SshExecutor();
        $pageDir = '/var/www/_lrv_suspenso';
        $pageFile = $pageDir . '/index.html';

        $htmlB64 = base64_encode($this->gerarPaginaSuspensao(''));
        $cmd = $sudo . 'mkdir -p ' . escapeshellarg($pageDir)
            . ' && echo ' . escapeshellarg($htmlB64) . ' | base64 -d | ' . $sudo . 'tee ' . escapeshellarg($pageFile) . ' > /dev/null'
            . ' && echo lrv-page-updated';
        $r = $this->exec($ssh, $srv, $cmd);
        $saida = (string)($r['saida'] ?? '');

        if (!str_contains($saida, 'lrv-page-updated')) {
            return ['ok' => false, 'erro' => 'Falha ao atualizar página.', 'saida' => trim($saida)];
        }
        return ['ok' => true, 'saida' => trim($saida)];
    }

    /**
     * HTML autocontido da página de "site em manutenção".
     * NÃO menciona pagamento — orienta a contatar o suporte do servidor.
     */
    private function gerarPaginaSuspensao(string $domain): string
    {
        $dominioSafe = htmlspecialchars($domain, ENT_QUOTES, 'UTF-8');
        return <<<HTML
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Site em manutenção</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  :root { --brand1:#4F46E5; --brand2:#7C3AED; }
  html { background:#0B1C3D; }
  body {
    font-family:'Plus Jakarta Sans', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
    min-height:100vh; width:100%; overflow-x:hidden;
    display:flex; align-items:center; justify-content:center;
    padding:24px; color:#e2e8f0; position:relative;
  }
  /* Fundo fixo que cobre a viewport inteira (não rola, não emenda, não faz "xadrez").
     Gradiente diagonal simples e contínuo — sem radiais com 'transparent', que criavam
     bordas duras / divisão de cor. Cobre 100% da tela sempre. */
  .fundo {
    position:fixed; inset:0; z-index:-1;
    background:linear-gradient(160deg, #4F46E5 0%, #3730a3 22%, #1e2a5a 50%, #131c3d 75%, #0B1C3D 100%);
  }

  .card {
    position:relative; z-index:1;
    background:rgba(255,255,255,.98); color:#0f172a; border-radius:28px;
    padding:52px 44px; max-width:520px; width:100%; text-align:center;
    box-shadow:0 30px 80px rgba(0,0,0,.45); border:1px solid rgba(255,255,255,.6);
    animation:pop .6s cubic-bezier(.16,1,.3,1);
  }
  @keyframes pop { from{ opacity:0; transform:translateY(20px) scale(.97) } to{ opacity:1; transform:none } }

  .icone {
    width:92px; height:92px; margin:0 auto 26px; border-radius:26px;
    background:linear-gradient(135deg,var(--brand1),var(--brand2));
    display:flex; align-items:center; justify-content:center;
    box-shadow:0 12px 30px rgba(124,58,237,.45);
  }

  .badge {
    display:inline-block; font-size:12px; font-weight:700; letter-spacing:.08em;
    text-transform:uppercase; color:var(--brand2); background:#f3f0ff;
    padding:6px 14px; border-radius:999px; margin-bottom:18px;
  }
  h1 { font-size:28px; font-weight:800; margin-bottom:14px; color:#0f172a; letter-spacing:-.02em; }
  p { font-size:15.5px; line-height:1.75; color:#475569; margin-bottom:14px; }
  .contato {
    margin-top:28px; padding:18px 20px; background:#f8fafc; border:1px solid #eef2f7;
    border-radius:16px; font-size:14.5px; color:#334155; display:flex; align-items:center;
    gap:12px; justify-content:center; text-align:left;
  }
  .contato svg { flex-shrink:0; color:var(--brand1); }
  .dots { margin-top:26px; display:flex; gap:8px; justify-content:center; }
  .dots span { width:9px; height:9px; border-radius:50%; background:var(--brand2); opacity:.35; animation:blink 1.4s infinite; }
  .dots span:nth-child(2){ animation-delay:.2s } .dots span:nth-child(3){ animation-delay:.4s }
  @keyframes blink { 0%,100%{ opacity:.25; transform:scale(.9) } 50%{ opacity:1; transform:scale(1.1) } }
  .rodape { margin-top:24px; font-size:12.5px; color:#94a3b8; }

  /* ---- Mobile ---- */
  @media (max-width:600px){
    body { padding:16px; }
    .card { padding:34px 22px; border-radius:22px; }
    .icone { width:76px; height:76px; border-radius:22px; margin-bottom:20px; }
    .icone svg { width:38px; height:38px; }
    .badge { font-size:10.5px; padding:5px 12px; margin-bottom:14px; }
    h1 { font-size:22px; margin-bottom:12px; }
    p { font-size:14.5px; line-height:1.65; }
    .contato { padding:14px 16px; font-size:13.5px; gap:10px; }
    .contato svg { width:20px; height:20px; }
  }
  @media (max-width:360px){
    .card { padding:28px 18px; }
    h1 { font-size:20px; }
  }
</style>
</head>
<body>
  <div class="fundo"></div>
  <div class="card">
    <div class="icone">
      <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
      </svg>
    </div>
    <div class="badge">Manutenção em andamento</div>
    <h1>Voltamos já, já</h1>
    <p>Este site está temporariamente em manutenção e ficará indisponível por um curto período. Agradecemos a compreensão.</p>
    <div class="contato">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
      </svg>
      <span>É o responsável por este site? Entre em contato com o suporte da sua hospedagem para regularizar o acesso.</span>
    </div>
    <div class="dots"><span></span><span></span><span></span></div>
    <div class="rodape">Estamos trabalhando para restabelecer o serviço.</div>
  </div>
</body>
</html>
HTML;
    }

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
        $stmt = $pdo->prepare('SELECT id, ip_address, ssh_port, ssh_user, ssh_auth_type, ssh_key_id, ssh_password, nginx_vhost_path, nginx_reload_cmd, is_managed_server FROM servers WHERE id = :id LIMIT 1');
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
