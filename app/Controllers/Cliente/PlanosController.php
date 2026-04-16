<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Cliente;

use LRV\Core\Auth;
use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;

final class PlanosController
{
    public function listar(Requisicao $req): Resposta
    {
        $pdo = BancoDeDados::pdo();

        $erro = '';
        $clienteId = Auth::clienteId() ?? 0;
        $isManaged = Auth::clienteGerenciado();

        // Filtro por tipo de produto
        $tipoFiltro = trim((string)($req->query['tipo'] ?? ''));
        $validTypes = ['vps', 'wordpress', 'webhosting', 'nodejs', 'cpp', 'app'];
        if ($tipoFiltro !== '' && !in_array($tipoFiltro, $validTypes, true)) {
            $tipoFiltro = '';
        }

        try {
            $tipoClause = '';
            $params = [];

            if ($tipoFiltro !== '') {
                $tipoClause = ' AND plan_type = :tipo';
                $params[':tipo'] = $tipoFiltro;
            }

            if ($isManaged && $clienteId > 0) {
                // Cliente gerenciado: só planos exclusivos dele
                $sql = "SELECT id, name, description, plan_type, cpu, ram, storage, price_monthly, price_monthly_usd, currency, stripe_price_id, support_channels, specs_json, is_featured FROM plans WHERE status = 'active' AND client_id = :cid{$tipoClause} ORDER BY price_monthly ASC";
                $params[':cid'] = $clienteId;
            } else {
                // Cliente normal: só planos públicos
                $sql = "SELECT id, name, description, plan_type, cpu, ram, storage, price_monthly, price_monthly_usd, currency, stripe_price_id, support_channels, specs_json, is_featured FROM plans WHERE status = 'active' AND client_id IS NULL{$tipoClause} ORDER BY price_monthly ASC";
            }

            try {
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $planos = $stmt->fetchAll();
            } catch (\Throwable $e) {
                // Fallback sem plan_type (migration não rodou ainda)
                if ($isManaged && $clienteId > 0) {
                    $stmt = $pdo->prepare("SELECT id, name, description, cpu, ram, storage, price_monthly FROM plans WHERE status = 'active' AND client_id = :cid ORDER BY price_monthly ASC");
                    $stmt->execute([':cid' => $clienteId]);
                } else {
                    $stmt = $pdo->query("SELECT id, name, description, cpu, ram, storage, price_monthly FROM plans WHERE status = 'active' AND client_id IS NULL ORDER BY price_monthly ASC");
                }
                $planos = $stmt->fetchAll();
            }
        } catch (\Throwable $e) {
            $planos = [];
            $erro = 'Não foi possível carregar os planos. Verifique se as migrations foram executadas.';
        }

        // Carregar addons de cada plano
        foreach ($planos as &$p) {
            try {
                $aStmt = $pdo->prepare('SELECT id, name, description, price FROM plan_addons WHERE plan_id = :pid AND active = 1 ORDER BY sort_order');
                $aStmt->execute([':pid' => (int)$p['id']]);
                $p['addons'] = $aStmt->fetchAll() ?: [];
            } catch (\Throwable) {
                $p['addons'] = [];
            }
        }
        unset($p);

        $cliente = [];
        if ($clienteId > 0) {
            $s = BancoDeDados::pdo()->prepare('SELECT name, email FROM clients WHERE id = ?');
            $s->execute([$clienteId]);
            $cliente = $s->fetch() ?: [];
        }

        $html = View::renderizar(__DIR__ . '/../../Views/cliente/planos.php', [
            'planos'      => is_array($planos) ? $planos : [],
            'erro'        => $erro,
            'cliente'     => $cliente,
            'tipoFiltro'  => $tipoFiltro,
        ]);

        return Resposta::html($html);
    }

    public function checkout(Requisicao $req): Resposta
    {
        $clienteId = Auth::clienteId();
        if ($clienteId === null) {
            return Resposta::redirecionar('/cliente/entrar');
        }

        $planId = (int)($req->query['plan_id'] ?? 0);
        if ($planId <= 0) {
            return Resposta::redirecionar('/cliente/planos');
        }

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare("SELECT * FROM plans WHERE id = :id AND status = 'active' AND (client_id IS NULL OR client_id = :cid)");
        $stmt->execute([':id' => $planId, ':cid' => $clienteId]);
        $plano = $stmt->fetch();

        if (!is_array($plano)) {
            return Resposta::redirecionar('/cliente/planos');
        }

        $addons = [];
        try {
            $aStmt = $pdo->prepare('SELECT id, name, description, price, price_usd, price_annual, price_annual_usd FROM plan_addons WHERE plan_id = :pid AND active = 1 ORDER BY sort_order');
            $aStmt->execute([':pid' => $planId]);
            $addons = $aStmt->fetchAll() ?: [];
        } catch (\Throwable) {
            try {
                $aStmt = $pdo->prepare('SELECT id, name, description, price FROM plan_addons WHERE plan_id = :pid AND active = 1 ORDER BY sort_order');
                $aStmt->execute([':pid' => $planId]);
                $addons = $aStmt->fetchAll() ?: [];
            } catch (\Throwable) {}
        }

        $cStmt = $pdo->prepare('SELECT name, email, cpf_cnpj FROM clients WHERE id = ?');
        $cStmt->execute([$clienteId]);
        $cliente = $cStmt->fetch() ?: [];

        return Resposta::html(View::renderizar(__DIR__ . '/../../Views/cliente/plano-checkout.php', [
            'plano'   => $plano,
            'addons'  => $addons,
            'cliente' => $cliente,
        ]));
    }
}
