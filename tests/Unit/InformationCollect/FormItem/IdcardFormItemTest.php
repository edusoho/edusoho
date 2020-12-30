<?php

namespace Tests\Unit\InformationCollect\FormItem;

use Biz\BaseTestCase;

class IdcardFormItemTest extends BaseTestCase
{
    public function testGetData()
    {
        $data = [
                'type' => 'input',
                'title' => '身份证号',
                'field' => 'idcard',
                'value' => '330621199406213333',
                'group' => 'base',
                'required' => true,
                'props' => [
                    'placeholder' => '仅支持中国大陆身份证号',
                ],
                'validate' => [
                    [
                        'required' => true,
                        'message' => '身份证号不能为空',
                    ],
                    [
                        'pattern' => "^[1-9]\d{5}(19|20)\d{2}((0[1-9])|(1[0-2]))(([0-2][1-9])|10|20|30|31)\d{3}[0-9Xx]$",
                        'message' => '身份证号格式错误',
                    ],
                ],
            ];

        $formItem = new \Biz\InformationCollect\FormItem\IdcardFormItem();

        $this->assertEquals($data, $formItem->required(true)->value('330621199406213333')->getData());
    }
}
