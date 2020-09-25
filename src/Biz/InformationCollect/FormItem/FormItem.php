<?php

namespace Biz\InformationCollect\FormItem;

abstract class FormItem
{
    protected $required = false;

    protected $value = '';

    abstract public function getData();

    public function required($required = false)
    {
        $this->required = true === (bool) $required ? true : false;

        return $this;
    }

    public function value($value = '')
    {
        $this->value = $value;

        return $this;
    }
}
