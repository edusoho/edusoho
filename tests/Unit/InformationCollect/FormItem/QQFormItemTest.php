<?php

namespace Tests\Unit\InformationCollect\FormItem;

use Biz\BaseTestCase;

class QQFormItemTest extends BaseTestCase
{
    public function testGetData()
    {
        $data = json_decode('
            {
                "type": "input",
                "title": "QQ号",
                "field": "qq",
                "group": "contact",
                "value": "543245784",
                "required": true,
                "validate": [
                    {
                        "required": true,
                        "message": "QQ号不能为空"
                    },
                    {
                        "pattern": "^[0-9]{5,10}$",
                        "message": "QQ号格式错误"
                    }
                ]
            }
        ', true);

        $formItem = new \Biz\InformationCollect\FormItem\QQFormItem();

        $this->assertEquals($data, $formItem->required(true)->value('543245784')->getData());
    }
}
