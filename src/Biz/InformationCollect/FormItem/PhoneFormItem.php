<?php

namespace Biz\InformationCollect\FormItem;

class PhoneFormItem extends FormItem
{
    const TYPE = 'input';

    const TITLE = '手机号码';

    const FILED = 'phone';

    public function getData()
    {
        return [
            'type' => self::TYPE,
            'title' => self::TITLE,
            'field' => self::FILED,
            'value' => $this->value,
            'props' => [
                'type' => 'number',
                'placeholder' => '仅支持中国大陆手机号码',
            ],
            'validate' => [
                ['required' => $this->required, 'message' => self::TITLE.'不能为空'],
                ['pattern' => '^[1][0-9]{10}$', 'message' => self::TITLE.'格式错误'],
            ],
        ];
    }
}
