<?php

namespace app\commands;

use app\servers\ControlPanel;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Factory;
use React\Socket\Server;
use Ratchet\Http\OriginCheck;
use Yii;
use yii\console\Controller;

class ServerController extends Controller
{
    public function actionIndex($port = 8081)
    {
        echo 'Starting server on port ' . $port . "..." . PHP_EOL;

        $loop = Factory::create();

        $socket = new Server($loop);
        $socket->listen($port, '0.0.0.0');

        $server = new IoServer(
            new HttpServer(
                new OriginCheck(
                    new WsServer(
                        new ControlPanel($loop, Yii::$app->params)
                    ),
                    [
                        Yii::$app->params['domain']
                    ]
                )
            ),
            $socket,
            $loop
        );

        $server->run();
    }
}
