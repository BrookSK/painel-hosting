<?php

declare(strict_types=1);

namespace LRV\App\Services\Errors;

use LRV\App\Services\Email\EmailTemplate;
use LRV\App\Services\Email\SmtpMailer;
use LRV\Core\AppLogger;
use LRV\Core\Auth;
use LRV\Core\BancoDeDados;
use LRV\Core\ConfiguracoesSistema;

final class ErrorLogService
{
    /**
     * Registra um erro no banco e notifica a equipe técnica.
     *
     * @param int             $httpCode  Código HTTP (404, 500, 503…)
     * @param string          $errorType Tipo: 'exception', 'not_found', 'csrf', 'rate_limit', etc.
     * @param string          $message   Mensagem do erro
     * @param \Throwable|null $throwable Exceção original (opcional)
     * @param array           $context   Dados extras
     */
    public static function registrar(
        int $httpCode,
        string $errorType,
        string $message,
        ?\Throwable $throwable = null,
        array $context = [],
    ): int {
        $url       = self::urlAtual();
        $method    = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        $ip        = self::ip();
        $ua        = substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500);
        $userType  = null;
        $userId    = null;

        try {
            if (Auth::equipeId() !== null) {
                $userType = 'team';
                $userId   = Auth::equipeId();
            } elseif (Auth::clienteId() !== null) {
                $userType = 'client';
                $userId   = Auth::clienteId();
            } else {
                $userType = 'guest';
            }
        } catch (\Throwable) {}

        $file  = $throwable ? $throwable->getFile() : null;
        $line  = $throwable ? $throwable->getLine() : null;
        $trace = $throwable ? substr($throwable->getTraceAsString(), 0, 8000) : null;

        if ($context !== []) {
            $contextJson = json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } else {
            $contextJson = null;
        }

        $id = 0;
        try {
            $pdo  = BancoDeDados::pdo();
            $stmt = $pdo->prepare(
                'INSERT INTO system_errors
                 (http_code, error_type, message, url, method, ip_address, user_agent,
                  user_type, user_id, file, line, trace, context_json, notified, created_at)
                 VALUES (:code,:type,:msg,:url,:meth,:ip,:ua,:ut,:uid,:file,:line,:trace,:ctx,0,:now)'
            );
            $stmt->execute([
                ':code'  => $httpCode,
                ':type'  => $errorType,
                ':msg'   => substr($message, 0, 65535),
                ':url'   => substr($url, 0, 1000),
                ':meth'  => $method,
                ':ip'    => $ip,
                ':ua'    => $ua !== '' ? $ua : null,
                ':ut'    => $userType,
                ':uid'   => $userId,
                ':file'  => $file ? substr($file, 0, 500) : null,
                ':line'  => $line,
                ':trace' => $trace,
                ':ctx'   => $contextJson,
                ':now'   => date('Y-m-d H:i:s'),
            ]);
            $id = (int) $pdo->lastInsertId();
        } catch (\Throwable $e) {
            AppLogger::erro('ErrorLogService: falha ao salvar erro no banco: ' . $e->getMessage());
        }

        // Notificar apenas erros 5xx (não 404 para não gerar spam)
        if ($httpCode >= 500 && $id > 0) {
            self::notificarEquipe($id, $httpCode, $errorType, $message, $url, $method);
        }

        return $id;
    }

    private static function notificarEquipe(
        int $id,
        int $httpCode,
        string $errorType,
        string $message,
        string $url,
        string $method,
    ): void {
        try {
            $pdo = BancoDeDados::pdo();

            // Buscar todos os membros com roles: superadmin, admin, devops, dev
            $stmt = $pdo->query(
                "SELECT id, name, email FROM users
                 WHERE role IN ('superadmin','admin','devops','dev') AND active = 1
                 LIMIT 50"
            );
            $membros = $stmt ? ($stmt->fetchAll() ?: []) : [];

            $appUrl  = rtrim((string) ConfiguracoesSistema::appUrlBase(), '/');
            $linkVer = $appUrl . '/equipe/erros/ver?id=' . $id;

            $titulo  = "[{$httpCode}] Erro {$errorType} — {$method} {$url}";
            $corpo   = '<p style="margin:0 0 12px;">Erro <strong>#' . $id . '</strong> registrado no sistema.</p>'
                     . '<table style="width:100%;border-collapse:collapse;margin:16px 0;">'
                     . '<tr><td style="padding:8px 12px;border-bottom:1px solid #e2e8f0;font-weight:600;color:#64748b;width:120px;">Código HTTP</td>'
                     . '<td style="padding:8px 12px;border-bottom:1px solid #e2e8f0;color:#0f172a;">' . $httpCode . '</td></tr>'
                     . '<tr><td style="padding:8px 12px;border-bottom:1px solid #e2e8f0;font-weight:600;color:#64748b;">Tipo</td>'
                     . '<td style="padding:8px 12px;border-bottom:1px solid #e2e8f0;color:#0f172a;">' . htmlspecialchars($errorType, ENT_QUOTES, 'UTF-8') . '</td></tr>'
                     . '<tr><td style="padding:8px 12px;border-bottom:1px solid #e2e8f0;font-weight:600;color:#64748b;">Método</td>'
                     . '<td style="padding:8px 12px;border-bottom:1px solid #e2e8f0;color:#0f172a;">' . htmlspecialchars($method, ENT_QUOTES, 'UTF-8') . '</td></tr>'
                     . '<tr><td style="padding:8px 12px;border-bottom:1px solid #e2e8f0;font-weight:600;color:#64748b;">URL</td>'
                     . '<td style="padding:8px 12px;border-bottom:1px solid #e2e8f0;color:#0f172a;word-break:break-all;">' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '</td></tr>'
                     . '<tr><td style="padding:8px 12px;font-weight:600;color:#64748b;">Mensagem</td>'
                     . '<td style="padding:8px 12px;color:#0f172a;">' . htmlspecialchars(substr($message, 0, 300), ENT_QUOTES, 'UTF-8') . '</td></tr>'
                     . '</table>';

            $htmlEmail = EmailTemplate::renderizar(
                'Erro no Sistema',
                $corpo,
                'Ver Detalhes',
                $linkVer,
            );

            foreach ($membros as $m) {
                $email = trim((string) ($m['email'] ?? ''));
                if ($email === '') continue;
                try {
                    (new SmtpMailer())->enviar($email, '[LRV] ' . $titulo, $htmlEmail, true);
                } catch (\Throwable) {}
            }

            // Marcar como notificado
            if ($membros !== []) {
                $pdo->prepare('UPDATE system_errors SET notified = 1 WHERE id = :id')
                    ->execute([':id' => $id]);
            }
        } catch (\Throwable $e) {
            AppLogger::erro('ErrorLogService: falha ao notificar equipe: ' . $e->getMessage());
        }
    }

    private static function urlAtual(): string
    {
        $host   = (string) ($_SERVER['HTTP_HOST'] ?? '');
        $uri    = (string) ($_SERVER['REQUEST_URI'] ?? '/');
        $scheme = 'http';
        if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
            $scheme = 'https';
        }
        return $host !== '' ? "{$scheme}://{$host}{$uri}" : $uri;
    }

    private static function ip(): ?string
    {
        $xff = trim((string) ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? ''));
        if ($xff !== '') {
            $partes = array_map('trim', explode(',', $xff));
            $ip = (string) ($partes[0] ?? '');
            if ($ip !== '') return $ip;
        }
        $ip = trim((string) ($_SERVER['REMOTE_ADDR'] ?? ''));
        return $ip !== '' ? $ip : null;
    }
}
