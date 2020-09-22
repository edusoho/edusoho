<?php

namespace Biz\InformationCollect\FormItem;

class NameFormItem extends FormItem
{
    const TYPE = 'input';

    const TITLE = '姓名';

    const FILED = 'name';

    public function getData()
    {
        return [
            'type' => self::TYPE,
            'title' => self::TITLE,
            'field' => self::FILED,
            'value' => $this->value,
            'validate' => [
                ['required' => $this->required, 'message' => '请输入姓名'],
                ['min' => 2, 'message' => '最少2个字'],
                ['max' => 20, 'message' => '最多20个字'],
            ],
        ];
    }
}
