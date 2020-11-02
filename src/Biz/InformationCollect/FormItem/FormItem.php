<?php

namespace Biz\InformationCollect\FormItem;

abstract class FormItem
{
    protected $required = false;

    protected $value = '';

    const BASE_INFO_GROUP = 'base';

    const CONTACT_INFO_GROUP = 'contact';

    const COMPANY_INFO_GROUP = 'company';

    const SCHOOL_INFO_GROUP = 'school';

    const OTHER_INFO_GROUP = 'other';

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
