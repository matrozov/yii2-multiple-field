<?php

namespace matrozov\yii2multipleField;

use Yii;
use yii\base\DynamicModel;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\helpers\Html;
use yii\validators\Validator;

/**
 * Class MultipleFieldKeyValidator
 * @package matrozov\yii2multipleField
 *
 * @property array $keyRules
 */
class MultipleFieldKeyValidator extends Validator
{
    public $keyRules;

    /**
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

        $keys = array_keys($values);

        $object = DynamicModel::validateData($keys, $this->keyRules);

        if ($object->hasErrors()) {
            $errors = $object->getFirstErrors();

            foreach ($errors as $field => $error) {
                if (!preg_match(Html::$attributeRegex, $field, $matches)) {
                    $model->addError($attribute . '[' . $field . ']', $error);
                    return;
                }

                $model->addError($attribute . '[' . $matches[2] . ']' . $matches[3], $error);
            }
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

        $keys = array_keys($values);

        $object = DynamicModel::validateData($keys, $this->keyRules);

        if ($object->hasErrors()) {
            $errors = $object->getFirstErrors();

            foreach ($errors as $field => $error) {
                return [$error, []];
            }
        }
    }
}