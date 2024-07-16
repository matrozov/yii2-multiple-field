<?php

namespace matrozov\yii2multipleField\traits;

use matrozov\yii2multipleField\models\DynamicModel;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\validators\InlineValidator;
use yii\validators\Validator;

/**
 * Class ModelValidatorTrait
 * @package matrozov\yii2multipleField\traits
 */
trait ModelValidatorTrait
{
    /**
     * @param array        $rules
     * @param DynamicModel $dynamicModel
     * @param Model|null   $model
     * @param mixed        $key
     * @param mixed        $value
     *
     * @throws InvalidConfigException
     */
    protected static function prepareModelRules(array $rules, DynamicModel $dynamicModel, Model $model, $key, $value)
    {
        $validators = $dynamicModel->getValidators();

        foreach ($rules as $rule) {
            if ($rule instanceof Validator) {
                $validators->append($rule);
            } elseif (is_array($rule) && isset($rule[0]) && isset($rule[1])) {
                $params = array_slice($rule, 2);

                $validator = Validator::createValidator($rule[1], $model, $rule[0], $params);

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
            }  else {
                throw new InvalidConfigException('Invalid validation rule: a rule must be an array specifying validator type.');
            }
        }
    }
}
