<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Equipe;

use LRV\App\Services\DevWorkflow\DevDemandService;
use LRV\App\Services\DevWorkflow\DevProjectService;
use LRV\Core\Auth;
use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;

/**
 * Controller do módulo Dev Workflow (equipe).
 * Gerencia projetos internos e demandas de desenvolvimento.
 */
final class ProjetosDevController
{
    // =========================================================================
    // PROJETOS
    // =========================================================================

    public function listar(Requisicao $req): Resposta
    {
        $svc = new DevProjectService();
        $projetos = $svc->listar('active');

        // Buscar PRs pendentes para badge
        $demandSvc = new DevDemandService();
        $prsPendentes = $demandSvc->listarPrsPendentes();

        return Resposta::html(View::renderizar(__DIR__ . '/../../Views/equipe/projetos-dev-listar.php', [
            'projetos'     => $projetos,
            'prsPendentes' => $prsPendentes,
        ]));
    }

    public function novo(Requisicao $req): Resposta
    {
        $pdo = BancoDeDados::pdo();
        // Buscar VPS da equipe (is_team_vps = 1)
        $vpsList = $pdo->query("SELECT v.id, COALESCE(v.team_vps_name, v.name) AS name, v.cpu, v.ram, v.storage, s.hostname FROM vps v JOIN servers s ON s.id = v.server_id WHERE v.is_team_vps = 1 AND v.status = 'running' AND v.deleted_at IS NULL ORDER BY v.id DESC")->fetchAll() ?: [];

        return Resposta::html(View::renderizar(__DIR__ . '/../../Views/equipe/projeto-dev-editar.php', [
            'projeto'  => null,
            'vpsList'  => $vpsList,
            'erro'     => '',
            'sucesso'  => '',
        ]));
    }

    public function editar(Requisicao $req): Resposta
    {
        $id = (int) ($req->query['id'] ?? 0);
        $svc = new DevProjectService();
        $projeto = $svc->buscarPorId($id);
        if ($projeto === null) {
            return Resposta::redirecionar('/equipe/dev');
        }

        $pdo = BancoDeDados::pdo();
        $vpsList = $pdo->query("SELECT v.id, COALESCE(v.team_vps_name, v.name) AS name, v.cpu, v.ram, v.storage, s.hostname FROM vps v JOIN servers s ON s.id = v.server_id WHERE v.is_team_vps = 1 AND v.status = 'running' AND v.deleted_at IS NULL ORDER BY v.id DESC")->fetchAll() ?: [];

        return Resposta::html(View::renderizar(__DIR__ . '/../../Views/equipe/projeto-dev-editar.php', [
            'projeto'  => $projeto,
            'vpsList'  => $vpsList,
            'erro'     => '',
            'sucesso'  => ($req->query['ok'] ?? '') === '1' ? 'Projeto salvo com sucesso.' : '',
        ]));
    }

    public function salvar(Requisicao $req): Resposta
    {
        $id = (int) ($req->post['id'] ?? 0);
        $dados = [
            'name'            => trim((string) ($req->post['name'] ?? '')),
            'description'     => trim((string) ($req->post['description'] ?? '')),
            'repo_url'        => trim((string) ($req->post['repo_url'] ?? '')),
            'default_branch'  => trim((string) ($req->post['default_branch'] ?? 'main')),
            'vps_id'          => (int) ($req->post['vps_id'] ?? 0),
            'deploy_path'     => trim((string) ($req->post['deploy_path'] ?? '')),
            'temp_domain'     => trim((string) ($req->post['temp_domain'] ?? '')),
            'app_type'        => trim((string) ($req->post['app_type'] ?? 'php')),
            'app_port'        => (int) ($req->post['app_port'] ?? 3000),
            'php_version'     => trim((string) ($req->post['php_version'] ?? '8.3')),
            'post_deploy_cmd' => trim((string) ($req->post['post_deploy_cmd'] ?? '')),
            'auth_token'      => trim((string) ($req->post['auth_token'] ?? '')),
        ];

        // Deploy path auto
        if ($dados['deploy_path'] === '') {
            $slug = strtolower(preg_replace('/[^a-z0-9]/', '-', strtolower($dados['name'])));
            $slug = trim($slug, '-') ?: 'projeto';
            $dados['deploy_path'] = '/var/www/dev/' . $slug;
        }

        $svc = new DevProjectService();

        try {
            if ($id > 0) {
                $svc->atualizar($id, $dados);
                return Resposta::redirecionar('/equipe/dev/projeto/editar?id=' . $id . '&ok=1');
            }

            $userId = Auth::equipeId() ?? 0;
            $newId = $svc->criar($dados, $userId);
            return Resposta::redirecionar('/equipe/dev/projeto/editar?id=' . $newId . '&ok=1');
        } catch (\InvalidArgumentException $e) {
            $pdo = BancoDeDados::pdo();
            $vpsList = $pdo->query("SELECT v.id, COALESCE(v.team_vps_name, v.name) AS name, v.cpu, v.ram, v.storage, s.hostname FROM vps v JOIN servers s ON s.id = v.server_id WHERE v.is_team_vps = 1 AND v.status = 'running' AND v.deleted_at IS NULL ORDER BY v.id DESC")->fetchAll() ?: [];

            return Resposta::html(View::renderizar(__DIR__ . '/../../Views/equipe/projeto-dev-editar.php', [
                'projeto'  => $id > 0 ? $svc->buscarPorId($id) : array_merge($dados, ['id' => 0]),
                'vpsList'  => $vpsList,
                'erro'     => $e->getMessage(),
                'sucesso'  => '',
            ]));
        }
    }

    public function arquivar(Requisicao $req): Resposta
    {
        $id = (int) ($req->post['id'] ?? 0);
        if ($id > 0) {
            (new DevProjectService())->arquivar($id);
        }
        return Resposta::redirecionar('/equipe/dev');
    }

    public function regenerarChave(Requisicao $req): Resposta
    {
        $id = (int) ($req->post['id'] ?? 0);
        $pub = (new DevProjectService())->regenerarDeployKey($id);
        return Resposta::json(['ok' => $pub !== null, 'public_key' => $pub]);
    }

    public function clonarRepo(Requisicao $req): Resposta
    {
        $id = (int) ($req->post['id'] ?? 0);
        try {
            $result = (new DevProjectService())->clonarRepositorio($id);
            return Resposta::json(['ok' => true, 'output' => $result['output'] ?? '']);
        } catch (\Throwable $e) {
            return Resposta::json(['ok' => false, 'erro' => $e->getMessage()]);
        }
    }

    // =========================================================================
    // DEMANDAS
    // =========================================================================

    public function demandas(Requisicao $req): Resposta
    {
        $projetoId = (int) ($req->query['projeto'] ?? 0);
        $projectSvc = new DevProjectService();
        $projeto = $projectSvc->buscarPorId($projetoId);
        if ($projeto === null) {
            return Resposta::redirecionar('/equipe/dev');
        }

        $demandSvc = new DevDemandService();
        $demandas = $demandSvc->listarPorProjeto($projetoId);
        $stats = $demandSvc->estatisticasProjeto($projetoId);

        // Lista de membros da equipe (para atribuição)
        $pdo = BancoDeDados::pdo();
        $membros = $pdo->query("SELECT id, name, email, role FROM users WHERE active = 1 ORDER BY name")->fetchAll() ?: [];

        return Resposta::html(View::renderizar(__DIR__ . '/../../Views/equipe/demandas-dev-listar.php', [
            'projeto'   => $projeto,
            'demandas'  => $demandas,
            'stats'     => $stats,
            'membros'   => $membros,
        ]));
    }

    public function criarDemanda(Requisicao $req): Resposta
    {
        $dados = [
            'project_id'  => (int) ($req->post['project_id'] ?? 0),
            'title'       => trim((string) ($req->post['title'] ?? '')),
            'description' => trim((string) ($req->post['description'] ?? '')),
            'priority'    => trim((string) ($req->post['priority'] ?? 'medium')),
            'assigned_to' => (int) ($req->post['assigned_to'] ?? 0),
        ];

        $userId = Auth::equipeId() ?? 0;
        $demandSvc = new DevDemandService();

        try {
            $demandSvc->criar($dados, $userId);
            return Resposta::redirecionar('/equipe/dev/demandas?projeto=' . $dados['project_id']);
        } catch (\Throwable $e) {
            return Resposta::json(['ok' => false, 'erro' => $e->getMessage()]);
        }
    }

    public function verDemanda(Requisicao $req): Resposta
    {
        $id = (int) ($req->query['id'] ?? 0);
        $demandSvc = new DevDemandService();
        $demanda = $demandSvc->buscarPorId($id);
        if ($demanda === null) {
            return Resposta::redirecionar('/equipe/dev');
        }

        $comentarios = $demandSvc->listarComentarios($id);
        $deployLogs = $demandSvc->listarDeployLogs($id);

        // Buscar commits da branch (se possível)
        $commits = [];
        $branchName = (string) ($demanda['branch_name'] ?? '');
        $projetoId = (int) ($demanda['project_id'] ?? 0);
        if ($branchName !== '' && $projetoId > 0) {
            try {
                $projectSvc = new DevProjectService();
                $result = $projectSvc->listarCommitsBranch($projetoId, $branchName);
                $commits = $result['commits'] ?? [];
            } catch (\Throwable) {}
        }

        // Membros da equipe
        $pdo = BancoDeDados::pdo();
        $membros = $pdo->query("SELECT id, name, email, role FROM users WHERE active = 1 ORDER BY name")->fetchAll() ?: [];

        return Resposta::html(View::renderizar(__DIR__ . '/../../Views/equipe/demanda-dev-ver.php', [
            'demanda'     => $demanda,
            'comentarios' => $comentarios,
            'deployLogs'  => $deployLogs,
            'commits'     => $commits,
            'membros'     => $membros,
        ]));
    }

    public function deployDemanda(Requisicao $req): Resposta
    {
        $id = (int) ($req->post['id'] ?? 0);
        $userId = Auth::equipeId() ?? 0;
        $demandSvc = new DevDemandService();

        try {
            $result = $demandSvc->deployTeste($id, $userId);
            return Resposta::json([
                'ok'     => true,
                'commit' => $result['commit_hash'] ?? '',
                'output' => $result['output'] ?? '',
            ]);
        } catch (\Throwable $e) {
            return Resposta::json(['ok' => false, 'erro' => $e->getMessage()]);
        }
    }

    public function criarPR(Requisicao $req): Resposta
    {
        $id = (int) ($req->post['id'] ?? 0);
        $prTitle = trim((string) ($req->post['pr_title'] ?? ''));
        $prDescription = trim((string) ($req->post['pr_description'] ?? ''));
        $userId = Auth::equipeId() ?? 0;
        $demandSvc = new DevDemandService();

        try {
            $demandSvc->criarPR($id, $userId, $prTitle, $prDescription);
            return Resposta::json(['ok' => true]);
        } catch (\Throwable $e) {
            return Resposta::json(['ok' => false, 'erro' => $e->getMessage()]);
        }
    }

    public function aprovarPR(Requisicao $req): Resposta
    {
        $id = (int) ($req->post['id'] ?? 0);
        $userId = Auth::equipeId() ?? 0;
        $demandSvc = new DevDemandService();

        try {
            $demandSvc->aprovarPR($id, $userId);
            return Resposta::json(['ok' => true]);
        } catch (\Throwable $e) {
            return Resposta::json(['ok' => false, 'erro' => $e->getMessage()]);
        }
    }

    public function rejeitarPR(Requisicao $req): Resposta
    {
        $id = (int) ($req->post['id'] ?? 0);
        $motivo = trim((string) ($req->post['motivo'] ?? ''));
        $userId = Auth::equipeId() ?? 0;
        $demandSvc = new DevDemandService();

        try {
            $demandSvc->rejeitarPR($id, $userId, $motivo);
            return Resposta::json(['ok' => true]);
        } catch (\Throwable $e) {
            return Resposta::json(['ok' => false, 'erro' => $e->getMessage()]);
        }
    }

    public function diffPR(Requisicao $req): Resposta
    {
        $id = (int) ($req->query['id'] ?? 0);
        $demandSvc = new DevDemandService();
        $demanda = $demandSvc->buscarPorId($id);
        if ($demanda === null) {
            return Resposta::json(['ok' => false, 'erro' => 'Demanda não encontrada.']);
        }

        $branchName = (string) ($demanda['branch_name'] ?? '');
        $projetoId = (int) ($demanda['project_id'] ?? 0);

        try {
            $projectSvc = new DevProjectService();
            $result = $projectSvc->obterDiff($projetoId, $branchName);
            return Resposta::json(['ok' => true, 'diff' => $result['output'] ?? '']);
        } catch (\Throwable $e) {
            return Resposta::json(['ok' => false, 'erro' => $e->getMessage()]);
        }
    }

    public function comentar(Requisicao $req): Resposta
    {
        $demandId = (int) ($req->post['demand_id'] ?? 0);
        $comment = trim((string) ($req->post['comment'] ?? ''));
        $userId = Auth::equipeId() ?? 0;

        if ($comment === '' || $demandId <= 0) {
            return Resposta::json(['ok' => false, 'erro' => 'Comentário vazio.']);
        }

        $demandSvc = new DevDemandService();
        $demandSvc->adicionarComentario($demandId, $userId, $comment);

        return Resposta::json(['ok' => true]);
    }

    public function fecharDemanda(Requisicao $req): Resposta
    {
        $id = (int) ($req->post['id'] ?? 0);
        $motivo = trim((string) ($req->post['motivo'] ?? ''));
        $userId = Auth::equipeId() ?? 0;

        (new DevDemandService())->fechar($id, $userId, $motivo);
        return Resposta::redirecionar('/equipe/dev/demanda?id=' . $id);
    }

    public function reabrirDemanda(Requisicao $req): Resposta
    {
        $id = (int) ($req->post['id'] ?? 0);
        $userId = Auth::equipeId() ?? 0;

        (new DevDemandService())->reabrir($id, $userId);
        return Resposta::redirecionar('/equipe/dev/demanda?id=' . $id);
    }

    public function atribuirDemanda(Requisicao $req): Resposta
    {
        $id = (int) ($req->post['id'] ?? 0);
        $assignedTo = (int) ($req->post['assigned_to'] ?? 0);
        $userId = Auth::equipeId() ?? 0;

        if ($assignedTo > 0) {
            (new DevDemandService())->atribuir($id, $userId, $assignedTo);
        }

        return Resposta::json(['ok' => true]);
    }

    // =========================================================================
    // MINHAS DEMANDAS (visão do desenvolvedor)
    // =========================================================================

    public function minhasDemandas(Requisicao $req): Resposta
    {
        $userId = Auth::equipeId() ?? 0;
        $demandSvc = new DevDemandService();
        $demandas = $demandSvc->listarPorDesenvolvedor($userId);

        return Resposta::json(['ok' => true, 'demandas' => $demandas]);
    }

    // =========================================================================
    // VPS DA EQUIPE
    // =========================================================================

    /**
     * Lista VPS da equipe + formulário para criar nova.
     */
    public function vpsEquipe(Requisicao $req): Resposta
    {
        $pdo = BancoDeDados::pdo();

        // Listar VPS da equipe
        $stmt = $pdo->query(
            "SELECT v.id, v.team_vps_name, v.cpu, v.ram, v.storage, v.status, v.server_id, v.created_at, s.hostname, s.ip_address
             FROM vps v
             LEFT JOIN servers s ON s.id = v.server_id
             WHERE v.is_team_vps = 1 AND v.deleted_at IS NULL
             ORDER BY v.id DESC"
        );
        $vpsList = $stmt->fetchAll() ?: [];

        // Servidores disponíveis para provisionar
        $servidores = $pdo->query("SELECT id, hostname, ip_address, cpu_total, ram_total, storage_total FROM servers WHERE status = 'active' ORDER BY hostname")->fetchAll() ?: [];

        return Resposta::html(View::renderizar(__DIR__ . '/../../Views/equipe/dev-vps-listar.php', [
            'vpsList'    => $vpsList,
            'servidores' => $servidores,
            'erro'       => '',
            'sucesso'    => ($req->query['ok'] ?? '') === '1' ? 'VPS da equipe criada com sucesso.' : '',
        ]));
    }

    /**
     * Cria uma VPS da equipe (sem client_id).
     */
    public function salvarVps(Requisicao $req): Resposta
    {
        $nome = trim((string) ($req->post['name'] ?? ''));
        $serverId = (int) ($req->post['server_id'] ?? 0);
        $cpu = (int) ($req->post['cpu'] ?? 2);
        $ram = (int) ($req->post['ram'] ?? 2048);
        $storage = (int) ($req->post['storage'] ?? 20480);

        if ($nome === '') {
            $pdo = BancoDeDados::pdo();
            $servidores = $pdo->query("SELECT id, hostname, ip_address, cpu_total, ram_total, storage_total FROM servers WHERE status = 'active' ORDER BY hostname")->fetchAll() ?: [];
            $vpsList = $pdo->query("SELECT v.id, v.team_vps_name, v.cpu, v.ram, v.storage, v.status, v.server_id, v.created_at, s.hostname, s.ip_address FROM vps v LEFT JOIN servers s ON s.id = v.server_id WHERE v.is_team_vps = 1 AND v.deleted_at IS NULL ORDER BY v.id DESC")->fetchAll() ?: [];
            return Resposta::html(View::renderizar(__DIR__ . '/../../Views/equipe/dev-vps-listar.php', [
                'vpsList'    => $vpsList,
                'servidores' => $servidores,
                'erro'       => 'Nome da VPS é obrigatório.',
                'sucesso'    => '',
            ]));
        }

        if ($serverId <= 0) {
            $pdo = BancoDeDados::pdo();
            $servidores = $pdo->query("SELECT id, hostname, ip_address, cpu_total, ram_total, storage_total FROM servers WHERE status = 'active' ORDER BY hostname")->fetchAll() ?: [];
            $vpsList = $pdo->query("SELECT v.id, v.team_vps_name, v.cpu, v.ram, v.storage, v.status, v.server_id, v.created_at, s.hostname, s.ip_address FROM vps v LEFT JOIN servers s ON s.id = v.server_id WHERE v.is_team_vps = 1 AND v.deleted_at IS NULL ORDER BY v.id DESC")->fetchAll() ?: [];
            return Resposta::html(View::renderizar(__DIR__ . '/../../Views/equipe/dev-vps-listar.php', [
                'vpsList'    => $vpsList,
                'servidores' => $servidores,
                'erro'       => 'Selecione um servidor.',
                'sucesso'    => '',
            ]));
        }

        // Validar limites
        if ($cpu < 1 || $cpu > 32) $cpu = 2;
        if ($ram < 512 || $ram > 131072) $ram = 2048;
        if ($storage < 5120 || $storage > 1048576) $storage = 20480;

        $pdo = BancoDeDados::pdo();
        $agora = date('Y-m-d H:i:s');

        $pdo->prepare(
            'INSERT INTO vps (client_id, server_id, name, team_vps_name, cpu, ram, storage, status, is_team_vps, created_at)
             VALUES (NULL, :server, :name, :tname, :cpu, :ram, :storage, "pending_provisioning", 1, :cr)'
        )->execute([
            ':server' => $serverId,
            ':name'   => $nome,
            ':tname'  => $nome,
            ':cpu'    => $cpu,
            ':ram'    => $ram,
            ':storage' => $storage,
            ':cr'     => $agora,
        ]);

        $newId = (int) $pdo->lastInsertId();

        // Tentar provisionar automaticamente
        try {
            $repo = new \LRV\Core\Jobs\RepositorioJobs();
            $repo->criar('provisionar_vps', ['vps_id' => $newId]);

            $svc = new \LRV\App\Services\Provisioning\VpsProvisioningService(
                new \LRV\App\Services\Provisioning\DockerCli()
            );
            $svc->provisionar($newId, function(string $m) {});
        } catch (\Throwable) {
            // Se falhar, fica em pending_provisioning pro admin provisionar manualmente
        }

        return Resposta::redirecionar('/equipe/dev/vps?ok=1');
    }
}
