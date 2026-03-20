<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Equipe;

use LRV\App\Services\Audit\AuditLogService;
use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;

final class PlanosController
{
    public function listar(Requisicao $req): Resposta
    {
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->query('SELECT id, name, cpu, ram, storage, price_monthly, status FROM plans ORDER BY id DESC');
        $planos = $stmt->fetchAll();

        $html = View::renderizar(__DIR__ . '/../../Views/equipe/planos-listar.php', [
            'planos' => is_array($planos) ? $planos : [],
        ]);

        return Resposta::html($html);
    }

    public function novo(Requisicao $req): Resposta
    {
        $html = View::renderizar(__DIR__ . '/../../Views/equipe/plano-editar.php', [
            'erro' => '',
            'plano' => [
                'id' => null,
                'name' => '',
                'description' => '',
                'cpu' => 2,
                'ram' => 4 * 1024,
                'storage' => 80 * 1024,
                'price_monthly' => '297.00',
                'specs_json' => '',
                'status' => 'active',
            ],
        ]);

        return Resposta::html($html);
    }

    public function editar(Requisicao $req): Resposta
    {
        $id = (int) ($req->query['id'] ?? 0);
        if ($id <= 0) {
            return Resposta::texto('Plano inválido.', 400);
        }

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT * FROM plans WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $plano = $stmt->fetch();

        if (!is_array($plano)) {
            return Resposta::texto('Plano não encontrado.', 404);
        }

        $html = View::renderizar(__DIR__ . '/../../Views/equipe/plano-editar.php', [
            'erro' => '',
            'plano' => $plano,
        ]);

        return Resposta::html($html);
    }

    public function salvar(Requisicao $req): Resposta
    {
        $id = (int) ($req->post['id'] ?? 0);
        $nome = trim((string) ($req->post['name'] ?? ''));
        $desc = trim((string) ($req->post['description'] ?? ''));
        $cpu = (int) ($req->post['cpu'] ?? 0);
        $ram = (int) ($req->post['ram'] ?? 0);
        $storage = (int) ($req->post['storage'] ?? 0);
        $preco = (string) ($req->post['price_monthly'] ?? '0');
        $specs = trim((string) ($req->post['specs_json'] ?? ''));
        $status = (string) ($req->post['status'] ?? 'active');

        if ($nome === '' || $cpu <= 0 || $ram <= 0 || $storage <= 0) {
            return $this->renderizarErro($id, $nome, $desc, $cpu, $ram, $storage, $preco, $specs, $status, 'Preencha os campos obrigatórios.');
        }

        if (!in_array($status, ['active', 'inactive'], true)) {
            $status = 'active';
        }

        $pdo = BancoDeDados::pdo();

        try {
            if ($id > 0) {
                $stmt = $pdo->prepare('UPDATE plans SET name=:n, description=:d, cpu=:c, ram=:r, storage=:s, price_monthly=:p, specs_json=:j, status=:st WHERE id=:id');
                $stmt->execute([
                    ':n' => $nome,
                    ':d' => $desc !== '' ? $desc : null,
                    ':c' => $cpu,
                    ':r' => $ram,
                    ':s' => $storage,
                    ':p' => $preco,
                    ':j' => $specs !== '' ? $specs : null,
                    ':st' => $status,
                    ':id' => $id,
                ]);
            } else {
                $stmt = $pdo->prepare('INSERT INTO plans (name, description, cpu, ram, storage, price_monthly, specs_json, status, created_at) VALUES (:n,:d,:c,:r,:s,:p,:j,:st,:cr)');
                $stmt->execute([
                    ':n' => $nome,
                    ':d' => $desc !== '' ? $desc : null,
                    ':c' => $cpu,
                    ':r' => $ram,
                    ':s' => $storage,
                    ':p' => $preco,
                    ':j' => $specs !== '' ? $specs : null,
                    ':st' => $status,
                    ':cr' => date('Y-m-d H:i:s'),
                ]);
            }
        } catch (\Throwable $e) {
            return $this->renderizarErro($id, $nome, $desc, $cpu, $ram, $storage, $preco, $specs, $status, 'Não foi possível salvar o plano.');
        }

        $auditId = $id;
        if ($auditId <= 0) {
            try {
                $auditId = (int) $pdo->lastInsertId();
            } catch (\Throwable $e) {
                $auditId = 0;
            }
        }

        (new AuditLogService())->registrar(
            'team',
            \LRV\Core\Auth::equipeId(),
            $id > 0 ? 'plan.update' : 'plan.create',
            'plan',
            $auditId > 0 ? $auditId : null,
            [
                'plan_id' => $auditId > 0 ? $auditId : null,
                'name' => $nome,
                'cpu' => $cpu,
                'ram' => $ram,
                'storage' => $storage,
                'price_monthly' => $preco,
                'status' => $status,
                'specs_json_set' => $specs !== '',
                'specs_json_len' => $specs !== '' ? strlen($specs) : 0,
            ],
            $req,
        );

        return Resposta::redirecionar('/equipe/planos');
    }

    private function renderizarErro(int $id, string $nome, string $desc, int $cpu, int $ram, int $storage, string $preco, string $specs, string $status, string $erro): Resposta
    {
        $html = View::renderizar(__DIR__ . '/../../Views/equipe/plano-editar.php', [
            'erro' => $erro,
            'plano' => [
                'id' => $id > 0 ? $id : null,
                'name' => $nome,
                'description' => $desc,
                'cpu' => $cpu,
                'ram' => $ram,
                'storage' => $storage,
                'price_monthly' => $preco,
                'specs_json' => $specs,
                'status' => $status,
            ],
        ]);

        return Resposta::html($html, 422);
    }
}
