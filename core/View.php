<?php

declare(strict_types=1);

namespace LRV\Core;

final class View
{
    public static function renderizar(string $arquivoAbsoluto, array $dados = []): string
    {
        if (!is_file($arquivoAbsoluto)) {
            throw new \RuntimeException('View não encontrada.');
        }

        extract($dados, EXTR_SKIP);

        ob_start();
        require $arquivoAbsoluto;
        return (string) ob_get_clean();
    }

    public static function e(string $texto): string
    {
        return htmlspecialchars($texto, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
