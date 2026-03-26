<?php

declare(strict_types=1);

namespace LRV\App\Services\Infra;

use LRV\Core\Settings;
use LRV\Core\ConfiguracoesSistema;

/**
 * Gerencia configurações Nginx no servidor do painel (Plesk) para proxy reverso
 * de domínios temporários (*.apps.lrvweb.com.br → VPS do cliente).
 *
 * Usa /etc/nginx/conf.d/ que é compatível com Plesk e Nginx standalone.
 */
final class NginxProxyService
{
    private string $host;
    private int $port;
    private string $user;
    private string $authType;
    private string $senha = '';
    private string $keyPath = '';

    public function __construct()
    {
        $this->host = trim((string) Settings::obter('proxy.server_ip', ''));
        $this->port = (int) Settings::obter('proxy.server_ssh_port', 22);
        $this->user = trim((string) Settings::obter('proxy.server_ssh_user', 'root'));
        $senhaCifrada = trim((string) Settings::obter('proxy.server_ssh_password', ''));

        if ($this->host === '') {
            throw new \RuntimeException('Servidor proxy não configurado. Defina proxy.server_ip nas configurações.');
        }

        $this->port = $this->port > 0 ? $this->port : 22;
        $this->user = $this->user !== '' ? $this->user : 'root';

        if ($senhaCifrada !== '') {
            $this->senha = SshCrypto::decifrar($senhaCifrada);
            $this->authType = 'password';
        } else {
            $keyId = trim((string) Settings::obter('proxy.server_ssh_key_id', ''));
            $this->keyPath = ConfiguracoesSistema::sshKeyDir() . DIRECTORY_SEPARATOR . $keyId;
            $this->authType = 'key';
        }
    }

    /**
     * Cria um virtual host Nginx que faz proxy reverso para a VPS do cliente.
     * Arquivo salvo em /etc/nginx/conf.d/ (compatível com Plesk e standalone).
     */
    public function criarProxy(string $tempDomain, string $vpsIp, int $vpsPort = 80): void
    {
        $safeFilename = preg_replace('/[^a-z0-9._-]/', '_', strtolower($tempDomain));
        $confFile = '/etc/nginx/conf.d/lrv_proxy_' . $safeFilename . '.conf';

        $nginxConf = "server {\n"
            . "    listen 80;\n"
            . "    server_name {$tempDomain};\n\n"
            . "    location / {\n"
            . "        proxy_pass http://{$vpsIp}:{$vpsPort};\n"
            . "        proxy_set_header Host \\\$host;\n"
            . "        proxy_set_header X-Real-IP \\\$remote_addr;\n"
            . "        proxy_set_header X-Forwarded-For \\\$proxy_add_x_forwarded_for;\n"
            . "        proxy_set_header X-Forwarded-Proto \\\$scheme;\n"
            . "        proxy_connect_timeout 10s;\n"
            . "        proxy_read_timeout 60s;\n"
            . "    }\n"
            . "}\n";

        $cmd = 'cat > ' . escapeshellarg($confFile) . " << 'NGINXEOF'\n"
            . "server {\n"
            . "    listen 80;\n"
            . "    server_name {$tempDomain};\n\n"
            . "    location / {\n"
            . "        proxy_pass http://{$vpsIp}:{$vpsPort};\n"
            . "        proxy_set_header Host \$host;\n"
            . "        proxy_set_header X-Real-IP \$remote_addr;\n"
            . "        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;\n"
            . "        proxy_set_header X-Forwarded-Proto \$scheme;\n"
            . "        proxy_connect_timeout 10s;\n"
            . "        proxy_read_timeout 60s;\n"
            . "    }\n"
            . "}\n"
            . "NGINXEOF\n"
            . 'nginx -t 2>&1 && (systemctl reload nginx 2>/dev/null || service nginx reload 2>&1) && echo nginx-proxy-ok';

        $result = $this->run($cmd, 30);
        $saida = (string)($result['saida'] ?? '');

        if (!str_contains($saida, 'nginx-proxy-ok')) {
            // Rollback: remove config se nginx -t falhou
            $this->run('rm -f ' . escapeshellarg($confFile), 5);
            throw new \RuntimeException('Falha ao configurar proxy Nginx: ' . substr($saida, 0, 300));
        }
    }

    /**
     * Remove o virtual host Nginx para um domínio temporário.
     */
    public function removerProxy(string $tempDomain): void
    {
        $safeFilename = preg_replace('/[^a-z0-9._-]/', '_', strtolower($tempDomain));
        $confFile = '/etc/nginx/conf.d/lrv_proxy_' . $safeFilename . '.conf';

        $cmd = 'rm -f ' . escapeshellarg($confFile)
            . ' && nginx -t 2>&1 && (systemctl reload nginx 2>/dev/null || service nginx reload 2>&1) && echo nginx-removed-ok';

        $this->run($cmd, 15);
    }

    private function run(string $cmd, int $timeout): array
    {
        $exec = new SshExecutor();
        if ($this->authType === 'password') {
            return $exec->executarComSenha($this->host, $this->port, $this->user, $this->senha, $cmd, $timeout);
        }
        return $exec->executar($this->host, $this->port, $this->user, $this->keyPath, $cmd, $timeout);
    }
}
