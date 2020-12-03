<?php

namespace Tests\Unit\InformationCollect\FormItem;

use Biz\BaseTestCase;

class WechatFormItemTest extends BaseTestCase
{
    public function testGetData()
    {
        $data = json_decode('
            {
                "type": "input",
                "title": "微信号",
                "field": "wechat",
                "group": "contact",
                "value": "abc123",
                "required": true,
                "validate": [
                    {
                        "required": true,
                        "message": "微信号不能为空"
                    },
                    {
                        "pattern": "^[a-zA-Z]([-_a-zA-Z0-9])+$",
                        "message": "微信号格式错误"
                    },
                    {
                        "min": 6, 
                        "message": "微信号格式错误"
                    },
                    {
                        "max": 20,
                        "message": "微信号格式错误" 
                    }
                ]
            }
        ', true);

        $formItem = new \Biz\InformationCollect\FormItem\WechatFormItem();

        $this->assertEquals($data, $formItem->required(true)->value('abc123')->getData());
    }
}
