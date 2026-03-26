<?php

declare(strict_types=1);

namespace LRV\App\Services\Email;

use LRV\Core\SistemaConfig;
use LRV\Core\ConfiguracoesSistema;

/**
 * Template HTML para e-mails do sistema.
 * Gera e-mails estilizados com a identidade visual da plataforma.
 */
final class EmailTemplate
{
    /**
     * Renderiza um e-mail completo com layout estilizado.
     *
     * @param string      $titulo   Título principal do e-mail
     * @param string      $corpo    Conteúdo HTML do corpo (pode conter <p>, <a>, etc.)
     * @param string|null $botaoTexto Texto do botão CTA (opcional)
     * @param string|null $botaoUrl   URL do botão CTA (opcional)
     * @param string|null $rodape    Texto extra no rodapé (opcional)
     */
    public static function renderizar(
        string $titulo,
        string $corpo,
        ?string $botaoTexto = null,
        ?string $botaoUrl = null,
        ?string $rodape = null,
    ): string {
        $appNome = htmlspecialchars(SistemaConfig::nome(), ENT_QUOTES, 'UTF-8');
        $logoUrl = SistemaConfig::logoUrl();
        $copyright = htmlspecialchars(SistemaConfig::copyrightText(), ENT_QUOTES, 'UTF-8');
        $appUrl = rtrim(ConfiguracoesSistema::appUrlBase(), '/');

        $logoHtml = '';
        if ($logoUrl !== '') {
            $logoUrl = htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8');
            $logoHtml = '<img src="' . $logoUrl . '" alt="' . $appNome . '" style="max-height:40px;max-width:180px;" />';
        } else {
            $logoHtml = '<span style="font-size:22px;font-weight:800;color:#ffffff;letter-spacing:-0.02em;">' . $appNome . '</span>';
        }

        $botaoHtml = '';
        if ($botaoTexto !== null && $botaoUrl !== null) {
            $botaoUrl = htmlspecialchars($botaoUrl, ENT_QUOTES, 'UTF-8');
            $botaoTexto = htmlspecialchars($botaoTexto, ENT_QUOTES, 'UTF-8');
            $botaoHtml = '<div style="text-align:center;margin:28px 0 8px;">'
                . '<a href="' . $botaoUrl . '" style="display:inline-block;padding:13px 32px;'
                . 'background:linear-gradient(135deg,#4F46E5,#7C3AED);color:#ffffff;'
                . 'font-weight:700;font-size:15px;border-radius:12px;text-decoration:none;'
                . 'font-family:system-ui,-apple-system,sans-serif;">'
                . $botaoTexto . '</a></div>';
        }

        $rodapeExtra = '';
        if ($rodape !== null && $rodape !== '') {
            $rodapeExtra = '<p style="margin:0 0 8px;font-size:12px;color:#94a3b8;">'
                . htmlspecialchars($rodape, ENT_QUOTES, 'UTF-8') . '</p>';
        }

        $titulo = htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8');

        return <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"></head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:system-ui,-apple-system,'Segoe UI',Roboto,sans-serif;">
<div style="max-width:600px;margin:0 auto;padding:20px 16px;">

<!-- Header -->
<div style="background:linear-gradient(135deg,#0B1C3D,#4F46E5,#7C3AED);border-radius:16px 16px 0 0;padding:28px 32px;text-align:center;">
{$logoHtml}
</div>

<!-- Body -->
<div style="background:#ffffff;padding:32px;border-left:1px solid #e2e8f0;border-right:1px solid #e2e8f0;">
<h1 style="margin:0 0 20px;font-size:22px;font-weight:800;color:#0f172a;letter-spacing:-0.02em;">{$titulo}</h1>
<div style="font-size:15px;line-height:1.7;color:#334155;">
{$corpo}
</div>
{$botaoHtml}
</div>

<!-- Footer -->
<div style="background:#0B1C3D;border-radius:0 0 16px 16px;padding:24px 32px;text-align:center;">
{$rodapeExtra}
<p style="margin:0;font-size:12px;color:#64748b;">{$copyright}</p>
</div>

</div>
</body>
</html>
HTML;
    }

    /**
     * Atalho: converte texto simples em parágrafos HTML.
     */
    public static function textoParaHtml(string $texto): string
    {
        $linhas = explode("\n", $texto);
        $html = '';
        foreach ($linhas as $linha) {
            $linha = trim($linha);
            if ($linha === '') {
                continue;
            }
            $linha = htmlspecialchars($linha, ENT_QUOTES, 'UTF-8');
            // Converter URLs em links clicáveis
            $linha = preg_replace(
                '#(https?://[^\s<]+)#',
                '<a href="$1" style="color:#4F46E5;text-decoration:underline;">$1</a>',
                $linha
            );
            $html .= '<p style="margin:0 0 12px;">' . $linha . '</p>';
        }
        return $html;
    }
}
