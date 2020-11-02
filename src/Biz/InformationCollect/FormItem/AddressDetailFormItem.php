<?php

namespace Biz\InformationCollect\FormItem;

class AddressDetailFormItem extends FormItem
{
    const TYPE = 'textarea';

    const TITLE = '详细地址';

    const FIELD = 'address_detail';

    public function getData()
    {
        return [
            'type' => self::TYPE,
            'title' => self::TITLE,
            'field' => self::FIELD,
            'value' => $this->value,
            'group' => self::CONTACT_INFO_GROUP,
            'required' => $this->required,
            'validate' => [
                ['required' => $this->required, 'message' => self::TITLE.'不能为空'],
                ['min' => 2, 'message' => '最少输入2个字符'],
                ['max' => 100, 'message' => '最多输入100个字符'],
            ],
        ];
    }
}
