<?php

namespace Omnipay\PayPal\Message;

use Omnipay\Tests\TestCase;
use Omnipay\PayPal\Message\ExpressInContextAuthorizeResponse;

class ExpressInContextAuthorizeResponseTest extends TestCase
{
    public function testConstruct()
    {
        // response should decode URL format data
        $response = new ExpressInContextAuthorizeResponse($this->getMockRequest(), 'example=value&foo=bar');

        $this->assertEquals(array('example' => 'value', 'foo' => 'bar'), $response->getData());
    }

    public function testExpressPurchaseSuccess()
    {
        $httpResponse = $this->getMockHttpResponse('ExpressPurchaseSuccess.txt');
        $request = $this->getMockRequest();
        $request->shouldReceive('getTestMode')->once()->andReturn(true);
        $response = new ExpressInContextAuthorizeResponse($request, $httpResponse->getBody());

        $this->assertFalse($response->isPending());
        $this->assertFalse($response->isSuccessful());
        $this->assertSame('EC-42721413K79637829', $response->getTransactionReference());
        $this->assertNull($response->getMessage());
        $this->assertNull($response->getRedirectData());
        $this->assertSame('https://www.sandbox.paypal.com/checkoutnow?useraction=commit&token=EC-42721413K79637829', $response->getRedirectUrl());
        $this->assertSame('GET', $response->getRedirectMethod());
    }

    public function testExpressPurchaseFailure()
    {
        $httpResponse = $this->getMockHttpResponse('ExpressPurchaseFailure.txt');
        $response = new ExpressInContextAuthorizeResponse($this->getMockRequest(), $httpResponse->getBody());

        $this->assertFalse($response->isPending());
        $this->assertFalse($response->isSuccessful());
        $this->assertNull($response->getTransactionReference());
        $this->assertNull($response->getTransactionReference());
        $this->assertSame('This transaction cannot be processed. The amount to be charged is zero.', $response->getMessage());
    }
}