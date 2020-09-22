<?php

namespace Biz\InformationCollect\FormItem;

class FormItemFectory
{
    public static function create($code)
    {
        $formItem = self::getFormItems()[$code];

        return new $formItem();
    }

    public static function getFormItems()
    {
        return [
            'name' => 'Biz\InformationCollect\FormItem\NameFormItem',
            'gender' => 'Biz\InformationCollect\FormItem\GenderFormItem',
        ];
    }
}
