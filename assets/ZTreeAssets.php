<?php

namespace app\assets;

use yii\web\AssetBundle;
class ZTreeAssets extends AssetBundle
{
    public $baseUrl = '@web';

    public $sourcePath = 'js/lib/ztree';

    public $css = [
        'zTreeStyle/zTreeStyle.css',
    ];

    public $js = [
        'jquery.ztree.all.js'
    ];

    public $depends = [
        'yii\web\JqueryAsset',
    ];
}