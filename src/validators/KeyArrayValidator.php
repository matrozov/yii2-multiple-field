<?php

namespace matrozov\yii2multipleField\validators;

use matrozov\yii2multipleField\models\DynamicModel;
use matrozov\yii2multipleField\traits\ModelValidatorTrait;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\helpers\Html;

/**
 * Class KeyArrayValidator
 * @package matrozov\yii2multipleField
 *
 * @property array $rules
 */
class KeyArrayValidator extends KeyValidator
{
    use ModelValidatorTrait;

    public $rules;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        if (!$this->rules && !is_array($this->rules)) {
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

        foreach ($values as $key => $value) {
            $object = new DynamicModel($value);

            static::prepareModelRules($this->rules, $object, $model, $key, $value);

            if (!$object->validate()) {
                $errors = $object->getFirstErrors();

                foreach ($errors as $field => $error) {
                    if (!preg_match(Html::$attributeRegex, $field, $matches)) {
                        $model->addError($attribute . '[' . $key . '][' . $field . ']', $error);
                        return;
                    }

                    $model->addError($attribute . '[' . $key . '][' . $matches[2] . ']' . $matches[3], $error);
                }

                continue;
            }

            $values[$key] = $object->getAttributes();
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

        $validator = new ArrayValidator([
            'rules' => $this->rules,
        ]);

        foreach ($values as $key => $value) {
            $error = $validator->validateValue($value);

            if ($error !== null) {
                return $error;
            }
        }

        return null;
    }
}
