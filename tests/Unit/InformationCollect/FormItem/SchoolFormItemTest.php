<?php

namespace Tests\Unit\InformationCollect\FormItem;

use Biz\BaseTestCase;

class SchoolFormItemTest extends BaseTestCase
{
    public function testGetData()
    {
        $data = json_decode('
            {
                "type": "input",
                "title": "学校",
                "field": "school",
                "group": "school",
                "value": "浙江大学",
                "required": true,
                "validate": [
                    {
                        "required": true,
                        "message": "学校不能为空"
                    },
                    {
                        "min": 2,
                        "message": "最少输入2个字符"
                    },
                    {
                        "max": 40,
                        "message": "最多输入40个字符"
                    }
                ]
            }
        ', true);

        $formItem = new \Biz\InformationCollect\FormItem\SchoolFormItem();

        $this->assertEquals($data, $formItem->required(true)->value('浙江大学')->getData());
    }
}
