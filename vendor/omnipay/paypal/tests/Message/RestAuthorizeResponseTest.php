<?php


namespace Omnipay\PayPal\Message;


use Omnipay\Tests\TestCase;

class RestAuthorizeResponseTest extends TestCase
{
    public function testRestPurchaseWithoutCardSuccess()
    {
        $httpResponse = $this->getMockHttpResponse('RestPurchaseWithoutCardSuccess.txt');
        $data = json_decode($httpResponse->getBody()->getContents(), true);

        $response = new RestAuthorizeResponse($this->getMockRequest(), $data, $httpResponse->getStatusCode());

        $this->assertTrue($response->isSuccessful());
        $this->assertSame('PAY-3TJ47806DA028052TKTQGVYI', $response->getTransactionReference());
        $this->assertNull($response->getMessage());

        $this->assertNull($response->getRedirectData());
        $this->assertSame('GET', $response->getRedirectMethod());
        $this->assertSame('https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=EC-5KV58254GL528393N', $response->getRedirectUrl());
    }
}