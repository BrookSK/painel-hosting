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

        date_default_timezone_set('America/Sao_Paulo');

        ini_set('display_errors', '0');
        error_reporting(E_ALL);

        set_exception_handler(static function (\Throwable $e): void {
            http_response_code(500);
            header('Content-Type: text/html; charset=utf-8');
            echo 'Erro interno.';
        });

        try {
            InicializadorSistema::garantirDadosBase();
        } catch (\Throwable $e) {
        }
    }
}
