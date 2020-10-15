<?php

namespace Biz\InformationCollect\FormItem;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;

class GenderFormItem extends FormItem
{
    const TYPE = 'radio';

    const TITLE = '性别';

    const FIELD = 'gender';

    public function getData()
    {
        return [
            'type' => self::TYPE,
            'title' => self::TITLE,
            'field' => self::FIELD,
            'group' => self::BASE_INFO_GROUP,
            'value' => empty($this->value) ? '男' : $this->value,
            'builderType' => ChoiceType::class,
            'builderOptions' => [
                'expanded' => true,
                'choices' => [
                    '男' => '男', '女' => '女'
                ],
            ],
            'options' => [
                ['value' => '男', 'label' => '男'],
                ['value' => '女', 'label' => '女'],
            ],
            'validate' => [
                ['required' => $this->required, 'message' => self::TITLE.'不能为空'],
            ],
        ];
    }
}
