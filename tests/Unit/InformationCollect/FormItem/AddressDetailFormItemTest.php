<?php

namespace Tests\Unit\InformationCollect\FormItem;

use Biz\BaseTestCase;

class AddressDetailFormItemTest extends BaseTestCase
{
    public function testGetData()
    {
        $data = json_decode('
            {
                "type": "textarea",
                "title": "详细地址",
                "field": "address_detail",
                "value": "德信AI产业园",
                "group": "contact",
                "required": true,
                "validate": [
                    {
                        "required": true,
                        "message": "详细地址不能为空"
                    },
                    {
                        "min": 2,
                        "message": "最少输入2个字符"
                    },
                    {
                        "max": 100,
                        "message": "最多输入100个字符"
                    }
                ]
            }
        ', true);

        $formItem = new \Biz\InformationCollect\FormItem\AddressDetailFormItem();

        $this->assertEquals($data, $formItem->required(true)->value('德信AI产业园')->getData());
    }
}
