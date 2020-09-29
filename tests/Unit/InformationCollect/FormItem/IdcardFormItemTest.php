<?php

namespace Tests\Unit\InformationCollect\FormItem;

use Biz\BaseTestCase;

class IdcardFormItemTest extends BaseTestCase
{
    public function testGetData()
    {
        $data = json_decode('
            {
                "type": "input",
                "title": "身份证号",
                "field": "idcard",
                "value": "330621199406213333",
                "group": "base",
                "props": {
                    "placeholder": "仅支持中国大陆身份证号"
                },
                "validate": [
                    {
                        "required": true,
                        "message": "身份证号不能为空"
                    },
                    {
                        "pattern": "[0-9]{17}[0-9xX]{1}",
                        "message": "身份证号格式错误"
                    }
                ]
            }
        ', true);

        $formItem = new \Biz\InformationCollect\FormItem\IdcardFormItem();

        $this->assertEquals($data, $formItem->required(true)->value('330621199406213333')->getData());
    }
}
