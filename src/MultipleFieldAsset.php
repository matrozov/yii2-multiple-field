<?php

namespace matrozov\yii2multipleField;

use yii\web\AssetBundle;

/**
 * Class MultipleFieldAsset
 * @package matrozov\yii2multipleField
 */
class MultipleFieldAsset extends AssetBundle
{
    public $sourcePath = __DIR__.'/asset';

    public $depends = [
        'yii\web\JqueryAsset',
    ];

    public $js = [
        YII_DEBUG ? 'js/jquery.multipleField.js' : 'js/jquery.multipleField.min.js'
    ];
}