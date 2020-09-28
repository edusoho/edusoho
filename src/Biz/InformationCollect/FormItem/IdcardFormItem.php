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
            'props' => [
                'placeholder' => '仅支持中国大陆身份证号',
            ],
            'validate' => [
                ['required' => $this->required, 'message' => self::TITLE.'不能为空'],
                ['pattern' => '[0-9]{17}[0-9xX]{1}', 'message' => self::TITLE.'格式错误'],
            ],
        ];
    }
}
