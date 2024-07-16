<?php

namespace matrozov\yii2multipleField\helpers;

use yii\helpers\ArrayHelper;

/**
 * Class Html
 * @package matrozov\yii2multipleField\helpers
 */
class Html extends \yii\helpers\Html
{
    /**
     * @param \yii\base\Model $model
     * @param string          $attribute
     * @param array           $options
     *
     * @return string
     */
    public static function error($model, $attribute, $options = [])
    {
        $errorSource = ArrayHelper::remove($options, 'errorSource');

        if ($errorSource !== null) {
            $error = call_user_func($errorSource, $model, $attribute);
        } else {
            $error = $model->getFirstError($attribute);
        }

        $tag    = ArrayHelper::remove($options, 'tag', 'div');
        $encode = ArrayHelper::remove($options, 'encode', true);

        return Html::tag($tag, $encode ? Html::encode($error) : $error, $options);
    }
}
