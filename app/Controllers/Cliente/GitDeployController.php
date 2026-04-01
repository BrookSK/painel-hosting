<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Cliente;

use LRV\Core\Auth;
use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;
use LRV\Core\I18n;

final class GitDeployController
{
    public function listar(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::redirecionar('/cliente/entrar');

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare(
            'SELECT g.*, v.cpu, v.ram FROM git_deployments g
             JOIN vps v ON v.id = g.vps_id
             WHERE g.client_id = :c ORDER BY g.id DESC'
        );
        $stmt->execute([':c' => $clienteId]);
        $deployments = $stmt->fetchAll() ?: [];

        $cStmt = $pdo->prepare('SELECT name, email FROM clients WHERE id = ?');
        $cStmt->execute([$clienteId]);
        $cliente = $cStmt->fetch() ?: [];

        return Resposta::html(View::renderizar(__DIR__ . '/../../Views/cliente/git-deploy-listar.php', [
            'deployments' => $deployments,
            'cliente'     => $cliente,
        ]));
    }

    public function novo(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::redirecionar('/cliente/entrar');

        $pdo = BancoDeDados::pdo();
        $vpsStmt = $pdo->prepare("SELECT id, cpu, ram, storage FROM vps WHERE client_id = :c AND status = 'running' ORDER BY id");
        $vpsStmt->execute([':c' => $clienteId]);
        $vpsList = $vpsStmt->fetchAll() ?: [];

        $cStmt = $pdo->prepare('SELECT name, email FROM clients WHERE id = ?');
        $cStmt->execute([$clienteId]);
        $cliente = $cStmt->fetch() ?: [];

        return Resposta::html(View::renderizar(__DIR__ . '/../../Views/cliente/git-deploy-form.php', [
            'deployment' => [],
            'vpsList'    => $vpsList,
            'cliente'    => $cliente,
            'erro'       => '',
        ]));
    }

    public function salvar(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::redirecionar('/cliente/entrar');

        $id = (int)($req->post['id'] ?? 0);
        $vpsId = (int)($req->post['vps_id'] ?? 0);
        $name = trim((string)($req->post['name'] ?? ''));
        $repoUrl = trim((string)($req->post['repo_url'] ?? ''));
        $branch = trim((string)($req->post['branch'] ?? 'main'));
        $subdomain = trim((string)($req->post['subdomain'] ?? ''));
        $deployPath = trim((string)($req->post['deploy_path'] ?? ''));
        if ($deployPath === '' || $deployPath === '/var/www/html') {
            // Gerar path único baseado no nome
            $slugPath = strtolower(preg_replace('/[^a-z0-9]/', '-', strtolower($name)));
            $deployPath = '/var/www/' . ($slugPath !== '' ? $slugPath : 'app-' . time());
        }
        $forceOverwrite = (int)($req->post['force_overwrite'] ?? 1) === 1 ? 1 : 0;
        $gerarTempDomain = (int)($req->post['gerar_temp_domain'] ?? 0) === 1;
        $authToken = trim((string)($req->post['auth_token'] ?? ''));
        $postDeployCmd = trim((string)($req->post['post_deploy_cmd'] ?? ''));
        $appType = trim((string)($req->post['app_type'] ?? 'php'));
        if (!in_array($appType, ['php', 'static', 'nodejs', 'python'], true)) $appType = 'php';
        $appPort = in_array($appType, ['nodejs', 'python']) ? max(1024, min(65535, (int)($req->post['app_port'] ?? 3000))) : null;
        $phpVersion = trim((string)($req->post['php_version'] ?? '8.3'));
        $phpSettings = json_encode([
            'memory_limit' => trim((string)($req->post['php_memory_limit'] ?? '256M')),
            'upload_max_filesize' => trim((string)($req->post['php_upload_max'] ?? '64M')),
            'post_max_size' => trim((string)($req->post['php_post_max'] ?? '64M')),
            'max_execution_time' => trim((string)($req->post['php_max_exec'] ?? '300')),
            'max_input_vars' => trim((string)($req->post['php_max_input_vars'] ?? '3000')),
        ], JSON_UNESCAPED_UNICODE);

        if ($name === '' || $repoUrl === '' || $vpsId <= 0) {
            return $this->renderizarErro($clienteId, $id, 'Preencha nome, repositório e VPS.');
        }

        if (!preg_match('#^https?://[a-zA-Z0-9._/-]+$#', $repoUrl) && !preg_match('#^git@[a-zA-Z0-9._-]+:[a-zA-Z0-9._/-]+$#', $repoUrl)) {
            return $this->renderizarErro($clienteId, $id, 'URL do repositório inválida.');
        }

        $pdo = BancoDeDados::pdo();

        // Validar que a VPS pertence ao cliente
        $vStmt = $pdo->prepare("SELECT id FROM vps WHERE id = :v AND client_id = :c AND status = 'running' LIMIT 1");
        $vStmt->execute([':v' => $vpsId, ':c' => $clienteId]);
        if (!$vStmt->fetch()) {
            return $this->renderizarErro($clienteId, $id, 'VPS não encontrada ou inativa.');
        }

        if ($branch === '') $branch = 'main';
        if ($deployPath === '') $deployPath = '/var/www/html';

        // Validar que subdomínio pertence ao cliente e está ativo
        if ($subdomain !== '') {
            $subCheck = $pdo->prepare("SELECT id FROM client_subdomains WHERE client_id = :c AND subdomain = :s AND status = 'active' LIMIT 1");
            $subCheck->execute([':c' => $clienteId, ':s' => $subdomain]);
            $subRow = $subCheck->fetch();
            if (!is_array($subRow)) {
                return $this->renderizarErro($clienteId, $id, 'Subdomínio inválido ou não verificado. Cadastre e verifique em Domínios.');
            }
        }

        $subSvc = new \LRV\App\Services\Infra\SubdomainVerificationService();

        if ($id > 0) {
            // Liberar subdomínio anterior
            $subSvc->liberarUso('git_deploy', $id);

            $updateSql = 'UPDATE git_deployments SET name=:n, repo_url=:r, branch=:b, subdomain=:s, deploy_path=:dp, force_overwrite=:fo, post_deploy_cmd=:pdc, php_version=:pv, php_settings=:ps, app_type=:at2, app_port=:ap';
            $params = [':n'=>$name,':r'=>$repoUrl,':b'=>$branch,':s'=>$subdomain!==''?$subdomain:null,':dp'=>$deployPath,':fo'=>$forceOverwrite,':pdc'=>$postDeployCmd!==''?$postDeployCmd:null,':pv'=>$phpVersion,':ps'=>$phpSettings,':at2'=>$appType,':ap'=>$appPort,':id'=>$id,':c'=>$clienteId];
            if ($authToken !== '') {
                $updateSql .= ', auth_token_enc=:at';
                $params[':at'] = \LRV\App\Services\Infra\SshCrypto::cifrar($authToken);
            }
            $updateSql .= ' WHERE id=:id AND client_id=:c';
            $pdo->prepare($updateSql)->execute($params);

            // Marcar novo subdomínio como em uso
            if ($subdomain !== '' && isset($subRow)) {
                $subSvc->marcarEmUso((int)$subRow['id'], 'git_deploy', $id);
            }
        } else {
            // Gerar domínio temporário se solicitado
            $tempDomain = null;
            if ($gerarTempDomain) {
                $tempBase = trim((string)\LRV\Core\Settings::obter('infra.temp_domain_base', ''));
                if ($tempBase !== '') {
                    $slug = strtolower(preg_replace('/[^a-z0-9]/', '', $name));
                    $slug = substr($slug, 0, 8) ?: 'app';
                    $tempDomain = $slug . substr(bin2hex(random_bytes(3)), 0, 4) . '.' . $tempBase;

                    // Configurar proxy Nginx no servidor principal
                    try {
                        $vpsIpStmt = $pdo->prepare('SELECT s.ip_address FROM vps v JOIN servers s ON s.id = v.server_id WHERE v.id = :v AND v.client_id = :c LIMIT 1');
                        $vpsIpStmt->execute([':v' => $vpsId, ':c' => $clienteId]);
                        $vpsRow = $vpsIpStmt->fetch();
                        $vpsIp = is_array($vpsRow) ? (string)($vpsRow['ip_address'] ?? '') : '';
                        if ($vpsIp !== '') {
                            (new \LRV\App\Services\Infra\NginxProxyService())->criarProxy($tempDomain, $vpsIp, 80);
                        }
                    } catch (\Throwable) {
                        // Silencioso — domínio é criado mesmo se proxy falhar
                    }
                }
            }
            $tokenEnc = $authToken !== '' ? \LRV\App\Services\Infra\SshCrypto::cifrar($authToken) : null;

            // Gerar deploy key (par de chaves SSH) para autenticação via SSH
            $deployKeyPublic = null;
            $deployKeyPrivateEnc = null;
            try {
                $keyPair = $this->gerarDeployKey($name);
                $deployKeyPublic = $keyPair['public'];
                $deployKeyPrivateEnc = \LRV\App\Services\Infra\SshCrypto::cifrar($keyPair['private']);
            } catch (\Throwable) {}

            $pdo->prepare('INSERT INTO git_deployments (client_id, vps_id, name, repo_url, auth_token_enc, deploy_key_public, deploy_key_private_enc, branch, subdomain, temp_domain, deploy_path, force_overwrite, post_deploy_cmd, php_version, php_settings, app_type, app_port, status, created_at) VALUES (:c,:v,:n,:r,:at,:dkpub,:dkpriv,:b,:s,:td,:dp,:fo,:pdc,:pv,:ps,:at2,:ap,:st,:cr)')
                ->execute([':c'=>$clienteId,':v'=>$vpsId,':n'=>$name,':r'=>$repoUrl,':at'=>$tokenEnc,':dkpub'=>$deployKeyPublic,':dkpriv'=>$deployKeyPrivateEnc,':b'=>$branch,':s'=>$subdomain!==''?$subdomain:null,':td'=>$tempDomain,':dp'=>$deployPath,':fo'=>$forceOverwrite,':pdc'=>$postDeployCmd!==''?$postDeployCmd:null,':pv'=>$phpVersion,':ps'=>$phpSettings,':at2'=>$appType,':ap'=>$appPort,':st'=>'active',':cr'=>date('Y-m-d H:i:s')]);

            // Marcar subdomínio como em uso
            $newDeployId = (int)$pdo->lastInsertId();
            if ($subdomain !== '' && isset($subRow) && $newDeployId > 0) {
                $subSvc->marcarEmUso((int)$subRow['id'], 'git_deploy', $newDeployId);
            }
        }

        return Resposta::redirecionar('/cliente/git-deploy');
    }

    public function editar(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::redirecionar('/cliente/entrar');

        $id = (int)($req->query['id'] ?? 0);
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT * FROM git_deployments WHERE id = :id AND client_id = :c LIMIT 1');
        $stmt->execute([':id' => $id, ':c' => $clienteId]);
        $deployment = $stmt->fetch();
        if (!is_array($deployment)) return Resposta::texto('Não encontrado.', 404);

        // Gerar deploy key se não existir
        if (empty($deployment['deploy_key_public'])) {
            try {
                $keyPair = $this->gerarDeployKey((string)($deployment['name'] ?? ''));
                $pdo->prepare('UPDATE git_deployments SET deploy_key_public=:pub, deploy_key_private_enc=:priv WHERE id=:id')
                    ->execute([':pub' => $keyPair['public'], ':priv' => \LRV\App\Services\Infra\SshCrypto::cifrar($keyPair['private']), ':id' => $id]);
                $deployment['deploy_key_public'] = $keyPair['public'];
            } catch (\Throwable) {}
        }

        $vpsStmt = $pdo->prepare("SELECT id, cpu, ram, storage FROM vps WHERE client_id = :c AND status = 'running' ORDER BY id");
        $vpsStmt->execute([':c' => $clienteId]);
        $vpsList = $vpsStmt->fetchAll() ?: [];

        $cStmt = $pdo->prepare('SELECT name, email FROM clients WHERE id = ?');
        $cStmt->execute([$clienteId]);
        $cliente = $cStmt->fetch() ?: [];

        return Resposta::html(View::renderizar(__DIR__ . '/../../Views/cliente/git-deploy-form.php', [
            'deployment' => $deployment,
            'vpsList'    => $vpsList,
            'cliente'    => $cliente,
            'erro'       => '',
        ]));
    }

    public function deploy(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::json(['ok' => false, 'erro' => 'Não autenticado.'], 401);

        $id = (int)($req->post['id'] ?? 0);
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare(
            'SELECT g.*, v.server_id, s.ip_address, s.ssh_port, s.ssh_user, s.ssh_password, s.ssh_auth_type, s.ssh_key_id
             FROM git_deployments g
             JOIN vps v ON v.id = g.vps_id
             JOIN servers s ON s.id = v.server_id
             WHERE g.id = :id AND g.client_id = :c AND g.status != "inactive" LIMIT 1'
        );
        $stmt->execute([':id' => $id, ':c' => $clienteId]);
        $dep = $stmt->fetch();

        if (!is_array($dep)) {
            return Resposta::json(['ok' => false, 'erro' => 'Deploy não encontrado.'], 404);
        }

        try {
            $result = $this->executarDeploy($dep);
        } catch (\Throwable $e) {
            $pdo->prepare('UPDATE git_deployments SET status="error", error_message=:e WHERE id=:id')
                ->execute([':e' => $e->getMessage(), ':id' => $id]);
            $pdo->prepare('INSERT INTO git_deploy_logs (deployment_id, status, output, deployed_at) VALUES (:d,:s,:o,:t)')
                ->execute([':d'=>$id,':s'=>'error',':o'=>$e->getMessage(),':t'=>date('Y-m-d H:i:s')]);
            return Resposta::json(['ok' => false, 'erro' => $e->getMessage()]);
        }

        $pdo->prepare('UPDATE git_deployments SET status="active", last_deployed_at=:t, last_commit_hash=:h, last_commit_message=:m, last_commit_author=:a, error_message=NULL WHERE id=:id')
            ->execute([':t'=>date('Y-m-d H:i:s'),':h'=>$result['hash'],':m'=>$result['message'],':a'=>$result['author'],':id'=>$id]);
        $pdo->prepare('INSERT INTO git_deploy_logs (deployment_id, status, commit_hash, commit_message, commit_author, output, deployed_at) VALUES (:d,:s,:h,:m,:a,:o,:t)')
            ->execute([':d'=>$id,':s'=>'success',':h'=>$result['hash'],':m'=>$result['message'],':a'=>$result['author'],':o'=>$result['output'],':t'=>date('Y-m-d H:i:s')]);

        // Criar/atualizar vhost Nginx para o subdomínio (se configurado)
        $deployDomain = trim((string)($dep['subdomain'] ?? ''));
        $deployServerId = (int)($dep['server_id'] ?? 0);
        $deployPath = rtrim((string)($dep['deploy_path'] ?? '/var/www/html'), '/');
        $appType = (string)($dep['app_type'] ?? 'php');
        $appPort = (int)($dep['app_port'] ?? 3000);

        if ($deployDomain !== '' && $deployServerId > 0) {
            try {
                $vhostSvc = new \LRV\App\Services\Infra\NginxVhostService();
                if (in_array($appType, ['nodejs', 'python'])) {
                    // Reverse proxy para Node.js/Python
                    $vhostSvc->criarVhostProxy($deployServerId, $deployDomain, $appPort, true);
                } else {
                    // Static/PHP vhost
                    $phpVer = (string)($dep['php_version'] ?? '8.3');
                    $phpSet = [];
                    if (!empty($dep['php_settings'])) {
                        $phpSet = is_string($dep['php_settings']) ? (json_decode($dep['php_settings'], true) ?: []) : (array)$dep['php_settings'];
                    }
                    $vhostSvc->criarVhostStaticSite($deployServerId, $deployDomain, $deployPath, true, $phpVer, $phpSet);
                }
            } catch (\Throwable) {}
        }

        return Resposta::json(['ok' => true, 'commit' => $result['hash'], 'mensagem' => $result['message']]);
    }

    public function logs(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::json(['ok' => false], 401);

        $id = (int)($req->query['id'] ?? 0);
        $pdo = BancoDeDados::pdo();

        // Verify ownership
        $st = $pdo->prepare('SELECT id FROM git_deployments WHERE id = :id AND client_id = :c LIMIT 1');
        $st->execute([':id' => $id, ':c' => $clienteId]);
        if (!$st->fetch()) return Resposta::json(['ok' => false, 'erro' => 'Não encontrado.'], 404);

        $logStmt = $pdo->prepare('SELECT * FROM git_deploy_logs WHERE deployment_id = :id ORDER BY deployed_at DESC LIMIT 20');
        $logStmt->execute([':id' => $id]);
        $logs = $logStmt->fetchAll() ?: [];

        return Resposta::json(['ok' => true, 'logs' => $logs]);
    }

    /**
     * Executa um comando na pasta do deploy (console inline).
     */
    public function console(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::json(['ok' => false, 'erro' => 'Não autenticado.'], 401);

        $id = (int)($req->post['id'] ?? 0);
        $command = trim((string)($req->post['command'] ?? ''));
        if ($command === '') return Resposta::json(['ok' => false, 'erro' => 'Comando vazio.']);
        if (strlen($command) > 2000) return Resposta::json(['ok' => false, 'erro' => 'Comando muito longo.']);

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare(
            'SELECT g.deploy_path, s.ip_address, s.ssh_port, s.ssh_user, s.ssh_password, s.ssh_auth_type, s.ssh_key_id
             FROM git_deployments g
             JOIN vps v ON v.id = g.vps_id
             JOIN servers s ON s.id = v.server_id
             WHERE g.id = :id AND g.client_id = :c LIMIT 1'
        );
        $stmt->execute([':id' => $id, ':c' => $clienteId]);
        $dep = $stmt->fetch();
        if (!is_array($dep)) return Resposta::json(['ok' => false, 'erro' => 'Não encontrado.'], 404);

        $deployPath = rtrim((string)($dep['deploy_path'] ?? '/var/www/html'), '/');
        $fullCmd = 'cd ' . escapeshellarg($deployPath) . ' && ' . $command . ' 2>&1';

        $exec = new \LRV\App\Services\Infra\SshExecutor();
        $host = (string)($dep['ip_address'] ?? '');
        $port = (int)($dep['ssh_port'] ?? 22);
        $user = (string)($dep['ssh_user'] ?? 'root');
        $authType = (string)($dep['ssh_auth_type'] ?? 'password');

        try {
            if ($authType === 'password') {
                $senha = \LRV\App\Services\Infra\SshCrypto::decifrar((string)($dep['ssh_password'] ?? ''));
                $result = $exec->executarComSenha($host, $port, $user, $senha, $fullCmd, 120);
            } else {
                $keyPath = \LRV\Core\ConfiguracoesSistema::sshKeyDir() . DIRECTORY_SEPARATOR . (string)($dep['ssh_key_id'] ?? '');
                $result = $exec->executar($host, $port, $user, $keyPath, $fullCmd, 120);
            }
        } catch (\Throwable $e) {
            return Resposta::json(['ok' => false, 'erro' => $e->getMessage()]);
        }

        $output = $this->filtrarOutputSsh((string)($result['saida'] ?? ''));
        return Resposta::json(['ok' => true, 'output' => $output]);
    }

    public function excluir(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::redirecionar('/cliente/entrar');

        $id = (int)($req->post['id'] ?? 0);
        $pdo = BancoDeDados::pdo();

        // Remove proxy Nginx se tinha domínio temporário
        $st = $pdo->prepare('SELECT temp_domain FROM git_deployments WHERE id = :id AND client_id = :c LIMIT 1');
        $st->execute([':id' => $id, ':c' => $clienteId]);
        $dep = $st->fetch();
        if (is_array($dep) && !empty($dep['temp_domain'])) {
            try {
                (new \LRV\App\Services\Infra\NginxProxyService())->removerProxy((string)$dep['temp_domain']);
            } catch (\Throwable) {}
        }

        // Liberar subdomínio
        (new \LRV\App\Services\Infra\SubdomainVerificationService())->liberarUso('git_deploy', $id);

        $pdo->prepare('DELETE FROM git_deploy_logs WHERE deployment_id = :id')->execute([':id' => $id]);
        $pdo->prepare('DELETE FROM git_deployments WHERE id = :id AND client_id = :c')->execute([':id' => $id, ':c' => $clienteId]);

        return Resposta::redirecionar('/cliente/git-deploy');
    }

    private function executarDeploy(array $dep): array
    {
        $host = trim((string)($dep['ip_address'] ?? ''));
        $port = (int)($dep['ssh_port'] ?? 22);
        $user = trim((string)($dep['ssh_user'] ?? 'root'));
        $authType = (string)($dep['ssh_auth_type'] ?? 'password');
        $repoUrl = (string)$dep['repo_url'];
        $branch = (string)($dep['branch'] ?? 'main');
        $deployPath = rtrim((string)($dep['deploy_path'] ?? '/var/www/html'), '/');
        $forceOverwrite = (int)($dep['force_overwrite'] ?? 1) === 1;

        // Setup SSH executor
        $exec = new \LRV\App\Services\Infra\SshExecutor();
        if ($authType === 'password') {
            $senha = \LRV\App\Services\Infra\SshCrypto::decifrar((string)($dep['ssh_password'] ?? ''));
            $runCmd = fn(string $cmd) => $exec->executarComSenha($host, $port, $user, $senha, $cmd, 120);
        } else {
            $keyId = trim((string)($dep['ssh_key_id'] ?? ''));
            $keyPath = \LRV\Core\ConfiguracoesSistema::sshKeyDir() . DIRECTORY_SEPARATOR . $keyId;
            $runCmd = fn(string $cmd) => $exec->executar($host, $port, $user, $keyPath, $cmd, 120);
        }

        // Ensure git is installed
        $runCmd('which git 2>/dev/null || (apt-get update -qq && apt-get install -y -qq git 2>/dev/null) || true');

        // Fix DNS if needed
        $runCmd('getent hosts github.com >/dev/null 2>&1 || echo -e "nameserver 8.8.8.8\nnameserver 1.1.1.1" > /etc/resolv.conf 2>/dev/null || true');

        // Autenticação: token HTTPS ou deploy key SSH
        $tokenEnc = (string)($dep['auth_token_enc'] ?? '');
        $deployKeyPrivateEnc = (string)($dep['deploy_key_private_enc'] ?? '');
        $gitSshPrefix = '';

        if ($tokenEnc !== '') {
            // Opção 1: Token — injetar na URL HTTPS
            $token = \LRV\App\Services\Infra\SshCrypto::decifrar($tokenEnc);
            if ($token !== '' && str_starts_with($repoUrl, 'https://')) {
                $repoUrl = preg_replace('#^https://#', 'https://' . urlencode($token) . '@', $repoUrl);
            }
        } elseif ($deployKeyPrivateEnc !== '') {
            // Opção 2: Deploy key — converter para SSH e usar chave
            $privateKey = \LRV\App\Services\Infra\SshCrypto::decifrar($deployKeyPrivateEnc);
            if ($privateKey !== '') {
                $depId = (int)($dep['id'] ?? 0);
                // Extrair host e repo path da URL
                $gitHost = 'github.com';
                $gitRepoPath = '';
                if (preg_match('#^https?://([^/]+)/(.+?)(?:\.git)?$#', $repoUrl, $m)) {
                    $gitHost = $m[1];
                    $gitRepoPath = $m[2];
                    // Converter URL HTTPS para SSH com alias único por deploy
                    $repoUrl = 'git@github-deploy-' . $depId . ':' . $gitRepoPath . '.git';
                } elseif (preg_match('#^git@([^:]+):(.+?)(?:\.git)?$#', $repoUrl, $m)) {
                    $gitHost = $m[1];
                    $gitRepoPath = $m[2];
                    $repoUrl = 'git@github-deploy-' . $depId . ':' . $gitRepoPath . '.git';
                }

                // Instalar deploy key com nome único por deploy
                $keyClean = str_replace(["\r\n", "\r"], "\n", trim($privateKey));
                $keyFileName = 'deploy_key_' . $depId;
                $hostAlias = 'github-deploy-' . $depId;
                $installKeyCmd = 'mkdir -p ~/.ssh && chmod 700 ~/.ssh'
                    . ' && printf ' . escapeshellarg('%s') . ' ' . escapeshellarg($keyClean . "\n") . ' > ~/.ssh/' . $keyFileName
                    . ' && chmod 600 ~/.ssh/' . $keyFileName
                    // Adicionar/atualizar bloco no config sem sobrescrever outros
                    . ' && (grep -q "Host ' . $hostAlias . '" ~/.ssh/config 2>/dev/null || echo "" >> ~/.ssh/config)'
                    . ' && sed -i "/Host ' . $hostAlias . '/,/^$/d" ~/.ssh/config 2>/dev/null; true'
                    . ' && echo "Host ' . $hostAlias . '" >> ~/.ssh/config'
                    . ' && echo "  HostName ' . $gitHost . '" >> ~/.ssh/config'
                    . ' && echo "  IdentityFile ~/.ssh/' . $keyFileName . '" >> ~/.ssh/config'
                    . ' && echo "  IdentitiesOnly yes" >> ~/.ssh/config'
                    . ' && echo "  StrictHostKeyChecking no" >> ~/.ssh/config'
                    . ' && echo "" >> ~/.ssh/config'
                    . ' && chmod 600 ~/.ssh/config'
                    . ' && echo KEY_INSTALLED';
                $keyResult = $runCmd($installKeyCmd);
                if (!str_contains((string)($keyResult['saida'] ?? ''), 'KEY_INSTALLED')) {
                    throw new \RuntimeException('Falha ao instalar deploy key: ' . trim((string)($keyResult['saida'] ?? '')));
                }
            }
        }

        // Check if repo already cloned (and if it's the correct repo)
        $checkResult = $runCmd('test -d ' . escapeshellarg($deployPath . '/.git') . ' && git -C ' . escapeshellarg($deployPath) . ' remote get-url origin 2>/dev/null || echo "new"');
        $currentRemote = trim((string)($checkResult['saida'] ?? 'new'));
        // Se o remote não bate com o repo atual, forçar clone limpo
        $isNew = ($currentRemote === 'new' || $currentRemote === '');
        if (!$isNew && !str_contains($currentRemote, basename(rtrim($repoUrl, '.git')))) {
            $isNew = true; // Remote diferente, precisa clonar de novo
        }

        $output = '';

        if ($isNew) {
            $cloneCmd = 'rm -rf ' . escapeshellarg($deployPath) . ' && ' . $gitSshPrefix . 'GIT_TERMINAL_PROMPT=0 git clone --branch ' . escapeshellarg($branch) . ' ' . escapeshellarg($repoUrl) . ' ' . escapeshellarg($deployPath) . ' 2>&1';
            $r = $runCmd($cloneCmd);
            $output .= $this->filtrarOutputSsh((string)($r['saida'] ?? ''));
        } else {
            // Atualizar remote origin para a URL correta (pode ter mudado)
            $runCmd('cd ' . escapeshellarg($deployPath) . ' && git remote set-url origin ' . escapeshellarg($repoUrl) . ' 2>/dev/null');
            if ($forceOverwrite) {
                $pullCmd = 'cd ' . escapeshellarg($deployPath) . ' && ' . $gitSshPrefix . 'GIT_TERMINAL_PROMPT=0 git fetch origin 2>&1 && git reset --hard origin/' . escapeshellarg($branch) . ' 2>&1 && git clean -fd 2>&1';
            } else {
                $pullCmd = 'cd ' . escapeshellarg($deployPath) . ' && git stash 2>&1 && ' . $gitSshPrefix . 'GIT_TERMINAL_PROMPT=0 git pull origin ' . escapeshellarg($branch) . ' 2>&1 && git stash pop 2>&1';
            }
            $r = $runCmd($pullCmd);
            $output .= $this->filtrarOutputSsh((string)($r['saida'] ?? ''));
        }

        // Limpeza não necessária — chave fica em ~/.ssh/deploy_key para futuros deploys

        // Verificar se o clone/pull falhou (antes de rodar pós-deploy)
        if (str_contains(strtolower($output), 'fatal:') || str_contains(strtolower($output), 'error:')) {
            $msg = substr($output, 0, 500);
            if (str_contains($output, 'No such device or address') || str_contains($output, 'Could not resolve host')) {
                $msg = 'Erro de DNS: o servidor não consegue acessar a internet. Detalhes: ' . $msg;
            } elseif (str_contains($output, 'Permission denied') || str_contains($output, 'terminal prompts disabled') || str_contains($output, 'could not read Username') || str_contains($output, 'Authentication failed')) {
                $msg = 'Autenticação falhou. Verifique se a deploy key foi adicionada no repositório ou configure um token de acesso. Detalhes: ' . $msg;
            } elseif (str_contains($output, 'not found') && str_contains($output, 'repository')) {
                $msg = 'Repositório não encontrado. Verifique a URL. Detalhes: ' . $msg;
            } elseif (str_contains($output, 'Remote branch') && str_contains($output, 'not found')) {
                $msg = 'Branch "' . $branch . '" não encontrada. Detalhes: ' . $msg;
            }
            throw new \RuntimeException($msg);
        }

        // Comando pós-deploy (npm install, composer install, etc.)
        $postCmd = trim((string)($dep['post_deploy_cmd'] ?? ''));
        if ($postCmd !== '') {
            $postResult = $runCmd('cd ' . escapeshellarg($deployPath) . ' && ' . $postCmd . ' 2>&1');
            $postOutput = $this->filtrarOutputSsh((string)($postResult['saida'] ?? ''));
            $output .= "\n--- Pós-deploy ---\n" . $postOutput;
        }

        // Corrigir permissões para PHP-FPM (www-data)
        $runCmd('chown -R www-data:www-data ' . escapeshellarg($deployPath) . ' 2>/dev/null; chmod -R 755 ' . escapeshellarg($deployPath) . ' 2>/dev/null');

        // Para Node.js: instalar PM2 e (re)iniciar o processo
        $appType = (string)($dep['app_type'] ?? 'php');
        $appPort = (int)($dep['app_port'] ?? 3000);
        if ($appType === 'nodejs') {
            $pm2Name = 'deploy-' . (int)($dep['id'] ?? 0);
            // Instalar PM2 globalmente se não existir
            $runCmd('which pm2 >/dev/null 2>&1 || npm install -g pm2 2>&1');
            // Parar processo anterior se existir
            $runCmd('pm2 delete ' . escapeshellarg($pm2Name) . ' 2>/dev/null; true');
            // Iniciar com PM2 — usa ecosystem ou npm start
            $startScript = 'cd ' . escapeshellarg($deployPath)
                . ' && export PORT=' . $appPort
                . ' && if test -f ecosystem.config.js || test -f ecosystem.config.cjs; then'
                . '   pm2 start ecosystem.config.* --name ' . escapeshellarg($pm2Name) . ' 2>&1;'
                . ' elif test -f package.json; then'
                . '   pm2 start npm --name ' . escapeshellarg($pm2Name) . ' -- start 2>&1;'
                . ' elif test -f server.js; then'
                . '   pm2 start server.js --name ' . escapeshellarg($pm2Name) . ' 2>&1;'
                . ' elif test -f index.js; then'
                . '   pm2 start index.js --name ' . escapeshellarg($pm2Name) . ' 2>&1;'
                . ' else'
                . '   echo "Nenhum arquivo de entrada encontrado (package.json, server.js, index.js)";'
                . ' fi'
                . ' && pm2 save 2>&1';
            $pm2Result = $runCmd($startScript);
            $output .= "\n--- PM2 ---\n" . $this->filtrarOutputSsh((string)($pm2Result['saida'] ?? ''));
        }

        // Get last commit info
        $logCmd = 'cd ' . escapeshellarg($deployPath) . ' && git log -1 --format="%H|%s|%an" 2>&1';
        $logResult = $runCmd($logCmd);
        $logLine = $this->filtrarOutputSsh(trim((string)($logResult['saida'] ?? '')));
        $parts = explode('|', $logLine, 3);
        $hash = substr(trim($parts[0] ?? ''), 0, 40);
        $message = trim($parts[1] ?? '');
        $author = trim($parts[2] ?? '');

        return ['hash' => $hash, 'message' => $message, 'author' => $author, 'output' => $output];
    }

    /**
     * Remove warnings SSH e linhas irrelevantes do output.
     */
    private function filtrarOutputSsh(string $output): string
    {
        $lines = explode("\n", $output);
        $clean = [];
        foreach ($lines as $l) {
            $trimmed = trim($l);
            if ($trimmed === '') continue;
            if (str_contains($l, 'Warning: Permanently added')) continue;
            if (str_contains($l, 'known_hosts')) continue;
            if (str_starts_with($trimmed, 'Warning:') && !str_contains($l, 'git')) continue;
            $clean[] = $l;
        }
        return implode("\n", $clean);
    }

    /**
     * Gera par de chaves SSH ed25519 para deploy key.
     */
    private function gerarDeployKey(string $label): array
    {
        $tmpDir = sys_get_temp_dir();
        $keyFile = $tmpDir . '/deploy_key_' . bin2hex(random_bytes(8));

        // Gerar chave ed25519 sem passphrase
        $cmd = 'ssh-keygen -t ed25519 -f ' . escapeshellarg($keyFile) . ' -N "" -C ' . escapeshellarg('deploy@lrvweb-' . preg_replace('/[^a-z0-9]/', '', strtolower($label))) . ' 2>&1';
        exec($cmd, $output, $code);

        if ($code !== 0 || !file_exists($keyFile) || !file_exists($keyFile . '.pub')) {
            // Fallback: tentar rsa se ed25519 não disponível
            $cmd = 'ssh-keygen -t rsa -b 4096 -f ' . escapeshellarg($keyFile) . ' -N "" -C ' . escapeshellarg('deploy@lrvweb') . ' 2>&1';
            exec($cmd, $output, $code);
        }

        if (!file_exists($keyFile) || !file_exists($keyFile . '.pub')) {
            throw new \RuntimeException('Falha ao gerar chave SSH.');
        }

        $private = file_get_contents($keyFile);
        $public = file_get_contents($keyFile . '.pub');

        // Limpar arquivos temporários
        @unlink($keyFile);
        @unlink($keyFile . '.pub');

        return ['private' => trim($private), 'public' => trim($public)];
    }

    /**
     * Regenerar deploy key para um deploy existente.
     */
    public function regenerarChave(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::json(['ok' => false], 401);

        $id = (int)($req->post['id'] ?? 0);
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT id, name FROM git_deployments WHERE id = :id AND client_id = :c LIMIT 1');
        $stmt->execute([':id' => $id, ':c' => $clienteId]);
        $dep = $stmt->fetch();
        if (!is_array($dep)) return Resposta::json(['ok' => false, 'erro' => 'Não encontrado.'], 404);

        try {
            $keyPair = $this->gerarDeployKey((string)($dep['name'] ?? ''));
            $pdo->prepare('UPDATE git_deployments SET deploy_key_public=:pub, deploy_key_private_enc=:priv WHERE id=:id AND client_id=:c')
                ->execute([':pub' => $keyPair['public'], ':priv' => \LRV\App\Services\Infra\SshCrypto::cifrar($keyPair['private']), ':id' => $id, ':c' => $clienteId]);
            return Resposta::json(['ok' => true, 'public_key' => $keyPair['public']]);
        } catch (\Throwable $e) {
            return Resposta::json(['ok' => false, 'erro' => $e->getMessage()]);
        }
    }

    private function renderizarErro(int $clienteId, int $id, string $erro): Resposta
    {
        $pdo = BancoDeDados::pdo();
        $vpsStmt = $pdo->prepare("SELECT id, cpu, ram, storage FROM vps WHERE client_id = :c AND status = 'running' ORDER BY id");
        $vpsStmt->execute([':c' => $clienteId]);
        $vpsList = $vpsStmt->fetchAll() ?: [];
        $cStmt = $pdo->prepare('SELECT name, email FROM clients WHERE id = ?');
        $cStmt->execute([$clienteId]);
        $cliente = $cStmt->fetch() ?: [];
        $dep = [];
        if ($id > 0) {
            $st = $pdo->prepare('SELECT * FROM git_deployments WHERE id = :id AND client_id = :c LIMIT 1');
            $st->execute([':id' => $id, ':c' => $clienteId]);
            $dep = $st->fetch() ?: [];
        }
        return Resposta::html(View::renderizar(__DIR__ . '/../../Views/cliente/git-deploy-form.php', [
            'deployment' => $dep, 'vpsList' => $vpsList, 'cliente' => $cliente, 'erro' => $erro,
        ]), 422);
    }
}
