<?php

namespace matrozov\yii2multipleField\validators;

use matrozov\yii2multipleField\models\DynamicModel;
use matrozov\yii2multipleField\traits\ValueValidatorTrait;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * Class KeyValidator
 * @package matrozov\yii2multipleField\validators
 *
 * @property array  $keyRules
 *
 * @property bool   $keyIsIndexed
 * @property string $messageKeyIsIndexed
 *
 * @property integer|null $min
 * @property string       $messageMin
 *
 * @property integer|null $max
 * @property string       $messageMax
 */
class KeyValidator extends Validator
{
    use ValueValidatorTrait;

    public $keyRules = [];

    public $messageArray = null;

    public $keyIsIndexed = false;
    public $messageKeyIsIndexed = null;

    public $min = null;
    public $messageMin = null;

    public $max = null;
    public $messageMax = null;

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

        if ($this->messageArray === null) {
            $this->messageArray = Yii::t('yii', '{attribute} must be an array.');
        }

        if ($this->messageKeyIsIndexed === null) {
            $this->messageKeyIsIndexed = Yii::t('yii', '{attribute} must be indexed.');
        }

        if ($this->messageMin === null) {
            $this->messageMin = Yii::t('yii', '{attribute} must contain at least {min} element.');
        }

        if ($this->messageMax === null) {
            $this->messageMax = Yii::t('yii', '{attribute} must contain at most {max} element.');
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
            $this->addError($model, $attribute, $this->messageArray);
            return;
        }

        if ($this->keyIsIndexed && !ArrayHelper::isIndexed($values)) {
            $this->addError($model, $attribute, $this->messageKeyIsIndexed);
            return;
        }

        if (($this->min !== null) && (count($values) < $this->min)) {
            $this->addError($model, $attribute, $this->messageMin, [
                'min' => $this->min,
            ]);
        }

        if (($this->max !== null) && (count($values) > $this->max)) {
            $this->addError($model, $attribute, $this->messageMax, [
                'max' => $this->max,
            ]);
        }

        $filtered = [];

        foreach ($values as $key => $value) {
            $object = new DynamicModel(['key' => $key]);

            static::prepareValueRules($this->keyRules, $object, $model, 'key', $key, $value);

            if (!$object->validate()) {
                $error = $object->getFirstError('key');

                $model->addError($this->formatErrorAttribute($attribute, [$key]), $error);
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
