<?php

namespace matrozov\yii2multipleField\traits;

use matrozov\yii2multipleField\models\DynamicModel;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\validators\InlineValidator;
use yii\validators\Validator;

/**
 * Class ValueValidatorTrait
 * @package matrozov\yii2multipleField\traits
 */
trait ValueValidatorTrait
{
    /**
     * @param array           $rules
     * @param DynamicModel    $dynamicModel
     * @param Model|null      $model
     * @param string[]|string $attributes
     * @param mixed           $key
     * @param mixed           $value
     *
     * @throws InvalidConfigException
     */
    protected static function prepareValueRules(array $rules, DynamicModel $dynamicModel, Model $model, $attributes, $key, $value)
    {
        $validators = $dynamicModel->getValidators();

        foreach ($rules as $rule) {
            if ($rule instanceof Validator) {
                $validators->append($rule);
            } elseif (is_array($rule) && isset($rule[0])) {
                $params = array_slice($rule, 1);

                $validator = Validator::createValidator($rule[0], $model, $attributes, $params);

                if ($validator instanceof InlineValidator) {
                    if (is_string($validator->method)) {
                        $validator->method = [$model, $validator->method];
                    }

                    $validator->params = array_merge((array)$validator->params, array_filter([
                        'model' => $dynamicModel,
                        'key'   => $key,
                        'value' => $value,
                    ]));
                }

                $validators->append($validator);
            } else {
                throw new InvalidConfigException('Invalid validation rule: a rule must be an array specifying validator type.');
            }
        }
    }
}
