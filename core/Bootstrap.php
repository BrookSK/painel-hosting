<?php

declare(strict_types=1);

namespace LRV\Core;

final class Bootstrap
{
    public static function iniciar(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
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
                            'samesite' => 'Lax',
                        ]);
                    }
                }
            }
        }

        I18n::definirIdioma($idioma);

        date_default_timezone_set('America/Sao_Paulo');

        ini_set('display_errors', '0');
        error_reporting(E_ALL);

        set_exception_handler(static function (\Throwable $e): void {
            http_response_code(500);
            header('Content-Type: text/html; charset=utf-8');
            echo I18n::t('geral.erro_interno');
        });

        try {
            InicializadorSistema::garantirDadosBase();
        } catch (\Throwable $e) {
        }
    }
}
