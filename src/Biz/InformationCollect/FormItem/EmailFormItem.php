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
            'value' => $this->value,
            'validate' => [
                ['required' => $this->required, 'message' => self::TITLE.'不能为空'],
                ['pattern' => '^[a-z0-9]+([._\\-]*[a-z0-9])*@([a-z0-9]+[-a-z0-9]*[a-z0-9]+.){1,63}[a-z0-9]+$', 'message' => self::TITLE.'格式错误'],
            ],
        ];
    }
}
