<?php

namespace app\assets;

use yii\web\AssetBundle;

class ChartJsAsset extends AssetBundle
{

    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'ChartJs/Chart.min.css',
    ];
    public $js = [
        'ChartJs/Chart.min.js',
    ];
    public $depends = [
        'app\assets\SiteAsset',
    ];

}
