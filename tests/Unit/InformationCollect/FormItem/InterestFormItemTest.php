<?php

namespace Tests\Unit\InformationCollect\FormItem;

use Biz\BaseTestCase;

class InterestFormItemTest extends BaseTestCase
{
    public function testGetData()
    {
        $data = json_decode('
            {
                "type": "textarea",
                "title": "兴趣",
                "field": "interest",
                "value": "篮球",
                "group": "other",
                "required": true,
                "validate": [
                    {
                        "required": true,
                        "message": "兴趣不能为空"
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

        $formItem = new \Biz\InformationCollect\FormItem\InterestFormItem();

        $this->assertEquals($data, $formItem->required(true)->value('篮球')->getData());
    }
}
