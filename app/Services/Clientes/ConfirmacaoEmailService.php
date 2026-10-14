<?php

declare(strict_types=1);

namespace LRV\App\Services\Clientes;

use LRV\Core\BancoDeDados;
use LRV\Core\Settings;

/**
 * Confirmação de e-mail de contas criadas pelo próprio cliente.
 * O token em claro só vai no e-mail; no banco fica o SHA-256.
 */
final class ConfirmacaoEmailService
{
    private const VALIDADE = 172800; // 48h
    private const INTERVALO_REENVIO = 60; // segundos entre reenvios

    public static function exigida(): bool
    {
        return (int) Settings::obter('signup.exigir_confirmacao_email', 1) === 1;
    }

    /** Cliente precisa confirmar o e-mail antes de usar o painel? */
    public static function pendente(int $clienteId): bool
    {
        if ($clienteId <= 0 || !self::exigida()) {
            return false;
        }
        try {
            $s = BancoDeDados::pdo()->prepare('SELECT email_verification_pending FROM clients WHERE id = :id');
            $s->execute([':id' => $clienteId]);
            return (int) $s->fetchColumn() === 1;
        } catch (\Throwable) {
            return false; // banco sem a migration: não bloqueia
        }
    }

    /** Marca a conta como pendente e envia o e-mail. Usado no cadastro. */
    public function iniciar(int $clienteId): void
    {
        if (!self::exigida()) {
            return;
        }
        try {
            BancoDeDados::pdo()->prepare('UPDATE clients SET email_verification_pending = 1 WHERE id = :id')
                ->execute([':id' => $clienteId]);
        } catch (\Throwable) {
            return;
        }
        $this->enviar($clienteId, true);
    }

    /** Gera um novo link e envia. Retorna false se pediu de novo cedo demais. */
    public function enviar(int $clienteId, bool $ignorarIntervalo = false): bool
    {
        $pdo = BancoDeDados::pdo();
        $s = $pdo->prepare('SELECT email, name, email_verify_sent_at FROM clients WHERE id = :id AND email_verification_pending = 1');
        $s->execute([':id' => $clienteId]);
        $c = $s->fetch();
        if (!is_array($c)) {
            return false;
        }
        $ultimo = strtotime((string) ($c['email_verify_sent_at'] ?? '')) ?: 0;
        if (!$ignorarIntervalo && time() - $ultimo < self::INTERVALO_REENVIO) {
            return false;
        }

        $token = bin2hex(random_bytes(32));
        $pdo->prepare('UPDATE clients SET email_verify_token_hash = :h, email_verify_expires_at = :e, email_verify_sent_at = :s WHERE id = :id')
            ->execute([
                ':h' => hash('sha256', $token),
                ':e' => date('Y-m-d H:i:s', time() + self::VALIDADE),
                ':s' => date('Y-m-d H:i:s'),
                ':id' => $clienteId,
            ]);

        try {
            $link = \LRV\Core\ConfiguracoesSistema::appUrlBase() . '/cliente/confirmar-email?token=' . $token;
            $html = \LRV\App\Services\Email\EmailTemplate::renderizar(
                'Confirme seu e-mail',
                '<p style="margin:0 0 12px;">Olá' . ($c['name'] ? ', ' . htmlspecialchars((string) $c['name'], ENT_QUOTES) : '') . '!</p>'
                . '<p style="margin:0 0 12px;">Para ativar sua conta, confirme seu e-mail clicando no botão abaixo. O link vale por 48 horas.</p>',
                'Confirmar e-mail',
                $link,
                'Se você não criou esta conta, ignore este e-mail.',
            );
            (new \LRV\App\Services\Email\SmtpMailer())->enviar((string) $c['email'], 'Confirme seu e-mail', $html, true);
        } catch (\Throwable $e) {
            error_log('[confirmacao-email] falha ao enviar para cliente #' . $clienteId . ': ' . $e->getMessage());
        }
        return true;
    }

    /** Confirma pelo token do link. Retorna o ID do cliente ou null. */
    public function confirmar(string $token): ?int
    {
        if (preg_match('/^[a-f0-9]{64}$/', $token) !== 1) {
            return null;
        }
        $pdo = BancoDeDados::pdo();
        $s = $pdo->prepare('SELECT id FROM clients WHERE email_verify_token_hash = :h AND email_verify_expires_at > :n LIMIT 1');
        $s->execute([':h' => hash('sha256', $token), ':n' => date('Y-m-d H:i:s')]);
        $id = $s->fetchColumn();
        if ($id === false) {
            return null;
        }
        $pdo->prepare('UPDATE clients SET email_verification_pending = 0, email_verified_at = :v, email_verify_token_hash = NULL, email_verify_expires_at = NULL WHERE id = :id')
            ->execute([':v' => date('Y-m-d H:i:s'), ':id' => (int) $id]);
        return (int) $id;
    }
}
