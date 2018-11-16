<?php

namespace matrozov\yii2multipleField;

use Yii;
use yii\base\DynamicModel;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\helpers\Html;
use yii\validators\Validator;

/**
 * Class MultipleFieldValidator
 * @package matrozov\yii2multipleField
 *
 * @property array $rules
 * @property Model $model
 */
class MultipleFieldValidator extends Validator
{
    public $rules;

    public $model;

    /**
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();

        if ($this->message === null) {
            $this->message = Yii::t('yii', '{attribute} is invalid.');
        }

        if (!$this->rules && !is_array($this->rules) && !$this->model && !($this->model instanceof Model)) {
            throw new InvalidConfigException('Rules or Model parameter required!');
        }
    }

    /**
     * @param \yii\base\Model $model
     * @param string          $attribute
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

        foreach ($values as $key => $data) {
            if ($this->model) {
                $object = Yii::createObject(['class' => $this->model]);

                if (!$object->load($data)) {
                    $this->addError($model, $attribute, $this->message);
                    return;
                }

                $object->validate();
            }
            else {
                $object = DynamicModel::validateData($data, $this->rules);
            }

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

        foreach ($values as $key => $data) {
            if ($this->model) {
                $object = Yii::createObject(['class' => $this->model]);

                if (!$object->load($data)) {
                    return [$this->message, []];
                }

                $object->validate();
            }
            else {
                $object = DynamicModel::validateData($data, $this->rules);
            }

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