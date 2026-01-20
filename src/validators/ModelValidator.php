<?php

namespace matrozov\yii2multipleField\validators;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\helpers\Html;

/**
 * Class ModelValidator
 * @package matrozov\yii2multipleField\validators
 *
 * @property Model|Callable  $model
 * @property string|Callable $scenario
 * @property bool            $strictClass
 */
class ModelValidator extends Validator
{
    public $model;
    public $scenario    = Model::SCENARIO_DEFAULT;
    public $strictClass = false;

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

        if (!$this->model && !(($this->model instanceof Model) || is_callable($this->model))) {
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

        if (is_callable($this->scenario)) {
            $scenario = call_user_func($this->scenario, $model);
        } else {
            $scenario = $this->scenario;
        }

        if ((!$this->strictClass && ($value instanceof $this->model))
            || ($this->strictClass && (get_class($value) == $this->model))
        ) {
            $object = $value;
            $object->scenario = $scenario;
        } else {
            if (is_callable($this->model)) {
                /** @var Model $object */
                $object = call_user_func($this->model, $model);
            } else {
                /** @var Model $object */
                $object = Yii::createObject(['class' => $this->model]);
            }

            $object->scenario = $scenario;

            if (!$object->load($value, '')) {
                $this->addError($model, $attribute, $this->message);
                return;
            }
        }

        if (!$object->validate() && $object->hasErrors()) {
            $errors = $object->getFirstErrors();

            foreach ($errors as $field => $error) {
                if (!preg_match(Html::$attributeRegex, $field, $matches)) {
                    $model->addError($this->formatErrorAttribute($attribute, [$field]), $error);
                    return;
                }

                $model->addError($this->formatErrorAttribute($attribute, [$matches[2]], $matches[3]), $error);
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

        if (is_callable($this->scenario)) {
            $scenario = call_user_func($this->scenario);
        } else {
            $scenario = $this->scenario;
        }

        if ((!$this->strictClass && ($value instanceof $this->model))
            || ($this->strictClass && (get_class($value) == $this->model))
        ) {
            $object = $value;
            $object->scenario = $scenario;
        } else {
            if (is_callable($this->model)) {
                /** @var Model $object */
                $object = call_user_func($this->model);
            } else {
                /** @var Model $object */
                $object = Yii::createObject(['class' => $this->model]);
            }

            $object->scenario = $scenario;

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
