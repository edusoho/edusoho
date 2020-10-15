<?php

namespace Biz\InformationCollect\FormItem;

use Symfony\Component\Form\Extension\Core\Type\TextType;

class PhoneFormItem extends FormItem
{
    const TYPE = 'input';

    const TITLE = '手机号码';

    const FIELD = 'phone';

    public function getData()
    {
        return [
            'type' => self::TYPE,
            'title' => self::TITLE,
            'field' => self::FIELD,
            'value' => $this->value,
            'group' => self::CONTACT_INFO_GROUP,
            'builderType' => TextType::class,
            'builderOptions' => [
                'attr' => ['placeholder' => '仅支持中国大陆手机号码',],
            ],
            'props' => [
                'type' => 'number',
                'placeholder' => '仅支持中国大陆手机号码',
            ],
            'validate' => [
                ['required' => $this->required, 'message' => self::TITLE.'不能为空'],
                ['pattern' => '^[1][0-9]{10}$', 'message' => self::TITLE.'格式错误'],
            ],
        ];
    }
}
