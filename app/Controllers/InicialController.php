<?php

declare(strict_types=1);

namespace LRV\App\Controllers;

use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\Settings;
use LRV\Core\View;

final class InicialController
{
    public function index(Requisicao $req): Resposta
    {
        $trialAtivo = (int) Settings::obter('trial.enabled', 0) === 1;

        // Buscar planos públicos
        $planos = [];
        try {
            $pdo   = BancoDeDados::pdo();
            $stmt  = $pdo->query(
                "SELECT id, name, price_monthly AS price, specs_json, description, is_featured,
                        cpu, ram, storage, support_channels
                 FROM plans WHERE status = 'active' ORDER BY price_monthly ASC LIMIT 6"
            );
            $planos = $stmt ? ($stmt->fetchAll() ?: []) : [];

            // Gerar badge a partir de is_featured
            foreach ($planos as &$_pp) {
                $_pp['badge'] = ((int)($_pp['is_featured'] ?? 0) === 1) ? 'POPULAR' : '';
            }
            unset($_pp);

            // Buscar addons para cada plano
            if (!empty($planos)) {
                $ids = implode(',', array_map('intval', array_column($planos, 'id')));
                $stmtA = $pdo->query("SELECT * FROM plan_addons WHERE plan_id IN ($ids) AND active = 1 ORDER BY plan_id, sort_order ASC");
                $allAddons = $stmtA ? ($stmtA->fetchAll() ?: []) : [];
                $addonsByPlan = [];
                foreach ($allAddons as $a) {
                    $addonsByPlan[(int)$a['plan_id']][] = $a;
                }
                foreach ($planos as &$_p) {
                    $_p['addons'] = $addonsByPlan[(int)$_p['id']] ?? [];
                }
                unset($_p);
            }
        } catch (\Throwable) {}

        $html = View::renderizar(__DIR__ . '/../Views/inicial.php', [
            'equipe_logada' => \LRV\Core\Auth::equipeId() !== null,
            'trial_ativo'   => $trialAtivo,
            'trial_dias'    => (int) Settings::obter('trial.dias', 7),
            'trial_label'   => (string) Settings::obter('trial.label_cta', 'Testar grátis'),
            'trial_desc'    => (string) Settings::obter('trial.descricao', ''),
            'planos'        => $planos,
        ]);
        return Resposta::html($html);
    }

    public function infraestrutura(Requisicao $req): Resposta
    {
        $trialAtivo = (int) Settings::obter('trial.enabled', 0) === 1;

        $planos = [];
        try {
            $pdo  = BancoDeDados::pdo();
            $stmt = $pdo->query(
                "SELECT id, name, price_monthly AS price, specs_json, description, is_featured,
                        cpu, ram, storage
                 FROM plans WHERE status = 'active' ORDER BY price_monthly ASC LIMIT 6"
            );
            $planos = $stmt ? ($stmt->fetchAll() ?: []) : [];
            foreach ($planos as &$_pp) {
                $_pp['badge'] = ((int)($_pp['is_featured'] ?? 0) === 1) ? 'POPULAR' : '';
            }
            unset($_pp);
        } catch (\Throwable) {}

        $html = View::renderizar(__DIR__ . '/../Views/infraestrutura.php', [
            'equipe_logada' => \LRV\Core\Auth::equipeId() !== null,
            'trial_ativo'   => $trialAtivo,
            'trial_dias'    => (int) Settings::obter('trial.dias', 7),
            'trial_label'   => (string) Settings::obter('trial.label_cta', 'Testar grátis'),
            'trial_desc'    => (string) Settings::obter('trial.descricao', ''),
            'planos'        => $planos,
        ]);
        return Resposta::html($html);
    }

    public function contato(Requisicao $req): Resposta
    {
        $html = View::renderizar(__DIR__ . '/../Views/contato.php', [
            'erro' => '',
            'ok' => '',
            'form' => ['name' => '', 'email' => '', 'subject' => '', 'message' => ''],
        ]);
        return Resposta::html($html);
    }

    public function enviarContato(Requisicao $req): Resposta
    {
        $in = $req->input();
        $name = $in->postString('name', 190, true);
        $email = $in->postEmail('email', 190, true);
        $subject = $in->postString('subject', 190, true);
        $message = $in->postString('message', 5000, true);

        if ($in->temErros() || $name === '' || $email === '' || $subject === '' || $message === '') {
            $html = View::renderizar(__DIR__ . '/../Views/contato.php', [
                'erro' => $in->temErros() ? $in->primeiroErro() : 'Preencha todos os campos.',
                'ok' => '',
                'form' => compact('name', 'email', 'subject', 'message'),
            ]);
            return Resposta::html($html, 422);
        }

        $ip = trim((string) ($req->headers['x-forwarded-for'] ?? ''));
        if ($ip !== '') {
            $partes = array_map('trim', explode(',', $ip));
            $ip = (string) ($partes[0] ?? '');
        }
        if ($ip === '') {
            $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
        }

        try {
            $pdo = BancoDeDados::pdo();
            $ins = $pdo->prepare('INSERT INTO contact_messages (name, email, subject, message, ip_address, created_at) VALUES (:n,:e,:s,:m,:ip,:c)');
            $ins->execute([':n' => $name, ':e' => $email, ':s' => $subject, ':m' => $message, ':ip' => $ip ?: null, ':c' => date('Y-m-d H:i:s')]);
        } catch (\Throwable $e) {
        }

        // Notificar admin
        try {
            $emailAdmin = trim((string) Settings::obter('alertas.email_admin', ''));
            if ($emailAdmin !== '') {
                $corpo = '<p style="margin:0 0 12px;"><strong>De:</strong> '
                       . htmlspecialchars($name, ENT_QUOTES, 'UTF-8')
                       . ' &lt;' . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . '&gt;</p>'
                       . '<p style="margin:0 0 12px;"><strong>Assunto:</strong> '
                       . htmlspecialchars($subject, ENT_QUOTES, 'UTF-8') . '</p>'
                       . '<div style="background:#f8fafc;border-left:3px solid #4F46E5;padding:14px 16px;border-radius:0 8px 8px 0;margin:16px 0;">'
                       . nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'))
                       . '</div>';
                $html = \LRV\App\Services\Email\EmailTemplate::renderizar(
                    'Nova Mensagem de Contato',
                    $corpo,
                );
                (new \LRV\App\Services\Email\SmtpMailer())->enviar(
                    $emailAdmin,
                    '[LRV] Contato: ' . $subject,
                    $html,
                    true
                );
            }
        } catch (\Throwable $e) {
        }

        $html = View::renderizar(__DIR__ . '/../Views/contato.php', [
            'erro' => '',
            'ok' => 'Mensagem enviada com sucesso. Entraremos em contato em breve.',
            'form' => ['name' => '', 'email' => '', 'subject' => '', 'message' => ''],
        ]);
        return Resposta::html($html);
    }
}
