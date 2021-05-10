<?php

namespace Tests\Unit\InformationCollect\FormItem;

use Biz\BaseTestCase;

class PositionFormItemTest extends BaseTestCase
{
    public function testGetData()
    {
        $data = json_decode('
            {
                "type": "input",
                "title": "职位",
                "field": "position",
                "value": "PHP程序员",
                "group": "company",
                "required": true,
                "validate": [
                    {
                        "required": true,
                        "message": "职位不能为空"
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

        $formItem = new \Biz\InformationCollect\FormItem\PositionFormItem();

        $this->assertEquals($data, $formItem->required(true)->value('PHP程序员')->getData());
    }
}
