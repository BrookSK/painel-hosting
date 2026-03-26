<?php

declare(strict_types=1);

namespace LRV\Core;

final class Bootstrap
{
    public static function iniciar(): void
    {
        $https = false;
        if (PHP_SAPI !== 'cli') {
            $httpsSrv = (string) ($_SERVER['HTTPS'] ?? '');
            if ($httpsSrv !== '' && strtolower($httpsSrv) !== 'off') {
                $https = true;
            }
            $xfp = strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));
            if ($xfp === 'https') {
                $https = true;
            }
            $scheme = strtolower((string) ($_SERVER['REQUEST_SCHEME'] ?? ''));
            if ($scheme === 'https') {
                $https = true;
            }

            // Forçar HTTPS se configurado
            try {
                $forceHttps = \LRV\Core\Settings::obter('app.force_https', '0');
                if ($forceHttps === '1' && !$https) {
                    $host = (string) ($_SERVER['HTTP_HOST'] ?? '');
                    $uri  = (string) ($_SERVER['REQUEST_URI'] ?? '/');
                    if ($host !== '') {
                        header('Location: https://' . $host . $uri, true, 301);
                        exit;
                    }
                }
            } catch (\Throwable $e) {
            }
        }

        if (PHP_SAPI !== 'cli' && session_status() !== PHP_SESSION_ACTIVE) {
            @ini_set('session.use_strict_mode', '1');
            @ini_set('session.use_only_cookies', '1');
            @ini_set('session.use_trans_sid', '0');
            @ini_set('session.cookie_httponly', '1');

            session_set_cookie_params([
                'lifetime' => 86400,
                'path' => '/',
                'secure' => $https,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);

            @ini_set('session.gc_maxlifetime', '86400');

            session_start();
        }

        $idioma = 'pt-BR';
        if (PHP_SAPI !== 'cli') {
            $candidato = (string) ($_GET['lang'] ?? ($_COOKIE['lang'] ?? ''));
            $candidato = trim($candidato);
            if ($candidato !== '' && preg_match('/^[a-z]{2}-[A-Z]{2}$/', $candidato) === 1) {
                $arquivo = __DIR__ . '/../app/Idiomas/' . $candidato . '.php';
                if (is_file($arquivo)) {
                    $idioma = $candidato;
                    if (isset($_GET['lang'])) {
                        setcookie('lang', $idioma, [
                            'expires' => time() + 31536000,
                            'path' => '/',
                            'secure' => $https,
                            'httponly' => true,
                            'samesite' => 'Lax',
                        ]);
                    }
                }
            }
        }

        I18n::definirIdioma($idioma);

        // Moeda independente do idioma
        $moeda = 'BRL';
        if (PHP_SAPI !== 'cli') {
            $moedaCand = strtoupper(trim((string) ($_GET['currency'] ?? ($_COOKIE['currency'] ?? ''))));
            if (in_array($moedaCand, ['BRL', 'USD'], true)) {
                $moeda = $moedaCand;
                if (isset($_GET['currency'])) {
                    setcookie('currency', $moeda, [
                        'expires' => time() + 31536000,
                        'path' => '/',
                        'secure' => $https,
                        'httponly' => false,
                        'samesite' => 'Lax',
                    ]);
                }
            }
        }
        I18n::definirMoeda($moeda);

        date_default_timezone_set('America/Sao_Paulo');

        ini_set('display_errors', '0');
        error_reporting(E_ALL);

        set_exception_handler(static function (\Throwable $e): void {
            AppLogger::erro('Uncaught exception: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => substr($e->getTraceAsString(), 0, 2000),
            ]);
            $errorId = 0;
            try {
                $errorId = \LRV\App\Services\Errors\ErrorLogService::registrar(
                    500,
                    'uncaught_exception',
                    $e->getMessage(),
                    $e,
                );
            } catch (\Throwable) {}
            http_response_code(500);
            header('Content-Type: text/html; charset=utf-8');
            try {
                echo \LRV\Core\View::renderizar(__DIR__ . '/../app/Views/erros/erro.php', [
                    'code'    => 500,
                    'errorId' => $errorId > 0 ? $errorId : null,
                ]);
            } catch (\Throwable) {
                echo I18n::t('geral.erro_interno');
            }
        });

        try {
            InicializadorSistema::garantirDadosBase();
        } catch (\Throwable $e) {
        }
    }
}
