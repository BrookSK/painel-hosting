<?php

declare(strict_types=1);

namespace LRV\App\Controllers;

use LRV\Core\Http\Requisicao;
use LRV\Core\Http\Resposta;
use LRV\Core\SistemaConfig;

final class SeoController
{
    public function robots(Requisicao $req): Resposta
    {
        $base    = SistemaConfig::seoCanonicalBase();
        $robots  = SistemaConfig::seoRobots();
        $noindex = str_contains($robots, 'noindex');

        $linhas = [];
        $linhas[] = 'User-agent: *';

        if ($noindex) {
            $linhas[] = 'Disallow: /';
        } else {
            // Bloquear áreas privadas
            $linhas[] = 'Disallow: /equipe/';
            $linhas[] = 'Disallow: /cliente/';
            $linhas[] = 'Disallow: /api/';
            $linhas[] = 'Disallow: /webhooks/';
            $linhas[] = 'Allow: /';
            if ($base !== '') {
                $linhas[] = '';
                $linhas[] = 'Sitemap: ' . $base . '/sitemap.xml';
            }
        }

        return Resposta::texto(implode("\n", $linhas))->comHeaders(['Content-Type' => 'text/plain; charset=utf-8']);
    }

    public function sitemap(Requisicao $req): Resposta
    {
        $base   = SistemaConfig::seoCanonicalBase();
        $robots = SistemaConfig::seoRobots();

        if ($base === '' || str_contains($robots, 'noindex')) {
            return Resposta::texto('', 404);
        }

        $now = date('Y-m-d');

        $urls = [
            ['loc' => $base . '/',           'priority' => '1.0', 'changefreq' => 'weekly'],
            ['loc' => $base . '/status',      'priority' => '0.7', 'changefreq' => 'hourly'],
            ['loc' => $base . '/contato',     'priority' => '0.6', 'changefreq' => 'monthly'],
            ['loc' => $base . '/changelog',   'priority' => '0.5', 'changefreq' => 'weekly'],
            ['loc' => $base . '/termos',      'priority' => '0.3', 'changefreq' => 'yearly'],
            ['loc' => $base . '/privacidade', 'priority' => '0.3', 'changefreq' => 'yearly'],
        ];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $u) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>' . htmlspecialchars($u['loc'], ENT_XML1) . "</loc>\n";
            $xml .= '    <lastmod>' . $now . "</lastmod>\n";
            $xml .= '    <changefreq>' . $u['changefreq'] . "</changefreq>\n";
            $xml .= '    <priority>' . $u['priority'] . "</priority>\n";
            $xml .= "  </url>\n";
        }
        $xml .= '</urlset>';

        return Resposta::texto($xml)->comHeaders(['Content-Type' => 'application/xml; charset=utf-8']);
    }
}
