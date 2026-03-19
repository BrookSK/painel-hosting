<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use LRV\App\Services\Terminal\TerminalWsApp;
use LRV\Core\Bootstrap;
use LRV\Core\ConfiguracoesSistema;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Factory as LoopFactory;
use React\Socket\SocketServer;

Bootstrap::iniciar();

$loop = LoopFactory::create();

$porta = ConfiguracoesSistema::terminalWsInternalPort();

$socket = new SocketServer('127.0.0.1:' . $porta, [], $loop);

$app = new TerminalWsApp($loop);

$server = new IoServer(
    new HttpServer(
        new WsServer($app)
    ),
    $socket,
    $loop
);

echo "Terminal WS iniciado em 127.0.0.1:" . $porta . "\n";

$server->run();
