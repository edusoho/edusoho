<?php

use Omnipay\Tests\TestCase;

class HelperTest extends TestCase
{
    /** @test */
    public function it_should_return_an_array_if_xml_is_ok()
    {
        $xml = '<xml><return_code><![CDATA[SUCCESS]]></return_code> <return_msg><![CDATA[OK]]></return_msg> <appid><![CDATA[wx57b9bd4dd4b01ef5]]></appid> <mch_id><![CDATA[1499803642]]></mch_id> <nonce_str><![CDATA[k5ntjxBlcq0KnkJQ]]></nonce_str> <sign><![CDATA[8C03594BEB37A08BE70422F9FCC05168]]></sign> <result_code><![CDATA[SUCCESS]]></result_code> <prepay_id><![CDATA[wx05153421914401e72549e4843149659371]]></prepay_id> <trade_type><![CDATA[NATIVE]]></trade_type> <code_url><![CDATA[weixin://wxpay/bizpayurl?pr=QXuSXeo]]></code_url> </xml>';

        $array = \Omnipay\WechatPay\Helper::xml2array($xml);

        $this->assertArrayHasKey('return_code', $array);
    }


    /** @test */
    public function it_should_return_an_empty_array_if_xml_is_empty()
    {
        $array = \Omnipay\WechatPay\Helper::xml2array('');

        $this->assertEquals([], $array);
    }
}
