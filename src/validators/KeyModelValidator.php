<?php

namespace matrozov\yii2multipleField\validators;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\helpers\Html;

/**
 * Class KeyModelValidator
 * @package matrozov\yii2multipleField
 *
 * @property Model|Callable  $model
 * @property string|Callable $scenario
 * @property bool            $strictClass
 */
class KeyModelValidator extends KeyValidator
{
    public $model;
    public $scenario    = Model::SCENARIO_DEFAULT;
    public $strictClass = false;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

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
        parent::validateAttribute($model, $attribute);

        if ($model->hasErrors($attribute)) {
            return;
        }

        $values = $model->$attribute;

        if (is_callable($this->scenario)) {
            $scenario = call_user_func($this->scenario, $model);
        } else {
            $scenario = $this->scenario;
        }

        foreach ($values as $key => $value) {
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
                        $model->addError($attribute . '[' . $key . '][' . $field . ']', $error);
                        return;
                    }

                    $model->addError($attribute . '[' . $key . '][' . $matches[2] . ']' . $matches[3], $error);
                }

                continue;
            }

            $values[$key] = $object;
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

        $validator = new ModelValidator([
            'model'    => $this->model,
            'scenario' => $this->scenario,
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
