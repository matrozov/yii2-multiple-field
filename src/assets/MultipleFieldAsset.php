<?php

namespace matrozov\yii2multipleField\assets;

use yii\web\AssetBundle;

/**
 * Class MultipleFieldAsset
 * @package matrozov\yii2multipleField
 */
class MultipleFieldAsset extends AssetBundle
{
    public $sourcePath = __DIR__ . '/../web';

    public $depends = [
        'yii\web\JqueryAsset',
    ];

    public $js = [
        YII_DEBUG ? 'js/jquery.multipleField.js' : 'js/jquery.multipleField.min.js'
    ];
}
