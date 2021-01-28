<?php

namespace Omnipay\WechatPay;

use Omnipay\Omnipay;
use Omnipay\Tests\TestCase;
use Omnipay\WechatPay\Message\CloseOrderResponse;
use Omnipay\WechatPay\Message\CompletePurchaseResponse;
use Omnipay\WechatPay\Message\CreateOrderResponse;
use Omnipay\WechatPay\Message\QueryOrderResponse;
use Omnipay\WechatPay\Message\RefundOrderResponse;

class GatewayTest extends TestCase
{

    /**
     * @var Gateway $gateway
     */
    protected $gateway;

    protected $options;

    protected $fuckTimeout = false;


    public function setUp()
    {
        parent::setUp();
        $this->gateway = Omnipay::create('WechatPay');
        $this->gateway->setAppId('123456789');
        $this->gateway->setMchId('4567891011');
        $this->gateway->setApiKey('XXSXXXSXXSXXSX');
        $this->gateway->setNotifyUrl('http://example.com/notify');
        $this->gateway->setTradeType('APP');
    }


    public function testPurchase()
    {
        if ($this->fuckTimeout) {
            return;
        }

        $order = array(
            'outTrade_No'      => '1112',//date('YmdHis'), //Should be format 'YmdHis'
            'totalfee'         => '0.01', //Order Title
            'body'             => 'test', //Your order ID
            'spbill_create_ip' => '114.119.110.120', //Order Total Fee
        );

        /**
         * @var CreateOrderResponse $response
         */
        $response = $this->gateway->purchase($order)->send();
        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
    }


    public function testCompletePurchase()
    {
        if ($this->fuckTimeout) {
            return;
        }

        $options = array(
            'request_params' => array(
                'appid'       => '123456',
                'mch_id'      => '789456',
                'result_code' => 'SUCCESS'
            ),
        );

        /**
         * @var CompletePurchaseResponse $response
         */
        $response = $this->gateway->completePurchase($options)->send();
        $this->assertFalse($response->isSuccessful());
    }


    public function testQuery()
    {
        if ($this->fuckTimeout) {
            return;
        }

        $options = array(
            'transaction_id' => '3474813271258769001041842579301293446',
        );

        /**
         * @var QueryOrderResponse $response
         */
        $response = $this->gateway->query($options)->send();
        $this->assertFalse($response->isSuccessful());
    }


    public function testClose()
    {
        if ($this->fuckTimeout) {
            return;
        }

        $options = array(
            'out_trade_no' => '1234567891023',
        );

        /**
         * @var CloseOrderResponse $response
         */
        $response = $this->gateway->query($options)->send();
        $this->assertFalse($response->isSuccessful());
    }


    public function testRefund()
    {
        if ($this->fuckTimeout) {
            return;
        }

        $options = array(
            'transaction_id' => '1234567891023',
            'out_refund_no'  => '1234567891023',
            'total_fee'      => '100',
            'refund_fee'     => '100',
        );

        /**
         * @var RefundOrderResponse $response
         */
        $response = $this->gateway->query($options)->send();
        $this->assertFalse($response->isSuccessful());
    }


    public function testQueryRefund()
    {
        if ($this->fuckTimeout) {
            return;
        }

        $options = array(
            'transaction_id' => '1234567891023',
        );

        /**
         * @var RefundOrderResponse $response
         */
        $response = $this->gateway->query($options)->send();
        $this->assertFalse($response->isSuccessful());
    }
}
