<?php

namespace Omnipay\PayPal;

use Omnipay\Tests\GatewayTestCase;
use Omnipay\Common\CreditCard;

class RestGatewayTest extends GatewayTestCase
{
    /** @var RestGateway */
    public $gateway;

    /** @var array */
    public $options;

    /** @var array */
    public $subscription_options;

    public function setUp()
    {
        parent::setUp();

        $this->gateway = new RestGateway($this->getHttpClient(), $this->getHttpRequest());
        $this->gateway->setToken('TEST-TOKEN-123');
        $this->gateway->setTokenExpires(time() + 600);

        $this->options = array(
            'amount' => '10.00',
            'card' => new CreditCard(array(
                'firstName' => 'Example',
                'lastName' => 'User',
                'number' => '4111111111111111',
                'expiryMonth' => '12',
                'expiryYear' => date('Y'),
                'cvv' => '123',
            )),
        );

        $this->subscription_options = array(
            'transactionReference'  => 'ABC-1234',
            'description'           => 'Description goes here',
        );
    }

    public function testBearerToken()
    {
        $this->gateway->setToken('');
        $this->setMockHttpResponse('RestTokenSuccess.txt');

        $this->assertFalse($this->gateway->hasToken());
        $this->assertEquals('A015GQlKQ6uCRzLHSGRliANi59BHw6egNVKEWRnxvTwvLr0', $this->gateway->getToken()); // triggers request
        $this->assertEquals(time() + 28800, $this->gateway->getTokenExpires());
        $this->assertTrue($this->gateway->hasToken());
    }

    public function testBearerTokenReused()
    {
        $this->setMockHttpResponse('RestTokenSuccess.txt');
        $this->gateway->setToken('MYTOKEN');
        $this->gateway->setTokenExpires(time() + 60);

        $this->assertTrue($this->gateway->hasToken());
        $this->assertEquals('MYTOKEN', $this->gateway->getToken());
    }

    public function testBearerTokenExpires()
    {
        $this->setMockHttpResponse('RestTokenSuccess.txt');
        $this->gateway->setToken('MYTOKEN');
        $this->gateway->setTokenExpires(time() - 60);

        $this->assertFalse($this->gateway->hasToken());
        $this->assertEquals('A015GQlKQ6uCRzLHSGRliANi59BHw6egNVKEWRnxvTwvLr0', $this->gateway->getToken());
    }

    public function testAuthorize()
    {
        $this->setMockHttpResponse('RestPurchaseSuccess.txt');

        $response = $this->gateway->authorize($this->options)->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('44E89981F8714392Y', $response->getTransactionReference());
        $this->assertNull($response->getMessage());
    }

    public function testPurchase()
    {
        $this->setMockHttpResponse('RestPurchaseSuccess.txt');

        $response = $this->gateway->purchase($this->options)->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('44E89981F8714392Y', $response->getTransactionReference());
        $this->assertNull($response->getMessage());
    }

    public function testCapture()
    {
        $request = $this->gateway->capture(array(
            'transactionReference' => 'abc123',
            'amount' => 10.00,
            'currency' => 'AUD',
        ));

        $this->assertInstanceOf('\Omnipay\PayPal\Message\RestCaptureRequest', $request);
        $this->assertSame('abc123', $request->getTransactionReference());
        $endPoint = $request->getEndpoint();
        $this->assertSame('https://api.paypal.com/v1/payments/authorization/abc123/capture', $endPoint);
        $data = $request->getData();
        $this->assertNotEmpty($data);
    }

    public function testRefund()
    {
        $request = $this->gateway->refund(array(
            'transactionReference' => 'abc123',
            'amount' => 10.00,
            'currency' => 'AUD',
        ));

        $this->assertInstanceOf('\Omnipay\PayPal\Message\RestRefundRequest', $request);
        $this->assertSame('abc123', $request->getTransactionReference());
        $endPoint = $request->getEndpoint();
        $this->assertSame('https://api.paypal.com/v1/payments/sale/abc123/refund', $endPoint);
        $data = $request->getData();
        $this->assertNotEmpty($data);
    }

    public function testFullRefund()
    {
        $request = $this->gateway->refund(array(
            'transactionReference' => 'abc123',
        ));

        $this->assertInstanceOf('\Omnipay\PayPal\Message\RestRefundRequest', $request);
        $this->assertSame('abc123', $request->getTransactionReference());
        $endPoint = $request->getEndpoint();
        $this->assertSame('https://api.paypal.com/v1/payments/sale/abc123/refund', $endPoint);
        $data = $request->getData();

        // we're expecting an empty object here
        $json = json_encode($data);
        $this->assertEquals('{}', $json);
    }

    public function testFetchTransaction()
    {
        $request = $this->gateway->fetchTransaction(array('transactionReference' => 'abc123'));

        $this->assertInstanceOf('\Omnipay\PayPal\Message\RestFetchTransactionRequest', $request);
        $this->assertSame('abc123', $request->getTransactionReference());
        $data = $request->getData();
        $this->assertEmpty($data);
    }

    public function testListPlan()
    {
        $request = $this->gateway->listPlan(array(
            'page'         => 0,
            'status'       => 'ACTIVE',
            'pageSize'    => 10, //number of plans in a single page
            'totalRequired'     => 'yes'
        ));

        $this->assertInstanceOf('\Omnipay\PayPal\Message\RestListPlanRequest', $request);
        $this->assertSame(0, $request->getPage());
        $this->assertSame('ACTIVE', $request->getStatus());
        $this->assertSame(10, $request->getPageSize());
        $this->assertSame('yes', $request->getTotalRequired());

        $endPoint = $request->getEndpoint();
        $this->assertSame('https://api.paypal.com/v1/payments/billing-plans', $endPoint);
        $data = $request->getData();
        $this->assertNotEmpty($data);
    }

    public function testFetchPurchase()
    {
        $request = $this->gateway->fetchPurchase(array('transactionReference' => 'abc123'));

        $this->assertInstanceOf('\Omnipay\PayPal\Message\RestFetchPurchaseRequest', $request);
        $this->assertSame('abc123', $request->getTransactionReference());
        $data = $request->getData();
        $this->assertEmpty($data);
    }

    public function testListPurchase()
    {
        $request = $this->gateway->listPurchase(array(
            'count'         => 15,
            'startId'       => 'PAY123',
            'startIndex'    => 1,
            'startTime'     => '2015-09-07T00:00:00Z',
            'endTime'       => '2015-09-08T00:00:00Z',
        ));

        $this->assertInstanceOf('\Omnipay\PayPal\Message\RestListPurchaseRequest', $request);
        $this->assertSame(15, $request->getCount());
        $this->assertSame('PAY123', $request->getStartId());
        $this->assertSame(1, $request->getStartIndex());
        $this->assertSame('2015-09-07T00:00:00Z', $request->getStartTime());
        $this->assertSame('2015-09-08T00:00:00Z', $request->getEndTime());
        $endPoint = $request->getEndpoint();
        $this->assertSame('https://api.paypal.com/v1/payments/payment', $endPoint);
        $data = $request->getData();
        $this->assertNotEmpty($data);
    }

    public function testCreateCard()
    {
        $this->setMockHttpResponse('RestCreateCardSuccess.txt');

        $response = $this->gateway->createCard($this->options)->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('CARD-70E78145XN686604FKO3L6OQ', $response->getCardReference());
        $this->assertNull($response->getMessage());
    }

    public function testPayWithSavedCard()
    {
        $this->setMockHttpResponse('RestCreateCardSuccess.txt');
        $response = $this->gateway->createCard($this->options)->send();
        $cardRef = $response->getCardReference();

        $this->setMockHttpResponse('RestPurchaseSuccess.txt');
        $response = $this->gateway->purchase(array('amount'=>'10.00', 'cardReference'=>$cardRef))->send();
        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('44E89981F8714392Y', $response->getTransactionReference());
        $this->assertNull($response->getMessage());
    }

    // Incomplete generic tests for subscription payments

    public function testCompleteSubscription()
    {
        $this->setMockHttpResponse('RestExecuteSubscriptionSuccess.txt');
        $response = $this->gateway->completeSubscription($this->subscription_options)->send();
        $this->assertTrue($response->isSuccessful());
        $this->assertNull($response->getMessage());

        $this->assertEquals('I-0LN988D3JACS', $response->getTransactionReference());
    }

    public function testCancelSubscription()
    {
        $this->setMockHttpResponse('RestGenericSubscriptionSuccess.txt');
        $response = $this->gateway->cancelSubscription($this->subscription_options)->send();
        $this->assertTrue($response->isSuccessful());
        $this->assertNull($response->getMessage());
    }

    public function testSuspendSubscription()
    {
        $this->setMockHttpResponse('RestGenericSubscriptionSuccess.txt');
        $response = $this->gateway->suspendSubscription($this->subscription_options)->send();
        $this->assertTrue($response->isSuccessful());
        $this->assertNull($response->getMessage());
    }

    public function testReactivateSubscription()
    {
        $this->setMockHttpResponse('RestGenericSubscriptionSuccess.txt');
        $response = $this->gateway->reactivateSubscription($this->subscription_options)->send();
        $this->assertTrue($response->isSuccessful());
        $this->assertNull($response->getMessage());
    }

    public function testRefundCapture()
    {
        $request = $this->gateway->refundCapture(array(
            'transactionReference' => 'abc123'
        ));

        $this->assertInstanceOf('\Omnipay\PayPal\Message\RestRefundCaptureRequest', $request);
        $this->assertSame('abc123', $request->getTransactionReference());
        $endPoint = $request->getEndpoint();
        $this->assertSame('https://api.paypal.com/v1/payments/capture/abc123/refund', $endPoint);
        
        $request->setAmount('15.99');
        $request->setCurrency('BRL');
        $request->setDescription('Test Description');
        $data = $request->getData();
        // we're expecting an empty object here
        $json = json_encode($data);
        $this->assertEquals('{"amount":{"currency":"BRL","total":"15.99"},"description":"Test Description"}', $json);
    }

    public function testVoid()
    {
        $request = $this->gateway->void(array(
            'transactionReference' => 'abc123'
        ));

        $this->assertInstanceOf('\Omnipay\PayPal\Message\RestVoidRequest', $request);
        $this->assertSame('abc123', $request->getTransactionReference());
        $endPoint = $request->getEndpoint();
        $this->assertSame('https://api.paypal.com/v1/payments/authorization/abc123/void', $endPoint);
        $data = $request->getData();
        $this->assertEmpty($data);
    }
}
