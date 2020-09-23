<?php

namespace Biz\InformationCollect\FormItem;

class WechatFormItem extends FormItem
{
    const TYPE = 'input';

    const TITLE = '微信号';

    const FILED = 'wechat';

    public function getData()
    {
        return [
            'type' => self::TYPE,
            'title' => self::TITLE,
            'field' => self::FILED,
            'value' => $this->value,
            'validate' => [
                ['required' => $this->required, 'message' => self::TITLE.'不能为空'],
                ['pattern' => '^[a-zA-Z]([-_a-zA-Z0-9]{5,19})+$', 'message' => self::TITLE.'格式错误'],
            ],
        ];
    }
}
