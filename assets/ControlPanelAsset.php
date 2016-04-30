<?php

namespace app\assets;

use yii\web\AssetBundle;

class ControlPanelAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/control-panel.css',
    ];
    public $js = [
        'js/control-panel.js',
    ];
    public $depends = [
        'app\assets\AppAsset',
    ];
}
