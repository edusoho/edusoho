<?php

namespace Tests\Unit\InformationCollect\FormItem;

use Biz\BaseTestCase;

class CompanyFormItemTest extends BaseTestCase
{
    public function testGetData()
    {
        $data = json_decode('
            {
                "type": "input",
                "title": "公司",
                "field": "company",
                "group": "company",
                "value": "杭州阔知网络有限公司",
                "required": true,
                "validate": [
                    {
                        "required": true,
                        "message": "公司不能为空"
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

        $formItem = new \Biz\InformationCollect\FormItem\CompanyFormItem();

        $this->assertEquals($data, $formItem->required(true)->value('杭州阔知网络有限公司')->getData());
    }
}
