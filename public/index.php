<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use LRV\Core\Bootstrap;
use LRV\Core\Roteador;

Bootstrap::iniciar();

$roteador = new Roteador();

require __DIR__ . '/../routes/web.php';
require __DIR__ . '/../routes/api.php';

$roteador->despachar();
