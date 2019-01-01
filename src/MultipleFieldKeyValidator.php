<?php

namespace matrozov\yii2multipleField;

use Yii;
use yii\base\DynamicModel;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\validators\Validator;

/**
 * Class MultipleFieldKeyValidator
 * @package matrozov\yii2multipleField
 *
 * @property array $keyRules
 */
abstract class MultipleFieldKeyValidator extends Validator
{
    public $keyRules;

    /**
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();

        if ($this->message === null) {
            $this->message = Yii::t('yii', '{attribute} is invalid.');
        }

        if (!$this->keyRules && !is_array($this->keyRules)) {
            throw new InvalidConfigException('"keyRules" parameter required!');
        }
    }

    /**
     * @return array
     */
    protected function prepareKeyRules()
    {
        $rules = [];

        foreach ($this->keyRules as $rule) {
            if ($rule instanceof Validator) {
                $rules[] = $rule;
            }
            elseif (is_array($rule) && isset($rule[0])) {
                $rules[] = ArrayHelper::merge(['key'], $rule);
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

        $rules = $this->prepareKeyRules();

        foreach ($values as $key => $data) {
            $object = DynamicModel::validateData(['key' => $key], $rules);

            if ($object->hasErrors()) {
                $error = $object->getFirstError('key');

                $model->addError($attribute . '[' . $key . ']', $error);
            }

            $filtered[$object['key']] = $data;
        }

        $model->$attribute = $filtered;
    }

    /**
     * @param mixed $values
     *
     * @return array|null
     * @throws InvalidConfigException
     */
    public function validateValue($values)
    {
        if (!is_array($values) && !($values instanceof \ArrayAccess)) {
            return [$this->message, []];
        }

        $rules = $this->prepareKeyRules();

        foreach ($values as $key => $data) {
            $object = DynamicModel::validateData(['key' => $key], $rules);

            if ($object->hasErrors()) {
                $error = $object->getFirstError('key');

                return [$error, []];
            }
        }

        return null;
    }
}