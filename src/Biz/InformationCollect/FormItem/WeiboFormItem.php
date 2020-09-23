<?php

namespace Biz\InformationCollect\FormItem;

class WeiboFormItem extends FormItem
{
    const TYPE = 'input';

    const TITLE = '微博号';

    const FILED = 'weibo';

    public function getData()
    {
        return [
            'type' => self::TYPE,
            'title' => self::TITLE,
            'field' => self::FILED,
            'value' => $this->value,
            'validate' => [
                ['required' => $this->required, 'message' => self::TITLE.'不能为空'],
                ['pattern' => '^[A-Za-z0-9\u4e00-\u9fa5]+$', 'message' => self::TITLE.'格式错误'],
                ['min' => 4, 'message' => '最少输入4个字符'],
                ['max' => 30, 'message' => '最多输入30个字符'],
            ],
        ];
    }
}
