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
        $deployPath = trim((string)($req->post['deploy_path'] ?? '/var/www/html'));
        $forceOverwrite = (int)($req->post['force_overwrite'] ?? 1) === 1 ? 1 : 0;
        $gerarTempDomain = (int)($req->post['gerar_temp_domain'] ?? 0) === 1;

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

        if ($id > 0) {
            $pdo->prepare('UPDATE git_deployments SET name=:n, repo_url=:r, branch=:b, subdomain=:s, deploy_path=:dp, force_overwrite=:fo WHERE id=:id AND client_id=:c')
                ->execute([':n'=>$name,':r'=>$repoUrl,':b'=>$branch,':s'=>$subdomain!==''?$subdomain:null,':dp'=>$deployPath,':fo'=>$forceOverwrite,':id'=>$id,':c'=>$clienteId]);
        } else {
            // Gerar domínio temporário se solicitado
            $tempDomain = null;
            if ($gerarTempDomain) {
                $tempBase = trim((string)\LRV\Core\Settings::obter('infra.temp_domain_base', ''));
                if ($tempBase !== '') {
                    $slug = strtolower(preg_replace('/[^a-z0-9]/', '', $name));
                    $slug = substr($slug, 0, 8) ?: 'app';
                    $tempDomain = $slug . substr(bin2hex(random_bytes(3)), 0, 4) . '.' . $tempBase;
                }
            }
            $pdo->prepare('INSERT INTO git_deployments (client_id, vps_id, name, repo_url, branch, subdomain, temp_domain, deploy_path, force_overwrite, status, created_at) VALUES (:c,:v,:n,:r,:b,:s,:td,:dp,:fo,:st,:cr)')
                ->execute([':c'=>$clienteId,':v'=>$vpsId,':n'=>$name,':r'=>$repoUrl,':b'=>$branch,':s'=>$subdomain!==''?$subdomain:null,':td'=>$tempDomain,':dp'=>$deployPath,':fo'=>$forceOverwrite,':st'=>'active',':cr'=>date('Y-m-d H:i:s')]);
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

    public function excluir(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) return Resposta::redirecionar('/cliente/entrar');

        $id = (int)($req->post['id'] ?? 0);
        $pdo = BancoDeDados::pdo();
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

        $exec = new \LRV\App\Services\Infra\SshExecutor();

        if ($authType === 'password') {
            $senha = \LRV\App\Services\Infra\SshCrypto::decifrar((string)($dep['ssh_password'] ?? ''));
            $runCmd = fn(string $cmd) => $exec->executarComSenha($host, $port, $user, $senha, $cmd, 120);
        } else {
            $keyId = trim((string)($dep['ssh_key_id'] ?? ''));
            $keyPath = \LRV\Core\ConfiguracoesSistema::sshKeyDir() . DIRECTORY_SEPARATOR . $keyId;
            $runCmd = fn(string $cmd) => $exec->executar($cmd, $host, $port, $user, $keyPath, 120);
        }

        // Check if repo already cloned
        $checkCmd = 'test -d ' . escapeshellarg($deployPath . '/.git') . ' && echo "exists" || echo "new"';
        $checkResult = $runCmd($checkCmd);
        $isNew = !str_contains((string)($checkResult['saida'] ?? ''), 'exists');

        $output = '';

        if ($isNew) {
            // Clone fresh
            $cloneCmd = 'mkdir -p ' . escapeshellarg($deployPath) . ' && git clone --branch ' . escapeshellarg($branch) . ' ' . escapeshellarg($repoUrl) . ' ' . escapeshellarg($deployPath) . ' 2>&1';
            $r = $runCmd($cloneCmd);
            $output .= (string)($r['saida'] ?? '');
        } else {
            if ($forceOverwrite) {
                // Hard reset — discard local changes
                $pullCmd = 'cd ' . escapeshellarg($deployPath) . ' && git fetch origin 2>&1 && git reset --hard origin/' . escapeshellarg($branch) . ' 2>&1 && git clean -fd 2>&1';
            } else {
                // Stash local changes, pull, pop
                $pullCmd = 'cd ' . escapeshellarg($deployPath) . ' && git stash 2>&1 && git pull origin ' . escapeshellarg($branch) . ' 2>&1 && git stash pop 2>&1';
            }
            $r = $runCmd($pullCmd);
            $output .= (string)($r['saida'] ?? '');
        }

        // Get last commit info
        $logCmd = 'cd ' . escapeshellarg($deployPath) . ' && git log -1 --format="%H|%s|%an" 2>&1';
        $logResult = $runCmd($logCmd);
        $logLine = trim((string)($logResult['saida'] ?? ''));
        $parts = explode('|', $logLine, 3);
        $hash = substr(trim($parts[0] ?? ''), 0, 40);
        $message = trim($parts[1] ?? '');
        $author = trim($parts[2] ?? '');

        return ['hash' => $hash, 'message' => $message, 'author' => $author, 'output' => $output];
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
