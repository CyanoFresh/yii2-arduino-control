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

    protected $connection;

    protected $ledOn;
    protected $relayOn;

    /**
     * ControlPanel constructor.
     *
     * Init variables and control panel
     *
     * @param $loop
     * @param $config
     */
    public function __construct($loop, $config)
    {
        $this->loop = $loop;
        $this->config = $config;
        $this->clients = [];

        $this->curl = new Curl();

        if (!$this->checkConnection()) {
            die('No connection with Arduino');
        }

        $this->loop->addPeriodicTimer($this->config['connectionCheckInterval'], function () {
            if (!$this->checkConnection()) {
                die('No connection with Arduino');
            }
        });

        $this->ledOn = $this->getLedIsOn();
        $this->relayOn = $this->getRelayIsOn();

        echo 'LED is ' . $this->boolToState($this->ledOn) . PHP_EOL;
        echo 'Relay is ' . $this->boolToState($this->relayOn) . PHP_EOL;
    }

    /**
     * @inheritdoc
     */
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
     * @inheritdoc
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

    /**
     * @inheritdoc
     */
    public function onClose(ConnectionInterface $conn)
    {
        if (isset($this->clients[$conn->resourceId])) {
            unset($this->clients[$conn->resourceId]);
        }
    }

    /**
     * @inheritdoc
     */
    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}" . PHP_EOL;

        $conn->close();
    }

    /**
     * Send data to all clients
     * @param array $data Will be encoded to json
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
     * Get LED state
     * @return bool
     */
    private function getLedIsOn()
    {
        $response = $this->get('digital/' . $this->config['pins']['led']);

        return $response['return_value'] === 0 ? false : true;
    }

    /**
     * Switch LED on
     * @return bool|mixed
     */
    private function ledOn()
    {
        if ($this->get('digital/' . $this->config['pins']['led'] . '/1/')) {
            $this->ledOn = true;

            echo 'LED is switched ON' . PHP_EOL;

            return $this->sendAll([
                'type' => 'led',
                'on' => true,
            ]);
        }

        echo 'Cannot switch LED On';

        return $this->sendAll([
            'type' => 'error',
            'message' => 'Cannot switch LED On',
        ]);
    }

    /**
     * Switch LED off
     * @return bool
     */
    private function ledOff()
    {
        if ($this->get('digital/' . $this->config['pins']['led'] . '/0/')) {
            $this->ledOn = false;

            echo 'LED is set to OFF' . PHP_EOL;

            return $this->sendAll([
                'type' => 'led',
                'on' => false,
            ]);
        }

        echo 'Cannot switch LED Off';
        return false;
    }

    /**
     * Get relay state
     * @return bool
     */
    private function getRelayIsOn()
    {
        $response = $this->get('digital/' . $this->config['pins']['relay']);

        return $response['return_value'] === 0 ? true : false;
    }

    /**
     * Switch relay on
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
     * Switch relay off
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
     * Make request to arduino
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

    /**
     * Get state string by boolean value.
     * E.g. true = 'On'
     *      false = 'Off'
     *
     * @param $ledOn
     * @return string
     */
    private function boolToState($ledOn)
    {
        if ($ledOn) {
            return 'On';
        }

        return 'Off';
    }

    /**
     * @return boolean
     */
    private function checkConnection()
    {
        echo 'Checking Arduino connection...' . PHP_EOL;

        try {
            $data = $this->get('');

            if (!$data or !$data['connected']) {
                throw new \Exception;
            }

            $this->connection = true;

            echo 'Connection is active!' . PHP_EOL;

            return true;
        } catch (\Exception $e) {
            if ($this->connection) {
                $this->connection = false;

                echo 'Connection is lost' . PHP_EOL;

                $this->sendAll([
                    'type' => 'arduinoConnectionLost',
                ]);
            }

            return false;
        }
    }
}
