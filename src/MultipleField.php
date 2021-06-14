<?php

namespace matrozov\yii2multipleField;

use matrozov\yii2multipleField\extend\ActiveField;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\InputWidget;

/**
 * Class MultipleField
 * @package matrozov\yii2multipleField
 *
 * @property Model           $model
 * @property string          $attribute
 *
 * @property array           $options;
 * @property array           $itemOptions;

 * @property string|callable $item
 * @property int|null        $max
 * @property string          $maxReachedMessage
 */
class MultipleField extends InputWidget
{
    public $itemOptions = [];

    public $item;
    public $max;
    public $maxReachedMessage;

    /** @var int $_key */
    protected $_key;

    /**
     * {@inheritdoc}
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();

        if (empty($this->maxReachedMessage)) {
            $this->maxReachedMessage = Yii::t('app', 'Maximum field reached!');
        }
    }

    /**
     * @return string
     */
    public function run()
    {
        MultipleFieldAsset::register($this->view);

        $id       = $this->getId();
        $template = $this->renderTemplate();

        $this->options['id'] = $id;

        $result = $this->renderItems($this->options);

        $options = [
            'id'                => $id,
            'template'          => $template,
            'nextKey'           => $this->_key++,
            'max'               => $this->max,
            'maxReachedMessage' => $this->maxReachedMessage,
        ];

        $this->view->registerJs('$(\'#' . $id . '\').multipleField(' . Json::encode($options) . ');');

        return $result;
    }

    /**
     * @param $options
     *
     * @return string
     */
    protected function renderItems($options)
    {
        $result = '';

        $values = $this->model->{$this->attribute};
        $keys   = $values ? array_keys($values) : [1];

        foreach ($keys as $key) {
            $result .= $this->renderItem($key, $this->itemOptions);
        }

        $tag = ArrayHelper::remove($options, 'tag', 'div');
        Html::addCssClass($options, 'multiple-field');

        return Html::tag($tag, $result, $options);
    }

    /**
     * @param int|string $key
     * @param array      $options
     *
     * @return string
     */
    protected function renderItem($key, $options)
    {
        $this->_key = $key;

        if (is_callable($this->item)) {
            $result = call_user_func_array($this->item, [$this, $key, &$options]);
        }
        else {
            $result = $this->view->render($this->item, [
                'block' => $this,
                'key'   => $key,
            ]);
        }

        $tag = ArrayHelper::remove($options, 'tag', 'div');
        Html::addCssClass($options, 'multiple-field-item');

        return Html::tag($tag, $result, $options);
    }

    protected function renderTemplate()
    {
        $templateKey = uniqid();

        return explode($templateKey, $this->renderItem($templateKey, $this->itemOptions));
    }

    /**
     * @param string $attribute
     * @param array  $options
     *
     * @return \yii\widgets\ActiveField
     */
    public function field($attribute, array $options = [])
    {
        $attributeFullName = $this->attribute . '[' . $this->_key . '][' . $attribute . ']';

        return $this->field->form->field($this->model, $attributeFullName, ArrayHelper::merge([
            'class' => ActiveField::class,
        ], $options));
    }
}
