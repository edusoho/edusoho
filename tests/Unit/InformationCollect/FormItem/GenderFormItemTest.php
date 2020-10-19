<?php

namespace Tests\Unit\InformationCollect\FormItem;

use Biz\BaseTestCase;

class GenderFormItemTest extends BaseTestCase
{
    public function testGetData()
    {
        $data = json_decode('
            {
                "type": "radio",
                "title": "性别",
                "field": "gender",
                "group": "base",
                "value": "男",
                "required": true,
                "options": [
                    {
                        "value": "男",
                        "label": "男"
                    },
                    {
                        "value": "女",
                        "label": "女"
                    }
                ],
                "validate": [
                    {
                        "required": true,
                        "message": "性别不能为空"
                    }
                ]
            }
        ', true);

        $formItem = new \Biz\InformationCollect\FormItem\GenderFormItem();

        $this->assertEquals($data, $formItem->required(true)->value('男')->getData());
    }
}
