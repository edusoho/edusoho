<?php

namespace Biz\InformationCollect\FormItem;

class GenderFormItem extends FormItem
{
    const TYPE = 'select';

    const TITLE = '性别';

    const FILED = 'gender';

    protected $value = '男';

    public function getData()
    {
        return [
            'type' => self::TYPE,
            'title' => self::TITLE,
            'field' => self::FILED,
            'value' => $this->value,
            'options' => [
                ['value' => '男', 'label' => '男'],
                ['value' => '女', 'label' => '女'],
                ['value' => '保密', 'label' => '保密'],
            ],
            'validate' => [
                ['required' => $this->required, 'message' => '请选择性别'],
            ],
        ];
    }
}
