<?php

namespace Biz\InformationCollect\FormItem;

class AgeFormItem extends FormItem
{
    const TYPE = 'input';

    const TITLE = '年龄';

    const FIELD = 'age';

    public function getData()
    {
        return [
            'type' => self::TYPE,
            'title' => self::TITLE,
            'field' => self::FIELD,
            'value' => $this->value,
            'group' => self::BASE_INFO_GROUP,
            'required' => $this->required,
            'props' => [
                'type' => 'number',
            ],
            'validate' => [
                ['required' => $this->required, 'message' => self::TITLE.'不能为空'],
                ['pattern' => '^[1-9]([0-9])?$', 'message' => self::TITLE.'不在正常范围内'],
            ],
        ];
    }
}
