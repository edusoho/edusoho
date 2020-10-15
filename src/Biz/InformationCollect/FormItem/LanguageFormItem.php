<?php

namespace Biz\InformationCollect\FormItem;

use Symfony\Component\Form\Extension\Core\Type\TextType;

class LanguageFormItem extends FormItem
{
    const TYPE = 'input';

    const TITLE = '语言';

    const FIELD = 'language';

    public function getData()
    {
        return [
            'type' => self::TYPE,
            'title' => self::TITLE,
            'field' => self::FIELD,
            'value' => $this->value,
            'group' => self::OTHER_INFO_GROUP,
            'builderType' => TextType::class,
            'validate' => [
                ['required' => $this->required, 'message' => self::TITLE.'不能为空'],
                ['min' => 2, 'message' => '最少输入2个字符'],
                ['max' => 40, 'message' => '最多输入40个字符'],
            ],
        ];
    }
}
