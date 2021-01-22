<?php

namespace Tests\Unit\InformationCollect\FormItem;

use Biz\BaseTestCase;

class BirthdayFormItemTest extends BaseTestCase
{
    public function testGetData()
    {
        $data = json_decode('
            {
                "type": "DatePicker",
                "title": "生日",
                "field": "birthday",
                "value": "1994-06-21",
                "group": "base",
                "required": true,
                "props": {
                    "type": "date",
                    "format": "yyyy-MM-dd",
                    "placeholder": "请选择出生年月日"
                },
                "validate": [
                    {
                        "required": true,
                        "message": "生日不能为空"
                    }
                ]
            }
        ', true);

        $formItem = new \Biz\InformationCollect\FormItem\BirthdayFormItem();

        $this->assertEquals($data, $formItem->required(true)->value('1994-06-21')->getData());
    }
}
