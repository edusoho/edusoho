<?php

namespace Omnipay\Alipay\Tests;

use Omnipay\Alipay\AopJsGateway;
use Omnipay\Alipay\Responses\AopTradeCreateResponse;

class AopJsGatewayTest extends AbstractGatewayTestCase
{

    /**
     * @var AopJsGateway $gateway
     */
    protected $gateway;

    protected $options;


    public function setUp()
    {
        parent::setUp();
        $this->gateway = new AopJsGateway($this->getHttpClient(), $this->getHttpRequest());
        $this->gateway->setAppId($this->appId);
        $this->gateway->setPrivateKey(ALIPAY_AOP_PRIVATE_KEY);
    }


    public function testPurchase()
    {
        $this->setMockHttpResponse('AopJs_Purchase_Failure.txt');

        /**
         * @var AopTradeCreateResponse $response
         */
        $response = $this->gateway->purchase(
            [
                'biz_content' => [
                    'subject'      => 'test',
                    'out_trade_no' => date('YmdHis') . mt_rand(1000, 9999),
                    'total_amount' => '0.01',
                    'product_code' => '0.01',
                ]
            ]
        )->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertArrayHasKey('code', $response->getAlipayResponse());
    }
}
