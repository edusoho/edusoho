<?php

namespace Biz\InformationCollect\FormItem;

class IdcardFormItem extends FormItem
{
    const TYPE = 'input';

    const TITLE = '身份证号';

    const FIELD = 'idcard';

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
                'placeholder' => '仅支持中国大陆身份证号',
            ],
            'validate' => [
                ['required' => $this->required, 'message' => self::TITLE.'不能为空'],
                ['pattern' => '^[1-9]\d{5}(19|20)\d{2}((0[1-9])|(1[0-2]))(([0-2][1-9])|10|20|30|31)\d{3}[0-9Xx]$', 'message' => self::TITLE.'格式错误'],
            ],
        ];
    }
}
