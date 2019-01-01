<?php

namespace matrozov\yii2multipleField;

use yii\base\DynamicModel;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\helpers\Html;

/**
 * Class MultipleFieldKeyValueValidator
 * @package matrozov\yii2multipleField
 *
 * @property array $rules
 */
class MultipleFieldKeyValueValidator extends MultipleFieldKeyValidator
{
    public $rules;

    /**
     * @throws InvalidConfigException
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
        $values = $model->$attribute;

        if (!is_array($values) && !($values instanceof \ArrayAccess)) {
            $this->addError($model, $attribute, $this->message);
            return;
        }

        parent::validateAttribute($model, $attribute);

        foreach ($values as $key => $data) {
            $object = DynamicModel::validateData($data, $this->rules);

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

            $model->$attribute[$key] = $object;
        }
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

        $error = parent::validateValue($values);

        if ($error !== null) {
            return $error;
        }

        foreach ($values as $key => $data) {
            $object = DynamicModel::validateData($data, $this->rules);

            if ($object->hasErrors()) {
                $errors = $object->getFirstErrors();

                foreach ($errors as $field => $error) {
                    return [$error, []];
                }
            }
        }

        return null;
    }
}