<?php
namespace app\assets;

use yii\web\AssetBundle;

class MediumEditorAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        "MediumEditor/css/medium-editor.min.css",
        "MediumEditor/css/themes/beagle.css"
    ];
    public $js = [
        "MediumEditor/js/medium-editor-glyphicon.js"
    ];
}
