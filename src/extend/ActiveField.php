<?php

namespace matrozov\yii2multipleField\extend;

/**
 * Class ActiveField
 * @package matrozov\yii2multipleField\mod
 */
class ActiveField extends \yii\widgets\ActiveField
{
    /**
     * {@inheritdoc}
     */
    public function error($options = [])
    {
        if ($options === false) {
            $this->parts['{error}'] = '';
            return $this;
        }

        $options = array_merge($this->errorOptions, $options);
        $this->parts['{error}'] = Html::error($this->model, $this->attribute, $options);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function addErrorClassIfNeeded(&$options)
    {
        if ($this->model->hasErrors($this->attribute)) {
            Html::addCssClass($options, $this->form->errorCssClass);
        }
    }
}