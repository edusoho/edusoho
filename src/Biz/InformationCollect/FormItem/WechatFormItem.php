<?php

namespace Biz\InformationCollect\FormItem;

use Symfony\Component\Form\Extension\Core\Type\TextType;

class WechatFormItem extends FormItem
{
    const TYPE = 'input';

    const TITLE = '微信号';

    const FIELD = 'wechat';

    public function getData()
    {
        return [
            'type' => self::TYPE,
            'title' => self::TITLE,
            'field' => self::FIELD,
            'group' => self::CONTACT_INFO_GROUP,
            'value' => $this->value,
            'builderType' => TextType::class,
            'validate' => [
                ['required' => $this->required, 'message' => self::TITLE.'不能为空'],
                ['pattern' => '^[a-zA-Z]([-_a-zA-Z0-9])+$', 'message' => self::TITLE.'格式错误'],
                ['min' => 6, 'message' => self::TITLE.'格式错误'],
                ['max' => 20, 'message' => self::TITLE.'格式错误'],
            ],
        ];
    }
}
