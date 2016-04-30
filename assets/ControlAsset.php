<?php

namespace app\assets;

use yii\web\AssetBundle;

class ControlAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/control.css',
    ];
    public $js = [
        'js/control.js',
    ];
    public $depends = [
        'app\assets\AppAsset',
    ];
}
