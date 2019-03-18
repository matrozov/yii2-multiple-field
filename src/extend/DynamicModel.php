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
        if (!parent::__isset($name)) {
            $this->defineAttribute($name, null);
        }

        return parent::__get($name);
    }

    /**
     * {@inheritdoc}
     */
    public function __isset($name)
    {
        $this->defineAttribute($name, null);

        return true;
    }
}
