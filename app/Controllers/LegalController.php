<?php

declare(strict_types=1);

namespace LRV\App\Controllers;

use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\SistemaConfig;
use LRV\Core\View;

final class LegalController
{
    public function termos(Requisicao $req): Resposta
    {
        $html = View::renderizar(__DIR__ . '/../Views/termos.php', [
            'conteudo' => SistemaConfig::termsHtml(),
            'nome_sistema' => SistemaConfig::nome(),
        ]);
        return Resposta::html($html);
    }

    public function privacidade(Requisicao $req): Resposta
    {
        $html = View::renderizar(__DIR__ . '/../Views/privacidade.php', [
            'conteudo' => SistemaConfig::privacyHtml(),
            'nome_sistema' => SistemaConfig::nome(),
        ]);
        return Resposta::html($html);
    }
}
