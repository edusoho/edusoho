<?php

namespace Biz\InformationCollect\FormItem;

class NameFormItem extends FormItem
{
    const TYPE = 'input';

    const TITLE = '姓名';

    const FIELD = 'name';

    public function getData()
    {
        return [
            'type' => self::TYPE,
            'title' => self::TITLE,
            'field' => self::FIELD,
            'value' => $this->value,
            'group' => self::BASE_INFO_GROUP,
            'validate' => [
                ['required' => $this->required, 'message' => self::TITLE.'不能为空'],
                ['min' => 2, 'message' => '最少输入2个字符'],
                ['max' => 20, 'message' => '最多输入20个字符'],
                ['pattern' => '^[\u4E00-\u9FA5A-Za-z0-9 ]+$', 'message' => self::TITLE.'格式错误'],
            ],
        ];
    }
}
