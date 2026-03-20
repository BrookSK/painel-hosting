<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Equipe;

use LRV\Core\Auth;
use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;

final class PainelController
{
    public function index(Requisicao $req): Resposta
    {
        $id = Auth::equipeId();
        if ($id === null) {
            return Resposta::redirecionar('/equipe/entrar');
        }

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('SELECT name, email, role FROM users WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $u = $stmt->fetch();

        // Métricas agregadas
        $metricas = [];
        try {
            $metricas['total_vps']      = (int) $pdo->query("SELECT COUNT(*) FROM vps WHERE deleted_at IS NULL")->fetchColumn();
            $metricas['vps_running']    = (int) $pdo->query("SELECT COUNT(*) FROM vps WHERE status='running' AND deleted_at IS NULL")->fetchColumn();
            $metricas['total_clientes'] = (int) $pdo->query("SELECT COUNT(*) FROM clients")->fetchColumn();
            $metricas['tickets_abertos']= (int) $pdo->query("SELECT COUNT(*) FROM tickets WHERE status NOT IN ('closed')")->fetchColumn();
            $metricas['jobs_pendentes'] = (int) $pdo->query("SELECT COUNT(*) FROM jobs WHERE status IN ('pending','running')")->fetchColumn();
            $metricas['nodes_online']   = (int) $pdo->query("SELECT COUNT(*) FROM servers WHERE status='active' AND is_online=1")->fetchColumn();
            $rec = $pdo->query("SELECT COALESCE(SUM(s.price_monthly),0) FROM subscriptions sub INNER JOIN plans s ON s.id=sub.plan_id WHERE sub.status='active'")->fetchColumn();
            $metricas['receita_mensal'] = number_format((float) $rec, 2, '.', '');
        } catch (\Throwable $e) {
        }

        $html = View::renderizar(__DIR__ . '/../../Views/equipe/painel.php', [
            'usuario' => is_array($u) ? $u : ['name' => 'Usuário', 'email' => '', 'role' => ''],
            'metricas' => $metricas,
        ]);

        return Resposta::html($html);
    }
}
