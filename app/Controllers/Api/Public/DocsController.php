<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Api\Public;

use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\I18n;
use LRV\Core\View;

/**
 * Controlador de documentação da API Pública.
 * - GET /developers/api         → Página de documentação
 * - GET /developers/api/swagger → Interface Swagger UI
 * - GET /api/v1/openapi.json    → Spec em JSON
 */
final class DocsController
{
    /**
     * GET /developers — Landing page pública para desenvolvedores
     */
    public function landing(Requisicao $req): Resposta
    {
        $html = View::renderizar(__DIR__ . '/../../Views/developers/landing.php', []);
        return Resposta::html($html);
    }

    /**
     * GET /developers/api
     */
    public function index(Requisicao $req): Resposta
    {
        $html = View::renderizar(__DIR__ . '/../../Views/developers/api-docs.php', [
            'titulo' => I18n::t('api_docs.titulo'),
        ]);
        return Resposta::html($html);
    }

    /**
     * GET /developers/api/swagger
     */
    public function swagger(Requisicao $req): Resposta
    {
        $html = View::renderizar(__DIR__ . '/../../Views/developers/swagger.php', []);
        return Resposta::html($html);
    }

    /**
     * GET /developers/api/changelog
     */
    public function changelog(Requisicao $req): Resposta
    {
        $html = View::renderizar(__DIR__ . '/../../Views/developers/api-changelog.php', []);
        return Resposta::html($html);
    }

    /**
     * GET /developers/api/status
     */
    public function statusPage(Requisicao $req): Resposta
    {
        $html = View::renderizar(__DIR__ . '/../../Views/developers/api-status.php', []);
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

        // Parse YAML to JSON
        $content = file_get_contents($yamlPath);

        // Simple YAML to array (for basic specs) — using yaml_parse if available, otherwise serve YAML
        if (function_exists('yaml_parse')) {
            $data = yaml_parse($content);
            if (is_array($data)) {
                return Resposta::json($data);
            }
        }

        // Fallback: servir como text/yaml
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
