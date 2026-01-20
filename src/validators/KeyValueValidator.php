<?php

namespace matrozov\yii2multipleField\validators;

use matrozov\yii2multipleField\models\DynamicModel;
use yii\base\InvalidConfigException;
use yii\base\Model;

/**
 * Class KeyValueValidator
 * @package matrozov\yii2multipleField\validators
 *
 * @property array $rules
 */
class KeyValueValidator extends KeyValidator
{
    public $rules;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        if ($this->rules && !is_array($this->rules)) {
            throw new InvalidConfigException('"rules" parameter required!');
        }
    }

    /**
     * @param Model  $model
     * @param string $attribute
     *
     * @throws InvalidConfigException
     */
    public function validateAttribute($model, $attribute)
    {
        parent::validateAttribute($model, $attribute);

        if ($model->hasErrors($attribute)) {
            return;
        }

        $values = $model->$attribute;

        if (!is_array($values) && !($values instanceof \ArrayAccess)) {
            $this->addError($model, $attribute, $this->message);
            return;
        }

        foreach ($values as $key => $value) {
            $object = new DynamicModel(['value' => $value]);

            static::prepareValueRules($this->rules, $object, $model, 'value', $key, $value);

            if (!$object->validate()) {
                $error = $object->getFirstError('value');

                $model->addError($this->formatErrorAttribute($attribute, [$key]), $error);
            }

            $values[$key] = $object['value'];
        }

        $model->$attribute = $values;
    }

    /**
     * @param array $values
     *
     * @return array|null
     * @throws InvalidConfigException
     */
    public function validateValue($values)
    {
        if (($error = parent::validateValue($values)) !== null) {
            return $error;
        }

        foreach ($values as $key => $value) {
            $object = new DynamicModel(['value' => $value]);

            static::prepareValueRules($this->rules, $object, new Model(), 'value', $key, $value);

            if (!$object->validate()) {
                $error = $object->getFirstError('value');

                return [$error, []];
            }
        }

        return null;
    }
}
