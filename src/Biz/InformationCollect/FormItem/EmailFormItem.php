<?php

namespace Biz\InformationCollect\FormItem;

class EmailFormItem extends FormItem
{
    const TYPE = 'input';

    const TITLE = 'Email';

    const FIELD = 'email';

    public function getData()
    {
        return [
            'type' => self::TYPE,
            'title' => self::TITLE,
            'field' => self::FIELD,
            'group' => self::CONTACT_INFO_GROUP,
            'value' => $this->value,
            'required' => $this->required,
            'validate' => [
                ['required' => $this->required, 'message' => self::TITLE.'不能为空'],
                ['pattern' => "^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$", 'message' => self::TITLE.'格式错误'],
                ['min' => 6, 'message' => '最少输入6个字符'],
                ['max' => 64, 'message' => '最多输入64个字符'],
            ],
        ];
    }
}
