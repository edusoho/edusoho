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
                "value": "abc123",
                "validate": [
                    {
                        "required": true,
                        "message": "微信号不能为空"
                    },
                    {
                        "pattern": "^[a-zA-Z]([-_a-zA-Z0-9]{5,19})+$",
                        "message": "微信号格式错误"
                    }
                ]
            }
        ', true);

        $formItem = new \Biz\InformationCollect\FormItem\WechatFormItem();

        $this->assertEquals($data, $formItem->required(true)->value('abc123')->getData());
    }
}
