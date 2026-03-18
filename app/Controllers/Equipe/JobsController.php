<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Equipe;

use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;

final class JobsController
{
    public function listar(Requisicao $req): Resposta
    {
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->query('SELECT id, type, status, created_at, updated_at FROM jobs ORDER BY id DESC LIMIT 200');
        $jobs = $stmt->fetchAll();

        $html = View::renderizar(__DIR__ . '/../../Views/equipe/jobs-listar.php', [
            'jobs' => is_array($jobs) ? $jobs : [],
        ]);

        return Resposta::html($html);
    }

    public function ver(Requisicao $req): Resposta
    {
        $id = (int) ($req->query['id'] ?? 0);
        if ($id <= 0) {
            return Resposta::texto('Job inválido.', 400);
        }

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT id, type, payload, status, COALESCE(log,\'\') AS log, created_at, updated_at FROM jobs WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $job = $stmt->fetch();

        if (!is_array($job)) {
            return Resposta::texto('Job não encontrado.', 404);
        }

        $payloadArr = json_decode((string) ($job['payload'] ?? ''), true);
        if (!is_array($payloadArr)) {
            $payloadArr = [];
        }

        $html = View::renderizar(__DIR__ . '/../../Views/equipe/job-ver.php', [
            'job' => $job,
            'payload' => $payloadArr,
        ]);

        return Resposta::html($html);
    }
}
