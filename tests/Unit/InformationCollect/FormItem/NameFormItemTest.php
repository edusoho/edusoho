<?php

namespace Tests\Unit\InformationCollect\FormItem;

use Biz\BaseTestCase;

class NameFormItemTest extends BaseTestCase
{
    public function testGetData()
    {
        $data = [
            'type' => 'input',
            'title' => '姓名',
            'field' => 'name',
            'value' => '张三',
            'group' => 'base',
            'required' => true,
            'validate' => [
                [
                    'required' => true,
                    'message' => '姓名不能为空',
                ],
                [
                    'min' => 2,
                    'message' => '最少输入2个字符',
                ],
                [
                    'max' => 36,
                    'message' => '最多输入36个字符',
                ],
                [
                    'pattern' => "^[\u4E00-\u9FA5A-Za-z0-9_.·]+$",
                    'message' => '姓名格式错误',
                ],
            ],
        ];

        $formItem = new \Biz\InformationCollect\FormItem\NameFormItem();

        $this->assertEquals($data, $formItem->required(true)->value('张三')->getData());
    }
}
