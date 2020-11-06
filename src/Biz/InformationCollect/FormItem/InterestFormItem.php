<?php

namespace Biz\InformationCollect\FormItem;

class InterestFormItem extends FormItem
{
    const TYPE = 'textarea';

    const TITLE = '兴趣';

    const FIELD = 'interest';

    public function getData()
    {
        return [
            'type' => self::TYPE,
            'title' => self::TITLE,
            'field' => self::FIELD,
            'value' => $this->value,
            'group' => self::OTHER_INFO_GROUP,
            'required' => $this->required,
            'validate' => [
                ['required' => $this->required, 'message' => self::TITLE.'不能为空'],
                ['min' => 2, 'message' => '最少输入2个字符'],
                ['max' => 100, 'message' => '最多输入100个字符'],
            ],
        ];
    }
}
