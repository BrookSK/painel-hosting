<?php

declare(strict_types=1);

namespace LRV\App\Controllers;

use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;

final class SolucoesController
{
    public function vps(Requisicao $req): Resposta
    {
        return Resposta::html(View::renderizar(__DIR__ . '/../Views/solucoes/vps.php', [
            'planos' => $this->buscarPlanosPorTipo('vps'),
        ]));
    }

    public function aplicacoes(Requisicao $req): Resposta
    {
        return Resposta::html(View::renderizar(__DIR__ . '/../Views/solucoes/aplicacoes.php'));
    }

    public function devops(Requisicao $req): Resposta
    {
        return Resposta::html(View::renderizar(__DIR__ . '/../Views/solucoes/devops.php'));
    }

    public function email(Requisicao $req): Resposta
    {
        return Resposta::html(View::renderizar(__DIR__ . '/../Views/solucoes/email.php'));
    }

    public function seguranca(Requisicao $req): Resposta
    {
        return Resposta::html(View::renderizar(__DIR__ . '/../Views/solucoes/seguranca.php'));
    }

    public function wordpress(Requisicao $req): Resposta
    {
        return Resposta::html(View::renderizar(__DIR__ . '/../Views/solucoes/wordpress.php', [
            'planos' => $this->buscarPlanosPorTipo('wordpress'),
        ]));
    }

    public function webhosting(Requisicao $req): Resposta
    {
        return Resposta::html(View::renderizar(__DIR__ . '/../Views/solucoes/webhosting.php', [
            'planos' => $this->buscarPlanosPorTipo('webhosting'),
        ]));
    }

    public function nodejs(Requisicao $req): Resposta
    {
        return Resposta::html(View::renderizar(__DIR__ . '/../Views/solucoes/nodejs.php', [
            'planos' => $this->buscarPlanosPorTipo('nodejs'),
        ]));
    }

    public function cpp(Requisicao $req): Resposta
    {
        return Resposta::html(View::renderizar(__DIR__ . '/../Views/solucoes/cpp.php', [
            'planos' => $this->buscarPlanosPorTipo('cpp'),
        ]));
    }

    public function php(Requisicao $req): Resposta
    {
        return Resposta::html(View::renderizar(__DIR__ . '/../Views/solucoes/php.php', [
            'planos' => $this->buscarPlanosPorTipo('php'),
        ]));
    }

    public function python(Requisicao $req): Resposta
    {
        return Resposta::html(View::renderizar(__DIR__ . '/../Views/solucoes/python.php', [
            'planos' => $this->buscarPlanosPorTipo('python'),
        ]));
    }

    private function buscarPlanosPorTipo(string $tipo): array
    {
        try {
            $pdo = \LRV\Core\BancoDeDados::pdo();
            $stmt = $pdo->prepare(
                "SELECT id, name, description, cpu, ram, storage, price_monthly, price_monthly_usd, currency,
                        specs_json, is_featured, max_sites, max_databases, plan_type, support_channels
                 FROM plans
                 WHERE status = 'active' AND client_id IS NULL AND plan_type = :t
                 ORDER BY price_monthly ASC"
            );
            $stmt->execute([':t' => $tipo]);
            $planos = $stmt->fetchAll() ?: [];

            // Buscar addons para cada plano
            if (!empty($planos)) {
                $ids = implode(',', array_map('intval', array_column($planos, 'id')));
                $stmtA = $pdo->query("SELECT * FROM plan_addons WHERE plan_id IN ($ids) AND active = 1 ORDER BY plan_id, sort_order ASC");
                $allAddons = $stmtA ? ($stmtA->fetchAll() ?: []) : [];
                $addonsByPlan = [];
                foreach ($allAddons as $a) {
                    $addonsByPlan[(int)$a['plan_id']][] = $a;
                }
                foreach ($planos as &$p) {
                    $p['addons'] = $addonsByPlan[(int)$p['id']] ?? [];
                    $p['badge'] = ((int)($p['is_featured'] ?? 0) === 1) ? 'POPULAR' : '';
                }
                unset($p);
            }

            return $planos;
        } catch (\Throwable) {
            return [];
        }
    }
}
