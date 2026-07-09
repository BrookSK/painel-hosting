<?php

declare(strict_types=1);

namespace LRV\App\Controllers\Api\Public;

use LRV\App\Services\PublicApi\CollectionExportService;
use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;

/**
 * Endpoints para download de coleções e specs.
 * - GET /developers/api/postman.json    → Postman Collection
 * - GET /developers/api/bruno.json      → Bruno Collection
 * - GET /developers/api/insomnia.json   → Insomnia Export
 */
final class ExportController
{
    public function postman(Requisicao $req): Resposta
    {
        $service = new CollectionExportService();
        $json = $service->gerarPostman();

        return Resposta::texto($json)->comHeaders([
            'Content-Type' => 'application/json; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="LRV-API-v1-Postman.json"',
        ]);
    }

    public function bruno(Requisicao $req): Resposta
    {
        $service = new CollectionExportService();
        $json = $service->gerarBruno();

        return Resposta::texto($json)->comHeaders([
            'Content-Type' => 'application/json; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="LRV-API-v1-Bruno.json"',
        ]);
    }

    public function insomnia(Requisicao $req): Resposta
    {
        $service = new CollectionExportService();
        $json = $service->gerarInsomnia();

        return Resposta::texto($json)->comHeaders([
            'Content-Type' => 'application/json; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="LRV-API-v1-Insomnia.json"',
        ]);
    }
}
