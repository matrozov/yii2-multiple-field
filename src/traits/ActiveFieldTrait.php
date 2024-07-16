<?php

declare(strict_types=1);

namespace matrozov\yii2multipleField\traits;

use matrozov\yii2multipleField\helpers\Html;

trait ActiveFieldTrait
{
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
        } else {
            parent::addErrorClassIfNeeded($options);
        }
    }
}
