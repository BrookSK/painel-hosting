<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Cliente;

use LRV\Core\Auth;
use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\View;

final class PainelController
{
    public function index(Requisicao $req): Resposta
    {
        $id = Auth::clienteId();
        if ($id === null) {
            return Resposta::redirecionar('/cliente/entrar');
        }

        $pdo = BancoDeDados::pdo();

        $stmt = $pdo->prepare('SELECT name, email, onboarding_done FROM clients WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $c = $stmt->fetch();

        $onboardingDone = (bool) ($c['onboarding_done'] ?? true);

        // Notificações não lidas
        $notifs = [];
        try {
            $stmtN = $pdo->prepare(
                'SELECT id, type, title, body, created_at FROM client_notifications
                 WHERE client_id = :c AND read_at IS NULL
                 ORDER BY id DESC LIMIT 10'
            );
            $stmtN->execute([':c' => $id]);
            $notifs = $stmtN->fetchAll() ?: [];
        } catch (\Throwable) {}

        // Contagem de VPS
        $totalVps = 0;
        $vpsRunning = 0;
        try {
            $stmtV = $pdo->prepare('SELECT status FROM vps WHERE client_id = :c');
            $stmtV->execute([':c' => $id]);
            $vpsList = $stmtV->fetchAll() ?: [];
            $totalVps = count($vpsList);
            foreach ($vpsList as $v) {
                if (($v['status'] ?? '') === 'running') $vpsRunning++;
            }
        } catch (\Throwable) {}

        // Tickets abertos
        $ticketsAbertos = 0;
        try {
            $stmtT = $pdo->prepare("SELECT COUNT(*) FROM tickets WHERE client_id = :c AND status NOT IN ('closed','resolved')");
            $stmtT->execute([':c' => $id]);
            $ticketsAbertos = (int) $stmtT->fetchColumn();
        } catch (\Throwable) {}

        // Assinatura ativa
        $assinatura = null;
        try {
            $stmtA = $pdo->prepare(
                "SELECT s.status, p.name as plan_name FROM subscriptions s
                 LEFT JOIN plans p ON p.id = s.plan_id
                 WHERE s.client_id = :c AND s.status = 'active'
                 ORDER BY s.id DESC LIMIT 1"
            );
            $stmtA->execute([':c' => $id]);
            $assinatura = $stmtA->fetch() ?: null;
        } catch (\Throwable) {}

        $html = View::renderizar(__DIR__ . '/../../Views/cliente/painel.php', [
            'cliente'        => is_array($c) ? $c : ['name' => 'Cliente', 'email' => ''],
            'notificacoes'   => $notifs,
            'totalVps'       => $totalVps,
            'vpsRunning'     => $vpsRunning,
            'ticketsAbertos' => $ticketsAbertos,
            'assinatura'     => $assinatura,
            'onboardingDone' => $onboardingDone,
        ]);

        return Resposta::html($html);
    }

    public function concluirOnboarding(Requisicao $req): Resposta
    {
        $id = Auth::clienteId();
        if ($id === null) {
            return Resposta::json(['ok' => false], 401);
        }

        $pdo = BancoDeDados::pdo();
        $stmt = $pdo->prepare('UPDATE clients SET onboarding_done = 1 WHERE id = :id');
        $stmt->execute([':id' => $id]);

        return Resposta::json(['ok' => true]);
    }
}
