<?php

declare(strict_types=1);

namespace LRV\Core\Jobs;

use LRV\Core\BancoDeDados;

final class RepositorioJobs
{
    public function criar(string $type, array $payload, ?\DateTimeInterface $runAt = null): int
    {
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('INSERT INTO jobs (type, payload, run_at, status, log, created_at, updated_at) VALUES (:t, :p, :r, :s, :l, :c, :u)');

        $agora = date('Y-m-d H:i:s');
        $runAtDb = $runAt ? $runAt->format('Y-m-d H:i:s') : null;

        $stmt->execute([
            ':t' => $type,
            ':p' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ':r' => $runAtDb,
            ':s' => 'pending',
            ':l' => '',
            ':c' => $agora,
            ':u' => $agora,
        ]);

        return (int) $pdo->lastInsertId();
    }

    public function pegarProximoEMarcarRunning(): ?Job
    {
        $pdo = BancoDeDados::pdo();
        $pdo->beginTransaction();

        try {
            $agora = date('Y-m-d H:i:s');
            $stmt = $pdo->prepare("SELECT id, type, payload, status, COALESCE(log,'') AS log FROM jobs WHERE status = 'pending' AND (run_at IS NULL OR run_at <= :agora) ORDER BY id ASC LIMIT 1 FOR UPDATE");
            $stmt->execute([':agora' => $agora]);
            $linha = $stmt->fetch();

            if (!is_array($linha)) {
                $pdo->commit();
                return null;
            }

            $id = (int) $linha['id'];

            $up = $pdo->prepare("UPDATE jobs SET status = 'running', updated_at = :u WHERE id = :id");
            $up->execute([
                ':u' => date('Y-m-d H:i:s'),
                ':id' => $id,
            ]);

            $pdo->commit();

            $payload = (string) ($linha['payload'] ?? '');
            $payloadArray = json_decode($payload, true);
            if (!is_array($payloadArray)) {
                $payloadArray = [];
            }

            return new Job(
                $id,
                (string) ($linha['type'] ?? ''),
                $payloadArray,
                (string) ($linha['status'] ?? ''),
                (string) ($linha['log'] ?? ''),
            );
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function adicionarLog(int $jobId, string $texto): void
    {
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('UPDATE jobs SET log = CONCAT(COALESCE(log,\'\'), :t), updated_at = :u WHERE id = :id');
        $stmt->execute([
            ':t' => $texto,
            ':u' => date('Y-m-d H:i:s'),
            ':id' => $jobId,
        ]);
    }

    public function marcarConcluido(int $jobId): void
    {
        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare("UPDATE jobs SET status = 'completed', updated_at = :u WHERE id = :id");
        $stmt->execute([
            ':u' => date('Y-m-d H:i:s'),
            ':id' => $jobId,
        ]);
    }

    public function marcarFalha(int $jobId, string $erro): void
    {
        $this->adicionarLog($jobId, "\n[ERRO] " . $erro . "\n");

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare("UPDATE jobs SET status = 'failed', updated_at = :u WHERE id = :id");
        $stmt->execute([
            ':u' => date('Y-m-d H:i:s'),
            ':id' => $jobId,
        ]);
    }
}
