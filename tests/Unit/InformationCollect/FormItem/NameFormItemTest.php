<?php

namespace Tests\Unit\InformationCollect\FormItem;

use Biz\BaseTestCase;

class NameFormItemTest extends BaseTestCase
{
    public function testGetData()
    {
        $data = json_decode('
            {
                "type": "input",
                "title": "姓名",
                "field": "name",
                "value": "张三",
                "validate": [
                    {
                        "required": true,
                        "message": "姓名不能为空"
                    },
                    {
                        "min": 2,
                        "message": "最少输入2个字符"
                    },
                    {
                        "max": 20,
                        "message": "最多输入20个字符"
                    }
                ]
            }
        ', true);

        $formItem = new \Biz\InformationCollect\FormItem\NameFormItem();

        $this->assertEquals($data, $formItem->required(true)->value('张三')->getData());
    }
}
