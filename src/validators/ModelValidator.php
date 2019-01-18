<?php

namespace matrozov\yii2multipleField\validators;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\helpers\Html;
use yii\validators\Validator;

/**
 * Class ModelValidator
 * @package matrozov\yii2multipleField
 *
 * @property Model  $model
 * @property string $scenario
 */
class ModelValidator extends Validator
{
    public $model;
    public $scenario = Model::SCENARIO_DEFAULT;

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

        if (!$this->model && !($this->model instanceof Model)) {
            throw new InvalidConfigException('"model" parameter required!');
        }

        if (empty($this->scenario)) {
            throw new InvalidConfigException('"scenario" parameter required!');
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
        $value = $model->$attribute;

        if (!is_array($value) && !($value instanceof \ArrayAccess)) {
            $this->addError($model, $attribute, $this->message);
            return;
        }

        if ($value instanceof $this->model) {
            $object = $value;
            $object->scenario = $this->scenario;
        }
        else {
            /** @var Model $object */
            $object = Yii::createObject(['class' => $this->model]);
            $object->scenario = $this->scenario;

            if (!$object->load($value, '')) {
                $this->addError($model, $attribute, $this->message);
                return;
            }
        }

        if (!$object->validate() && $object->hasErrors()) {
            $errors = $object->getFirstErrors();

            foreach ($errors as $field => $error) {
                if (!preg_match(Html::$attributeRegex, $field, $matches)) {
                    $model->addError($attribute . '[' . $field . ']', $error);
                    return;
                }

                $model->addError($attribute . '[' . $matches[2] . ']' . $matches[3], $error);
            }
        }

        $model->$attribute = $object;
    }

    /**
     * @param  array|Model $value
     *
     * @return array|null
     * @throws InvalidConfigException
     */
    public function validateValue($value)
    {
        if (!is_array($value) && !($value instanceof \ArrayAccess)) {
            return [$this->message, []];
        }

        if ($value instanceof $this->model) {
            $object = $value;
            $object->scenario = $this->scenario;
        }
        else {
            /** @var Model $object */
            $object = Yii::createObject(['class' => $this->model]);
            $object->scenario = $this->scenario;

            if (!$object->load($value, '')) {
                return [$this->message, []];
            }
        }

        if (!$object->validate() && $object->hasErrors()) {
            $errors = $object->getFirstErrors();

            foreach ($errors as $field => $error) {
                return [$error, []];
            }
        }

        return null;
    }
}