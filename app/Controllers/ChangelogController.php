<?php

declare(strict_types=1);

namespace LRV\App\Controllers;

use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\SistemaConfig;
use LRV\Core\View;

final class ChangelogController
{
    public function index(Requisicao $req): Resposta
    {
        $arquivo = dirname(__DIR__, 2) . '/CHANGELOG.md';
        $md = is_file($arquivo) ? (string) file_get_contents($arquivo) : '# Changelog\n\nNenhum changelog disponível.';
        $html = View::renderizar(__DIR__ . '/../Views/changelog.php', [
            'conteudo_html' => self::mdParaHtml($md),
            'nome_sistema'  => SistemaConfig::nome(),
        ]);
        return Resposta::html($html);
    }

    private static function mdParaHtml(string $md): string
    {
        $linhas = explode("\n", $md);
        $html = '';
        $emLista = false;

        foreach ($linhas as $linha) {
            // Fechar lista pendente se a linha não for item
            if ($emLista && !preg_match('/^- /', $linha)) {
                $html .= '</ul>';
                $emLista = false;
            }

            if (preg_match('/^### (.+)$/', $linha, $m)) {
                $html .= '<h3>' . htmlspecialchars($m[1], ENT_QUOTES) . '</h3>';
            } elseif (preg_match('/^## (.+)$/', $linha, $m)) {
                $html .= '<h2>' . htmlspecialchars($m[1], ENT_QUOTES) . '</h2>';
            } elseif (preg_match('/^# (.+)$/', $linha, $m)) {
                $html .= '<h1>' . htmlspecialchars($m[1], ENT_QUOTES) . '</h1>';
            } elseif (preg_match('/^- (.+)$/', $linha, $m)) {
                if (!$emLista) {
                    $html .= '<ul>';
                    $emLista = true;
                }
                $html .= '<li>' . self::inlineHtml($m[1]) . '</li>';
            } elseif (trim($linha) === '') {
                $html .= '';
            } else {
                $html .= '<p>' . self::inlineHtml($linha) . '</p>';
            }
        }

        if ($emLista) {
            $html .= '</ul>';
        }

        return $html;
    }

    private static function inlineHtml(string $texto): string
    {
        $texto = htmlspecialchars($texto, ENT_QUOTES);
        // **negrito**
        $texto = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $texto) ?? $texto;
        // `código`
        $texto = preg_replace('/`(.+?)`/', '<code>$1</code>', $texto) ?? $texto;
        // [link](url)
        $texto = preg_replace('/\[([^\]]+)\]\((https?:\/\/[^\)]+)\)/', '<a href="$2" target="_blank" rel="noopener">$1</a>', $texto) ?? $texto;
        return $texto;
    }
}
