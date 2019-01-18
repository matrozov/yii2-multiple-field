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
 * @property Model $model
 * @property string $scenario
 */
class KeyModelValidator extends KeyValidator
{
    public $model;
    public $scenario = Model::SCENARIO_DEFAULT;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

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
        parent::validateAttribute($model, $attribute);

        if ($model->hasErrors($attribute)) {
            return;
        }

        $values = $model->$attribute;

        foreach ($values as $key => $value) {
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