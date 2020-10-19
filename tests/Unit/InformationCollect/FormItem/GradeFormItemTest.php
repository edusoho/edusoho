<?php

namespace Tests\Unit\InformationCollect\FormItem;

use Biz\BaseTestCase;

class GradeFormItemTest extends BaseTestCase
{
    public function testGetData()
    {
        $data = json_decode('
            {
                "type": "input",
                "title": "年级",
                "field": "grade",
                "value": "一年级",
                "group": "school",
                "required": true,
                "validate": [
                    {
                        "required": true,
                        "message": "年级不能为空"
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

        $formItem = new \Biz\InformationCollect\FormItem\GradeFormItem();

        $this->assertEquals($data, $formItem->required(true)->value('一年级')->getData());
    }
}
