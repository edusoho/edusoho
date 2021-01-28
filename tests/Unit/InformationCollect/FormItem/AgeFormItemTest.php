<?php

namespace Tests\Unit\InformationCollect\FormItem;

use Biz\BaseTestCase;

class AgeFormItemTest extends BaseTestCase
{
    public function testGetData()
    {
        $data = json_decode('
            {
                "type": "input",
                "title": "年龄",
                "field": "age",
                "value": 10,
                "group": "base",
                "required": true,
                "props": {
                    "type": "number"
                },
                "validate": [
                    {
                        "required": true,
                        "message": "年龄不能为空"
                    },
                    {
                        "pattern": "^[1-9]([0-9])?$",
                        "message": "年龄不在正常范围内"
                    }
                ]
            }
        ', true);

        $formItem = new \Biz\InformationCollect\FormItem\AgeFormItem();

        $this->assertEquals($data, $formItem->required(true)->value(10)->getData());
    }
}
