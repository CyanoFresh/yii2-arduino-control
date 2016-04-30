<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\View;

class ControlController extends Controller
{
    public function actionIndex()
    {
        $this->view->registerJs('
        var WebSocketURL = "' . Yii::$app->params['webSocketURL'] . '";
        ', View::POS_HEAD);

        return $this->render('index');
    }
}
