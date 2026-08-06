<?php

declare(strict_types=1);

namespace LRV\App\Services\DevWorkflow;

use LRV\Core\BancoDeDados;

/**
 * Service para gerenciamento de projetos internos de desenvolvimento.
 * Cuida do CRUD de projetos e integração com repositórios Git.
 */
final class DevProjectService
{
    // -------------------------------------------------------------------------
    // Listagem
    // -------------------------------------------------------------------------

    public function listar(string $status = 'active'): array
    {
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare(
            'SELECT p.*,
                    (SELECT COUNT(*) FROM dev_demands d WHERE d.project_id = p.id AND d.status NOT IN ("merged","closed")) AS open_demands,
                    (SELECT COUNT(*) FROM dev_demands d WHERE d.project_id = p.id AND d.status = "pr_pending") AS pending_prs
             FROM dev_projects p
             WHERE p.status = :s
             ORDER BY p.updated_at DESC'
        );
        $stmt->execute([':s' => $status]);
        return $stmt->fetchAll() ?: [];
    }

    public function buscarPorId(int $id): ?array
    {
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT * FROM dev_projects WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $r = $stmt->fetch();
        return is_array($r) ? $r : null;
    }

    // -------------------------------------------------------------------------
    // CRUD
    // -------------------------------------------------------------------------

    public function criar(array $dados, int $criadoPor): int
    {
        $this->validar($dados);

        $pdo = BancoDeDados::pdo();
        $agora = date('Y-m-d H:i:s');

        // Gerar deploy key
        $deployKeyPublic = null;
        $deployKeyPrivateEnc = null;
        try {
            $keyPair = $this->gerarDeployKey($dados['name']);
            $deployKeyPublic = $keyPair['public'];
            $deployKeyPrivateEnc = \LRV\App\Services\Infra\SshCrypto::cifrar($keyPair['private']);
        } catch (\Throwable) {}

        // Gerar webhook secret
        $webhookSecret = bin2hex(random_bytes(32));

        // Cifrar auth token se fornecido
        $authTokenEnc = null;
        if (!empty($dados['auth_token'])) {
            $authTokenEnc = \LRV\App\Services\Infra\SshCrypto::cifrar($dados['auth_token']);
        }

        $stmt = $pdo->prepare(
            'INSERT INTO dev_projects (name, description, repo_url, default_branch, vps_id, deploy_path, temp_domain, app_type, app_port, php_version, post_deploy_cmd, auth_token_enc, deploy_key_public, deploy_key_private_enc, webhook_secret, status, created_by, created_at, updated_at)
             VALUES (:name, :desc, :repo, :branch, :vps, :path, :domain, :type, :port, :php, :cmd, :token, :pub, :priv, :ws, "active", :by, :cr, :up)'
        );
        $stmt->execute([
            ':name'   => $dados['name'],
            ':desc'   => $dados['description'] ?? null,
            ':repo'   => $dados['repo_url'],
            ':branch' => $dados['default_branch'] ?? 'main',
            ':vps'    => !empty($dados['vps_id']) ? (int) $dados['vps_id'] : null,
            ':path'   => $dados['deploy_path'] ?? null,
            ':domain' => $dados['temp_domain'] ?? null,
            ':type'   => $dados['app_type'] ?? 'php',
            ':port'   => !empty($dados['app_port']) ? (int) $dados['app_port'] : null,
            ':php'    => $dados['php_version'] ?? '8.3',
            ':cmd'    => $dados['post_deploy_cmd'] ?? null,
            ':token'  => $authTokenEnc,
            ':pub'    => $deployKeyPublic,
            ':priv'   => $deployKeyPrivateEnc,
            ':ws'     => $webhookSecret,
            ':by'     => $criadoPor,
            ':cr'     => $agora,
            ':up'     => $agora,
        ]);

        $newId = (int) $pdo->lastInsertId();

        // Gerar domínio temporário se VPS está vinculada
        if ($newId > 0 && !empty($dados['vps_id']) && empty($dados['temp_domain'])) {
            $this->gerarDominioTemporario($newId, $dados['name'], (int) $dados['vps_id']);
        }

        return $newId;
    }

    public function atualizar(int $id, array $dados): void
    {
        $this->validar($dados);

        $pdo = BancoDeDados::pdo();
        $agora = date('Y-m-d H:i:s');

        $sql = 'UPDATE dev_projects SET name=:name, description=:desc, repo_url=:repo, default_branch=:branch, vps_id=:vps, deploy_path=:path, temp_domain=:domain, app_type=:type, app_port=:port, php_version=:php, post_deploy_cmd=:cmd, updated_at=:up';
        $params = [
            ':name'   => $dados['name'],
            ':desc'   => $dados['description'] ?? null,
            ':repo'   => $dados['repo_url'],
            ':branch' => $dados['default_branch'] ?? 'main',
            ':vps'    => !empty($dados['vps_id']) ? (int) $dados['vps_id'] : null,
            ':path'   => $dados['deploy_path'] ?? null,
            ':domain' => $dados['temp_domain'] ?? null,
            ':type'   => $dados['app_type'] ?? 'php',
            ':port'   => !empty($dados['app_port']) ? (int) $dados['app_port'] : null,
            ':php'    => $dados['php_version'] ?? '8.3',
            ':cmd'    => $dados['post_deploy_cmd'] ?? null,
            ':up'     => $agora,
            ':id'     => $id,
        ];

        if (!empty($dados['auth_token'])) {
            $sql .= ', auth_token_enc=:token';
            $params[':token'] = \LRV\App\Services\Infra\SshCrypto::cifrar($dados['auth_token']);
        }

        $sql .= ' WHERE id=:id';
        $pdo->prepare($sql)->execute($params);
    }

    public function arquivar(int $id): void
    {
        $pdo = BancoDeDados::pdo();
        $pdo->prepare('UPDATE dev_projects SET status="archived", updated_at=:u WHERE id=:id')
            ->execute([':u' => date('Y-m-d H:i:s'), ':id' => $id]);
    }

    public function reativar(int $id): void
    {
        $pdo = BancoDeDados::pdo();
        $pdo->prepare('UPDATE dev_projects SET status="active", updated_at=:u WHERE id=:id')
            ->execute([':u' => date('Y-m-d H:i:s'), ':id' => $id]);
    }

    // -------------------------------------------------------------------------
    // Deploy Key
    // -------------------------------------------------------------------------

    public function regenerarDeployKey(int $id): ?string
    {
        $projeto = $this->buscarPorId($id);
        if ($projeto === null) return null;

        try {
            $keyPair = $this->gerarDeployKey((string) ($projeto['name'] ?? 'project'));
            $pdo = BancoDeDados::pdo();
            $pdo->prepare('UPDATE dev_projects SET deploy_key_public=:pub, deploy_key_private_enc=:priv, updated_at=:u WHERE id=:id')
                ->execute([
                    ':pub'  => $keyPair['public'],
                    ':priv' => \LRV\App\Services\Infra\SshCrypto::cifrar($keyPair['private']),
                    ':u'    => date('Y-m-d H:i:s'),
                    ':id'   => $id,
                ]);
            return $keyPair['public'];
        } catch (\Throwable) {
            return null;
        }
    }

    // -------------------------------------------------------------------------
    // Git Operations via SSH no servidor
    // -------------------------------------------------------------------------

    /**
     * Clona ou atualiza o repositório no servidor de teste (branch principal).
     */
    public function clonarRepositorio(int $projetoId): array
    {
        $projeto = $this->buscarPorId($projetoId);
        if ($projeto === null) {
            throw new \RuntimeException('Projeto não encontrado.');
        }

        $serverInfo = $this->obterInfoServidor((int) ($projeto['vps_id'] ?? 0));
        if ($serverInfo === null) {
            throw new \RuntimeException('VPS/Servidor não configurado para este projeto.');
        }

        $repoUrl = (string) ($projeto['repo_url'] ?? '');
        $deployPath = (string) ($projeto['deploy_path'] ?? '');
        $branch = (string) ($projeto['default_branch'] ?? 'main');

        if ($repoUrl === '' || $deployPath === '') {
            throw new \RuntimeException('URL do repositório ou caminho de deploy não configurados.');
        }

        // Montar comando de clone/pull
        $cmd = $this->montarComandoClone($projeto, $branch, $deployPath);

        return $this->executarNoServidor($serverInfo, $cmd);
    }

    /**
     * Cria uma nova branch a partir da branch principal.
     */
    public function criarBranch(int $projetoId, string $branchName): array
    {
        $projeto = $this->buscarPorId($projetoId);
        if ($projeto === null) {
            throw new \RuntimeException('Projeto não encontrado.');
        }

        $serverInfo = $this->obterInfoServidor((int) ($projeto['vps_id'] ?? 0));
        if ($serverInfo === null) {
            throw new \RuntimeException('VPS/Servidor não configurado.');
        }

        $deployPath = (string) ($projeto['deploy_path'] ?? '');
        $defaultBranch = (string) ($projeto['default_branch'] ?? 'main');

        $cmd = "cd " . escapeshellarg($deployPath)
            . " && git fetch origin"
            . " && git checkout " . escapeshellarg($defaultBranch)
            . " && git pull origin " . escapeshellarg($defaultBranch)
            . " && git checkout -b " . escapeshellarg($branchName)
            . " && git push -u origin " . escapeshellarg($branchName);

        return $this->executarNoServidor($serverInfo, $cmd);
    }

    /**
     * Faz deploy de uma branch específica no ambiente de teste.
     */
    public function deployBranch(int $projetoId, string $branchName): array
    {
        $projeto = $this->buscarPorId($projetoId);
        if ($projeto === null) {
            throw new \RuntimeException('Projeto não encontrado.');
        }

        $serverInfo = $this->obterInfoServidor((int) ($projeto['vps_id'] ?? 0));
        if ($serverInfo === null) {
            throw new \RuntimeException('VPS/Servidor não configurado.');
        }

        $deployPath = (string) ($projeto['deploy_path'] ?? '');
        $postDeploy = (string) ($projeto['post_deploy_cmd'] ?? '');

        $cmd = "cd " . escapeshellarg($deployPath)
            . " && git fetch origin"
            . " && git checkout " . escapeshellarg($branchName)
            . " && git pull origin " . escapeshellarg($branchName);

        if ($postDeploy !== '') {
            $cmd .= " && " . $postDeploy;
        }

        $cmd .= " && git log -1 --format='%H|||%s|||%an'";

        $result = $this->executarNoServidor($serverInfo, $cmd);

        // Extrair info do commit
        $output = $result['output'] ?? '';
        $lines = explode("\n", trim($output));
        $lastLine = end($lines);
        $parts = explode('|||', $lastLine);

        $result['commit_hash'] = $parts[0] ?? '';
        $result['commit_message'] = $parts[1] ?? '';
        $result['commit_author'] = $parts[2] ?? '';

        return $result;
    }

    /**
     * Faz merge de uma branch na branch principal (simula aprovação de PR).
     */
    public function mergeBranch(int $projetoId, string $branchName): array
    {
        $projeto = $this->buscarPorId($projetoId);
        if ($projeto === null) {
            throw new \RuntimeException('Projeto não encontrado.');
        }

        $serverInfo = $this->obterInfoServidor((int) ($projeto['vps_id'] ?? 0));
        if ($serverInfo === null) {
            throw new \RuntimeException('VPS/Servidor não configurado.');
        }

        $deployPath = (string) ($projeto['deploy_path'] ?? '');
        $defaultBranch = (string) ($projeto['default_branch'] ?? 'main');

        $cmd = "cd " . escapeshellarg($deployPath)
            . " && git fetch origin"
            . " && git checkout " . escapeshellarg($defaultBranch)
            . " && git pull origin " . escapeshellarg($defaultBranch)
            . " && git merge --no-ff " . escapeshellarg($branchName) . " -m " . escapeshellarg("Merge branch '$branchName' into $defaultBranch")
            . " && git push origin " . escapeshellarg($defaultBranch);

        return $this->executarNoServidor($serverInfo, $cmd);
    }

    /**
     * Obtém diff entre uma branch e a branch principal.
     */
    public function obterDiff(int $projetoId, string $branchName): array
    {
        $projeto = $this->buscarPorId($projetoId);
        if ($projeto === null) {
            throw new \RuntimeException('Projeto não encontrado.');
        }

        $serverInfo = $this->obterInfoServidor((int) ($projeto['vps_id'] ?? 0));
        if ($serverInfo === null) {
            throw new \RuntimeException('VPS/Servidor não configurado.');
        }

        $deployPath = (string) ($projeto['deploy_path'] ?? '');
        $defaultBranch = (string) ($projeto['default_branch'] ?? 'main');

        $cmd = "cd " . escapeshellarg($deployPath)
            . " && git fetch origin"
            . " && git diff origin/" . escapeshellarg($defaultBranch) . "...origin/" . escapeshellarg($branchName) . " --stat"
            . " && echo '---FULL_DIFF---'"
            . " && git diff origin/" . escapeshellarg($defaultBranch) . "...origin/" . escapeshellarg($branchName);

        return $this->executarNoServidor($serverInfo, $cmd);
    }

    /**
     * Lista commits de uma branch que não estão na main.
     */
    public function listarCommitsBranch(int $projetoId, string $branchName): array
    {
        $projeto = $this->buscarPorId($projetoId);
        if ($projeto === null) {
            throw new \RuntimeException('Projeto não encontrado.');
        }

        $serverInfo = $this->obterInfoServidor((int) ($projeto['vps_id'] ?? 0));
        if ($serverInfo === null) {
            throw new \RuntimeException('VPS/Servidor não configurado.');
        }

        $deployPath = (string) ($projeto['deploy_path'] ?? '');
        $defaultBranch = (string) ($projeto['default_branch'] ?? 'main');

        $cmd = "cd " . escapeshellarg($deployPath)
            . " && git fetch origin"
            . " && git log origin/" . escapeshellarg($defaultBranch) . "..origin/" . escapeshellarg($branchName) . " --format='%H|||%s|||%an|||%ai' --no-merges";

        $result = $this->executarNoServidor($serverInfo, $cmd);
        $output = trim($result['output'] ?? '');
        $commits = [];

        if ($output !== '') {
            foreach (explode("\n", $output) as $line) {
                $parts = explode('|||', $line);
                if (count($parts) >= 4) {
                    $commits[] = [
                        'hash'    => $parts[0],
                        'message' => $parts[1],
                        'author'  => $parts[2],
                        'date'    => $parts[3],
                    ];
                }
            }
        }

        $result['commits'] = $commits;
        return $result;
    }

    // -------------------------------------------------------------------------
    // Helpers privados
    // -------------------------------------------------------------------------

    private function validar(array $dados): void
    {
        $name = trim((string) ($dados['name'] ?? ''));
        $repoUrl = trim((string) ($dados['repo_url'] ?? ''));

        if ($name === '' || mb_strlen($name) > 150) {
            throw new \InvalidArgumentException('Nome do projeto é obrigatório (máx. 150 caracteres).');
        }
        if ($repoUrl === '' || mb_strlen($repoUrl) > 500) {
            throw new \InvalidArgumentException('URL do repositório é obrigatória.');
        }
        if (!empty($dados['app_type']) && !in_array($dados['app_type'], ['php', 'nodejs', 'python', 'static'], true)) {
            throw new \InvalidArgumentException('Tipo de aplicação inválido.');
        }
    }

    private function gerarDeployKey(string $nome): array
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'devkey_');
        if ($tmpFile === false) {
            throw new \RuntimeException('Não foi possível criar arquivo temporário.');
        }
        @unlink($tmpFile);

        $comment = 'devworkflow-' . preg_replace('/[^a-z0-9]/', '', strtolower($nome));
        $cmd = 'ssh-keygen -t ed25519 -f ' . escapeshellarg($tmpFile) . ' -N "" -C ' . escapeshellarg($comment) . ' 2>&1';
        exec($cmd, $output, $exitCode);

        if ($exitCode !== 0 || !is_file($tmpFile)) {
            throw new \RuntimeException('Falha ao gerar deploy key: ' . implode("\n", $output));
        }

        $private = file_get_contents($tmpFile);
        $public = file_get_contents($tmpFile . '.pub');

        @unlink($tmpFile);
        @unlink($tmpFile . '.pub');

        if ($private === false || $public === false) {
            throw new \RuntimeException('Falha ao ler deploy key gerada.');
        }

        return ['public' => trim($public), 'private' => trim($private)];
    }

    private function gerarDominioTemporario(int $projetoId, string $nome, int $vpsId): void
    {
        $tempBase = trim((string) \LRV\Core\Settings::obter('infra.temp_domain_base', ''));
        if ($tempBase === '') return;

        $slug = strtolower(preg_replace('/[^a-z0-9]/', '', strtolower($nome)));
        $slug = substr($slug, 0, 10) ?: 'dev';
        $tempDomain = 'dev-' . $slug . substr(bin2hex(random_bytes(2)), 0, 4) . '.' . $tempBase;

        $pdo = BancoDeDados::pdo();
        $pdo->prepare('UPDATE dev_projects SET temp_domain=:d, updated_at=:u WHERE id=:id')
            ->execute([':d' => $tempDomain, ':u' => date('Y-m-d H:i:s'), ':id' => $projetoId]);

        // Configurar proxy Nginx
        try {
            $vpsStmt = $pdo->prepare('SELECT s.ip_address FROM vps v JOIN servers s ON s.id = v.server_id WHERE v.id = :v LIMIT 1');
            $vpsStmt->execute([':v' => $vpsId]);
            $vpsRow = $vpsStmt->fetch();
            $vpsIp = is_array($vpsRow) ? (string) ($vpsRow['ip_address'] ?? '') : '';
            if ($vpsIp !== '') {
                (new \LRV\App\Services\Infra\NginxProxyService())->criarProxy($tempDomain, $vpsIp, 80);
            }
        } catch (\Throwable) {}
    }

    private function obterInfoServidor(int $vpsId): ?array
    {
        if ($vpsId <= 0) return null;

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare(
            'SELECT s.id AS server_id, s.ip_address, s.ssh_port, s.ssh_user, s.ssh_password, s.ssh_auth_type, s.ssh_key_id
             FROM vps v JOIN servers s ON s.id = v.server_id
             WHERE v.id = :v LIMIT 1'
        );
        $stmt->execute([':v' => $vpsId]);
        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }

    private function executarNoServidor(array $serverInfo, string $cmd): array
    {
        $ssh = new \LRV\App\Services\Infra\SshExecutor();
        $authType = (string) ($serverInfo['ssh_auth_type'] ?? 'password');
        $host = (string) ($serverInfo['ip_address'] ?? '');
        $port = (int) ($serverInfo['ssh_port'] ?? 22);
        $user = (string) ($serverInfo['ssh_user'] ?? 'root');

        if ($authType === 'key') {
            $keyDir = rtrim(\LRV\Core\ConfiguracoesSistema::sshKeyDir(), "/\\");
            $keyId = (string) ($serverInfo['ssh_key_id'] ?? '');
            $keyPath = $keyDir . DIRECTORY_SEPARATOR . $keyId;
            $result = $ssh->executar($host, $port, $user, $keyPath, $cmd, 120);
        } else {
            $senhaEnc = (string) ($serverInfo['ssh_password'] ?? '');
            $senha = $senhaEnc !== '' ? \LRV\App\Services\Infra\SshCrypto::decifrar($senhaEnc) : '';
            $result = $ssh->executarComSenha($host, $port, $user, $senha, $cmd, 120);
        }

        return $result;
    }

    private function montarComandoClone(array $projeto, string $branch, string $deployPath): string
    {
        $repoUrl = (string) ($projeto['repo_url'] ?? '');

        // Verificar se repositório já está clonado
        $cmd = "if [ -d " . escapeshellarg($deployPath . '/.git') . " ]; then "
            . "cd " . escapeshellarg($deployPath)
            . " && git fetch origin"
            . " && git checkout " . escapeshellarg($branch)
            . " && git pull origin " . escapeshellarg($branch)
            . "; else "
            . "mkdir -p " . escapeshellarg(dirname($deployPath))
            . " && git clone " . escapeshellarg($repoUrl) . " " . escapeshellarg($deployPath)
            . " -b " . escapeshellarg($branch)
            . "; fi";

        return $cmd;
    }
}
