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
        return Resposta::html(View::renderizar(__DIR__ . '/../Views/solucoes/vps.php'));
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

    private function buscarPlanosPorTipo(string $tipo): array
    {
        try {
            $pdo = \LRV\Core\BancoDeDados::pdo();
            $stmt = $pdo->prepare(
                "SELECT id, name, description, cpu, ram, storage, price_monthly, price_monthly_usd, currency,
                        specs_json, is_featured, max_sites, max_databases, plan_type
                 FROM plans
                 WHERE status = 'active' AND client_id IS NULL AND plan_type = :t
                 ORDER BY price_monthly ASC"
            );
            $stmt->execute([':t' => $tipo]);
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable) {
            return [];
        }
    }
}
