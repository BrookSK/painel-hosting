<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use LRV\App\Services\Chat\ChatWsApp;
use LRV\Core\Bootstrap;
use LRV\Core\Settings;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Factory as LoopFactory;
use React\Socket\SocketServer;

Bootstrap::iniciar();

$loop  = LoopFactory::create();
$porta = (int) Settings::obter('chat.ws_port', '8082');

$socket = new SocketServer('127.0.0.1:' . $porta, [], $loop);

$server = new IoServer(
    new HttpServer(new WsServer(new ChatWsApp())),
    $socket,
    $loop
);

echo "Chat WS iniciado em 127.0.0.1:{$porta}\n";

$server->run();
