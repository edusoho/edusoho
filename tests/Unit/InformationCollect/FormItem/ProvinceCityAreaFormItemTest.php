<?php

namespace Tests\Unit\InformationCollect\FormItem;

use Biz\BaseTestCase;

class ProvinceCityAreaFormItemTest extends BaseTestCase
{
    public function testGetData()
    {
        $data = json_decode('
            {
                "type": "cascader",
                "title": "省市区县",
                "field": "province_city_area",
                "group": "contact",
                "value": ["浙江省", "杭州市", "滨江区"],
                "required": true,
                "props": {
                    "options": [],
                    "placeholder": "请选择省市区县"
                },
                "validate": [
                    {
                        "required": true,
                        "message": "省市区县不能为空"
                    }
                ]
            }
        ', true);

        $formItem = new \Biz\InformationCollect\FormItem\ProvinceCityAreaFormItem();

        $this->assertEquals($data, $formItem->required(true)->value(['浙江省', '杭州市', '滨江区'])->getData());
    }
}
