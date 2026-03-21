<?php
declare(strict_types=1);

namespace LRV\App\Controllers\Cliente;

use LRV\Core\Auth;
use LRV\Core\BancoDeDados;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\Totp;
use LRV\Core\View;

final class DoisFatoresController
{
    public function configurar(Requisicao $req): Resposta
    {
        $id = Auth::clienteId();
        if ($id === null) return Resposta::redirecionar('/cliente/entrar');

        $pdo = BancoDeDados::pdo();
        $s = $pdo->prepare('SELECT email FROM clients WHERE id = :id LIMIT 1');
        $s->execute([':id' => $id]);
        $cli = $s->fetch();
        if (!is_array($cli)) return Resposta::redirecionar('/cliente/entrar');

        $st = $pdo->prepare('SELECT secret, enabled FROM client_totp WHERE client_id = :id LIMIT 1');
        $st->execute([':id' => $id]);
        $totp = $st->fetch();

        $secret  = '';
        $enabled = false;

        if (is_array($totp)) {
            $secret  = (string)($totp['secret'] ?? '');
            $enabled = (bool)($totp['enabled'] ?? false);
        }

        if ($secret === '') {
            $secret = Totp::gerarSecret();
            $agora  = date('Y-m-d H:i:s');
            $pdo->prepare(
                'INSERT INTO client_totp (client_id, secret, enabled, created_at, updated_at)
                 VALUES (:u,:s,0,:c,:c)
                 ON DUPLICATE KEY UPDATE secret = IF(enabled=0, :s2, secret), updated_at = :c2'
            )->execute([':u' => $id, ':s' => $secret, ':s2' => $secret, ':c' => $agora, ':c2' => $agora]);
        }

        $qrUrl = Totp::gerarQrCodeUrl($secret, (string)($cli['email'] ?? ''));

        return Resposta::html(View::renderizar(__DIR__ . '/../../Views/cliente/2fa-configurar.php', [
            'secret'  => $secret,
            'qr_url'  => $qrUrl,
            'enabled' => $enabled,
            'erro'    => '',
            'ok'      => '',
        ]));
    }

    public function ativar(Requisicao $req): Resposta
    {
        $id = Auth::clienteId();
        if ($id === null) return Resposta::redirecionar('/cliente/entrar');

        $codigo = trim((string)($req->post['codigo'] ?? ''));

        $pdo = BancoDeDados::pdo();
        $st  = $pdo->prepare('SELECT secret, enabled FROM client_totp WHERE client_id = :id LIMIT 1');
        $st->execute([':id' => $id]);
        $totp = $st->fetch();

        if (!is_array($totp)) return Resposta::redirecionar('/cliente/2fa/configurar');

        $secret = (string)($totp['secret'] ?? '');

        if (!Totp::verificar($secret, $codigo)) {
            $s2 = $pdo->prepare('SELECT email FROM clients WHERE id = :id LIMIT 1');
            $s2->execute([':id' => $id]);
            $cli = $s2->fetch();
            $qrUrl = Totp::gerarQrCodeUrl($secret, is_array($cli) ? (string)($cli['email'] ?? '') : '');

            return Resposta::html(View::renderizar(__DIR__ . '/../../Views/cliente/2fa-configurar.php', [
                'secret'  => $secret,
                'qr_url'  => $qrUrl,
                'enabled' => false,
                'erro'    => 'Código inválido. Verifique o horário do dispositivo e tente novamente.',
                'ok'      => '',
            ]), 422);
        }

        $pdo->prepare('UPDATE client_totp SET enabled = 1, updated_at = :u WHERE client_id = :id')
            ->execute([':u' => date('Y-m-d H:i:s'), ':id' => $id]);

        $s2 = $pdo->prepare('SELECT email FROM clients WHERE id = :id LIMIT 1');
        $s2->execute([':id' => $id]);
        $cli   = $s2->fetch();
        $qrUrl = Totp::gerarQrCodeUrl($secret, is_array($cli) ? (string)($cli['email'] ?? '') : '');

        return Resposta::html(View::renderizar(__DIR__ . '/../../Views/cliente/2fa-configurar.php', [
            'secret'  => $secret,
            'qr_url'  => $qrUrl,
            'enabled' => true,
            'erro'    => '',
            'ok'      => '2FA ativado com sucesso.',
        ]));
    }

    public function desativar(Requisicao $req): Resposta
    {
        $id = Auth::clienteId();
        if ($id === null) return Resposta::redirecionar('/cliente/entrar');

        $codigo = trim((string)($req->post['codigo'] ?? ''));

        $pdo = BancoDeDados::pdo();
        $st  = $pdo->prepare('SELECT secret FROM client_totp WHERE client_id = :id AND enabled = 1 LIMIT 1');
        $st->execute([':id' => $id]);
        $totp = $st->fetch();

        if (!is_array($totp) || !Totp::verificar((string)($totp['secret'] ?? ''), $codigo)) {
            return Resposta::redirecionar('/cliente/2fa/configurar');
        }

        $pdo->prepare('UPDATE client_totp SET enabled = 0, updated_at = :u WHERE client_id = :id')
            ->execute([':u' => date('Y-m-d H:i:s'), ':id' => $id]);

        return Resposta::redirecionar('/cliente/2fa/configurar');
    }

    public function formularioVerificar(Requisicao $req): Resposta
    {
        return Resposta::html(View::renderizar(__DIR__ . '/../../Views/cliente/2fa-verificar.php', ['erro' => '']));
    }

    public function verificar(Requisicao $req): Resposta
    {
        $pendingId = (int)($_SESSION['2fa_pending_cliente_id'] ?? 0);
        if ($pendingId <= 0) return Resposta::redirecionar('/cliente/entrar');

        $codigo = trim((string)($req->post['codigo'] ?? ''));

        $pdo = BancoDeDados::pdo();
        $st  = $pdo->prepare('SELECT secret FROM client_totp WHERE client_id = :id AND enabled = 1 LIMIT 1');
        $st->execute([':id' => $pendingId]);
        $totp = $st->fetch();

        if (!is_array($totp) || !Totp::verificar((string)($totp['secret'] ?? ''), $codigo)) {
            $html = View::renderizar(__DIR__ . '/../../Views/cliente/2fa-verificar.php', ['erro' => 'Código inválido.']);
            return Resposta::html($html, 422);
        }

        unset($_SESSION['2fa_pending_cliente_id']);
        $_SESSION['auth_cliente_id'] = $pendingId;

        return Resposta::redirecionar('/cliente/painel');
    }
}
