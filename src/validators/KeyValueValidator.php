<?php

namespace matrozov\yii2multipleField\validators;

use matrozov\yii2multipleField\extend\DynamicModel;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\validators\Validator;

/**
 * Class KeyValueValidator
 * @package matrozov\yii2multipleField\validators
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
     * @return array
     */
    protected function prepareRules()
    {
        $rules = [];

        foreach ($this->rules as $rule) {
            if ($rule instanceof Validator) {
                $rules[] = $rule;
            }
            elseif (is_array($rule) && isset($rule[0])) {
                $rules[] = ArrayHelper::merge(['value'], $rule);
            }
        }

        return $rules;
    }

    /**
     * @param Model  $model
     * @param string $attribute
     *
     * @throws InvalidConfigException
     */
    public function validateAttribute($model, $attribute)
    {
        $values = $model->$attribute;

        if (!is_array($values) && !($values instanceof \ArrayAccess)) {
            $this->addError($model, $attribute, $this->message);
            return;
        }

        $filtered = [];

        $rules = $this->prepareRules();

        foreach ($values as $key => $value) {
            $object = DynamicModel::validateData(['value' => $value], $rules);

            if ($object->hasErrors()) {
                $error = $object->getFirstError('value');

                $model->addError($attribute . '[' . $key . ']', $error);
            }

            $filtered[$key] = $object['value'];
        }
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

        $rules = $this->prepareRules();

        foreach ($values as $key => $value) {
            $object = DynamicModel::validateData(['value' => $value], $rules);

            if ($object->hasErrors()) {
                $error = $object->getFirstError('value');

                return [$error, []];
            }
        }

        return null;
    }
}