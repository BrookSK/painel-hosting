<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use LRV\Core\Bootstrap;
use LRV\Core\Roteador;

Bootstrap::iniciar();

// Remover CSP do servidor (Plesk) e definir o nosso com Stripe permitido
header_remove('Content-Security-Policy');
header("Content-Security-Policy: default-src 'self'; base-uri 'self'; frame-ancestors 'none'; object-src 'none'; form-action 'self' https://checkout.stripe.com; img-src 'self' data: https:; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; script-src 'self' 'unsafe-inline' https://js.stripe.com; connect-src 'self' https://api.stripe.com ws: wss:; frame-src https://js.stripe.com https://hooks.stripe.com;");

$roteador = new Roteador();

require __DIR__ . '/../routes/web.php';
require __DIR__ . '/../routes/api.php';

$roteador->despachar();
