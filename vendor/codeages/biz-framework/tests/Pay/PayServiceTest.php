<?php

namespace Tests;

use Codeages\Biz\Framework\Pay\Payment\WechatGetway;

class PayServiceTest extends IntegrationTestCase
{
    public function setUp()
    {
        parent::setUp();

        $currentUser = array(
            'id' => 1
        );
        $this->biz['user'] = $currentUser;
    }

    public function testCreateTrade()
    {
        $this->biz['payment.wechat'] = $this->mockCreateTradeResult();

        $data = $this->mockTrade();
        $this->getPayService()->createTrade($data);

        $trade = $this->getPaymentTradeDao()->getByOrderSnAndPlatform('123456', 'wechat');
        $this->assertCreatedTrade($data, $trade);
    }

    protected function assertCreatedTrade($data, $trade)
    {
        $this->assertNotEmpty($trade);
        $this->assertNotEmpty($trade['trade_sn']);
        $this->assertEquals('created', $trade['status']);
        $this->assertEquals($data['order_sn'], $trade['order_sn']);
        $this->assertEquals($data['price_type'], $trade['price_type']);
        $this->assertEquals($data['amount'], $trade['amount']);
        $this->assertEquals($data['coin_amount'], $trade['coin_amount']);
        if ($trade['price_type'] == 'coin') {
            $this->assertEquals($trade['cash_amount'] * $this->getCoinRate(), $trade['amount']-$trade['coin_amount']);
        } else {
            $this->assertEquals($trade['cash_amount'] * $this->getCoinRate(), $trade['amount'] * $this->getCoinRate() - $trade['coin_amount']);
        }
        $this->assertEquals($data['platform'], $trade['platform']);
        $this->assertEquals($data['goods_title'], $trade['title']);
        $this->assertEquals($data['seller_id'], $trade['seller_id']);
        $this->assertNotEmpty($trade['platform_created_result']);
    }

    public function testPurchaseNotify()
    {
        $this->biz['payment.wechat'] = $this->mockCreateTradeResult();

        $data = $this->mockTrade();
        $trade = $this->getPayService()->createTrade($data);

        $notifyData = $this->mockNotifyData($trade);
        $this->biz['payment.wechat'] = $this->mockConvertNotifyData($notifyData);
        $result = $this->getPayService()->notify('wechat', $notifyData);
        $this->assertEquals('<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>',$result);

        $this->getPayService()->notify('wechat', $this->mockNotifyData($trade));

        $trade = $this->getPaymentTradeDao()->get($trade['id']);
        $this->assertPaidTrade($notifyData, $trade);
    }

    public function testRechargeNotify()
    {
        $this->biz['payment.wechat'] = $this->mockCreateTradeResult();

        $data = $this->mockTrade();
        $data['type'] = 'recharge';
        $trade = $this->getPayService()->createTrade($data);

        $notifyData = $this->mockNotifyData($trade);
        $this->biz['payment.wechat'] = $this->mockConvertNotifyData($notifyData);
        $result = $this->getPayService()->notify('wechat', $notifyData);
        $this->assertEquals('<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>',$result);

        $this->getPayService()->notify('wechat', $this->mockNotifyData($trade));

        $trade = $this->getPaymentTradeDao()->get($trade['id']);
        $this->assertPaidTrade($notifyData, $trade);
    }

    protected function assertPaidTrade($notifyData, $trade)
    {
        $xml = simplexml_load_string($notifyData,'SimpleXMLElement', LIBXML_NOCDATA);
        $notifyData = json_decode(json_encode($xml),TRUE);
        $this->assertEquals('wechat', $trade['platform']);
        $this->assertEquals('paid', $trade['status']);
        $this->assertEquals($notifyData['fee_type'], $trade['currency']);
        $this->assertNotEmpty($trade['notify_data']);
        $this->assertEquals($notifyData['transaction_id'], $trade['platform_sn']);

        if (in_array($trade['type'], array('purchase', 'recharge')))  {
            $cashFlows = $this->getUserCashflowDao()->findByTradeSn($trade['trade_sn']);

            $this->assertEquals(3,count($cashFlows));
            foreach ($cashFlows as $cashFlow) {
                $this->assertNotEmpty($cashFlow['sn']);
                $this->assertTrue(in_array($cashFlow['type'], array('inflow', 'outflow')));
                $this->assertEquals($this->biz['user']['id'], $cashFlow['user_id']);
                $this->assertEquals($trade['order_sn'], $cashFlow['order_sn']);
                $this->assertEquals($trade['trade_sn'], $cashFlow['trade_sn']);
                $this->assertEquals($trade['platform'], $cashFlow['platform']);

                if ($cashFlow['type'] == 'outflow') {
                    $this->assertNotEmpty($cashFlow['parent_sn']);
                }

                if($cashFlow['currency'] == 'coin' && $cashFlow['type'] == 'outflow') {
                    $this->assertEquals($trade['coin_amount'] + $trade['cash_amount'] * $this->getCoinRate(), $cashFlow['amount']);
                }

                if($cashFlow['currency'] == 'coin' && $cashFlow['type'] == 'inflow') {
                    $this->assertEquals($trade['cash_amount'] * $this->getCoinRate(), $cashFlow['amount']);
                }

                if($cashFlow['currency'] != 'coin') {
                    $this->assertEquals($trade['currency'], $cashFlow['currency']);
                    $this->assertEquals($trade['cash_amount'], $cashFlow['amount']);
                }
            }

            $siteCashflows = $this->getSiteCashflowDao()->findByTradeSn($trade['trade_sn']);
            foreach ($siteCashflows as $index => $siteCashflow) {
                $this->assertNotEmpty($siteCashflow['sn']);
                $this->assertEquals($trade['trade_sn'], $siteCashflow['trade_sn']);
                $this->assertTrue(in_array($siteCashflow['currency'], array('CNY', 'coin')));

                if ('coin' == $siteCashflow['currency']) {
                    $this->assertEquals($trade['coin_amount'] + $trade['cash_amount'] * $this->getCoinRate(), $siteCashflow['amount']);
                } else {
                    $this->assertEquals($trade['cash_amount'], $siteCashflow['amount']);
                }

                $this->assertEquals($trade['platform'], $siteCashflow['platform']);
                $this->assertEquals($trade['title'], $siteCashflow['title']);
                $this->assertEquals($trade['order_sn'], $siteCashflow['order_sn']);
                $this->assertEquals($trade['platform_sn'], $siteCashflow['platform_sn']);
                $this->assertEquals($trade['price_type'], $siteCashflow['price_type']);
                $this->assertEquals($trade['pay_time'], $siteCashflow['pay_time']);
            }
        }
    }

    public function tearDown()
    {
        \Mockery::close();
    }

    protected function mockTrade()
    {
        return array(
            'goods_title' => 'java基础课程',
            'goods_desc' => 'java基础课程，适合初学者',
            'attach' => array(
                'user_id' => 1
            ),
            'order_sn' => '123456',
            'amount' => 100,
            'coin_amount' => 20,
            'notify_url' => 'http://try6.edusoho.cn/',
            'create_ip' => '127.0.0.1',
            'pay_type' => 'Native',
            'price_type' => 'money',
            'platform' => 'wechat',
            'seller_id' => '12'
        );

    }

    protected function mockNotifyData($trade = array())
    {
        $outTradeNo = empty($trade) ? '1409811653' : $trade['trade_sn'];
        $totalFee = empty($trade) ? '100' : $trade['cash_amount'];
        return "<xml>
              <appid><![CDATA[wx2421b1c4370ec43b]]></appid>
              <attach><![CDATA[支付测试]]></attach>
              <bank_type><![CDATA[CFT]]></bank_type>
              <fee_type><![CDATA[CNY]]></fee_type>
              <is_subscribe><![CDATA[Y]]></is_subscribe>
              <mch_id><![CDATA[10000100]]></mch_id>
              <nonce_str><![CDATA[5d2b6c2a8db53831f7eda20af46e531c]]></nonce_str>
              <openid><![CDATA[oUpF8uMEb4qRXf22hE3X68TekukE]]></openid>
              <out_trade_no><![CDATA[{$outTradeNo}]]></out_trade_no><result_code><![CDATA[SUCCESS]]></result_code>
              <return_code><![CDATA[SUCCESS]]></return_code>
              <sign><![CDATA[B552ED6B279343CB493C5DD0D78AB241]]></sign>
              <sub_mch_id><![CDATA[10000100]]></sub_mch_id>
              <time_end><![CDATA[20140903131540]]></time_end>
              <total_fee>{$totalFee}</total_fee>
              <trade_type><![CDATA[JSAPI]]></trade_type>
              <transaction_id><![CDATA[1004400740201409030005092168]]></transaction_id>
            </xml>";
    }

    protected function mockConvertNotifyData($notifyData = '')
    {
        $return = array(
            'status' => 'paid',
            'cash_flow' => '1004400740201409030005092168',
            'paid_time' => time(),
            'pay_amount' => 10,
            'trade_sn' => '1409811653',
            'cash_type' => 'CNY',
            'attach' => array(
                'user_id' => 1
            ),
            'notify_data' => array()
        );

        if (!empty($notifyData)) {
            $xml = simplexml_load_string($notifyData,'SimpleXMLElement', LIBXML_NOCDATA);
            $notifyData = json_decode(json_encode($xml),TRUE);

            $return['notify_data'] = $notifyData;
            $return['cash_flow'] = $notifyData['transaction_id'];
            $return['pay_amount'] = $notifyData['total_fee'];
            $return['paid_time'] = $this->convertTime($notifyData['time_end']);
            $return['trade_sn'] = $notifyData['out_trade_no'];
        }

        $mock = \Mockery::mock(WechatGetway::class);
        $mock->shouldReceive('converterNotify')->andReturn(array($return, '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>'));
        return $mock;
    }

    protected function convertTime($paidTime)
    {
        $year = substr($paidTime,0,4);
        $month = substr($paidTime,4,2);
        $day = substr($paidTime,6,2);
        $hour = substr($paidTime,8,2);
        $minuts = substr($paidTime,10,2);
        $seconds = substr($paidTime,12,2);
        return strtotime("{$year}-{$month}-{$day} {$hour}:{$minuts}:{$seconds}");
    }

    protected function mockCreateTradeResult()
    {
        $return = array(
            'return_code' => 'SUCCESS',
            'return_msg' => 'OK',
            'appid' => 'wx2421b1c4370ec43b',
            'mch_id' => '10000100',
            'nonce_str' => 'IITRi8Iabbblz1Jc',
            'openid' => 'oUpF8uMuAJO_M2pxb1Q9zNjWeS6o',
            'prepay_id' => 'wx201411101639507cbf6ffd8b0779950874',
            'trade_type' => 'JSAPI',
            'sign' => '7921E432F65EB8ED0CE9755F0E86D72F',
            'result_code' => 'SUCCESS',
        );

        $mock = \Mockery::mock(WechatGetway::class);
        $mock->shouldReceive('createTrade')->andReturn($return);
        return $mock;
    }

    protected function getCoinRate()
    {
        return 1;
    }

    protected function getSiteCashflowDao()
    {
        return $this->biz->dao('Pay:SiteCashflowDao');
    }

    protected function getPaymentTradeDao()
    {
        return $this->biz->dao('Pay:PaymentTradeDao');
    }

    protected function getUserCashflowDao()
    {
        return $this->biz->dao('Pay:UserCashflowDao');
    }

    protected function getPayService()
    {
        return $this->biz->service('Pay:PayService');
    }
}