<?php

namespace Tests;

class PayServiceTest extends IntegrationTestCase
{
    public function setUp()
    {
        parent::setUp();

        $currentUser = array(
            'id' => 1,
        );
        $this->biz['user'] = $currentUser;

        $this->biz['payment.platforms.options'] = array(
            'wechat' => array(
                'appid' => 'aaa',
            ),
        );

        $this->biz['payment.final_options'] = array(
            'closed_by_notify' => true,
            'refunded_by_notify' => true,
            'coin_rate' => 1,
        );
    }

    public function testCreateTrade()
    {
        $this->rechargeCoin();

        $this->biz['payment.wechat'] = $this->mockCreateTradeResult();

        $data = $this->mockTrade();
        $data['cash_amount'] = 200;
        $data['coin_amount'] = 80;
        $trade = $this->getPayService()->createTrade($data);

        $this->assertCreatedTrade($data, $trade);
    }

    protected function assertCreatedTrade($data, $trade)
    {
        $this->assertNotEmpty($trade);
        $this->assertNotEmpty($trade['trade_sn']);
        $this->assertEquals('paying', $trade['status']);
        $this->assertEquals($data['order_sn'], $trade['order_sn']);
        $this->assertEquals($data['price_type'], $trade['price_type']);
        $this->assertEquals($data['amount'], $trade['amount']);
        $this->assertEquals($data['coin_amount'], $trade['coin_amount']);
        if ('coin' == $trade['price_type']) {
            $this->assertEquals($trade['cash_amount'] * $this->getCoinRate(), $trade['amount'] - $trade['coin_amount']);
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
        $this->rechargeCoin();
        $this->biz['payment.wechat'] = $this->mockCreateTradeResult();

        $data = $this->mockTrade();
        $trade = $this->getPayService()->createTrade($data);

        $notifyData = $this->mockNotifyData($trade);
        $this->biz['payment.wechat'] = $this->mockConvertNotifyData($notifyData);
        $result = $this->getPayService()->notifyPaid('wechat', $notifyData);
        $this->assertEquals('<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>', $result);

        $this->getPayService()->notifyPaid('wechat', $this->mockNotifyData($trade));

        $trade = $this->getPayTradeDao()->get($trade['id']);
        $this->assertPaidTrade($notifyData, $trade);
    }

    public function testLockedAmount()
    {
        $user = array(
            'user_id' => $this->biz['user']['id'],
            'cash_amount' => 100,
        );

        $userBalance = $this->getAccountService()->createUserBalance($user);

        $user = array(
            'user_id' => 2,
        );

        $seller = $this->getAccountService()->createUserBalance($user);
        $recharge = array(
            'from_user_id' => $seller['user_id'],
            'to_user_id' => $userBalance['user_id'],
            'buyer_id' => $userBalance['user_id'],
            'amount' => '100',
            'title' => '充值1000个虚拟币',
            'action' => 'recharge',
        );

        $this->getAccountService()->transferCoin($recharge);
        $userBalance = $this->getAccountService()->getUserBalanceByUserId($userBalance['user_id']);

        $this->assertEquals(100, $userBalance['amount']);

        $this->biz['payment.wechat'] = $this->mockCreateTradeResult();

        $data = $this->mockTrade();
        $data['coin_amount'] = 20;
        $trade = $this->getPayService()->createTrade($data);

        $userBalance = $this->getAccountService()->getUserBalanceByUserId($this->biz['user']['id']);
        $this->assertEquals(80, $userBalance['amount']);
        $this->assertEquals(20, $userBalance['locked_amount']);
    }

    public function testReleaseAmount()
    {
        $user = array(
            'user_id' => $this->biz['user']['id'],
            'cash_amount' => 100,
        );

        $userBalance = $this->getAccountService()->createUserBalance($user);

        $user = array(
            'user_id' => 2,
        );

        $seller = $this->getAccountService()->createUserBalance($user);
        $recharge = array(
            'from_user_id' => $seller['user_id'],
            'to_user_id' => $userBalance['user_id'],
            'buyer_id' => $userBalance['user_id'],
            'amount' => '100',
            'title' => '充值1000个虚拟币',
            'action' => 'recharge',
        );

        $this->getAccountService()->transferCoin($recharge);
        $userBalance = $this->getAccountService()->getUserBalanceByUserId($userBalance['user_id']);
        $this->assertEquals(100, $userBalance['amount']);

        $this->biz['payment.wechat'] = $this->mockCreateTradeResult();

        $data = $this->mockTrade();
        $data['coin_amount'] = 20;
        $trade = $this->getPayService()->createTrade($data);

        $userBalance = $this->getAccountService()->getUserBalanceByUserId($this->biz['user']['id']);
        $this->assertEquals(80, $userBalance['amount']);
        $this->assertEquals(20, $userBalance['locked_amount']);

        $this->getPayService()->closeTradesByOrderSn($trade['order_sn']);

        $userBalance = $this->getAccountService()->getUserBalanceByUserId($this->biz['user']['id']);
        $this->assertEquals(100, $userBalance['amount']);
        $this->assertEquals(0, $userBalance['locked_amount']);
    }

    public function testCreateZeroTrade()
    {
        $this->rechargeCoin();

        $data = $this->mockTrade();
        $data['amount'] = 20;
        $trade = $this->getPayService()->createTrade($data);
        $this->getPayService()->notifyPaid('coin', $trade);

        $trade = $this->getPayTradeDao()->get($trade['id']);
        $this->assertEquals('paid', $trade['status']);
    }

    public function testRechargeNotify()
    {
        $user = array(
            'user_id' => $this->biz['user']['id'],
        );
        $this->getAccountService()->createUserBalance($user);

        $seller = array(
            'user_id' => 12,
        );
        $this->getAccountService()->createUserBalance($seller);

        $this->biz['payment.wechat'] = $this->mockCreateTradeResult();

        $data = $this->mockTrade();
        $data['type'] = 'recharge';
        $trade = $this->getPayService()->createTrade($data);

        $notifyData = $this->mockNotifyData($trade);
        $this->biz['payment.wechat'] = $this->mockConvertNotifyData($notifyData);
        $result = $this->getPayService()->notifyPaid('wechat', $notifyData);
        $this->assertEquals('<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>', $result);

        $this->getPayService()->notifyPaid('wechat', $this->mockNotifyData($trade));

        $trade = $this->getPayTradeDao()->get($trade['id']);
        $this->assertPaidTrade($notifyData, $trade);

        $userBalance = $this->getAccountService()->getUserBalanceByUserId($this->biz['user']['id']);
        $this->assertEquals('80', $userBalance['amount']);
    }

    public function testRechargeByIap()
    {
        $seller = array(
            'user_id' => 0,
        );
        $this->getAccountService()->createUserBalance($seller);

        $this->mockIapGetWay();
        $user = array(
            'user_id' => $this->biz['user']['id'],
        );
        $this->getAccountService()->createUserBalance($user);

        $this->biz['payment.iap'] = $this->mockIapGetWay();

        $data = array(
            'transaction_id' => '1004400740201409030005092168',
            'user_id' => $user['user_id'],
            'amount' => 1000,
            'receipt' => 'xxx',
            'is_sand_box' => false,
        );
        $this->getPayService()->rechargeByIap($data);

        $userBalance = $this->getAccountService()->getUserBalanceByUserId($this->biz['user']['id']);
        $this->assertEquals('1000', $userBalance['amount']);
    }

    protected function rechargeCoin()
    {
        $user = array(
            'user_id' => $this->biz['user']['id'],
        );
        $this->getAccountService()->createUserBalance($user);

        $this->biz['payment.wechat'] = $this->mockCreateTradeResult();

        $data = $this->mockTrade();
        $data['type'] = 'recharge';
        $trade = $this->getPayService()->createTrade($data);

        $notifyData = $this->mockNotifyData($trade);
        $this->biz['payment.wechat'] = $this->mockConvertNotifyData($notifyData);
        $result = $this->getPayService()->notifyPaid('wechat', $notifyData);
        $userBalance = $this->getAccountService()->getUserBalanceByUserId($this->biz['user']['id']);
    }

    protected function mockIapGetWay()
    {
        $return = array(
            'status' => 'paid',
            'cash_flow' => '1004400740201409030005092168',
            'paid_time' => '2017-10-18 06:55:36 Etc/GMT',
            'pay_amount' => 1000,
            'cash_type' => 'CNY',
            'attach' => array(
                'user_id' => 1,
            ),
            'quantity' => 1,
            'product_id' => 1,
        );

        $mock = \Mockery::mock('Codeages\\Biz\\Pay\\Payment\\IapGateway');
        $mock->shouldReceive('converterNotify')->andReturn(array($return, 'success'));

        return $mock;
    }

    protected function assertPaidTrade($notifyData, $trade)
    {
        $xml = simplexml_load_string($notifyData, 'SimpleXMLElement', LIBXML_NOCDATA);
        $notifyData = json_decode(json_encode($xml), true);
        $this->assertEquals('wechat', $trade['platform']);
        $this->assertEquals('paid', $trade['status']);
        $this->assertEquals($notifyData['fee_type'], $trade['currency']);
        $this->assertNotEmpty($trade['notify_data']);
        $this->assertEquals($notifyData['transaction_id'], $trade['platform_sn']);
        $cashFlows = $this->getCashflowDao()->findByTradeSn($trade['trade_sn']);
        $this->assertEquals(5, count($cashFlows));

        foreach ($cashFlows as $cashFlow) {
            $this->assertNotEmpty($cashFlow['sn']);
            $this->assertTrue(in_array($cashFlow['type'], array('inflow', 'outflow')));

            $this->assertEquals($trade['order_sn'], $cashFlow['order_sn']);
            $this->assertEquals($trade['trade_sn'], $cashFlow['trade_sn']);
            if ('coin' == $cashFlow['currency']) {
                $this->assertEquals('none', $cashFlow['platform']);
            } else {
                $this->assertEquals($trade['platform'], $cashFlow['platform']);
            }
        }

        if ('recharge' == $trade['type']) {
            foreach ($cashFlows as $cashFlow) {
                if ('coin' == $cashFlow['currency']) {
                    $this->assertEquals($trade['cash_amount'] * $this->getCoinRate(), $cashFlow['amount']);
                } else {
                    $this->assertEquals($trade['cash_amount'], $cashFlow['amount']);
                }
            }
        }

        if ('purchase' == $trade['type']) {
            foreach ($cashFlows as $cashFlow) {
                if ('coin' == $cashFlow['currency']) {
                    $this->assertEquals($trade['coin_amount'], $cashFlow['amount']);
                } else {
                    $this->assertEquals($trade['cash_amount'], $cashFlow['amount']);
                }
            }
        }
    }

    public function testFindPaymentPlatforms()
    {
        $this->biz['payment.platforms.options'] = array(
            'wechat' => array(
                'appid' => 'bcbc',
            ),
        );

        $payments = $this->getPayService()->findEnabledPayments();
        $this->assertNotEmpty($payments);
    }

    public function tearDown()
    {
        parent::tearDown();
        \Mockery::close();
    }

    protected function mockTrade()
    {
        return array(
            'goods_title' => 'java基础课程',
            'goods_detail' => 'java基础课程，适合初学者',
            'attach' => array(
                'user_id' => 1,
            ),
            'order_sn' => '123456',
            'amount' => 100,
            'coin_amount' => 20,
            'notify_url' => 'http://try6.edusoho.cn/',
            'create_ip' => '127.0.0.1',
            'platform_type' => 'Native',
            'price_type' => 'money',
            'platform' => 'wechat',
            'seller_id' => '12',
            'type' => 'purchase',
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
                'user_id' => 1,
            ),
            'notify_data' => array(),
        );

        if (!empty($notifyData)) {
            $xml = simplexml_load_string($notifyData, 'SimpleXMLElement', LIBXML_NOCDATA);
            $notifyData = json_decode(json_encode($xml), true);

            $return['notify_data'] = $notifyData;
            $return['cash_flow'] = $notifyData['transaction_id'];
            $return['pay_amount'] = $notifyData['total_fee'];
            $return['paid_time'] = $this->convertTime($notifyData['time_end']);
            $return['trade_sn'] = $notifyData['out_trade_no'];
        }

        $mock = \Mockery::mock('Codeages\\Biz\\Pay\\Payment\\WechatGateway');
        $mock->shouldReceive('converterNotify')->andReturn(array($return, '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>'));

        return $mock;
    }

    protected function convertTime($paidTime)
    {
        $year = substr($paidTime, 0, 4);
        $month = substr($paidTime, 4, 2);
        $day = substr($paidTime, 6, 2);
        $hour = substr($paidTime, 8, 2);
        $minuts = substr($paidTime, 10, 2);
        $seconds = substr($paidTime, 12, 2);

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

        $mock = \Mockery::mock('Codeages\\Biz\\Pay\\Payment\\WechatGateway');
        $mock->shouldReceive('createTrade')->andReturn($return);
        $mock->shouldReceive('closeTrade')->andReturn(new CloseOrderResponseTest());

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

    protected function getPayTradeDao()
    {
        return $this->biz->dao('Pay:PayTradeDao');
    }

    protected function getCashflowDao()
    {
        return $this->biz->dao('Pay:CashflowDao');
    }

    protected function getPayService()
    {
        return $this->biz->service('Pay:PayService');
    }

    protected function getAccountService()
    {
        return $this->biz->service('Pay:AccountService');
    }
}

class CloseOrderResponseTest
{
    public function isSuccessful()
    {
        return true;
    }

    public function getFailData()
    {
        return '错误码：666，错误原因：系统错误';
    }
}
