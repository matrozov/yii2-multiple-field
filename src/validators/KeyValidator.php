<?php

namespace matrozov\yii2multipleField\validators;

use Yii;
use matrozov\yii2multipleField\extend\DynamicModel;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\validators\Validator;

/**
 * Class KeyValidator
 * @package matrozov\yii2multipleField
 *
 * @property array $keyRules
 * @property bool  $keyIsIndexed
 */
abstract class KeyValidator extends Validator
{
    public $keyRules     = [];
    public $keyIsIndexed = false;

    /**
     * {@inheritdoc}
     *
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

        if ($this->keyIsIndexed && !ArrayHelper::isIndexed($values)) {
            $this->addError($model, $attribute, $this->message);
            return;
        }

        $filtered = [];

        $rules = $this->prepareKeyRules();

        foreach ($values as $key => $value) {
            $object = DynamicModel::validateData(['key' => $key], $rules);

            if ($object->hasErrors()) {
                $error = $object->getFirstError('key');

                $model->addError($attribute . '[' . $key . ']', $error);
            }

            $filtered[$object['key']] = $value;
        }

        $model->$attribute = $filtered;
    }

    /**
     * @param array $values
     *
     * @return array|null
     * @throws InvalidConfigException
     */
    public function validateValue($values)
    {
        if (!is_array($values) && !($values instanceof \ArrayAccess)) {
            return [$this->message, []];
        }

        if ($this->keyIsIndexed && !ArrayHelper::isIndexed($values)) {
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