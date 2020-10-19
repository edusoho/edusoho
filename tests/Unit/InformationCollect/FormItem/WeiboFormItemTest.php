<?php

namespace Tests\Unit\InformationCollect\FormItem;

use Biz\BaseTestCase;

class WeiboFormItemTest extends BaseTestCase
{
    public function testGetData()
    {
        $data = [
            'type' => 'input',
            'title' => '微博号',
            'field' => 'weibo',
            'group' => 'contact',
            'value' => 'abc123',
            'required' => true,
            'validate' => [
                [
                    'required' => true,
                    'message' => '微博号不能为空',
                ],
                [
                    'pattern' => '^[A-Za-z0-9\\u4e00-\\u9fa5]+$',
                    'message' => '微博号格式错误',
                ],
                [
                    'min' => 4,
                    'message' => '最少输入4个字符',
                ],
                [
                    'max' => 30,
                    'message' => '最多输入30个字符',
                ],
            ],
        ];

        $formItem = new \Biz\InformationCollect\FormItem\WeiboFormItem();

        $this->assertEquals($data, $formItem->required(true)->value('abc123')->getData());
    }
}
