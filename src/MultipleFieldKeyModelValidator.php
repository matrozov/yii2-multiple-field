<?php

namespace matrozov\yii2multipleField;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\helpers\Html;

/**
 * Class MultipleFieldKeyModelValidator
 * @package matrozov\yii2multipleField
 *
 * @property Model $model
 */
class MultipleFieldKeyModelValidator extends MultipleFieldKeyValidator
{
    public $model;

    /**
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();

        if (!$this->model && !($this->model instanceof Model)) {
            throw new InvalidConfigException('"model" parameter required!');
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

        foreach ($values as $key => $data) {
            $object = Yii::createObject(['class' => $this->model]);

            if (!$object->load($data, '')) {
                $this->addError($model, $attribute, $this->message);
                return;
            }

            if (!$object->validate() && $object->hasErrors()) {
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
        if (($error = parent::validateValue($values)) !== null) {
            return $error;
        }

        $validator = new MultipleFieldModelValidator([
            'model' => $this->model,
        ]);

        foreach ($values as $key => $value) {
            $error = $validator->validateValue($value);

            if ($error !== null) {
                return [$this->message, []];
            }
        }

        return null;
    }
}