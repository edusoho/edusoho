<?php

namespace Tests\Unit\InformationCollect\FormItem;

use Biz\BaseTestCase;

class ClassFormItemTest extends BaseTestCase
{
    public function testGetData()
    {
        $data = json_decode('
            {
                "type": "input",
                "title": "班级",
                "field": "class",
                "group": "school",
                "value": "班级名称",
                "required": true,
                "validate": [
                    {
                        "required": true,
                        "message": "班级不能为空"
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

        $formItem = new \Biz\InformationCollect\FormItem\ClassFormItem();

        $this->assertEquals($data, $formItem->required(true)->value('班级名称')->getData());
    }
}
