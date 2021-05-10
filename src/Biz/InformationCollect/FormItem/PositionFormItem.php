<?php

namespace Biz\InformationCollect\FormItem;

class PositionFormItem extends FormItem
{
    const TYPE = 'input';

    const TITLE = '职位';

    const FIELD = 'position';

    public function getData()
    {
        return [
            'type' => self::TYPE,
            'title' => self::TITLE,
            'field' => self::FIELD,
            'value' => $this->value,
            'group' => self::COMPANY_INFO_GROUP,
            'required' => $this->required,
            'validate' => [
                ['required' => $this->required, 'message' => self::TITLE.'不能为空'],
                ['min' => 2, 'message' => '最少输入2个字符'],
                ['max' => 40, 'message' => '最多输入40个字符'],
            ],
        ];
    }
}
