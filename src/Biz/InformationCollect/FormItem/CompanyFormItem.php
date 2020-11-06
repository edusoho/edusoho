<?php

namespace Biz\InformationCollect\FormItem;

class CompanyFormItem extends FormItem
{
    const TYPE = 'input';

    const TITLE = '公司';

    const FIELD = 'company';

    public function getData()
    {
        return [
            'type' => self::TYPE,
            'title' => self::TITLE,
            'field' => self::FIELD,
            'group' => self::COMPANY_INFO_GROUP,
            'value' => $this->value,
            'required' => $this->required,
            'validate' => [
                ['required' => $this->required, 'message' => self::TITLE.'不能为空'],
                ['min' => 2, 'message' => '最少输入2个字符'],
                ['max' => 40, 'message' => '最多输入40个字符'],
            ],
        ];
    }
}
