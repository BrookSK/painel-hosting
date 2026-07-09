<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use LRV\Core\Bootstrap;
use LRV\Core\Roteador;

Bootstrap::iniciar();

// CSP é gerenciado pelo .htaccess (Apache mod_headers)
// Remover CSP do Plesk caso exista
header_remove('Content-Security-Policy');

$roteador = new Roteador();

require __DIR__ . '/../routes/web.php';
require __DIR__ . '/../routes/api.php';
require __DIR__ . '/../routes/api_public.php';

$roteador->despachar();
