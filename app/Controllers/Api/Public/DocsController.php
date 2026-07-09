<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Api\Public;

use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\I18n;
use LRV\Core\View;

/**
 * Controlador de documentação da API Pública.
 * - GET /developers             → Landing page desenvolvedores
 * - GET /developers/api         → Página de documentação
 * - GET /developers/api/swagger → Interface Swagger UI
 * - GET /api/v1/openapi.json    → Spec em JSON
 */
final class DocsController
{
    private function viewPath(string $arquivo): string
    {
        return dirname(__DIR__, 3) . '/Views/developers/' . $arquivo;
    }

    /**
     * GET /developers — Landing page pública para desenvolvedores
     */
    public function landing(Requisicao $req): Resposta
    {
        $html = View::renderizar($this->viewPath('landing.php'), []);
        return Resposta::html($html);
    }

    /**
     * GET /developers/api
     */
    public function index(Requisicao $req): Resposta
    {
        $html = View::renderizar($this->viewPath('api-docs.php'), [
            'titulo' => I18n::t('api_docs.titulo'),
        ]);
        return Resposta::html($html);
    }

    /**
     * GET /developers/api/swagger
     */
    public function swagger(Requisicao $req): Resposta
    {
        $html = View::renderizar($this->viewPath('swagger.php'), []);
        // Setar CSP permissivo nos headers da resposta — o enviar() vê que já existe e não duplica
        // O header_remove no início limpa qualquer CSP do index.php
        if (PHP_SAPI !== 'cli') {
            header_remove('Content-Security-Policy');
        }
        return Resposta::html($html)->comHeaders([
            'Content-Security-Policy' => "default-src 'self'; base-uri 'self'; frame-ancestors 'none'; object-src 'none'; img-src 'self' data: https:; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net; font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net blob:; connect-src 'self' ws: wss:;",
        ]);
    }

    /**
     * GET /developers/api/changelog
     */
    public function changelog(Requisicao $req): Resposta
    {
        $html = View::renderizar($this->viewPath('api-changelog.php'), []);
        return Resposta::html($html);
    }

    /**
     * GET /developers/api/status
     */
    public function statusPage(Requisicao $req): Resposta
    {
        $html = View::renderizar($this->viewPath('api-status.php'), []);
        return Resposta::html($html);
    }

    /**
     * GET /api/v1/openapi.json
     */
    public function openapiJson(Requisicao $req): Resposta
    {
        $yamlPath = dirname(__DIR__, 4) . '/public/api/v1/openapi.yaml';
        if (!is_file($yamlPath)) {
            return Resposta::json(['error' => 'OpenAPI spec not found'], 404);
        }

        $content = file_get_contents($yamlPath);

        if (function_exists('yaml_parse')) {
            $data = yaml_parse($content);
            if (is_array($data)) {
                return Resposta::json($data);
            }
        }

        // Fallback: servir como YAML (a maioria dos clientes aceita)
        return Resposta::texto($content)->comHeaders([
            'Content-Type' => 'text/yaml; charset=utf-8',
        ]);
    }

    /**
     * GET /api/v1/openapi.yaml
     */
    public function openapiYaml(Requisicao $req): Resposta
    {
        $yamlPath = dirname(__DIR__, 4) . '/public/api/v1/openapi.yaml';
        if (!is_file($yamlPath)) {
            return Resposta::texto('Not found', 404);
        }

        $content = file_get_contents($yamlPath);
        return Resposta::texto($content)->comHeaders([
            'Content-Type' => 'text/yaml; charset=utf-8',
        ]);
    }
}
