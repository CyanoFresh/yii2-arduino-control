<?php

namespace app\controllers;

use linslin\yii2\curl\Curl;
use Yii;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\helpers\VarDumper;
use yii\web\Controller;
use yii\web\Response;
use yii\web\View;

class ControlPanelController extends Controller
{
    public function actionIndex()
    {
        // Insert urls into javascript
        $this->view->registerJs('
        var ledURL = "' . Url::to(['led']) . '";
        var ledStatusURL = "' . Url::to(['led-status']) . '";
        ', View::POS_HEAD);

        return $this->render('index');
    }

    public function actionLed()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $curl = new Curl();

        $response = Json::decode($curl->get(Yii::$app->params['arduinoRESTURL'] . '/digital/2'));

        if ($response['return_value'] === 0) {
            if ($curl->get(Yii::$app->params['arduinoRESTURL'] . '/digital/2/1/')) {
                return [
                    'success' => true,
                ];
            }
        } else {
            if ($curl->get(Yii::$app->params['arduinoRESTURL'] . '/digital/2/0/')) {
                return [
                    'success' => true,
                ];
            }
        }
    }

    public function actionLedStatus()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $curl = new Curl();
        $response = Json::decode($curl->get(Yii::$app->params['arduinoRESTURL'] . '/digital/2'));

        return $response['return_value'];
    }
}
