<?php

namespace Biz\InformationCollect\FormItem;

class AgeFormItem extends FormItem
{
    const TYPE = 'input';

    const TITLE = '年龄';

    const FILED = 'age';

    public function getData()
    {
        return [
            'type' => self::TYPE,
            'title' => self::TITLE,
            'field' => self::FILED,
            'value' => $this->value,
            'props' => [
                'type' => 'number',
            ],
            'validate' => [
                ['required' => $this->required, 'message' => self::TITLE.'不能为空'],
                ['pattern' => '^[1-9]([0-9])?$', 'message' => '年龄不在正常范围内'],
            ],
        ];
    }
}
