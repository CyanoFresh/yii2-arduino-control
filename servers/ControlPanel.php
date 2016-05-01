<?php

namespace app\servers;

use linslin\yii2\curl\Curl;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use Yii;
use yii\helpers\Json;
use yii\helpers\VarDumper;

/**
 * Class Roulette
 *
 * WebSockets handler and game logic
 *
 * @package app\components
 * @author CyanoFresh <cyanofresh@gmail.com>
 */
class ControlPanel implements MessageComponentInterface
{
    /**
     * @var \React\EventLoop\LoopInterface
     */
    protected $loop;

    /**
     * @var ConnectionInterface[] array
     */
    protected $clients;

    /**
     * @var array Server configuration
     */
    protected $config;

    /**
     * @var Curl CURL component
     */
    protected $curl;

    protected $ledOn;

    public function __construct($loop, $config)
    {
        $this->loop = $loop;
        $this->config = $config;
        $this->clients = [];

        $this->curl = new Curl();

        $this->ledOn = $this->getLedIsOn();

        echo 'LED is ' . $this->boolToState($this->ledOn) . PHP_EOL;
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients[$conn->resourceId] = $conn;

        $conn->send(Json::encode([
            'type' => 'welcome',
            'led' => $this->getLedIsOn(),
        ]));
    }

    /**
     * @param ConnectionInterface $from
     * @param string $msg
     * @return bool|mixed
     */
    public function onMessage(ConnectionInterface $from, $msg)
    {
        $data = Json::decode($msg);

        switch ($data['type']) {
            case 'led':
                if ($this->ledOn) {
                    return $this->ledOff();
                }

                return $this->ledOn();

                break;
        }

        return true;
    }

    public function onClose(ConnectionInterface $conn)
    {
        if (isset($this->clients[$conn->resourceId])) {
            unset($this->clients[$conn->resourceId]);
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}" . PHP_EOL;

        $conn->close();
    }

    /**
     * Send data to all clients
     *
     * @param array $data
     */
    private function sendAll($data)
    {
        $jsonData = Json::encode($data);

        foreach ($this->clients as $client) {
            /** @var ConnectionInterface $client */
            $client->send($jsonData);
        }
    }

    /**
     * @return bool
     */
    private function getLedIsOn()
    {
        $response = $this->get('digital/' . $this->config['pins']['led']);

        return $response['return_value'] === 0 ? false : true;
    }

    /**
     * @return bool|mixed
     */
    private function ledOn()
    {
        $this->get('digital/' . $this->config['pins']['led'] . '/1/');

        $this->ledOn = true;

        echo 'LED is set to ON';

        return $this->sendAll([
            'type' => 'led',
            'on' => true,
        ]);
    }

    /**
     * @return bool|mixed
     */
    private function ledOff()
    {
        $this->get('digital/' . $this->config['pins']['led'] . '/0/');

        $this->ledOn = false;

        echo 'LED is set to OFF';

        return $this->sendAll([
            'type' => 'led',
            'on' => false,
        ]);
    }

    /**
     * @param $url
     * @return bool|mixed
     */
    private function get($url)
    {
        $response = $this->curl->get(
            $this->config['arduinoRESTURL'] . '/' . $url
        );

        if ($response) {
            return Json::decode($response);
        }

        return false;
    }

    private function boolToState($ledOn)
    {
        if ($ledOn) {
            return 'On';
        }

        return 'Off';
    }
}
