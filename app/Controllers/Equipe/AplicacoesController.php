<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Equipe;

use LRV\Core\Auth;
use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\Jobs\RepositorioJobs;
use LRV\Core\View;

final class AplicacoesController
{
    public function listar(Requisicao $req): Resposta
    {
        $pdo = BancoDeDados::pdo();

        $sql = "SELECT a.id, a.vps_id, a.type, a.domain, a.port, a.status, a.repository, a.created_at,
                       v.client_id,
                       c.email AS client_email
                FROM applications a
                INNER JOIN vps v ON v.id = a.vps_id
                INNER JOIN clients c ON c.id = v.client_id
                ORDER BY a.id DESC";
        $stmt = $pdo->query($sql);
        $aplicacoes = $stmt->fetchAll();

        $html = View::renderizar(__DIR__ . '/../../Views/equipe/aplicacoes-listar.php', [
            'aplicacoes' => is_array($aplicacoes) ? $aplicacoes : [],
        ]);

        return Resposta::html($html);
    }

    public function novo(Requisicao $req): Resposta
    {
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->query('SELECT v.id, v.client_id, c.email AS client_email FROM vps v INNER JOIN clients c ON c.id = v.client_id ORDER BY v.id DESC');
        $vps = $stmt->fetchAll();

        $html = View::renderizar(__DIR__ . '/../../Views/equipe/aplicacao-editar.php', [
            'erro' => '',
            'vps' => is_array($vps) ? $vps : [],
            'aplicacao' => [
                'id' => null,
                'vps_id' => null,
                'type' => 'app',
                'domain' => '',
                'port' => '',
                'repository' => '',
                'status' => 'active',
            ],
        ]);

        return Resposta::html($html);
    }

    public function editar(Requisicao $req): Resposta
    {
        $id = (int) ($req->query['id'] ?? 0);
        if ($id <= 0) {
            return Resposta::texto('Aplicação inválida.', 400);
        }

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT * FROM applications WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $aplicacao = $stmt->fetch();

        if (!is_array($aplicacao)) {
            return Resposta::texto('Aplicação não encontrada.', 404);
        }

        $stmt = $pdo->query('SELECT v.id, v.client_id, c.email AS client_email FROM vps v INNER JOIN clients c ON c.id = v.client_id ORDER BY v.id DESC');
        $vps = $stmt->fetchAll();

        $html = View::renderizar(__DIR__ . '/../../Views/equipe/aplicacao-editar.php', [
            'erro' => '',
            'vps' => is_array($vps) ? $vps : [],
            'aplicacao' => $aplicacao,
        ]);

        return Resposta::html($html);
    }

    public function salvar(Requisicao $req): Resposta
    {
        $id = (int) ($req->post['id'] ?? 0);
        $vpsId = (int) ($req->post['vps_id'] ?? 0);
        $type = trim((string) ($req->post['type'] ?? ''));
        $domain = trim((string) ($req->post['domain'] ?? ''));
        $portStr = trim((string) ($req->post['port'] ?? ''));
        $repository = trim((string) ($req->post['repository'] ?? ''));
        $status = trim((string) ($req->post['status'] ?? 'active'));

        if (!in_array($status, ['active', 'inactive', 'deploying', 'error'], true)) {
            $status = 'active';
        }

        $port = 0;
        if ($portStr !== '') {
            $port = (int) $portStr;
        }

        if ($vpsId <= 0 || $type === '') {
            return $this->renderizarErro($id, $vpsId, $type, $domain, $portStr, $repository, $status, 'Preencha os campos obrigatórios.');
        }

        if ($port !== 0 && ($port < 1 || $port > 65535)) {
            return $this->renderizarErro($id, $vpsId, $type, $domain, $portStr, $repository, $status, 'Porta inválida.');
        }

        $pdo = BancoDeDados::pdo();

        try {
            $pdo->beginTransaction();

            $portaAtual = null;
            if ($id > 0) {
                $stmt = $pdo->prepare('SELECT port FROM applications WHERE id = :id');
                $stmt->execute([':id' => $id]);
                $r = $stmt->fetch();
                if (!is_array($r)) {
                    $pdo->rollBack();
                    return Resposta::texto('Aplicação não encontrada.', 404);
                }
                $portaAtual = ($r['port'] ?? null) === null ? null : (int) $r['port'];

                $up = $pdo->prepare('UPDATE applications SET vps_id=:v, type=:t, domain=:d, repository=:r, status=:s WHERE id=:id');
                $up->execute([
                    ':v' => $vpsId,
                    ':t' => $type,
                    ':d' => $domain !== '' ? $domain : null,
                    ':r' => $repository !== '' ? $repository : null,
                    ':s' => $status,
                    ':id' => $id,
                ]);
            } else {
                $ins = $pdo->prepare('INSERT INTO applications (vps_id, type, domain, port, status, repository, created_at) VALUES (:v,:t,:d,NULL,:s,:r,:c)');
                $ins->execute([
                    ':v' => $vpsId,
                    ':t' => $type,
                    ':d' => $domain !== '' ? $domain : null,
                    ':s' => $status,
                    ':r' => $repository !== '' ? $repository : null,
                    ':c' => date('Y-m-d H:i:s'),
                ]);
                $id = (int) $pdo->lastInsertId();
            }

            $portDesejada = $port;
            if ($id > 0) {
                if ($portDesejada === 0 && $portaAtual !== null && $portaAtual > 0) {
                    $portDesejada = $portaAtual;
                }

                if ($portaAtual !== null && $portaAtual > 0 && $portDesejada !== $portaAtual) {
                    $this->liberarPortasDaAplicacao($pdo, $id);
                }

                $portaFinal = $this->reservarPorta($pdo, $id, $portDesejada);

                $upPort = $pdo->prepare('UPDATE applications SET port = :p WHERE id = :id');
                $upPort->execute([':p' => $portaFinal, ':id' => $id]);
            }

            $pdo->commit();
        } catch (\Throwable $e) {
            try {
                $pdo->rollBack();
            } catch (\Throwable $e2) {
            }
            $msg = 'Não foi possível salvar a aplicação.';
            $err = strtolower((string) $e->getMessage());
            if (str_contains($err, 'porta') || str_contains($err, 'port')) {
                $msg = 'Porta indisponível. Escolha outra porta ou deixe vazio para selecionar automaticamente.';
            }
            return $this->renderizarErro($id, $vpsId, $type, $domain, $portStr, $repository, $status, $msg);
        }

        return Resposta::redirecionar('/equipe/aplicacoes');
    }

    public function deploy(Requisicao $req): Resposta
    {
        $id = (int) ($req->post['id'] ?? 0);
        if ($id <= 0) {
            return Resposta::texto('Aplicação inválida.', 400);
        }

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT id FROM applications WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $app = $stmt->fetch();

        if (!is_array($app)) {
            return Resposta::texto('Aplicação não encontrada.', 404);
        }

        $up = $pdo->prepare("UPDATE applications SET status = 'deploying' WHERE id = :id");
        $up->execute([':id' => $id]);

        $repoJobs = new RepositorioJobs();
        $jobId = $repoJobs->criar('deploy_application', [
            'application_id' => $id,
            'user_id' => (int) (Auth::equipeId() ?? 0),
        ]);

        return Resposta::redirecionar('/equipe/jobs/ver?id=' . $jobId);
    }

    public function excluir(Requisicao $req): Resposta
    {
        $id = (int) ($req->post['id'] ?? 0);
        if ($id <= 0) {
            return Resposta::texto('Aplicação inválida.', 400);
        }

        $pdo = BancoDeDados::pdo();

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare('SELECT id FROM applications WHERE id = :id');
            $stmt->execute([':id' => $id]);
            $r = $stmt->fetch();
            if (!is_array($r)) {
                $pdo->rollBack();
                return Resposta::texto('Aplicação não encontrada.', 404);
            }

            $this->liberarPortasDaAplicacao($pdo, $id);

            $del = $pdo->prepare('DELETE FROM applications WHERE id = :id');
            $del->execute([':id' => $id]);

            $pdo->commit();
        } catch (\Throwable $e) {
            try {
                $pdo->rollBack();
            } catch (\Throwable $e2) {
            }
            return Resposta::texto('Não foi possível excluir a aplicação.', 500);
        }

        return Resposta::redirecionar('/equipe/aplicacoes');
    }

    private function reservarPorta(\PDO $pdo, int $applicationId, int $portaDesejada): int
    {
        if ($portaDesejada > 0) {
            if ($this->tentarReservarPorta($pdo, $applicationId, $portaDesejada)) {
                return $portaDesejada;
            }
            throw new \RuntimeException('Porta indisponível.');
        }

        for ($p = 20000; $p <= 40000; $p++) {
            if ($this->tentarReservarPorta($pdo, $applicationId, $p)) {
                return $p;
            }
        }

        throw new \RuntimeException('Sem portas disponíveis.');
    }

    private function tentarReservarPorta(\PDO $pdo, int $applicationId, int $porta): bool
    {
        $stmt = $pdo->prepare('SELECT id, status, application_id FROM ports WHERE port = :p');
        $stmt->execute([':p' => $porta]);
        $row = $stmt->fetch();

        if (!is_array($row)) {
            try {
                $ins = $pdo->prepare('INSERT INTO ports (port, status, application_id) VALUES (:p,:s,:aid)');
                $ins->execute([':p' => $porta, ':s' => 'reserved', ':aid' => $applicationId]);
                return true;
            } catch (\Throwable $e) {
                return false;
            }
        }

        $status = (string) ($row['status'] ?? '');
        $aid = ($row['application_id'] ?? null) === null ? null : (int) $row['application_id'];

        if ($aid === $applicationId && $status === 'reserved') {
            return true;
        }

        if ($aid === null && $status === 'free') {
            $up = $pdo->prepare('UPDATE ports SET status=:s, application_id=:aid WHERE id=:id');
            $up->execute([':s' => 'reserved', ':aid' => $applicationId, ':id' => (int) ($row['id'] ?? 0)]);
            return true;
        }

        return false;
    }

    private function liberarPortasDaAplicacao(\PDO $pdo, int $applicationId): void
    {
        $up = $pdo->prepare('UPDATE ports SET status = :s, application_id = NULL WHERE application_id = :id');
        $up->execute([':s' => 'free', ':id' => $applicationId]);
    }

    private function renderizarErro(int $id, int $vpsId, string $type, string $domain, string $port, string $repository, string $status, string $erro): Resposta
    {
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->query('SELECT v.id, v.client_id, c.email AS client_email FROM vps v INNER JOIN clients c ON c.id = v.client_id ORDER BY v.id DESC');
        $vps = $stmt->fetchAll();

        $html = View::renderizar(__DIR__ . '/../../Views/equipe/aplicacao-editar.php', [
            'erro' => $erro,
            'vps' => is_array($vps) ? $vps : [],
            'aplicacao' => [
                'id' => $id > 0 ? $id : null,
                'vps_id' => $vpsId > 0 ? $vpsId : null,
                'type' => $type,
                'domain' => $domain,
                'port' => $port,
                'repository' => $repository,
                'status' => $status,
            ],
        ]);

        return Resposta::html($html, 422);
    }
}
