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
    protected $relayOn;

    public function __construct($loop, $config)
    {
        $this->loop = $loop;
        $this->config = $config;
        $this->clients = [];

        $this->curl = new Curl();

        $this->ledOn = $this->getLedIsOn();
        $this->relayOn = $this->getRelayIsOn();

        echo 'LED is ' . $this->boolToState($this->ledOn) . PHP_EOL;
        echo 'Relay is ' . $this->boolToState($this->relayOn) . PHP_EOL;
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients[$conn->resourceId] = $conn;

        $conn->send(Json::encode([
            'type' => 'welcome',
            'led' => $this->getLedIsOn(),
            'relay' => $this->getRelayIsOn(),
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
            case 'relay':
                if ($this->relayOn) {
                    return $this->relayOff();
                }

                return $this->relayOn();

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

        echo 'LED is set to ON' . PHP_EOL;

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

        echo 'LED is set to OFF' . PHP_EOL;

        return $this->sendAll([
            'type' => 'led',
            'on' => false,
        ]);
    }

    /**
     * @return bool
     */
    private function getRelayIsOn()
    {
        $response = $this->get('digital/' . $this->config['pins']['relay']);

        return $response['return_value'] === 0 ? true : false;
    }

    /**
     * @return bool|mixed
     */
    private function relayOn()
    {
        $this->get('relay1?params=1');

        $this->relayOn = true;

        echo 'Relay is set to ON' . PHP_EOL;

        return $this->sendAll([
            'type' => 'relay',
            'on' => true,
        ]);
    }

    /**
     * @return bool|mixed
     */
    private function relayOff()
    {
        $this->get('relay1?params=0');

        $this->relayOn = false;

        echo 'Relay is set to OFF' . PHP_EOL;

        return $this->sendAll([
            'type' => 'relay',
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
