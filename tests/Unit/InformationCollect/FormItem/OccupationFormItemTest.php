<?php

namespace Tests\Unit\InformationCollect\FormItem;

use Biz\BaseTestCase;

class OccupationFormItemTest extends BaseTestCase
{
    public function testGetData()
    {
        $data = json_decode('
            {
                "type": "input",
                "title": "职业",
                "field": "occupation",
                "value": "程序员",
                "group": "company",
                "required": true,
                "validate": [
                    {
                        "required": true,
                        "message": "职业不能为空"
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

        $formItem = new \Biz\InformationCollect\FormItem\OccupationFormItem();

        $this->assertEquals($data, $formItem->required(true)->value('程序员')->getData());
    }
}
