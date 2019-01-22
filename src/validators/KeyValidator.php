<?php

namespace matrozov\yii2multipleField\validators;

use matrozov\yii2multipleField\traits\ValueValidatorTrait;
use Yii;
use matrozov\yii2multipleField\extend\DynamicModel;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\validators\Validator;

/**
 * Class KeyValidator
 * @package matrozov\yii2multipleField
 *
 * @property array $keyRules
 * @property bool  $keyIsIndexed
 */
abstract class KeyValidator extends Validator
{
    use ValueValidatorTrait;

    public $keyRules     = [];
    public $keyIsIndexed = false;

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

        if ($this->keyIsIndexed && !ArrayHelper::isIndexed($values)) {
            $this->addError($model, $attribute, $this->message);
            return;
        }

        $filtered = [];

        foreach ($values as $key => $value) {
            $object = new DynamicModel(['key' => $key]);

            static::prepareValueRules($this->keyRules, $object, $model, 'key', $key, $value);

            if (!$object->validate()) {
                $error = $object->getFirstError('key');

                $model->addError($attribute . '[' . $key . ']', $error);
            }

            $filtered[$object['key']] = $value;
        }

        $model->$attribute = $filtered;
    }

    /**
     * @param array $values
     *
     * @return array|null
     * @throws InvalidConfigException
     */
    public function validateValue($values)
    {
        if (!is_array($values) && !($values instanceof \ArrayAccess)) {
            return [$this->message, []];
        }

        if ($this->keyIsIndexed && !ArrayHelper::isIndexed($values)) {
            return [$this->message, []];
        }

        foreach ($values as $key => $value) {
            $object = new DynamicModel(['key' => $key]);

            static::prepareValueRules($this->keyRules, $object, new Model(), 'key', $key, $value);

            if (!$object->validate()) {
                $error = $object->getFirstError('key');

                return [$error, []];
            }
        }

        return null;
    }
}