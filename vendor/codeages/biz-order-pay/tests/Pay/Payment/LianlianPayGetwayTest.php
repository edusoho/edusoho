<?php

namespace Tests;

use Codeages\Biz\Pay\Payment\LianlianPayGetway;
use Codeages\Biz\Pay\Payment\SignatureToolkit;

class LianlianPayGetwayTest extends IntegrationTestCase
{
    public function setUp()
    {
        parent::setUp();
        $signatureToolkit = \Mockery::mock(SignatureToolkit::class);
        $this->biz['payment.platforms'] = array(
            'lianlianpay' => array(
                'secret' => 'secret',
                'accessKey' => 'accessKey',
                'oid_partner' => 'oid_partner',
                'signatureToolkit' => $signatureToolkit
            ),
        );
    }

    public function testConverterNotify()
    {
        $gateway = new LianlianPayGetway($this->biz);

        $this->biz['payment.platforms']['lianlianpay']['signatureToolkit']->shouldReceive('signVerify')->andReturn(true);

        $result = $gateway->converterNotify(
            array(
                'oid_partner' => 'abc',
                'sign_type' => 'RSA',
                'sign' => '123',
                'dt_order' => '19701122092022',
                'no_order' => '123123123',
                'oid_paybill' => 'oid_paybill',
                'money_order' => '12321',
                'result_pay' => 'success',
                'settle_date' => '20171120',
                'info_order' => '221332',
                'pay_type' => 'money',
                'bank_code' => '2221332',
            )
        );

        $this->assertArrayEquals(
            array(
                array(
                    'status' => 'paid',
                    'cash_flow' => 'oid_paybill',
                    'paid_time' => time(),
                    'pay_amount' => 1232100,
                    'cash_type' => 'CNY',
                    'trade_sn' => '123123123',
                    'attach' => array(),
                    'notify_data' => array(
                        'oid_partner' => 'abc',
                        'sign_type' => 'RSA',
                        'sign' => '123',
                        'dt_order' => '19701122092022',
                        'no_order' => '123123123',
                        'oid_paybill' => 'oid_paybill',
                        'money_order' => '12321',
                        'result_pay' => 'success',
                        'settle_date' => '20171120',
                        'info_order' => '221332',
                        'pay_type' => 'money',
                        'bank_code' => '2221332',
                    ),
                ),
                '{"ret_code":"0000","ret_msg":"\u4ea4\u6613\u6210\u529f"}',
            ),
            $result
        );
    }

    /**
     * @expectedException Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testCreateTradeWithInvalidArguments()
    {
        $gateway = new LianlianPayGetway($this->biz);
        $gateway->createTrade(array());
    }

    public function testCreateTrade()
    {
        $this->biz['payment.platforms']['lianlianpay']['signatureToolkit']->shouldReceive('signParams')->andReturn('signResult');
        $gateway = new LianlianPayGetway($this->biz);
        $result = $gateway->createTrade(array(
            'goods_title' => 'goods_title_result',
            'goods_detail' => 'detail_result',
            'attach' => array(
                'identify_user_id' => 123,
                'user_created_time' => 1231232123,
            ),
            'trade_sn' => 'trade_sn_result',
            'amount' => 333211,
            'notify_url' => 'http://www.edusoho.com/notify',
            'return_url' => 'http://www.edusoho.com/return',
            'create_ip' => '127.0.0.1',
            'platform_type' => 'Wap',
            'show_url' => 'http://www.edusoho.com/show',
        ));

        $reqData = json_decode($result['data']['req_data'], true);
        $this->assertEquals('signResult', $reqData['sign']);
    }
}
