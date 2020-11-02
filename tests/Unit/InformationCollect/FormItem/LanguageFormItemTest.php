<?php

namespace Tests\Unit\InformationCollect\FormItem;

use Biz\BaseTestCase;

class LanguageFormItemTest extends BaseTestCase
{
    public function testGetData()
    {
        $data = json_decode('
            {
                "type": "input",
                "title": "语言",
                "field": "language",
                "value": "中文",
                "group": "other",
                "required": true,
                "validate": [
                    {
                        "required": true,
                        "message": "语言不能为空"
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

        $formItem = new \Biz\InformationCollect\FormItem\LanguageFormItem();

        $this->assertEquals($data, $formItem->required(true)->value('中文')->getData());
    }
}
