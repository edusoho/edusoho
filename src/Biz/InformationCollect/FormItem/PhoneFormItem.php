<?php

namespace Biz\InformationCollect\FormItem;

class PhoneFormItem extends FormItem
{
    const TYPE = 'input';

    const TITLE = '手机号码';

    const FIELD = 'phone';

    public function getData()
    {
        return [
            'type' => self::TYPE,
            'title' => self::TITLE,
            'field' => self::FIELD,
            'value' => $this->value,
            'group' => self::CONTACT_INFO_GROUP,
            'required' => $this->required,
            'props' => [
                'type' => 'number',
                'placeholder' => '仅支持中国大陆手机号码',
            ],
            'options' => [
                'before' => ['class' => 'phone-input-before', 'value' => '+86'],
            ],
            'validate' => [
                ['required' => $this->required, 'message' => self::TITLE.'不能为空'],
                ['pattern' => '^[1][0-9]{10}$', 'message' => self::TITLE.'格式错误'],
            ],
        ];
    }
}
