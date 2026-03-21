<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Equipe;

use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;

final class ErrosController
{
    public function listar(Requisicao $req): Resposta
    {
        $pdo = BancoDeDados::pdo();

        $filtroCode    = (int) ($req->query['code'] ?? 0);
        $filtroType    = trim((string) ($req->query['type'] ?? ''));
        $filtroResolved = isset($req->query['resolved']) ? (int) $req->query['resolved'] : -1;
        $pagina        = max(1, (int) ($req->query['pagina'] ?? 1));
        $porPagina     = 30;
        $offset        = ($pagina - 1) * $porPagina;

        $where  = ['1=1'];
        $params = [];

        if ($filtroCode > 0) {
            $where[]          = 'http_code = :code';
            $params[':code']  = $filtroCode;
        }
        if ($filtroType !== '') {
            $where[]          = 'error_type = :type';
            $params[':type']  = $filtroType;
        }
        if ($filtroResolved >= 0) {
            $where[]              = 'resolved = :res';
            $params[':resolved']  = $filtroResolved;
        }

        $whereStr = implode(' AND ', $where);

        $total = 0;
        try {
            $stmtC = $pdo->prepare("SELECT COUNT(*) FROM system_errors WHERE {$whereStr}");
            $stmtC->execute($params);
            $total = (int) $stmtC->fetchColumn();
        } catch (\Throwable) {}

        $erros = [];
        try {
            $stmtL = $pdo->prepare(
                "SELECT id, http_code, error_type, message, url, method,
                        ip_address, user_type, user_id, notified, resolved, created_at
                 FROM system_errors
                 WHERE {$whereStr}
                 ORDER BY id DESC
                 LIMIT {$porPagina} OFFSET {$offset}"
            );
            $stmtL->execute($params);
            $erros = $stmtL->fetchAll() ?: [];
        } catch (\Throwable) {}

        // Contagens por código para o resumo
        $resumo = [];
        try {
            $stmtR = $pdo->query(
                'SELECT http_code, COUNT(*) as total, SUM(resolved=0) as pendentes
                 FROM system_errors GROUP BY http_code ORDER BY total DESC LIMIT 10'
            );
            $resumo = $stmtR ? ($stmtR->fetchAll() ?: []) : [];
        } catch (\Throwable) {}

        $html = View::renderizar(__DIR__ . '/../../Views/equipe/erros-listar.php', [
            'erros'          => $erros,
            'resumo'         => $resumo,
            'total'          => $total,
            'pagina'         => $pagina,
            'porPagina'      => $porPagina,
            'filtroCode'     => $filtroCode,
            'filtroType'     => $filtroType,
            'filtroResolved' => $filtroResolved,
        ]);

        return Resposta::html($html);
    }

    public function ver(Requisicao $req): Resposta
    {
        $id  = (int) ($req->query['id'] ?? 0);
        if ($id <= 0) {
            return Resposta::redirecionar('/equipe/erros');
        }

        $pdo  = BancoDeDados::pdo();
        $erro = null;

        try {
            $stmt = $pdo->prepare('SELECT * FROM system_errors WHERE id = :id');
            $stmt->execute([':id' => $id]);
            $erro = $stmt->fetch() ?: null;
        } catch (\Throwable) {}

        if ($erro === null) {
            return Resposta::redirecionar('/equipe/erros');
        }

        $html = View::renderizar(__DIR__ . '/../../Views/equipe/erro-ver.php', [
            'erro' => $erro,
        ]);

        return Resposta::html($html);
    }

    public function resolver(Requisicao $req): Resposta
    {
        $id = (int) ($req->post['id'] ?? 0);
        if ($id <= 0) {
            return Resposta::json(['ok' => false, 'erro' => 'ID inválido'], 400);
        }

        $pdo = BancoDeDados::pdo();

        try {
            $stmt = $pdo->prepare(
                'UPDATE system_errors SET resolved = 1, resolved_by = :uid, resolved_at = :now WHERE id = :id'
            );
            $stmt->execute([
                ':uid' => \LRV\Core\Auth::equipeId(),
                ':now' => date('Y-m-d H:i:s'),
                ':id'  => $id,
            ]);
        } catch (\Throwable $e) {
            return Resposta::json(['ok' => false, 'erro' => $e->getMessage()], 500);
        }

        return Resposta::json(['ok' => true]);
    }

    public function excluir(Requisicao $req): Resposta
    {
        $id = (int) ($req->post['id'] ?? 0);
        if ($id <= 0) {
            return Resposta::json(['ok' => false, 'erro' => 'ID inválido'], 400);
        }

        $pdo = BancoDeDados::pdo();

        try {
            $pdo->prepare('DELETE FROM system_errors WHERE id = :id')->execute([':id' => $id]);
        } catch (\Throwable $e) {
            return Resposta::json(['ok' => false, 'erro' => $e->getMessage()], 500);
        }

        return Resposta::json(['ok' => true]);
    }

    public function limparResolvidos(Requisicao $req): Resposta
    {
        $pdo = BancoDeDados::pdo();

        try {
            $pdo->exec('DELETE FROM system_errors WHERE resolved = 1');
        } catch (\Throwable $e) {
            return Resposta::json(['ok' => false, 'erro' => $e->getMessage()], 500);
        }

        return Resposta::json(['ok' => true]);
    }
}
