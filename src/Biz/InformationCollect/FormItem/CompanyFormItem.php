<?php

namespace Biz\InformationCollect\FormItem;

class CompanyFormItem extends FormItem
{
    const TYPE = 'input';

    const TITLE = '公司';

    const FILED = 'company';

    public function getData()
    {
        return [
            'type' => self::TYPE,
            'title' => self::TITLE,
            'field' => self::FILED,
            'value' => $this->value,
            'validate' => [
                ['required' => $this->required, 'message' => self::TITLE.'不能为空'],
                ['min' => 2, 'message' => '最少输入2个字符'],
                ['max' => 20, 'message' => '最多输入20个字符'],
            ],
        ];
    }
}
