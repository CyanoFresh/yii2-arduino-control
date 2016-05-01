<?php

namespace app\assets;

use yii\web\AssetBundle;

class MaterialBootstrapAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/bootstrap-material-design.min.css',
        'css/ripples.min.css',
    ];
    public $js = [
        'js/material.min.js',
        'js/ripples.min.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
