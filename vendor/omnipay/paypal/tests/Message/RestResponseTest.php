<?php

namespace Omnipay\PayPal\Message;

use Omnipay\Tests\TestCase;

class RestResponseTest extends TestCase
{
    public function testPurchaseSuccess()
    {
        $httpResponse = $this->getMockHttpResponse('RestPurchaseSuccess.txt');
        $data = json_decode($httpResponse->getBody()->getContents(), true);
        $response = new RestResponse($this->getMockRequest(), $data, $httpResponse->getStatusCode());

        $this->assertTrue($response->isSuccessful());
        $this->assertSame('44E89981F8714392Y', $response->getTransactionReference());
        $this->assertNull($response->getMessage());
    }

    public function testPurchaseFailure()
    {
        $httpResponse = $this->getMockHttpResponse('RestPurchaseFailure.txt');
        $data = json_decode($httpResponse->getBody()->getContents(), true);
        $response = new RestResponse($this->getMockRequest(), $data, $httpResponse->getStatusCode());

        $this->assertFalse($response->isSuccessful());
        $this->assertNull($response->getTransactionReference());
        $this->assertSame('Invalid request - see details', $response->getMessage());
    }

    public function testCompletePurchaseSuccess()
    {
        $httpResponse = $this->getMockHttpResponse('RestCompletePurchaseSuccess.txt');
        $data = json_decode($httpResponse->getBody()->getContents(), true);

        $response = new RestResponse($this->getMockRequest(), $data, $httpResponse->getStatusCode());

        $this->assertTrue($response->isSuccessful());
        $this->assertSame('9EA05739TH369572R', $response->getTransactionReference());
        $this->assertNull($response->getMessage());
    }

    public function testCompletePurchaseFailure()
    {
        $httpResponse = $this->getMockHttpResponse('RestCompletePurchaseFailure.txt');
        $data = json_decode($httpResponse->getBody()->getContents(), true);

        $response = new RestResponse($this->getMockRequest(), $data, $httpResponse->getStatusCode());

        $this->assertFalse($response->isSuccessful());
        $this->assertNull($response->getTransactionReference());
        $this->assertSame('This request is invalid due to the current state of the payment', $response->getMessage());
    }

    public function testTokenFailure()
    {
        $httpResponse = $this->getMockHttpResponse('RestTokenFailure.txt');
        $data = json_decode($httpResponse->getBody()->getContents(), true);

        $response = new RestResponse($this->getMockRequest(), $data, $httpResponse->getStatusCode());

        $this->assertFalse($response->isSuccessful());
        $this->assertSame('Client secret does not match for this client', $response->getMessage());
    }

    public function testAuthorizeSuccess()
    {
        $httpResponse = $this->getMockHttpResponse('RestAuthorizationSuccess.txt');
        $data = json_decode($httpResponse->getBody()->getContents(), true);

        $response = new RestResponse($this->getMockRequest(), $data, $httpResponse->getStatusCode());

        $this->assertTrue($response->isSuccessful());
        $this->assertSame('58N7596879166930B', $response->getTransactionReference());
        $this->assertNull($response->getMessage());
    }

    public function testCreateCardSuccess()
    {
        $httpResponse = $this->getMockHttpResponse('RestCreateCardSuccess.txt');
        $data = json_decode($httpResponse->getBody()->getContents(), true);

        $response = new RestResponse($this->getMockRequest(), $data, $httpResponse->getStatusCode());

        $this->assertTrue($response->isSuccessful());
        $this->assertSame('CARD-70E78145XN686604FKO3L6OQ', $response->getCardReference());
        $this->assertNull($response->getMessage());
    }
}
