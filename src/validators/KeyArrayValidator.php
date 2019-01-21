<?php

namespace matrozov\yii2multipleField\validators;

use matrozov\yii2multipleField\extend\DynamicModel;
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
            $object = DynamicModel::validateData($value, $this->rules);

            if ($object->hasErrors()) {
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

            $model->$attribute[$key] = $object->getAttributes();
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