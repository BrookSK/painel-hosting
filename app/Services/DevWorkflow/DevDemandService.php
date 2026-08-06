<?php

declare(strict_types=1);

namespace LRV\App\Services\DevWorkflow;

use LRV\Core\BancoDeDados;

/**
 * Service para gerenciamento de demandas de desenvolvimento.
 * Controla o fluxo: criação → branch → desenvolvimento → deploy teste → PR → aprovação/rejeição → merge.
 */
final class DevDemandService
{
    // -------------------------------------------------------------------------
    // Listagem
    // -------------------------------------------------------------------------

    public function listarPorProjeto(int $projetoId, ?string $status = null): array
    {
        $pdo = BancoDeDados::pdo();
        $sql = 'SELECT d.*, u.name AS assigned_name, c.name AS creator_name
                FROM dev_demands d
                LEFT JOIN users u ON u.id = d.assigned_to
                LEFT JOIN users c ON c.id = d.created_by
                WHERE d.project_id = :pid';
        $params = [':pid' => $projetoId];

        if ($status !== null) {
            $sql .= ' AND d.status = :s';
            $params[':s'] = $status;
        }

        $sql .= ' ORDER BY FIELD(d.priority, "urgent", "high", "medium", "low"), d.updated_at DESC';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Lista demandas pendentes de aprovação (PRs) — para admins.
     */
    public function listarPrsPendentes(): array
    {
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->query(
            'SELECT d.*, p.name AS project_name, p.repo_url, u.name AS assigned_name, c.name AS creator_name
             FROM dev_demands d
             JOIN dev_projects p ON p.id = d.project_id
             LEFT JOIN users u ON u.id = d.assigned_to
             LEFT JOIN users c ON c.id = d.created_by
             WHERE d.status = "pr_pending"
             ORDER BY d.pr_created_at ASC'
        );
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Lista demandas atribuídas a um desenvolvedor.
     */
    public function listarPorDesenvolvedor(int $userId): array
    {
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare(
            'SELECT d.*, p.name AS project_name, p.temp_domain
             FROM dev_demands d
             JOIN dev_projects p ON p.id = d.project_id
             WHERE d.assigned_to = :uid AND d.status NOT IN ("merged","closed")
             ORDER BY FIELD(d.priority, "urgent", "high", "medium", "low"), d.updated_at DESC'
        );
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll() ?: [];
    }

    public function buscarPorId(int $id): ?array
    {
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare(
            'SELECT d.*, p.name AS project_name, p.repo_url, p.default_branch, p.temp_domain, p.vps_id,
                    u.name AS assigned_name, u.email AS assigned_email,
                    c.name AS creator_name,
                    r.name AS reviewer_name
             FROM dev_demands d
             JOIN dev_projects p ON p.id = d.project_id
             LEFT JOIN users u ON u.id = d.assigned_to
             LEFT JOIN users c ON c.id = d.created_by
             LEFT JOIN users r ON r.id = d.pr_reviewed_by
             WHERE d.id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $r = $stmt->fetch();
        return is_array($r) ? $r : null;
    }

    // -------------------------------------------------------------------------
    // Criar demanda
    // -------------------------------------------------------------------------

    /**
     * Cria uma demanda e automaticamente cria a branch no repositório.
     */
    public function criar(array $dados, int $criadoPor): int
    {
        $this->validar($dados);

        $pdo = BancoDeDados::pdo();
        $agora = date('Y-m-d H:i:s');

        // Gerar nome da branch baseado no título
        $branchName = $this->gerarNomeBranch($dados['title'], $criadoPor);

        $stmt = $pdo->prepare(
            'INSERT INTO dev_demands (project_id, title, description, branch_name, status, priority, assigned_to, created_by, created_at, updated_at)
             VALUES (:pid, :title, :desc, :branch, "open", :priority, :assigned, :created_by, :cr, :up)'
        );
        $stmt->execute([
            ':pid'        => (int) $dados['project_id'],
            ':title'      => $dados['title'],
            ':desc'       => $dados['description'] ?? null,
            ':branch'     => $branchName,
            ':priority'   => $dados['priority'] ?? 'medium',
            ':assigned'   => !empty($dados['assigned_to']) ? (int) $dados['assigned_to'] : $criadoPor,
            ':created_by' => $criadoPor,
            ':cr'         => $agora,
            ':up'         => $agora,
        ]);

        $demandId = (int) $pdo->lastInsertId();

        // Criar branch no repositório
        try {
            $projectSvc = new DevProjectService();
            $projectSvc->criarBranch((int) $dados['project_id'], $branchName);

            // Atualizar status para in_progress
            $pdo->prepare('UPDATE dev_demands SET status="in_progress", updated_at=:u WHERE id=:id')
                ->execute([':u' => $agora, ':id' => $demandId]);

            // Registrar comentário de criação de branch
            $this->adicionarComentario($demandId, $criadoPor, 'Branch criada: ' . $branchName, 'branch_created', [
                'branch' => $branchName,
            ]);
        } catch (\Throwable $e) {
            // Se falhar a criação da branch, manter open e registrar erro
            $this->adicionarComentario($demandId, $criadoPor, 'Erro ao criar branch: ' . $e->getMessage(), 'comment');
        }

        return $demandId;
    }

    // -------------------------------------------------------------------------
    // Deploy no ambiente de teste
    // -------------------------------------------------------------------------

    /**
     * Faz deploy da branch da demanda no ambiente de teste.
     */
    public function deployTeste(int $demandId, int $userId): array
    {
        $demanda = $this->buscarPorId($demandId);
        if ($demanda === null) {
            throw new \RuntimeException('Demanda não encontrada.');
        }

        $branchName = (string) ($demanda['branch_name'] ?? '');
        $projetoId = (int) ($demanda['project_id'] ?? 0);

        if ($branchName === '') {
            throw new \RuntimeException('Branch não configurada para esta demanda.');
        }

        $projectSvc = new DevProjectService();
        $result = $projectSvc->deployBranch($projetoId, $branchName);

        $pdo = BancoDeDados::pdo();
        $agora = date('Y-m-d H:i:s');

        $commitHash = (string) ($result['commit_hash'] ?? '');
        $commitMsg = (string) ($result['commit_message'] ?? '');
        $commitAuthor = (string) ($result['commit_author'] ?? '');
        $output = (string) ($result['output'] ?? '');

        // Atualizar demanda
        $pdo->prepare('UPDATE dev_demands SET status="testing", last_deploy_at=:d, last_deploy_commit=:h, last_deploy_output=:o, updated_at=:u WHERE id=:id')
            ->execute([':d' => $agora, ':h' => $commitHash, ':o' => $output, ':u' => $agora, ':id' => $demandId]);

        // Log de deploy
        $pdo->prepare(
            'INSERT INTO dev_deploy_logs (demand_id, project_id, branch, commit_hash, commit_message, commit_author, status, output, deployed_by, deployed_at)
             VALUES (:did, :pid, :branch, :hash, :msg, :author, "success", :out, :by, :at)'
        )->execute([
            ':did'    => $demandId,
            ':pid'    => $projetoId,
            ':branch' => $branchName,
            ':hash'   => $commitHash,
            ':msg'    => $commitMsg,
            ':author' => $commitAuthor,
            ':out'    => $output,
            ':by'     => $userId,
            ':at'     => $agora,
        ]);

        // Comentário
        $this->adicionarComentario($demandId, $userId, 'Deploy realizado no ambiente de teste.', 'deploy', [
            'commit_hash' => $commitHash,
            'commit_message' => $commitMsg,
        ]);

        return $result;
    }

    // -------------------------------------------------------------------------
    // Pull Request
    // -------------------------------------------------------------------------

    /**
     * Cria um pull request (muda status para pr_pending).
     */
    public function criarPR(int $demandId, int $userId, string $prTitle = '', string $prDescription = ''): void
    {
        $demanda = $this->buscarPorId($demandId);
        if ($demanda === null) {
            throw new \RuntimeException('Demanda não encontrada.');
        }

        $status = (string) ($demanda['status'] ?? '');
        if (!in_array($status, ['in_progress', 'testing', 'pr_rejected'], true)) {
            throw new \RuntimeException('Não é possível criar PR neste status: ' . $status);
        }

        $pdo = BancoDeDados::pdo();
        $agora = date('Y-m-d H:i:s');

        $titulo = $prTitle !== '' ? $prTitle : (string) ($demanda['title'] ?? '');
        $desc = $prDescription !== '' ? $prDescription : (string) ($demanda['description'] ?? '');

        $pdo->prepare('UPDATE dev_demands SET status="pr_pending", pr_title=:t, pr_description=:d, pr_created_at=:c, pr_rejection_reason=NULL, updated_at=:u WHERE id=:id')
            ->execute([':t' => $titulo, ':d' => $desc, ':c' => $agora, ':u' => $agora, ':id' => $demandId]);

        $this->adicionarComentario($demandId, $userId, 'Pull Request criado: ' . $titulo, 'pr_created', [
            'pr_title' => $titulo,
            'branch' => (string) ($demanda['branch_name'] ?? ''),
        ]);

        // Notificar admins
        $this->notificarAdmins($demanda, $userId, 'pr_created');
    }

    /**
     * Aprova o pull request e faz merge.
     */
    public function aprovarPR(int $demandId, int $reviewerId): array
    {
        $demanda = $this->buscarPorId($demandId);
        if ($demanda === null) {
            throw new \RuntimeException('Demanda não encontrada.');
        }

        if ((string) ($demanda['status'] ?? '') !== 'pr_pending') {
            throw new \RuntimeException('Esta demanda não tem um PR pendente.');
        }

        $branchName = (string) ($demanda['branch_name'] ?? '');
        $projetoId = (int) ($demanda['project_id'] ?? 0);

        // Executar merge
        $projectSvc = new DevProjectService();
        $result = $projectSvc->mergeBranch($projetoId, $branchName);

        $pdo = BancoDeDados::pdo();
        $agora = date('Y-m-d H:i:s');

        $pdo->prepare('UPDATE dev_demands SET status="merged", pr_reviewed_by=:r, pr_reviewed_at=:at, merged_at=:m, updated_at=:u WHERE id=:id')
            ->execute([':r' => $reviewerId, ':at' => $agora, ':m' => $agora, ':u' => $agora, ':id' => $demandId]);

        $this->adicionarComentario($demandId, $reviewerId, 'Pull Request aprovado e merge realizado.', 'pr_approved', [
            'branch' => $branchName,
        ]);

        // Notificar desenvolvedor
        $this->notificarDesenvolvedor($demanda, $reviewerId, 'pr_approved');

        return $result;
    }

    /**
     * Rejeita o pull request com motivo.
     */
    public function rejeitarPR(int $demandId, int $reviewerId, string $motivo): void
    {
        $demanda = $this->buscarPorId($demandId);
        if ($demanda === null) {
            throw new \RuntimeException('Demanda não encontrada.');
        }

        if ((string) ($demanda['status'] ?? '') !== 'pr_pending') {
            throw new \RuntimeException('Esta demanda não tem um PR pendente.');
        }

        $pdo = BancoDeDados::pdo();
        $agora = date('Y-m-d H:i:s');

        $pdo->prepare('UPDATE dev_demands SET status="pr_rejected", pr_reviewed_by=:r, pr_reviewed_at=:at, pr_rejection_reason=:reason, updated_at=:u WHERE id=:id')
            ->execute([':r' => $reviewerId, ':at' => $agora, ':reason' => $motivo, ':u' => $agora, ':id' => $demandId]);

        $this->adicionarComentario($demandId, $reviewerId, 'Pull Request rejeitado. Motivo: ' . $motivo, 'pr_rejected', [
            'reason' => $motivo,
        ]);

        // Notificar desenvolvedor
        $this->notificarDesenvolvedor($demanda, $reviewerId, 'pr_rejected', $motivo);
    }

    // -------------------------------------------------------------------------
    // Comentários e histórico
    // -------------------------------------------------------------------------

    public function adicionarComentario(int $demandId, int $userId, string $comment, string $type = 'comment', ?array $metadata = null): int
    {
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO dev_demand_comments (demand_id, user_id, comment, type, metadata, created_at)
             VALUES (:did, :uid, :comment, :type, :meta, :cr)'
        );
        $stmt->execute([
            ':did'     => $demandId,
            ':uid'     => $userId,
            ':comment' => $comment,
            ':type'    => $type,
            ':meta'    => $metadata !== null ? json_encode($metadata) : null,
            ':cr'      => date('Y-m-d H:i:s'),
        ]);
        return (int) $pdo->lastInsertId();
    }

    public function listarComentarios(int $demandId): array
    {
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare(
            'SELECT c.*, u.name AS user_name
             FROM dev_demand_comments c
             LEFT JOIN users u ON u.id = c.user_id
             WHERE c.demand_id = :did
             ORDER BY c.created_at ASC'
        );
        $stmt->execute([':did' => $demandId]);
        return $stmt->fetchAll() ?: [];
    }

    public function listarDeployLogs(int $demandId): array
    {
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare(
            'SELECT l.*, u.name AS deployed_by_name
             FROM dev_deploy_logs l
             LEFT JOIN users u ON u.id = l.deployed_by
             WHERE l.demand_id = :did
             ORDER BY l.deployed_at DESC
             LIMIT 20'
        );
        $stmt->execute([':did' => $demandId]);
        return $stmt->fetchAll() ?: [];
    }

    // -------------------------------------------------------------------------
    // Ações auxiliares
    // -------------------------------------------------------------------------

    /**
     * Fechar/cancelar uma demanda.
     */
    public function fechar(int $demandId, int $userId, string $motivo = ''): void
    {
        $pdo = BancoDeDados::pdo();
        $agora = date('Y-m-d H:i:s');
        $pdo->prepare('UPDATE dev_demands SET status="closed", closed_at=:c, updated_at=:u WHERE id=:id')
            ->execute([':c' => $agora, ':u' => $agora, ':id' => $demandId]);

        $comentario = 'Demanda fechada.';
        if ($motivo !== '') {
            $comentario .= ' Motivo: ' . $motivo;
        }
        $this->adicionarComentario($demandId, $userId, $comentario, 'status_change');
    }

    /**
     * Reabrir uma demanda fechada/rejeitada.
     */
    public function reabrir(int $demandId, int $userId): void
    {
        $pdo = BancoDeDados::pdo();
        $agora = date('Y-m-d H:i:s');
        $pdo->prepare('UPDATE dev_demands SET status="in_progress", closed_at=NULL, updated_at=:u WHERE id=:id')
            ->execute([':u' => $agora, ':id' => $demandId]);

        $this->adicionarComentario($demandId, $userId, 'Demanda reaberta.', 'status_change');
    }

    /**
     * Atualizar atribuição de desenvolvedor.
     */
    public function atribuir(int $demandId, int $userId, int $assignedTo): void
    {
        $pdo = BancoDeDados::pdo();
        $pdo->prepare('UPDATE dev_demands SET assigned_to=:a, updated_at=:u WHERE id=:id')
            ->execute([':a' => $assignedTo, ':u' => date('Y-m-d H:i:s'), ':id' => $demandId]);

        // Buscar nome do novo responsável
        $stmt = $pdo->prepare('SELECT name FROM users WHERE id=:id');
        $stmt->execute([':id' => $assignedTo]);
        $nome = (string) ($stmt->fetchColumn() ?: 'Desconhecido');

        $this->adicionarComentario($demandId, $userId, 'Demanda atribuída a: ' . $nome, 'status_change', [
            'assigned_to' => $assignedTo,
        ]);
    }

    // -------------------------------------------------------------------------
    // Estatísticas
    // -------------------------------------------------------------------------

    public function estatisticasProjeto(int $projetoId): array
    {
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare(
            'SELECT status, COUNT(*) AS total FROM dev_demands WHERE project_id = :pid GROUP BY status'
        );
        $stmt->execute([':pid' => $projetoId]);
        $rows = $stmt->fetchAll() ?: [];

        $stats = [
            'open' => 0, 'in_progress' => 0, 'testing' => 0,
            'pr_pending' => 0, 'pr_rejected' => 0, 'merged' => 0, 'closed' => 0,
            'total' => 0,
        ];
        foreach ($rows as $row) {
            $s = (string) ($row['status'] ?? '');
            $t = (int) ($row['total'] ?? 0);
            if (isset($stats[$s])) {
                $stats[$s] = $t;
            }
            $stats['total'] += $t;
        }
        return $stats;
    }

    // -------------------------------------------------------------------------
    // Privados
    // -------------------------------------------------------------------------

    private function validar(array $dados): void
    {
        $title = trim((string) ($dados['title'] ?? ''));
        $projectId = (int) ($dados['project_id'] ?? 0);

        if ($title === '' || mb_strlen($title) > 255) {
            throw new \InvalidArgumentException('Título da demanda é obrigatório (máx. 255 caracteres).');
        }
        if ($projectId <= 0) {
            throw new \InvalidArgumentException('Projeto é obrigatório.');
        }
        if (!empty($dados['priority']) && !in_array($dados['priority'], ['low', 'medium', 'high', 'urgent'], true)) {
            throw new \InvalidArgumentException('Prioridade inválida.');
        }
    }

    private function gerarNomeBranch(string $titulo, int $userId): string
    {
        // Formato: feature/titulo-slugificado-userid-timestamp
        $slug = mb_strtolower($titulo);
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s]+/', '-', trim($slug));
        $slug = substr($slug, 0, 40);
        $slug = rtrim($slug, '-');

        if ($slug === '') {
            $slug = 'demanda';
        }

        return 'feature/' . $slug . '-' . $userId . '-' . substr((string) time(), -5);
    }

    private function notificarAdmins(array $demanda, int $remetenteId, string $tipo): void
    {
        try {
            $pdo = BancoDeDados::pdo();

            // Buscar admins/superadmins
            $admins = $pdo->query("SELECT id, name, email FROM users WHERE role IN ('admin','superadmin') AND active = 1")->fetchAll() ?: [];

            // Buscar nome do remetente
            $stmt = $pdo->prepare('SELECT name FROM users WHERE id=:id');
            $stmt->execute([':id' => $remetenteId]);
            $remetenteNome = (string) ($stmt->fetchColumn() ?: 'Desenvolvedor');

            $projetoNome = (string) ($demanda['project_name'] ?? '');
            $demandaTitulo = (string) ($demanda['title'] ?? '');
            $branchName = (string) ($demanda['branch_name'] ?? '');

            $appUrl = rtrim(\LRV\Core\ConfiguracoesSistema::appUrlBase(), '/');
            $demandaUrl = $appUrl . '/equipe/dev/demanda?id=' . (int) ($demanda['id'] ?? 0);

            foreach ($admins as $admin) {
                $email = (string) ($admin['email'] ?? '');
                if ($email === '') continue;

                if ($tipo === 'pr_created') {
                    $titulo = 'Novo Pull Request aguardando aprovação';
                    $corpo = "<p>O desenvolvedor <strong>{$remetenteNome}</strong> criou um Pull Request para a demanda:</p>"
                        . "<p><strong>Projeto:</strong> {$projetoNome}<br>"
                        . "<strong>Demanda:</strong> {$demandaTitulo}<br>"
                        . "<strong>Branch:</strong> {$branchName}</p>"
                        . "<p>Acesse o sistema para revisar e aprovar ou rejeitar.</p>";

                    $html = \LRV\App\Services\Email\EmailTemplate::renderizar(
                        $titulo, $corpo, 'Revisar PR', $demandaUrl
                    );

                    \LRV\App\Services\Email\EmailService::enviar($email, $titulo, $html);
                }

                // Criar notificação interna
                try {
                    $pdo->prepare(
                        'INSERT INTO notifications (user_id, type, title, message, link, created_at) VALUES (:uid, :type, :title, :msg, :link, :cr)'
                    )->execute([
                        ':uid'   => (int) ($admin['id'] ?? 0),
                        ':type'  => 'dev_pr',
                        ':title' => 'PR: ' . $demandaTitulo,
                        ':msg'   => $remetenteNome . ' solicitou revisão do PR para "' . $demandaTitulo . '"',
                        ':link'  => '/equipe/dev/demanda?id=' . (int) ($demanda['id'] ?? 0),
                        ':cr'    => date('Y-m-d H:i:s'),
                    ]);
                } catch (\Throwable) {}
            }
        } catch (\Throwable) {
            // Notificação é best-effort, não deve bloquear o fluxo
        }
    }

    private function notificarDesenvolvedor(array $demanda, int $reviewerId, string $tipo, string $motivo = ''): void
    {
        try {
            $assignedTo = (int) ($demanda['assigned_to'] ?? 0);
            if ($assignedTo <= 0) return;

            $pdo = BancoDeDados::pdo();

            $stmt = $pdo->prepare('SELECT name, email FROM users WHERE id=:id');
            $stmt->execute([':id' => $assignedTo]);
            $dev = $stmt->fetch();
            if (!is_array($dev)) return;

            $stmt2 = $pdo->prepare('SELECT name FROM users WHERE id=:id');
            $stmt2->execute([':id' => $reviewerId]);
            $reviewerNome = (string) ($stmt2->fetchColumn() ?: 'Admin');

            $demandaTitulo = (string) ($demanda['title'] ?? '');
            $projetoNome = (string) ($demanda['project_name'] ?? '');

            $appUrl = rtrim(\LRV\Core\ConfiguracoesSistema::appUrlBase(), '/');
            $demandaUrl = $appUrl . '/equipe/dev/demanda?id=' . (int) ($demanda['id'] ?? 0);

            $email = (string) ($dev['email'] ?? '');
            if ($email === '') return;

            if ($tipo === 'pr_approved') {
                $titulo = 'Pull Request aprovado!';
                $corpo = "<p>Seu Pull Request para a demanda <strong>{$demandaTitulo}</strong> do projeto <strong>{$projetoNome}</strong> foi aprovado por <strong>{$reviewerNome}</strong>.</p>"
                    . "<p>O merge foi realizado com sucesso na branch principal.</p>";
            } elseif ($tipo === 'pr_rejected') {
                $titulo = 'Pull Request não aprovado';
                $corpo = "<p>Seu Pull Request para a demanda <strong>{$demandaTitulo}</strong> do projeto <strong>{$projetoNome}</strong> foi devolvido por <strong>{$reviewerNome}</strong>.</p>";
                if ($motivo !== '') {
                    $corpo .= "<p><strong>Motivo:</strong> {$motivo}</p>";
                }
                $corpo .= "<p>Faça os ajustes necessários e envie novamente.</p>";
            } else {
                return;
            }

            $html = \LRV\App\Services\Email\EmailTemplate::renderizar(
                $titulo, $corpo, 'Ver demanda', $demandaUrl
            );

            \LRV\App\Services\Email\EmailService::enviar($email, $titulo, $html);

            // Notificação interna
            try {
                $pdo->prepare(
                    'INSERT INTO notifications (user_id, type, title, message, link, created_at) VALUES (:uid, :type, :title, :msg, :link, :cr)'
                )->execute([
                    ':uid'   => $assignedTo,
                    ':type'  => 'dev_pr',
                    ':title' => ($tipo === 'pr_approved' ? 'PR Aprovado' : 'PR Devolvido') . ': ' . $demandaTitulo,
                    ':msg'   => $reviewerNome . ($tipo === 'pr_approved' ? ' aprovou' : ' devolveu') . ' o PR de "' . $demandaTitulo . '"',
                    ':link'  => '/equipe/dev/demanda?id=' . (int) ($demanda['id'] ?? 0),
                    ':cr'    => date('Y-m-d H:i:s'),
                ]);
            } catch (\Throwable) {}
        } catch (\Throwable) {
            // Best-effort
        }
    }
}
