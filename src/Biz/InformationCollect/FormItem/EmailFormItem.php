<?php

namespace Biz\InformationCollect\FormItem;

class EmailFormItem extends FormItem
{
    const TYPE = 'input';

    const TITLE = 'Email';

    const FILED = 'email';

    public function getData()
    {
        return [
            'type' => self::TYPE,
            'title' => self::TITLE,
            'field' => self::FILED,
            'value' => $this->value,
            'validate' => [
                ['required' => $this->required, 'message' => self::TITLE.'不能为空'],
                ['type' => 'email', 'message' => self::TITLE.'格式错误'],
            ],
        ];
    }
}
