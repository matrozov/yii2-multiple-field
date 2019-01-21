<?php

namespace matrozov\yii2multipleField\extend;

/**
 * Class DynamicModel
 * @package matrozov\yii2multipleField\extend
 */
class DynamicModel extends \yii\base\DynamicModel
{
    /**
     * {@inheritdoc}
     */
    public function __get($name)
    {
        if (parent::__isset($name)) {
            return parent::__get($name);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function __isset($name)
    {
        return true;
    }
}