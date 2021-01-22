<?php

namespace Tests\Unit\InformationCollect\FormItem;

use Biz\BaseTestCase;

class CountryFormItemTest extends BaseTestCase
{
    public function testGetData()
    {
        $data = json_decode('
            {
                "type": "input",
                "title": "国家",
                "field": "country",
                "group": "other",
                "value": "中国",
                "required": true,
                "validate": [
                    {
                        "required": true,
                        "message": "国家不能为空"
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

        $formItem = new \Biz\InformationCollect\FormItem\CountryFormItem();

        $this->assertEquals($data, $formItem->required(true)->value('中国')->getData());
    }
}
