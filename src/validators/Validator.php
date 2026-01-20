<?php

declare(strict_types=1);

namespace matrozov\yii2multipleField\validators;

/**
 * Class Validator
 * @package matrozov\yii2multipleField\validators
 *
 * @property string errorFormat
 */
abstract class Validator extends \yii\validators\Validator
{
    const ERROR_FORMAT_URL = 'url';
    const ERROR_FORMAT_DOT = 'dot';

    public static $globalErrorFormat = self::ERROR_FORMAT_URL;

    public $errorFormat = null;

    protected static $formats = [
        self::ERROR_FORMAT_URL => [
            'delimiter'       => '',
            'beforeAttribute' => '[',
            'afterAttribute'  => ']',
        ],
        self::ERROR_FORMAT_URL => [
            'delimiter'       => '.',
            'beforeAttribute' => '',
            'afterAttribute'  => '',
        ],
    ];

    protected function formatErrorAttribute($prefix, $attributes, $suffix = '')
    {
        $errorFormat = $this->errorFormat ?? static::$globalErrorFormat;

        $format = static::$formats[$errorFormat];

        $attributes = array_map(function ($attribute) use ($format) {
            return $format['beforeAttribute'] . $attribute . $format['afterAttribute'];
        }, $attributes);

        $result = $prefix . $format['delimiter'] . implode($format['delimiter'], $attributes);

        if (!empty($suffix)) {
            $result .= $format['delimiter'] . $suffix;
        }

        return $result;
    }
}
