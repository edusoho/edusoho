<?php

namespace Omnipay\PayPal;

use Omnipay\Tests\GatewayTestCase;

class ExpressInContextGatewayTest extends GatewayTestCase
{
    /**
     * @var \Omnipay\PayPal\ExpressInContextGateway
     */
    protected $gateway;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var array
     */
    protected $voidOptions;

    public function setUp()
    {
        parent::setUp();

        $this->gateway = new ExpressInContextGateway($this->getHttpClient(), $this->getHttpRequest());

        $this->options = array(
            'amount' => '10.00',
            'returnUrl' => 'https://www.example.com/return',
            'cancelUrl' => 'https://www.example.com/cancel',
        );
        $this->voidOptions = array(
            'transactionReference' => 'ASDFASDFASDF',
        );
    }

    public function testAuthorizeSuccess()
    {
        $this->setMockHttpResponse('ExpressPurchaseSuccess.txt');

        $response = $this->gateway->authorize($this->options)->send();

        $this->assertInstanceOf('\Omnipay\PayPal\Message\ExpressInContextAuthorizeResponse', $response);
        $this->assertFalse($response->isPending());
        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->isRedirect());
        $this->assertEquals('https://www.paypal.com/checkoutnow?useraction=commit&token=EC-42721413K79637829', $response->getRedirectUrl());
    }

    public function testPurchaseSuccess()
    {
        $this->setMockHttpResponse('ExpressPurchaseSuccess.txt');

        $response = $this->gateway->purchase($this->options)->send();

        $this->assertInstanceOf('\Omnipay\PayPal\Message\ExpressInContextAuthorizeResponse', $response);
        $this->assertFalse($response->isPending());
        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->isRedirect());
        $this->assertEquals('https://www.paypal.com/checkoutnow?useraction=commit&token=EC-42721413K79637829', $response->getRedirectUrl());
    }

    public function testOrderSuccess()
    {
        $this->setMockHttpResponse('ExpressOrderSuccess.txt');

        $response = $this->gateway->order($this->options)->send();

        $this->assertInstanceOf('\Omnipay\PayPal\Message\ExpressInContextAuthorizeResponse', $response);
        $this->assertFalse($response->isPending());
        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->isRedirect());
        $this->assertEquals('https://www.paypal.com/checkoutnow?useraction=commit&token=EC-42721413K79637829', $response->getRedirectUrl());
    }
}
