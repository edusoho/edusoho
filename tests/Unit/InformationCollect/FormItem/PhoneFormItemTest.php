<?php

namespace Tests\Unit\InformationCollect\FormItem;

use Biz\BaseTestCase;

class PhoneFormItemTest extends BaseTestCase
{
    public function testGetData()
    {
        $data = json_decode('
            {
                "type": "input",
                "title": "手机号码",
                "field": "phone",
                "value": "1333333333",
                "props": {
                    "type": "number",
                    "placeholder": "仅支持中国大陆手机号码"
                },
                "validate": [
                    {
                        "required": true,
                        "message": "手机号码不能为空"
                    },
                    {
                        "pattern": "^[1][0-9]{10}$",
                        "message": "手机号码格式错误"
                    }
                ]
            }
        ', true);

        $formItem = new \Biz\InformationCollect\FormItem\PhoneFormItem();

        $this->assertEquals($data, $formItem->required(true)->value('1333333333')->getData());
    }
}
