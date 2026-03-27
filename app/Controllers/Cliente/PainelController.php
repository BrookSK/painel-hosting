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
            $stmtV = $pdo->prepare("SELECT status FROM vps WHERE client_id = :c AND status NOT IN ('expired','removed') AND deleted_at IS NULL");
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

        // Trial ativo
        $trialInfo = null;
        try {
            $stmtTr = $pdo->prepare(
                "SELECT expires_at, vcpu, ram_mb, disco_gb, status FROM client_trials
                 WHERE client_id = :c AND status = 'active' LIMIT 1"
            );
            $stmtTr->execute([':c' => $id]);
            $tr = $stmtTr->fetch();
            if ($tr) {
                $now     = new \DateTimeImmutable();
                $expires = new \DateTimeImmutable((string) $tr['expires_at']);
                $diff    = $now->diff($expires);
                $diasRestantes = $diff->invert ? 0 : (int) $diff->days;
                $trialInfo = [
                    'expires_at'     => (string) $tr['expires_at'],
                    'dias_restantes' => $diasRestantes,
                    'vcpu'           => (int) $tr['vcpu'],
                    'ram_mb'         => (int) $tr['ram_mb'],
                    'disco_gb'       => (int) $tr['disco_gb'],
                ];
                // Expirar lazy se já passou
                if ($diasRestantes === 0) {
                    $pdo->prepare("UPDATE client_trials SET status='expired' WHERE client_id=:c AND status='active'")
                        ->execute([':c' => $id]);
                    $trialInfo = null;
                }
            }
        } catch (\Throwable) {}

        // Plano exclusivo pendente (para clientes gerenciados sem assinatura)
        $planoExclusivo = null;
        if ($assinatura === null) {
            try {
                $stmtPe = $pdo->prepare("SELECT id, name, price_monthly, cpu, ram, storage FROM plans WHERE status = 'active' AND client_id = :c LIMIT 1");
                $stmtPe->execute([':c' => $id]);
                $planoExclusivo = $stmtPe->fetch() ?: null;
            } catch (\Throwable) {}
        }

        $html = View::renderizar(__DIR__ . '/../../Views/cliente/painel.php', [
            'cliente'        => is_array($c) ? $c : ['name' => 'Cliente', 'email' => ''],
            'notificacoes'   => $notifs,
            'totalVps'       => $totalVps,
            'vpsRunning'     => $vpsRunning,
            'ticketsAbertos' => $ticketsAbertos,
            'assinatura'     => $assinatura,
            'onboardingDone' => $onboardingDone,
            'trialInfo'      => $trialInfo,
            'planoExclusivo' => $planoExclusivo,
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
