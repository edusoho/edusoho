<?php

namespace Biz\InformationCollect\FormItem;

class BirthdayFormItem extends FormItem
{
    const TYPE = 'DatePicker';

    const TITLE = '生日';

    const FIELD = 'birthday';

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
                'type' => 'date',
                'format' => 'yyyy-MM-dd',
                'placeholder' => '请选择出生年月日',
            ],
            'validate' => [
                ['required' => $this->required, 'message' => self::TITLE.'不能为空'],
            ],
        ];
    }
}
